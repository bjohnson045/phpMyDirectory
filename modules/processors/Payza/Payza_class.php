<?php
class Payza extends PaymentAPIHandler {
    var $gateway_name = 'Payza';
    var $test_url = 'https://secure.payza.com/checkout';
    var $url = 'https://secure.payza.com/checkout';

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
        $this->parameters = array(
            'ap_amount'=>$data['subtotal'],
            'ap_taxamount'=>$data['tax'],
            'ap_currency'=>$this->settings['alert_pay_currency'],
            'ap_itemcode'=>$data['invoice_id'],
            'ap_merchant'=>$this->settings['alert_pay_email'],
            'ap_itemname'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'ap_quantity'=>1,
            'ap_returnurl'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'ap_cancelurl'=>BASE_URL.MEMBERS_FOLDER.'user_invoices.php',
            'ap_notifyurl'=>BASE_URL.'/modules/processors/AlertPay/AlertPay_ipn.php'
        );
        if($this->settings['alert_pay_subscriptions']) {
            if($data['days_until_due'] > 0) {
                $this->parameters['ap_trialtimeunit'] = 'Day';
                $this->parameters['ap_trialperiodlength'] = $data['days_until_due'];
                $this->parameters['ap_trialamount'] = $data['balance'];
            }
            $this->parameters['ap_purchasetype'] = 'subscription';
            $this->parameters['ap_amount'] = $data['amount_recurring'];
            $this->parameters['ap_timeunit'] = rtrim($data['period'],'s');
            $this->parameters['ap_periodlength'] = $data['period_count'];
            $this->parameters['ap_nextrundate'] = date('Y-m-d',strtotime('+'.$data['period_count'].' '.$data['period'],strtotime($data['date'])));
        } else {
            $this->parameters['ap_purchasetype'] = 'service';
            $this->parameters['ap_totalamount'] = $data['balance'];
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
        return $this->response['ap_itemcode'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if($this->settings['alert_pay_securitycode'] != $_POST['ap_securitycode']) {
            $this->errors[] = 'Security code invalid';
            return false;
        }

        if($this->response['ap_test'] == '1') {
            // test mode
        }

        if($this->response['ap_purchasetype'] == 'subscription') {
            // subscription
        }

        if(strlen($this->response['ap_referencenumber']) == 0 AND $this->response['ap_trialamount'] != "0") {
            $this->errors[] = 'Reference number invalid and trial amount not zero';
            return false;
        }
        if(!$this->loadStoredParameters($this->response['ap_itemcode'])) {
            $this->errors[] = 'Invalid invoice ID (item code)';
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
            switch($this->response['ap_status']) {
                case 'Success':
                    $this->result = 'success';
                    $this->result_amount = $this->response['ap_totalamount'];
                    break;
                case 'Subscription-Payment-Success':
                    $this->result = 'success';
                    $this->result_amount = $this->response['ap_totalamount'];
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
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }
}

class Payza_Notification extends Payza {
    /**
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['ap_status']) {
                case 'Success':
                    if($this->response['ap_purchasetype'] == 'Subscription') {
                        $this->setupSubscription($this->response['ap_itemcode'],$this->response['ap_subscriptionreferencenumber']);
                    }
                    parent::processPayment($this->response['ap_itemcode'],$this->response['ap_referencenumber'],$this->response['ap_totalamount']);
                    break;
                case 'Subscription-Payment-Success':
                    if($invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_INVOICES." i WHERE i.order_id=? AND i.status='unpaid' ORDER BY i.date_due ASC",array($this->response['ap_itemcode']))) {
                        parent::processPayment($invoice_id,$this->response['ap_referencenumber'],$this->response['ap_totalamount']);
                    } else {
                        // give credit
                    }
                case 'Subscription-Payment-Canceled':
                    $this->cancelSubscription($this->response['ap_itemcode']);
                default:
                    return false;
            }
        }
        return $this->response['ap_itemcode'];
    }
}
?>