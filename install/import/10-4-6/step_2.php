<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 2 of 7</h3><p>';

// Locations
$locations = $PMDR->get('Locations');
$batcher = $PMDR->get('Database_Batcher');

if(!isset($_GET['action'])) {
    echo 'Importing first level locations.... '; ob_flush();
    $db->Execute("DELETE FROM ".T_LOCATIONS);
    $db->Execute("INSERT INTO ".T_LOCATIONS." (id,title,left_,right_,level,parent_id) VALUES (1,'ROOT',0,1,0,NULL)");
    if(!in_array('importer_original_id',$db->MetaColumnNames(T_LOCATIONS))) {
        $db->Execute("ALTER IGNORE TABLE ".T_LOCATIONS." ADD importer_original_id varchar(55) NOT NULL default '0'");
    }
    echo 'done.<br /> Please wait... '; ob_flush(); sleep(2);
    echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=batching&current=0&source=".OLD_T_LOC_ONE."\"; } window.onload = runAgain;</script>";
}

if($_GET['action'] == 'batching') {
    // Load the batch and see where we are
    $batcher->loadBatch($_GET['source'], $_GET['current']);
    // Set the batcher query (to get the data)
    $batcher->query = "SELECT * FROM ".$batcher->source." ORDER BY loc_id ASC LIMIT ?,?";
    // Get the records within the current batch
    $records = $batcher->getBatch();
    // Cycle through the records in the batch
    foreach($records as $record) {
        $record['importer_original_id'] = 'one_'.$record['loc_id'];
        $locations->insert($record);
        echo 'Location '.$record['loc_id'].' imported.<br />'; ob_flush();
    }
    // Unload the batch and redirect if needed
    echo $batcher->unloadBatch();
} elseif($_GET['action'] == 'complete') {
    echo 'Done!</p><a class="btn btn-default" href="step_2_1.php">Continue to step 2.1</a>';
}

include('../../template/footer.tpl');

// re-do counts
?>