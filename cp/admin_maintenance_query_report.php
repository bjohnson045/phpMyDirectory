<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$template_content->set('title',$PMDR->getLanguage('admin_maintenance_report'));

$content = '<table class="frame" style="width: 100%"><tr><td><table class="table">';
$content .= '<thead><tr><th>Query/URL</th><th>Count</th><th>Average Time</th><th>Max Execution Time</th><th>Minimum Execution Time</th><th>Total Time</th></tr></thead><tbody>';

// The sql_query field and url field will be unique based on the sql_query_hash field that we group by, so we use ANY_VALUE since we do 
$log_data = $db->GetAll("
    SELECT 
        COUNT(sql_query) count,
        AVG(timer) average_time,
        MAX(timer) max_time,
        MIN(timer) min_time,
        SUM(timer) total_time,
        ANY_VALUE(sql_query) sql_query,
        ANY_VALUE(url) url
    FROM ".T_LOG_SQL." 
    GROUP BY sql_query_hash 
    ORDER BY average_time DESC 
    LIMIT 500"
);                               

foreach($log_data as $log_key=>$log) {
    $content .= '<tr><td style="width: 500px"><div id="sql_query'.$log_key.'">'.substr($log['sql_query'],0,200).' (<a onclick="$(\'#sql_query'.$log_key.'\').hide(); $(\'#sql_query'.$log_key.'_long\').show();" href="#">Expand</a>)</div><div id="sql_query'.$log_key.'_long" style="display: none">'.$log['sql_query'].' (<a onclick="$(\'#sql_query'.$log_key.'_long\').hide(); $(\'#sql_query'.$log_key.'\').show();" href="#">Collapse</a>)</div><br /><a href="'.$log['url'].'">View URL</a></td><td>'.$log['count'].'</td><td>'.$log['average_time'].'</td><td>'.$log['max_time'].'</td><td>'.$log['min_time'].'</td><td>'.$log['total_time'].'</td></tr>';
}

$content .= '</tbody></table></td></tr></table>';
$template_content->set('content',$content);

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>