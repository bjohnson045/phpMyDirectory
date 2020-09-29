<?php
class iDeal extends PaymentAPIHandler {
    var $gateway_name = 'iDeal';
    var $test_url = 'https://secure.mollie.nl/xml/ideal';
    var $url = 'https://secure.mollie.nl/xml/ideal';

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
            'a'=>'fetch',
            'partnerid'=>$this->settings['ideal_partner_id'],
            'returnurl'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php?invoice_id='.$data['invoice_id'],
            'reporturl'=>BASE_URL.'/modules/processors/iDeal/iDeal_ipn.php?invoice_id='.$data['invoice_id'],
            'description'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'amount'=>number_format($data['balance'],2,'','')
        );
        if(!empty($this->settings['ideal_profile_key'])) {
            $this->parameters['profile_key'] = $this->settings['ideal_profile_key'];
        }
        return true;
    }

    /*
    function getPaymentForm($data = array()) {
        $this->payment_template = $this->PMDR->getNew('Template',PMDROOT.'/modules/processors/'.$this->gateway_name.'/payment_form.tpl');
        $this->payment_template->set('form',$this->getPaymentButton());
        return $this->payment_template;
    }
    */
    function getPaymentButton() {
        if(isset($_POST['bank_id'])) {
            $this->parameters['bank_id'] = str_pad($_POST['bank_id'],4,'0',STR_PAD_LEFT);
            $url = 'https://secure.mollie.nl/xml/ideal?';
            $url .= http_build_query($this->parameters);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            $xml = new SimpleXMLElement($response);
            redirect_url($xml->order->URL);
        }

        $ch = curl_init();
        $url = 'https://secure.mollie.nl/xml/ideal?a=banklist';
        if($this->settings['testmode']) {
            $url .= '&testmode=true';
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $bank_list = curl_exec($ch);
        curl_close($ch);

        $options = array(''=>'Select a Bank');
        $xml = new SimpleXMLElement($bank_list);

        foreach($xml->bank AS $bank) {
            $options[(int)$bank->bank_id] = (string)$bank->bank_name;
        }

        $form = $this->PMDR->getNew('Form');
        $form->setName('payment_button');
        $form->action = BASE_URL.MEMBERS_FOLDER.'user_invoices_pay_summary.php';
        $form->addField('bank_id','select',array('label'=>'Bank','options'=>$options));
        $form->addFieldSet('hidden');
        foreach($this->parameters as $name=>$value) {
            $form->addField($name,'hidden',array('label'=>$name,'fieldset'=>'hidden','value'=>$value));
        }
        $form->addField('submit','submit',array('label'=>$this->PMDR->getLanguage('user_general_pay_now'),'fieldset'=>'submit'));
        return $form;
    }

    /**
    * Process return
    * We process the return to get result
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['invoice_id'];
    }

    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['invoice_id'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }
        return true;
    }

    function loadResult() {
        if($this->verifyResponse()) {
            if(isset($this->response['transaction_id'])) {
                $this->result = 'success';
                $this->result_amount = $this->parameters['amount'];
            } else {
                $this->result = 'failed';
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    function loadResponse() {
        if(!count($_GET)) {
            $this->errors[] = 'GET empty';
        }
        $this->response = $_GET;
    }
}

class iDeal_Notification extends iDeal {
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['payed']) {
                case 'true':
                    parent::processPayment($this->response['invoice_id'],$this->response['transaction_id'],$this->response['amount']);
                    break;
                default:
                    return false;
            }
        }
        return $this->response['invoice_id'];
    }

    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['payed']) {
                case 'true':
                    $this->result = 'success';
                    $this->result_amount = $this->response['amount'];
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

    function loadResponse() {
        if(!count($_GET)) {
            $this->errors[] = 'GET empty';
        }
        $this->response = $_GET;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://secure.mollie.nl/xml/ideal?a=check&partnerid='.urlencode($this->settings['ideal_partner_id']).'&transaction_id='.urlencode($_GET['transaction_id']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        if(empty($response)) {
            return false;
        }
        if(!$xml = new SimpleXMLElement($response)) {
            return false;
        }

        $this->response['amount'] = number_format($xml->order->amount,2,'.');
        $this->response['payed'] = $xml->order->payed;
        $this->response['message'] = $xml->order->message;
        $this->response['currency'] = $xml->order->currency;
        $this->response['transaction_id'] = $xml->order->transaction_id;

        return true;
    }
}
?>