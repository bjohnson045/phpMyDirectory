<?php
/**
* Site Links class
*/
class Site_Links extends TableGateway{
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Site Links constructor
    * @param object Registry
    */
    function __construct($PMDR) {
        $this->table = T_SITE_LINKS;
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Insert a site link
    * @param array $data Site link data
    * @return void
    */
    function insert($data) {
        $id = parent::insert($data);
        if(!empty($data['image'])) {
            $this->processImage($data,$id);
        }
    }

    /**
    * Update a site link
    * @param array $data Site link data
    * @param mixed $id Site link ID
    * @return void
    */
    function update($data,$id) {
        parent::update($data,$id);
        if(!empty($data['image'])) {
            $this->processImage($data,$id);
        }
    }

    /**
    * Delete a site link
    * @param int $id Site link ID
    * @return void
    */
    function delete($id) {
        @unlink(find_file(SITE_LINKS_PATH.$id.'.*'));
        parent::delete($id);
    }

    /**
    * Get Links
    * @param mixed $listing_id Int if a listing ID, null otherwise
    * @return array Links
    */
    function getLinks($listing_id = NULL) {
        if(!is_null($listing_id)) {
            $links = $this->db->GetAll("SELECT * FROM ".T_SITE_LINKS." ORDER BY extension");
            $pricing_id = $this->db->GetOne("SELECT o.pricing_id FROM ".T_ORDERS." o WHERE o.type='listing_membership' AND o.type_id=?",array($listing_id));
            foreach($links AS $key=>$link) {
                if($link['requires_active_product']) {
                    $pricing_ids = explode(',',$link['pricing_ids']);
                    if(!in_array($pricing_id,$pricing_ids)) {
                        unset($links[$key]);
                    }
                }
            }
        } else {
            $links = $this->db->GetAll("SELECT * FROM ".T_SITE_LINKS." WHERE requires_active_product=0 ORDER BY extension");
        }
        foreach($links as $key=>$value) {
            $links[$key]['javascript'] = $this->PMDR->get('Cleaner')->clean_output('<script type="text/javascript" src="'.BASE_URL.'/site_links.php?action=display&id='.$value['id'].((!is_null($listing_id) AND is_numeric($listing_id)) ? '&listing_id='.$listing_id : '').'"></script>');
            $links[$key]['example'] = '<script type="text/javascript" src="'.BASE_URL.'/site_links.php?action=display&id='.$value['id'].((!is_null($listing_id) AND is_numeric($listing_id)) ? '&listing_id='.$listing_id : '').'"></script>';
        }
        return $links;
    }

    /**
    * Process a link image
    * @param array $data Link data
    * @param int $id Link ID
    */
    function processImage($data,$id) {
        @unlink(find_file(SITE_LINKS_PATH.$id.'.*'));
        $options = array(
            'width'=>300,
            'height'=>300,
            'enlarge'=>false
        );
        if($extension = $this->PMDR->get('Image_Handler')->process($data['image'],SITE_LINKS_PATH.$id.'.*',$options)) {
            $this->update(array('extension'=>$extension),$id);
        }
    }
}
?>