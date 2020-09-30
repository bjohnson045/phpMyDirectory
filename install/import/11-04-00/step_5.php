<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 5 of 7</h3><p>';

echo 'Importing banners....'; ob_flush();
// Banners
$db->Execute("TRUNCATE ".T_BANNERS);
$db->Execute("INSERT INTO ".T_BANNERS." (id,listing_id,type_id,extension,impressions,date_last_displayed) SELECT id, list_id, type_id, extension, impressions, date_last_displayed FROM ".OLD_T_BANNERS);

$handle = opendir(OLD_PMDROOT.'user_media/banner/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        copy(OLD_PMDROOT.'user_media/banner/'.$file,BANNERS_PATH.$file);
    }
}
closedir($handle);
$db->Execute("UPDATE ".T_BANNERS." b,".T_LISTINGS." l SET b.status=IF(l.status='active','active','pending') WHERE b.listing_id=l.id");

echo 'done.<br />Importing documents....'; ob_flush();
// Documents
$db->Execute("TRUNCATE ".T_DOCUMENTS);
$db->Execute("INSERT INTO ".T_DOCUMENTS." (id,listing_id,title,date,description,extension) SELECT num,firmselector,item,date,message,ext FROM ".OLD_T_DOCUMENTS);
$handle = opendir(OLD_PMDROOT.'user_media/documents/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        copy(OLD_PMDROOT.'user_media/documents/'.$file,DOCUMENTS_PATH.$file);
    }
}
closedir($handle);
echo 'done.<br />Importing images....'; ob_flush();
// Images
$db->Execute("TRUNCATE ".T_IMAGES);
$db->Execute("INSERT INTO ".T_IMAGES." (id,listing_id,title,date,description,extension) SELECT num,firmselector,item,date,message,ext FROM ".OLD_T_IMAGES);

$handle = opendir(OLD_PMDROOT.'user_media/gallery/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        copy(OLD_PMDROOT.'user_media/gallery/'.$file,IMAGES_PATH.$file);
    } elseif(preg_match('/^\d+-small\.[a-zA-Z]{3,4}$/',$file)) {
        copy(OLD_PMDROOT.'user_media/gallery/'.$file,IMAGES_THUMBNAILS_PATH.str_replace('-small','',$file));
    }
}
closedir($handle);

echo 'done.</p>'; ob_flush();

echo '<a class="btn btn-default" href="step_6.php">Continue to step 6 <i class="glyphicon glyphicon-chevron-right"></i></a>';

include('../../template/footer.tpl');
?>