<?php
define('UPGRADE',true);

include('../../defaults.php');
include('../includes/functions.php');
include('../includes/functions_upgrade.php');

if(is_writable(PMDROOT.CACHE_PATH)) {
    unlink_files(PMDROOT.CACHE_PATH,true);
}

if(!defined('DB_CHARSET')) {
    define('DB_CHARSET','utf8');
}

$PMDR->loadLanguage(array());

if(md5($_SESSION['login'].$_SESSION['pass']) != $_SESSION['import_hash']) {
    redirect(BASE_URL.'/install/upgrade/index.php');
}

$version = $_SESSION['version'];
$upgrade_version = include(PMDROOT.'/includes/version.php');

if(!isset($_POST['action'])) {
    $queue = generateUpgradeQueue($version);
    $queue_count = count($queue);
    $_SESSION['upgrade_queue'] = $queue;
}

if(isset($_POST['action'])) {
    header('Content-Type: application/json; charset='.CHARSET);
    $queue = $_SESSION['upgrade_queue'];
    $queue_current = count($queue);
    if($queue_item = array_shift($queue)) {
        $message = processQueueItem($queue_item);
    } else {
        $message = 'Updating version number';
        $db->Execute("UPDATE ".T_SETTINGS." SET value='".$upgrade_version."' WHERE varname='pmd_version'");
    }

    $_SESSION['upgrade_queue'] = $queue;
    session_write_close();
    // Sleeping here is required for some servers who block quick accesses (DoS prevention)
    usleep(1000000);

    $return = array(
        'percent'=>floor(((intval($_POST['queue_count'])-$queue_current)/intval($_POST['queue_count']))*100),
        'queue_count'=>intval($_POST['queue_count']),
        'message'=>$message
    );

    echo json_encode($return);
    exit();
}

$template_content = '<h3>Upgrade</h3>
<p>The upgrade process will automatically update phpMyDirectory to the latest version.</p>';

if(isset($_GET['action']) AND $_GET['action'] == 'complete') {
    $template_content .= '
    <div class="alert alert-success">
        <h4>Complete</h4>
        <p>The upgrade to version '.$upgrade_version.' has been completed.  Please delete the /install/ folder.</p>
        <p>
            <a class="btn btn-default" href="../../">View Home Page</a>
            <a class="btn btn-default" href="../../cp/">View Control Panel</a>
        </p>
    </div>';
} else {
    $template_content .= '
    <p>The upgrade process may take several minutes.</p>
    <div class="alert alert-warning">Do not close your browser until the upgrade process completes.</div>
    <script type="text/javascript">
    var upgradeOnComplete = function(data) {
        $("#percent_number").html(data.percent+"%");
        $("#message").html(data.message+"..");
        $("#percent").css("width",data.percent+"%");
        if(data.percent == 100) {
            setTimeout(function() {
                window.location.replace("'.BASE_URL.'/install/upgrade/upgrade.php?action=complete");
            },1000);
        } else {
            upgradeStart(data.queue_count);
        }
    };

    var upgradeStart = function(queue_count) {
        $.ajax({url:"'.BASE_URL.'/install/upgrade/upgrade.php", type:"POST", data: ({ action: \'upgrade\', queue_count: queue_count }), success: upgradeOnComplete, dataType: "json", cache: false });
    };

    upgradeStart('.$queue_count.');
    </script>
    <h4 style="color: #666;"><img src="../images/loading.gif"/> <span id="message">Starting Upgrade..</span></h4>
    <div class="progress progress-striped active pull-left" style="width: 400px; margin-right: 10px;">
        <div id="percent" class="progress-bar progress-bar-info" style="width: 0%;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div id="percent_number" class="label label-default pull-left">0%</div>';
}

include('../includes/template_setup.php');
?>