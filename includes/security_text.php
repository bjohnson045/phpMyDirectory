<?php
include ('../defaults.php');
include (PMDROOT.'/includes/class_security_text.php');

$imageText = new Captcha_Image($PMDR);
$imageText->addText($_SESSION['security_code']);
header('Content-type: image/jpeg');
ImageJpeg($imageText->getImage());
?>