<?php
class Locations_Featured_Block extends Template_Block {
    function content($limit = 15) {
        $block_locations_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_locations_featured.tpl');
        $block_locations_template->cache_id = 'block_locations_featured';
        $block_locations_template->expire = 900;
        if(!($block_locations_template->isCached())) {
            if(!$locations = $this->db->GetAll("SELECT id, title, link, count_total, friendly_url_path, hidden, no_follow FROM ".T_LOCATIONS." WHERE level=1 AND featured=1 ORDER BY left_")) {
                $locations = $this->db->GetAll("SELECT id, title, link, count_total, friendly_url_path, hidden, no_follow FROM ".T_LOCATIONS." WHERE level=1 ORDER BY count_total DESC, title ASC LIMIT ?",array(intval($limit)));
            }
            foreach($locations as $key=>$location) {
                if(($this->PMDR->getConfig('loc_empty_hidden') AND $location['count_total'] == 0) OR $location['hidden']) {
                    unset($locations[$key]);
                    continue;
                }
                if($location['link'] != '') {
                    $locations[$key]['url'] = $location['link'];
                } else {
                    $locations[$key]['url'] = $this->PMDR->get('Locations')->getURL($location['id'],$location['friendly_url_path']);
                }
            }
        }
        $block_locations_template->set('locations',$locations);
        $block_locations_template->set('show_indexes',$this->PMDR->getConfig('loc_show_indexes'));
        return $block_locations_template;
    }
}
?>