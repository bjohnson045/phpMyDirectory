<!DOCTYPE html>
<html>
    <head>
    <title><?php echo $this->escape($page_title); ?></title>
    <link rel="apple-touch-icon" sizes="114x114" href="./images/touch-icon-iphone4.png" />
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <?php echo $javascript; ?>
    <link rel="stylesheet" href="<?php echo $this->urlCDN('css.css'); ?>" />
    <link rel="stylesheet" href="<?php echo CDN_URL; ?>/includes/jquery/mobile/jquery_mobile.css" />
    <script src="<?php echo CDN_URL; ?>/includes/jquery/mobile/jquery_mobile.js"></script>
    <link rel="stylesheet" href="<?php echo $this->urlCDN('add2home.css'); ?>">
    <script type="application/javascript" src="<?php echo $this->urlCDN('add2home.js'); ?>"></script>
    <script type="text/javascript">
    $(document).bind("mobileinit", function() {
        $.mobile.addBackBtn = true;
        $.mobile.loadingMessage = false;
     });
    </script>
</head>
<body>