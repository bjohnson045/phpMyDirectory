<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_backup($j) {
    global $PMDR, $db;

    // If CRON backup is enabled and the backup path is writable
    if($PMDR->getConfig('backup_cron_days') AND file_exists($PMDR->getConfig('backup_path'))) {
        // Get size of database
        $tables = $db->GetAll("SHOW TABLE STATUS");
        $size = 0;
        foreach($tables as $table) {
            $size += $table['Data_length'];
        }
        // If database size is less than 30 megabytes back it up
        if($size / 1048576 <= 30) {
            $file = $PMDR->getConfig('backup_path').DB_NAME.'.'.date('Y-m-d').($PMDR->getConfig('backup_cron_compress') ? '.gzip' : '.sql');
            $dump = $PMDR->get('Backup_Database');
            $string = $dump->createFile($file,$PMDR->getConfig('backup_cron_compress'));
            return array('status'=>true);
        } else {
            trigger_error('Database too big to back up.',E_USER_WARNING);
            return array('status'=>false);
        }
    } else {
        return array('status'=>false);
    }
}
// Add the CRON job to the queue and set it to run based on the backup CRON days setting
$cron['cron_backup'] = array('day'=>intval($PMDR->getConfig('backup_cron_days')),'hour'=>0,'minute'=>0,'run_order'=>9);
?>