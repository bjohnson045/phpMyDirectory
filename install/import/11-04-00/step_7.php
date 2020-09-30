<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 7 of 7</h3><p>';

// Reviews, Ratings, Modules, Modules Code, Plugins, Zip codes, FAQ, Discount Codes

echo 'Importing reviews.... '; ob_flush(); sleep(1);
// Reviews
$db->Execute("TRUNCATE ".T_REVIEWS);
$db->Execute("INSERT INTO ".T_REVIEWS." (id,status,date,listing_id,review,name) SELECT id, IF(status='on','active','pending') AS new_status,date,company,review,user FROM ".OLD_T_REVIEWS);

echo 'done.<br />Importing ratings.... '; ob_flush(); sleep(1);
// Ratings
$db->Execute("TRUNCATE ".T_RATINGS);
$db->Execute("INSERT INTO ".T_RATINGS." (id,listing_id,rating,ip_address) SELECT id,selector,rating,ip_internal FROM ".OLD_T_RATINGS);

// Zip codes
if($db->GetRow("SHOW TABLES LIKE '".$table_prefix."_zipdata'")) {
    $db->Execute("TRUNCATE ".T_ZIP_DATA);
    echo 'done.<br />Importing zip codes.... '; ob_flush(); sleep(1);
    $db->Execute("INSERT INTO ".T_ZIP_DATA." (zipcode,lon,lat) SELECT * FROM ".$table_prefix."_zipdata");
}

// FAQ
if($db->GetRow("SHOW TABLES LIKE '".OLD_T_FAQ_CATEGORIES."'")) {
    echo 'done.<br />Importing FAQ.... '; ob_flush(); sleep(1);
    $db->Execute("TRUNCATE ".T_FAQ_CATEGORIES);
    $db->Execute("TRUNCATE ".T_FAQ_QUESTIONS);
    $db->Execute("INSERT INTO ".T_FAQ_CATEGORIES." (id,title,ordering,active) SELECT id,title,sort_order,active FROM ".OLD_T_FAQ_CATEGORIES);
    $db->Execute("INSERT INTO ".T_FAQ_QUESTIONS." (id,category_id,question,answer,ordering,active) VALUES SELECT id,cat_id,question,answer,sort_order,active FROM ".OLD_T_FAQ_QUESTIONS);
}

// Discount codes - "deleted" field on old table?
if($db->GetRow("SHOW TABLES LIKE '".OLD_T_DISCOUNT_CODES."'")) {
    $db->Execute("TRUNCATE ".T_DISCOUNT_CODES);
    echo 'done.<br />Importing discount codes.... '; ob_flush(); sleep(1);
    $db->Execute("INSERT INTO ".T_DISCOUNT_CODES." (id,title,code,value,type,discount_type,date_start,date_expire,used_limit,used)
                  SELECT id, CONCAT('Discount Code - ',code),code,value,'onetime',IF(!STRCMP(type,'PERCENT'),'percentage','fixed') AS discount_type, NOW(), date_expire FROM ".OLD_T_DISCOUNT_CODES);
}

echo 'done.<br />Importing payment gateways.... '; ob_flush(); sleep(1);
// Gateways
$old_gateways = $db->GetAssoc("SELECT processor_name, settings FROM ".OLD_T_GATEWAYS." WHERE enabled=1");
$paypal_settings = unserialize($old_gateways['PayPal']);
$twocheckout_settings = unserialize($old_gateways['2Checkout']);
$authorize_settings = unserialize($old_gateways['Authorize.net']);
$worldpay_settings = unserialize($old_gateways['WorldPay']);
$secpay_settings = unserialize($old_gateways['SECPay']);
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='PayPal'",array(serialize(array('paypal_email'=>$paypal_settings['paypal_email'],'paypal_currency'=>$paypal_settings['paypal_currency']))));
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='TwoCheckout'",array(serialize(array('2co_word'=>$twocheckout_settings['2co_word'],'2co_id'=>$twocheckout_settings['2co_id']))));
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='AuthorizeNetAIM'",array(serialize(array('authaim_tran_key'=>$authorize_settings['auth_tran_key'],'authaim_login'=>$authorize_settings['auth_login'],'authaim_currency'=>$authorize_settings['auth_currency'],'authaim_require_cvv'=>$authorize_settings['auth_require_cvv']))));
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='WorldPay'",array(serialize(array('worldpay_currency'=>$worldpay_settings['worldpay_currency'],'worldpay_pw'=>$worldpay_settings['worldpay_pw'],'worldpay_id'=>$worldpay_settings['worldpay_id']))));
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='PayPoint'",array(serialize(array('paypoint_id'=>$secpay_settings['secpay_id'],'paypoint_require_cv2'=>$secpay_settings['secpay_require_cv2'],'paypoint_ssl_cb'=>$secpay_settings['secpay_ssl_cb'],'paypoint_currency'=>$secpay_settings['secpay_currency'],'paypoint_password'=>$secpay_settings['secpay_password']))));

echo 'done.<br />Importing banned urls, words and ip addresses.... '; ob_flush(); sleep(1);
// Banned urls, words, ip addresses
$urls = implode("\n",$db->GetCol("SELECT * FROM ".OLD_T_BANNED." WHERE type='url'"));
$words = implode("\n",$db->GetCol("SELECT * FROM ".OLD_T_BANNED." WHERE type='word'"));
$ips = implode("\n",$db->GetCol("SELECT * FROM ".OLD_T_BANNED." WHERE type='ip'"));
$db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='banned_words'",array($words));
$db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='banned_ips'",array($ips));
$db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='banned_urls'",array($urls));
unset($urls);
unset($words);
unset($ips);

echo 'done.<br />Importing invoices.... '; ob_flush(); sleep(1);
// Invoices -- set gateway ID by analyzing transaction ID or make it null value?
$db->Execute("TRUNCATE ".T_INVOICES);
$db->Execute("TRUNCATE ".T_TRANSACTIONS);
$db->Execute("INSERT INTO ".T_INVOICES." (id,user_id,type_id,type,description,date,date_due,subtotal,tax,tax_rate,tax2,tax_rate2,total,status,date_paid,created_sent,reminder_sent,overdue_1_sent,overdue_2_sent,overdue_3_sent) SELECT id,userid,listid,'listing_membership','',date,date,amount,'0.00','0.00','0.00','0.00',amount,IF(!STRCMP(status,'paid'),'paid','unpaid') AS new_status, IF(!STRCMP(status,'paid'),date,NULL) AS date_paid, 1, 1, 1, 1, 1 FROM ".OLD_T_INVOICES);
$db->Execute("UPDATE ".T_INVOICES." i, ".T_ORDERS." o, ".OLD_T_INVOICES." oi SET i.order_id=o.id WHERE o.type='listing_membership' AND oi.id=i.id AND o.type_id=oi.listid");
$db->Execute("INSERT INTO ".T_TRANSACTIONS." (user_id,gateway_id,transaction_id,invoice_id,date,description,amount) SELECT userid,'',transactionid,id,date,'',amount FROM ".OLD_T_INVOICES);

echo 'done.<br><br>'; ob_flush(); sleep(1);

echo 'Import completed.  Delete /install/</p><a class="btn btn-success" href="../../../index.php">View Directory Home Page</a>';

include('../../template/footer.tpl');
?>