<?php
/**
* Class Factory
* Used to serve objects as called by the registry
*/
class Factory {
    function makeDB($registry) {
        require_once(PMDROOT.'/includes/class_database.php');
        $db = new Database();
        if(defined('DB_CHARSET') AND DB_CHARSET != '') {
            if(defined('DB_COLLATE') AND DB_COLLATE != '') {
                $connection = $db->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_CHARSET, DB_COLLATE);
            } else {
                $connection = $db->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_CHARSET);
            }
        } else {
            $connection = $db->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        }

        if($connection) {
            return $db;
        } else {
            return false;
        }
    }

    function makeLinkChecker($registry) {
        require_once(PMDROOT.'/includes/class_link_checker.php');
        $link_checker = new LinkChecker($registry);
        if($registry->getConfig('reciprocal_url') != '') {
            $link_checker->check_url = $registry->getConfig('reciprocal_url');
        } else {
            $link_checker->check_url = BASE_URL_NOSSL;
        }
        return $link_checker;
    }

    function makeCSV_Batcher($registry) {
        require_once(PMDROOT.'/includes/class_batcher.php');
        return new CSV_Batcher($registry);
    }

    function makeDatabase_Batcher($registry) {
        require_once(PMDROOT.'/includes/class_batcher.php');
        return new Database_Batcher($registry);
    }

    function makeTableList($registry, $parameters = array()) {
        require_once(PMDROOT.'/includes/class_table_list.php');
        $table_list = new TableList($registry, $parameters);
        switch(PMD_SECTION) {
            case 'admin':
                if(!isset($parameters['template'])) {
                    $table_list->template = $registry->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_table_list.tpl');
                }
                $table_list->page_template = $registry->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_page_navigation.tpl');
                break;
            case 'members':
                if(!isset($parameters['template'])) {
                    $table_list->template = $registry->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_table_list.tpl');
                }
                $table_list->page_template = $registry->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_page_navigation.tpl');
                break;
            case 'public':
                if(!isset($parameters['template'])) {
                    $table_list->template = $registry->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/table_list.tpl');
                }
                $table_list->page_template = $registry->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
                break;
        }
        return $table_list;
    }

    function makeServeFile($registry) {
        require_once(PMDROOT.'/includes/class_serve_file.php');
        return new ServeFile($registry);
    }

    function makeUsersGroups($registry) {
        require_once(PMDROOT.'/includes/class_users_groups.php');
        return new UsersGroups($registry);
    }

    function makeRSS_Parser($registry) {
        require_once(PMDROOT.'/includes/SimplePie/simplepie.php');
        $parser = new SimplePie();
        $parser->cache_location = CACHE_PATH;
        require_once(PMDROOT.'/includes/class_rss_parser.php');
        return new RSS_Parser($registry,$parser);
    }

    function makeMailEmail_Queue($registry) {
        require_once(PMDROOT.'/includes/class_email_queue.php');
        return new Email_Queue($registry);
    }

    function makeEmail_Handler($registry,$parameters=array()) {
        require_once(PMDROOT.'/includes/Mailer/swift_required.php');
        require_once(PMDROOT.'/includes/class_email_handler.php');
        return new Email_Handler($registry,$parameters);
    }

    function makeTableGateway($registry, $table) {
        return new TableGateway($registry, $table);
    }

    function makeSession($registry,$storage='database') {
        require_once(PMDROOT.'/includes/class_session.php');
        if($storage == 'files') {
            return new Session_Files($registry);
        } else {
            return new Session($registry);
        }
    }

    function makeCategories($registry) {
        require_once(PMDROOT.'/includes/class_nested_set.php');
        require_once(PMDROOT.'/includes/class_tree_gateway.php');
        require_once(PMDROOT.'/includes/class_categories.php');
        return new Categories($registry);
    }

    function makeLocations($registry) {
        require_once(PMDROOT.'/includes/class_nested_set.php');
        require_once(PMDROOT.'/includes/class_tree_gateway.php');
        require_once(PMDROOT.'/includes/class_locations.php');
        return new Locations($registry);
    }

    function makeLanguages($registry) {
        require_once(PMDROOT.'/includes/class_languages.php');
        return new Languages($registry);
    }

    function makePhrases($registry) {
        require_once(PMDROOT.'/includes/class_languages.php');
        return new Phrases($registry);
    }

    function makeError($registry, $error) {
        require_once(PMDROOT.'/includes/class_error.php');
        return new Error_Code($registry, $error);
    }

    function makeErrorHandler($registry) {
        require_once(PMDROOT.'/includes/class_error_handler.php');
        return new ErrorHandler($registry);
    }

    function makeCustomPage($registry) {
        require_once(PMDROOT.'/includes/class_pages.php');
        return new CustomPage($registry);
    }

    function makeCustomLinks($registry) {
        require_once(PMDROOT.'/includes/class_menu_links.php');
        return new CustomLinks($registry);
    }

    function makeTemplate($registry, $template=null) {
        require_once(PMDROOT.'/includes/class_template.php');
        return new PMDTemplate($registry, $template);
    }

    function makeTCPDF($registry) {
        require_once(PMDROOT.'/includes/TCPDF/tcpdf.php');
        $pdf = new TCPDF("P", "mm", "A4", true, 'UTF-8',false);
        $pdf->setLanguageArray(array('a_meta_charset'=>CHARSET,'a_meta_dir'=>strtolower($registry->getLanguage('textdirection')),'a_meta_language'=>substr($registry->getLanguage('languagecode'),0,2),'w_page'=>'page'));
        return $pdf;
    }

    function makeQRCode($registry) {
        require_once(PMDROOT.'/includes/TCPDF/tcpdf_barcodes_2d.php');
        return new TCPDF2DBarcode('','');
    }

    function makePaging($registry,$parameters=array()) {
        if(isset($_GET['page_size'])) {
            if(PMD_SECTION == 'admin') {
                $_SESSION['page_size'] = intval($_GET['page_size']);
            }
            $parameters['page_size'] = intval($_GET['page_size']);
        } elseif(isset($_SESSION['page_size']) AND PMD_SECTION == 'admin') {
            $parameters['page_size'] = $_SESSION['page_size'];
        }
        require_once(PMDROOT.'/includes/class_paging.php');
        return new Paging($registry,$parameters);
    }

    function makeSearch($registry, $type) {
        require_once(PMDROOT.'/includes/class_search.php');
        $searchType = 'Search'.$type;
        return new $searchType($registry);
    }

    function makeMap($registry) {
        require_once(PMDROOT.'/includes/class_map.php');
        $map = $registry->get($registry->getConfig('map_type').'_Map');
        $map->apiKey = $registry->getConfig($registry->getConfig('map_type').'_apikey');
        $map->zoomLevel = $registry->getConfig('map_zoom');
        $geocoding_service = $registry->getConfig('geocoding_service');
        if($geocoding_service != 'disabled') {
            $map->lookupService = $geocoding_service;
            $map->apiKeyGeoCoding = $registry->getConfig(($map->lookupService == 'google' ? 'google_server' : $map->lookupService).'_apikey');
        } else {
            $map->lookupService = null;
        }
        return $map;
    }

    function makeGoogle_Map($registry) {
        require_once(PMDROOT.'/includes/class_map.php');
        $map = new Google_Map($registry);
        $map->lookupService = $registry->getConfig('geocoding_service');
        $map->apiKeyGeoCoding = $registry->getConfig(($map->lookupService == 'google' ? 'google_server' : $map->lookupService).'_apikey');
        $map->apiKey = $registry->getConfig('google_apikey');
        return $map;
    }

    function makeMapQuest_Map($registry) {
        require_once(PMDROOT.'/includes/class_map.php');
        $map = new MapQuest_Map($registry);
        $map->apiKey = $registry->getConfig('mapquest_apikey');
        $map->lookupService = $registry->getConfig('geocoding_service');
        $map->apiKeyGeoCoding = $registry->getConfig(($map->lookupService == 'google' ? 'google_server' : $map->lookupService).'_apikey');
        return $map;
    }

    function makeBing_Map($registry) {
        require_once(PMDROOT.'/includes/class_map.php');
        $map = new Bing_Map($registry);
        $map->apiKey = $registry->getConfig('bing_apikey');
        return $map;
    }

    function makeSitemapIndex($registry) {
        require_once(PMDROOT.'/includes/class_sitemap.php');
        return new SitemapIndex();
    }

    function makeHTML_Filter($registry) {
        require_once(PMDROOT.'/includes/htmlpurifier/HTMLPurifier.standalone.php');
        require_once(PMDROOT.'/includes/htmlpurifier/standalone/HTMLPurifier/Filter/YouTube.php');
        require_once(PMDROOT.'/includes/class_html_filter.php');
        return new HTML_Filter($registry);
    }

    function makeZip($registry, $archive) {
        require_once(PMDROOT.'/includes/PclZip/pclzip.lib.php');
        return new PclZip($archive);
    }

    function makeGoogle_Translate($registry) {
        require_once(PMDROOT.'/includes/class_translate.php');
        return new Google_Translate($registry);
    }

    function makeDates_Local($registry) {
        require_once(PMDROOT.'/includes/class_dates.php');
        return new Dates_Local($registry);
    }

    function makeForm($registry) {
        require_once(PMDROOT.'/includes/class_form.php');
        require_once(PMDROOT.'/includes/class_validate.php');
        $form = new Form($registry);
        if(PMD_SECTION == 'admin') {
            if(DEMO_MODE) {
                $form->allowed_html_tags = 'p,ul,ol,li,strong,em,u,span,hr,div,br,*[style]';
            } else {
                $form->allowed_html_tags = null;
            }
        } else {
            // HTML purifier requires a unique in order array
            $form->allowed_html_tags = $registry->getConfig('allowed_html_tags');
        }
        return $form;
    }

    function makeCache($registry) {
        require_once(PMDROOT.'/includes/class_cache.php');
        return new Cache_File($registry);
    }

    function makeCache_Memcache($registry) {
        require_once(PMDROOT.'/includes/class_cache.php');
        try {
            if(!defined('MEMCACHE_ADDRESS')) {
                $address = '127.0.0.1';
                $port = 11211;
            } elseif(MEMCACHE_ADDRESS !== '') {
                $address = MEMCACHE_ADDRESS;
                if(!defined('MEMCACHE_PORT') OR MEMCACHE_PORT == '') {
                    $port = 11211;
                } else {
                    $port = MEMCACHE_PORT;
                }
            } else {
                throw new Exception('Memcache disabled by global variable.',E_USER_NOTICE);
            }
            $cache = new Cache_Memcache($registry,$address,$port);
        } catch (Exception $e) {
            throw $e;
        }
        return $cache;
    }

    function makeCache_Fallback($registry) {
        require_once(PMDROOT.'/includes/class_cache.php');
        try {
            $cache = $registry->get('Cache_Memcache');
        } catch (Exception $e) {
            $cache = new Cache_File($registry);
        }
        return $cache;
    }

    function makeBanner_Display($registry) {
        require_once(PMDROOT.'/includes/class_banner_display.php');
        $banner_display = new Banner_Display($registry);
        return $banner_display;
    }

    function makeAuthentication($registry) {
        require_once(PMDROOT.'/includes/class_authentication.php');
        switch(PMD_SECTION) {
            case 'admin':
                $auth = new AuthenticationAdmin($registry);
                return $auth;
            default:
                if($registry->getConfig('login_module') AND file_exists(PMDROOT.'/modules/login/'.$registry->getConfig('login_module').'/'.$registry->getConfig('login_module').'.php')) {
                    require_once(PMDROOT.'/includes/class_authentication_module.php');
                    require_once(PMDROOT.'/modules/login/'.$registry->getConfig('login_module').'/'.$registry->getConfig('login_module').'.php');
                    $name = 'Authentication_'.$registry->getConfig('login_module');
                    $auth = new $name($registry);
                } else {
                    $auth = new AuthenticationUser($registry);
                }
                return $auth;
        }
    }

    function makeEncryption($registry) {
        require_once(PMDROOT.'/includes/class_encryption.php');
        if(!function_exists('mcrypt_encrypt')) {
            require_once(PMDROOT.'/includes/encryption/Aes.inc.php');
            require_once(PMDROOT.'/includes/encryption/AesCtr.inc.php');
            $encryption = new Encryption($registry);
        } else {
            $encryption = new Encryption_MCrypt($registry);
        }
        return $encryption;
    }

    function makeSpell_Checker($registry) {
        include(PMDROOT.'/modules/spell/'.$registry->getConfig('spell_checker').'.php');
        $name = 'Spell_'.$registry->getConfig('spell_checker');
        return new $name($registry);
    }

    function makeSMS($registry) {
        include(PMDROOT.'/includes/class_sms.php');
        $gateway = $registry->getConfig('sms_gateway');
        if(empty($gateway)) {
            return false;
        } else {
            include(PMDROOT.'/modules/sms/'.$gateway.'/'.$gateway.'.php');
            try {
                return new $gateway($registry);
            } catch (Exception $e) {
                return false;
            }
        }
    }

    function makeEmail_Marketing($registry) {
        include(PMDROOT.'/includes/class_email_marketing.php');
        $marketing = $registry->getConfig('email_marketing');
        if(empty($marketing)) {
            return false;
        } else {
            include(PMDROOT.'/modules/email_marketing/'.$marketing.'/'.$marketing.'.php');
            try {
                $marketer = new $marketing($registry);
                return $marketer;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    function makeCaptcha($registry) {
        include(PMDROOT.'/includes/class_captcha.php');
        $captcha = $registry->getConfig('captcha_type');
        if(empty($captcha)) {
            $captcha = 'Image';
        }
        include(PMDROOT.'/modules/captcha/'.$captcha.'/'.$captcha.'.php');
        return new $captcha($registry);
    }

    function makeAPI($registry, $parameters) {
        include(PMDROOT.'/includes/class_api.php');
        return new API($registry, $parameters);
    }
}
?>