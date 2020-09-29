<?php
/**
* Classifieds Class
*/
class Classifieds extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database Database object
    */
    var $db;

    /**
    * Classifieds constructor
    * @param object Registry
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_CLASSIFIEDS;
    }

    /**
    * Get classified URL
    * @param int $id
    * @param string $friendly_url
    * @param string $query_string
    * @param string $query_string_rewrite
    * @param string $filename
    * @return string
    */
    function getURL($id, $friendly_url, $query_string='', $query_string_rewrite='.html', $filename='classified.php') {
        if(MOD_REWRITE) {
            return BASE_URL_NOSSL.'/classified/'.$friendly_url.'-'.$id.$query_string_rewrite;
        } else {
            return BASE_URL_NOSSL.'/'.$filename.'?id='.$id.$query_string;
        }
    }

    /**
    * Insert classified
    * @param array $data
    * @return void
    */
    function insert($data) {
        $data['description'] = Strings::limit_characters($data['description'],$this->PMDR->getConfig('classified_description_size'));
        $data['www'] = standardize_url($data['www']);
        if(!isset($data['date'])) {
            $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        }
        if(empty($data['price'])) {
            $data['price'] = null;
        }
        $id = parent::insert($data);
        $this->updateImages($data,$id);
        $this->updateCategories($id,$data['categories'],$data['categories'],$data['primary_category_id']);
        return $id;
    }

    /**
    * Update classified
    * @param array $data
    * @param int $id
    * @return void
    */
    function update($data, $id) {
        $data['www'] = standardize_url($data['www']);
        if(!empty($data['delete_images'])) {
            $images = $this->db->GetAll("SELECT id AS image_id, classified_id AS id, extension FROM ".T_CLASSIFIEDS_IMAGES." WHERE id IN(".$this->PMDR->get('Cleaner')->clean_db(implode(',',$data['delete_images']),false).") AND extension!=''");
            foreach($images AS $image) {
                $this->deleteImageFile($image['id'],$image['image_id'],$image['extension'],true);
            }
        }
        parent::update($data, $id);
        $this->updateImages($data,$id);
        $this->updateCategories($id,$data['categories'],$data['primary_category_id']);
    }

    /**
    * Delete classified
    * @param int $id
    * @return void
    */
    function delete($id, $delete_classifieds = true) {
        $images = $this->db->GetAll("SELECT id AS image_id, classified_id AS id, extension FROM ".T_CLASSIFIEDS_IMAGES." WHERE classified_id=? AND extension!=''",array($id));
        foreach($images AS $image) {
            $this->deleteImageFile($image['id'],$image['image_id'],$image['extension'],false);
        }
        $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS_IMAGES." WHERE classified_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS_CATEGORIES_LOOKUP." WHERE classified_id=?",array($id));

        $this->db->Execute("DELETE FROM ".T_EMAIL_QUEUE." WHERE type='classified' AND type_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_UPDATES." WHERE type='classified' AND type_id=?",array($id));
        if($delete_classifieds) {
            $this->db->Execute("INSERT INTO ".T_REDIRECTS." (type,type_id,type_new,type_id_new,date_redirected) SELECT 'classified', id, 'listing', listing_id, NOW() FROM ".T_CLASSIFIEDS." WHERE id=?",array($id));
            $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS." WHERE id=?",array($id));
        }
    }

    /**
    * Delete a classified image file/thumbnail file.
    * @param int $id Classified ID
    * @param int $image_id Image ID
    * @param string $extension Image extension
    * @param bool $delete_record Delete the database record
    */
    function deleteImageFile($id, $image_id, $extension, $delete_record = true) {
        @unlink(find_file(CLASSIFIEDS_PATH.$id.'-'.$image_id.'.'.$extension));
        @unlink(find_file(CLASSIFIEDS_THUMBNAILS_PATH.$id.'-'.$image_id.'.'.$extension));
        if($delete_record) {
            $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS_IMAGES." WHERE id=?",array($image_id));
        }
    }

    /**
    * Update classifieds images
    * @param mixed $data
    * @param mixed $id
    */
    function updateImages($data,$id) {
        for($x=1; $x < 20; $x++) {
            if(!empty($data['classified_image'.$x])) {
                $this->db->Execute("INSERT INTO ".T_CLASSIFIEDS_IMAGES." (classified_id) VALUES (?)",array($id));
                $image_id = $this->db->Insert_ID();
                $options = array(
                    'width'=>$this->PMDR->getConfig('classified_image_width'),
                    'height'=>$this->PMDR->getConfig('classified_image_height'),
                    'enlarge'=>$this->PMDR->getConfig('classified_image_small'),
                    'watermark'=>true
                );
                if($extension = $this->PMDR->get('Image_Handler')->process($data['classified_image'.$x],CLASSIFIEDS_PATH.$id.'-'.$image_id.'.*',$options)) {
                    $options = array(
                        'width'=>$this->PMDR->getConfig('classified_thumb_width'),
                        'height'=>$this->PMDR->getConfig('classified_thumb_height'),
                        'enlarge'=>$this->PMDR->getConfig('classified_thumb_small'),
                        'crop'=>$this->PMDR->getConfig('classified_thumb_crop')
                    );
                    $this->PMDR->get('Image_Handler')->process($data['classified_image'.$x],CLASSIFIEDS_THUMBNAILS_PATH.$id.'-'.$image_id.'.*',$options);
                    $this->db->Execute("UPDATE ".T_CLASSIFIEDS_IMAGES." SET extension=? WHERE classified_id=?",array($extension,$id));
                } else {
                    $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS_IMAGES." WHERE id=?",array($image_id));
                }
            }
        }
    }

    /**
    * Get an image URL based on classified information
    * @param array $classified
    * @param boolean $noimage
    * @return mixed String if URL found, if not false
    */
    function getImageURL($classified, $noimage = false) {
        $classified = $this->getImageRecord($classified);
        if(isset($classified['image_id'])) {
            if(!empty($classified['image_extension'])) {
                $image = get_file_url_cdn(CLASSIFIEDS_PATH.$classified['id'].'-'.$classified['image_id'].'.'.$classified['image_extension']);
            } else {
                $image = get_file_url_cdn(CLASSIFIEDS_PATH.$classified['id'].'-'.$classified['image_id'].'.*');
            }
        }
        if(!$image AND $noimage) {
            $image = get_file_url_cdn($this->PMDR->get('Templates')->path('images/noimage.png'));
        }
        return $image;
    }

    /**
    * Get an image thumbnail based on classiifed information
    * @param array $classified
    * @param boolean $noimage
    * @return mixed String if URL found, if not false
    */
    function getImageThumbnailURL($classified, $noimage = false) {
        $classified = $this->getImageRecord($classified);
        if(isset($classified['image_id'])) {
            if(!empty($classified['image_extension'])) {
                $image = get_file_url_cdn(CLASSIFIEDS_THUMBNAILS_PATH.$classified['id'].'-'.$classified['image_id'].'.'.$classified['image_extension']);
            } else {
                $image = get_file_url_cdn(CLASSIFIEDS_THUMBNAILS_PATH.$classified['id'].'-'.$classified['image_id'].'.*');
            }
        }
        if(!$image AND $noimage) {
            $image = get_file_url_cdn($this->PMDR->get('Templates')->path('images/noimage.png'));
        }
        return $image;
    }

    /**
    * Get an image record from the database if we do not have an image in the classifieds array
    * @param array Classified
    * @return array
    */
    function getImageRecord($classified) {
        if(!isset($classified['image_id'])) {
            if($image_record = $this->db->GetRow("SELECT id AS image_id, extension AS image_extension FROM ".T_CLASSIFIEDS_IMAGES." WHERE classified_id=?",array($classified['id']))) {
                $classified = array_merge($classified,$image_record);
            }
        }
        return $classified;
    }

    /**
    * Get All images for a classified
    * @param $id Classified ID
    * @return array Images array
    */
    function getImages($id) {
        if($classified_images = $this->db->GetAll("SELECT id AS image_id, classified_id AS id, extension AS image_extension FROM ".T_CLASSIFIEDS_IMAGES." WHERE classified_id=?",array($id))){
            foreach($classified_images AS $key=>$image) {
                $classified_images[$key]['image_url'] = $this->PMDR->get('Classifieds')->getImageURL($image);
                $classified_images[$key]['thumbnail_url'] = $this->PMDR->get('Classifieds')->getImageThumbnailURL($image);
            }
        }
        return $classified_images;
    }

    /**
    * Update classified categories
    * @param int $id
    * @param array $categories
    * @param int $primary_category
    */
    function updateCategories($id, $categories, $primary_category) {
        if($categories == '') $categories = array();

        if(!is_array($categories)) {
            $categories = array($categories);
        }
        if(!in_array($primary_category,$categories)) {
            $categories[] = $primary_category;
        }
        $categories = array_filter(array_unique($categories));
        if(!count($categories)) {
            return false;
        }
        $value_string = '';
        foreach($categories as $category) {
            $value_string .= '('.$id.','.$category.'),';
        }
        $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS_CATEGORIES_LOOKUP." WHERE classified_id=?",array($id));
        $this->db->Execute("INSERT INTO ".T_CLASSIFIEDS_CATEGORIES_LOOKUP." (classified_id,category_id) VALUES ".trim($value_string,','));
    }

    /**
    * Generate pdf version of classified
    * @param object $data Classified object
    * @return string PDF data string
    */
    function generatePDF($data) {
        /**
        * @var $pdf TCPDF
        */
        $pdf = $this->PMDR->get('TCPDF');
        $pdf->setHtmlVSpace($tagvs);
        $pdf->setJPEGQuality(100);
        $pdf->SetCreator(BASE_URL);
        $pdf->SetAuthor(BASE_URL);
        $pdf->SetTitle($data['title']);
        $pdf->SetSubject($data['description']);
        $pdf->SetKeywords($data['keywords']);
        $pdf->SetPrintHeader(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array("dejavusans", '', 12));
        $pdf->AddPage();
        if ($this->PMDR->getLanguage('textdirection') == 'rtl') {
            $pdf->setRTL(true);
        }
        $title = $data['title'];
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->writeHTML('<h1>'.$title.'</h1>');
        if($data['listing_title']) {
            $pdf->writeHTML('<p>'.$this->PMDR->getLanguage('public_classified_from').': '.$data['listing_title'].'</p>');
        }
        if($data['price']) {
            $pdf->writeHTML('<br><p><strong>'.$this->PMDR->getLanguage('public_classified_price').'</strong>: '.format_number_currency($data['price']).'</p>');
        }
        $pdf->writeHTML('<p><strong>'.$this->PMDR->getLanguage('public_classified_date').'</strong>: '.$data['date'].'</p>');
        if($data['expire_date']) {
            $pdf->writeHTML('<p><strong>'.$this->PMDR->getLanguage('public_classified_expire_date').'</strong>: '.$data['expire_date'].'</p>');
        }
        if($data['description']) {
            $pdf->writeHTML('<br><h2>'.$this->PMDR->getLanguage('public_classified_description').'</h2>');
            $pdf->writeHTML($data['description']);
        }
        if($fields = $this->PMDR->get('Fields')->getFields('classifields')) {
            $field_html = '<br>';
            foreach($fields as $key=>$field) {
                if($data['custom_'.$field['id'].'_allow'] AND $data['custom_'.$field['id']] != '' AND !$field['hidden']) {
                    $field_html .= $field['name'].': '.str_replace("\n",', ',$data['custom_'.$field['id']]).'<br />';
                }
            }
            $pdf->writeHTML($field_html);
        }
        if($data['classifieds_images_allow']) {
            $classified_images = $this->getImages($data['id']);
            foreach($classified_images AS $image) {
                $image_file = CLASSIFIEDS_THUMBNAILS_PATH.$data['id'].'-'.$image['image_id'].'.'.$image['image_extension'];
                if(file_exists($image_file)) {
                    if($image_details = getimagesize($image_file)) {
                        $width = $pdf->pixelsToUnits($image_details[0]);
                        if ($pdf->pixelsToUnits($image_details[0]) > 0.6 * $pdf->getPageWidth()) {
                            $width = 0.6 * $pdf->getPageWidth();
                        }
                    }
                    $pdf->Image($image_file, null, null, $width, '', '', BASE_URL, 'N', false, '72', 'L');
                }
            }
        }

        $pdf->writeHTML('<br><p>'.$data['url'].'</p>');

        $pdf->Output($data['friendly_url'] . '.pdf', 'D');
        exit();
    }

    function getFeatured($limit = 3, $category_sql = '', $location_sql = '') {
        $featured_classifieds = $this->db->GetAll("
            SELECT
                c.*,
                l.classifieds_images_allow,
                ci.id AS image_id,
                ci.extension AS image_extension
            FROM ".T_CLASSIFIEDS." c
            INNER JOIN ".T_LISTINGS." l ON c.listing_id=l.id
            LEFT JOIN (SELECT classified_id, MIN(id) id FROM ".T_CLASSIFIEDS_IMAGES." GROUP BY classified_id) cii ON cii.classified_id=c.id
            LEFT JOIN ".T_CLASSIFIEDS_IMAGES." ci on ci.id=cii.id
            WHERE
                l.status='active' AND
                (c.expire_date > NOW() OR
                c.expire_date IS NULL)
                $category_sql
                $location_sql
            ORDER by RAND('".session_id()."')
            LIMIT ?",array(intval($limit))
        );

        foreach($featured_classifieds as $key=>$classified) {
            if($classified['classifieds_images_allow']) {
                $featured_classifieds[$key]['thumb'] = $this->getImageThumbnailURL($classified);
                $featured_classifieds[$key]['image'] = $this->getImageURL($classified);
            }
            $featured_classifieds[$key]['description'] = Strings::limit_words($classified['description'],$this->PMDR->getConfig('block_description_size'));
            $featured_classifieds[$key]['link'] = $this->getURL($classified['id'],$classified['friendly_url']);
        }

        return $featured_classifieds;
    }
}
?>