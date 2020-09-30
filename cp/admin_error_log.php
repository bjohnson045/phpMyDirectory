<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_error_log','admin_maintenance'));

$PMDR->get('Authentication')->authenticate();

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_error_log.tpl');

if(value($_GET,'clear')) {
    $db->Execute("TRUNCATE ".T_ERROR_LOG);
    redirect();
}

$template_content->set('title',$PMDR->getLanguage('admin_error_log'));
$table_list = $PMDR->get('TableList');
$table_list->addColumn('date',null,false,true);
$table_list->addColumn('message');
$table_list->addColumn('manage');

$paging = $PMDR->get('Paging');
$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_ERROR_LOG." ORDER BY date DESC LIMIT ?,?",array($paging->limit1,$paging->limit2));
$paging->setTotalResults($db->FoundRows());
foreach($records as $key=>$record) {
    $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
    $records[$key]['message'] =  $PMDR->get('ErrorHandler')->formatError($record['code'],$record['message'],$record['file'],$record['line'],true);
    $trace_output = '';
    $trace = unserialize($record['trace']);
    if(!empty($trace)) {
        foreach($trace AS $trace_part) {
            if(!is_object($trace_part) AND !is_null($trace_part) AND is_array($trace_part)) {
                $trace_output .= '<strong>File</strong>: '.$trace_part['file'].'<br />';
                $trace_output .= '<strong>Line</strong>: '.$trace_part['line'].'<br />';
                if(isset($trace['function'])) {
                    $trace_output .= '<strong>Function</strong>: '.$trace_part['function'].'<br />';
                }
                if(isset($trace_part['class'])) {
                    $trace_output .= '<strong>Class</strong>: '.$trace_part['class'].'<br />';
                }
                $trace_output .= '<br />';
            }
        }
    }
    $records[$key]['manage'] = '<div id="trace_link_'.$key.'_content" style="display: none">'.$trace_output.'</div>';
    $records[$key]['manage'] .= $PMDR->get('HTML')->icon('doc',array('id'=>'trace_link_'.$key,'href'=>'#','label'=>$PMDR->getLanguage('admin_error_log_trace')));

}
$table_list->addRecords($records);
$table_list->addPaging($paging);
$template_content->set('content',$table_list->render());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>