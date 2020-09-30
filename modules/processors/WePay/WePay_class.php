<?php
class WePay extends PaymentAPIHandler {
    var $gateway_name = 'WePay';
    var $test_url = '';
    var $url = '';
    var $wepay = null;

    function __construct($PMDR) {
        parent::__construct($PMDR);

        require 'wepay.php';
        if($this->test_mode) {
            Wepay_Library::useStaging($this->settings['wepay_appid'], $this->settings['wepay_secret']);
        } else {
            WePay_Library::useProduction($this->settings['wepay_appid'], $this->settings['wepay_secret']);
        }

        $this->wepay = new WePay_Library($this->settings['wepay_token']);
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        include(PMDROOT.'/includes/country_codes.php');
        $country_codes_inverse = array_flip($country_codes);

        $response = $this->wepay->request('checkout/create', array(
            'account_id'        => $this->settings['wepay_account_id'],
            'amount'            => $data['balance'],
            'short_description' => $this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'type'              => 'SERVICE',
            'redirect_uri'      => BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'callback_uri'      => BASE_URL.'/modules/processors/WePay/WePay_ipn.php',
            'reference_id'      => $data['invoice_id'],
            'fee_payer'         => 'payee',
            'prefill_info'      => array(
                'name'=>$data['user_first_name'].' '.$data['user_last_name'],
                'email'=>$data['user_email'],
                'phone_number'=>$data['user_phone'],
                'address'=>$data['user_address1'],
                'city'=>$data['user_city'],
                'state'=>$data['user_city'],
                'zip'=>$data['user_zip'],
                'country'=>$country_codes_inverse[$data['user_country']]
            )
        ));

        $this->url = $this->test_url = $response->checkout_uri;

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
        return $this->response['reference_id'];
    }

    /**
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function processNotification() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['state']) {
                case 'captured':
                    parent::processPayment($this->response['reference_id'],$this->response['checkout_id'],$this->response['amount']);
                    break;
                case 'refunded':
                case 'charged_back':
                case 'cancelled':
                    parent::processRefund($this->response['checkout_id']);
                    break;
                default:
                    return false;
            }
        }
        return $this->response['custom'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['reference_id'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }
        if($this->response['account_id'] != $this->settings['wepay_account_id']) {
            $this->errors[] = 'Invalid account id: '.$this->response['account_id'].' != '.$this->settings['wepay_account_id'];
            return false;
        }
        return true;
    }

    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['state']) {
                case 'captured':
                    $this->result = 'success';
                    $this->result_amount = $this->response['amount'];
                    break;
                case 'failed':
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
    * Load response
    * Load the response into class variable for processing
    * @return void
    */
    function loadResponse() {
        if(isset($_GET['checkout_id'])) {
            if(!count($_GET)) {
                $this->errors[] = 'GET empty';
            }

            $response = $this->wepay->request('checkout', array(
                'checkout_id'=>intval($_GET['checkout_id']),
            ));

            $this->response = get_object_vars($response);
        }
    }
}
?>