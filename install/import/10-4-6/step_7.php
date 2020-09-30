<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 7 of 7</h3><p>';

echo 'Importing reviews.... '; ob_flush(); sleep(1);
// Reviews
$db->Execute("TRUNCATE ".T_REVIEWS);
$db->Execute("INSERT INTO ".T_REVIEWS." (id,status,date,listing_id,title,review) SELECT id, IF(!STRCMP(status,'yes'),'active','pending') AS new_status,date,company,CONCAT(SUBSTRING(review,1,15),'...') as new_title,review FROM ".OLD_T_REVIEWS);

echo 'done.<br />Importing ratings.... '; ob_flush(); sleep(1);
// Ratings
$db->Execute("TRUNCATE ".T_RATINGS);
$db->Execute("INSERT INTO ".T_RATINGS." (id,listing_id,rating,ip_address) SELECT id,selector,rating,ip_internal FROM ".OLD_T_RATINGS);

echo 'done.<br />Importing payment gateways.... '; ob_flush(); sleep(1);
// Gateways
$old_gateways = $db->GetAssoc("SELECT config_key, config_value FROM ".OLD_T_GATEWAYS);
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='PayPal'",array(serialize(array('paypal_email'=>$old_gateways['paypal_email'],'paypal_currency'=>$old_gateways['paypal_currency']))));
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='TwoCheckout'",array(serialize(array('2co_word'=>$old_gateways['2co_word'],'2co_id'=>$old_gateways['2co_id']))));
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='AuthorizeNetAIM'",array(serialize(array('authaim_tran_key'=>$old_gateways['auth_tran_key'],'authaim_login'=>$old_gateways['auth_login'],'authaim_currency'=>$old_gateways['auth_currency']))));
$db->Execute("UPDATE ".T_GATEWAYS." SET settings=? WHERE id='WorldPay'",array(serialize(array('worldpay_currency'=>$old_gateways['worldpay_currency'],'worldpay_pw'=>$old_gateways['worldpay_pw'],'worldpay_id'=>$old_gateways['worldpay_id']))));

echo 'done.'; ob_flush(); sleep(1);

// Cleanup
if(in_array('importer_original_id',$db->MetaColumnNames(T_LOCATIONS))) {
    $db->Execute("ALTER IGNORE TABLE ".T_LOCATIONS." DROP importer_original_id");
}

echo '<br><br>Import completed.  Delete /install/</p><a class="btn btn-success" href="../../../index.php">View Directory Home Page</a>';

include('../../template/footer.tpl');
?>