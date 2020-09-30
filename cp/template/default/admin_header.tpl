<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
    <title><?php echo $lang['admin_general_title']; ?></title>
    <link rel="icon" href="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>images/favicon.ico" type="image/x-icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>bootstrap/css/bootstrap-theme.css" />
    <!--[if lt IE 9]>
        <script src="<?php echo URL_SCHEME; ?>://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <?php echo $javascript; ?>
    <script type="text/javascript" src="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>bootstrap/js/bootstrap.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>bootstrap/js/bootbox.js"></script>
    <script type="text/javascript">
    var btn = $.fn.button.noConflict() // reverts $.fn.button to jqueryui btn
    $.fn.btn = btn
    </script>
    <?php echo $css; ?>
    <link href="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>bootstrap/css/font-awesome.css" rel="stylesheet" media="screen">
</head>
<body>
    <div id="loading">
        <img src="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>images/loading_bar.gif" />
    </div>
    <div class="navbar navbar-default navbar-fixed-top">
        <div class="navbar-top"></div>
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo BASE_URL_ADMIN; ?>"><img class="logo" src="<?php echo $logo; ?>"></a>
            </div>
            <div class="navbar-collapse collapse" id="navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li class="dropdown">
                        <a title="Home" href="./admin_index.php"><?php echo $lang['admin_general_menu_home']; ?></a>
                        <ul class="dropdown-menu">
                            <li class="top_border"></li>
                            <li><a href="./admin_index.php"><?php echo $lang['admin_general_menu_control_panel_home']; ?></a></li>
                            <li><a target="_blank" href="<?php echo BASE_URL_NOSSL; ?>/index.php"><?php echo $lang['admin_general_menu_index']; ?></a></li>
                            <?php if(LOGGED_IN) { ?>
                            <li><a href="./admin_index.php?check=true"><?php echo $lang['admin_general_menu_check_updates']; ?></a></li>
                            <li><a href="./admin_index.php?action=logout"><?php echo $lang['admin_general_menu_logout']; ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="./admin_users.php"><?php echo $lang['admin_general_menu_users']; ?></a>
                        <ul class="dropdown-menu">
                            <li class="top_border"></li>
                            <li><a href="./admin_users.php"><?php echo $lang['admin_general_menu_users']; ?></a></li>
                            <li><a href="./admin_users.php?action=search"><?php echo $lang['admin_general_menu_users_search']; ?></a></li>
                            <li><a href="./admin_users.php?action=add"><?php echo $lang['admin_general_menu_users_add']; ?></a></li>
                            <li><a href="./admin_users_groups.php"><?php echo $lang['admin_general_menu_users_groups']; ?></a></li>
                            <li><a href="./admin_users_groups.php?action=add"><?php echo $lang['admin_general_menu_users_groups_add']; ?></a></li>
                            <li><a href="./admin_users_merge.php"><?php echo $lang['admin_general_menu_users_merge']; ?></a></li>
                            <li><a href="./admin_contact_requests.php"><?php echo $lang['admin_general_menu_contact_requests']; ?></a></li>
                            <li><a href="./admin_messages.php"><?php echo $lang['admin_general_menu_messages']; ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="./admin_orders.php"><?php echo $lang['admin_general_menu_orders']; ?></a>
                        <ul class="dropdown-menu">
                            <li class="top_border"></li>
                            <li><a href="./admin_orders.php"><?php echo $lang['admin_general_menu_orders']; ?></a></li>
                            <li><a href="./admin_orders.php?action=search"><?php echo $lang['admin_general_menu_search']; ?></a></li>
                            <li><a href="./admin_orders_add.php"><?php echo $lang['admin_general_menu_orders_add']; ?></a></li>
                            <li class="dropdown-submenu">
                                <a href="./admin_invoices.php"><?php echo $lang['admin_general_menu_billing']; ?></a>
                                <ul class="dropdown-menu">
                                    <li class="top_border"></li>
                                    <li><a href="./admin_invoices.php"><?php echo $lang['admin_general_menu_invoices']; ?></a></li>
                                    <li><a href="./admin_invoices.php?action=search"><?php echo $lang['admin_general_menu_invoices_search']; ?></a></li>
                                    <li><a href="./admin_invoices.php?action=add"><?php echo $lang['admin_general_menu_invoices_add']; ?></a></li>
                                    <li><a href="./admin_transactions.php"><?php echo $lang['admin_general_menu_transactions']; ?></a></li>
                                </ul>
                            </li>
                            <li><a href="./admin_cancellations.php"><?php echo $lang['admin_general_menu_cancellations']; ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="./admin_listings_search.php"><?php echo $lang['admin_general_menu_content']; ?></a>
                        <ul class="dropdown-menu">
                            <li class="top_border"></li>
                            <li class="dropdown-submenu"><a href="./admin_listings.php"><?php echo $lang['admin_general_menu_listings']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_listings.php"><?php echo $lang['admin_general_menu_listings']; ?></a></li>
                                    <li><a href="./admin_listings_search.php"><?php echo $lang['admin_general_menu_listings_search']; ?></a></li>
                                    <li><a href="./admin_orders_add.php?type=listing_membership"><?php echo $lang['admin_general_menu_listings_add']; ?></a></li>
                                    <li><a href="./admin_categories.php"><?php echo $lang['admin_general_menu_categories']; ?></a></li>
                                    <li><a href="./admin_locations.php"><?php echo $lang['admin_general_menu_locations']; ?></a></li>
                                    <li><a href="./admin_documents.php"><?php echo $lang['admin_general_menu_documents']; ?></a></li>
                                    <li><a href="./admin_images.php"><?php echo $lang['admin_general_menu_images']; ?></a></li>
                                    <li><a href="./admin_listings_move.php"><?php echo $lang['admin_general_menu_listings_move']; ?></a></li>
                                    <li><a href="./admin_listings_suggestions.php"><?php echo $lang['admin_general_menu_listings_suggestions']; ?></a></li>
                                    <li><a href="./admin_listings_claims.php"><?php echo $lang['admin_general_menu_listings_claims']; ?></a></li>
                                    <li><a href="./admin_import.php"><?php echo $lang['admin_general_menu_importer']; ?></a></li>
                                    <li><a href="./admin_export.php"><?php echo $lang['admin_general_menu_exporter']; ?></a></li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu"><a href="./admin_classifieds.php"><?php echo $lang['admin_general_menu_classifieds']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_classifieds.php"><?php echo $lang['admin_general_menu_classifieds']; ?></a></li>
                                    <li><a href="./admin_classifieds_categories.php"><?php echo $lang['admin_general_menu_categories']; ?></a></li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu"><a href="./admin_events.php"><?php echo $lang['admin_general_menu_events']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_events.php"><?php echo $lang['admin_general_menu_events']; ?></a></li>
                                    <li><a href="./admin_events_categories.php"><?php echo $lang['admin_general_menu_events_categories']; ?></a></li>
                                </ul>
                            </li>
                            <?php if($config['jobs']) { ?>
                            <li class="dropdown-submenu"><a href="./admin_jobs.php"><?php echo $lang['admin_general_menu_jobs']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_jobs.php"><?php echo $lang['admin_general_menu_jobs']; ?></a></li>
                                    <li><a href="./admin_jobs_categories.php"><?php echo $lang['admin_general_menu_jobs_categories']; ?></a></li>
                                </ul>
                            </li>
                            <?php } ?>
                            <li class="dropdown-submenu"><a href="./admin_banners.php"><?php echo $lang['admin_general_menu_banners']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_banners.php"><?php echo $lang['admin_general_menu_banners']; ?></a></li>
                                    <li><a href="./admin_banners.php?action=add"><?php echo $lang['admin_general_menu_banners_add']; ?></a></li>
                                    <li><a href="./admin_banners_types.php"><?php echo $lang['admin_general_menu_banners_types']; ?></a></li>
                                    <li><a href="./admin_banners_types.php?action=add"><?php echo $lang['admin_general_menu_banners_types_add']; ?></a></li>
                                </ul>
                            </li>
                            <?php if(ADDON_BLOG) { ?>
                            <li class="dropdown-submenu"><a href="./admin_blog.php"><?php echo $lang['admin_general_menu_blog']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_blog.php"><?php echo $lang['admin_general_menu_blog_posts']; ?></a></li>
                                    <li><a href="./admin_blog.php?action=add"><?php echo $lang['admin_general_menu_blog_add']; ?></a></li>
                                    <li><a href="./admin_blog_comments.php"><?php echo $lang['admin_general_menu_blog_comments']; ?></a></li>
                                    <li><a href="./admin_blog.php?action=search"><?php echo $lang['admin_general_menu_search']; ?></a></li>
                                    <li><a href="./admin_blog_categories.php"><?php echo $lang['admin_general_menu_categories']; ?></a></li>
                                    <li><a href="./admin_blog_categories.php?action=add"><?php echo $lang['admin_general_menu_categories_add']; ?></a></li>
                                </ul>
                            </li>
                            <?php } ?>
                            <li class="dropdown-submenu"><a href="./admin_reviews.php"><?php echo $lang['admin_general_menu_listings_reviews_ratings']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_reviews.php"><?php echo $lang['admin_general_menu_listings_reviews']; ?></a></li>
                                    <li><a href="./admin_ratings.php"><?php echo $lang['admin_general_menu_ratings']; ?></a></li>
                                    <li><a href="./admin_ratings_categories.php"><?php echo $lang['admin_general_menu_ratings_categories']; ?></a></li>
                                </ul>
                            </li>
                            <li><a href="./admin_pages.php"><?php echo $lang['admin_general_menu_pages']; ?></a></li>
                            <li><a href="./admin_site_links.php"><?php echo $lang['admin_general_menu_site_links']; ?></a></li>
                            <li class="dropdown-submenu"><a href="#"><?php echo $lang['admin_general_menu_faq']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_faq_categories.php"><?php echo $lang['admin_general_menu_faq_categories']; ?></a></li>
                                    <li><a href="./admin_faq_categories.php?action=add"><?php echo $lang['admin_general_menu_categories_add']; ?></a></li>
                                    <li><a href="./admin_faq_questions.php"><?php echo $lang['admin_general_menu_faq_questions']; ?></a></li>
                                    <li><a href="./admin_faq_questions.php?action=add"><?php echo $lang['admin_general_menu_faq_questions_add']; ?></a></li>
                                </ul>
                            </li>
                            <li><a href="./admin_zones.php"><?php echo $lang['admin_general_menu_zones']; ?></a></li>
                            <li><a href="./admin_blocks.php"><?php echo $lang['admin_general_menu_blocks']; ?></a></li>
                            <li><a href="./admin_feeds_external.php"><?php echo $lang['admin_general_menu_external_feeds']; ?></a></li>
                            <li><a href="./admin_updates.php"><?php echo $lang['admin_general_menu_approve_updates']; ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a data-toggle="dropdown" href="#"><?php echo $lang['admin_general_menu_tools']; ?></a>
                        <ul class="dropdown-menu">
                            <li class="top_border"></li>
                            <li><a href="./admin_templates.php"><?php echo $lang['admin_general_menu_templates_manager']; ?></a></li>
                            <li><a href="./admin_menu_links.php"><?php echo $lang['admin_general_menu_links_manager']; ?></a></li>
                            <li><a href="./admin_fields_groups.php"><?php echo $lang['admin_general_menu_field_editor']; ?></a></li>
                            <li><a href="./admin_email_campaigns.php"><?php echo $lang['admin_general_menu_email_manager']; ?></a></li>
                            <li><a href="./admin_sitemap_xml.php"><?php echo $lang['admin_general_menu_sitemap_manager']; ?></a></li>
                            <li><a href="./admin_remote_features.php"><?php echo $lang['admin_general_menu_remote_features']; ?></a></li>
                            <?php if(ADDON_LINK_CHECKER) { ?><li><a href="./admin_link_checker.php"><?php echo $lang['admin_general_menu_link_checker']; ?></a></li><?php } ?>
                            <li class="dropdown-submenu"><a href="javascript:void(0);"><?php echo $lang['admin_general_menu_reports']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_log.php"><?php echo $lang['admin_general_menu_activity_log']; ?></a></li>
                                    <li><a href="./admin_search_log.php"><?php echo $lang['admin_general_menu_search_log']; ?></a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <?php if($plugin_links) { ?>
                    <li class="dropdown">
                        <a href="./admin_plugins.php"><?php echo $lang['admin_general_menu_plugins']; ?></a>
                        <ul class="dropdown-menu">
                            <li class="top_border"></li>
                            <?php foreach($plugin_links AS $key=>$link) { ?>
                                <li><a href="./admin_plugin_page.php?id=<?php echo $key; ?>"><?php echo $link['menu']['menu_text']; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown"><?php echo $lang['admin_general_menu_setup']; ?></a>
                        <ul class="dropdown-menu">
                            <li class="top_border"></li>
                            <li><a href="./admin_settings.php"><?php echo $lang['admin_general_menu_settings']; ?></a></li>
                            <li class="dropdown-submenu"><a href="./admin_languages.php"><?php echo $lang['admin_general_menu_language']; ?></a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_languages.php"><?php echo $lang['admin_general_menu_languages']; ?></a></li>
                                    <li><a href="./admin_phrases.php"><?php echo $lang['admin_general_menu_phrases']; ?></a></li>
                                    <li><a href="./admin_phrases_replace.php"><?php echo $lang['admin_general_menu_phrases_find']; ?></a></li>
                                </ul>
                            </li>
                            <li><a href="./admin_products.php"><?php echo $lang['admin_general_menu_products']; ?></a></li>
                            <li class="dropdown-submenu"><a href="#"><?php echo $lang['admin_general_menu_billing']; ?></a>
                                <ul class="dropdown-menu">
                                    <?php if(ADDON_DISCOUNT_CODES) { ?>
                                        <li><a href="./admin_discount_codes.php"><?php echo $lang['admin_general_menu_discount_codes']; ?></a></li>
                                    <?php } ?>
                                    <li><a href="./admin_tax.php"><?php echo $lang['admin_general_menu_tax_rates']; ?></a></li>
                                    <li><a href="./admin_payment_gateways.php"><?php echo $lang['admin_general_menu_payment_gateways']; ?></a></li>
                                    <li><a href="./admin_gateways_log.php"><?php echo $lang['admin_general_menu_gateway_log']; ?></a></li>
                                </ul>
                            </li>
                            <li><a href="./admin_email_templates.php"><?php echo $lang['admin_general_menu_email_templates']; ?></a></li>
                            <li><a href="./admin_zip_codes.php"><?php echo $lang['admin_general_menu_zip_codes']; ?></a></li>
                            <li class="dropdown-submenu"><a href="#">Integrations</a>
                                <ul class="dropdown-menu">
                                    <li><a href="./admin_captchas.php"><?php echo $lang['admin_general_menu_captcha']; ?></a></li>
                                    <li><a href="./admin_sms_gateways.php"><?php echo $lang['admin_general_menu_sms_gateways']; ?></a></li>
                                    <li><a href="./admin_email_marketing.php"><?php echo $lang['admin_general_menu_email_marketing']; ?></a></li>
                                </ul>
                            </li>
                            <li><a href="./admin_plugins.php"><?php echo $lang['admin_general_menu_plugins']; ?></a></li>
                            <li><a href="./admin_settings_custom.php"><?php echo $lang['admin_general_menu_custom_settings']; ?></a></li>
                            <li><a href="./admin_maintenance.php"><?php echo $lang['admin_general_menu_maintenance']; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                </ul>
                <?php if(LOGGED_IN) { ?>
                    <form class="navbar-form navbar-left" action="" role="search">
                         <div class="form-group">
                             <input id="quicksearch" autocomplete="off" type="text" class="col-md-4 form-control" placeholder="<?php echo $lang['admin_general_quick_search']; ?>">
                         </div>
                    </form>
                    <div class="navbar-right">
                        <div class="header-shortcuts">
                            <a class="btn btn-default navbar-btn" href="./admin_calendar.php"><i class="glyphicon glyphicon-calendar"></i></a>
                            <a class="btn btn-default navbar-btn" title="<?php echo $lang['admin_general_menu_settings']; ?>" href="./admin_settings.php"><i class="glyphicon glyphicon-wrench"></i></a>
                            <?php if(!ADDON_UNBRANDING) { ?>
                                <div class="btn-group">
                                    <a class="btn btn-default navbar-btn" target="_blank" href="http://manual.phpmydirectory.com"><i class="glyphicon glyphicon-question-sign"></i></a>
                                    <button id="help-suggestions-link" type="button" class="btn btn-default navbar-btn dropdown-toggle">
                                        <span class="caret"></span>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                        <a class="btn btn-default navbar-btn" title="<?php echo $admin_name; ?>" href="./admin_users_summary.php?id=<?php echo $_SESSION['admin_id']; ?>"><i class="glyphicon glyphicon-user"></i></a>
                        <a class="btn btn-default navbar-btn" href="./admin_index.php?action=logout"><i class="glyphicon glyphicon-lock"></i> <?php echo $lang['admin_general_menu_logout']; ?></a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div align="left" id="quicksearchresults" data-html="true"></div>


    <div class="wrapper">
        <?php if(LOGGED_IN AND $template_page_menu) { ?>
            <div id="main-nav-bg"></div>
            <div id="main-nav">
                <div class="navigation">
                    <?php echo $template_page_menu; ?>
                </div>
            </div>
            <div id="page_content">
                <div class="container-full">
                    <div class="row">
                        <div class="col-lg-24">
        <?php } else { ?>
            <div id="content-full">
                <div class="container-full">
                    <div class="row">
                        <div class="col-lg-24">
        <?php } ?>
    <?php echo $message; ?>
