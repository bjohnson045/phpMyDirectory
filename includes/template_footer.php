<?php
if(!defined('IN_PMD')) exit();

// Load the footer file.  If a custom footer file is set, load it, if not load the default footer.tpl file
if($PMDR->get('footer_file') AND $PMDR->get('Templates')->path($PMDR->get('footer_file'))) {
    $footer = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.$PMDR->get('footer_file'));
} elseif(is_null($PMDR->get('footer_file'))) {
    $footer = $PMDR->getNew('Template',null);
} else {
    $footer = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'footer.tpl');
}

$footer->set('disable_cron',$PMDR->getConfig('disable_cron'));
$footer->set('pmd_version',$PMDR->getConfig('pmd_version'));

$PMDR->get('Plugins')->run_hook('template_footer_end');

unset($languages_array,$templates_array,$options);
?>