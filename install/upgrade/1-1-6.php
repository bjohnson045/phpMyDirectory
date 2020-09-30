<?php
function presync_1_1_6() {
    global $db;
    $db->DropColumn(T_LISTINGS,'first_payment_amount');
}

function postsync_1_1_6() {
    global $db;
    $gateways = array (
        '1'=>'PayPal',
        '2'=>'TwoCheckout',
        '3'=>'AuthorizeNetAIM',
        '4'=>'WorldPayJunior',
        '5'=>'OfflinePayment',
        '6'=>'LinkPointAPI',
        '7'=>'AuthorizeNetSIM',
        '8'=>'AuthorizeNetARB',
        '9'=>'WebMoney',
        '10'=>'viaKlix',
        '11'=>'PSIGateXML',
        '12'=>'ProtX',
        '13'=>'PayPalProUS',
        '14'=>'LinkPointConnect',
        '15'=>'GoogleCheckout',
        '16'=>'ClickBank',
        '17'=>'BankTransfer',
        '18'=>'MailInPayment',
        '19'=>'eWayShared',
        '20'=>'PayPoint',
        '21'=>'PayPalSubscriptions',
        '22'=>'MoneyBookers',
        '23'=>'AlertPay',
        '24'=>'Setcom',
        '25'=>'PayflowPro',
        '26'=>'Beanstream'
    );
    foreach($gateways AS $id=>$gateway) {
        $db->Execute("UPDATE ".T_ORDERS." SET gateway_id=? WHERE gateway_id=?",array($gateway,$id));
        $db->Execute("UPDATE ".T_INVOICES." SET gateway_id=? WHERE gateway_id=?",array($gateway,$id));
        $db->Execute("UPDATE ".T_TRANSACTIONS." SET gateway_id=? WHERE gateway_id=?",array($gateway,$id));
    }

    $db->Execute("UPDATE ".T_ORDERS." SET gateway_id='' WHERE gateway_id=0");
    $db->Execute("UPDATE ".T_INVOICES." SET gateway_id='' WHERE gateway_id=0");
    $db->Execute("UPDATE ".T_TRANSACTIONS." SET gateway_id='' WHERE gateway_id=0");
}
?>