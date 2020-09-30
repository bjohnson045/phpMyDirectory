<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('settings','admin_settings','general_locations'));

$PMDR->get('Authentication')->checkPermission('admin_settings');

$location_tree = $PMDR->get('Locations');

if(isset($_GET['varname'])) {
    $PMDR->loadJavascript('
    <script type="text/javascript">
    $(document).ready(function(){
         $(\'html, body\').animate({
             scrollTop: ($("#'.$_GET['varname'].'").offset().top - 120)
         }, 2000);
         $("#'.$_GET['varname'].'").parents(".form-group").effect("highlight", {}, 5000);
    });
    </script>
    ',200);
}

if(!isset($_GET['group'])) {
    $_GET['group'] = 'general';
}

$template_content_form = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_settings_'.$_GET['group'].'.tpl');

$form = $PMDR->get('Form');
$form->enctype = 'multipart/form-data';
$form->action = 'admin_settings.php?group='.$_GET['group'];
$form->addFieldSet('settings',array('legend'=>'Settings'));

$demo_settings_disabled = array(
    'documents_allow','profile_image_types','search_ad_code','head_javascript','classifieds_images_formats',
    'banners_formats','affiliate_program_code','allowed_html_tags',
    'category_mod_rewrite','location_mod_rewrite','google_apikey','google_server_apikey','curl_proxy_url','logo','admin_email','maintenance'
);

$settings = $db->GetAssoc("SELECT varname AS varname_key, varname, grouptitle, value, optioncode, optioncode_type, optioncode_parse_type, validationcode FROM ".T_SETTINGS." WHERE ".T_SETTINGS.".grouptitle=?",array($_GET['group']));

if(isset($_GET['delete'])) {
    if(!empty($settings[$_GET['delete']]['value'])) {
        if(unlink_file(TEMP_UPLOAD_PATH.$settings[$_GET['delete']]['value'])) {
            $settings[$_GET['delete']]['value'] = '';
            $db->Execute("UPDATE ".T_SETTINGS." SET value='' WHERE varname=?",array($_GET['delete']));
        }
    }
    redirect(array('group'=>$_GET['group']));
}

$non_empty = array('category_mod_rewrite','location_mod_rewrite');
foreach($settings as $key=>$setting) {
    if($setting['varname'] == 'reciprocal_field' AND !ADDON_LINK_CHECKER) {
        unset($settings[$key]);
        continue;
    }
    if($setting['optioncode_type'] == 'select') {
        if($setting['optioncode_parse_type'] == 'static') {
            $options = explode("\r\n",trim($setting['optioncode'],"\r\n"));
            $form_options = array();
            foreach($options as $option) {
                $option = explode('|',$option);
                $form_options[$option[1]] = $option[0];
            }
        } elseif($setting['optioncode_parse_type'] == 'eval_options') {
            $form_options = eval($setting['optioncode']);
        }
        $form->addField($setting['varname'],'select',array('label'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title'),'fieldset'=>'settings','value'=>$setting['value'],'options'=>$form_options,'help'=>Strings::nl2br_replace($PMDR->getLanguage('setting_'.$setting['varname'].'_desc')),'help_title'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title').' ('.$setting['varname'].')'));
    } elseif($setting['optioncode_type'] == 'textarea') {
        $form->addField($setting['varname'],'textarea',array('label'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title'),'fieldset'=>'settings','value'=>$setting['value'],'help'=>Strings::nl2br_replace($PMDR->getLanguage('setting_'.$setting['varname'].'_desc')),'help_title'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title').' ('.$setting['varname'].')'));
    } elseif($setting['optioncode_type'] == 'checkbox') {
        $options = explode("\r\n",trim($setting['optioncode'],"\r\n"));
        if(count($options) > 1) {
            $form_options = array();
            foreach($options as $option) {
                $option = explode('|',$option);
                $form_options[$option[1]] = $option[0];
            }
            $form->addField($setting['varname'],'checkbox',array('label'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title'),'fieldset'=>'settings','options'=>$form_options,'value'=>$setting['value'],'help'=>Strings::nl2br_replace($PMDR->getLanguage('setting_'.$setting['varname'].'_desc')),'help_title'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title').' ('.$setting['varname'].')'));
        } else {
            $form->addField($setting['varname'],'checkbox',array('label'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title'),'fieldset'=>'settings','value'=>$setting['value'],'help'=>Strings::nl2br_replace($PMDR->getLanguage('setting_'.$setting['varname'].'_desc')),'help_title'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title').' ('.$setting['varname'].')'));
        }
    } elseif($setting['optioncode_type'] == 'file') {
        $url_image = null;
        if(!empty($setting['value'])) {
            // Add a random value to prevent caching
            $url_image = get_file_url(TEMP_UPLOAD_PATH.$setting['value']).'?random='.time();
        }
        $form->addField($setting['varname'],'file',array('label'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title'),'fieldset'=>'settings','value'=>$setting['value'],'help'=>Strings::nl2br_replace($PMDR->getLanguage('setting_'.$setting['varname'].'_desc')),'help_title'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title').' ('.$setting['varname'].')','delete_url'=>rebuild_url(array('delete'=>$setting['varname'])),'url_image'=>$url_image,'options'=>array('url_allow'=>true)));
    } else {
        $form->addField($setting['varname'],$setting['optioncode_type'],array('label'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title'),'fieldset'=>'settings','value'=>$setting['value'],'help'=>Strings::nl2br_replace($PMDR->getLanguage('setting_'.$setting['varname'].'_desc')),'help_title'=>$PMDR->getLanguage('setting_'.$setting['varname'].'_title').' ('.$setting['varname'].')'));
    }
    if(in_array($setting['varname'],$non_empty)) {
        $form->addValidator($setting['varname'],new Validate_NonEmpty());
    }
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));


$template_content_form->set('form',$form);

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_settings.tpl');
$template_content->set('title',$PMDR->getLanguage('setting_group_'.$_GET['group']).' '.$PMDR->getLanguage('admin_settings'));
$template_content->set('form',$template_content_form);

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(DEMO_MODE) {
        foreach($demo_settings_disabled AS $demo_setting) {
            if(in_array($demo_setting,array_keys($data)) AND $data[$demo_setting] != $settings[$demo_setting]['value']) {
                $data[$demo_setting] = $settings[$demo_setting]['value'];
                $PMDR->addMessage('notice','This setting may not be edited in the demo: '.$PMDR->getLanguage('setting_'.$settings[$demo_setting]['varname'].'_title'));
            }
        }
    }
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $validation_rules = $db->GetAssoc("SELECT varname, validationcode FROM ".T_SETTINGS." WHERE varname IN('".implode('\',\'',array_keys($data))."') AND validationcode != ''");
        $validation_error = false;
        foreach($validation_rules as $varname=>$rule) {
            $validation_data = $data[$varname];
            if(!eval($rule)) {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_settings_invalid_value',array($PMDR->getLanguage('setting_'.$varname.'_title'))));
                $validation_error = true;
            }
            unset($validation_data);
        }
        if(isset($data['allowed_html_tags'])) {
            $data['allowed_html_tags'] = $PMDR->get('HTML')->tagsToString($PMDR->get('HTML')->tagsToArray($data['allowed_html_tags']));
        }
        if(isset($data['logo'])) {
            try {
                $image_handler = $PMDR->get('Image_Handler',$data['logo']);
                $image_handler->saveToPath(TEMP_UPLOAD_PATH);
                if(!empty($settings['logo']['value']) AND $settings['logo']['value'] != $image_handler->file_name) {
                    unlink_file(TEMP_UPLOAD_PATH.$settings['logo']['value']);
                }
                $data['logo'] = $image_handler->file_name;
            } catch(Exception $e) {
                unset($data['logo']);
            }
        }
        if(isset($data['invoice_logo'])) {
            try {
                $image_handler = $PMDR->get('Image_Handler',$data['invoice_logo']);
                $image_handler->saveToPath(TEMP_UPLOAD_PATH);
                if(!empty($settings['invoice_logo']['value']) AND $settings['invoice_logo']['value'] != $image_handler->file_name) {
                    unlink_file(TEMP_UPLOAD_PATH.$settings['invoice_logo']['value']);
                }
                $data['invoice_logo'] = $image_handler->file_name;
            } catch(Exception $e) {
                unset($data['invoice_logo']);
            }
        }
        if(!$validation_error) {
            foreach($data as $name=>$value) {
                $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array($value, $name));
            }
            $PMDR->addMessage('success',$PMDR->getLanguage('admin_settings_edited'));
            redirect(null,array('group'=>$_GET['group']));
        }
    }
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_settings_menu.tpl');
$template_page_menu->set('custom',$db->GetOne("SELECT COUNT(*) FROM ".T_SETTINGS." WHERE grouptitle='custom'"));
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>