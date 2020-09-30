<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_tax'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_tax_rates_view');

if($PMDR->getConfig('disable_billing')) {
    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_general_disable_billing'));
}

$tax = $PMDR->get('Tax');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_tax_rates_delete');
    $tax->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_tax'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('level');
    $table_list->addColumn('name');
    $table_list->addColumn('country');
    $table_list->addColumn('state');
    $table_list->addColumn('tax_rate',$PMDR->getLanguage('admin_tax_rate'));
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_TAX." ORDER BY level,country,state LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['tax_rate'] = (float) $record['tax_rate'];
        $records[$key]['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        if(empty($record['country'])) {
            $records[$key]['country'] = 'All Countries';
        } else {
            $records[$key]['country'] = $PMDR->get('Cleaner')->clean_output($record['country']);
        }
        $records[$key]['state'] = $PMDR->get('Cleaner')->clean_output($record['state']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('admin_tax_rates'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_tax_rates_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('tax',array('legend'=>$PMDR->getLanguage('admin_tax_information')));
    $form->addField('level','select',array('options'=>array('1'=>'1','2'=>'2')));
    $form->addField('name','text');
    $form->addField('country','select',array('first_option'=>array(''=>'All Countries'),'options'=>get_countries_array()));
    $form->addField('state','text');
    $form->addField('tax_rate','text',array('label'=>$PMDR->getLanguage('admin_tax_rate')));
    $form->addField('submit','submit');

    $form->addValidator('name',new Validate_NonEmpty());
    $form->addValidator('tax_rate',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_tax_edit'));
        $form->loadValues($db->GetRow("SELECT * FROM ".T_TAX." WHERE id=?",array($_GET['id'])));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_tax_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $tax->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_tax'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $tax->update($data, array('id'=>$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_tax'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_tax_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>