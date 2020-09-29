<?php
function presync_1_3_5() {
    global $db;
    $db->RenameColumn(T_EMAIL_TEMPLATES,'fromaddress','from_address',true);
    $db->RenameColumn(T_EMAIL_TEMPLATES,'fromname','from_name',true);
}

function postsync_1_3_5() {
    global $PMDR, $db;
    $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='meta_title_default'",array($db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='title'")));
    $db->Execute("UPDATE ".T_DISCOUNT_CODES." SET gateway_ids=REPLACE(gateway_ids,'Moneybookers','Skrill')");
    $db->Execute("UPDATE ".T_GATEWAYS." SET id=REPLACE(id,'Moneybookers','Skrill')");
    $db->Execute("UPDATE ".T_GATEWAYS_LOG." SET gateway=REPLACE(gateway,'Moneybookers','Skrill')");
    $db->Execute("UPDATE ".T_INVOICES." SET gateway_id=REPLACE(gateway_id,'Moneybookers','Skrill')");
    $db->Execute("UPDATE ".T_ORDERS." SET gateway_id=REPLACE(gateway_id,'Moneybookers','Skrill')");
    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET gateway_ids=REPLACE(gateway_ids,'Moneybookers','Skrill')");
    $db->Execute("UPDATE ".T_TRANSACTIONS." SET gateway_id=REPLACE(gateway_id,'Moneybookers','Skrill')");
    $db->Execute("UPDATE ".T_INVOICES." SET date_paid=FROM_UNIXTIME(UNIX_TIMESTAMP(date_paid)-".$PMDR->get('Dates')->offset.") WHERE date_paid!='0000-00-00 00:00:00' AND date_paid IS NOT NULL");
    $db->Execute("UPDATE ".T_CLASSIFIEDS." SET date=FROM_UNIXTIME(UNIX_TIMESTAMP(date)-".$PMDR->get('Dates')->offset.")");
    $db->Execute("UPDATE ".T_CLASSIFIEDS." SET expire_date=FROM_UNIXTIME(UNIX_TIMESTAMP(expire_date)-".$PMDR->get('Dates')->offset.")");
}
?>