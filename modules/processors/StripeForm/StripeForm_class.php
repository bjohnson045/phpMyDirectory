<?php
class StripeForm extends PaymentAPIHandler {
    var $gateway_name = 'StripeForm';
    var $test_url = '';
    var $url = '';
    var $on_site_payment = true;

    function __construct($PMDR) {
        parent::__construct($PMDR);
        require_once(PMDROOT.'/modules/processors/StripeForm/lib/Stripe.php');
        $PMDR->loadJavascript('<script type="text/javascript" src="https://js.stripe.com/v1/"></script>',50);
        $PMDR->loadJavascript('<script type="text/javascript">Stripe.setPublishableKey(\''.$this->settings['stripe_publishable_key'].'\');</script>',55);
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array(
            'amount'=>(floatval($data['balance'])*100),
            'currency'=>$this->settings['stripe_currency'],
            'card'=>$data['stripeToken'],
            'description'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'metadata'=>array('invoice_id'=>$data['invoice_id']),
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
        if($this->result == 'success') {
            parent::processPayment($this->response['invoice_id'],$this->response['id'],$this->response['amount']);
        }
        return $this->response['invoice_id'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['invoice_id'])) {
            $this->errors[] = 'Invalid invoice number (invoice_id)';
            return false;
        }
        if(strtoupper($this->parameters['currency']) != strtoupper($this->response['currency'])) {
            $this->errors[] = 'Invalid currency code: '.$this->parameters['currency'].' != '.$this->response['currency'];
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
            switch($this->response['paid']) {
                case 1:
                    $this->result = 'success';
                    $this->result_amount = $this->response['amount'];
                    break;
                case 0:
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
            return false;
        }
        Stripe::setApiKey($this->settings['stripe_secret_key']);
        try {
            $charge = Stripe_Charge::create($this->parameters);
            $this->response = array(
                'id'=>$charge->id,
                'paid'=>$charge->paid,
                'amount'=>($charge->amount / 100),
                'currency'=>$charge->currency,
                'captured'=>$charge->captured,
                'failure_message'=>$charge->failure_message,
                'failure_code'=>$charge->failure_code,
                'invoice_id'=>$charge->metadata->invoice_id
            );
        } catch(Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }
}

class StripeForm_Notification extends StripeForm {
    function loadResponse() {
        Stripe::setApiKey($this->settings['stripe_secret_key']);
        $body = @file_get_contents('php://input');
        $event_json = json_decode($body);
        $event = Stripe_Event::retrieve($event_json['id']);
    }
}
?>