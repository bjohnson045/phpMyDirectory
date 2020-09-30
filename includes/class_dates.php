<?php
/**
* Date Class
* Formats date from input output from user input, user output, and the database
*/
class Dates {
    /**
    * Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Database object
    * @var object Database
    */
    var $db;
    /**
    * Timezone offset based on timezone settings
    * @var int
    */
    var $offset = 0;

    /**
    * Date Constructor
    * Sets timezone variable
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $timezone = null;
        // Check if an admin is logged in
        if($this->PMDR->get('Session')->get('admin_id')) {
            // If logged in check their timezone, if empty it will fall back on default
            if($this->PMDR->get('Session')->get('admin_timezone')) {
                $timezone = $this->PMDR->get('Session')->get('admin_timezone');
            }
        }
        // We check the section here because of the case where an admin could be logged in as a user
        if(PMD_SECTION != 'admin' AND $this->PMDR->get('Session')->get('user_timezone')) {
            $timezone = $this->PMDR->get('Session')->get('user_timezone');
        }
        // Fall back on default timezone if we can't find one for the admin/user
        if(!isset($timezone) AND $this->PMDR->getConfig('timezone')) {
            $timezone = $this->PMDR->getConfig('timezone');
        }
        if(is_null($timezone)) {
            $timezone = date_default_timezone_get();
        }
        $this->setOffset($timezone);
    }

    /**
    * Set the offset by timezone
    * @param string $zone
    */
    function setOffset($zone) {
        $this->offset = $this->getOffset($zone);
    }

    /**
    * Get the offset by timezone
    * @param string $zone
    * @return int Offset
    */
    function getOffset($zone) {
        $timezone_utc = new DateTimeZone('UTC');
        $timezone_utc_now = new DateTime("now", $timezone_utc);
        try {
            $timezone_custom = new DateTimeZone($zone);
            return $timezone_custom->getOffset($timezone_utc_now);
        } catch(Exception $e) {
            trigger_error('Bad timezone: '.$zone,E_USER_WARNING);
            return 0;
        }
    }

    /**
    * Get the UTC timestamp
    * @return int
    */
    function timestampNow() {
        return time();
    }

    /**
    * Adjust the timestamp
    * Since this is UTC we do not adjust it
    * @param int $timestamp Timestamp to adjust
    * @return int
    */
    function adjustOffset($timestamp) {
        return $timestamp;
    }

    /**
    * Format a date using localization settings
    * @param string $date_string Date string
    * @return string Formatted date
    */
    function formatDate($date_string, $format = null) {
        if($this->isZero($date_string)) {
            return false;
        }
        $time = strtotime($date_string);
        if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/',$date_string)) {
            $time = $this->adjustOffset($time);
        }
        if(!is_null($format)) {
            if(strstr($format,'%')) {
                return strftime($format,$time);
            } else {
                return date($format,$time);
            }
        } else {
            return strftime($this->PMDR->getConfig('date_format'),$time);
        }
    }

    /**
    * Format a datetime string using localization settings
    * @param string $date_string Date string
    * @param boolean $show_time Include time in output
    * @return string
    */
    function formatDateTime($date_string) {
        return $this->formatDate($date_string,$this->PMDR->getConfig('date_format').' '.$this->PMDR->getConfig('time_format'));
    }

    /**
    * Format a date timestamp using localization settings
    * @param int $timestamp Timestamp to format
    * @param boolean $show_time Include time in output
    * @return string
    */
    function formatTimeStamp($timestamp, $show_time = false) {
        if($show_time) {
            return $this->formatDateTime(date('Y-m-d H:i:s',$timestamp));
        } else {
            return $this->formatDate(date('Y-m-d H:i:s',$timestamp));
        }
    }

    /**
    * Format a date from the database making it ready for use in input fields (matches input format)
    * @param string $date_string Date string to format
    * @return string Formatted date
    */
    function formatDateOutput($date_string) {
        if($this->isZero($date_string)) {
            return false;
        }
        return $this->formatDate($date_string,str_replace('-',$this->PMDR->getConfig('date_format_input_seperator'),$this->PMDR->getConfig('date_format_input')));
    }

    /**
    * Format a datetime from the database making it ready for use in input fields (matches input format)
    * @param string $date_string DateTime string to format
    * @return string Formatted datetime
    */
    function formatDateTimeOutput($date_string) {
        if($this->isZero($date_string)) {
            return false;
        }
        $format = str_replace('-',$this->PMDR->getConfig('date_format_input_seperator'),$this->PMDR->getConfig('date_format_input'));
        if($this->PMDR->getConfig('time_format_input') == '24') {
            return $this->formatDate($date_string,$format.' H:i');
        } else {
            return $this->formatDate($date_string,$format.' g:i a');
        }
    }

    /**
    * Format a date from input fields so its ready to go into the DB.
    * @param $string $date_string Date to format
    * @return string Formatted date
    */
    function formatDateInput($date_string) {
        if($this->isZero($date_string)) {
            return '';
        }

        // Split the date by anything non numerical
        $date_string_parts = preg_split('/[^0-9]{1}/',$date_string);

        if(count($date_string_parts) != 3) {
            return '';
        }

        $date_array = array();
        // Match the pieces to the day, month, and year parts
        foreach(explode('-', $this->PMDR->getConfig('date_format_input')) as $key=>$date_part) {
            $date_array[$date_part] = $date_string_parts[$key];
        }

        if(!checkdate($date_array['m'],$date_array['d'],$date_array['Y'])) {
            return '';
        }

        // Return a MySQL date/time compatible string
        return date('Y-m-d',strtotime($date_array['Y'].'-'.$date_array['m'].'-'.$date_array['d']));
    }

    /**
    * Format a datetime from input fields so its ready to go into the DB.
    * @param $string $date_string DateTime to format
    * @return string Formatted datetime
    */
    function formatDateTimeInput($date_string) {
        if($this->isZero($date_string)) {
            return '';
        }

        // Split the date by anything non numerical
        $date_string_parts = preg_split('/[^0-9]{1}/',$date_string,4);

        $date_array = array();
        // Match the pieces to the day, month, and year parts
        foreach(explode('-', $this->PMDR->getConfig('date_format_input')) as $key=>$date_part) {
            $date_array[$date_part] = $date_string_parts[$key];
            if($key == 2) {
                break;
            }
        }

        if(!checkdate($date_array['m'],$date_array['d'],$date_array['Y'])) {
            return '';
        }

        if(!$time = strtotime($date_array['Y'].'-'.$date_array['m'].'-'.$date_array['d'].' '.$date_string_parts[3])) {
            return '';
        }

        // Return a MySQL date/time compatible string
        return date('Y-m-d H:i:s',$time-$this->offset);
    }

    /**
    * Format a timestamp
    * @param int $timestamp
    * @return string Formatted timestamp
    */
    function formatTime($timestamp) {
        if($this->PMDR->getConfig('time_format_input') == '24') {
            $format = 'H:i';
        } else {
            $format = 'g:i a';
        }
        return $this->date($format,$timestamp);
    }

    /**
    * Add a certain number of days/months/years to a date
    * @param string $date
    * @param integer $number
    * @param string $length
    * @return string
    */
    function dateAdd($date, $number, $length = 'day') {
        $length = rtrim($length,'s');
        return date('Y-m-d',strtotime('+'.$number.' '.$length,strtotime($date)));
    }

    /**
    * Subtract a certain number of days/months/years to a date
    * @param string $date
    * @param integer $number
    * @param string $length
    * @return string
    */
    function dateSubtract($date, $number, $length = 'day') {
        $length = rtrim($length,'s');
        return date('Y-m-d',strtotime('-'.$number.' '.$length,strtotime($date)));
    }

    /**
    * Format a timestamp to a specific data format
    * @param string $format Format
    * @param int $time Timestamp
    * @return string Formatted timestamp
    */
    function date($format, $time = null) {
        if(is_null($time)) {
            $time = $this->timestampNow();
        }
        return date($format, $time);
    }

    /**
    * Get the current datetime string
    * @param string $now
    * @return string
    */
    function dateTimeNow($format = 'Y-m-d H:i:s') {
        return date($format,$this->timestampNow());
    }

    /**
    * Get the current date string
    * @param string $now
    * @return string
    */
    function dateNow($format = 'Y-m-d') {
        return $this->dateTimeNow($format);
    }

    /**
    * Get formatted date time for current time
    * @return string
    */
    function formatDateTimeNow() {
        return $this->formatTimeStamp(time(),true);
    }

    /**
    * Get formatted time for current date
    * @return string
    */
    function formatDateNow() {
        return $this->formatTimeStamp(time());
    }

    /**
    * Get the current hour
    * @param mixed $twenty_four_hour_format
    * @return int Hour
    */
    function getHour($twenty_four_hour_format = false) {
        if($twenty_four_hour_format) {
            return date('G',$this->timestampNow());
        } else {
            return date('g',$this->timestampNow());
        }
    }

    /**
    * Get the current minute
    * @return int Minute
    */
    function getMinute() {
        return date('i',$this->timestampNow());
    }

    /**
    * Get an array of times based on a gap and time formatting
    * @param int $gap_minutes
    * @return array
    */
    function getTimeBlocks($gap_minutes = 30) {
        if($this->PMDR->getConfig('time_format_input') == '24') {
            $starting_hour = 0;
        } else {
            $starting_hour = 6;
        }
        $time = $starting_hour*3600;
        $blocks = array();
        do {
            $blocks[] = $this->formatTime($time);
            $time += $gap_minutes*60;

        } while($time%86400 != $starting_hour*3600);
        return $blocks;
    }

    /**
    * Get week days according to locale settings
    * @param boolean $abbreviated Get standard abbreviated names
    * @param boolean $minimum Get 2 character week day names
    * @return array
    */
    function getWeekDays($abbreviated = false, $minimum = false) {
        // Set the time so we start on Sunday because other components (jQuery date picker) use Sunday as the week starting point
        $time = 259200;
        $days = array();
        $format = $abbreviated ? '%a' : '%A';
        do {
            $days[] = $minimum ? Strings::substr(strftime($format,$time),0,2) : strftime($format,$time);
            $time += 86400;
        } while (count($days) < 7);
        return $days;
    }

    /**
    * Get the weekday name
    * @param int $numeric_value 0-6
    */
    function getWeekDayName($numeric_value) {
        $weekdays = array(
            'sunday','monday','tuesday','wednesday','thursday','friday','saturday'
        );
        return $weekdays[$numeric_value];
    }

    /**
    * Get the day name for a numeric value
    * @param int $numeric_value
    * @return string
    */
    function getDayOccurance($numeric_value) {
        $occurances = array(
            'first','second','third','fourth','fifth','sixth'
        );
        return $occurances[($numeric_value-1)];
    }

    /**
    * Get month names according to locale settings
    * @param boolean $abbreviated Get standard abbreviated names
    * @return array
    */
    function getMonths($abbreviated = false) {
        $months = array();

        for($x=1; $x<13; $x++) {
            // Use day 10 to prevent timezone issues
            $months[] = strftime(($abbreviated ? '%b' : '%B'),mktime(0, 0, 0, $x, 10));
        }
        return $months;
    }

    /**
    * Determine if we have an empty date
    * @param string $now
    * @return boolean
    */
    function isZero($date_string) {
        return (trim($date_string) == '' OR $date_string == '0000-00-00' OR $date_string == '0000-00-00 00:00:00' OR in_array(preg_split('/[^0-9]{1}/',$date_string,3),array('0','00','0000')));
    }

    /**
    * Check if a target time appears within a time window
    * @param int $start Starting timestamp
    * @param int $end Ending timestamp
    * @param int $target Timestamp target
    */
    function betweenTime($start, $end, $target) {
        if($start <= $target AND $end >= $target) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Determine if a day and time occurs between a certain time window
    * @param int $day Day of the week 1-7
    * @param string $start Starting time ex: 08:00
    * @param mixed $end Ending time ex: 15:00
    */
    function isOpen($day, $start, $end) {
        if($end == '0:00') {
            $end == '23:59';
        }
        if($day == strftime('%w',$this->timestampNow())) {
            return $this->betweenTime(strtotime($start),strtotime($end),$this->timestampNow());
        }
        return false;
    }
}

/**
* Dates Local class
* Used to get dates in local time according to time zone
*/
class Dates_Local extends Dates {
    /**
    * Get the timestamp based on timezone offset
    * @return int
    */
    function timestampNow() {
        return time()+$this->offset;
    }

    /**
    * Adjust the timestamp for the current timezone offset
    * @param int $timestamp Timestamp to adjust
    * @return int
    */
    function adjustOffset($timestamp) {
        return $timestamp+$this->offset;
    }
}
?>