<?php
function presync_1_1_7() {
    global $db;
    $db->DropColumn(T_EMAIL_TEMPLATES,'variables');
    $db->DropColumn(T_EMAIL_TEMPLATES,'template_group');
    $db->RenameColumn(T_EMAIL_TEMPLATES,'exclude_user','disable',true);
    $db->Execute("TRUNCATE TABLE ".T_PLUGINS);
    $db->DropColumn(T_PLUGINS,array('eventname','phpcode','module_id','runtime_order','title'));
    $db->RenameColumn(T_ORDERS,'override_suspension','suspend_overdue_days',true);
    $db->DropColumn(T_ORDERS,'amount');
    $db->RenameColumn(T_PRODUCTS_PRICING,'auto_activate','activate',true);
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."modules");
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."module_code");
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."plugin_events");
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."tags");
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."tags_lookup");
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."category");
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."settings_groups");
}

function postsync_1_1_7() {
    global $PMDR, $db;
    $db->RenameColumn(T_SITE_LINKS,'requires_active_listing','requires_active_product',true);
    if(isset($_SESSION['field_fax']) AND is_numeric($_SESSION['field_fax'])) {
        $db->Execute("UPDATE ".T_LISTINGS." SET fax = custom_".$_SESSION['field_fax']);
    }

    $db->Execute("UPDATE ".T_SETTINGS." SET value='p,ul,ol,li,strong,em,u,span,hr,div' WHERE varname='allowed_html_tags' AND value=''");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='style' WHERE varname='allowed_html_attributes' AND value=''");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname = 'image_logo_thumb_crop' WHERE varname='image_logo_thumb_scale'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname = 'gallery_thumb_crop' WHERE varname='gallery_scale'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname = 'offer_thumb_crop' WHERE varname='offer_thumb_scale'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='login_module' WHERE varname='usershare'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='login_module_db_host' WHERE varname='usershare_db_host'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='login_module_db_name' WHERE varname='usershare_db_name'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='login_module_db_user' WHERE varname='usershare_db_user'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='login_module_db_password' WHERE varname='usershare_db_pass'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='login_module_db_prefix' WHERE varname='usershare_db_prefix'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='login_module_registration_url' WHERE varname='usershare_registration_url'");

    if($db->ColumnExists(T_EMAIL_TEMPLATES,'fromaddress')) {
        $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET fromaddress=? WHERE fromaddress='{admin_email}'",array($PMDR->getConfig('admin_email')));
    }
    $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET id='order_status_change' WHERE id='order_status_change_notification'");
    $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET id='order_submitted' WHERE id='orders_add_listing'");
    $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET id='admin_order_submitted' WHERE id='admin_orders_add_listing'");

    if(!in_array('next_invoice_date',$db->MetaColumnNames(T_ORDERS))) {
        $db->Execute("UPDATE ".T_ORDERS." SET next_invoice_date = next_due_date WHERE period_count != 0 AND amount_recurring != 0.00");
    }

    if($db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='listing_suspend'") AND ($suspend_days = $db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='listing_suspend_days'")) != '') {
        $db->Execute("UPDATE ".T_PRODUCTS." SET suspend_overdue_days=?",array($suspend_days));
        $db->Execute("UPDATE ".T_ORDERS." SET suspend_overdue_days=?",array($suspend_days));
    } else {
        $db->Execute("UPDATE ".T_PRODUCTS." SET suspend_overdue_days=0");
        $db->Execute("UPDATE ".T_ORDERS." SET suspend_overdue_days=0");
    }

    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET activate = 'payment' WHERE activate=1");
    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET activate = 'approved' WHERE activate=0");

    $db->Execute("UPDATE ".T_LISTINGS." SET status='suspended' WHERE status='pending'");

    if(!in_array('id',$db->MetaColumnNames(T_GATEWAYS))) {
        $db->Execute("UPDATE ".T_GATEWAYS." SET id='FirstDataGlobal', display_name='FirstDataGlobal' WHERE id = 'LinkPointAPI'");
        $db->Execute("UPDATE ".T_GATEWAYS." SET id='SagePay', display_name='SagePay' WHERE id = 'ProtX'");
    }

    @unlink(PMDROOT.'/includes/cron/cron_listing_status_changes.php');

    if(!in_array('status',$db->MetaColumnNames(T_BANNERS))) {
        $db->Execute("UPDATE ".T_BANNERS." b, ".T_LISTINGS." l SET b.status=l.status WHERE b.listing_id=l.id");
    }

    if(!in_array('type',$db->MetaColumnNames(T_BANNER_TYPES))) {
        $db->Execute("UPDATE ".T_BANNER_TYPES." SET type='image'");
    }
}
?>