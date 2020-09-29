<!DOCTYPE html>
<html>
    <head>
    <title><?php echo $this->escape($page_title); ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-mobile-web-app-title" content="<?php echo $config['title']; ?>">
    <link rel="apple-touch-icon" href="<?php echo $this->urlCDN('images/icon_iphone.png'); ?>" />
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $this->urlCDN('images/icon_ipad.png'); ?>" />
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo $this->urlCDN('images/icon_ipad_retina.png'); ?>" />
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $this->urlCDN('images/icon_iphone_retina.png'); ?>" />
    <link rel="apple-touch-startup-image" href="<?php echo $this->urlCDN('images/startup.png'); ?>">
    <?php echo $javascript; ?>
    <script type="text/javascript">
    $(document).bind("mobileinit", function() {
        $.mobile.defaultPageTransition = 'fade';
     });
    </script>
    <link rel="stylesheet" href="<?php echo CDN_URL; ?>/includes/jquery/mobile/jquery_mobile.css" />
    <script src="<?php echo CDN_URL; ?>/includes/jquery/mobile/jquery_mobile.js"></script>
    <link rel="stylesheet" href="<?php echo $this->urlCDN('add2home.css'); ?>">
    <script type="text/javascript">
    var addToHomeConfig = {
        animationIn: 'bubble',
        animationOut: 'drop',
        startDelay: 2000, // Display after 2 seconds
        lifespan: 10000, // Displays for 10 seconds
        expire: 10, // Displays every 10 minutes
        touchIcon:true,
        returningVisitor: false, // Display on the first visit if set to false
    };
    </script>
    <script type="application/javascript" src="<?php echo $this->urlCDN('add2home.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo $this->urlCDN('css.css'); ?>" />
</head>
<body>
<?php if($maintenance) { ?>
    <div data-role="header" data-theme="e">
        <h6 style="white-space:normal"><?php echo $lang['maintenance_on']; ?></h6>
    </div>
<?php } ?>
<div data-role="page" data-theme="a">
    <div data-role="header" data-theme="a">
        <h1><?php echo $page_title; ?></h1>
    </div>