<?php
/**
 * @author Christoph Bessei
 * @version
 */

use BIT\EMS\Domain\Model\EventRegistration;

/**
 * Class Ems_Event quasi(!) extends WP_Post, because WP_Post is final it fake the extends via __get and __set method
 * e.g you can access the WP_Post variable $post_title via $event->post_title
 */
class Ems_Event extends \BIT\EMS\Model\AbstractPost
{

    protected static $post_type = 'ems_event';
    protected static $capability_type = ['ems_event', 'ems_events'];
    protected static $object = null;

    protected static $start_date_meta_key = 'ems_start_date';
    protected static $end_date_meta_key = 'ems_end_date';
    protected static $leader_meta_key = 'ems_event_leader';
    protected static $show_event_meta_key = 'ems_show_event';


    protected $start_date_time;
    protected $end_date_time;
    /** @var  Ems_Participant_Level[] */
    protected $participantLevels;
    /** @var Ems_Participant_Type[] */
    protected $participantTypes;
    /**
     * If $show_event is true, the event will be shown even if the start and end date do not fit the requirements.
     *
     * @var bool
     */
    protected $show_event;
    /**
     * place of event
     * @var ???
     */
    protected $location;

    protected $leader;

    public function __construct($post)
    {
        if (is_numeric($post)) {
            $post = get_post($post);
        }

        $this->post = $post;
        $postID = $post->ID;
        $this->start_date_time = get_post_meta($postID, self::$start_date_meta_key, true);
        if (empty($this->start_date_time)) {
            $this->start_date_time = null;
        }
        $this->end_date_time = get_post_meta($postID, self::$end_date_meta_key, true);
        if (empty($this->end_date_time)) {
            $this->end_date_time = null;
        }

        $this->leader = get_userdata(get_post_meta($postID, self::$leader_meta_key, true));
        if (false === $this->leader) {
            $this->leader = get_post_meta($postID, self::$leader_meta_key, true);
        }

        //Participant levels
        $this->participantLevels = get_post_meta($postID, 'ems_participant_level', true);
        $this->participantTypes = get_post_meta($postID, 'ems_participant_type', true);


        $this->show_event = get_post_meta($post->ID, self::$show_event_meta_key, true);

    }


    public function save_post()
    {
        /**
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $this->ID;
        }

        // Check the user's permissions.
        if ((isset($_REQUEST['post_type']) && Ems_Event::get_post_type() == $_REQUEST['post_type'])) {
            if (!current_user_can(Ems_Event::get_edit_capability(), $this->ID)) {
                return $this->ID;
            }
        }


        //Save form options first, then date stuff
        if (isset($_REQUEST['ems_premium_field_nonce']) && wp_verify_nonce(
                $_REQUEST['ems_premium_field_nonce'],
                'ems_premium_field'
            )) {

            /* OK, its safe for us to save the data now. */
            if (!isset($_REQUEST['ems_premium_field'])) {
                $_REQUEST['ems_premium_field'] = 0;
            }
            $premium_field = sanitize_text_field($_REQUEST['ems_premium_field']);
            update_post_meta($this->ID, 'ems_premium_field', $premium_field);
            $ID = preg_replace("/[^0-9]/", "", $_REQUEST['ems_event_leader']);
            //Set mail address if "Benutzerdefiniert" was set as leader
            if ("0" === $ID) {
                update_post_meta(
                    $this->ID,
                    'ems_event_leader_mail',
                    sanitize_text_field($_REQUEST['ems_event_leader_mail'])
                );
            } else {
                update_post_meta($this->ID, 'ems_event_leader_mail', "");
            }
            update_post_meta($this->ID, 'ems_event_leader', $ID);
            if (!isset($_REQUEST['ems_inform_via_mail'])) {
                $_REQUEST['ems_inform_via_mail'] = 0;
            }
            $inform_via_mail = sanitize_text_field($_REQUEST['ems_inform_via_mail']);
            update_post_meta($this->ID, 'ems_inform_via_mail', $inform_via_mail);

        }

        //Save participant levels
        if (
            isset($_REQUEST['ems_participant_level_meta_box_nonce']) &&
            wp_verify_nonce($_REQUEST['ems_participant_level_meta_box_nonce'], 'ems_participant_level_meta_box') &&
            isset($_REQUEST['ems_participant_level']) &&
            is_array($_REQUEST['ems_participant_level'])
        ) {
            $participantLevels = $_REQUEST['ems_participant_level'];
            foreach ($participantLevels as $level => $value) {
                //Check value (0,0.5 or 1)
                if (!is_numeric($value) || !in_array($value, [0, 0.5, 1])) {
                    unset($participantLevels[$level]);
                    continue;
                }
                $participantLevels[$level] = new Ems_Participant_Level($level, $value, $level);
            }
            //Unset deprecated value
            unset($participantLevels["amateur"]);
            update_post_meta($this->ID, 'ems_participant_level', $participantLevels);
        }

        //Save participant types
        if (
            isset($_REQUEST['ems_participant_type_meta_box_nonce']) &&
            wp_verify_nonce($_REQUEST['ems_participant_type_meta_box_nonce'], 'ems_participant_type_meta_box') &&
            isset($_REQUEST['ems_participant_type']) &&
            is_array($_REQUEST['ems_participant_type'])
        ) {
            $participantTypes = $_REQUEST['ems_participant_type'];
            foreach ($participantTypes as $type => $value) {
                if (!in_array($value, [0, 1])) {
                    unset($participantTypes[$type]);
                    continue;
                }
                $participantTypes[$type] = new Ems_Participant_Type($type, $value, $type);
            }
            update_post_meta($this->ID, 'ems_participant_type', $participantTypes);
        }

        //Save start and end date
        if (isset($_REQUEST['ems_calendar_meta_box_nonce']) &&
            wp_verify_nonce($_REQUEST['ems_calendar_meta_box_nonce'], 'ems_calendar_meta_box')
        ) {
            $format = get_option('date_format');

            $start_date = $_REQUEST['ems_start_date'];
            $end_date = $_REQUEST['ems_end_date'];
            $start_date = Ems_Date_Helper::get_timestamp($format, $start_date);
            $end_date = Ems_Date_Helper::get_timestamp($format, $end_date);

            $date_time_start_date = new DateTime('@' . $start_date);
            $date_time_end_date = new DateTime('@' . $end_date);

            //Convert Datetime back to $_POST values, to check if date is valid
            if (date_i18n($format, $date_time_start_date->getTimestamp()) == sanitize_text_field(
                    $_REQUEST['ems_start_date']
                )) {
                // Update the meta field in the database.
                update_post_meta($this->ID, 'ems_start_date', $date_time_start_date);
            } else {
                return $this->ID;
            }


            if (date_i18n($format, $date_time_end_date->getTimestamp()) == sanitize_text_field(
                    $_REQUEST['ems_end_date']
                )) {
                // Update the meta field in the database.
                update_post_meta($this->ID, 'ems_end_date', $date_time_end_date);
            }
        }

        return $this->ID;
    }


    /**
     * @param DateTime $end_date_time
     */
    public function set_end_date_time($end_date_time)
    {
        $this->end_date_time = $end_date_time;
    }

    /**
     * @return DateTime
     */
    public function get_end_date_time()
    {
        return $this->end_date_time;
    }

    /**
     * @param DateTime $start_date_time
     */
    public function set_start_date_time($start_date_time)
    {
        $this->start_date_time = $start_date_time;
    }

    /**
     * @return DateTime
     */
    public function get_start_date_time()
    {
        return $this->start_date_time;
    }

    /**
     * @param bool|WP_User $leader
     */
    public function set_leader($leader)
    {
        $this->leader = $leader;
    }

    /**
     * @return bool|WP_User
     */
    public function get_leader()
    {
        return $this->leader;
    }

    /**
     * @param mixed $location
     */
    public function set_location($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function get_location()
    {
        return $this->location;
    }

    /**
     * @param boolean $show_event
     */
    public function set_show_event($show_event)
    {
        $this->show_event = $show_event;
    }

    /**
     * @return boolean
     */
    public function get_show_event()
    {
        return $this->show_event;
    }

    /**
     * @return Ems_Participant_Level[]
     */
    public function getParticipantLevels()
    {
        return $this->participantLevels;
    }

    /**
     * @param Ems_Participant_Level[] $participantLevels
     */
    public function setParticipantLevels($participantLevels)
    {
        $this->participantLevels = $participantLevels;
    }

    /**
     * @return Ems_Participant_Type[]
     */
    public function getParticipantTypes()
    {
        return $this->participantTypes;
    }

    /**
     * @param Ems_Participant_Type[] $participantTypes
     */
    public function setParticipantTypes($participantTypes)
    {
        $this->participantTypes = $participantTypes;
    }

    /**
     * @param $dateFormat
     * @return string
     */
    public function getFormattedDateString($dateFormat)
    {
        /** @var DateTime $start_date_object */
        $start_date_object = $this->get_start_date_time();
        $start_date = "";

        if (null !== $start_date_object) {
            $start_date = date_i18n($dateFormat, $start_date_object->getTimestamp());
        }
        /** @var DateTime $end_date_object */
        $end_date_object = $this->get_end_date_time();
        $end_date = "";
        if (null !== $end_date_object) {
            $end_date = date_i18n($dateFormat, $end_date_object->getTimestamp());
        }
        $date_string = "";
        if (!empty($start_date) && !empty($end_date)) {
            $date_string = $start_date . ' - ' . $end_date;
        }
        return $date_string;
    }

    public function update(Fum_Observable $observable)
    {
        if ($observable instanceof Fum_Html_Form) {
            switch ($observable->get_unique_name()) {
                case Fum_Conf::$fum_event_register_form_unique_name:

                    //Value of the input field is ID_<id_of_event> the preg_replace below is not safe for use with floating points numbers, but  ID should be an integer anyway
                    $post_id = preg_replace(
                        "/[^0-9]/",
                        "",
                        $observable->get_input_field(
                            Fum_Conf::$fum_input_field_select_event
                        )->get_value()
                    );
                    self::register_user_to_event($post_id, get_current_user_id(), $observable);
                    break;
            }
        }
    }

    /**
     * @param bool $short
     * @return null|string
     */
    public function get_formatted_date($short = false)
    {
        /** @var DateTime $start_date_object */
        $start_date_object = $this->get_start_date_time();
        $start_date = "";
        if (null !== $start_date_object) {
            if ($short) {
                $start_date = date_i18n("j.n", $start_date_object->getTimestamp());
            } else {
                $start_date = date_i18n(get_option('date_format'), $start_date_object->getTimestamp());
            }
        }
        /** @var DateTime $end_date_object */
        $end_date_object = $this->get_end_date_time();
        $end_date = "";
        if (null !== $end_date_object) {
            if ($short) {
                $end_date = date_i18n("j.n", $end_date_object->getTimestamp());

            } else {
                $end_date = date_i18n(get_option('date_format'), $end_date_object->getTimestamp());
            }
        }
        $date_string = null;
        if (!empty($start_date) && !empty($end_date)) {
            $date_string = $start_date . ' - ' . $end_date;
        }

        return $date_string;
    }

    /**
     * Orders event ascending by start date and end date (if start dates are equal)
     *
     * @param Ems_Event $a
     * @param Ems_Event $b
     *
     * @return int
     */
    public static function compare(Ems_Event $a, Ems_Event $b)
    {

        $a_start = 0;
        if ($a->get_start_date_time() instanceof DateTime) {
            $a_start = $a->get_start_date_time()->getTimestamp();
        }

        $b_start = 0;
        if ($b->get_start_date_time() instanceof DateTime) {
            $b_start = $b->get_start_date_time()->getTimestamp();
        }

        $start_diff = $a_start - $b_start;
        if ($start_diff != 0) {
            return $start_diff;
        }

        $a_end = 0;
        if ($a->get_end_date_time() instanceof DateTime) {
            $a_end = $a->get_end_date_time()->getTimestamp();
        }

        $b_end = 0;
        if ($b->get_end_date_time() instanceof DateTime) {
            $b_end = $b->get_end_date_time()->getTimestamp();
        }

        return ($a_end - $b_end);
    }

    public static function observe_object(Fum_Observable $observable)
    {
        if (self::$object === null) {
            self::$object = new Ems_Event(-1);
        }
        $observable->addObserver(self::$object);
    }


    /**
     * @param $id
     * @return Ems_Event|null
     * @throws Exception
     */
    public static function get_event($id)
    {
        $post = get_post($id);
        return new self($post);

    }

    /**
     * Returns the events which are currently active (starts between ems_start_date_period and ems_end_date_period)
     *
     * @param bool $hideEventsInPast
     * @return Ems_Event[]
     * @throws \Exception
     */
    public static function get_active_events($hideEventsInPast = false)
    {
        $allowed_event_time_start = new DateTime();
        $allowed_event_time_start->setTimestamp(
            Ems_Date_Helper::get_timestamp(get_option("date_format"), get_option("ems_start_date_period"))
        );

        $allowed_event_time_end = new DateTime();
        $allowed_event_time_end->setTimestamp(
            Ems_Date_Helper::get_timestamp(get_option("date_format"), get_option("ems_end_date_period"))
        );
        $allowed_event_time_period = new Ems_Date_Period($allowed_event_time_start, $allowed_event_time_end);

        $allowed_event_time_end_period = new Ems_Date_Period(
            new DateTime(),
            $allowed_event_time_end
        );

        return self::get_events(-1, true, false, null, [], $allowed_event_time_period, $allowed_event_time_end_period);
    }


    /**
     * @param Ems_Date_Period $start_period
     *
     * @return Ems_Event[]
     * @throws Exception
     */
    public static function get_events_by_start_date(Ems_Date_Period $start_period)
    {

        return self::get_events(-1, true, false, null, [], $start_period);
    }

    /**
     * @param Ems_Date_Period $end_period
     *
     * @return Ems_Event[]
     * @throws Exception
     */
    public static function get_events_by_end_date(Ems_Date_Period $end_period)
    {
        return self::get_events(-1, true, false, null, [], null, $end_period);
    }


    /**
     * Returns an array of events (posts with post_type Ems_Event::$post_type)
     *
     * @param int $limit limits the returned events. If $sort the events gets sorted first and then the first $limit events will be returned
     *                                            without sort
     * @param bool $sort sort events (true) or not (false)
     * @param bool $reverse_order reverse the order after sort, has no affect if $sort=false
     * @param callable $user_sort_callback function which compares two Ems_Event objects, used with usort(). Default is Ems_Event->compare
     * @param array $user_args array of arguments to use in wordpress get_posts. 'post_type','posts_per_page' and 'order_by' are ignored! Use $sort and $limit instead
     * @param Ems_Date_Period $start_period period in which the event should start, if $start_period AND $end_period are NOT set, no filtering is done
     * @param Ems_Date_Period $end_period period in which the event should end
     *
     * @throws Exception
     * @return Ems_Event[]    returns an array of Ems_Event
     */
    public static function get_events(
        $limit = -1,
        $sort = true,
        $reverse_order = false,
        callable $user_sort_callback = null,
        array $user_args = [],
        Ems_Date_Period $start_period = null,
        Ems_Date_Period $end_period = null
    ) {

        //return empty array if limit is 0
        if (0 === $limit) {
            return [];
        }

        //unset post_type,posts_per_page and order_by from $user_args because we do this on our own
        unset($user_args['post_type']);
        unset($user_args['posts_per_page']);
        unset($user_args['order_by']);

        $posts_per_page = $limit;
        if ($sort) {
            //Because we have to order the events later in the function, we need all events
            $posts_per_page = -1;
        }

        $args = [
            'post_type' => self::get_post_type(),
            'posts_per_page' => $posts_per_page,
        ];
        $posts = get_posts(array_merge($user_args, $args));

        $events = [];
        /** @var WP_Post[] $posts */
        foreach ($posts as $post) {
            $event = new Ems_Event($post);
            $start_date_time = $event->get_start_date_time();
            //Check if start period is set and if the event start fits in
            if (null !== $start_period && (!$start_date_time instanceof DateTime || !$start_period->contains(
                        $event->get_start_date_time()
                    ))) {
                //Skip event if start isn't in start period
                continue;
            }

            $end_date_time = $event->get_end_date_time();
            //Check if end period is set and if the event end fits in
            if (null !== $end_period && (!$end_date_time instanceof DateTime || !$end_period->contains(
                        $event->get_end_date_time()
                    ))) {
                //Skip event if end isn't in end period
                continue;
            }

            $events[] = $event;
        }


        if ($sort) {
            if (null === $user_sort_callback) {
                $user_sort_callback = [__CLASS__, 'compare'];
            }

            if (false === usort($events, $user_sort_callback)) {
                throw new Exception(
                    "Couldn't sort events with " . print_r($user_sort_callback, true) . " as callback function"
                );
            }
            if ($reverse_order) {
                $events = array_reverse($events);
            }
        }

        //Take the first $limit elements if array is sorted, if not we have done this via posts_per_page
        if ($sort && $limit > -1) {
            $events = array_splice($events, 0, $limit);
        }

        return $events;
    }

    private static function register_user_to_event($event_post_id, $user_id, Fum_Html_Form $form = null)
    {
        $event_registration = (new EventRegistration())->setEventId($event_post_id)->setUserId($user_id);
        $data = [];

        $used_input_fields = Fum_Activation::get_event_input_fields();
        if (null !== $form) {
            foreach ($form->get_input_fields() as $input_field) {
                //Skip select_event field (contains ID) because we already have $event_post_id
                if ($input_field->get_unique_name() == 'select_event' || $input_field->get_unique_name(
                    ) == Fum_Conf::$fum_input_field_accept_agb) {
                    continue;
                }
                if (in_array($input_field->get_unique_name(), $used_input_fields)) {
                    $value = $input_field->get_value();
                    if (empty($value)) {
                        $value = 0;
                    }
                    $data[$input_field->get_unique_name()] = $value;
                }
            }
        }

        $event_registration->setData($data);
        (new \BIT\EMS\Service\Event\Registration\RegistrationService())->add($event_registration);
    }


    public static function get_event_by_id($id)
    {
        return get_post($id);
    }

    private static function delete_user_from_event(Fum_Html_Form $form)
    {

    }

    public static function register_post_type()
    {
        register_post_type(
            self::get_post_type(),
            [
                'labels' => ['name' => __('Events'), 'singular_name' => __('Event')],
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'post_type' => self::get_post_type(),
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => true,
                'capability_type' => self::get_capability_type(),
                'has_archive' => false,
                'hierarchical' => false,
                'supports' => ['title', 'editor', 'custom_fields'],
            ]
        );
    }

    public static function get_custom_columns()
    {
        return [];
        // TODO: Implement get_custom_columns() method.
    }

    public function get_meta_value($name)
    {
        // TODO: Implement get_meta_value() method.
    }

    /**
     * Returns a meta value in a "nice" format. e.g. not the post ID but the post title, not an array but a string etc.
     *
     * @param string $name name of the meta value
     *
     * @return string print friendly string
     */
    public function get_meta_value_printable($name)
    {
        // TODO: Implement get_meta_value_printable() method.
    }


}