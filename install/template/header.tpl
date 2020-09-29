<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>phpMyDirectory Setup</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/install/template/bootstrap/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/install/template/bootstrap/bootstrap-theme.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/install/template/css.css" />
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php echo $javascript; ?>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>/install/template/bootstrap/bootstrap.js"></script>
</head>
<body>
<div class="container">
    <div class="row row-header text-center">
        <div class="col-xs-20 col-xs-offset-2">
            <p><img border="0" src="<?php echo BASE_URL; ?>/install/images/phpmydirectory.jpg"></p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-20 col-xs-offset-2">
        <div id="messages">
            <?php echo $message; ?>
        </div>

