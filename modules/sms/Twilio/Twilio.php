<?php
include(PMDROOT.'/modules/sms/Twilio/library/autoload.php');
use Twilio\Rest\Client;

/**
* Twilio SMS/Call Class
*/
class Twilio extends SMS {
    /**
    * Twilio API object
    * @var Services_Twilio
    */
    var $client = null;

    /**
    * Load the Twilio PHP API and initialize the object
    */
    function initialize() {
        return $this->connect();
    }

    /**
    * Connect to the API by loading the API authentication details
    */
    function connect() {
        if(!empty($this->settings['twilio_sid']) AND !empty($this->settings['twilio_token'])) {
            $this->client = new Client($this->settings['twilio_sid'], $this->settings['twilio_token']);
        } else {
            throw new Exception('SMS gateway credentials empty.',E_USER_WARNING);
        }
    }

    /**
    * Send a SMS message
    * @param mixed $numbers Numbers to message
    * @param mixed $message Message to send
    * @param mixed $from Twilio account number
    */
    function sendMessage($numbers, $message, $from = null) {
        if(is_null($from)) {
            $from = $this->settings['twilio_number'];
        }
        if(!is_array($numbers)) {
            $numbers = array_filter(array($numbers));
        }
        if(count($numbers)) {
            $success = true;
            foreach($numbers AS $number) {
                $number = $this->normalize_E164($number);
                $response = $this->client->account->messages->create($number, array('from'=>$from,'body'=>$message));
                $response = json_decode($response,true);
                if($response['status'] != 200) {
                    trigger_error('SMS: '.$response['message'],E_USER_WARNING);
                    $success = false;
                }
            }
            return $error;
        } else {
            trigger_error('SMS: No numbers to send to.',E_USER_WARNING);
            return false;
        }
    }

    /**
    * Connect two numbers
    * @param mixed $number
    * @param mixed $number2
    * @param mixed $from Twilio account number
    */
    function connectCall($number, $number2, $from = null) {
        if(is_null($from)) {
            $from = $this->settings['twilio_number'];
        }
        $number = $this->normalize_E164($number);
        $callback_parameters = array(
            'number_spaced'=>$this->spaceNumber($this->normalize_E164($number2)),
            'number_formatted'=>$this->normalize_E164($number2)
        );
        $callback = BASE_URL.'/modules/sms/Twilio/Twilio_callback.php?'.http_build_query($callback_parameters);
        try {
            $call = $this->client->account->calls->create($number, $from, array('url'=>$callback));
            return $call->sid;
        } catch (Exception $e) {
            trigger_error('SMS: '.$e->getMessage(),E_USER_WARNING);
            return false;
        }
    }
}
?>