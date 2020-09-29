<?php
class Options_Block extends Template_Block {
    function content() {
        // Get the languages array from the cache or the database
        if(!$languages_array = $this->PMDR->get('Cache')->get('language_options',0,'language_')) {
            $languages_array = $this->db->GetAssoc("SELECT languageid, title FROM ".T_LANGUAGES." WHERE active=1");
            $this->PMDR->get('Cache')->write('language_options',$languages_array,'language_');
        }

        // Get all templates from the /template/ folder setting the current template to the first option
        $templates = glob(PMDROOT.'/template/*',GLOB_ONLYDIR);
        $templates_array = array();
        foreach($templates as $file) {
            $filename = pathinfo($file);
            array_push($templates_array,$filename['basename']);
        }
        $options = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_options.tpl');
        if(count($languages_array) > 1) {
            $options->set('languages_array',$languages_array);
        }
        if(count($templates_array) > 1) {
            $options->set('templates_array',$templates_array);
        }
        $options->set('current_language',$this->PMDR->getConfig('language'));
        $options->set('current_template',$this->PMDR->getConfig('template'));
        return $options;
    }
}
?>