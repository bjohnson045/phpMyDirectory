<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 3 of 7</h3><p>';

// Categories
$categories = $PMDR->get('Categories');
$batcher = $PMDR->get('Database_Batcher');

if(!isset($_GET['action'])) {
    echo 'Importing categories.... '; ob_flush();
    $db->Execute("DELETE FROM ".T_CATEGORIES);
    $db->Execute("INSERT INTO ".T_CATEGORIES." (id,title,left_,right_,level,parent_id) VALUES (1,'ROOT',0,1,0,NULL)");
    echo 'done.<br /> Please wait... '; ob_flush(); sleep(2);
    echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=batching&current=0&source=".OLD_T_CATEGORIES."\"; } window.onload = runAgain;</script>";
}

if($_GET['action'] == 'batching') {
    // Load the batch and see where we are
    $batcher->loadBatch($_GET['source'], $_GET['current']);
    // Set the batcher query (to get the data)
    $batcher->query = "SELECT * FROM ".$batcher->source." ORDER BY cat_id ASC LIMIT ?,?";
    // Get the records within the current batch
    $records = $batcher->getBatch();

    if($db->GetRow("SELECT * FROM ".OLD_T_CATEGORIES." WHERE cat_id=1")) {
        $increment = true;
    } else {
        $increment = false;
    }

    // Cycle through the records in the batch
    foreach($records as $record) {
        if($increment) {
            $record['cat_id'] += 1;
            if(!is_null($record['p']) AND $record['p'] != 'NULL') {
                $record['p'] += 1;
            }
        }
        if(is_null($record['p']) OR $record['p'] == 'NULL') {
            $record['p'] = 1;
        }
        if($db->GetRow("SELECT * FROM ".T_CATEGORIES." WHERE id=?",array($record['p']))) {
            $insert_record = array();
            $insert_record['placement_id'] = $record['p'];
            $insert_record['placement'] = 'subcategory';
            $insert_record['id'] = $record['cat_id'];
            $insert_record['importer_original_id'] = 'four_'.$record['loc_id'];
            $insert_record['impressions'] = $record['hits'];
            $insert_record['title'] = $record['title'];
            $categories->insert($insert_record);
            echo 'Category '.$record['cat_id'].' imported.<br />'; ob_flush();
        }
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
    echo 'Done!</p><a class="btn btn-default" href="step_4.php">Continue to step 4</a>';
}

include('../../template/footer.tpl');

// re-do counts
?>