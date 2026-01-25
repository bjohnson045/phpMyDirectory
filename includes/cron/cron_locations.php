<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

// Note: This file appears to have incomplete/broken code and has been disabled for PHP 8 compatibility
// Original function cron_locations was incomplete with syntax errors

if(!function_exists('cron_locations')) {
    function cron_locations($j) {
        global $PMDR, $db;
        // Disabled - original code was incomplete
        return array('status'=>true);
    }
    $cron['cron_locations'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>1);
}
?>
