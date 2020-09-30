<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 5 of 7</h3><p>';

$config = $db->GetAssoc("SELECT config_key, config_value FROM ".OLD_T_SETTINGS);

echo 'Importing banners....'; ob_flush();
// Banners
$db->Execute("TRUNCATE ".T_BANNERS);
$handle = opendir(OLD_PMDROOT.'banner/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        $banner_parts = explode('.',$file);
        $db->Execute("INSERT INTO ".T_BANNERS." SET listing_id=?, type_id=?, extension=?",array($banner_parts[0],2,$banner_parts[1]));
        copy(OLD_PMDROOT.'banner/'.$file,BANNERS_PATH.$db->Insert_ID().'.'.$banner_parts[1]);
    }
}
closedir($handle);
$handle = opendir(OLD_PMDROOT.'banner2/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        $banner_parts = explode('.',$file);
        $db->Execute("INSERT INTO ".T_BANNERS." SET listing_id=?, type_id=?, extension=?",array($banner_parts[0],1,$banner_parts[1]));
        copy(OLD_PMDROOT.'banner2/'.$file,BANNERS_PATH.$db->Insert_ID().'.'.$banner_parts[1]);
    }
}
closedir($handle);
$db->Execute("UPDATE ".T_BANNERS." b LEFT JOIN ".T_LISTINGS." l ON b.listing_id=l.id SET b.status=IF(l.status='active','active',IF(b.listing_id IS NULL,'active','pending'))");

echo 'done.<br />Importing documents....'; ob_flush();
// Documents
$db->Execute("TRUNCATE ".T_DOCUMENTS);
$db->Execute("INSERT INTO ".T_DOCUMENTS." (id,listing_id,title,date,description,extension) SELECT num,firmselector,item,date,message,ext FROM ".OLD_T_DOCUMENTS);
$handle = opendir(OLD_PMDROOT.'documents/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        copy(OLD_PMDROOT.'documents/'.$file,DOCUMENTS_PATH.$file);
    }
}
closedir($handle);
echo 'done.<br />Importing images....'; ob_flush();
// Images
$db->Execute("TRUNCATE ".T_IMAGES);
$db->Execute("INSERT INTO ".T_IMAGES." (id,listing_id,title,date,description) SELECT num,firmselector,item,date,message FROM ".OLD_T_IMAGES);

$handle = opendir(OLD_PMDROOT.'gallery/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        $image_parts = explode('.',$file);
        $db->Execute("UPDATE ".T_IMAGES." SET extension=? WHERE id=?",array($image_parts[1],$image_parts[0]));
        copy(OLD_PMDROOT.'gallery/'.$file,IMAGES_PATH.$file);
        $PMDR->get('Image_Handler')->process(OLD_PMDROOT.'gallery/'.$file,IMAGES_THUMBNAILS_PATH.$file,array('width'=>100,'enlarge'=>true));
    }
}
closedir($handle);

echo 'done.'; ob_flush();

echo '</p><a class="btn btn-default" href="step_6.php">Continue to step 6</a>';

include('../../template/footer.tpl');
?>