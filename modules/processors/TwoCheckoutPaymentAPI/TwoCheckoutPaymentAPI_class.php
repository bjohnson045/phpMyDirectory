<?php
class TwoCheckoutPaymentAPI extends PaymentAPIHandler {
    var $gateway_name = 'TwoCheckoutPaymentAPI';
    var $test_url = '';
    var $url = '';
    var $on_site_payment = true;

    function __construct($PMDR) {
        parent::__construct($PMDR);
        if($this->test_mode) {
            $host = 'sandbox.2checkout.com';
        } else {
            $host = 'www.2checkout.com';
        }
        require_once(PMDROOT.'/modules/processors/TwoCheckoutPaymentAPI/library/Twocheckout.php');
        $PMDR->loadJavascript('<script type="text/javascript" src="https://'.$host.'/checkout/api/script/publickey/'.$this->settings['2checkout_account_id'].'"></script>',50);
        $PMDR->loadJavascript('<script type="text/javascript"src="https://'.$host.'/checkout/api/2co.min.js"/></script>',55);
    }

    function getCreditCardForm($data) {
        $form = parent::getCreditCardForm($data);
        $form->addField('sellerId','hidden',array('fieldset'=>'hidden','value'=>$this->settings['2checkout_account_id']));
        $form->addField('publishableKey','hidden',array('fieldset'=>'hidden','value'=>$this->settings['2checkout_public_key']));
        $form->addField('token','hidden',array('fieldset'=>'hidden','value'=>''));
        $form->setFieldAttribute('submit_form','onclick','retrieveToken()');
        return $form;
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array(
            'sellerId'=>$this->settings['2checkout_account_id'],
            'merchantOrderId'=>$data['invoice_id'],
            'token'=>$data['token'],
            'currency'=>'USD',
            'total'=>$data['balance'],
            'billingAddr'=>array(
                'name'=>$data['user_first_name'].' '.$data['user_last_name'],
                'addrLine1'=>$data['user_address1'],
                'city'=>$data['user_city'],
                'state'=>$data['user_state'],
                'zipCode'=>$data['user_zip'],
                'country'=>$data['user_country'],
                'email'=>$data['user_email'],
                'phoneNumber'=>$data['user_phone']
            )
        );
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
        return $this->response['response']['merchantOrderId'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['response']['merchantOrderId'])) {
            $this->errors[] = 'Invalid invoice number (merchantOrderId)';
            return false;
        }
        if($this->parameters['currency'] != $this->response['response']['currencyCode']) {
            $this->errors[] = 'Invalid currency code: '.$this->parameters['currency'].' != '.$this->response['response']['currencyCode'];
            return false;
        }
        return true;
    }

    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['response']['responseCode']) {
                case 'APPROVED':
                    $this->result = 'success';
                    $this->result_amount = $this->response['response']['total'];
                    break;
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
        $mode = '';
        if($this->test_mode) {
            $mode = 'sandbox';
        }
        try {
            Twocheckout::setApiCredentials($this->settings['2checkout_account_id'], $this->settings['2checkout_private_key'], $mode);
            $this->response = Twocheckout_Charge::auth($this->parameters);
        } catch (Twocheckout_Error $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }
}

class TwoCheckoutPaymentAPI_Notification extends TwoCheckoutPaymentAPI {
    function __construct($PMDR) {
        parent::__construct($PMDR);
    }

    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['message_type']) {
                case 'ORDER_CREATED':
                    if($this->response['invoice_status'] == 'approved') {
                        if($this->response['recurring'] == '1') {
                            if($invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_INVOICES." i WHERE i.order_id=? AND i.status='unpaid' ORDER BY i.date_due ASC",array($this->response['vendor_order_id']))) {
                                parent::processPayment($invoice_id,$this->response['sale_id'],$this->response['invoice_list_amount']);
                            } else {
                                $this->errors[] = 'No invoice found for order: '.$this->response['vendor_order_id'];
                            }
                            $this->setupSubscription($this->response['vendor_order_id'],$this->response['sale_id']);
                        } else {
                            parent::processPayment($this->response['vendor_order_id'],$this->response['sale_id'],$this->response['invoice_list_amount']);
                        }
                    }
                    break;
                case 'INVOICE_STATUS_CHANGED':
                    break;
                case 'RECURRING_INSTALLMENT_SUCCESS':
                    parent::processPayment($this->response['vendor_order_id'],$this->response['sale_id'],$this->response['item_list_amount_1']);
                    break;
                case 'RECURRING_STOPPED':
                    $this->cancelSubscription($this->response['sale_id']);
                    break;
                case 'FRAUD_STATUS_CHANGED':
                    if($this->response['fraud_status'] == 'fail') {
                        parent::processRefund($this->response['sale_id']);
                    }
                    break;
                case 'REFUND_ISSUED':
                    parent::processRefund($this->response['sale_id']);
                    break;
                default:
                    return false;
                    break;
            }
        }
        return $this->response['vendor_order_id'];
    }

    function loadResult() {
        if($this->verifyResponse()) {
            if($this->response['invoice_status'] == 'approved') {
                $this->result = 'success';
                $this->result_amount = $this->response['invoice_list_amount'];
            } elseif ($this->response['invoice_status'] == 'pending') {
                $this->result = 'pending';
            } elseif($this->response['invoice_status'] == 'declined') {
                $this->result = 'failed';
            } else {
                $this->result = 'failed';
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    function verifyResponse() {
        if($this->settings['testmode']) {
            $key = strtoupper(md5('1'.$this->settings['2co_id'].$this->settings['invoice_id'].$this->settings['2co_word']));
        } else {
            $key = strtoupper(md5($this->response['sale_id'].$this->settings['2co_id'].$this->response['invoice_id'].$this->settings['2co_word']));
        }

        if ($key == $this->response['md5_hash']) {
            return true;
        } else {
            $this->errors[] = 'Invalid hash';
            return false;
        }
    }

    function loadResponse() {
        if(!count($_POST)) {
            $this->errors = 'POST empty';
        }
        $this->response = $_POST;
    }
}
?>