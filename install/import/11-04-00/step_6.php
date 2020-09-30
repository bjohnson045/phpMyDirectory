<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 6 of 7</h3><p>';

$batcher = $PMDR->get('Database_Batcher');

// Offers
if(!isset($_GET['action'])) {
    echo 'Importing products.... '; ob_flush();
    $db->Execute("DELETE FROM ".T_CLASSIFIEDS);
    $db->Execute("DELETE FROM ".T_CLASSIFIEDS_IMAGES);
    $db->Execute("INSERT INTO ".T_CLASSIFIEDS." (id,listing_id,title,date,description,price,expire_date,www,buttoncode)
              SELECT num,firmselector,item,date,message,price,expire_date,www,buttoncode FROM ".OLD_T_OFFERS);
    echo 'done.<br /> Please wait... '; ob_flush(); sleep(3);
    echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=batching&current=0&source=".T_CLASSIFIEDS."\"; } window.onload = runAgain;</script>";
}

// Convert classifieds friendly URL over.
if($_GET['action'] == 'batching') {
    // Load the batch and see where we are
    $batcher->loadBatch($_GET['source'], $_GET['current']);
    // Set the batcher query (to get the data)
    $batcher->query = "SELECT * FROM ".$batcher->source." LIMIT ?,?";
    // Get the records within the current batch
    $records = $batcher->getBatch();
    // Cycle through the records in the batch
    foreach($records as $record) {
        $db->Execute("UPDATE ".T_CLASSIFIEDS." SET friendly_url = ? WHERE id=? AND friendly_url=''",array(Strings::rewrite($record['title']),$record['id']));
        if($file = find_file(OLD_PMDROOT.'/user_media/products/'.$record['id'].'.*')) {
            $db->Execute("INSERT INTO ".T_CLASSIFIEDS_IMAGES." SET classified_id=?, extension=?",array($record['id'],get_file_extension($file)));
            $image_id = $db->Insert_ID();
            $path_info = pathinfo($file);
            copy($file,CLASSIFIEDS_PATH.$record['id'].'-'.$image_id.'.'.get_file_extension($file));
            copy(str_replace('.'.get_file_extension($file),'-small.'.get_file_extension($file),$file),CLASSIFIEDS_THUMBNAILS_PATH.$record['id'].'-'.$image_id.'.'.get_file_extension($file));
            echo 'Copied images for offer '.$record['id'].'. ('.$record['id'].'-'.$image_id.'.'.get_file_extension($file).')<br />'; ob_flush();
        }
    }
    // Unload the batch and redirect if needed
    echo $batcher->unloadBatch();
} elseif($_GET['action'] == 'complete') {
    echo 'Done!</p><a class="btn btn-default" href="step_7.php">Continue to step 7 <i class="glyphicon glyphicon-chevron-right"></i></a>';
}

include('../../template/footer.tpl');
?>