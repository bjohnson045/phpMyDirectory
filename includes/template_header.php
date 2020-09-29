<?php
if(!defined('IN_PMD')) exit();

$PMDR->get('Plugins')->run_hook('template_header_begin');

// If a custom header file is set, use it, or else use the default header.tpl file
if($PMDR->get('header_file') AND $PMDR->get('Templates')->path($PMDR->get('header_file'))) {
    $header = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.$PMDR->get('header_file'));
} elseif(is_null($PMDR->get('header_file'))) {
    $header = $PMDR->getNew('Template',null);
} else {
    $header = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'header.tpl');
}

// Check for maintenance option and show header message bar if necesarry
if($PMDR->getConfig('maintenance') AND @in_array('admin_login',$_SESSION['admin_permissions'])) {
    $header->set('maintenance',true);
} else {
    $header->set('maintenance',false);
}

if($PMDR->get('Session')->get('admin_id') AND $PMDR->get('Session')->get('admin_id') != $PMDR->get('Session')->get('user_id')) {
    $header->set('admin_as_user',$PMDR->get('Session')->get('user_id'));
    $header->set('admin_as_user_message',$PMDR->getLanguage('admin_as_user',array($PMDR->get('Session')->get('user_id'),BASE_URL.MEMBERS_FOLDER.'index.php?user_login='.$PMDR->get('Session')->get('admin_id'))));
} else {
    $header->set('admin_as_user',false);
}

// Set the canonical URL useful to prevent duplicate content
if($PMDR->get('canonical_url')) {
    $header->set('canonical_url','<link rel="canonical" href="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('canonical_url')).'" />');
} else {
    $header->set('canonical_url',false);
}

// Set the previous URL for pagination SEO
if($PMDR->get('previous_url')) {
    $header->set('previous_url',$PMDR->get('Cleaner')->clean_output($PMDR->get('previous_url')));
}

// Set the next URL for pagination SEO
if($PMDR->get('next_url')) {
    $header->set('next_url',$PMDR->get('Cleaner')->clean_output($PMDR->get('next_url')));
}

// Set user logged in details
if(!empty($_SESSION['user_first_name'])) {
    $header->set('username',trim($_SESSION['user_first_name'].' '.$_SESSION['user_last_name']));
} elseif(!empty($_SESSION['user_login'])) {
    $header->set('username',$_SESSION['user_login']);
} else {
    $header->set('username',false);
}

// Load jQuery libraries and CSS stylesheet
if ($PMDR->getConfig('use_remote_libraries')) {
    $PMDR->loadJavascript('<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>',10);
    $PMDR->loadJavascript('<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>',10);
} else {
    $PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/jquery.js"></script>',10);
    $PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/jquery_custom.js"></script>',10);
}
if(!isset($_COOKIE[COOKIE_PREFIX.'mobile'])) {
    $PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/plugins/jquery.cookies.js"></script>',20);
}
$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/qTip/jquery_qtip.js"></script>',15);

$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/javascript_global.js"></script>',15);
$PMDR->loadJavascript('<script type="text/javascript" src="'.$PMDR->get('Templates')->urlCDN('javascript.js').'"></script>',15);

// Load jQuery CSS
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/jquery.css" />',10);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/qTip/jquery_qtip.css" />',15);

// Load main CSS
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.$PMDR->get('Templates')->urlCDN('css.css').'" />',10);

// Set up AJAX
$PMDR->loadJavascript('
    <script type="text/javascript">
    $(document).ready(function(){
        $.ajaxSetup({
            url:"'.BASE_URL.'/ajax.php",
            type:"POST",
            data:{
                '.COOKIE_PREFIX.'from:'.$PMDR->get('Cleaner')->output_js((isset($_COOKIE[COOKIE_PREFIX.'from']) ? $_COOKIE[COOKIE_PREFIX.'from'] : constant(COOKIE_PREFIX.'from'))).'
            }
        });
    });
    </script>'
,20);

// Load javascript set in the administrative area
$PMDR->loadJavascript($PMDR->getConfig('head_javascript'),25);

// Set and load the onload javascript
$onLoad = '<script type="text/javascript">'."\n";
$onLoad .= '//<![CDATA['."\n";
$onLoad .= '$(window).load(function(){';
if($PMDR->get('javascript_onload')) {
    $onLoad .= implode("\n",$PMDR->get('javascript_onload'));
}
if(!$PMDR->getConfig('disable_cron')) {
    $onLoad .= '$.getScript("'.BASE_URL.'/cron.php?type=javascript");';
}
$onLoad .= '});'."\n";
$onLoad .= '//]]>'."\n";
$onLoad .= '</script>'."\n";
$PMDR->loadJavascript($onLoad,30);
unset($onLoad);

if(!isset($_SESSION['location']) AND $PMDR->getConfig('geolocation_fill')) {
    $PMDR->loadJavascript('
    <script type="text/javascript">
    $(window).load(function(){
        if(navigator && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    $.ajax({
                        data: ({
                            action: \'geolocation_cache\',
                            ip: "'.get_ip_address().'",
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        }),
                        success: function() {}
                    });
                },
                function() {},
                {timeout:10000}
            );
        }
    });
    </script>',35);
}

// Set the javascript and css in the template
$header->set('javascript',$PMDR->getJavascript());
$header->set('css',$PMDR->getCSS());

// Initialize meta tags array
$meta_tags = array();

// If a custom meta description is set, use it, or else use the default meta description
$meta_tags[] = '<meta name="description" content="'.$PMDR->get('Cleaner')->clean_output(($PMDR->get('meta_description') ? $PMDR->get('meta_description') : ((!on_page('/index.php') AND $PMDR->get('page_title')) ? (is_array($PMDR->get('page_title')) ? array_pop($PMDR->get('page_title')) : $PMDR->get('page_title')).' - ' : '').$PMDR->getConfig('meta_description_default')),true).'" />';

// If custom meta keywords are set, use it, or else the default meta keywords
$meta_tags[] = '<meta name="keywords" content="'.Strings::comma_separated($PMDR->get('Cleaner')->clean_output(($PMDR->get('meta_keywords') ? $PMDR->get('meta_keywords') : $PMDR->getConfig('meta_keywords_default')),true)).'" />';

// IF custom meta robots is set then add the tag.
if($PMDR->get('meta_robots')) {
    $meta_tags[] = '<meta name="robots" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('meta_robots')).'">';
}

// Add title from configuration to end of array and display, seperated by a dash -
$header->set('title',$PMDR->getConfig('title'));
if($PMDR->get('meta_title') != '') {
    $header->set('meta_title',$PMDR->get('meta_title'));
} else {
    if(is_array($PMDR->data['page_title']) AND count($PMDR->data['page_title'])) {
        $PMDR->setAdd('meta_title',implode(' - ',array_reverse((array) $PMDR->data['page_title'])));
    }
    if($PMDR->getConfig('meta_title_suffix') != '') {
        $PMDR->setAdd('meta_title',$PMDR->getConfig('meta_title_suffix'));
    }
    $header->set('meta_title',(!is_null($PMDR->get('meta_title')) ? implode(' - ',$PMDR->get('meta_title')) : ''));
}

// Set the 2 character language code in the template
$header->set('languagecode',substr($PMDR->getLanguage('languagecode'),0,2));

// Set the text direction
$header->set('textdirection',$PMDR->getLanguage('textdirection'));

if($PMDR->getConfig('twitter_site_id')) {
    $meta_tags[] = '<meta name="twitter:card" content="summary">';
    $meta_tags[] = '<meta name="twitter:site" content="@'.$PMDR->get('Cleaner')->clean_output(ltrim($PMDR->getConfig('twitter_site_id'),'@')).'">';
    $meta_tags[] = '<meta name="twitter:title" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('meta_title')).'">';
    $meta_tags[] = '<meta name="twitter:description" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('meta_description')).'">';
    if($PMDR->get('meta_image')) {
        $meta_tags[] = '<meta name="twitter:image" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('meta_image')).'">';
    }
}

if($PMDR->get('og:type')) {
    $meta_tags[] = '<meta property="og:title" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('meta_title')).'" />';
    $meta_tags[] = '<meta property="og:type" content="'.$PMDR->get('og:type').'" />';
    $meta_tags[] = '<meta property="og:url" content="'.$PMDR->get('Cleaner')->clean_output(URL).'" />';
    $meta_tags[] = '<meta property="og:description" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('meta_description')).'" />';
    if($PMDR->get('meta_image')) {
        $meta_tags[] = '<meta property="og:image" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->get('meta_image')).'" />';
    }
    if($PMDR->getConfig('meta_title_default')) {
        $meta_tags[] = '<meta property="og:site_name" content="'.$PMDR->getConfig('meta_title_default').'" />';
    }
    if($PMDR->getConfig('facebook_app_id')) {
        $meta_tags[] = '<meta property="fb:app_id" content="'.$PMDR->getConfig('facebook_app_id').'" />';
    }
    if($PMDR->get('og:data')) {
        $og_data = $PMDR->get('og:data');
        foreach($og_data AS $property=>$content) {
            $meta_tags[] = '<meta property="'.$property.'" content="'.$PMDR->get('Cleaner')->clean_output($content).'" />';
        }
        unset($og_data,$property,$content);
    }
}

// Set geo location meta tags if available
if($PMDR->get('meta_geo_position')) {
    $meta_tags[] = '<meta name="geo.position" content="'.$PMDR->get('meta_geo_position').'" />';

    if($PMDR->get('og:type')) {
        $coordinates = explode(';',$PMDR->get('meta_geo_position'));
        $meta_tags[] = '<meta property="place:location:latitude"  content="'.$coordinates[0].'" />';
        $meta_tags[] = '<meta property="place:location:longitude" content="'.$coordinates[1].'" />';
    }
}

if($PMDR->getConfig('google_verification_code')) {
    $meta_tags[] = '<meta name="google-site-verification" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->getConfig('google_verification_code')).'" />';
}
if($PMDR->getConfig('bing_verification_code')) {
    $meta_tags[] = '<meta name="msvalidate.01" content="'.$PMDR->get('Cleaner')->clean_output($PMDR->getConfig('bing_verification_code')).'" />';
}

if($PMDR->getConfig('google_page_id')) {
    $publisher_id = $PMDR->getConfig('google_page_id');
    if(!is_numeric($publisher_id)) {
        $publisher_id = '+'.ltrim($publisher_id,'+');
    }
    $meta_tags[] = '<link href="https://plus.google.com/'.$publisher_id.'" rel="publisher" />';
    unset($publisher_id);
}

// Set all meta tags
$header->set('meta_tags',implode("\n",$meta_tags), null);

$PMDR->get('Plugins')->run_hook('template_header_end');
?>