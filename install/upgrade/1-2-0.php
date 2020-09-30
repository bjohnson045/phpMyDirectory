<?php
function presync_1_2_0() {
    global $db;
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."todo");
    $db->RenameTable(DB_TABLE_PREFIX."offers",T_CLASSIFIEDS,true);
    $db->RenameTable(DB_TABLE_PREFIX."offers_images",T_CLASSIFIEDS_IMAGES,true);
    $db->RenameColumn(T_MEMBERSHIPS,'offers_images_allow','classifieds_images_allow',true);
    $db->RenameColumn(T_MEMBERSHIPS,'offers_limit','classifieds_limit',true);
    $db->RenameColumn(T_CLASSIFIEDS_IMAGES,'offer_id','classified_id',true);
    $db->RenameColumn(T_LISTINGS,'offers_images_allow','classifieds_images_allow',true);
    $db->RenameColumn(T_LISTINGS,'offers_limit','classifieds_limit',true);
    $db->DropColumn(T_SEARCH_LOG,'sort_order');
    $db->RenameColumn(T_SEARCH_LOG,'sort_by','sort',true);
    $db->RenameColumn(T_LISTINGS,'www_status_old','www_status',true);
}

function postsync_1_2_0() {
    global $db;
    if(file_exists(PMDROOT.'/files/classifieds/')) {
        unlink_directory(PMDROOT.'/files/classifieds/');
    }
    if(file_exists(PMDROOT.'/files/offers/')) {
        rename(PMDROOT.'/files/offers/',PMDROOT.'/files/classifieds/');
    }

    $classifieds = $db->GetAll("SELECT id, title FROM ".T_CLASSIFIEDS);
    foreach($classifieds AS $classified) {
        $db->Execute("UPDATE ".T_CLASSIFIEDS." SET friendly_url=? WHERE id=?",array(Strings::rewrite($classified['title']),$classified['id']));
    }

    if(in_array('www_status_old',$db->MetaColumnNames(T_LISTINGS))) {
        $db->Execute("UPDATE ".T_LISTINGS." SET www_status=IF(www_status_old='dead',0,1), www_reciprocal=IF(www_status_old='valid',1,0)");
        $db->DropColumn(T_LISTINGS,'www_status_old');
    }
}
?>