<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_sitemap_xml'));

$PMDR->get('Authentication')->authenticate();

if($_GET['action'] == 'delete') {
    $PMDR->get('Sitemap_XML')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_sitemap_xml'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->sortable(T_SITEMAP_XML,$PMDR->getLanguage('admin_sitemap_xml_ordering'));
    $table_list->addColumn('url');
    $table_list->addColumn('active');
    $table_list->addColumn('manage');
    $table_list->setTotalResults($PMDR->get('Sitemap_XML')->getCount());
    $records = $PMDR->get('Sitemap_XML')->GetRows(array(),array('ordering'=>'ASC'),$table_list->page_data['limit1'],$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $record['active'] = $PMDR->get('HTML')->icon($record['active']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_sitemap_xml'));
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information');
    $form->addField('url','url');
    $sitemap_priorities = array(
        '0.0'=>'0.0',
        '0.1'=>'0.1',
        '0.2'=>'0.2',
        '0.3'=>'0.3',
        '0.4'=>'0.4',
        '0.5'=>'0.5',
        '0.6'=>'0.6',
        '0.7'=>'0.7',
        '0.8'=>'0.8',
        '0.9'=>'0.9',
        '1.0'=>'1.0',
    );
    $form->addField('priority','select',array('label'=>$PMDR->getLanguage('admin_sitemap_xml_priority'),'options'=>$sitemap_priorities,'first_option'=>array('NULL'=>''),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_sitemap_xml_priority_help')));
    $form->addField('date_updated','datetime',array('label'=>$PMDR->getLanguage('admin_sitemap_xml_date_updated'),'fieldset'=>'information','value'=>$PMDR->get('Dates')->dateTimeNow()));
    $sitemap_frequencies = array(
        'always'=>$PMDR->getLanguage('admin_sitemap_xml_always'),
        'hourly'=>$PMDR->getLanguage('admin_sitemap_xml_hourly'),
        'daily'=>$PMDR->getLanguage('admin_sitemap_xml_daily'),
        'weekly'=>$PMDR->getLanguage('admin_sitemap_xml_weekly'),
        'monthly'=>$PMDR->getLanguage('admin_sitemap_xml_monthly'),
        'yearly'=>$PMDR->getLanguage('admin_sitemap_xml_yearly'),
        'never'=>$PMDR->getLanguage('admin_sitemap_xml_never'),
    );
    $form->addField('frequency','select',array('label'=>$PMDR->getLanguage('admin_sitemap_xml_frequency'),'options'=>$sitemap_frequencies,'first_option'=>array('NULL'=>''),'fieldset'=>'information'));
    $form->addField('active','checkbox',array('value'=>1));
    $form->addField('submit','submit');

    $form->addValidator('url',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_sitemap_xml_edit'));
        $form->loadValues($PMDR->get('Sitemap_XML')->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_sitemap_xml_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Sitemap_XML')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['url'],$PMDR->getLanguage('admin_sitemap_xml'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Sitemap_XML')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['url'],$PMDR->getLanguage('admin_sitemap_xml'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_sitemap_xml_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>