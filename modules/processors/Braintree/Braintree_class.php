<?php
class Braintree extends PaymentAPIHandler {
    var $gateway_name = 'Braintree';
    var $gateway;

    function __construct($PMDR) {
        parent::__construct($PMDR);

        require_once(PMDROOT.'/modules/processors/Braintree/lib/autoload.php');

        if($this->test_mode) {
            $environment = 'sandbox';
        } else {
            $environment = 'production';
        }

        $this->gateway = new Braintree\Gateway([
            'environment' => $environment,
            'merchantId' => $this->settings['braintree_merchant_id'],
            'publicKey' => $this->settings['braintree_public_key'],
            'privateKey' => $this->settings['braintree_private_key']
        ]);
        
        $PMDR->loadJavascript('<script type="text/javascript"src="https://js.braintreegateway.com/web/dropin/1.9.4/js/dropin.min.js"/></script>',55);      
    }

    /**
    * Get the Braintree payment form since it is custom
    * @param array $data
    * @return Template object
    */
    function getPaymentForm($data = array()) {
        $this->payment_template = $this->PMDR->getNew('Template',PMDROOT.'/modules/processors/'.$this->gateway_name.'/payment_form.tpl');
        $this->payment_template->set('gateway',$this->gateway);
        foreach($data AS $key=>$value) {
             $this->payment_template->set($key,$value);    
        }
        return $this->payment_template;
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array(
            'return'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',     // returned here after payment
            'invoice_id'=>$data['invoice_id'],   // used to store the invoice ID we are paying
            'item_name'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],    // set item name by company name and invoice ID
            'amount'=>$data['balance'],       // amount of payment -- We need to change this to use 'balance' instead.
            'address1'=>$data['user_address1'],      // address of payer
            'address2'=>$data['user_address2'],      // address of payer line 2
            'city'=>$data['user_city'],          // city of payer
            'email'=>$data['user_email'],         // email of payer
            'first_name'=>$data['user_first_name'],    // first name of payer
            'last_name'=>$data['user_last_name'],     // last name of payer
            'zip'=>$data['user_zip'],          // zip of payer
        );
        if(trim($this->settings['braintree_plan_id_'.$data['pricing_id']]) != '') {
            $this->parameters['plan_id'] = $this->settings['braintree_plan_id_'.$data['pricing_id']];
            $this->parameters['order_id'] = $data['order_id'];
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
        if($this->result == 'success') {
            parent::processPayment($this->response['invoice_id'],$this->response['transaction_id'],$this->response['amount']);
        }
        return $this->response['invoice_id'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(isset($this->settings['braintree_plan_id_'.$this->response["pricing_id"]])) {
            $result = $this->gateway->customer()->create([
                'firstName' => $this->response['first_name'],
                'lastName' => $this->response['last_name'],
                'paymentMethodNonce' => $this->response["payment_method_nonce"]
            ]);
            $this->response['subscription_customer_create'] = $result;
            if ($result->success) {
                $result = $this->gateway->subscription()->create([
                    'paymentMethodToken' => $result->customer->paymentMethods[0]->token,
                    'planId' => $this->settings['braintree_plan_id_'.$this->response["pricing_id"]],
                    'options' => ['startImmediately' => true],
                    'price' => $this->response["amount"],
                ]);
                if($result->success) {
                    $this->response['transaction_id'] = $result->subscription->transactions[0]->id;
                    $this->setupSubscription($this->response['order_id'],$result->subscription->id);
                } else {
                    foreach($result->errors->deepAll() AS $error) {
                        $this->errors[] = $error->message;
                    }
                }
            } else {
                foreach($result->errors->deepAll() AS $error) {
                    $this->errors[] = $error->message;
                }
                return false;
            }
        } else {
            $result = $this->gateway->transaction()->sale([
                'amount' => $this->response["amount"],
                'paymentMethodNonce' => $this->response["payment_method_nonce"],
                'options' => [
                    'submitForSettlement' => true
                ]
            ]);
            if($result->success OR !is_null($result->transaction)) {
                $this->response['transaction_id'] = $result->transaction->id;
            } else {
                foreach($result->errors->deepAll() as $error) {
                    $this->errors[] = $error->message;
                }
                return false;
            }
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
            $transaction = $this->gateway->transaction()->find($this->response['transaction_id']);
            $this->response['transaction'] = $transaction;
            $transactionSuccessStatuses = [
                Braintree\Transaction::AUTHORIZED,
                Braintree\Transaction::AUTHORIZING,
                Braintree\Transaction::SETTLED,
                Braintree\Transaction::SETTLING,
                Braintree\Transaction::SETTLEMENT_CONFIRMED,
                Braintree\Transaction::SETTLEMENT_PENDING,
                Braintree\Transaction::SUBMITTED_FOR_SETTLEMENT
            ];
            if(in_array($transaction->status, $transactionSuccessStatuses)) {
                $this->result = 'success';
                $this->result_amount = $transaction->amount;
            } else {
                // $transaction->status
                $this->result = 'failed';
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
        $this->response = $_POST;
    }
    
    /**
    * Process braintree webhooks
    */
    function processNotification() {
        $notification = $this->gateway->webhookNotification()->parse($_POST['bt_signature'],$_POST['bt_payload']);
        $this->response['notification'] = $notification;   
        if($notification->kind == 'subscription_charged_successfully') {
            if($order_id = $this->getSubscriptionOrderID($notification->subscription->id)) {
                $subscription = $gateway->subscription()->find($notification->subscription->id);
                $subTransactionIds = $subscription->transactions;
                $this->processSubscriptionPayment($order_id,$notification->subscriptions->id,$transactionIds[0]->id);
            }
        }
    }
}
?>