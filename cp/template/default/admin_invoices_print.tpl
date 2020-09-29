<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Language" content="en-us">
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['charset']; ?>">
    <title><?php echo $lang['admin_invoices_invoice']; ?></title>
    <style>
    <!--
    html, body { font-size: 8pt; font-family: Tahoma; }
    table, td, tr { font-size: 8pt; font-family: Tahoma; }
    .invoice_amount_header { background-color: #EFEFEF; font-weight: bold; padding: 5px; }
    .invoice_amount_rows { padding: 5px; }
    .wrapper-table { border: 1px solid #C0C0C0; padding: 10px 10px 50px 10px; margin-top: 50px; }
    .header_label { font-weight: bold; padding-right: 5px; }
    .invoice_title { font-size: 18pt; padding: 20px 0px 20px 10px; }
    -->
    </style>
</head>
<body onload="setTimeout('window.print();',1000);">
<table align="center" border="0" width="600" cellspacing="0" cellpadding="0" class="wrapper-table">
    <tr>
        <?php if(isset($logo_url)) { ?>
            <td><img src="<?php echo $logo_url; ?>" /></td>
        <?php } else { ?>
        <td class="invoice_title">
            <?php echo $this->escape($invoice_company); ?>
        </td>
        <?php } ?>
        <td align="right">
            <table border="0" align="right" cellspacing="0" cellpadding="0" style="padding-right: 10px;">
                <tr>
                    <td align="right" class="header_label"><?php echo $lang['admin_invoices_id']; ?>:</td>
                    <td><?php echo $id; ?></td>
                </tr>
                <tr>
                    <td align="right" class="header_label"><?php echo $lang['admin_invoices_date']; ?>:</td>
                    <td><?php echo $date; ?></td>
                </tr>
                <tr>
                    <td align="right" class="header_label"><?php echo $lang['admin_invoices_date_due']; ?>:</td>
                    <td><?php echo $date_due; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td colspan="2">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="padding-top: 20px;">
                <tr>
                    <td width="50%" valign="top">
                        <b><?php echo $lang['admin_invoices_invoiced_to']; ?>:</b><br>
                        <?php echo $this->escape($user_first_name); ?> <?php echo $this->escape($user_last_name); ?><br>
                        <?php echo $this->escape($user_organization); ?><br>
                        <?php echo $this->escape($user_address1); ?><br>
                        <?php if(!empty($user_address2)) { ?><?php echo $this->escape($user_address2); ?><br><?php } ?>
                        <?php echo $this->escape($user_city); ?>, <?php echo $this->escape($user_state); ?> <?php echo $this->escape($user_zip); ?><br>
                        <?php echo $this->escape($user_country); ?>
                    </td>
                    <td valign="top">
                        <b><?php echo $lang['admin_invoices_pay_to']; ?>:</b><br>
                        <?php echo $this->escape($invoice_address); ?>
                    </td>
                    <td align="right" valign="top">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" class="table_border" style="border: 1px solid #C0C0C0; margin: 40px 0px 10px 0px;">
                <tr>
                    <td width="70%" class="invoice_amount_header" valign="top"><?php echo $lang['admin_invoices_description']; ?></td>
                    <td class="invoice_amount_header"><?php echo $lang['admin_invoices_total']; ?></td>
                </tr>
                <tr>
                    <td width="70%" class="invoice_amount_rows" valign="top"><?php echo $description; ?></td>
                    <td class="invoice_amount_rows"><?php echo format_number_currency($subtotal); ?></td>
                </tr>
                <tr>
                    <td class="invoice_amount_header" width="70%" align="right"><b><?php echo $lang['admin_invoices_subtotal']; ?>:</b></td>
                    <td class="invoice_amount_header"><?php echo format_number_currency($subtotal); ?></td>
                </tr>
                <?php if($discount_code) { ?>
                    <tr>
                        <td class="invoice_amount_header" width="70%" align="right"><b><?php echo $lang['admin_invoices_discount_code']; ?> (<?php echo $discount_code; ?>):</b></td>
                        <td class="invoice_amount_header">(<?php echo format_number_currency($discount_code_value); ?>)</td>
                    </tr>
                <?php } ?>
                <?php if($tax_rate != 0.00) { ?>
                <tr>
                    <td class="invoice_amount_header" width="70%" align="right"><b><?php echo $lang['admin_invoices_tax']; ?> (<?php echo $tax_rate; ?>%):</b></td>
                    <td class="invoice_amount_header"><?php echo format_number_currency($tax); ?></td>
                </tr>
                <?php } ?>
                <?php if($tax_rate2 != 0.00) { ?>
                <tr>
                    <td class="invoice_amount_header" width="70%" align="right"><b><?php echo $lang['admin_invoices_tax']; ?> (<?php echo $tax_rate2; ?>%):</b></td>
                    <td class="invoice_amount_header"><?php echo format_number_currency($tax2); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="invoice_amount_header" width="70%" align="right"><b><?php echo $lang['admin_invoices_total']; ?>:</b></td>
                    <td class="invoice_amount_header"><?php echo format_number_currency($total); ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td><b><?php echo $lang['admin_transactions']; ?></b></td>
    </tr>
    <tr>
        <td colspan="2">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" class="table_border" style="border: 1px solid #C0C0C0; margin-top: 10px;">
                <tr>
                    <td width="20%" class="invoice_amount_header" valign="top"><?php echo $lang['admin_transactions_date']; ?></td>
                    <td width="20%" class="invoice_amount_header"><?php echo $lang['admin_invoices_payment_method']; ?></td>
                    <td width="20%" class="invoice_amount_header"><?php echo $lang['admin_transactions_id']; ?></td>
                    <td width="20%" class="invoice_amount_header"><?php echo $lang['admin_transactions_amount']; ?></td>
                </tr>
                <?php if(!$transactions) { ?>
                    <tr><td class="invoice_amount_rows" colspan="4" align="center"><?php echo $lang['admin_transactions_none']; ?></td></tr>
                <?php } else { ?>
                    <?php foreach($transactions as $transaction) { ?>
                        <tr>
                            <td class="invoice_amount_rows"><?php echo $transaction['date']; ?></td>
                            <td class="invoice_amount_rows"><?php echo $transaction['gateway_name']; ?></td>
                            <td class="invoice_amount_rows"><?php echo $transaction['transaction_id']; ?></td>
                            <td class="invoice_amount_rows"><?php echo format_number_currency($transaction['amount']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <td colspan="3" class="invoice_amount_header" width="80%" align="right"><b><?php echo $lang['admin_invoices_balance']; ?>:</b></td>
                    <td class="invoice_amount_header"><?php echo format_number_currency($balance); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<center><a href="javascript: self.close();"><?php echo $lang['admin_invoices_close_window']; ?></a></center>
</body>
</html>