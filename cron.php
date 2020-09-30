<?php
@set_time_limit(900);

define('PMD_SECTION', 'public');

define('MAINTENANCE_MODE', false);

// Get the document root
$document_root = dirname(str_replace('\\','/',__FILE__));

$cron_folder = $document_root.'/files/temp/cronlock/';

if(!is_writable(dirname($cron_folder))) {
    trigger_error('Temp folder is not writable: '.dirname($cron_folder),E_USER_ERROR);
    // Check of the "cronlock" folder exists, if not, try to create it, if so, try to delete it
} elseif(!is_dir($cron_folder) AND @mkdir($cron_folder)) {
    // Set the bad access flag
    $bad_access = false;
    // If CRON was accessed via an image tag
    if(isset($_GET['type']) AND $_GET['type'] == 'image') {
        include('./defaults.php');

        function sendGIF(){
            $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
            header('Content-Type: image/gif');
            header('Content-Length: '.strlen($img));
            header('Connection: Close');
            print $img;
        }
        // Flush all output, ignore user aborting, and send the GIF conent.
        ob_implicit_flush(true);
        @ignore_user_abort(true);
        sendGIF();
        ob_implicit_flush(false);
        ob_start();
        // If automatic CRON is disabled, we will ignore the actual processing
        if($PMDR->getConfig('disable_cron')) {
            $bad_access = true;
        }
    // If the CRON was accessed vis a javascript include
    } elseif(isset($_GET['type']) AND $_GET['type'] == 'javascript') {
        // Send the content-type, flush, and ignore user apporting.
        header("Content-Type: text/javascript");
        ob_implicit_flush(false);
        @ignore_user_abort(true);
        header('Content-Length: 0');
        header('Connection: Close');
        echo '';
        ob_implicit_flush(false);
        ob_start();
        include('./defaults.php');
        // If automatic CRON is disabled, we will ignore the actual processing
        if($PMDR->getConfig('disable_cron')) {
            $bad_access = true;
        }
    } else {
        include($document_root.'/defaults.php');

        // Get the MD5 value of the security key
        $check = md5(SECURITY_KEY);

        // Check the arguement whether using php or GET (prevents unauthorized running)
        if($check != $_SERVER['argv'][1] AND $check != $_GET['c']) {
            $bad_access = true;
        }
    }
    if(!$bad_access) {
        // Load the email template language for CRON jobs tha require emails to be sent
        $PMDR->loadLanguage(array('email_templates'));

        // Load all CRON jobs from the /cron/ folder and include them
        $cron_directory = PMDROOT.'/includes/cron/';
        if(is_dir($cron_directory)) {
            if ($dh = opendir($cron_directory)) {
                while(($file = readdir($dh)) !== false) {
                    if(get_file_extension($file) == 'php' AND substr($file,0,4) == 'cron') {
                        include(PMDROOT.'/includes/cron/'.$file);
                    }
                }
                closedir($dh);
            }
        }

        // Insert any found CRON jobs into the database and update any duplicates already found
        $query = "INSERT INTO ".T_CRON." (id, run_order, day, hour, minute, run_date, last_run_date) VALUES ";
        $query_parts = array();
        foreach($cron AS $key=>$value) {
            $query_parts[] = "(".$db->Clean($key).",".$db->Clean($value['run_order']).",".$db->Clean($value['day']).",".$db->Clean($value['hour']).",".$db->Clean($value['minute']).",DATE_ADD(CURDATE(), INTERVAL 1 HOUR),CURDATE())";
        }
        $query .= implode(',',$query_parts);
        $query .= " ON DUPLICATE KEY UPDATE run_order=VALUES(run_order), day=VALUES(day), hour=VALUES(hour), minute=VALUES(minute)";
        $PMDR->get('DB')->Execute($query);

        $current_timestamp = time();

        // Get all of the CRON jobs
        $jobs = $db->GetAll("SELECT id, day, hour, minute, run_date, last_run_date FROM ".T_CRON." ORDER BY run_order ASC");

        // If there is a difference in the folder vs. the database, delete the ones that do not belong in the database
        if(count($jobs) != count($cron)) {
            $db->Execute("DELETE FROM ".T_CRON." WHERE id NOT IN ('".implode('\',\'',array_keys($cron))."')");
        }

        // Loop through the cron jobs from the database.  We get only the ones that have a run date less than the current time
        foreach($jobs as $j) {
            // Build current and next run date data, run_date is the time we were suppose to run it, current_run_date is the actual time it got run in case of a delay
            if($j['minute'] != 0) {
                $j['next_run_date'] = date('Y-m-d H:i:00',$current_timestamp+(abs($j['minute'])*60));
                $j['current_run_date'] = date('Y-m-d H:i:00',$current_timestamp);
            } elseif($j['hour'] != 0) {
                $j['next_run_date'] = date('Y-m-d H:i:00',$current_timestamp+(abs($j['hour'])*3600)+($j['minute']*60));
                $j['current_run_date'] = date('Y-m-d H:00:00',$current_timestamp);
            } else {
                $j['next_run_date'] = date('Y-m-d H:i:00',$current_timestamp+(abs($j['day'])*86400)+($j['hour']*3600)+($j['minute']*60));
                $j['current_run_date'] = date('Y-m-d 00:00:00',$current_timestamp);
            }
            // If the run date of the CRON job is in the future, ignore this job for now.
            if(strtotime($j['run_date']) >= $current_timestamp) continue;
            // If the CRON job shouldn't exist, ignore it (it was already deleted above)
            if(!in_array($j['id'],array_keys($cron))) continue;

            // Update the cron job and set its next run date
            $db->Execute("UPDATE ".T_CRON." SET run_date=?, last_run_date=? WHERE id=?",array($j['next_run_date'],$j['current_run_date'],$j['id']));

            // Run the CRON job function located in the CRON file
            $cron_data = $j['id']($j);

            // Update the CRON log with the details of this CRON job
            $db->Execute("INSERT INTO ".T_CRON_LOG." (id, date, status, data) VALUES (?,?,?,?)",array($j['id'],$j['current_run_date'],(bool) $cron_data['status'],serialize(value($cron_data,'data'))));
        }
    }
    // Remove the "cronlock" folder
    rmdir($cron_folder);
} elseif(@filemtime($cron_folder) + 1800 < time()) {
    // Remove the "cronlock" folder
    @rmdir($cron_folder);
}
?>