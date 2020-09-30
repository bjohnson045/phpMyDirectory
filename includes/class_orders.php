<?php
/**
* Orders
*/
class Orders extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Orders constructor
    * @param object $PMDR
    * @return Orders
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_ORDERS;
    }

    /**
    * Update an order
    * @param array $data Order data
    * @param int $id Order ID
    * @return resource
    */
    public function update($data, $id) {
        $discount_code = array(
            'discount_code'=>'',
            'discount_code_value'=>'0.00',
            'discount_code_type'=>NULL,
            'discount_code_discount_type'=>'fixed'
        );
        $data = array_merge($data,$discount_code);
        if(!empty($data['discount'])) {
            $discount_code = $this->db->GetRow("
                SELECT
                    code AS discount_code,
                    value AS discount_code_value,
                    type AS discount_code_type,
                    discount_type AS discount_code_discount_type
                FROM ".T_DISCOUNT_CODES." WHERE title=?",array($data['discount']));
            if($discount_code) {
                $data = array_merge($data,$discount_code);
            }
        }
        return parent::update($data, $id);
    }

    /**
    * Get a random order ID that is not in use
    * @return int Order ID
    */
    public function getRandomOrderID() {
        $limit = (4294967295 < PHP_INT_MAX) ? 4294967295 : PHP_INT_MAX;
        do {
            $id = mt_rand(1000000000,$limit);
        } while($this->db->GetOne("SELECT COUNT(*) AS count FROM ".T_ORDERS." WHERE order_id=?",array($id)));
        return $id;
    }

    /**
    * Delete an order
    * @param int $id Order ID
    * @return void
    */
    public function delete($id) {
        $type = $this->db->GetRow("SELECT type, type_id FROM ".T_ORDERS." WHERE id=?",array($id));
        switch($type['type']) {
            case 'listing_membership':
                $this->PMDR->get('Listings')->delete($type['type_id']);
                break;
        }

        $invoices = $this->db->GetCol("SELECT id FROM ".T_INVOICES." WHERE order_id=?",array($id));
        foreach($invoices AS $invoice_id) {
            $this->db->Execute("DELETE FROM ".T_TRANSACTIONS." WHERE invoice_id=?",array($invoice_id));
        }
        unset($invoices,$invoice_id);
        $this->db->Execute("DELETE FROM ".T_INVOICES." WHERE order_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_UPGRADES." WHERE order_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_ORDERS." WHERE id=?",array($id));
    }

    /**
    * Get an order
    * @param int $id Order ID
    * @return array Order data
    */
    public function get($id) {
        return $this->db->GetRow("SELECT o.*, u.user_first_name, u.user_last_name, i.status as payment_status FROM (".T_ORDERS." o INNER JOIN ".T_USERS." u ON o.user_id=u.id) LEFT JOIN ".T_INVOICES." i ON o.invoice_id=i.id WHERE o.id=? AND o.user_id=u.id",array($id));
    }

    /**
    * Get an order by the type and type ID
    * @param string $type Order Type
    * @param int $type_id Order Type ID
    * @return array Order data
    */
    public function getByType($type, $type_id) {
        return $this->db->GetRow("SELECT * FROM ".T_ORDERS." WHERE type_id=? AND type=?",array($type_id,$type));
    }

    /**
    * Change the user of an order
    * @param int $id Order ID
    * @param int $user_id New User ID
    * @return void
    */
    public function changeUser($id, $user_id) {
        $order = $this->db->GetRow("SELECT type, type_id FROM ".T_ORDERS." WHERE id=?",array($id));
        switch($order['type']) {
            case 'listing_membership':
                $this->PMDR->get('Listings')->changeUser($order['type_id'],$user_id);
                break;
        }
        $this->db->Execute("UPDATE ".T_INVOICES." SET user_id=? WHERE type='listing_membership' AND type_id=?",array($user_id, $order['type_id']));
        $this->db->Execute("UPDATE ".T_ORDERS." SET user_id=? WHERE type='listing_membership' AND type_id=?",array($user_id, $order['type_id']));
    }

    /**
    * Change the order priginc ID
    * @param int $order_id Order ID
    * @param int $new_pricing_id Pricing ID
    * @param bool $create_invoice Create an invoice
    * @return mixed Invoice ID if one is created, null otherwise
    */
    public function changePricingID($order_id,$new_pricing_id,$create_invoice=false) {
        $order = $this->get($order_id);
        $product = $this->PMDR->get('Products')->getByPricingID($new_pricing_id,$order['user_id']);

        $this->db->Execute("UPDATE ".T_ORDERS." SET pricing_id=?, amount_recurring=?, period=?, period_count=?, next_due_date=?, future_due_date=?, next_invoice_date=?, taxed=?, upgrades=?, renewable=?, suspend_overdue_days=? WHERE id=?",
        array($product['pricing_id'],$product['recurring_total'],$product['period'],$product['period_count'],$product['next_due_date'],$product['future_due_date'],$product['next_invoice_date'],$product['taxed'],$product['upgrades'],$product['renewable'],$product['suspend_overdue_days'],$order['id']));

        switch($order['type']) {
            case 'listing_membership':
                $this->PMDR->get('Listings')->updateMembership($order['type_id']);
                break;
        }

        $existing_invoices = $this->db->GetCol("SELECT id FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($order_id));
        foreach($existing_invoices AS $invoice) {
            $this->PMDR->get('Invoices')->changeStatus($invoice, 'canceled');
        }
        unset($existing_invoices,$invoice);

        $invoice_id = null;

        if($product['total'] != 0.00 AND $create_invoice) {
            $invoice_id = $this->PMDR->get('Invoices')->insert(
                array(
                    'order_id'=>$order['id'],
                    'user_id'=>$order['user_id'],
                    'type'=>$order['type'],
                    'type_id'=>$order['type_id'],
                    'date_due'=>$product['next_due_date'],
                    'subtotal'=>$product['subtotal'],
                    'tax'=>(float) $product['tax'],
                    'tax_rate'=>(float) $product['tax_rate'],
                    'tax2'=>(float) $product['tax2'],
                    'tax_rate2'=>(float) $product['tax_rate2'],
                    'total'=>$product['total'],
                    'product_name'=>$product['product_name'],
                    'product_group_name'=>$product['product_group_name']
                )
            );

            $this->PMDR->get('Invoices')->sendInvoiceCreatedEmail($invoice_id);

            $this->changeStatus($order_id,'pending');
        }
        return $invoice_id;
    }

    /**
    * Renew an order
    * @param int $order_id Order ID
    * @param mixed $date Date to renew from, or null to use the pre-existing next due date
    * @return void
    */
    public function renew($order_id, $date=null) {
        if(is_null($date)) {
            $date = 'next_due_date';
        } else {
            $date = "'".$date."'";
        }
        $this->db->Execute("UPDATE ".T_ORDERS." SET next_due_date = IF(future_due_date IS NULL,IF(period='days',$date+INTERVAL period_count DAY,IF(period='months',$date+INTERVAL period_count MONTH,$date+INTERVAL period_count YEAR)),future_due_date) WHERE id=?",array($order_id));
        $this->db->Execute("UPDATE ".T_ORDERS." SET future_due_date = IF(period='days',next_due_date+INTERVAL period_count DAY,IF(period='months',next_due_date+INTERVAL period_count MONTH,next_due_date+INTERVAL period_count YEAR)) WHERE id=?",array($order_id));
    }

    /**
    * Change an order status
    * @param int $order_id Order ID
    * @param string $new_status New status for the order
    * @return void
    */
    public function changeStatus($order_id,$new_status) {
        $order = $this->get($order_id);
        $product = $this->PMDR->get('Products')->getByPricingID($order['pricing_id'],$order['user_id']);
        $product_status = null;
        if($new_status == 'active') {
            if($product['activate'] == 'immediate' OR $product['activate'] == 'approved') {
                $product_status = 'active';
            } elseif($product['activate'] == 'payment') {
                if(!$this->db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($order['id']))) {
                    $product_status = 'active';
                }
            }
        } elseif($new_status == 'pending') {
            $product_status = 'pending';
        } elseif($new_status == 'completed') {
            $product_status = null;
        } elseif($new_status == 'canceled') {
            // Cancel unpaid invoices if order is canceled
            $order_invoices = $this->db->GetAll("SELECT id FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($order['id']));
            foreach($order_invoices AS $invoice) {
                $this->PMDR->get('Invoices')->changeStatus($invoice['id'],'canceled');
            }
            $product_status = 'suspended';
        } else {
            $product_status = 'suspended';
        }
        if(!is_null($product_status)) {
            $this->changeProductStatus($order['id'],$product_status);
        }
        $this->db->Execute("UPDATE ".T_ORDERS." SET status=? WHERE id=?",array($new_status,$order_id));
    }

    /**
    * Change the product status of an order
    * @param int $order_id Order ID
    * @param string $new_status New status
    * @return void
    */
    public function changeProductStatus($order_id,$new_status) {
        $order = $this->get($order_id);

        if($this->PMDR->get('Dates')->isZero($order['date_active']) AND $new_status == 'active') {
            $this->db->Execute("UPDATE ".T_ORDERS." SET date_active=NOW() WHERE id=?",array($order_id));
            $activate_email = $this->db->GetOne("SELECT activate_email FROM ".T_PRODUCTS_PRICING." WHERE id=?",array($order['pricing_id']));
            if(!empty($activate_email)) {
                $this->PMDR->get('Email_Templates')->send($activate_email,array('to'=>$order['user_id'],'order_id'=>$order['id']));
            }
        }

        switch($order['type']) {
            case 'listing_membership':
                if(!$this->PMDR->getConfig('approve_update_pending') OR !$this->db->GetOne("SELECT COUNT(*) FROM ".T_UPDATES." WHERE type='listing_membership' AND type_id=?",array($order['type_id']))) {
                    $this->PMDR->get('Listings')->changeStatus($order['type_id'],$new_status);
                    return true;
                }
                break;
        }
        return false;
    }
}
?>