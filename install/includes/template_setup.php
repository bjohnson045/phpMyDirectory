<?php
if(!IN_PMD) exit();

$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/jquery.js"></script>',10);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/jquery_custom.js"></script>',10);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/jquery.css" />',10);

$template_header = $PMDR->getNew('Template',PMDROOT.'/install/template/header.tpl');
$template_footer = $PMDR->getNew('Template',PMDROOT.'/install/template/footer.tpl');

$template_message = $PMDR->getNew('Template',PMDROOT.'/install/template/message.tpl');
$template_message->set('message_types',$PMDR->getMessages());
$template_header->set('message',$template_message);

$template_header->set('javascript',$PMDR->getJavascript());
$template_header->set('css',$PMDR->getCSS());

echo $template_header->render();
if(is_object($template_content)) {
    echo $template_content->render();
} else {
    echo $template_content;
}

echo $template_footer->render();
?>