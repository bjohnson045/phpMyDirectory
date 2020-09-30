<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

// Load language variables for admin_backup
$PMDR->loadLanguage(array('admin_backup','admin_maintenance'));

// Check we are authorized (logged in)
$PMDR->get('Authentication')->authenticate();

// Check for backup permissions, if failed, redirect with error
$PMDR->get('Authentication')->checkPermission('admin_backup');

function backup_sort($a, $b) {
    $column = 'date';
    if($a[$column] == $b[$column]) {
        return 0;
    }
    return ($a[$column] < $b[$column]) ? -1 : 1;
}

// If action to delete, make sure file is also selected and exists
if ($_GET['action'] == 'delete' and isset($_GET['file'])) {
    if(file_exists($PMDR->getConfig('backup_path') . $_GET['file'])) {
        // Get rid of the backup file
        @unlink($PMDR->getConfig('backup_path') . $_GET['file']);
        // Log and notify the action
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_backup_deleted'),'delete');
    }
    redirect();
}

if(isset($_POST['table_list_submit'])) {
    if($_POST['action'] == 'delete') {
        foreach($_POST['table_list_checkboxes'] AS $file) {
            if(file_exists($PMDR->getConfig('backup_path').$file)) {
                @unlink($PMDR->getConfig('backup_path').$file);
            }
        }
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_backup_deleted'),'delete');
    }
    redirect();
}

// Export or download a backup
if ($_GET['action'] == 'export' and isset($_GET['file'])) {
    // Make sure the file exists before serving it
    if(file_exists($PMDR->getConfig('backup_path') . $_GET['file'])) {
        // Serve the file to the browser
        $serve = $PMDR->get('ServeFile');
        $serve->serve($PMDR->getConfig('backup_path') . $_GET['file']);
    }
    redirect();
}

// Set the backup path
if (isset($_POST['set_path'])) {
    if(DEMO_MODE) {
        $PMDR->addMessage('error','The backup manager is disabled in the demo.');
    } else {
        $_POST['backuppath'] = rtrim($_POST['backuppath'],"/\\").'/';
        $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='backup_path'",array($_POST['backuppath']));
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_backup_path_set'));
        $PMDR->config['backup_path'] = $_POST['backuppath'];
    }
    redirect();
}

// Check the validity of the backup path
if(!is_dir($PMDR->getConfig('backup_path'))) {
    $PMDR->addMessage('error',$PMDR->getLanguage('admin_backup_path_does_not_exist'));
} elseif (!is_writable($PMDR->getConfig('backup_path'))) {
    $PMDR->addMessage('error',$PMDR->getLanguage('messages_not_writable',$PMDR->getConfig('backup_path')));
}

// Manually create a backup
if (isset($_POST['submit_backup'])) {
    if(DEMO_MODE) {
        $PMDR->addMessage('error','The backup manager is disabled in the demo.');
    } else {
        // Increase time limit to allow backup to finish
        set_time_limit(180);

        // If we want to compress, set file type accordingly
        if($_POST['zipformat'] == "gz") {
            $compress = true;
            $file = $PMDR->getConfig('backup_path').DB_NAME."-".$PMDR->get('Dates_Local')->dateNow().".sql.gzip";
        } else {
            $compress = false;
            $file = $PMDR->getConfig('backup_path').DB_NAME."-".$PMDR->get('Dates_Local')->dateNow().".sql";
        }

        // Create the file
        $dump = $PMDR->get('Backup_Database');
        $string = $dump->createFile($file,$compress);
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_backup_created',array($file)));
    }
    redirect();
}

// Create the backup path form
$form_backup_path = $PMDR->get('Form');
$form_backup_path->addFieldSet('backup_folder',array('legend'=>$PMDR->getLanguage('admin_backup_path')));
$form_backup_path->addField('backuppath','text',array('label'=>$PMDR->getLanguage('admin_backup_path'),'fieldset'=>'backup_folder','value'=>($PMDR->getConfig('backup_path') == '' ? PMDROOT : $PMDR->getConfig('backup_path')),'help'=>$PMDR->getLanguage('admin_backup_help_backuppath')));
$form_backup_path->addField('set_path','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

// Create the manual backup form
$form_backup = $PMDR->getNew('Form');
$form_backup->addFieldSet('backup',array('legend'=>$PMDR->getLanguage('admin_backup_manual')));
$form_backup->addField('zipformat','select',array('label'=>$PMDR->getLanguage('admin_backup_compression'),'fieldset'=>'backup','options'=>array('none'=>$PMDR->getLanguage('admin_backup_none'),'gz'=>$PMDR->getLanguage('admin_backup_gzip')),'help'=>$PMDR->getLanguage('admin_backup_help_compression')));
$form_backup->addField('submit_backup','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

// Get the list of current backup files for display
$bu_files = array();
$bu_count = 0;
$total_filesize = 0;
$latest_backup = 0;
if(@$handle=opendir($PMDR->getConfig('backup_path'))) {
    while (false !== ($file=readdir($handle))) {
        if($file != "." AND $file != ".." AND $file != "index.html" AND in_array(get_file_extension($file),array('sql','gzip'))) {
            $bu_files[$bu_count]['file'] = $file;
            $bu_files[$bu_count]['filesize'] = number_format((filesize($PMDR->getConfig('backup_path').$file) / 1048576),2).' MB';
            $bu_files[$bu_count]['date'] = $PMDR->get('Dates')->formatTimeStamp(filemtime($PMDR->getConfig('backup_path').$file), true);
            $bu_files[$bu_count]['manage'] = $PMDR->get('HTML')->icon('download',array('title'=>$PMDR->getLanguage('admin_backup_export'),'href'=>BASE_URL_ADMIN.'/admin_backup.php?action=export&file='.$file));
            $bu_files[$bu_count]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>BASE_URL_ADMIN.'/admin_backup.php?action=delete&file='.$file));

            $total_filesize += $bu_files[$bu_count]['filesize'];
            $latest_backup = (filemtime($PMDR->getConfig('backup_path').$file) > $latest_backup) ? filemtime($PMDR->getConfig('backup_path').$file) : $latest_backup;
            $bu_count++;
        }
    }
    closedir($handle);
}
usort($bu_files, 'backup_sort');

/** @var TableList */
$table_list = $PMDR->get('TableList');
$table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>array('delete'=>$PMDR->getLanguage('admin_delete')))),'file');
$table_list->addColumn('file',$PMDR->getLanguage('admin_backup_current_files'));
$table_list->addColumn('filesize',$PMDR->getLanguage('admin_backup_size'));
$table_list->addColumn('date',$PMDR->getLanguage('admin_backup_date'));
$table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
$table_list->all_one_page = true;
$table_list->setTotalResults(count($bu_files));
$table_list->addRecords($bu_files);

// Load the template and send needed variables
$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_backup.tpl');
$template_content->set('form_backup_path',$form_backup_path->toHTML());
$template_content->set('form_backup',$form_backup->toHTML());
$template_content->set('files',$table_list->render());
$template_content->set('total_filesize',$total_filesize);
$template_content->set('latest_backup',($latest_backup > 0) ? $PMDR->get('Dates')->formatTimeStamp($latest_backup,true) : '-');

$template_page_menu[] = array('content'=>$PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl'));
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>