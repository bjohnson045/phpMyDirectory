<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_locations'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_locations_view');

if(!value($_GET,'step')) {
    $_GET['step'] = 1;
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_locations_setup.tpl');

$template_content->set('step',$_GET['step']);

$form = $PMDR->get('Form');
$form->enctype = 'multipart/form-data';
$form->setAttributes(array('class'=>'form'));
$form->addFieldSet('details');
$form->addField('country','radio',array('label'=>'Will your directory serve more than one country?','fieldset'=>'details','inline'=>true,'options'=>array('dynamic'=>'Yes','static'=>'No')));
$form->addField('country_type','radio',array('label'=>'Would you like users to type in their country or select a country from a list?','fieldset'=>'details','inline'=>true,'wrapper_attributes'=>array('style'=>'display: none'),'options'=>array('type_in'=>'Type In','list'=>'List')));
$form->addField('country_static','text',array('label'=>'Please type the country you will support','fieldset'=>'details','wrapper_attributes'=>array('style'=>'display: none')));
$form->addField('country_option','text',array('label'=>'Please type one country you will support','fieldset'=>'details','wrapper_attributes'=>array('style'=>'display: none')));
$form->addFieldNote('country_option','You will have the opportunity to add more countries or import a locations file after running the wizard.');

$form->addField('state','radio',array('label'=>'Will your directory serve more than one state/region?','fieldset'=>'details','inline'=>true,'wrapper_attributes'=>array('style'=>'display: none'),'options'=>array('dynamic'=>'Yes','static'=>'No')));
$form->addField('state_type','radio',array('label'=>'Would you like users to type in their state/region or select a state/region from a list?','fieldset'=>'details','inline'=>true,'wrapper_attributes'=>array('style'=>'display: none'),'options'=>array('type_in'=>'Type In','list'=>'List')));
$form->addField('state_static','text',array('label'=>'Please type the state/region you will support','fieldset'=>'details','wrapper_attributes'=>array('style'=>'display: none')));
$form->addField('state_option','text',array('label'=>'Please type one state you will support','fieldset'=>'details','wrapper_attributes'=>array('style'=>'display: none')));
$form->addFieldNote('state_option','You will have the opportunity to add more states/regions or import a locations file after running the wizard.');

$form->addField('city','radio',array('label'=>'Will your directory serve more than one city/town?','fieldset'=>'details','inline'=>true,'options'=>array('dynamic'=>'Yes','static'=>'No'),'wrapper_attributes'=>array('style'=>'display: none')));
$form->addField('city_type','radio',array('label'=>'Would you like users to type in their city/town or select a city/town from a list?','fieldset'=>'details','inline'=>true,'wrapper_attributes'=>array('style'=>'display: none'),'options'=>array('type_in'=>'Type In','list'=>'List')));
$form->addField('city_static','text',array('label'=>'Please type the city/town you will support','fieldset'=>'details','wrapper_attributes'=>array('style'=>'display: none')));
$form->addField('city_option','text',array('label'=>'Please type one city you will support','fieldset'=>'details','wrapper_attributes'=>array('style'=>'display: none')));
$form->addFieldNote('city_option','You will have the opportunity to add more cities/towns or import a locations file after running the wizard.');

$form->addField('country_label','text',array('label'=>'Label used for the Country field (usually "Country")','fieldset'=>'details','value'=>'Country','wrapper_attributes'=>array('style'=>'display: none')));
$form->addField('state_label','text',array('label'=>'Label used for the State field (usually "State")','fieldset'=>'details','value'=>'State','wrapper_attributes'=>array('style'=>'display: none')));
$form->addField('city_label','text',array('label'=>'Label used for the City field (usually "City")','fieldset'=>'details','value'=>'City','wrapper_attributes'=>array('style'=>'display: none')));

$form->addValidator('country', new Validate_NonEmpty());
$form->addValidator('state', new Validate_NonEmpty());
$form->addValidator('city', new Validate_NonEmpty());

$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
$template_content->set('form',$form->toHTML());
if($form->wasSubmitted('submit') OR $form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    if($data['country_type'] == 'list' AND empty($data['country_option'])) {
        $form->addError('Please enter a country.','country_option');
    } elseif($data['state_type'] == 'list' AND empty($data['state_option'])) {
        $form->addError('Please enter a state.','state_option');
    } elseif($data['city_type'] == 'list' AND empty($data['city_option'])) {
        $form->addError('Please enter a city.','city_option');
    }

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        // Reset settings
        $db->Execute("UPDATE ".T_SETTINGS." SET value='' WHERE varname IN ('map_country','map_state','map_city','map_country_static','map_state_static','map_city_static')");
        $db->Execute("UPDATE ".T_SETTINGS." SET value=0 WHERE varname IN ('location_text_1','location_text_2','location_text_3')");
        $db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE variablename LIKE 'general_locations_text_%' AND languageid != -1");
        $db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE variablename LIKE 'general_locations_levels_%' AND languageid != -1");

        $location_text_field = 1;
        $location_field = 1;
        $placement_id = 1;

        $messages = array();
        $messages[] = 'Location setup is now complete.';
        if($data['country'] == 'static') {
            $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array($data['country_static'],'map_country_static'));
            $messages[] = 'All of the listings will use the country '.$data['country_static'].'.';
        } else {
            if($data['country_type'] == 'type_in') {
                $db->Execute("UPDATE ".T_SETTINGS." SET value=1 WHERE varname=?",array('location_text_'.$location_text_field));
                $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array('location_text_'.$location_text_field,'map_country'));
                $PMDR->get('Phrases')->updatePhrase($PMDR->getConfig('language'), 'general_locations', 'general_locations_text_'.$location_text_field, $data['country_label']);
                $location_text_field++;
                $messages[] = 'Users will type in their country name in an input field.';
            } else {
                $placement_id = $PMDR->get('Locations')->insert(array('title'=>$data['country_option']));
                $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array('location_'.$location_field,'map_country'));
                if(!empty($data['country_label'])) {
                    $PMDR->get('Phrases')->updatePhrase($PMDR->getConfig('language'), 'general_locations', 'general_locations_levels_'.$location_field, $data['country_label']);
                }
                $location_field++;
                $messages[] = 'Users will select their country from a list.  The country "<a href="admin_locations.php?action=edit&id='.$placement_id.'">'.$data['country_option'].'</a>" has been added to the list.  You may also <a href="admin_locations.php?action=add">add additional countries</a>.';
            }
        }
        if($data['state'] == 'static') {
            $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array($data['state_static'],'map_state_static'));
            $messages[] = 'All of the listings will use the state '.$data['state_static'].'.';
        } else {
            if($data['state_type'] == 'type_in') {
                $db->Execute("UPDATE ".T_SETTINGS." SET value=1 WHERE varname=?",array('location_text_'.$location_text_field));
                $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array('location_text_'.$location_text_field,'map_state'));
                $PMDR->get('Phrases')->updatePhrase($PMDR->getConfig('language'), 'general_locations', 'general_locations_text_'.$location_text_field, $data['state_label']);
                $location_text_field++;
                $messages[] = 'Users will type in their state name in an input field.';
            } else {
                $placement_id = $PMDR->get('Locations')->insert(array('title'=>$data['state_option'],'placement'=>'subcategory','placement_id'=>$placement_id));
                $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array('location_'.$location_field,'map_state'));
                if(!empty($data['state_label'])) {
                    $PMDR->get('Phrases')->updatePhrase($PMDR->getConfig('language'), 'general_locations', 'general_locations_levels_'.$location_field, $data['state_label']);
                }
                $location_field++;
                $messages[] = 'Users will select their state from a list.  The state "<a href="admin_locations.php?action=edit&id='.$placement_id.'">'.$data['state_option'].'</a>" has been added to the list.  You may also <a href="admin_locations.php?action=add">add additional states</a>.';
            }
        }
        if($data['city'] == 'static') {
            $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array($data['city_static'],'map_city_static'));
            $messages[] = 'All of the listings will use the city '.$data['city_static'].'.';
        } else {
            if($data['city_type'] == 'type_in') {
                $db->Execute("UPDATE ".T_SETTINGS." SET value=1 WHERE varname=?",array('location_text_'.$location_text_field));
                $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array('location_text_'.$location_text_field,'map_city'));
                $PMDR->get('Phrases')->updatePhrase($PMDR->getConfig('language'), 'general_locations', 'general_locations_text_'.$location_text_field, $data['city_label']);
                $messages[] = 'Users will type in their city name in an input field.';
            } else {
                $city_id = $PMDR->get('Locations')->insert(array('title'=>$data['city_option'],'placement'=>'subcategory','placement_id'=>$placement_id));
                $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array('location_'.$location_field,'map_city'));
                if(!empty($data['city_label'])) {
                    $PMDR->get('Phrases')->updatePhrase($PMDR->getConfig('language'), 'general_locations', 'general_locations_levels_'.$location_field, $data['city_label']);
                }
                $messages[] = 'Users will select their city from a list.  The city "<a href="admin_locations.php?action=edit&id='.$city_id.'">'.$data['city_option'].'</a>" has been added to the list.  You may also <a href="admin_locations.php?action=add">add additional cities</a>.';
            }
        }
        $PMDR->addMessage('success',implode('<br />',$messages));
        if($location_text_field == 3) {
            redirect('admin_settings.php?group=locations');
        } else {
            redirect('admin_locations.php');
        }
    }
} else {
    if($PMDR->get('Locations')->getSize() > 0) {
        $PMDR->addMessage('warning','Existing locations have been detected.  The wizard may only be used if locations have not already been entered.');
        redirect_url(BASE_URL_ADMIN.'/admin_locations.php');
    }
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_locations_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>