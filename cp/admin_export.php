<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_export','general_locations'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_export');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_export'));

if($_GET['action'] == 'download') {
    $serve = $PMDR->get('ServeFile');
    $serve->serve('Export'.$_GET['id'].'.csv',file_get_contents(TEMP_UPLOAD_PATH.'Export'.$_GET['id'].'.csv'));
}

if(!isset($_GET['action'])) {
    $fields_array = array(
        'id'=>$PMDR->getLanguage('admin_export_listing_id'),
        'user_id'=>$PMDR->getLanguage('admin_export_listing_user_id'),
        'pricing_id'=>$PMDR->getLanguage('admin_export_listing_pricing_id'),
        'status'=>$PMDR->getLanguage('admin_export_listing_status'),
        'title'=>$PMDR->getLanguage('admin_export_listing_title'),
        'friendly_url'=>$PMDR->getLanguage('admin_export_listing_friendly_url'),
        'description'=>$PMDR->getLanguage('admin_export_listing_description'),
        'description_short'=>$PMDR->getLanguage('admin_export_listing_description_short'),
        'keywords'=>$PMDR->getLanguage('admin_export_listing_keywords'),
        'category'=>$PMDR->getLanguage('admin_export_category'),
        'phone'=>$PMDR->getLanguage('admin_export_listing_phone'),
        'fax'=>$PMDR->getLanguage('admin_export_listing_fax'),
        'address1'=>$PMDR->getLanguage('admin_export_listing_address1'),
        'address2'=>$PMDR->getLanguage('admin_export_listing_address2'),
        'location'=>$PMDR->getLanguage('admin_export_location'),
        'zip'=>$PMDR->getLanguage('admin_export_listing_zip'),
        'latitude'=>$PMDR->getLanguage('admin_export_listing_latitude'),
        'longitude'=>$PMDR->getLanguage('admin_export_listing_longitude'),
        'email'=>$PMDR->getLanguage('admin_export_listing_email'),
        'www'=>$PMDR->getLanguage('admin_export_listing_www'),
        'logo_url'=>$PMDR->getLanguage('admin_export_listing_logo_url'),
        'impressions'=>$PMDR->getLanguage('admin_export_listing_impressions'),
        'impressions_search'=>$PMDR->getLanguage('admin_export_listing_impressions_search'),
        'emails'=>$PMDR->getLanguage('admin_export_listing_emails'),
        'website_clicks'=>$PMDR->getLanguage('admin_export_listing_website_clicks'),
        'banner_impressions'=>$PMDR->getLanguage('admin_export_listing_banner_impressions'),
        'banner_clicks'=>$PMDR->getLanguage('admin_export_listing_banner_clicks'),
        'facebook_page_id'=>$PMDR->getLanguage('admin_export_listing_facebook_page_id'),
        'twitter_id'=>$PMDR->getLanguage('admin_export_listing_twitter_id'),
        'google_page_id'=>$PMDR->getLanguage('admin_export_listing_google_page_id'),
        'linkedin_id'=>$PMDR->getLanguage('admin_export_listing_linkedin_id'),
        'linkedin_company_id'=>$PMDR->getLanguage('admin_export_listing_linkedin_company_id'),
        'pinterest_id'=>$PMDR->getLanguage('admin_export_listing_pinterest_id'),
        'youtube_id'=>$PMDR->getLanguage('admin_export_listing_youtube_id'),
        'foursquare_id'=>$PMDR->getLanguage('admin_export_listing_foursquare_id'),
        'instagram_id'=>$PMDR->getLanguage('admin_export_listing_instagram_id')
    );
    if($PMDR->getConfig('location_text_1')) {
        $fields_array['location_text_1'] = $PMDR->getLanguage('general_locations_text_1');
    }
    if($PMDR->getConfig('location_text_2')) {
        $fields_array['location_text_2'] = $PMDR->getLanguage('general_locations_text_2');
    }
    if($PMDR->getConfig('location_text_3')) {
        $fields_array['location_text_3'] = $PMDR->getLanguage('general_locations_text_3');
    }
    $fields = $PMDR->get('Fields')->getFields('listings');
    foreach($fields as $key=>$value) {
        $fields_array['custom_'.$value['id']] = $value['name'];
    }

    $form = $PMDR->get('Form');
    $form->addFieldSet('settings',array('legend'=>$PMDR->getLanguage('admin_export_settings')));
    $form->addFieldSet('filters',array('legend'=>$PMDR->getLanguage('admin_export_filter')));
    $form->addFieldSet('fields',array('legend'=>$PMDR->getLanguage('admin_export_fields'),'help'=>$PMDR->getLanguage('admin_export_help_fields')));
    $form->addField('limit','text',array('label'=>$PMDR->getLanguage('admin_export_limit'),'fieldset'=>'settings','help'=>$PMDR->getLanguage('admin_export_help_limit')));
    $form->addField('format','select',array('label'=>$PMDR->getLanguage('admin_export_format'),'fieldset'=>'settings','help'=>$PMDR->getLanguage('admin_export_help_format'),'options'=>array('primary_category'=>$PMDR->getLanguage('admin_export_primary_category_only'),'all_categories'=>$PMDR->getLanguage('admin_export_all_categories'),'import'=>$PMDR->getLanguage('admin_export_import_compatible'))));
    $delimiters = array(';'=>';',':'=>':',','=>',','|'=>'|','%'=>'%','@'=>'@','&'=>'&','*'=>'*');
    $form->addField('delimiter','select',array('label'=>$PMDR->getLanguage('admin_export_delimiter'),'fieldset'=>'settings','value'=>'Delimiter','options'=>$delimiters,'help'=>$PMDR->getLanguage('admin_export_help_delimiter')));
    $form->addField('delimiter2','text',array('label'=>$PMDR->getLanguage('admin_export_delimiter_own'),'fieldset'=>'settings'));
    $form->addField('category_list','tree_select_multiple',array('label'=>$PMDR->getLanguage('admin_export_category_list'),'fieldset'=>'filters','value'=>'','options'=>$PMDR->get('Categories')->getSelect()));
    $form->addField('products','tree_select_expanding_checkbox',array('fieldset'=>'filters','label'=>$PMDR->getLanguage('admin_export_products'),'value'=>'','options'=>array('type'=>'products_tree','hidden'=>true)));
    $form->addField('fields','checkbox',array('label'=>$PMDR->getLanguage('admin_export_fields'),'fieldset'=>'fields','options'=>$fields_array,'checkall'=>true));
    $form->addField('export_submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addFieldNote('category_list',$PMDR->getLanguage('admin_export_ctrl'));

    $form->addValidator('limit',new Validate_Numeric());

    $template_content->set('content', $form->toHTML());

    if($form->wasSubmitted('export_submit')) {

        $data = $form->loadValues();

        if(!isset($data['category_list'])) {
            $data['category_list'] = array();
        }

        if(!is_writable(TEMP_UPLOAD_PATH)) {
            $form->addError('Not writable');
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if(empty($data['limit'])) {
                $data['limit'] = 0;
            }
            $delimiter = ($data['delimiter2'] != "") ? $data['delimiter2'] : $data['delimiter'];
            $db->Execute("INSERT INTO ".T_EXPORTS." (amount,format,delimiter,categories,products,data) VALUES (?,?,?,?,?,?)",array($data['limit'],$data['format'],$delimiter,implode(',',$data['category_list']),implode(',',$data['products']),implode(',',$data['fields'])));
            redirect(array('action'=>'export','id'=>$db->Insert_ID()));
        }
    }
} else {
    $script = '
    <script type="text/javascript">
    var exportOnComplete = function(data) {
        $("#status").progressbar("option", "value", data.percent);
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            $("#status").progressbar("destroy");
            $("#status_percent").hide();
            $("#status").html(\''.$PMDR->getLanguage('admin_export_complete',array('<a href="'.BASE_URL_ADMIN.'/admin_export.php?action=download&id=\'+data.id+\'">Export\'+data.id+\'.csv</a>')).'\');
        } else {
            exportStart(data.start+data.num,data.num);
        }
    };

    var exportStart = function(start,num) {
        if(start == 0) {
            $("#status_percent").html("0%");
            $("#status").progressbar({ value: 0 });
        }
        $.ajax({ data: ({ action: "admin_export", start: start, num: num, id: "'.$_GET['id'].'" }), success: exportOnComplete, dataType: "json"});
    };
    $(document).ready(function() {
        exportStart(0,100);
    });
    </script>';
    $template_content->set('content',$script.'<div style="width: 500px; float: left;" id="status"></div><div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div>');
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>