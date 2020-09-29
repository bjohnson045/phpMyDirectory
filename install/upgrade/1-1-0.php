<?php
function presync_1_1_0() {
    global $db;
    $db->RenameColumn(T_LISTINGS,'ordering','priority',true);
    $db->Execute("TRUNCATE ".T_UPGRADES);
    $db->DropColumn(T_UPGRADES,array('type','type_id','original_id','new_id','new_period','new_period_count','amount','recurring_change','complete'));
}

function postsync_1_1_0() {
    global $db;
    if(in_array('pricing_id',$db->MetaColumnNames(T_LISTINGS))) {
        $db->Execute("
        INSERT IGNORE INTO
            ".T_ORDERS." (
                order_id,
                type,
                type_id,
                user_id,
                date,
                status,
                pricing_id,
                gateway_id,
                amount_recurring,
                period,
                period_count,
                next_due_date,
                subscription_id,
                suspend_overdue_days
            )
        SELECT 0, 'listing_membership', l.id, l.user_id, l.date, IF(l.status='active','active','pending'),
        l.pricing_id, l.gateway_id, l.amount, l.period, l.period_count, l.next_due_date, l.subscription_id, l.override_suspension
        FROM ".T_LISTINGS." l");

        $db->Execute("UPDATE ".T_ORDERS." SET order_id = 3294967295+type_id");

        $db->Execute("UPDATE ".T_ORDERS." o, ".T_LISTINGS." l SET
            o.pricing_id=l.pricing_id,
            o.gateway_id=l.gateway_id,
            o.amount_recurring=l.amount,
            o.period=l.period,
            o.period_count=l.period_count,
            o.next_due_date=l.next_due_date,
            o.subscription_id=l.subscription_id,
            o.suspend_overdue_days=l.override_suspension
        WHERE o.type_id=l.id AND o.type='listing_membership'");
    }

    $db->Execute("UPDATE ".T_ORDERS." o, ".T_PRODUCTS." p, ".T_PRODUCTS_PRICING." pp SET o.taxed=p.taxed WHERE o.pricing_id=pp.id AND pp.product_id=p.id");
    $db->DropColumn(T_LISTINGS,array('pricing_id','gateway_id','amount','period','period_count','next_due_date','subscription_id','override_suspension'));

    if($db->ColumnExists(T_PRODUCTS_PRICING,'priority')) {
        $db->Execute("UPDATE ".T_MEMBERSHIPS." m, ".T_PRODUCTS_PRICING." pp, ".T_PRODUCTS." p SET m.priority=pp.priority WHERE pp.product_id=p.id AND m.id=p.type_id AND type='listing_membership'");
    }

    $db->DropColumn(T_PRODUCTS_PRICING,'priority');

    $db->Execute("UPDATE ".T_SETTINGS." SET value='/usr/sbin/sendmail' WHERE varname='email_sendmail_path' AND value=''");
}
?>