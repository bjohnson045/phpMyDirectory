<!DOCTYPE html>
<html dir="<?php echo $textdirection; ?>" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $languagecode; ?>" lang="<?php echo $languagecode; ?>">
<head>
    <meta name="robots" content="noindex,nofollow" />
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['charset']; ?>" />
    <title><?php echo $this->escape($meta_title); ?></title>
    <link rel="icon" href="<?php echo $this->urlCDN('images/favicon.ico'); ?>" type="image/x-icon" />
    <script src="<?php echo $this->urlCDN('bootstrap/js/bootstrap.js'); ?>"></script>
    <?php echo $javascript; ?>
    <link href="<?php echo $this->urlCDN('bootstrap/css/bootstrap.css'); ?>" rel="stylesheet" media="screen">
    <link href="<?php echo $this->urlCDN('bootstrap/css/font-awesome.css'); ?>" rel="stylesheet" media="screen">
    <?php echo $css; ?>
    <!--[if lt IE 9]>
      <script src="<?php echo $this->urlCDN('bootstrap/js/html5shiv.js'); ?>"></script>
      <script src="<?php echo $this->urlCDN('bootstrap/js/respond.js'); ?>"></script>
    <![endif]-->
</head>
<body>
    <div class="container">