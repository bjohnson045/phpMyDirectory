<?php
/**
* PayflowPro payment gateway class
*/
class PayflowPro extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'PayflowPro';
    /**
    * Test URL
    * @var string
    */
    var $test_url = 'https://pilot-payflowpro.paypal.com';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://payflowpro.paypal.com';
    /**
    * On site payment
    * Displays credit card form and handles on site processing
    * @var boolean
    */
    var $on_site_payment = true;

    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return PayflowPro
    */
    function __construct($PMDR) {
        parent::__construct($PMDR);
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return boolean
    */
    function loadParameters($data) {
        $this->parameters = array(
            'VERBOSITY'=>'MEDIUM',
            'USER'=>$this->settings['payflowpro_id'],
            'VENDOR'=>$this->settings['payflowpro_vendor'],
            'PARTNER'=>$this->settings['payflowpro_partner'],
            'PWD'=>$this->settings['payflowpro_password'],
            'TRXTYPE'=>'S',
            'TENDER'=>'C',
            'ACCT'=>$data['cc_number'],
            'CVV2'=>$data['cc_cvv2'],
            'AMT'=>$data['balance'],
            'EXPDATE'=>$data['cc_expire_month'].$data['cc_expire_year'],
            'FIRSTNAME'=>$data['user_first_name'],
            'LASTNAME'=>$data['user_last_name'],
            'STREET'=>$data['user_address1'],
            'CITY'=>$data['user_city'],
            'STATE'=>$data['user_state'],
            'ZIP'=>$data['user_zip'],
            'COUNTRY'=>$data['user_country'],
            'CUSTIP'=>get_ip_address(),
            'INVNUM'=>$data['invoice_id'],
            'EMAIL'=>$data['user_email'],
            'TAXAMT'=>'',
            'COMMENT1'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'PHONENUM'=>$data['user_phone'],
            'CURRENCY'=>$this->settings['payflowpro_currency'],
            'BUTTONSOURCE'=>'AccomplishTechnology_SP',
        );

        return true;
    }

    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->parameters['INVNUM'],$this->response['PNREF'],$this->parameters['AMT']);
        }
        return $this->parameters['INVNUM'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if($this->response['RESULT'] == '0') {
                $this->result = 'success';
                $this->result_amount = $this->response['AMT'];
            } else {
                $this->result = 'failed';
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    /**
    * Verify Response
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
        return true;
    }

    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
    */
    function loadResponse() {
        $request_id = md5($this->parameters['ACCT'].$this->parameters['AMT'].date('YmdGis').'1');

        $headers[] = "Content-Type: text/namevalue"; //or maybe text/xml
        $headers[] = "X-VPS-Timeout: 30";
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $parameters = '';
        foreach($this->parameters as $name=>$value) {
            $parameters .= $name.'['.strlen($value).']='.$value.'&';
        }
        $parameters = rtrim($parameters,'&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
        $response = curl_exec($ch);
        if(!$response) {
            $this->errors[] = curl_error($ch).' ('.curl_errno($ch).')';
        } else {
            $parts = explode('&',$response);
            foreach($parts as $part) {
                $value = explode('=',$part);
                $this->response[$value[0]] = urldecode($value[1]);
            }
        }
        curl_close($ch);
        return $this->response;
    }
}
?>