<?php
require_once('lib/OAuth.php');

/**
* Yelp API Class
*/
class Yelp {
    /**
    * Registry
    * @var object
    */
    var $PMDR;

    /**
    * Yelp Constructor
    * @param object $PMDR
    * @return Yelp
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        if($this->PMDR->getConfig('yelp_id') == '') {
            return false;
        }
    }

    /**
     * Makes a request to the Yelp API and returns the response
     *
     * @param    $host    The domain host of the API
     * @param    $path    The path of the APi after the domain
     * @return   The JSON response from the request
     */
    function request($host, $path) {
        $unsigned_url = "https://" . $host . $path;
        // Token object built using the OAuth library
        $token = new OAuthToken($this->PMDR->getConfig('yelp_token'), $this->PMDR->getConfig('yelp_token_secret'));
        // Consumer object built using the OAuth library
        $consumer = new OAuthConsumer($this->PMDR->getConfig('yelp_consumer_key'), $this->PMDR->getConfig('yelp_consumer_secret'));
        // Yelp uses HMAC SHA1 encoding
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $oauthrequest = OAuthRequest::from_consumer_and_token(
            $consumer,
            $token,
            'GET',
            $unsigned_url
        );

        // Sign the request
        $oauthrequest->sign_request($signature_method, $consumer, $token);

        // Get the signed URL
        $signed_url = $oauthrequest->to_url();

        if(!$ch = curl_init($signed_url)) {
            return false;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
    * Get reviews based on a phone number
    * @param mixed $phone
    */
    function getReviewsByPhone($phone) {
        if(empty($phone)) {
            return false;
        }
        $result = $this->request('api.yelp.com','/v2/phone_search?phone='.urlencode($phone));
        if(!$result) {
            trigger_error('Yelp CURL command failed: '.$url,E_USER_WARNING);
            return false;
        }
        if(!$result = json_decode($result,true)) {
            trigger_error('Yelp response can not be decoded: '.$url,E_USER_WARNING);
            return false;
        }
        if($result['total'] == 0) {
            return fasle;
        }

        $result = $this->request('api.yelp.com','/v2/business/'.$result['businesses'][0]['id']);
        if(!$result) {
            trigger_error('Yelp CURL command failed: '.$url,E_USER_WARNING);
            return false;
        }
        if(!$result = json_decode($result,true)) {
            trigger_error('Yelp response can not be decoded: '.$url,E_USER_WARNING);
            return false;
        }
        if($result['review_count'] == 0) {
            return false;
        }
        if(is_array($result['reviews'])) {
            foreach($result['reviews'] AS &$review_details) {
                $review_details['date'] = $this->PMDR->get('Dates_Local')->formatTimeStamp($review_details['time_created']);
            }
        }
        return $result;
    }
}
?>