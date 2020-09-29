<?php
/**
* Invoices Class
*/
class Invoices extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Invoices Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_INVOICES;
    }

    /**
    * Get an invoices details
    * @param integer $id
    * @return array
    */
    public function get($id) {
        $invoice = $this->db->GetRow("SELECT * FROM ".T_INVOICES." WHERE id=?",array($id));
        $payment_total = $this->db->GetOne("SELECT SUM(amount) FROM ".T_TRANSACTIONS." WHERE invoice_id=? GROUP BY invoice_id",array($id));
        $invoice['balance'] = number_format($invoice['total'] - $payment_total,2);
        return $invoice;
    }

    /**
    * Insert an invoice
    * @param string $data
    * @return boolean
    */
    public function insert($data) {
        if(!isset($data['status'])) {
            $data['status'] = 'unpaid';
        }
        if(!isset($data['date'])) {
            $data['date'] = date('Y-m-d');
        }
        if(!isset($data['date_due'])) {
            $data['date_due'] = date('Y-m-d');
        }
        if(!isset($data['notes'])) {
            $data['notes'] = '';
        }

        if(!isset($data['description']) AND isset($data['type_id']) AND isset($data['type'])) {
            $data['description'] = $data['product_group_name'].' - '.$data['product_name'];

            if(!isset($data['product_title'])) {
                switch($data['type']) {
                    case 'listing_membership':
                        $data['description'] .= ' - '.$this->db->GetOne("SELECT title FROM ".T_LISTINGS." WHERE id=?",array($data['type_id']));
                        break;
                }
            } else {
                $data['description'] .= $data['product_title'];
            }
        }
        if(!$this->PMDR->get('Dates')->isZero($data['next_due_date']) AND isset($data['next_due_date'])) {
            $data['description'] .= ' ('.$this->PMDR->get('Dates_Local')->formatDate($data['date_due']).' - '.$this->PMDR->get('Dates_Local')->formatDate($this->PMDR->get('Dates')->dateSubtract($data['next_due_date'],1)).')';
        }
        unset($data['product_title']);
        unset($data['product_name']);
        unset($data['product_group_name']);
        unset($data['next_due_date']);
        return parent::insert($data);
    }

    /**
    * Send the invoice created email template
    * @param int $invoice_id
    * @return boolean Email sent or not
    */
    public function sendInvoiceCreatedEmail($invoice_id) {
        $attachment = array();
        if($this->PMDR->getConfig('invoice_email_pdf')) {
            if($data = $this->getPDF($invoice_id,false)) {
                $attachment = array(
                    'data'=>$data,
                    'name'=>'Invoice_'.$invoice_id.'.pdf',
                    'type'=>'application/pdf'
                );
            }
        }
        return $this->PMDR->get('Email_Templates')->send('invoice_created',array('invoice_id'=>$invoice_id,'attachment'=>$attachment));
    }

    /**
    * Update an invoices price details
    * @param integer $invoice_id
    * @return array
    */
    public function recalculatePrice($invoice_id) {
        // We don't refigure tax from the user account here because its done at the time of invoice creation
        if(!$invoice = $this->db->GetRow("SELECT id, subtotal, total, tax_rate, tax_rate2, discount_code_value, discount_code_discount_type FROM ".T_INVOICES." i WHERE id=?",array($invoice_id))) {
            return false;
        }
        if($invoice['discount_code_value'] > 0.00) {
            $discount = round((($invoice['discount_code_discount_type'] == 'percentage') ? (($invoice['discount_code_value'] / 100) * $invoice['subtotal']) : $invoice['discount_code_value']),2);
        } else {
            $discount = 0;
        }

        $invoice['tax'] = 0.00;
        $invoice['tax2'] = 0.00;

        if($invoice['tax_rate'] != 0.00) {
            if($this->PMDR->getConfig('tax_type') == 'exclusive') {
                $invoice['tax'] = round(($invoice['subtotal']-$discount)*($invoice['tax_rate']/100),2);
                if($invoice['tax_rate2'] != 0.00) {
                    if($this->PMDR->getConfig('compound_tax')) {
                        $invoice['tax2'] = round((($invoice['subtotal']-$discount)+$invoice['tax'])*($invoice['tax_rate2']/100),2);
                    } else {
                        $invoice['tax2'] = round(($invoice['subtotal']-$discount)*($invoice['tax_rate2']/100),2);
                    }
                }
            } else {
                $tax = round($invoice['subtotal'] - ($invoice['subtotal']*100/($invoice['tax_rate']+100)),2);
                if($invoice['tax_rate2'] != 0.00) {
                    $tax2 = round($invoice['subtotal'] - ($invoice['subtotal']*100/($invoice['tax_rate2']+100)),2);
                } else {
                    $tax2 = 0.00;
                }
                $invoice['tax'] = round(($invoice['subtotal']-$tax-$tax2-$discount) - (($invoice['subtotal']-$tax-$tax2-$discount)*100/($invoice['tax_rate']+100)),2);
                $invoice['tax2'] = round(($invoice['subtotal']-$tax-$tax2-$discount) - (($invoice['subtotal']-$tax-$tax2-$discount)*100/($invoice['tax_rate2']+100)),2);
                $invoice['subtotal'] = $invoice['total'] - $invoice['tax'] - $invoice['tax2'];
            }
        }

        $invoice['total'] = round($invoice['subtotal'] + $invoice['tax'] + $invoice['tax2'] - $discount,2);

        $this->update($invoice,$invoice['id']);
        return $invoice;
    }

    /**
    * Get HTML for a printable invoice
    * @param integer $id
    * @param string $template
    * @param boolean $pdf
    */
    public function getPrintTemplate($id, $template, $pdf = false) {
        // Get all of the user table fields in case of custom fields
        // u.* is selected first so the invoice ID overrides the user ID database table field
        if(!$invoice = $this->db->GetRow("SELECT u.*, i.* FROM ".T_INVOICES." i, ".T_USERS." u WHERE i.user_id=u.id AND i.id=?",array($id))) {
            return false;
        }

        $invoices = $this->PMDR->get('Invoices');

        $transactions = $this->PMDR->get('Transactions');

        $template_content = $this->PMDR->getNew('Template',$template);

        if($this->PMDR->getConfig('invoice_logo')) {
            if($logo_url = get_file_url(TEMP_UPLOAD_PATH.$this->PMDR->getConfig('invoice_logo'))) {
                $template_content->set('logo_url', $logo_url);
            }
        }

        $gateway = $this->db->GetRow("SELECT * FROM ".T_GATEWAYS." WHERE id=?",array($invoice['gateway_id']));
        $transactions = $this->db->GetAll("SELECT t.amount, t.transaction_id, t.date, t.gateway_id FROM ".T_TRANSACTIONS." t WHERE t.invoice_id=?",array($_GET['id']));
        $invoice['balance'] = (float) $invoice['total'];
        // Remove trailing zeros if they exist
        $invoice['tax_rate'] = (float) $invoice['tax_rate'];
        $invoice['tax_rate2'] = (float) $invoice['tax_rate2'];
        foreach($transactions as $key=>$transaction) {
            $transactions[$key]['gateway_name'] = ($gateway_name = $this->db->GetOne("SELECT display_name FROM ".T_GATEWAYS." WHERE id=?",array($transaction['gateway_id']))) ? $gateway_name : $transaction['gateway_id'];
            $transactions[$key]['date'] = $this->PMDR->get('Dates_Local')->formatDateTime($transaction['date']);
            $invoice['balance'] -= $transaction['amount'];
        }
        $invoice['balance'] = number_format($invoice['balance'],2);
        $invoice['invoice_company'] = $this->PMDR->getConfig('invoice_company');
        $invoice['invoice_address'] = $this->PMDR->getConfig('invoice_address');
        foreach($invoice AS $key=>$value) {
            switch($key) {
                case 'date':
                case 'date_due':
                    $template_content->set($key, $this->PMDR->get('Dates_Local')->formatDate($value));
                    break;
                case 'discount_code_value':
                    $template_content->set('discount_code_value', number_format(($invoice['discount_code_discount_type'] == 'percentage') ? ($invoice['discount_code_value'] / 100) * $invoice['subtotal'] : $invoice['discount_code_value'],2));
                    break;
                default:
                    $template_content->set($key,$value);
                    break;
            }
        }
        $template_content->set('transactions', $transactions);
        return $template_content;
    }

    /**
    * Insert a transaction to an invoice
    * @param integer $invoice_id
    * @param string $transaction_id
    * @param float $amount
    * @param string $date
    * @param string $description
    * @param string $gateway_id
    * @return integer Invoice ID
    */
    public function insertTransaction($invoice_id, $transaction_id, $amount, $date, $description = '', $comments = '', $gateway_id = NULL) {
        if(!$invoice = $this->get($invoice_id)) {
            return false;
        }
        if(!$user = $this->db->GetRow("SELECT id, user_email FROM ".T_USERS." WHERE id=?",array($invoice['user_id']))) {
            return false;
        }
        $insert_id = $this->PMDR->get('Transactions')->insert(
            array(
                'user_id'=>$invoice['user_id'],
                'gateway_id'=>$gateway_id,
                'transaction_id'=>$transaction_id,
                'invoice_id'=>$invoice_id,
                'date'=>$date,
                'description'=>$description,
                'comments'=>$comments,
                'amount'=>$amount
            )
        );

        // Setup the array we pass to the invoice object to update the invoice
        $this->db->Execute("UPDATE ".T_INVOICES." SET gateway_id=? WHERE id=?",array($gateway_id,$invoice_id));
        $this->db->Execute("UPDATE ".T_ORDERS." SET gateway_id=? WHERE invoice_id=?",array($gateway_id,$invoice_id));

        if(!empty($invoice['affiliate_program_tracking_code']) AND valid_url($this->PMDR->getConfig('affiliate_program_code'))) {
            $url = str_replace(array('*amount*','*invoice_id*','*transaction_id*','*tracking_code*'),array($amount,$invoice_id,$transaction_id,$invoice['affiliate_program_tracking_code']),$this->PMDR->getConfig('affiliate_program_code'));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
            curl_setopt($ch,CURLOPT_TIMEOUT,10);
            curl_exec($ch);
            $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
            if(curl_errno($ch)) {
                trigger_error('Affiliate program URL error: '.curl_error($ch),E_USER_WARNING);
            }
            if($code !== 200) {
                trigger_error('Affiliate program URL error, '.$this->PMDR->getConfig('affiliate_program_code').' returned HTTP code: '.$code);
            }
            curl_close($ch);
        }

        // If this payment pays off the balance or it overpays, we set the invoice status to paid
        if($invoice['balance'] - $amount <= 0) {
            $this->changeStatus($invoice_id,'paid');
        }

        return $insert_id;
    }

    /**
    * Change invoice status
    * @param int $id
    * @param string $status
    * @return mixed
    */
    public function changeStatus($id, $status) {
        if(!$invoice = $this->db->GetRow("SELECT type, type_id, order_id FROM ".T_INVOICES." WHERE id=?",array($id))) {
            return false;
        }
        if($status == 'paid') {
            if($order = $this->db->GetRow("SELECT * FROM ".T_ORDERS." WHERE id=?",array($invoice['order_id']))) {
                $activate = $this->db->GetOne("SELECT activate FROM ".T_PRODUCTS_PRICING." WHERE id=?",array($order['pricing_id']));

                $this->PMDR->get('Orders')->renew($order['id']);

                if($upgrade = $this->db->GetRow("SELECT pricing_id_new FROM ".T_UPGRADES." WHERE invoice_id=?",array($id))) {
                    // Update the order if its pending because it was set to pending by the upgrade
                    $this->db->Execute("UPDATE ".T_ORDERS." SET status='active' WHERE status='pending' AND id=?",array($invoice['order_id']));
                    // We update the upgrades based on invoice ID in case the user tried to upgrade more than once
                    $this->db->Execute("UPDATE ".T_UPGRADES." SET status='completed' WHERE invoice_id=?",array($id));
                }

                switch($invoice['type']) {
                    case 'listing_membership':
                        if($activate == 'payment' OR $activate == 'immediate') {
                            if(!$this->db->GetOne("SELECT COUNT(*) FROM ".T_UPDATES." WHERE type='listing_membership' AND type_id=?",array($invoice['type_id']))) {
                                $this->PMDR->get('Listings')->changeStatus($invoice['type_id'],'active');
                            }
                        }
                        break;
                }
            }
            $this->db->Execute("UPDATE ".T_INVOICES." SET status='paid', date_paid=NOW() WHERE id=?",array($id));
        } elseif($status == 'unpaid' OR $status == 'canceled') {
            $this->db->Execute("UPDATE ".T_INVOICES." SET status=?, date_paid=NULL WHERE id=?",array($status,$id));
        }
    }

    /**
    * Get the invoice PDF file
    * @param int $id Invoice ID
    * @param mixed $output Directly output to browser or return content
    * @return mixed
    */
    public function getPDF($id, $output = false) {
        // Language needs to be general

        // If we get a memory here with TCPDF its the unicode_data.php file which has a huge array for unicode lookup
        // Solution: increase server memory
        $invoice = $this->db->GetRow("
            SELECT
                ".T_INVOICES.".*,
                ".T_USERS.".user_first_name,
                ".T_USERS.".user_last_name,
                ".T_USERS.".user_organization,
                ".T_USERS.".user_address1,
                ".T_USERS.".user_address2,
                ".T_USERS.".user_city,
                ".T_USERS.".user_state,
                ".T_USERS.".user_country,
                ".T_USERS.".user_zip,
                ".T_USERS.".user_phone
            FROM
                ".T_INVOICES.",
                ".T_USERS."
            WHERE
                ".T_INVOICES.".user_id=".T_USERS.".id AND
                ".T_INVOICES.".id=?"
        ,array($id));

        if(!$invoice) {
            return false;
        }

        $gateway = $this->db->GetRow("SELECT * FROM ".T_GATEWAYS." WHERE id=?",array($invoice['gateway_id']));
        $transactions = $this->db->GetAll("SELECT t.amount, t.transaction_id, t.date, g.display_name FROM ".T_TRANSACTIONS." t, ".T_GATEWAYS." g WHERE t.gateway_id=g.id AND t.invoice_id=?",array($id));
        $balance = (float) $invoice['total'];
        foreach($transactions as $transaction) {
            $balance -= $transaction['amount'];
        }

        /** @var TCPDF */
        $pdf = $this->PMDR->getNew('TCPDF');
        $pdf->SetCreator(BASE_URL);
        $pdf->SetAuthor(BASE_URL);
        $pdf->SetTitle($this->PMDR->getLanguage('invoices_invoice').' '.$id);
        $pdf->SetSubject($this->PMDR->getLanguage('invoices_invoice').' '.$id);
        $pdf->setHeaderData('',0,'','',array(0,0,0), array(255,255,255) );
        $pdf->setFooterFont(array('helvetica', '', 10));
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 5);
        $pdf->SetFillColor(241, 241, 241);
        $pdf->AddPage();
        $invoiced_to = $invoice['user_first_name'].' '.$invoice['user_last_name']."\n";
        $invoiced_to .= $invoice['user_organization']."\n";
        $invoiced_to .= $invoice['user_address1']."\n";
        if(!empty($invoice['user_address2'])) { $invoiced_to .= $invoice['user_address2']."\n"; }
        $invoiced_to .= $invoice['user_city'].', '.$invoice['user_state'].', '.$invoice['user_zip']."\n";
        $invoiced_to .= $invoice['user_country'];
        if($this->PMDR->getConfig('invoice_logo')) {
            if($logo = find_file(TEMP_UPLOAD_PATH.$this->PMDR->getConfig('invoice_logo'))) {
                $image_details = getimagesize($logo);
                $pdf->Image($logo,$pdf->GetX(),$pdf->GetY(),$pdf->pixelsToUnits($image_details[0]),'','','','T', false);
                $pdf->SetFont("dejavusans", "B", 10);
                $pdf->MultiCell(60, 0, $this->PMDR->getLanguage('invoices_id').":\n".$this->PMDR->getLanguage('invoices_date').":\n".$this->PMDR->getLanguage('invoices_date_due').":", 0, 'R', 0, 0, '', '', true, 0);
            }
        } else {
            $pdf->MultiCell(60, 0, $this->PMDR->getConfig('invoice_company'), 0, 'L', 0, 0, '', '', true, 0);
            $pdf->SetFont("dejavusans", "B", 10);
            $pdf->MultiCell(80, 0, $this->PMDR->getLanguage('invoices_id').":\n".$this->PMDR->getLanguage('invoices_date').":\n".$this->PMDR->getLanguage('invoices_date_due').":", 0, 'R', 0, 0, '', '', true, 0);
        }

        $pdf->SetFont("dejavusans", '', 10);
        $pdf->MultiCell(40, 0, $invoice['id']."\n".$this->PMDR->get('Dates_Local')->formatDate($invoice['date'])."\n".$this->PMDR->get('Dates_Local')->formatDate($invoice['date_due'])."\n\n", 0, 'L', 0, 1, '', '', true, 0);

        $pdf->SetFont("dejavusans", "B", 10);
        $pdf->MultiCell(80, 0, $this->PMDR->getLanguage('invoices_invoiced_to').":", 0, 'L', 0, 0, '', '', true, 0);
        $pdf->MultiCell(80, 0, $this->PMDR->getLanguage('invoices_pay_to').":", 0, 'L', 0, 1, '', '', true, 0);

        $pdf->SetFont("dejavusans", '', 10);
        $pdf->MultiCell(80, 0, $invoiced_to, 0, 'L', 0, 0, '', '', true, 0);
        $pdf->MultiCell(80, 0, $this->PMDR->getConfig('invoice_address')."\n\n\n", 0, 'L', 0, 1, '', '', true, 0);

        $pdf->SetFont("dejavusans", "B", 10);
        $pdf->MultiCell(120, 8, $this->PMDR->getLanguage('invoices_description'), 1, 'L', 1, 0, '', '', true, 0, false, true, 8, 'M');
        $pdf->MultiCell(40, 8, $this->PMDR->getLanguage('invoices_total'), 1, 'L', 1, 1, '', '', true, 0, false, true, 8, 'M');

        $pdf->SetFont("dejavusans", "", 10);
        $pdf->MultiCell(120, 0, ($invoice['description'] == '') ? $this->PMDR->getLanguage('invoices_payment_for').$invoice['id'] : $invoice['description'], 1, 'L', 0, 0, '', '', true, 0);
        $pdf->MultiCell(40, $pdf->getLastH(), format_number_currency($invoice['subtotal']), 1, 'L', 0, 1, '', '', true, 0);

        $pdf->MultiCell(120, 0, $this->PMDR->getLanguage('invoices_subtotal').': ', 0, 'R', 0, 0, '', '', true, 0);
        $pdf->MultiCell(40, 0, format_number_currency($invoice['subtotal']), 1, 'L', 0, 1, '', '', true, 0);

        if(!empty($invoice['discount_code'])) {
            $pdf->MultiCell(120, 0, $this->PMDR->getLanguage('invoices_discount_code').' ('.$invoice['discount_code'].'): ', 0, 'R', 0, 0, '', '', true, 0);
            $pdf->MultiCell(40, 0, '('.format_number_currency(($invoice['discount_code_discount_type'] == 'percentage') ? ($invoice['discount_code_value'] / 100) * $invoice['subtotal'] : $invoice['discount_code_value'],2).')', 1, 'L', 0, 1, '', '', true, 0);
        }

        if($invoice['tax_rate'] != 0.00) {
            $pdf->MultiCell(120, 0, $this->PMDR->getLanguage('invoices_tax').' ('.(float) $invoice['tax_rate'].'%): ', 0, 'R', 0, 0, '', '', true, 0);
            $pdf->MultiCell(40, 0, format_number_currency($invoice['tax']), 1, 'L', 0, 1, '', '', true, 0);
        }

        if($invoice['tax_rate2'] != 0.00) {
            $pdf->MultiCell(120, 0, $this->PMDR->getLanguage('invoices_tax').' ('.(float) $invoice['tax_rate2'].'%): ', 0, 'R', 0, 0, '', '', true, 0);
            $pdf->MultiCell(40, 0, format_number_currency($invoice['tax2']), 1, 'L', 0, 1, '', '', true, 0);
        }

        $pdf->MultiCell(120, 0, $this->PMDR->getLanguage('invoices_total').': ', 0, 'R', 0, 0, '', '', true, 0);
        $pdf->MultiCell(40, 0, format_number_currency($invoice['total']), 1, 'L', 0, 1, '', '', true, 0);

        $pdf->SetFont("dejavusans", "B", 10);
        $pdf->MultiCell(0, 0, $this->PMDR->getLanguage('invoices_transactions'), 0, 'L', 0, 1, '', '', true, 0);

        $pdf->SetFont("dejavusans", "", 10);
        if($transactions) {
            $pdf->ln();
            $pdf->MultiCell(40, 8, $this->PMDR->getLanguage('invoices_transactions_date'), 1, 'L', 1, 0, '', '', true, 0, false, true, 8, 'M');
            $pdf->MultiCell(40, 8, $this->PMDR->getLanguage('invoices_payment_method'), 1, 'L', 1, 0, '', '', true, 0, false, true, 8, 'M');
            $pdf->MultiCell(40, 8, $this->PMDR->getLanguage('invoices_transactions_id'), 1, 'L', 1, 0, '', '', true, 0, false, true, 8, 'M');
            $pdf->MultiCell(40, 8, $this->PMDR->getLanguage('invoices_transactions_amount'), 1, 'L', 1, 1, '', '', true, 0, false, true, 8, 'M');
            $pdf->SetFont("dejavusans", "", 10);
            foreach($transactions as $transaction) {
                $pdf->MultiCell(40, $height, $this->PMDR->get('Dates_Local')->formatDateTime($transaction['date']), 1, 'L', 0, 0, '', '', true, 0);
                $pdf->MultiCell(40, $height, $transaction['display_name'], 1, 'L', 0, 0, '', '', true, 0, 0, 1, 0, 'T', true);
                $pdf->MultiCell(40, $height, $transaction['transaction_id'], 1, 'L', 0, 0, '', '', true, 0);
                $pdf->MultiCell(40, $height, format_number_currency($transaction['amount']), 1, 'L', 0, 1, '', '', true, 0);
            }
        } else {
            $pdf->MultiCell(120, 0, $this->PMDR->getLanguage('invoices_transactions_none'), 1, 'L', 0, 0, '', '', true, 0);
            $pdf->MultiCell(40, 0, '-', 1, 'L', 0, 1, '', '', true, 0);
        }
        $pdf->MultiCell(120, 0, $this->PMDR->getLanguage('invoices_balance').": ", 0, 'R', 0, 0, '', '', true, 0);
        $pdf->MultiCell(40, 0, format_number_currency($balance), 1, 'L', 0, 1, '', '', true, 0);
        $pdf->lastPage();

        if($output) {
            $pdf->Output('Invoice_'.$id.'.pdf','D');
            exit();
        } else {
            return $pdf->Output('Invoice_'.$id.'.pdf','S');
        }
    }
}
?>