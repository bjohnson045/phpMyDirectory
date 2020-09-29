<!DOCTYPE html>
<html dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['charset']; ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->escape($meta_title); ?></title>
    <?php echo $javascript; ?>
    <link href="<?php echo $this->urlCDN('bootstrap/css/bootstrap.css'); ?>" rel="stylesheet" media="screen">
    <link href="<?php echo $this->urlCDN('bootstrap/css/font-awesome.css'); ?>" rel="stylesheet" media="screen">
    <?php echo $css; ?>
    <script src="<?php echo $this->urlCDN('bootstrap/js/bootstrap.js'); ?>"></script>
</head>
<body>