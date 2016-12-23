<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Registration
{
    private static $option_name = 'ems_event_registration';

    private $event_post_id;
    private $user_id;
    /**
     * @var $data
     * Array of fields which belongs to the registration. Could be used for event specific information for the participants list
     */
    private $data;

    public function __construct($event_post_id, $user_id, $data = array())
    {
        $this->event_post_id = $event_post_id;
        $this->user_id = $user_id;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function get_event_post_id()
    {
        return $this->event_post_id;
    }

    /**
     * @return mixed
     */
    public function get_user_id()
    {
        return $this->user_id;
    }

    /**
     * @param array $data
     */
    public function set_data($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }

    public function equals(self $otherObject)
    {
        if ($otherObject->get_event_post_id() == $this->get_event_post_id() && $otherObject->get_user_id() == $this->get_user_id()) {
            return true;
        }
        return false;
    }

    public static function add_event_registration(Ems_Event_Registration $registration, $sendMail = true)
    {
        $registrations = self::get_event_registrations();
        if (self::is_already_registered($registration)) {
            throw new Exception("User is already registered for this event");
        }
        $registrations[] = $registration;
        $title = htmlspecialchars_decode(get_post($registration->get_event_post_id())->post_title);
        $user = get_userdata($registration->get_user_id());
        $subject = 'Erfolgreich für "' . $title . '" registriert';
        $message =
            'Liebe/r ' . $user->user_firstname . "\n" .
            'du hast dich erfolgreich für das Event "' . $title . '" registriert.' . "\n" .
            'Du bekommst spätestens 14 Tage vor dem Event weitere Informationen vom Eventleiter zugeschickt.' . "\n" .
            'Viele Grüße,' . "\n" .
            'Das DHV-Jugendteam';

        $leader_id = get_post_meta($registration->get_event_post_id(), 'ems_event_leader', true);
        $leader = get_userdata($leader_id);

        if (false === $leader) {
            $leader_email = get_post_meta($registration->get_event_post_id(), 'ems_event_leader_mail', true);
        } else {
            $leader_email = $leader->user_email;
        }

        if ($sendMail) {
            self::send_mail_via_smtp($user->user_email, $subject, $message, $leader_email);
        }


        if (1 == get_post_meta($registration->get_event_post_id(), 'ems_inform_via_mail', true)) {
            //TODO Use Ems_Event object
            $leader_id = get_post_meta($registration->get_event_post_id(), 'ems_event_leader', true);
            $leader = get_userdata($leader_id);

            if (false === $leader) {
                $leader_email = get_post_meta($registration->get_event_post_id(), 'ems_event_leader_mail', true);
            } else {
                $leader_email = $leader->user_email;
            }

            if ($sendMail && false !== $leader_email) {
                $subject = 'Es gibt eine neue Anmeldung für das "' . $title . '" Event';
                $message = $user->user_firstname . ' ' . $user->lastname . ' hat sich für dein Event "' . $title . '" angemeldet.' . "\n";
                $message .= 'Du kannst die Details zur Anmeldung auf ' . get_permalink(get_option('ems_partcipant_list_page')) . '?select_event=ID_' . $registration->get_event_post_id() . ' einsehen';
                self::send_mail_via_smtp($leader_email, $subject, $message);
            }
        }
        update_option(self::$option_name, $registrations);
    }


    public static function delete_event_registration(Ems_Event_Registration $registration)
    {
        $registrations = self::get_event_registrations();
        foreach ($registrations as $key => $cur_registration) {
            if ($registration->equals($cur_registration)) {
                unset($registrations[$key]);
            }
        }
        update_option(self::$option_name, $registrations);
    }

    /**
     * @return Ems_Event_Registration[]
     */
    protected static function get_event_registrations()
    {
        $registrations = get_option(self::$option_name);
        if (is_array($registrations)) {
            return $registrations;
        }
        return array();
    }


    /**
     * @param $event_post_id
     *
     * @return Ems_Event_Registration[]
     */
    public static function get_registrations_of_event($event_post_id)
    {
        $registrations = self::get_event_registrations();
        $event_registrations = array();
        foreach ($registrations as $registration) {
            if ($registration->get_event_post_id() == $event_post_id) {
                $event_registrations[] = $registration;
            }
        }
        return $event_registrations;
    }

    /**
     * @param $user_id
     *
     * @return Ems_Event_Registration[]
     */
    public static function get_registrations_of_user($user_id)
    {
        $registrations = self::get_event_registrations();
        $event_registrations = array();
        foreach ($registrations as $registration) {
            if ($registration->get_user_id() == $user_id) {
                $event_registrations[] = $registration;
            }
        }
        return $event_registrations;
    }

    public static function get_registration($event_id, $user_id)
    {
        $registrations = self::get_event_registrations();
        foreach ($registrations as $registration) {
            if ($registration->get_user_id() == $user_id && $event_id == $registration->get_event_post_id()) {
                return $registration;
            }
        }
        return null;
    }

    public static function is_already_registered(Ems_Event_Registration $registration)
    {
        $registrations = self::get_event_registrations();
        $used = false;
        foreach ($registrations as $cur_registration) {
            if ($registration->get_event_post_id() == $cur_registration->get_event_post_id() && $registration->get_user_id() == $cur_registration->get_user_id()) {
                $used = true;
                break;
            }
        }
        return $used;
    }

    /**
     * @return string
     */
    public static function get_option_name()
    {
        return self::$option_name;
    }

    public static function send_mail_via_smtp($email, $subject, $message, $reply_to = 'info@dhv-jugend.de')
    {

        $mail = new PHPMailer();
        $mail->IsSMTP(); //1 und 1 doesn't support isSMTP from webshosting packages
        $mail->CharSet = 'utf-8';
        $mail->Host = get_option('fum_smtp_host'); // Specify main and backup server
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = get_option('fum_smtp_username'); // SMTP username
        $mail->Password = get_option('fum_smtp_password'); // SMTP password
        $mail->SMTPSecure = 'tls'; // Enable encryption, 'ssl' also accepted
        $mail->Port = 587;

        $mail->AddReplyTo($reply_to);

        $mail->From = get_option('fum_smtp_sender');
        $mail->FromName = get_option('fum_smtp_sender_name');
        $mail->addAddress($email); // Add a recipient
        $mail->Sender = $reply_to;
        $mail->addCC('anmeldungen@test.dhv-jugend.de');

        $mail->WordWrap = 50; // Set word wrap to 50 characters
        $mail->isHTML(false); // Set email format to HTML

        $mail->Subject = $subject;
        $mail->Body = $message;

        if (!$mail->send()) {
            echo "Unser Mailserver scheint im Moment überlastet zu sein, bitte versuche es später nochmal!";
            throw new Exception("Could not sent mail, maybe your server has a problem? " . $mail->ErrorInfo);
        }


        //Check if imap extension is installed
        if (function_exists("imap_open")) {
            $stream = imap_open("{imap.1und1.de:143}Gesendete Objekte", get_option('fum_smtp_username'), get_option('fum_smtp_password'));
            if (false === $stream) {
                throw new Exception("Could not copy mail to sent directory, please check your IMAP server and IMAP directory configuration");
            }

            imap_append($stream, "{imap.1und1.de:143}Gesendete Objekte"
                , "From: " . get_option('fum_smtp_sender') . "\r\n"
                . "To: " . $email . "\r\n"
                . "Subject: " . $subject . "\r\n"
                . "\r\n"
                . $message . "\r\n"
            );

            $check = imap_check($stream);
            imap_close($stream);
        }
    }


}