<!DOCTYPE html>
<html dir="<?php echo $textdirection; ?>" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $languagecode; ?>" lang="<?php echo $languagecode; ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['charset']; ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php echo $meta_tags; ?>
    <title><?php echo $this->escape($meta_title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo $this->urlCDN('images/favicon.ico'); ?>" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $this->urlCDN('images/favicon.ico'); ?>" type="image/x-icon" />
    <?php if(isset($previous_url)) { ?>
        <link rel="prev" href="<?php echo $previous_url; ?>" />
    <?php } ?>
    <?php if(isset($next_url)) { ?>
        <link rel="next" href="<?php echo $next_url; ?>" />
    <?php } ?>
    <?php echo $canonical_url; ?>
    <?php echo $javascript; ?>
    <script src="<?php echo $this->urlCDN('bootstrap/js/bootstrap.js'); ?>"></script>
    <link href="<?php echo $this->urlCDN('bootstrap/css/bootstrap.css'); ?>" rel="stylesheet" media="screen">
    <link href="<?php echo $this->urlCDN('bootstrap/css/font-awesome.css'); ?>" rel="stylesheet" media="screen">
    <?php echo $css; ?>
    <!--[if lt IE 9]>
      <script src="<?php echo $this->urlCDN('bootstrap/js/html5shiv.js'); ?>"></script>
      <script src="<?php echo $this->urlCDN('bootstrap/js/respond.js'); ?>"></script>
    <![endif]-->
</head>
<body class="<?php if($maintenance) { ?> maintenance-body<?php } ?><?php if($admin_as_user) { ?> logged-in-line-body<?php } ?>">
<?php if($maintenance) { ?>
    <div id="maintenance-line" class="danger text-center">
        <span class="text-danger"><strong><?php echo $lang['maintenance_on']; ?></strong></span>
    </div>
<?php } ?>
<?php if($admin_as_user) { ?>
    <div id="logged-in-line" class="info text-center">
        <span class="text-info"><strong><?php echo $admin_as_user_message; ?></strong></span>
    </div>
<?php } ?>
<div id="header">
    <div class="header-top container hidden-xs hidden-sm">
        <div class="row">
            <div class="col-lg-4 col-md-3 col-sm-4">
                <a id="brand" href="<?php echo BASE_URL; ?>">
                    <?php if(!empty($config['logo'])) { ?>
                        <img class="logo" title="<?php echo $this->escape($title); ?>" src="<?php echo get_file_url_cdn(TEMP_UPLOAD_PATH.$config['logo']); ?>" alt="<?php echo $this->escape($title); ?>">
                    <?php } else {?>
                        <?php echo $this->escape($title); ?>
                    <?php } ?>
                </a>
            </div>
            <div class="col-lg-8 col-md-9 col-sm-8">
                <div class="pull-right">
                    <?php if(PMD_SECTION == 'public') { ?><?php echo $this->block('search'); ?><?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="navbar navbar-default navbar-static-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <?php if(PMD_SECTION == 'public' AND ($this->PMDR->getConfig('search_display_all') OR on_page('/index.php'))) { ?>
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-search">
                    <span class="fa fa-search fa-fw fa-lg"></span>
                </button>
                <?php } ?>
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-menu">
                    <span class="fa fa-bars fa-fw fa-lg"></span>
                </button>
                <div class="hidden-lg hidden-xl hidden-md">
                    <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                        <?php echo $this->escape($title); ?>
                    </a>
                </div>
            </div>
            <div class="collapse hidden-lg hidden-xl hidden-md" id="navbar-collapse-search">
                <?php echo $this->block('search',array('template'=>'search_collapsed')); ?>
            </div>
            <div class="collapse navbar-collapse" id="navbar-collapse-menu">
                <?php echo $this->block('menu'); ?>
                <ul class="nav navbar-nav navbar-right hidden-xs">
                    <?php if(!$username) { ?>
                        <li><a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_account_add.php"><span class="fa fa-pencil"></span> <?php echo $lang['signup']; ?></a></li>
                        <li><a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>index.php"><span class="fa fa-user"></span> <?php echo $lang['login']; ?></a></li>
                    <?php } else { ?>
                        <li><a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>index.php"><span class="fa fa-user"></span> <?php echo $lang['account']; ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <?php if(!on_page('/index.php')) { ?>
        <?php echo $this->block('breadcrumbs'); ?>
    <?php } ?>