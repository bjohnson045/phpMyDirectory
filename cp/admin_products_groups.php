<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_products','admin_products_pricing'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_products_groups_view');

$tablegateway = $PMDR->get('TableGateway',T_PRODUCTS_GROUPS);

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_products_groups_delete');
    if(!$db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS." WHERE group_id=?",array($_GET['id']))) {
        $tablegateway->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_products_groups'))),'delete');
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_products_groups'));
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->addColumn('id',$PMDR->getLanguage('admin_products_groups_id'));
    $table_list->addColumn('name',$PMDR->getLanguage('admin_products_groups_name'));
    $table_list->addColumn('hidden',$PMDR->getLanguage('admin_products_groups_hidden'));
    $table_list->addColumn('ordering',$PMDR->getLanguage('admin_products_groups_order').' [<a href="" onclick="updateOrdering(\''.T_PRODUCTS_GROUPS.'\',\'table_list_form\'); return false;">'.$PMDR->getLanguage('admin_update').'</a>]');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS_GROUPS));
    $records = $db->GetAll("SELECT pg.*, COUNT(p.id) AS product_count FROM ".T_PRODUCTS_GROUPS." pg LEFT JOIN ".T_PRODUCTS." p ON pg.id=p.group_id GROUP BY pg.id ORDER BY ordering LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        $records[$key]['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $records[$key]['hidden'] = $PMDR->get('HTML')->icon($record['hidden']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        if(!$record['product_count']) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_products_groups_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('group_details',array('legend'=>$PMDR->getLanguage('admin_products_group')));
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_products_groups_name'),'fieldset'=>'group_details','help'=>$PMDR->getLanguage('admin_products_groups_help_name')));
    $form->addField('user_limit','text_unlimited',array('label'=>$PMDR->getLanguage('admin_products_groups_user_limit'),'fieldset'=>'group_details','help'=>$PMDR->getLanguage('admin_products_groups_user_limit_help')));
    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('admin_products_groups_order'),'value'=>0,'fieldset'=>'group_details','help'=>$PMDR->getLanguage('admin_products_groups_help_order')));
    $form->addField('hidden','checkbox',array('label'=>$PMDR->getLanguage('admin_products_groups_hidden'),'fieldset'=>'group_details','help'=>$PMDR->getLanguage('admin_products_groups_help_hidden')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('name',new Validate_NonEmpty());
    $form->addValidator('ordering',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_products_groups_edit'));
        $form->loadValues($tablegateway->getRow(array('id'=>$_GET['id'])));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_products_groups_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $group_id = $tablegateway->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_products_groups'))),'insert');
                $PMDR->addMessage('warning','Please add at least one product to the product group that was just created.');
                redirect_url(BASE_URL_ADMIN.'/admin_products.php?action=add&group_id='.$group_id);
            } elseif($_GET['action'] == 'edit') {
                $tablegateway->update($data, array('id'=>$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_products_groups'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_products_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>