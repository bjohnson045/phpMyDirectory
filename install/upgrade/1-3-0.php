<?php
function postsync_1_3_0() {
    global $db;
    $gateway_ids = $db->GetCol("SELECT id FROM ".T_GATEWAYS." WHERE enabled=1");
    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET gateway_ids='".implode(',',$gateway_ids)."'");

    $db->Execute("UPDATE ".T_PAGES." SET active=1");

    $db->Execute("UPDATE ".T_INVOICES." i, ".T_TRANSACTIONS." t SET i.gateway_id=t.gateway_id WHERE i.id=t.invoice_id");
    $db->Execute("UPDATE ".T_ORDERS." o, ".T_TRANSACTIONS." t SET o.gateway_id=t.gateway_id WHERE o.invoice_id=t.invoice_id");

    $db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&amp;','&');");
    $db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&#039;','\'');");
    $db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&quot;','\"');");
    $db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&gt;','>');");
    $db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&lt;','<');");

    $db->Execute("UPDATE ".T_LOCATIONS." SET title=REPLACE(title,'&amp;','&');");
    $db->Execute("UPDATE ".T_LOCATIONS." SET title=REPLACE(title,'&#039;','\'');");
    $db->Execute("UPDATE ".T_LOCATIONS." SET title=REPLACE(title,'&quot;','\"');");
    $db->Execute("UPDATE ".T_LOCATIONS." SET title=REPLACE(title,'&gt;','>');");
    $db->Execute("UPDATE ".T_LOCATIONS." SET title=REPLACE(title,'&lt;','<');");

    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&amp;','&');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&#039;','\'');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&quot;','\"');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&gt;','>');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&lt;','<');");
    $db->Execute("UPDATE ".T_REVIEWS_COMMENTS." SET status='active'");
    $db->Execute("UPDATE ".T_REVIEWS." r, ".T_RATINGS." ra SET ra.date=r.date WHERE r.rating_id=ra.id");
    $db->Execute("UPDATE ".T_RATINGS." SET date=NOW() WHERE date = '0000-00-00'");

    $db->Execute("UPDATE ".T_ORDERS." SET next_invoice_date='0000-00-00' WHERE amount_recurring=0.00 OR period_count=0");
}
?>