<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_link_checker','email_templates'));

$PMDR->get('Authentication')->checkPermission('admin_link_checker_view');

$PMDR->get('Plugins')->run_hook('admin_link_checker_begin');

$reciprocal_field = $PMDR->getConfig('reciprocal_field') ? $PMDR->getConfig('reciprocal_field') : 'www';
$reciprocal_field_db = $PMDR->getConfig('reciprocal_field') ? ','.$PMDR->getConfig('reciprocal_field') : '';

if(isset($_POST['table_list_submit'])) {
    if($_POST['action'] == 'email') {
        foreach($_POST['table_list_checkboxes'] AS $id) {
            $PMDR->get('Email_Templates')->queue('reciprocal_failed',array('listing_id'=>$id));
        }
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_link_checker_reciprocal_failed_email_sent'));
    }
    redirect();
}

if($_GET['action'] == 'check') {
    $PMDR->get('Authentication')->checkPermission('admin_link_checker_check');

    $listing = $db->GetRow("SELECT id, www $reciprocal_field_db FROM ".T_LISTINGS." WHERE id=?",array($_GET['id']));

    if($_GET['type'] == 'dead') {
        if(in_array($status = $PMDR->get('LinkChecker')->checkURL($listing['www']), array('dead','no_reciprocal','valid'))) {
            if($status == 'dead') {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_link_checker_dead'));
                $message = $PMDR->getLanguage('admin_link_checker_log',array($PMDR->getLanguage('admin_link_checker_dead'),$_GET['id']));
            } else {
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_link_checker_valid'));
                $message = $PMDR->getLanguage('admin_link_checker_valid');
                $db->Execute("UPDATE ".T_LISTINGS." SET www_status=1 WHERE id=?",array($_GET['id']));
            }
        } else {
            $PMDR->addMessage('error',$PMDR->getLanguage('admin_link_checker_error'));
            $message = $PMDR->getLanguage('admin_link_checker_error');
        }
    } else {
        $status = $PMDR->get('LinkChecker')->checkURL($listing[$reciprocal_field]);
        if(in_array($status = $PMDR->get('LinkChecker')->checkURL($listing['www']), array('dead','no_reciprocal','valid'))) {
            if($status == 'valid') {
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_link_checker_valid'));
                $message = $PMDR->getLanguage('admin_link_checker_valid');
                $db->Execute("UPDATE ".T_LISTINGS." SET www_reciprocal=1 WHERE id=?",array($_GET['id']));
            } elseif($status == 'dead' AND !$PMDR->getConfig('reciprocal_field')) {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_link_checker_dead'));
                $message = $PMDR->getLanguage('admin_link_checker_log',array($PMDR->getLanguage('admin_link_checker_dead'),$_GET['id']));
                $db->Execute("UPDATE ".T_LISTINGS." SET www_status=0 WHERE id=?",array($_GET['id']));
            } else {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_link_checker_no_reciprocal'));
                $message = $PMDR->getLanguage('admin_link_checker_log',array($PMDR->getLanguage('admin_link_checker_no_reciprocal'),$_GET['id']));
            }
        } else {
            $PMDR->addMessage('error',$PMDR->getLanguage('admin_link_checker_error'));
            $message = $PMDR->getLanguage('admin_link_checker_error');
        }
    }
    $PMDR->log('general',$message);
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_link_checker.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_link_checker_listing'));
    $table_list->addColumn('www',$PMDR->getLanguage('admin_link_checker_url'));
    $table_list->addColumn('www_date_checked',$PMDR->getLanguage('admin_link_checker_date_checked'));
    $table_list->addColumn('www_status',$PMDR->getLanguage('admin_link_checker_status'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $PMDR->get('Plugins')->run_hook('admin_link_checker_table');
    $checkbox_options =
    array(
        ''=>'',
        'email'=>$PMDR->getLanguage('admin_link_checker_reciprocal_failed_email_send')
    );
    $table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$checkbox_options)));
    $paging = $PMDR->get('Paging');

    if(empty($_GET['status']) OR $_GET['status'] == 'dead') {
        $status = "www_status=0";
        $select = "'dead' AS type, www AS url";
    } else {
        $select = "'no_reciprocal' AS type, $reciprocal_field AS url";
        if($_GET['status'] == 'no_reciprocal') {
            $status = "www_reciprocal=0";
        } else {
            $status = "www_reciprocal=0 AND require_reciprocal=1";
        }
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS id, www_date_checked, www_status, www_reciprocal, $select FROM ".T_LISTINGS." WHERE $status ORDER BY id ASC LIMIT ?,?",array($paging->limit1,$paging->limit2));
    unset($status,$select);

    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['www_status'] = $PMDR->getLanguage('admin_link_checker_'.$record['type']);
        $records[$key]['www'] = '<a target="_blank" href="'.$PMDR->get('Cleaner')->clean_output($record['url']).'">'.$PMDR->get('Cleaner')->clean_output($record['url']).'</a>';
        if($PMDR->get('Dates')->isZero($record['www_date_checked'])) {
            $records[$key]['www_date_checked'] = $PMDR->getLanguage('admin_link_checker_never');
        } else {
            $records[$key]['www_date_checked'] = $PMDR->get('Dates_Local')->formatDateTime($record['www_date_checked']);
        }
        $records[$key]['listing_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_listings.php?action=edit&id='.$record['id'].'">'.$record['id'].'</a>';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('arrow_revert',array('label'=>'Check','href'=>URL_NOQUERY.'?action=check&type='.$record['type'].'&id='.$record['id'].'&from='.urlencode_url(URL)));
        $PMDR->get('Plugins')->run_hook('admin_link_checker_table_loop');
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);

    $title = $PMDR->getLanguage('admin_link_checker').' - ';
    if(empty($_GET['status']) OR $_GET['status'] == 'dead') {
        $title .= $PMDR->getLanguage('admin_link_checker_dead');
    } elseif($_GET['status'] == 'no_reciprocal') {
        $title .= $PMDR->getLanguage('admin_link_checker_no_reciprocal');
    } else {
        $title .= $PMDR->getLanguage('admin_link_checker_no_reciprocal').' ('.$PMDR->getLanguage('admin_link_checker_required').')';
    }
    $title .= ' '.$PMDR->getLanguage('admin_link_checker_links');

    $template_content->set('title',$title);
    $template_content->set('content',$table_list->render());
    unset($title);
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_link_checker_menu.tpl');

$PMDR->get('Plugins')->run_hook('admin_link_checker_end');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>