<?php
if(!IN_PMD) exit();

// jQuery and any plugins
if ($PMDR->getConfig('use_remote_libraries')) {
    // Update these
    $PMDR->loadJavascript('<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>',10);
    $PMDR->loadJavascript('<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>',10);
} else {
    $PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/jquery.js"></script>',10);
    $PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/jquery_custom.js"></script>',10);
}

$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/jquery_admin.css" />',10);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/qTip/jquery_qtip.js"></script>',15);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/javascript_global.js"></script>',20);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN.'javascript.js"></script>',20);

// Load main CSS
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN.'css.css" />',10);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/qTip/jquery_qtip.css" />',15);

include(PMDROOT.'/includes/common_header.php');

$template_header = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_header.tpl');
$template_footer = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_footer.tpl');

$template_message = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_message.tpl');
$template_message->set('message_types',$PMDR->getMessages());

$onLoad = '<script type="text/javascript">';
$onLoad .= '$(document).ready(function(){';
$onLoad .= '$.ajaxSetup({ url: "./admin_ajax.php", type: "POST", data: { '.COOKIE_PREFIX.'from: '.$PMDR->get('Cleaner')->output_js((isset($_COOKIE[COOKIE_PREFIX.'from']) ? $_COOKIE[COOKIE_PREFIX.'from'] : constant(COOKIE_PREFIX.'from'))).' }});';
if(!$PMDR->getConfig('disable_cron')) {
    $onLoad .= '$.getScript("'.BASE_URL.'/cron.php?type=javascript");';
}
if($PMDR->get('javascript_onload')) {
    $onLoad .= implode("\n",$PMDR->get('javascript_onload'));
}
$onLoad .= '});</script>'."\n";
$PMDR->loadJavascript($onLoad,25);
unset($onLoad);

$template_header->set('css',$PMDR->getCSS());
$template_header->set('message',$template_message);
if(trim($PMDR->get('Session')->get('admin_first_name').' '.$PMDR->get('Session')->get('admin_last_name')) != '') {
    $template_header->set('admin_name',trim($PMDR->get('Session')->get('admin_first_name').' '.$PMDR->get('Session')->get('admin_last_name')));
} else {
    $template_header->set('admin_name',$PMDR->get('Session')->get('admin_login'));
}
$template_header->set('plugin_links',$PMDR->get('Plugins')->admin_menu);
$template_header->set('date',$PMDR->get('Dates_Local')->dateNow('l F jS Y h:ia'));

if(isset($template_page_menu)) {
    if(is_object($template_page_menu)) {
        $template_page_menu_content = $template_page_menu->render();
        $template_page_menu = array();
        $template_page_menu[] = array('title'=>$PMDR->getLanguage('admin_general_navigation'),'content'=>$template_page_menu_content);
        unset($template_page_menu_content);
    } else {
        foreach((array) $template_page_menu AS $key=>$template_page_menu_item) {
            if(!isset($template_page_menu_item['title'])) {
                $template_page_menu[$key]['title'] = $PMDR->getLanguage('admin_general_navigation');
            }
            if(is_object($template_page_menu_item['content'])) {
                $template_page_menu[$key]['content'] = $template_page_menu_item['content']->render();
            }
        }
        unset($template_page_menu_item);
        unset($key);
    }

    $template_page_menu_html = '';
    foreach($template_page_menu AS $menu) {
        $template_side_menu_box = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_side_menu_box.tpl');
        $template_side_menu_box->set('title',$menu['title']);
        if(isset($menu['type']) AND $menu['type'] == 'content') {
            $template_side_menu_box->set('content',$menu['content']);
        } elseif(isset($menu['type']) AND $menu['type'] == 'content_raw') {
            $template_side_menu_box->set('content_raw',$menu['content']);
        } else {
            $template_side_menu_box->set('list',$menu['content']);
        }
        $template_page_menu_html .= $template_side_menu_box->render();
    }
    $template_header->set('template_page_menu',$template_page_menu_html);
} else {
    $template_header->set('template_page_menu',false);
}

// We need to check if it is defined because if its the first load we don't have the addon details yet
if(!defined('ADDON_UNBRANDING') OR !ADDON_UNBRANDING) {
    $template_header->set('logo',BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN.'images/logo.png');
    $copyright_text = '<span id="copyright_poweredby" style="float: left; display: block;">powered by phpMyDirectory (v'.$PMDR->getConfig('pmd_version').')</span>';
    $copyright_text .= '<span id="copyright_company" style="float: right; display: block;">Copyright &copy; 2005-'.date('Y').' Accomplish Technology, LLC</span>';
    $PMDR->loadJavascript('
    <script type="text/javascript">
    $(document).ready(function(){
        $("#help-suggestions-link").qtip({
            show: "click",
            hide: {
                fixed: true,
                delay: 100
            },
            events: {
                hide: function(event, api) {
                    $("#help-suggestions-link").blur().parent().removeClass("open");
                }
            },
            style: {
                classes: "qtip-bootstrap qtip-shadow",
            },
            position: {
                at: "bottom middle",
                my: "top right",
                adjust: { x: 5 },
                viewport: $(window),
                effect: false
            },
            content: {
                text: function(event, api) {
                    $.ajax({
                        url: "https://www.phpmydirectory.com/manual_suggestions.php",
                        data: ({
                            contents: $("h1").text(),
                            source: "cp"
                        }),
                        // Override the default ajaxSetup so we can show a specific error
                        error: function(x,e){}
                    })
                    .then(function(content) {
                        api.set("content.text", content);
                    }, function(xhr, status, error) {
                        api.set("content.text", "No results.");
                    });
                    return "Loading...";
                }
            }
        });
    });
    </script>',100);
} else {
    $template_header->set('logo',BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN.'images/logo_generic.png');
    $copyright_text .= '<span id="copyright_poweredby" style="float: left;">v'.$PMDR->getConfig('pmd_version').'</span>';
}

$template_header->set('javascript',$PMDR->getJavascript());

$template_footer->set('disable_cron',$PMDR->getConfig('disable_cron'));
$template_footer->set('copyright',$copyright_text);

echo $template_header->render();
if(is_object($template_content)) {
    echo $template_content->render();
} else {
    echo $template_content;
}

echo $template_footer->render();

include(PMDROOT.'/includes/common_footer.php');
?>