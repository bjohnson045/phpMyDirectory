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
    $db->Execute("INSERT INTO ".T_CLASSIFIEDS." (id,listing_id,title,date,description,price,www,buttoncode)
              SELECT num,firmselector,item,date,message,price,www,paypal FROM ".OLD_T_OFFERS);
    echo 'done.<br /> Please wait... '; ob_flush(); sleep(3);
    echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=batching&current=0&source=".T_CLASSIFIEDS."\"; } window.onload = runAgain;</script>";
}

if($_GET['action'] == 'batching') {
    // Load the batch and see where we are
    $batcher->loadBatch($_GET['source'], $_GET['current']);
    // Set the batcher query (to get the data)
    $batcher->query = "SELECT * FROM ".$batcher->source." LIMIT ?,?";
    // Get the records within the current batch
    $records = $batcher->getBatch();
    // Cycle through the records in the batch
    foreach($records as $record) {
        $period = $db->GetOne("SELECT period FROM ".OLD_T_OFFERS." WHERE num=?",array($record['id']));
        $db->Execute("UPDATE ".T_CLASSIFIEDS." SET expire_date=? WHERE id=?",array(date('Y-m-d',strtotime($record['date']) + $period*86400),$record['id']));
        $db->Execute("UPDATE ".T_CLASSIFIEDS." SET friendly_url = ? WHERE id=? AND friendly_url=''",array(Strings::rewrite($record['title']),$record['id']));
        if($file = find_file(OLD_PMDROOT.'offer/'.$record['id'].'.*')) {
            $db->Execute("INSERT INTO ".T_CLASSIFIEDS_IMAGES." SET classified_id=?, extension=?",array($record['id'],get_file_extension($file)));
            $path_info = pathinfo($file);
            copy($file,CLASSIFIEDS_PATH.$path_info['basename']);
            @copy(preg_replace('/(\d+)\./','$1-small.',$file),CLASSIFIEDS_THUMBNAILS_PATH.$path_info['basename']);
        }
        echo 'Copied images for offer '.$record['id'].'. <br />'; ob_flush();
    }
    // Unload the batch and redirect if needed
    echo $batcher->unloadBatch();
} elseif($_GET['action'] == 'complete') {
    echo 'Done!</p><a class="btn btn-default" href="step_7.php">Continue to step 7</a>';
}

include('../../template/footer.tpl');
?>