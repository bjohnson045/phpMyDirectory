<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

// Get authentication
$PMDR->get('Authentication')->authenticate();

// Load language
$PMDR->loadLanguage(array('admin_banners'));

// Check for permissions to view
$PMDR->get('Authentication')->checkPermission('admin_banners_types_view');

/** @var BannerTypes */
$banner_types = $PMDR->get('Banners_Types');

// Delete a banner
if($_GET['action'] == 'delete') {
    // Check for permission to delete
    $PMDR->get('Authentication')->checkPermission('admin_banners_types_delete');
    // Delete the banner
    $banner_types->delete($_GET['id']);
    // Set confirmation message and redidrect
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_banner_types'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id');
    $table_list->addColumn('name');
    $table_list->addColumn('description');
    $table_list->addColumn('manage');
    $table_list->setTotalResults($banner_types->getCount());
    $records = $banner_types->getRowsLimit($table_list->page_data['limit1'],$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        $record['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= '
        <script type="text/javascript">
        $(document).ready(function(){
            $("#banner_type_code'.$record['id'].'").dialog({
                 buttons: {
                    "Close": function() { $(this).dialog("close"); }
                 },
                 width: 500,
                 height: 300,
                 autoOpen: false,
                 modal: true,
                 resizable: false,
                 title: "'.$PMDR->getLanguage('admin_banners_types_banner_code').'"
            });
            $("#banner_type_code_link'.$record['id'].'").click(function() {
                $("#banner_type_code'.$record['id'].'").dialog("open");
            });
        });
        </script>
        <div id="banner_type_code'.$record['id'].'" style="margin-top: 5px; display: none;">
            <p><strong>'.$PMDR->getLanguage('admin_banners_types_template_code').':</strong></p>
            <input type="text" class="form-control code" readonly="readonly" value="'.$PMDR->get('Cleaner')->clean_output('<?php echo $PMDR->get(\'Banner_Display\')->getBanner('.$record['id'].'); ?>').'">
            <br />
            <p><strong>'.$PMDR->getLanguage('admin_banners_types_remote_code').':</strong></p>
            <textarea class="form-control code" readonly="readonly" style="height: 75px;"><script type="text/javascript" src="'.BASE_URL.'/remote_banner.php?type='.$record['id'].'"></script></textarea>
        </div>
        ';
        $record['manage'] .= $PMDR->get('HTML')->icon('code',array('id'=>'banner_type_code_link'.$record['id'],'href'=>'#','label'=>$PMDR->getLanguage('admin_banners_types_banner_code')));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $PMDR->loadJavascript('
    <script type="text/javascript">
    $(document).ready(function(){
        $(".template_toggle > .template_toggle_link").click(function() {
            $(this).next(".toggle_content").toggle();
        });
    });
    </script>',50);
    $template_content->set('title',$PMDR->getLanguage('admin_banners_types'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_banners_types_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('types',array('legend'=>$PMDR->getLanguage('admin_banners_types')));
    $types = array(
        'image'=>'Image',
        'html'=>'HTML',
        'text'=>'Text'
    );
    $form->addField('type','select',array('label'=>$PMDR->getLanguage('admin_banners_type'),'options'=>$types,'onchange'=>'toggle_banner_type();'));
    $form->addField('name','text');
    $form->addField('description','textarea');
    $form->addField('character_limit','text',array('value'=>'100'));
    $form->addField('width','text');
    $form->addField('height','text');
    $form->addField('filesize','text');
    $form->addField('submit','submit');

    $form->addValidator('name',new Validate_NonEmpty());
    $form->addValidator('width',new Validate_NonEmpty());
    $form->addValidator('height',new Validate_NonEmpty());
    $form->addValidator('filesize',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_banners_types_edit'));
        $form->loadValues($banner_types->getRow($_GET['id']));
        $form->setFieldAttribute('type','type','readonly');
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_banners_types_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if($data['type'] != 'image') {
            $form->removeValidator('width');
            $form->removeValidator('height');
            $form->removeValidator('filesize');
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $banner_types->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_banners_types'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $banner_types->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_banners_types'))),'update');
                redirect();
            }
        }
    }
    $javascript = '
    <script type="text/javascript">
    function toggle_banner_type() {
        $(\'#width-control-group,#height-control-group,#filesize-control-group\').toggle($(\'#type\').val() == \'image\');
        $(\'#character_limit-control-group\').toggle($(\'#type\').val() == \'text\');
        return false;
    }
    $(document).ready(toggle_banner_type);
    </script>';
    $template_content->set('content',$form->toHTML().$javascript);
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_banners_types_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>