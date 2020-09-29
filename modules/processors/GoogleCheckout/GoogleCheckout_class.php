<?php
class GoogleCheckout extends PaymentAPIHandler {
    var $gateway_name = 'GoogleCheckout';
    var $test_url = 'https://sandbox.google.com/checkout/api/checkout/v2/checkout/Merchant/';
    var $url = 'https://checkout.google.com/api/checkout/v2/checkout/Merchant/';
    var $submit_button;
    
    function __construct($PMDR) {
        parent::__construct($PMDR);
        $this->test_url .= $this->settings['googlecheckout_merchant_id'];
        $this->url .= $this->settings['googlecheckout_merchant_id'];
        $this->submit_button = 'http://sandbox.google.com/checkout/buttons/checkout.gif?merchant_id='.$this->settings['googlecheckout_merchant_id'].'&w=180&h=46&style=white&variant=text&loc=en_US'; 
    }
    
    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array(
            'item-name'=>'Invoice #'.$data['invoice_id'],
            'item-description'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'price'=>$data['balance'],              
            'currency'=>$this->settings['googlecheckout_currency'],
            'quantity'=>'1',
            'delivery-type'=>'OPTIMISTIC',
            'delivery-description'=>'Thank you for your order.  It may take up to 24 hours to process your order.  Once your order has been processed your invoice will show as paid and your listing will be active.  &amp;lt;a href="'.BASE_URL.MEMBERS_FOLDER.'"&amp;gt;Return to your account&amp;lt;/a&amp;gt;.',
            'merchant-private-data'=>$data['invoice_id']
        );
        
        $xml = $this->getCartXML();
        $this->parameters['cart'] = base64_encode($xml);
        $this->parameters['signature'] = base64_encode($this->calcHmacSha1($xml, $this->settings['googlecheckout_merchant_key']));
        return true;
    }
    
    /*
    function process() {
        $this->loadResponse();
        if(!$this->verifyResponse()) {
            $this->result = 'failed';
        } else {
            $this->loadResult();
        }
        return $this->response['merchant-private-data'];    
    }
    */
    
    function processNotification($raw_response) {
        $this->loadResponse($raw_response);
        if($this->verifyResponse()) {
            $this->result = 'failed';
            switch($this->response['notification-type']) {
                case 'new-order-notification': 
                    if(in_array($this->response['financial-order-state'],array('REVIEWING','CHARGEABLE','CHARGING','CHARGED'))) {
                        parent::processPayment($this->response['merchant-private-data'],$this->response['google-order-number'],$this->response['price']);
                        $this->result = 'success';
                        $this->result_amount = $this->response['price'];
                    }    
                    break;
                case 'order-state-change-notification':
                    if($this->response['new-financial-order-state'] == 'CHARGEABLE') {
                        //parent::processPayment($this->parameters['invoice_id'],$this->response['google-order-number'],$this->response['price']);
                        //$this->result = 'success'; 
                    } elseif(in_array($this->response['new-financial-order-state'],array('PAYMENT_DECLINED','CANCELLED','CANCELLED_BY_GOOGLE'))) {  
                        $this->result = 'failed';
                        parent::processRefund($this->response['google-order-number']);
                    } else {
                        //$this->result = 'pending';
                    }                                                      
                    break;
                case 'refund-amount-notification':
                    if($this->response['total-refund-amount'] == $this->parameters['price']) {
                        $this->result = 'success';
                        parent::processRefund($this->response['google-order-number']);
                    } 
                    break;
                case 'risk-information-notification':
                    //$this->result = 'success';
                    break;
                default:
            }
        } else {
            $this->result = 'failed';
            return false;
        }
        return true;     
    }
    
    function getCartXML() {
        $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<checkout-shopping-cart xmlns="http://checkout.google.com/schema/2">';
        $xml .= '<shopping-cart>';
        $xml .= '<items>';
        $xml .= '<item>';
        $xml .= '<item-name>'.$this->parameters['item-name'].'</item-name>';
        $xml .= '<item-description>'.$this->parameters['item-description'].'</item-description>';
        $xml .= '<unit-price currency="'.$this->parameters['currency'].'">'.$this->parameters['price'].'</unit-price>';
        $xml .= '<quantity>'.$this->parameters['quantity'].'</quantity>';
        
        /*
        <subscription type="google" period ="MONTHLY">
          <payments>
            <subscription-payment>
              <maximum-charge currency="'.$this->parameters['currency'].'">'.$this->parameters['price'].'</maximum-charge>
            </subscription-payment>
          </payments>
          <recurrent-item>
            <item-name>'.$this->parameters['item-name'].'</item-name>
            <item-description>'.$this->parameters['item-description'].'</item-description>
            <quantity>1</quantity>
            <unit-price currency="'.$this->parameters['currency'].'">'.$this->parameters['price'].'</unit-price>
            <digital-content>
              <display-disposition>'.$this->parameters['delivery-type'].'</display-disposition>
              <description>'.$this->parameters['delivery-description'].'</description>
            </digital-content>
          </recurrent-item>
        </subscription>
        */
        
        $xml .= '<digital-content>';
        $xml .= '<display-disposition>'.$this->parameters['delivery-type'].'</display-disposition>';
        $xml .= '<description>'.$this->parameters['delivery-description'].'</description>';
        $xml .= '</digital-content>';
        $xml .= '</item>';
        $xml .= '</items>';
        $xml .= '<merchant-private-data>'.$this->parameters['merchant-private-data'].'</merchant-private-data>';
        $xml .= '</shopping-cart>';
        $xml .= '<checkout-flow-support>';
        $xml .= '<merchant-checkout-flow-support/>';
        $xml .= '</checkout-flow-support>';
        $xml .= '</checkout-shopping-cart>';
        return $xml; 
    }

    /**
    * Load response
    * Load the response into class variable for processing
    * @return void
    */
    function verifyResponse() {
        if(isset($_SERVER['PHP_AUTH_USER']) AND isset($_SERVER['PHP_AUTH_PW'])) {
            $compare_id = $_SERVER['PHP_AUTH_USER'];
            $compare_key = $_SERVER['PHP_AUTH_PW'];
        } elseif(isset($_SERVER['HTTP_AUTHORIZATION'])) {
            list($compare_id, $compare_key) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'],strpos($_SERVER['HTTP_AUTHORIZATION'], " ") + 1)));
        } elseif(isset($_SERVER['Authorization'])) {
            list($compare_id, $compare_key) = explode(':', base64_decode(substr($_SERVER['Authorization'],strpos($_SERVER['Authorization'], " ") + 1)));
        } else {
            $this->errors = 'Authentication failed';
            return false;
        }

        if($compare_key != $this->settings['googlecheckout_merchant_key'] OR $compare_id != $this->settings['googlecheckout_merchant_id']) {
            $this->errors[] = 'Invalid merchant key or ID';
            return false;    
        }
        if(isset($this->response['merchant-private-data'])) {
            if(!$this->loadStoredParameters($this->response['merchant-private-data'])) {
                $this->errors[] = 'Invalid invoice number (merchant-private-data)';
            }
        }
    
        //need to check the header?
        //$header = 'Authorization: Basic '.base64_encode($this->settings['google_merchant_id'].':'.$this->settings['google_merchant_key']);
        //$header .= 'Content-Type: application/xml;charset=UTF-8';
        //$header .= 'Accept: application/xml;charset=UTF-8';
        return true;
    }
    
    function calcHmacSha1($data, $key) {
        $blocksize = 64;
        $hashfunc = 'sha1';
        if (strlen($key) > $blocksize) {
            $key = pack('H*', $hashfunc($key));
        }
        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack('H*', $hashfunc(($key^$opad).pack('H*', $hashfunc(($key^$ipad).$data))));
        return $hmac; 
    }
    
    function loadResponse($raw_response) { 
        $xmlobject = simplexml_load_string(trim($raw_response));        
        $this->response['notification-type'] = (string) $xmlobject->getName();
        $this->response['serial-number'] = (string) $xmlobject->attributes();                                                      
        $this->response['google-order-number'] = (string) $xmlobject->{'google-order-number'};    
         
        switch($this->response['notification-type']) {
            case 'new-order-notification':
                $this->response['item-name'] = (string) $xmlobject->{'shopping-cart'}->items->item->{'item-name'};
                $this->response['item-description'] = (string) $xmlobject->{'shopping-cart'}->items->item->{'item-description'};
                $this->response['price'] = (string) $xmlobject->{'shopping-cart'}->items->item->{'unit-price'};                                                      
                $this->response['merchant-private-data'] = (string) $xmlobject->{'shopping-cart'}->{'merchant-private-data'};
                $this->response['financial-order-state'] = (string) $xmlobject->{'financial-order-state'}; 
                break;
            case 'refund-amount-notification':
                $this->response['latest-refund-amount'] = (string) $xmlobject->{'latest-refund-amount'}; 
                $this->response['total-refund-amount'] = (string) $xmlobject->{'total-refund-amount'};
                break;
            case 'order-state-change-notification':
                $this->response['new-fulfillment-order-state'] = (string) $xmlobject->{'new-fulfillment-order-state'};
                $this->response['new-financial-order-state'] = (string) $xmlobject->{'new-financial-order-state'}; // CHARGEABLE
                $this->response['previous-fulfillment-order-state'] = (string) $xmlobject->{'previous-fulfillment-order-state'};
                $this->response['previous-financial-order-state'] = (string) $xmlobject->{'previous-financial-order-state'};                                                     
                break;
            case 'risk-information-notification':
                $this->response['eligible-for-protection'] = (string) $xmlobject->{'risk-information'}->{'eligible-for-protection'};
                $this->response['avs-response'] = (string) $xmlobject->{'risk-information'}->{'avs-response'};
                $this->response['cvn-response'] = (string) $xmlobject->{'risk-information'}->{'cvn-response'};
                $this->response['partial-cc-number'] = (string) $xmlobject->{'risk-information'}->{'partial-cc-number'};
                $this->response['ip-address'] = (string) $xmlobject->{'risk-information'}->{'ip-address'};
                $this->response['buyer-account-age'] = (string) $xmlobject->{'risk-information'}->{'buyer-account-age'};
                break;
            case 'chargeback-amount-notification':
                $this->response['latest-chargeback-amount'] = (string) $xmlobject->{'latest-chargeback-amount'}; 
                $this->response['total-chargeback-amount'] = (string) $xmlobject->{'total-chargeback-amount'}; 
                break;
            default:

        }
    }
}
?>