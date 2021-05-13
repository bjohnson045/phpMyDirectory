<?php
// TODO IMPORT
// Settings
// Listings
include('../../../defaults.php');
if(md5($_SESSION['login'].$_SESSION['pass']) != $_SESSION['import_hash']) {
    redirect(BASE_URL.'/install/import/index.php');
}

function globr($sDir, $sPattern, $nFlags = NULL) {
    $aFiles = glob("$sDir/$sPattern", $nFlags);
    foreach (glob("$sDir/*", GLOB_ONLYDIR) as $sSubDir) {
        $aSubFiles = globr($sSubDir, $sPattern, $nFlags);
        if($aSubFiles !== FALSE) {
            $aFiles = array_merge($aFiles, $aSubFiles);
        }
    }
    return $aFiles;
}

$template_content = $PMDR->getNew('Template',PMDROOT.'/install/import/10-4-6/index.tpl');

$cant_detect = false;

$form = $PMDR->get('Form');
$form->addFieldSet('import',array('legend'=>'Import')); 
$defaults_file = globr(substr(PMDROOT,0,strrpos(PMDROOT,'/')),'defaults.php');

foreach($defaults_file as $key=>$file) {
    $defaults_content = file_get_contents($file);
    if(!preg_match('/\$db_loc_one = "(.{1,50})";/',$defaults_content,$matches) OR !preg_match('/\$db_users/',$defaults_content)) {
        unset($defaults_file[$key]);
    }
}
if(count($defaults_file) == 1) {
    $old_path = str_replace('defaults.php','',array_shift($defaults_file));
    $form->addField('path','custom',array('label'=>'Old version path','fieldset'=>'import','value'=>$old_path,'html'=>$old_path));
} elseif(count($defaults_file) > 1) {
    foreach($defaults_file as $file) {
        $path = str_replace('defaults.php','',$file);
        if(rtrim($path,'/') == PMDROOT) continue;
        $options[$path] = $path;
    }
    $form->addField('path','radio',array('label'=>'Old version path','fieldset'=>'import','value'=>'','options'=>$options));
} else {
    $cant_detect = true;
    $content = 'We were unable to detect your old version.  Please ensure the new version is installed in a sub folder under your v10.4.6 installation or enter the path below.';
    $form->addField('path','text',array('label'=>'Old version path','fieldset'=>'import','value'=>$_SERVER['DOCUMENT_ROOT']));
}
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!file_exists($data['path'])) {
        $form->addError('Invalid upgrade path.');
    } else {
        $defaults_content = file_get_contents($data['path'].'defaults.php');
        if(!preg_match('/\$db_loc_one = "(.{1,50})";/',$defaults_content,$matches) OR !preg_match('/\$db_users/',$defaults_content)) {
            $form->addError('Unable to verify old version please double check path.');
        }
    }

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $upgrade_data['table_prefix'] = str_replace('_loc_one','',$matches[1]);
        $upgrade_data['PMDROOT'] = $_POST['path'];
        $_SESSION['upgrade_data'] = $upgrade_data;
        redirect(BASE_URL.'/install/import/10-4-6/step_1.php');
    }
}
if(!$cant_detect) {
    $template_content->set('content',$form->toHTML());
} else {
    $template_content->set('content',$content.$form->toHTML());    
}

include(PMDROOT.'/install/includes/template_setup.php');
?>