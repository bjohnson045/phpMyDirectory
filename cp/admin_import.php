<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');


$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_import','admin_imports','admin_users','admin_listings','general_locations','email_templates'));

$PMDR->get('Authentication')->checkPermission('admin_import');

$locations = $PMDR->get('Locations');
$imports = $PMDR->get('Imports');

$category_labels = $PMDR->get('Categories')->getLevelLabels();
$location_labels = $locations->getLevelLabels();

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_import'));

if($_GET['action'] == 'download') {
    $serve = $PMDR->get('ServeFile');
    $serve->serve('import_'.$_GET['id'].'_log.txt',file_get_contents(TEMP_UPLOAD_PATH.'import_'.$_GET['id'].'_log.txt'));
}

if(!isset($_GET['action'])) {
    $PMDR->get('Categories')->checkReset();
    $PMDR->get('Locations')->checkReset();

    $form = $PMDR->getNew('Form');
    $form->enctype= 'multipart/form-data';
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_import')));
    $form->addField('type','select',array('label'=>'Import','fieldset'=>'information','value'=>$_POST['type'],'options'=>array(''=>'Select Import Type','listings'=>$PMDR->getLanguage('listings'),'categories'=>'Categories','locations'=>'Locations'),'help'=>$PMDR->getLanguage('admin_import_help_type')));

    if(isset($_POST['type'])) {
        if($_POST['type'] == 'listings' AND !$db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS_PRICING." pp, ".T_PRODUCTS." p WHERE pp.product_id=p.id AND p.type='listing_membership'")) {
            $PMDR->addMessage('error',$PMDR->getLanguage('admin_import_pricing_id_error'));
            redirect(BASE_URL_ADMIN.'/admin_products_pricing.php?action=add');
        }

        $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_imports_name'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_import_help_name')));
        $form->addField('file','file',array('label'=>$PMDR->getLanguage('admin_import_upload_file'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_import_help_file')));
        $form->addField('file_path','text',array('label'=>$PMDR->getLanguage('admin_import_file_path'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_import_help_file_path')));
        $form->addField('delimiter','select',array('label'=>$PMDR->getLanguage('admin_imports_delimiter'),'fieldset'=>'information','value'=>';','options'=>array(';'=>';',':'=>':',','=>',','|'=>'|','%'=>'%','@'=>'@','&'=>'&','*'=>'*'),'help'=>$PMDR->getLanguage('admin_import_help_delimiter')));
        $form->addField('encapsulator','text',array('label'=>$PMDR->getLanguage('admin_imports_encapsulator'),'fieldset'=>'information','value'=>'','help'=>$PMDR->getLanguage('admin_import_help_encapsulator')));
        $form->addField('scheduled','checkbox',array('label'=>$PMDR->getLanguage('admin_import_scheduled'),'fieldset'=>'information','value'=>'','help'=>$PMDR->getLanguage('admin_import_scheduled_help')));
        $form->addField('notifications','checkbox',array('label'=>$PMDR->getLanguage('admin_import_notifications'),'fieldset'=>'information','value'=>'','help'=>$PMDR->getLanguage('admin_import_notifications_help')));
        if($_POST['type'] == 'listings') {
            $form->addField('pricing_id','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_import_pricing_id'),'fieldset'=>'information','options'=>array('type'=>'products_tree','product_type'=>'listing_membership','hidden'=>true),'help'=>$PMDR->getLanguage('admin_import_help_pricing_id')));
            $form->addField('create_invoice','checkbox',array('label'=>$PMDR->getLanguage('admin_import_create_invoice'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_import_help_create_invoice')));
            $form->addField('send_registration_email','checkbox',array('label'=>$PMDR->getLanguage('admin_import_send_registration_email'),'fieldset'=>'information','value'=>'','help'=>$PMDR->getLanguage('admin_import_send_registration_email_help')));
            $form->addValidator('pricing_id',new Validate_NonEmpty());
        }
        if($_POST['type'] == 'listings' OR $_POST['type'] == 'categories') {
            $form->addField('category_columns','text',array('label'=>$PMDR->getLanguage('admin_import_category_columns'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_import_help_category_columns')));
            $form->addValidator('category_columns',new Validate_NonEmpty());
            $form->addValidator('category_columns',new Validate_Numeric());
        }
        if($_POST['type'] == 'listings' OR $_POST['type'] == 'locations') {
            $form->addField('location_columns','text',array('label'=>$PMDR->getLanguage('admin_import_location_columns'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_import_help_location_columns')));
            $form->addValidator('location_columns',new Validate_NonEmpty());
            $form->addValidator('location_columns',new Validate_Numeric());
        }
        $form->addField('submit_file','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    }

    $form->addJavascript('type','onchange','this.form.submit()');

    $form->addValidator('name',new Validate_NonEmpty());
    $form->addValidator('type',new Validate_NonEmpty());

    $form->addFieldSet('server_config',array('legend'=>$PMDR->getLanguage('admin_import_server_configuration')));
    $form->addField('max_execution','custom',array('label'=>$PMDR->getLanguage('admin_import_max_execution_time'),'fieldset'=>'server_config','value'=>ini_get('max_execution_time').' seconds'));
    $form->addField('max_input_time','custom',array('label'=>$PMDR->getLanguage('admin_import_max_input_time'),'fieldset'=>'server_config','value'=>ini_get('max_input_time').' seconds'));
    $form->addField('max_filesize','custom',array('label'=>$PMDR->getLanguage('admin_import_upload_max_filesize'),'fieldset'=>'server_config','value'=>ini_get('upload_max_filesize')));
    $form->addField('session_timeout','custom',array('label'=>$PMDR->getLanguage('admin_import_session_timeout'),'fieldset'=>'server_config','value'=>$PMDR->getConfig('session_timeout').' seconds'));

    if($form->wasSubmitted('submit_file')) {
        if(DEMO_MODE) {
            $PMDR->addMessage('error','Importing is disabled in the demo.');
        } else {
            $data = $form->loadValues();

            if(!is_writable(TEMP_UPLOAD_PATH)) {
                $form->addError('The /files/upload/ folder must be writable.','file_path');
            } else {
                if(isset($data['file_path']) AND $data['file_path'] != '') {
                    if(!file_exists($data['file_path'])) {
                        $form->addError($PMDR->getLanguage('admin_import_file_not_found'),'file_path');
                    } else {
                        copy($data['file_path'],TEMP_UPLOAD_PATH.'import_manager_upload.csv');
                    }
                } elseif(isset($data['file']['tmp_name']) AND !empty($data['file']['tmp_name'])) {
                    move_uploaded_file($data['file']['tmp_name'],TEMP_UPLOAD_PATH.'import_manager_upload.csv');
                } else {
                    $form->addError($PMDR->getLanguage('admin_import_none_entered'),'file');
                }
            }
            if($data['type'] == 'categories') {
                if(!$PMDR->get('Imports')->checkDelimiter(TEMP_UPLOAD_PATH.'import_manager_upload.csv',$data['delimiter'],$data['encapsulator'],$data['category_columns'])) {
                    $form->addError($PMDR->getLanguage('admin_import_bad_delimiter'),'delimeter');
                }
            }
            if($data['type'] == 'locations') {
                if(!$PMDR->get('Imports')->checkDelimiter(TEMP_UPLOAD_PATH.'/import_manager_upload.csv',$data['delimiter'],$data['encapsulator'],$data['location_columns'])) {
                    $form->addError($PMDR->getLanguage('admin_import_bad_delimiter'),'delimeter');
                }
            }
            if($data['type'] == 'listings') {
                if(!$PMDR->get('Imports')->checkDelimiter(TEMP_UPLOAD_PATH.'/import_manager_upload.csv',$data['delimiter'],$data['encapsulator'])) {
                    $form->addError($PMDR->getLanguage('admin_import_bad_delimiter'),'delimeter');
                }
            }
            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                if($data['encapsulator'] == '') $data['encapsulator'] = '"';
                $data['data'] = array(
                    'type'=>$data['type'],
                    'pricing_id'=>$data['pricing_id'],
                    'create_invoice'=>$data['create_invoice'],
                    'send_registration_email'=>$data['send_registration_email'],
                    'category_columns'=>$data['category_columns'],
                    'location_columns'=>$data['location_columns'],
                    'statistics'=>array()
                );
                $import_id = $imports->insert($data);
                rename(TEMP_UPLOAD_PATH.'/import_manager_upload.csv',TEMP_UPLOAD_PATH.'/imports_'.$import_id.'.csv');
                redirect(array('action'=>'map','id'=>$import_id));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
} elseif($_GET['action'] == 'map') {
    $import = $imports->getRow($_GET['id']);
    $import['data'] = unserialize($import['data']);
    $file = fopen(TEMP_UPLOAD_PATH.'/imports_'.$import['id'].'.csv', 'r');
    $field_list = $PMDR->get('Fields')->getFields('listings');
    $scan_fields = fgetcsv($file,0,$import['delimiter'],(string) trim($import['encapsulator']));
    array_unshift_assoc($scan_fields,9999,$PMDR->getLanguage('admin_import_none'));
    $scan_fields_lookup = array_map('strtolower',$scan_fields);
    $values = array(
        'id'=>array_intersect($scan_fields_lookup,array('id')),
        'login'=>array_intersect($scan_fields_lookup,array('login','username','user','user id')),
        'password'=>array_intersect($scan_fields_lookup,array('pass','password')),
        'pricing_id'=>array_intersect($scan_fields_lookup,array('pricing','pricing id','product id')),
        'status'=>array_intersect($scan_fields_lookup,array('status')),
        'title'=>array_intersect($scan_fields_lookup,array('title','listing title','listing name','company','company name','business name')),
        'friendly_url'=>array_intersect($scan_fields_lookup,array('friendly url','slug')),
        'description'=>array_intersect($scan_fields_lookup,array('description','listing description','long description')),
        'description_short'=>array_intersect($scan_fields_lookup,array('short description','description short')),
        'keywords'=>array_intersect($scan_fields_lookup,array('keywords','key words','tags')),
        'location_text_1'=>array_intersect($scan_fields_lookup,array('location text 1',$PMDR->getLanguage('general_locations_text_1'))),
        'location_text_2'=>array_intersect($scan_fields_lookup,array('location text 2',$PMDR->getLanguage('general_locations_text_2'))),
        'location_text_3'=>array_intersect($scan_fields_lookup,array('location text 3',$PMDR->getLanguage('general_locations_text_3'))),
        'phone'=>array_intersect($scan_fields_lookup,array('phone','telephone','tel','phone number','telephone number','number')),
        'fax'=>array_intersect($scan_fields_lookup,array('fax','facsimile')),
        'listing_address1'=>array_intersect($scan_fields_lookup,array('address line 1','address 1','address')),
        'listing_address2'=>array_intersect($scan_fields_lookup,array('address line 2','address 2')),
        'listing_zip'=>array_intersect($scan_fields_lookup,array('zip','zipcode','zip code','postcode','post code')),
        'latitude'=>array_intersect($scan_fields_lookup,array('latitude','lat')),
        'longitude'=>array_intersect($scan_fields_lookup,array('longitude','lon','long')),
        'mail'=>array_intersect($scan_fields_lookup,array('email','e-mail','email address','e-mail address')),
        'www'=>array_intersect($scan_fields_lookup,array('website','url','www','website url','link')),
        'logo_url'=>array_intersect($scan_fields_lookup,array('logo','image','logo url','image url')),
        'claimed'=>array_intersect($scan_fields_lookup,array('claimed')),
    );
    for($x=0; $x < $import['data']['category_columns']; $x++) {
        $level = ($x+1);
        $values['category'.$level] = array_intersect($scan_fields_lookup,array('category level '.$level,'category '.$level));
        $values['category_friendly_url'.$level] = array_intersect($scan_fields_lookup,array('category level '.$level.' friendly url','category '.$level.' friendly url'));
        $values['category_description_short'.$level] = array_intersect($scan_fields_lookup,array('category level '.$level.' description short'));
        $values['category_description'.($x+1)] = array_intersect($scan_fields_lookup,array('category level '.$level.' description','category '.$level.' description'));
        $values['category_keywords'.($x+1)] = array_intersect($scan_fields_lookup,array('category level '.$level.' keywords','category '.$level.' keywords'));
        $values['category_meta_title'.($x+1)] = array_intersect($scan_fields_lookup,array('category level '.$level.' meta title','category '.$level.' meta title'));
        $values['category_meta_description'.($x+1)] = array_intersect($scan_fields_lookup,array('category level '.$level.' meta description','category '.$level.' meta description'));
        $values['category_meta_keywords'.($x+1)] = array_intersect($scan_fields_lookup,array('category level '.$level.' meta keywords','category '.$level.' meta keywords'));
        $values['category_link'.($x+1)] = array_intersect($scan_fields_lookup,array('category level '.$level.' link','category '.$level.' link'));
    }
    for($x=0; $x < $import['data']['location_columns']; $x++) {
        $level = ($x+1);
        $values['location'.$level] = array_intersect($scan_fields_lookup,array('location level '.$level,'location '.$level));
        $values['location_friendly_url'.$level] = array_intersect($scan_fields_lookup,array('location level '.$level.' friendly url','location '.$level.' friendly url'));
        $values['location_description_short'.$level] = array_intersect($scan_fields_lookup,array('location level '.$level.' description short','location '.$level.' description short'));
        $values['location_description'.($x+1)] = array_intersect($scan_fields_lookup,array('location level '.$level.' description','location '.$level.' description'));
        $values['location_keywords'.($x+1)] = array_intersect($scan_fields_lookup,array('location level '.$level.' keywords','location '.$level.' keywords'));
        $values['location_meta_title'.($x+1)] = array_intersect($scan_fields_lookup,array('location level '.$level.' meta title','location '.$level.' meta title'));
        $values['location_meta_description'.($x+1)] = array_intersect($scan_fields_lookup,array('location level '.$level.' meta description','location '.$level.' meta description'));
        $values['location_meta_keywords'.($x+1)] = array_intersect($scan_fields_lookup,array('location level '.$level.' meta keywords','location '.$level.' meta keywords'));
        $values['location_link'.($x+1)] = array_intersect($scan_fields_lookup,array('location level '.$level.' link','location '.$level.' link'));
    }
    foreach($field_list as $key=>$field) {
        $values['custom_'.$field['id']] = array_intersect($scan_fields_lookup,array(strtolower($field['name'])));
    }
    foreach($values AS $key=>$value) {
        if(empty($value)) {
            $values[$key] = '9999';
        } else {
            $values[$key] = key($value);
        }
    }
    $form = $PMDR->getNew('Form');
    $form->action = 'admin_import.php?action=map&id='.$import['id'];
    $form->label_width = '170px';
    $form->enctype= 'multipart/form-data';
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_import')));
    if($import['data']['type'] == 'listings') {
        $form->addField('login','select',array('label'=>$PMDR->getLanguage('admin_import_user_id'),'fieldset'=>'information','value'=>$values['login'],'options'=>$scan_fields));
        $form->addField('password','select',array('label'=>$PMDR->getLanguage('admin_import_password'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('pricing_id','select',array('label'=>$PMDR->getLanguage('admin_import_pricing_id'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('status','select',array('label'=>$PMDR->getLanguage('admin_import_status'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('title','select',array('label'=>$PMDR->getLanguage('admin_import_title'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('friendly_url','select',array('label'=>$PMDR->getLanguage('admin_import_friendly_url'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('description','select',array('label'=>$PMDR->getLanguage('admin_import_description'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('description_short','select',array('label'=>$PMDR->getLanguage('admin_import_short_description'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('keywords','select',array('label'=>$PMDR->getLanguage('admin_import_keywords'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addValidator('title',new Validate_NonEmpty());
    }
    if($import['data']['type'] == 'listings' OR $import['data']['type'] == 'categories') {
        for($x=0; $x < $import['data']['category_columns']; $x++) {
            $form->addField('category'.($x+1),'select',array('label'=>'Category Level '.($x+1),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_friendly_url'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_friendly_url'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_description_short'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_short_description'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_description'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_description'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_keywords'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_keywords'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_meta_title'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_meta_title'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_meta_description'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_meta_description'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_meta_keywords'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_meta_keywords'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('category_link'.($x+1),'select',array('label'=>'Category Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_link'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addValidator('category'.($x+1),new Validate_NonEmpty());
        }
    }

    if($import['data']['type'] == 'listings' OR $import['data']['type'] == 'locations') {
        for($x=0; $x < $import['data']['location_columns']; $x++) {
            $form->addField('location'.($x+1),'select',array('label'=>'Location Level '.($x+1),'fieldset'=>'information','value'=>in_array('Location Level '.($x+1),$scan_fields) ? $scan_fields_lookup['Location Level '.($x+1)] : '9999','options'=>$scan_fields));
            $form->addField('location_friendly_url'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_friendly_url'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('location_description_short'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_short_description'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('location_description'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_description'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('location_keywords'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_keywords'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('location_meta_title'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_meta_title'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('location_meta_description'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_meta_description'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('location_meta_keywords'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_meta_keywords'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addField('location_link'.($x+1),'select',array('label'=>'Location Level '.($x+1).' '.$PMDR->getLanguage('admin_import_cat_loc_link'),'fieldset'=>'information','options'=>$scan_fields));
            $form->addValidator('location'.($x+1),new Validate_NonEmpty());
        }
    }

    if($import['data']['type'] == 'listings') {
        if($PMDR->getConfig('location_text_1')) {
            $form->addField('location_text_1','select',array('label'=>'Location Text 1','fieldset'=>'information','options'=>$scan_fields));
        }
        if($PMDR->getConfig('location_text_2')) {
            $form->addField('location_text_2','select',array('label'=>'Location Text 2','fieldset'=>'information','options'=>$scan_fields));
        }
        if($PMDR->getConfig('location_text_3')) {
            $form->addField('location_text_3','select',array('label'=>'Location Text 3','fieldset'=>'information','options'=>$scan_fields));
        }

        // Make column selection smarter, look for popular keywords.
        $form->addField('phone','select',array('label'=>$PMDR->getLanguage('admin_import_phone'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('fax','select',array('label'=>$PMDR->getLanguage('admin_import_fax'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('listing_address1','select',array('label'=>$PMDR->getLanguage('admin_import_address1'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('listing_address2','select',array('label'=>$PMDR->getLanguage('admin_import_address2'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('listing_zip','select',array('label'=>$PMDR->getLanguage('admin_import_zip_code'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('latitude','select',array('label'=>$PMDR->getLanguage('admin_import_latitude'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('longitude','select',array('label'=>$PMDR->getLanguage('admin_import_longitude'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('mail','select',array('label'=>$PMDR->getLanguage('admin_import_email'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('www','select',array('label'=>$PMDR->getLanguage('admin_import_website'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('logo_url','select',array('label'=>$PMDR->getLanguage('admin_import_logo_url'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('claimed','select',array('label'=>$PMDR->getLanguage('admin_import_claimed'),'fieldset'=>'information','options'=>$scan_fields));
        $form->addField('facebook_page_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('twitter_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('google_page_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('linkedin_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('linkedin_company_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('pinterest_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('youtube_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('foursquare_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        $form->addField('instagram_id','select',array('fieldset'=>'information','options'=>$scan_fields));
        foreach($field_list as $key=>$field) {
            $form->addField('custom_'.$field['id'],'select',array('label'=>$field['name'],'fieldset'=>'information','options'=>$scan_fields));
        }
    }
    foreach($form->elements AS $element) {
        if(isset($values[$element['name']])) {
            $form->setFieldAttribute($element['name'],'value',$values[$element['name']]);
        }
    }
    $form->addField('submit_mapping','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    if($form->wasSubmitted('submit_mapping')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $imports->update(array('map'=>serialize($data),'data'=>serialize($import['data'])),$import['id']);
            if($import['scheduled']) {
                $PMDR->addMessage('success','Import scheduled.');
                redirect('admin_imports.php');
            } else {
                redirect(array('action'=>'import','id'=>$import['id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
} elseif($_GET['action'] == 'import') {
    $script = '
    <script type="text/javascript">
    var importOnComplete = function(data) {
        $("#status").progressbar("option", "value", data.percent);
        $("#status_percent").html(data.percent+"%");
        $("#category_count").html(data.statistics.categories);
        $("#location_count").html(data.statistics.locations);
        $("#user_count").html(data.statistics.users);
        $("#order_count").html(data.statistics.orders);
        $("#invoice_count").html(data.statistics.invoices);
        $("#listing_count").html(data.statistics.listings);
        $("#error_count").html(data.statistics.errors);
        if(data.statistics.errors > 0) {
            $("#view_errors").show();
        }

        if(data.percent == 100) {
            $("#status").progressbar("destroy");
            $("#status").remove();
            $("#status_percent").remove();
            addMessage("success","<p>'.$PMDR->getLanguage('admin_import_complete').'</p><p><a class=\"btn btn-default btn-sm\" href=\"admin_import.php?action=download&id="+data.id+"\">Download Full Log</a></p>","status_container");
        } else {
            importStart(false);
        }
    };

    var importStart = function(start) {
        if(start) {
            $("#status_percent").html("0%");
            $("#status").progressbar({ value: 0 });
        }
        $.ajax({ data: ({ action: "admin_import", id: '.$_GET['id'].' }), success: importOnComplete, dataType: "json", cache: false });
    };
    $(document).ready(function() {
        importStart(true);
    });
    </script>';
    $template_content->set('content',$script.'
    <div id="status_container">
        <div style="width: 500px; float: left; margin-bottom: 18px" id="status"></div>
        <div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div>
    </div>
    <table class="table table-striped table-bordered">
        <thead>
            <th>'.$PMDR->getLanguage('admin_import_categories').'</th>
            <th>'.$PMDR->getLanguage('admin_import_locations').'</th>
            <th>'.$PMDR->getLanguage('admin_import_users').'</th>
            <th>'.$PMDR->getLanguage('admin_import_orders').'</th>
            <th>'.$PMDR->getLanguage('admin_import_invoices').'</th>
            <th>'.$PMDR->getLanguage('admin_import_listings').'</th>
            <th>'.$PMDR->getLanguage('admin_import_errors').'</th>
        </thead>
        <tr>
            <td id="category_count">0</td>
            <td id="location_count">0</td>
            <td id="user_count">0</td>
            <td id="order_count">0</td>
            <td id="invoice_count">0</td>
            <td id="listing_count">0</td>
            <td><span id="error_count">0</span> <a id="view_errors" target="_blank" style="display: none" class="btn btn-warning btn-xs" href="'.get_file_url(TEMP_UPLOAD_PATH.'import_'.$_GET['id'].'_errors.txt').'">View Error Log</a></td>
        </tr>
    </table>
    <div style="clear: both">
        <a target="_blank" class="btn btn-default" href="'.get_file_url(TEMP_UPLOAD_PATH.'import_'.$_GET['id'].'_log.txt').'">View Log</a>
    </div>');
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_imports_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>