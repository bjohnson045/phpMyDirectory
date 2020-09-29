<?php
class Classifieds_New_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_classifieds_new_number'));
        }
        if($limit) {
            $block_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_classifieds_new.tpl');
            $block_template->cache_id = 'block_classifieds_new'.$limit;
            $block_template->expire = 900;
            if(!($block_template->isCached())) {
                $records = $this->db->GetAll("
                    SELECT
                        c.*,
                        l.classifieds_images_allow,
                        ci.id AS image_id,
                        ci.extension AS image_extension
                    FROM
                        ".T_CLASSIFIEDS." c
                        INNER JOIN ".T_LISTINGS." l ON c.listing_id=l.id
                        LEFT JOIN (SELECT classified_id, MIN(id) id FROM ".T_CLASSIFIEDS_IMAGES." GROUP BY classified_id) cii ON cii.classified_id=c.id
                        LEFT JOIN ".T_CLASSIFIEDS_IMAGES." ci on ci.id=cii.id
                    WHERE
                        l.status='active' AND
                        (c.expire_date > NOW() OR c.expire_date IS NULL)
                    ORDER BY date DESC
                    LIMIT ?",array(intval($limit))
                );
                foreach($records as $key=>$record) {
                    if($record['classifieds_images_allow']) {
                        $records[$key]['thumb'] = $this->PMDR->get('Classifieds')->getImageThumbnailURL($classified);
                        $records[$key]['image'] = $this->PMDR->get('Classifieds')->getImageURL($classified);
                    }
                    $records[$key]['description'] = Strings::limit_words($record['description'],$this->PMDR->getConfig('block_description_size'));
                    $records[$key]['link'] = $this->PMDR->get('Classifieds')->getURL($record['id'],$record['friendly_url']);
                    $records[$key]['date'] = $this->PMDR->get('Dates_Local')->formatDate($record['date']);
                }
            }
            $block_template->set('records',$records);
            return $block_template;
        }
    }
}
?>