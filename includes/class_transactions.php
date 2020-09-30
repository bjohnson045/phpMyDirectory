<?php
/**
* Class Transactions
* Transactions received from payment gateways used to pay invoices
*/
class Transactions extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Transaction Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_TRANSACTIONS;
    }

    /**
    * Get transactions with processor name based on invoice id
    * @param integer $invoice_id Invoice ID
    * @param integer $limit1 Table offset
    * @param integer $limit2 Number of records to get
    * @return array All matching rows
    */
    function getByInvoiceID($invoice_id, $limit1=null, $limit2=null) {
        return $this->db->GetAll("SELECT t.* FROM ".T_TRANSACTIONS." t WHERE invoice_id=? ".($limit1 ? "LIMIT ".$limit1.($limit2 ? ", ".$limit2 : '') : ''),array($invoice_id));
    }

    /**
    * Insert transaction
    * @param array $data Data array
    * @return integer Transaction ID
    */
    function insert($data) {
        $id = parent::insert($data);
        $this->updateInvoiceStatus($data['invoice_id']);
        return $id;
    }

    /**
    * Update a transaction
    * @param array $data Transaction data
    * @param int $id Transaction ID
    * @return int Transaction ID
    */
    function update($data,$id) {
        parent::update($data,$id);
        $this->updateInvoiceStatus($data['invoice_id']);
        return $id;
    }

    /**
    * Delete a transaction
    * @param int $id Transaction ID
    * @return void
    */
    function delete($id) {
        $invoice_id = $this->db->GetOne("SELECT invoice_id FROM ".T_TRANSACTIONS." WHERE id=?",array($id));
        parent::delete($id);
        $this->updateInvoiceStatus($invoice_id);
    }

    /**
    * Update invoice status based on transaction amounts
    * @param int $invoice_id Invoice ID
    * @return void
    */
    function updateInvoiceStatus($invoice_id) {
        $total_payments = $this->db->GetOne("SELECT SUM(amount) FROM ".T_TRANSACTIONS." WHERE invoice_id=? GROUP BY invoice_id",array($invoice_id));
        $invoice_amount = $this->db->GetOne("SELECT total FROM ".T_INVOICES." WHERE id=?",array($invoice_id));
        if($total_payments < $invoice_amount) {
            $this->db->Execute("UPDATE ".T_INVOICES." SET status='unpaid' WHERE id=?",array($invoice_id));
        } else {
            $this->db->Execute("UPDATE ".T_INVOICES." SET status='paid' WHERE id=?",array($invoice_id));
        }
    }

}
?>