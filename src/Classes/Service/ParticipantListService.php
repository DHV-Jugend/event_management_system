<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Service;

use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Model\Event;
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
use PHPExcel_Writer_Excel2007;

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
    }

    /**
     * @param \BIT\EMS\Domain\Model\EventRegistration[] $eventRegistrations
     * @param string $filePath
     * @param Set|null $fields
     * @return bool
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function generateParticipantList(array $eventRegistrations, string $filePath, Set $fields = null)
    {
        $participant_list = [];

        foreach ($eventRegistrations as $registration) {
            $user_data = array_intersect_key(
                Fum_User::get_user_data($registration->get_user_id()),
                array_merge(
                    Fum_Html_Form::get_form(
                        Fum_Conf::$fum_event_register_form_unique_name
                    )->get_unique_names_of_input_fields(),
                    ["fum_premium_participant" => "fum_premium_participant"]
                )
            );
            if (empty($user_data)) {
                continue;
            }
            unset($user_data[Fum_Conf::$fum_input_field_submit]);
            unset($user_data[Fum_Conf::$fum_input_field_accept_agb]);
            $participant_list[] = array_merge(
                $user_data,
                $registration->get_data(),
                ['id' => $registration->get_user_id()]
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

                $value = (0 === $participant[$title] ? 'Nein' : ('1' === $participant[$title] ? 'Ja' : $participant[$title]));
                if ($title === 'fum_premium_participant') {
                    $value = (empty($participant[$title]) ? 'Nein' : 'Ja');
                }
                $field = Fum_Html_Input_Field::get_input_field($title);
                if ($this->isAllowedField($field, $fields)) {
                    $data[$index + 1][] = $value;
                }
            }
        }

        $objPHPExcel = $this->getBootstrappedPhpExcel();
        $objPHPExcel->getActiveSheet()->fromArray($data);

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        try {
            $objWriter->save($filePath);
            if (file_exists($filePath)) {
                return true;
            }
        } catch (\Exception $e) {
            //TODO Add logging
            echo '';
        }


        return false;
    }

    public function generatePrivateParticipantList(array $eventRegistrations, string $filePath)
    {
        return $this->generateParticipantList($eventRegistrations, $filePath, null);
    }

    /**
     * Write all registration values to participant list
     *
     * @param \BIT\EMS\Model\Event $event
     * @param string $filePath
     * @return bool
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
     * @return bool
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
     * @return bool
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function generatePublicParticipantListFromEvent(Event $event, string $filePath)
    {
        return $this->generatePublicParticipantList($this->eventRegistrationRepository->findByEvent($event), $filePath);
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