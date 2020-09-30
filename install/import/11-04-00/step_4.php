<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 4 of 7</h3><p>';

// Categories
$categories = $PMDR->get('Categories');
$batcher = $PMDR->get('Database_Batcher');

if(!isset($_GET['action'])) {
    echo 'Importing categories.... '; ob_flush();
    $db->Execute("DELETE FROM ".T_CATEGORIES);
    $db->Execute("INSERT INTO ".T_CATEGORIES." (id,title,description_short,description,keywords,meta_title,friendly_url,friendly_url_path,friendly_url_path_hash,level,left_,right_,impressions,ip,meta_description,meta_keywords,hidden)
              SELECT id,title,description,description,keywords,'',friendly_url,'','',level,left_,right_,hits,ip,description,keywords,0 FROM ".OLD_T_CATEGORIES);
    echo 'done.<br /> Please wait... '; ob_flush(); sleep(3);
    echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=batching&current=0&source=".T_CATEGORIES."\"; } window.onload = runAgain;</script>";
}

if($_GET['action'] == 'batching') {
    // Load the batch and see where we are
    $batcher->loadBatch($_GET['source'], $_GET['current']);
    // Set the batcher query (to get the data)
    $batcher->query = "SELECT * FROM ".$batcher->source." ORDER BY left_ LIMIT ?,?";
    // Get the records within the current batch
    $records = $batcher->getBatch();
    // Cycle through the records in the batch
    foreach($records as $record) {
        $categories->updateFriendlyPath($record['id']);
        echo 'Category '.$record['id'].' friendly URL path updated.<br />'; ob_flush();
    }
    // Unload the batch and redirect if needed
    echo $batcher->unloadBatch();
} elseif($_GET['action'] == 'complete') {
    $categories->updateLanguageVariables();
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&amp;','&');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&#039;','\'');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&quot;','\"');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&gt;','>');");
    $db->Execute("UPDATE ".T_CATEGORIES." SET title=REPLACE(title,'&lt;','<');");
    echo 'Done!</p><a class="btn btn-default" href="step_5.php">Continue to step 5 <i class="glyphicon glyphicon-chevron-right"></i></a>';
}

include('../../template/footer.tpl');

// re-do counts
?>