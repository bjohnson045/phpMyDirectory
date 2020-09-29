<?php
class Search_Block extends Template_Block {
    function content($parameters = array()) {
        if(!$this->PMDR->getConfig('search_display_all') AND !on_page('/index.php')) {
            return '';
        }

        // Only set values if we are currently viewing a search
        $values = array(
            'keyword'=>'',
            'category'=>'',
            'location_id'=>'',
            'location'=>'',
            'zip_miles'=>''
        );
        if(on_page('/search_results.php')) {
            $values = $_GET;
        }

        if($this->PMDR->getConfig('geolocation_fill') AND empty($values['location']) AND isset($_SESSION['location']) AND $_SESSION['location']) {
            $values['location'] = $_SESSION['location']['city'].', '.$_SESSION['location']['region'];
        }
        if(isset($parameters['template'])) {
            $template = $parameters['template'];
        } else {
            $template = 'search';
        }
        $searchform = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_'.$template.'.tpl');
        $search_form_object = $this->PMDR->getNew('Form');
        $search_form_object->addField('keyword','text',array('label'=>$this->PMDR->getLanguage('public_general_search_find'),'fieldset'=>'search'));
        if(!$search_categories = $this->PMDR->get('Cache')->get('search_categories', 0, 'categories_')) {
            $search_categories = $this->PMDR->get('Categories')->getAssoc();
            $this->PMDR->get('Cache')->write('search_categories',$search_categories,'categories_');
        }
        if(!$search_locations = $this->PMDR->get('Cache')->get('search_locations', 0, 'locations_')) {
            $search_locations = $this->db->GetAssoc("SELECT id, title FROM ".T_LOCATIONS." WHERE hidden=0 AND level=1 ORDER BY left_");
            $this->PMDR->get('Cache')->write('search_locations',$search_locations,'locations_');
        }
        $search_form_object->addField('category','select',array('label'=>$this->PMDR->getLanguage('public_general_search_category'),'fieldset'=>'search','first_option'=>'','options'=>$search_categories));
        $search_form_object->addField('location_id','select',array('label'=>$this->PMDR->getLanguage('public_general_search_location'),'fieldset'=>'search','first_option'=>'','options'=>$search_locations));
        $search_form_object->addField('location','text',array('label'=>$this->PMDR->getLanguage('public_general_search_location'),'fieldset'=>'search'));

        if(is_null($zip_codes = $this->PMDR->get('Cache')->get('search_zip_codes', 0, 'zip_codes_'))) {
            $zip_codes = $this->db->GetOne("SELECT COUNT(*) FROM ".T_ZIP_DATA);
            $this->PMDR->get('Cache')->write('search_zip_codes',$zip_codes,'zip_codes_');
        }
        if($zip_codes) {
            $search_form_object->addField('zip_miles','select',array('label'=>$this->PMDR->getLanguage('public_general_search_within'),'fieldset'=>'search','options'=>array(''=>$this->PMDR->getLanguage('public_general_search_miles'),'5'=>'5','10'=>'10','25'=>'25','50'=>'50','100'=>'100')));
        }
        $search_form_object->addField('submit_search','submit',array('label'=>$this->PMDR->getLanguage('public_general_search_search'),'fieldset'=>'button'));
        $this->PMDR->get('Fields')->addToForm($search_form_object,'listings',array('fieldset'=>'search'));
        $search_form_object->loadValues($values);
        $searchform->set('form',$search_form_object);
        return $searchform;
    }
}
?>