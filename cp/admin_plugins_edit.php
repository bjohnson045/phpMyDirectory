<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_modules','admin_plugins'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_plugins_edit');

function directoryToArray($directory, $recursive = true, $folders = false, $full_paths = false) {
    $array_items = array();
    if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if(is_dir($directory. "/" . $file)) {
                    if($recursive) {
                        $array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
                    }
                    if($folders) {
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    }
                } else {
                    if($full_paths) {
                        $file = $directory . "/" . $file;
                    }
                    $array_items[] = preg_replace("/\/\//si", "/", $file);
                }
            }
        }
        closedir($handle);
    }
    return $array_items;
}

if(DEMO_MODE) {
    $PMDR->addMessage('error','Editing plugins is not available in demo mode.');
    redirect('admin_plugins.php');
}

if(!isset($_GET['id']) OR !isset($_GET['file']) OR !file_exists(PLUGINS_PATH.$_GET['id'].'/'.$_GET['file']) OR !$db->GetOne("SELECT id FROM ".T_PLUGINS." WHERE id=?",array($_GET['id']))) {
    redirect(BASE_URL_ADMIN.'/admin_plugins.php');
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_plugins_edit.tpl');

$files = scandir(PMDROOT.'/modules/plugins/');
$plugins = array();
foreach($files AS $file) {
    if($file == '.' OR $file == '..') continue;

    if(is_dir(PMDROOT.'/modules/plugins/'.$file.'/')) {
        if(file_exists(PMDROOT.'/modules/plugins/'.$file.'/'.$file.'.php')) {
            $plugin_file = PMDROOT.'/modules/plugins/'.$file.'/'.$file.'.php';
        } else {
            continue;
        }
    }
    if(!$fp = fopen($plugin_file, 'r')) continue;
    $plugin_file_contents = fread($fp, 8192);
    fclose($fp);

    preg_match( '/Plugin Name:\s?(.*)$/mi', $plugin_file_contents, $name);
    if(trim($name[1]) == '') continue;

    $plugins[basename($plugin_file,'.php')] = trim($name[1]);
}
unset($files,$file,$name,$plugin_file_contents,$plugin_file);

if(in_array(get_file_format(PMDROOT.'/modules/plugins/'.$_GET['id'].'/'.$_GET['file']),array('image/gif','image/jpg','image/png','image/jpeg'))) {
    $template_content->set('image', get_file_url(PMDROOT.'/modules/plugins/'.$_GET['id'].'/'.$_GET['file']));
} else {
    $code = file_get_contents(PMDROOT.'/modules/plugins/'.$_GET['id'].'/'.$_GET['file']);
    $form = $PMDR->get('Form');
    $form->addFieldSet('plugin_details',array('legend'=>$PMDR->getLanguage('admin_plugins_information')));
    $form->addField('code','textarea',array('label'=>'','value'=>$code,'fullscreen'=>true,'fieldset'=>'plugin_details','style'=>'font-family: Courier New; width: 100%; height: 400px'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!is_writable(PLUGINS_PATH.$_GET['id'].'/'.$_GET['file'])) {
            $form->addError($PMDR->getLanguage('messages_not_writable','/modules/plugins/'.$_GET['id'].'/'.$_GET['file']));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $fp = fopen(PLUGINS_PATH.'/'.$_GET['id'].'/'.$_GET['file'], 'w');
            fwrite($fp, $data['code']);
            fclose($fp);
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($plugins[$_GET['id']],$PMDR->getLanguage('admin_plugins'))),'update');
            redirect(URL);
        }
    }
    $template_content->set('form', $form);
}

$template_content->set('file',$plugins[$_GET['id']].' - /'.$_GET['id'].'/'.$_GET['file']);

$files = directoryToArray(PMDROOT.'/modules/plugins/'.$_GET['id']);
$files_content = '';
foreach($files AS $file) {
    if($_GET['file'] == $file) {
        $files_content .= '<a class="list-group-item active" href="'.URL_NOQUERY.'?id='.$_GET['id'].'&file='.$file.'">'.$file.'</a>';
    } else {
        $files_content .= '<a class="list-group-item" href="'.URL_NOQUERY.'?id='.$_GET['id'].'&file='.$file.'">'.$file.'</a>';
    }
}
$template_content->set('files',$files);

$edit_plugin_content = '<select id="plugin_select" class="form-control" style="width: 170px" name="id">';

foreach($plugins AS $key=>$value) {
    $edit_plugin_content .= '<option value="'.$key.'"';
    if($key == $_GET['id']) {
        $edit_plugin_content .= ' selected="selected"';
    }
    $edit_plugin_content .= '>'.$value.'</option>';
}
$edit_plugin_content .= '</select>';

$template_page_menu[] = array('content'=>$PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_plugins_menu.tpl'));
$template_page_menu[] = array('title'=>$PMDR->getLanguage('admin_plugins_edit'),'content'=>$edit_plugin_content,'type'=>'content');
$template_page_menu[] = array('title'=>$PMDR->getLanguage('admin_plugins_files'),'content'=>$files_content);

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>