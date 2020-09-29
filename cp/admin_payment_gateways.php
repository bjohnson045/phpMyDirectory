<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_gateways','admin_transactions'));

$PMDR->get('Authentication')->checkPermission('admin_paygateways');

if($PMDR->getConfig('disable_billing')) {
    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_general_disable_billing'));
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $gateways = array();
    $gateways_directory = PMDROOT.'/modules/processors/';
    if(is_dir($gateways_directory)) {
        if ($dh = opendir($gateways_directory)) {
            while(($folder = readdir($dh)) !== false) {
                if(is_dir(PMDROOT.'/modules/processors/'.$folder.'/') AND $folder != '.' AND $folder != '..') {
                    $gateways[] = $folder;
                }
            }
            closedir($dh);
        }
    }
    sort($gateways);

    $database_gateways = $db->GetCol("SELECT id FROM ".T_GATEWAYS);

    $install_gateways = array_diff($gateways,$database_gateways);

    foreach($install_gateways AS $gateway) {
        $db->Execute("INSERT INTO ".T_GATEWAYS." (id,display_name,enabled,hidden,ordering,settings) VALUES (?,?,?,?,?,'')",array($gateway,$gateway,0,0,0));
    }

    $uninstall_gateways = array_diff($database_gateways,$gateways);
    $db->Execute("DELETE FROM ".T_GATEWAYS." WHERE id IN('".implode("','",$uninstall_gateways)."')");

    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_gateways_name'));
    $table_list->addColumn('processor_image',$PMDR->getLanguage('admin_gateways_logo'));
    $table_list->addColumn('enabled',$PMDR->getLanguage('admin_gateways_enabled'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_GATEWAYS));
    $records = $db->GetAll("SELECT * FROM ".T_GATEWAYS." ORDER BY enabled DESC, ordering ASC, id ASC LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['enabled'] = $PMDR->get('HTML')->icon($record['enabled']);
        if($logo = get_file_url(PMDROOT.'/modules/processors/'.$record['id'].'/logo.*',true)) {
            $records[$key]['processor_image'] = '<img src="'.$logo.'">';
        }
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);

    $template_content->set('title',$PMDR->getLanguage('admin_gateways'));
    $template_content->set('content',$table_list->render());
} elseif($_GET['action']=='edit') {
    $processor = $db->GetRow("SELECT * FROM ".T_GATEWAYS." WHERE id=?",array($_GET['id']));
    if(!$processor OR !file_exists(PMDROOT.'/modules/processors/'.$processor['id'].'/'.$processor['id'].'_admin.php')) {
        redirect(BASE_URL.'/admin_payment_gateways.php');
    }

    $form = $PMDR->get('Form');
    $form->addFieldSet('details',array('legend'=>$PMDR->getLanguage('admin_gateways_configuration')));
    $form->addField('enabled','checkbox',array('label'=>$PMDR->getLanguage('admin_gateways_enabled'),'fieldset'=>'details'));
    $form->addField('hidden','checkbox',array('label'=>$PMDR->getLanguage('admin_gateways_hidden'),'fieldset'=>'details','help'=>$PMDR->getLanguage('admin_gateways_hidden_help')));
    $form->addField('display_name','text',array('label'=>$PMDR->getLanguage('admin_gateways_display_name'),'fieldset'=>'details'));
    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('admin_gateways_order'),'fieldset'=>'details'));

    include(PMDROOT.'/modules/processors/'.$processor['id'].'/'.$processor['id'].'_admin.php');

    $form->addValidator('display_name',new Validate_NonEmpty());

    $details = array();
    if(is_array(unserialize($processor['settings']))) {
        $details = unserialize($processor['settings']);
    }
    $form->loadValues(array_merge($details, $processor));

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            foreach($form->elements AS $name=>$element) {
                if(!in_array($name,array('submit','enabled','hidden','display_name','order'))) {
                    $settings[$name] = $data[$name];
                }
            }
            $settings = serialize($settings);
            $db->Execute('UPDATE '.T_GATEWAYS.' SET display_name=?, enabled=?, hidden=?, settings=?, ordering=? WHERE id=?',array($data['display_name'],$data['enabled'],$data['hidden'],$settings,$data['ordering'],$_GET['id']));

            if($data['enabled'] == 1 AND $processor['enabled'] == 0) {
                $product_pricing = $db->GetAll("SELECT id, gateway_ids FROM ".T_PRODUCTS_PRICING);
                foreach($product_pricing AS $pricing) {
                    $gateways = array_filter(explode(',',$pricing['gateway_ids']));
                    $gateways[] = $processor['id'];
                    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET gateway_ids='".implode(',',array_unique($gateways))."' WHERE id=?",array($pricing['id']));
                }
                unset($gateways,$product_pricing,$pricing);
            }

            $PMDR->addMessage('success',sprintf($PMDR->getLanguage('messages_updated'),$data['display_name'],$PMDR->getLanguage('admin_gateways')));
            redirect();
        }
    }

    $template_content->set('title',$processor['id']);
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_payment_gateways_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>