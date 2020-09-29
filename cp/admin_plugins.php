<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_modules','admin_plugins'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_plugins_view');

$PMDR->get('Cache')->delete('plugins');

// Get all plugins by the files
$files = scandir(PMDROOT.'/modules/plugins/');
$plugins = array();
foreach($files AS $file) {
    $plugin_file = PMDROOT.'/modules/plugins/'.$file.'/'.$file.'.php';
    if($file == '.' OR $file == '..' OR !file_exists($plugin_file) OR !($fp = fopen($plugin_file, 'r'))) {
        continue;
    }

    $plugin_file_contents = fread($fp, 8192);
    fclose($fp);

    preg_match('/Plugin Name:(.*)$/mi', $plugin_file_contents, $name);
    preg_match('/Plugin URL:(.*)$/mi', $plugin_file_contents, $url);
    preg_match('/Version:(.*)/i', $plugin_file_contents, $version);
    preg_match('/Description:(.*)$/mi', $plugin_file_contents, $description);
    preg_match('/Author:(.*)$/mi', $plugin_file_contents, $author);
    preg_match('/Author URL:(.*)$/mi', $plugin_file_contents, $author_url);
    preg_match('/Compatibility:(.*)$/mi', $plugin_file_contents, $compatibility);

    if(empty($name) OR trim($name[1]) == '') continue;

    $plugin_key = basename($plugin_file,'.php');

    foreach(array('name','url','version','description','author','author_url','compatibility') as $field) {
        $plugins[$plugin_key][$field] = trim(${$field}[1]);
    }

    $db->Execute("INSERT IGNORE INTO ".T_PLUGINS." (id,installed,active,version) VALUES (?,0,0,?)",array($plugin_key,$plugins[$plugin_key]['version']));
}

$installed_plugins = $db->GetAssoc("SELECT id, active, installed, version FROM ".T_PLUGINS);
foreach($installed_plugins AS $key=>$value) {
    if(isset($plugins[$key])) {
        $plugins[$key]['active'] = $value['active'];
        $plugins[$key]['installed'] = $value['installed'];
        // We have a version mismatch, run the upgrade code for the plugin
        if(version_compare($plugins[$key]['version'],$value['version']) == 1) {
            $upgrade_result = true;
            // If the function exists, we run it passing any data that may be useful for the plugin upgrade function
            if(function_exists($key.'_upgrade')) {
                $upgrade_result = call_user_func_array($key.'_upgrade',array('old_version'=>$value['version'],'new_version'=>$plugins[$key]['version']));
            }
            // If the upgrade was successful, show a message and also increment the version number automatically
            if($upgrade_result) {
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_plugins_upgrade_success',array($plugins[$key]['name'],$value['version'],$plugins[$key]['version'])));
                $db->Execute("UPDATE ".T_PLUGINS." SET version=? WHERE id=?",array($plugins[$key]['version'],$key));
            }
            unset($upgrade_result);
        }
    } else {
        $db->Execute("DELETE FROM ".T_PLUGINS." WHERE id=?",array($key));
    }
}

if($_GET['action'] == 'enable') {
    $PMDR->get('Authentication')->checkPermission('admin_plugins_edit');
    $PMDR->get('Plugins')->enable($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($plugins[$_GET['id']]['name'],$PMDR->getLanguage('admin_plugins'))),'update');
    redirect();
}

if($_GET['action'] == 'disable') {
    $PMDR->get('Authentication')->checkPermission('admin_plugins_edit');
    $PMDR->get('Plugins')->disable($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($plugins[$_GET['id']]['name'],$PMDR->getLanguage('admin_plugins'))),'update');
    redirect();
}

if($_GET['action'] == 'install') {
    $PMDR->get('Authentication')->checkPermission('admin_plugins_edit');
    if($PMDR->get('Plugins')->install($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_plugins_plugin_installed'));
    } else {
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_plugins_install_failed').' ('.$plugin['id'].'_install.php)');
    }
    redirect();
}

if($_GET['action'] == 'uninstall') {
    if($PMDR->get('Plugins')->uninstall($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_plugins_uninstalled').' /modules/plugins/'.$plugin['id'].'/ folder.');
    } else {
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_plugins_uninstall_failed').' ('.$plugin['id'].'_uninstall.php)');
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('name',$PMDR->getLanguage('admin_plugins_name'),false,true);
    $table_list->addColumn('description',$PMDR->getLanguage('admin_plugins_description'));
    $table_list->addColumn('version',$PMDR->getLanguage('admin_plugins_version'));
    $table_list->addColumn('author',$PMDR->getLanguage('admin_plugins_author'));
    $table_list->addColumn('installed',$PMDR->getLanguage('admin_plugins_installed'));
    $table_list->addColumn('active',$PMDR->getLanguage('admin_plugins_enabled'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'),false,true);

    $table_list->setTotalResults(count($plugins));
    $records = array_splice($plugins,$table_list->page_data['limit1'],$table_list->page_data['limit2']);

    foreach($records as $key=>$record) {
        if($record['url'] != '') {
            $records[$key]['name'] = '<a href="'.$record['url'].'">'.$record['name'].'</a>';
        }
        if($record['author_url'] != '') {
            $records[$key]['author'] = '<a href="'.$record['author_url'].'">'.$record['author'].'</a>';
        }
        if(!empty($record['compatibility'])) {
            $records[$key]['description'] .= '<br />'.$PMDR->getLanguage('admin_plugins_compatibility').': '.$record['compatibility'];
        } else {
            $records[$key]['description'] .= '<br />'.$PMDR->getLanguage('admin_plugins_compatibility').': -';
        }
        $records[$key]['active'] = $PMDR->get('HTML')->icon($record['active']);
        $records[$key]['installed'] = $PMDR->get('HTML')->icon($record['installed']);
        if($record['installed']) {
            if(file_exists(PMDROOT.'/modules/plugins/'.$key.'/uninstall_instructions.txt')) {
                $records[$key]['manage'] = '
                <script type="text/javascript">
                $(document).ready(function(){
                    $("#plugin_uninstall_content_'.$key.'").dialog({
                         open: function(event, ui) {
                            $.ajax({type: "get", url: "'.BASE_URL.'/modules/plugins/'.$key.'/uninstall_instructions.txt", success: function(data) { data = htmlspecialchars(data); $("#plugin_uninstall_content_'.$key.'").html(data.replace(/\n/g,"<br />")); } })
                         },
                         width: 550,
                         height: 350,
                         autoOpen: false,
                         modal: true,
                         resizable: false,
                         title: "Instructions"
                    });
                    $("#plugin_uninstall_'.$key.'").click(function(e) {
                        e.preventDefault();
                        var targetUrl = $(this).attr("href");
                        $("#plugin_uninstall_content_'.$key.'").dialog({
                            buttons: {
                                "Continue": function() {
                                    $(this).dialog("close");
                                    window.location.href = targetUrl;
                                    return true;
                                },
                                "Cancel": function() { $(this).dialog("close"); return false; }
                             }
                        });
                        $("#plugin_uninstall_content_'.$key.'").dialog("open");
                    });
                });
                </script>
                <div id="plugin_uninstall_content_'.$key.'"></div>
                ';
            }
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('plugin_uninstall',array('label'=>$PMDR->getLanguage('admin_plugins_uninstall'),'id'=>'plugin_uninstall_'.$key,'href'=>URL_NOQUERY.'?action=uninstall&id='.$key));
        } else {
            if(file_exists(PMDROOT.'/modules/plugins/'.$key.'/install_instructions.txt')) {
                $records[$key]['manage'] = '
                <script type="text/javascript">
                $(document).ready(function(){
                    $("#plugin_install_content_'.$key.'").dialog({
                         open: function(event, ui) {
                            $.ajax({type: "get", url: "'.BASE_URL.'/modules/plugins/'.$key.'/install_instructions.txt", success: function(data) { data = htmlspecialchars(data); $("#plugin_install_content_'.$key.'").html(data.replace(/\n/g,"<br />")); } })
                         },
                         width: 550,
                         height: 350,
                         autoOpen: false,
                         modal: true,
                         resizable: false,
                         title: "Instructions"
                    });
                    $("#plugin_install_'.$key.'").click(function(e) {
                        e.preventDefault();
                        var targetUrl = $(this).attr("href");
                        $("#plugin_install_content_'.$key.'").dialog({
                            buttons: {
                                "Continue": function() {
                                    $(this).dialog("close");
                                    window.location.href = targetUrl;
                                    return true;
                                },
                                "Cancel": function() { $(this).dialog("close"); return false; }
                             }
                        });
                        $("#plugin_install_content_'.$key.'").dialog("open");
                    });';
                    if(value($_GET,'install_trigger') == 'true' AND value($_GET,'id') == $key) {
                        $records[$key]['manage'] .= ' $("#plugin_install_'.$key.'").trigger("click");';
                    }
                    $records[$key]['manage'] .= '
                });
                </script>
                <div id="plugin_install_content_'.$key.'"></div>
                ';
            }
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('plugin_add',array('id'=>'plugin_install_'.$key,'label'=>$PMDR->getLanguage('admin_plugins_install'),'href'=>URL_NOQUERY.'?action=install&id='.$key));
        }
        if($record['active']) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('plugin_disable',array('label'=>$PMDR->getLanguage('admin_plugins_disable'),'href'=>URL_NOQUERY.'?action=disable&id='.$key));
        } elseif($record['installed']) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('plugin_enable',array('label'=>$PMDR->getLanguage('admin_plugins_enable'),'href'=>URL_NOQUERY.'?action=enable&id='.$key));
        }
        if($record['installed']) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('edit',array('href'=>'admin_plugins_edit.php?id='.$key.'&file='.$key.'.php'));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_plugins'));
    $template_content->set('content',$table_list->render());
} else {
    if(DEMO_MODE) {
        $PMDR->addMessage('error','Adding plugins is not available in demo mode.');
        redirect('admin_plugins.php');
    }

    $PMDR->get('Authentication')->checkPermission('admin_plugins_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('plugin_details',array('legend'=>$PMDR->getLanguage('admin_plugins_information')));
    $template_content->set('title',$PMDR->getLanguage('admin_plugins_add'));
    $form->addField('plugin_file','file',array('label'=>$PMDR->getLanguage('admin_plugins_file'),'fieldset'=>'plugin_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    if($form->wasSubmitted('submit')) {
        if(DEMO_MODE) {
            $PMDR->addMessage('error','Adding plugins is disabled in the demo.');
        } else {
            $data = $form->loadValues();

            if($_GET['action'] == 'add') {
                $plugin_name = substr($_FILES['plugin_file']['name'],0,strpos($_FILES['plugin_file']['name'],'.'));
                if(!is_writable(PLUGINS_PATH)) {
                    $form->addError($PMDR->getLanguage('messages_not_writable',PLUGINS_PATH));
                } elseif(file_exists(PLUGINS_PATH.$plugin_name.'/')) {
                    $form->addError($PMDR->getLanguage('admin_plugins_name_in_use'));
                } elseif(!$_FILES['plugin_file']['size']) {
                    $form->addError($PMDR->getLanguage('admin_plugins_file_empty'));
                }
            }

            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                move_uploaded_file($_FILES['plugin_file']['tmp_name'],TEMP_UPLOAD_PATH.$_FILES['plugin_file']['name']);
                $zip = $PMDR->get('Zip',TEMP_UPLOAD_PATH.$_FILES['plugin_file']['name']);
                $zip_file_list = $zip->extract(PCLZIP_OPT_PATH,PLUGINS_PATH.$plugin_name.'/',PCLZIP_OPT_REMOVE_PATH, $plugin_name);
                if(!$zip->errorCode() AND file_exists(PLUGINS_PATH.$plugin_name.'/'.$plugin_name.'.php')) {
                    $plugin_file_contents = file_get_contents(PLUGINS_PATH.$plugin_name.'/'.$plugin_name.'.php');
                    if(preg_match( '/Version:(.*)/i', $plugin_file_contents, $version)) {
                        $db->Execute("INSERT IGNORE INTO ".T_PLUGINS." (id,installed,active,version) VALUES (?,0,0,?)",array($plugin_name,trim($version[1])));
                        $PMDR->addMessage('success',$PMDR->getLanguage('admin_plugins_files_uploaded'));
                        redirect(null,array('install_trigger'=>'true','id'=>$plugin_name));
                    } else {
                        unlink(PLUGINS_PATH.$plugin_name.'/');
                        $PMDR->addMessage('error',$PMDR->getLanguage('admin_plugins_incompatible'));
                    }
                } else {
                    unlink(PLUGINS_PATH.$plugin_name.'/');
                    $PMDR->addMessage('error',$PMDR->getLanguage('admin_plugins_unzip_error'));
                }
                unlink(TEMP_UPLOAD_PATH.$_FILES['plugin_file']['name']);
            }
        }
    }
    $template_content->set('content', $form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_plugins_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>