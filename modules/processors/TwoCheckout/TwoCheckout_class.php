<?php
class TwoCheckout extends PaymentAPIHandler {
    var $gateway_name = 'TwoCheckout';
    var $test_url = '';
    var $url = 'https://www.2checkout.com/checkout/spurchase';  // https://www2.2checkout.com/2co/buyer/purchase

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
            'sid'=>$this->settings['2co_id'],
            'first_name'=>$data['user_first_name'],
            'last_name'=>$data['user_last_name'],
            'email'=>$data['user_email'],
            'street_address'=>$data['user_address1'],
            'city'=>$data['user_city'],
            'state'=>$data['user_state'],
            'zip'=>$data['user_zip'],
            'country'=>$data['user_country'],
            'phone'=>$data['user_phone'],
            'fixed'=>'Y',
            'x_receipt_link_url'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'merchant_order_id'=>$data['invoice_id']
        );

        if($this->settings['testmode']) {
            $this->parameters['demo'] = 'Y';
        }

        if(trim($this->settings['2co_product_id_'.$data['pricing_id']]) != '') {
            $this->parameters['product_id'] = $this->settings['2co_product_id_'.$data['pricing_id']];
            $this->parameters['quantity'] = 1;
            $this->parameters['merchant_order_id'] = $data['order_id'];
        } else {
            $this->parameters['total'] = $data['balance'];
            $this->parameters['cart_order_id'] = $data['invoice_id'];
            $this->parameters['id_type'] = 1;
            $this->parameters['c_name'] = $this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'];
            $this->parameters['c_description'] = $this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'];
            $this->parameters['c_price'] = $data['balance']; // same as amount
            $this->parameters['c_tangible'] = 'N';
            //$this->parameters['c_prod'] = 'Invoice #'.$data['invoice_id'].',1'; // Used for 2checkout verification
        }

        return true;
    }

    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['cart_order_id'];
    }

    function loadResult() {
        if($this->verifyResponse()) {
            if ($this->response['credit_card_processed'] == 'Y') {
                $this->result = 'success';
				$this->result_amount = $this->response['total'];
            } elseif($this->response['credit_card_processed'] == 'K') {
                $this->result = 'pending';
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
            $key = strtoupper(md5($this->settings['2co_word'].$this->settings['2co_id'].'1'.$this->response['total']));
        } else {
            $key = strtoupper(md5($this->settings['2co_word'].$this->settings['2co_id'].$this->response['order_number'].$this->response['total']));
        }
        // The response by the header redirect option posts back using GET and it is "key" instead of "md5_hash"
        if($key == $this->response['key']) {
            return true;
        } else {
            $this->errors[] = 'Invalid hash';
            return false;
        }
    }

    function loadResponse() {
        if(!count($_POST)) {
            if(!count($_GET)) {
                $this->errors[] = 'GET and POST empty';
            } else {
                $this->response = $_GET;
            }
        } else {
            $this->response = $_POST;
        }
    }
}

class TwoCheckout_Notification extends TwoCheckout {
    var $gateway_name = 'TwoCheckout';
    var $test_url = '';
    var $url = 'https://www.2checkout.com/checkout/spurchase';  // https://www2.2checkout.com/2co/buyer/purchase

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