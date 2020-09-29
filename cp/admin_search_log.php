<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_search_log'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_search_log_view');

$PMDR->loadJavascript('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_search_log.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_search_log'));

if($_GET['action'] == 'download') {
    $serve = $PMDR->get('ServeFile');
    $serve->serve(TEMP_UPLOAD_PATH.'search_log.csv');
}

if($_GET['action'] == 'export') {
    $script = '
    <script type="text/javascript">
    var exportOnComplete = function(data) {
        $("#status").progressbar("option", "value", data.percent);
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            $("#status").progressbar("destroy");
            $("#status_percent").hide();
            $("#download_button").show();
            $("#download_button").click(function() { window.location.href = \''.BASE_URL_ADMIN.'/admin_search_log.php?action=download\' });
        } else {
            exportStart(data.start+data.num,data.num);
        }
    };

    var exportStart = function(start,num) {
        if(start == 0) {
            $("#status_percent").html("0%");
            $("#status").progressbar({ value: 0 });
        }
        $.ajax({ data: ({ action: "admin_search_log_export", start: start, num: num }), success: exportOnComplete, dataType: "json"});
    };
    $(document).ready(function() {
        exportStart(0,50);
    });
    </script>';
    $template_content->set('content',$script.'<div style="width: 500px; float: left;" id="status"></div><div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div><a class="btn btn-success" style="display: none" id="download_button"><i class="glyphicon glyphicon-download-alt"></i> Download</a>');
} else {
    if($_GET['action'] == 'clear') {
        $PMDR->get('Authentication')->checkPermission('admin_search_log_delete');
        $PMDR->get('Search_Log')->clear();
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_search_log_cleared'),'delete');
        redirect();
    }

    $form_search = $PMDR->getNew('Form');
    $form_search->method = 'GET';
    $form_search->addFieldSet('search_log_search',array('legend'=>$PMDR->getLanguage('admin_search_log_search')));
    $form_search->addField('date_start','date',array('label'=>$PMDR->getLanguage('admin_search_log_date_start'),'fieldset'=>'search_log_search','value'=>$_GET['date_start']));
    $form_search->addField('date_end','date',array('label'=>$PMDR->getLanguage('admin_search_log_date_end'),'fieldset'=>'search_log_search','value'=>$_GET['date_end']));
    $form_search->addField('keywords','text',array('label'=>$PMDR->getLanguage('admin_search_log_keywords'),'fieldset'=>'search_log_search','value'=>$_GET['keywords']));
    $form_search->addField('results_found','checkbox',array('label'=>$PMDR->getLanguage('admin_search_log_results'),'fieldset'=>'search_log_search','value'=>$_GET['results_found']));
    $form_search->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $template_content->set('form_search',$form_search);

    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('keywords',$PMDR->getLanguage('admin_search_log_keywords'),true);
    $table_list->addColumn('count',$PMDR->getLanguage('admin_search_log_count'),true);
    $table_list->addColumn('results',$PMDR->getLanguage('admin_search_log_results'),true);
    $table_list->addColumn('ip',$PMDR->getLanguage('admin_search_log_ip'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_search_log_date'),true);
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');

    $where = array();
    if(!empty($_GET['keywords'])) {
        $where[] = "keywords LIKE '%".$PMDR->get('Cleaner')->clean_db($_GET['keywords'])."%'";
    }
    if(!empty($_GET['results_found'])) {
        $where[] = "results != ''";
    }
    if($_GET['action'] == 'noresults') {
        $where[] = "results = ''";
    }
    if(!empty($_GET['type'])) {
        $where[] = "results != ''";
    }
    if(!empty($_GET['date_start'])) {
        $where[] = "date >  '".$PMDR->get('Dates')->formatDateInput($_GET['date_start'])."'";
    }
    if(!empty($_GET['date_end'])) {
        $where[] = "date <  '".$PMDR->get('Dates')->formatDateInput($_GET['date_end'])."'";
    }
    if(!empty($where)) {
        $where_sql = 'WHERE '.implode(' AND ',$where);
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_SEARCH_LOG." $where_sql ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());

    foreach($records AS $key=>$record) {
        $records[$key]['keywords'] = $PMDR->get('Cleaner')->clean_output($record['keywords']);
        if(strlen($record['keywords']) > 100) {
            $records[$key]['keywords'] = '<textarea>'.$record['keywords'].'</textarea>';
        }
        $records[$key]['results'] = str_replace(',',', ',$record['results']);
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('eye',array('target'=>'_blank','href'=>BASE_URL.'/search_results.php?'.http_build_query(unserialize($record['terms'])),'label'=>$PMDR->getLanguage('admin_search_log_view')));
        $records[$key]['manage'] .= '
        <script type="text/javascript">
        $(document).ready(function(){
            $("#search_log_details'.$record['id'].'").dialog({
                 buttons: {
                    "Close": function() { $(this).dialog("close"); }
                 },
                 width: 350,
                 height: 260,
                 autoOpen: false,
                 modal: true,
                 resizable: false,
                 title: "'.$PMDR->getLanguage('admin_search_log_parameters').'"
            });
            $("#search_log_details_link'.$record['id'].'").click(function() {
                $("#search_log_details'.$record['id'].'").dialog("open");
            });
        });
        </script>
        <div id="search_log_details'.$record['id'].'" style="margin-top: 5px;">';
        foreach(unserialize($record['terms']) as $terms_key=>$value) {
            $records[$key]['manage'] .= $terms_key.': '.$value.'<br />';
        }
        $records[$key]['manage'] .= '</div>';
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('doc',array('id'=>'search_log_details_link'.$record['id'],'href'=>'#','label'=>$PMDR->getLanguage('admin_search_log_parameters')));
    }

    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('records',$records);
    $template_content->set('content',$table_list->render());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_search_log_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>