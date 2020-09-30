<?php
class FirstDataGlobal extends PaymentAPIHandler {
    // Test Card: 4111111111111111 Visa
    var $gateway_name = 'FirstDataGlobal';
    var $test_url = 'https://www.staging.linkpointcentral.com/lpc/servlet/lppay';
    var $url = 'https://www.linkpointcentral.com/lpc/servlet/lppay';

    function __construct($PMDR) {
        parent::__construct($PMDR);
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        include(PMDROOT.'/includes/country_codes.php');
        include(PMDROOT.'/includes/state_codes.php');
        $country_codes_inverse = array_flip($country_codes);

        $this->parameters = array(
            'mode'=>'PayOnly',
            'chargetotal'=>$data['balance'],
            'storename'=>$this->settings['store_id'],
            'bname'=>$data['user_first_name'].' '.$user['user_last_name'],
            'baddr1'=>$data['user_address1'],
            'baddr2'=>$data['user_address2'],
            'bcity'=>$data['user_city'],
            'bcountry'=>$country_codes_inverse[$data['user_country']],
            'phone'=>$data['user_phone'],
            'fax'=>$data['user_fax'],
            'email'=>$data['user_email'],
            'bzip'=>$data['user_zip'],
            'txntype'=>'sale',
            'responseSuccessURL'=>'',
            'responseFailURL'=>'',
            'responseURL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'invoice_number'=>$data['invoice_id'],
            'hash'=>md5(md5(LICENSE).$this->settings['store_id'].$data['invoice_id'].$data['balance']),
            'comments'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id']
        );

        if(in_array(strtoupper($data['user_state']),$state_codes)) {
            $this->parameters['bstate'] = $state_codes_inverse[$data['user_state']];
        }

        if($this->settings['test_mode']) {
            $this->parameters['debug'] = 'true';
        }

        if($this->settings['recurring']) {
            $this->parameters['submode'] = 'periodic';
            $this->parameters['periodicity'] = 'd30'; // m d or y, d can only be 1 to 99
            $this->parameters['startdate'] = date('Ymd');
            $this->parameters['installments'] = '99';
            $this->parameters['threshold'] = 3;
        }
        return true;
    }

    /**
    * Process return
    * We process the return to get result
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result = 'success') {
            parent::processPayment($this->response['invoice_number'],$this->response['oid'],$this->response['chargetotal']);
        }
        return $this->response['r_ordernum'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['status']) {
                case 'APPROVED':
                    $this->result = 'success';
                    $this->result_amount = $this->response['chargetotal'];
                    break;
                case 'DECLINED':
                    $this->result = 'declined';
                    break;
                case 'BLOCKED':
                default:
                    $this->result = 'failed';
                    break;
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if($this->response['hash'] != md5(md5(LICENSE).$this->settings['store_id'].$this->response['invoice_number'].$this->response['chargetotal'])) {
            return false;
        }
        if(!$this->loadStoredParameters($this->response['invoice_number'])) {
            return false;
        }
        return true;
    }

    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
    */
    function loadResponse() {
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        return $this->response = $_POST;
    }
}
?>