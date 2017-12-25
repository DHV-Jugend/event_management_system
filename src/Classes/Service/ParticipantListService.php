<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Service;

use BIT\EMS\Domain\Model\EventRegistration;
use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Model\Event;
use BIT\EMS\Service\Cloud\WebDav;
use BIT\EMS\Settings\Settings;
use BIT\EMS\Settings\Tab\CloudTab;
use BIT\EMS\Utility\GeneralUtility;
use BIT\EMS\Utility\PHPExcel\Value_Binder;
use Ds\Set;
use Fum_Conf;
use Fum_Html_Form;
use Fum_Html_Input_Field;
use Fum_User;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Worksheet;

class ParticipantListService
{
    /**
     * @var \Ds\Set
     */
    protected $publicFields;

    /**
     * @var \BIT\EMS\Domain\Repository\EventRegistrationRepository
     */
    protected $eventRegistrationRepository;

    protected $webDavSettings;

    protected $webDav;

    /**
     * ParticipantList constructor
     */
    public function __construct()
    {
        $this->eventRegistrationRepository = new EventRegistrationRepository();
        $this->publicFields = new Set(
            [
                "Vorname",
                "Nachname",
                "E-Mail",
                "Stadt",
                "Postleitzahl",
                "Bundesland",
                "Telefonnummer",
                "Handynummer",
                "Suche Mitfahrgelegenheit",
                "Biete Mitfahrgelgenheit",
            ]
        );

        $this->webDavSettings = [
            'baseUri' => Settings::get(CloudTab::CLOUD1_HOST, CloudTab::class),
            'userName' => Settings::get(CloudTab::CLOUD1_USERNAME, CloudTab::class),
            'password' => Settings::get(CloudTab::CLOUD1_PASSWORD, CloudTab::class),
            'dir' => Settings::get(CloudTab::CLOUD1_DIR, CloudTab::class),
        ];

        $this->webDav = new WebDav($this->webDavSettings);
    }


    public function generateAndUploadPrivateParticipantListFromEvent(Event $event)
    {
        return $this->generateAndUploadParticipantList($event, 'private');
    }

    public function generateAndUploadPublicParticipantListFromEvent(Event $event)
    {
        return $this->generateAndUploadParticipantList($event, 'public');
    }

    protected function generateAndUploadParticipantList(Event $event, $type = 'public')
    {
        $basePath = \Event_Management_System::getPluginPath();
        // File names must stay the same for an event. Otherwise each run would create a new file instead of updating the old one
        $fileNameBase = sanitize_file_name($event->get_post()->post_title) . '_' . $event->getID();

        if ($type === 'private') {
            $fileName = $fileNameBase . '_Eventleiter.xlsx';
        } else {
            $fileName = $fileNameBase . '_Teilnehmer.xlsx';
        }

        $fileNameSecured = sha1(random_bytes(30)) . $fileName;

        $listPath = $basePath . 'tmp/' . $fileNameSecured;

        if ($type === 'private') {
            $newChecksum = $this->generatePrivateParticipantListFromEvent($event, $listPath);
            $metaKey = 'privateParticipantChecksum';
        } else {
            $newChecksum = $this->generatePublicParticipantListFromEvent($event, $listPath);
            $metaKey = 'publicParticipantChecksum';
        }

        $webDavFolder = trim($this->webDavSettings['dir'], '/') . '/' . $event->get_start_date_time()->format('Y');
        $remotePath = $webDavFolder . '/' . $fileName;

        $this->uploadList($event, $metaKey, $listPath, $remotePath, $newChecksum);
    }

    /**
     * @param array $eventRegistrations
     * @param string $filePath
     * @return string
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function generatePrivateParticipantList(array $eventRegistrations, string $filePath)
    {
        return $this->generateParticipantList($eventRegistrations, $filePath, null);
    }

    /**
     * Write all registration values to participant list
     *
     * @param \BIT\EMS\Model\Event $event
     * @param string $filePath
     * @return string
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function generatePrivateParticipantListFromEvent(Event $event, string $filePath)
    {
        return $this->generatePrivateParticipantList(
            $this->eventRegistrationRepository->findByEvent($event),
            $filePath
        );
    }

    /**
     * @param array $eventRegistrations
     * @param string $filePath
     * @return string
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function generatePublicParticipantList(array $eventRegistrations, string $filePath)
    {
        return $this->generateParticipantList($eventRegistrations, $filePath, $this->publicFields);
    }

    /**
     * Write public registration values to participant list
     * @param \BIT\EMS\Model\Event $event
     * @param string $filePath
     * @return string
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function generatePublicParticipantListFromEvent(Event $event, string $filePath)
    {
        return $this->generatePublicParticipantList($this->eventRegistrationRepository->findByEvent($event), $filePath);
    }

    /**
     * @param EventRegistration[] $eventRegistrations
     * @param string $filePath
     * @param Set|null $fields
     * @return string MD5 checksum of content
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    protected function generateParticipantList(array $eventRegistrations, string $filePath, Set $fields = null)
    {
        $participant_list = [];

        foreach ($eventRegistrations as $registration) {
            $user_data = array_intersect_key(
                Fum_User::get_user_data($registration->getUserId()),
                array_merge(
                    Fum_Html_Form::get_form(
                        Fum_Conf::$fum_event_register_form_unique_name
                    )->get_unique_names_of_input_fields(),
                    ['fum_premium_participant' => 'fum_premium_participant']
                )
            );
            if (empty($user_data)) {
                continue;
            }
            unset($user_data[Fum_Conf::$fum_input_field_submit]);
            unset($user_data[Fum_Conf::$fum_input_field_accept_agb]);
            $participant_list[] = array_merge(
                $user_data,
                $registration->getData(),
                ['id' => $registration->getUserId()]
            );
        }

        $data = [];
        // Has event participants?
        if (isset($participant_list[0])) {
            $fieldOrder = $participant_list[0];
        } else {
            $fieldOrder = [];
        }

        //Generate title row
        foreach ($fieldOrder as $title => $value) {
            $field = Fum_Html_Input_Field::get_input_field($title);
            if ($this->isAllowedField($field, $fields)) {
                $data[0][] = $field->get_title();
            }
        }

        //Generate entry rows
        foreach ($participant_list as $index => $participant) {
            foreach ($fieldOrder as $title => $unused) {
                // TODO Remove static conversion
                $value = (0 === $participant[$title] ? 'Nein' : ('1' === $participant[$title] ? 'Ja' : $participant[$title]));
                if ($title === 'fum_premium_participant') {
                    $value = (empty($participant[$title]) ? 'Nein' : 'Ja');
                }
                // TODO Add output filter (e.g to allow plugins convert from 0 to "No" / 1 to  Yes etc.
                $field = Fum_Html_Input_Field::get_input_field($title);
                if ($this->isAllowedField($field, $fields)) {
                    $data[$index + 1][] = $value;
                }
            }
        }

        $objPHPExcel = $this->getBootstrappedPhpExcel();
        $objPHPExcel->getActiveSheet()->fromArray($data);

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($filePath);
        if (file_exists($filePath)) {
            return md5(json_encode($data));
        }
        throw new \Exception("Couldn't write file " . $filePath);
    }

    /**
     * @param Fum_Html_Input_Field $field
     * @param \Ds\Set|null $fields
     * @return bool
     */
    protected function isAllowedField($field, ?Set $fields)
    {
        // Invalid field
        if (!$field instanceof Fum_Html_Input_Field) {
            return false;
        }

        // No restriction
        if (is_null($fields)) {
            return true;
        }

        return $fields->contains($field->get_title()) || $fields->contains($field->get_unique_name());
    }

    /**
     * @param string $name
     * @return \PHPExcel
     * @throws \PHPExcel_Exception
     */
    protected function getBootstrappedPhpExcel(string $name = 'Teilnehmerliste')
    {
        $objPHPExcel = new PHPExcel();
        $myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, $name);
        //Use customized value binder so phone numbers with leading zeros are preserved
        PHPExcel_Cell::setValueBinder(new Value_Binder());

        //Remove default worksheet named "Worksheet"
        $objPHPExcel->removeSheetByIndex(0);

        // Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
        $objPHPExcel->addSheet($myWorkSheet, 0);
        $objPHPExcel->setActiveSheetIndex(0);
        return $objPHPExcel;
    }

    /**
     * @param \BIT\EMS\Model\Event $event
     * @param $metaKey
     * @param $localPath
     * @param $remotePath
     * @param $newChecksum
     */
    protected function uploadList(Event $event, $metaKey, $localPath, $remotePath, $newChecksum)
    {
        $changed = $newChecksum !== get_post_meta($event->getID(), $metaKey, true);

        $uploadSuccessful = $this->webDav->upload(file_get_contents($localPath), $remotePath, $changed);
        if ($changed && $uploadSuccessful) {
            update_post_meta($event->getID(), $metaKey, $newChecksum);
        }

        unlink($localPath);
    }

    /**
     * Get url safe file name (unique, not guessable) for participant lists
     *
     * @param \BIT\EMS\Model\Event $event
     * @param string|null $additionalDescription
     * @return string
     */
    public static function getUrlSafeFileName(Event $event, string $additionalDescription = null)
    {
        $fileName = sanitize_file_name($event->get_post()->post_title) . '_';
        if (!is_null($additionalDescription)) {
            $fileName .= $additionalDescription . '_';
        }
        $fileName .= GeneralUtility::getUrlSafeUid() . '_';
        $fileName .= $event->getID();
        $fileName .= '.xlsx';

        return $fileName;
    }
}