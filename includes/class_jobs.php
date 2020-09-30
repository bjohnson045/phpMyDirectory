<?php
/**
* Jobs Class
*/
class Jobs extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database Database object
    */
    var $db;

    /**
    * Events constructor
    * @param object Registry
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_JOBS;
    }

    /**
    * Get an Job by ID
    * @param int $id Job ID
    * @return array
    */
    function getRow($id) {
        $record = $this->db->GetRow("SELECT * FROM ".T_JOBS." WHERE id=?",array($id));
        $record['categories'] = $this->db->GetCol("SELECT category_id FROM ".T_JOBS_CATEGORIES_LOOKUP." WHERE job_id=?",array($id));
        return $record;
    }

    /**
    * Get job URL
    * @param int $id
    * @param string $friendly_url
    * @param string $query_string
    * @param string $query_string_rewrite
    * @param string $filename
    * @return string
    */
    function getURL($id, $friendly_url, $query_string='', $query_string_rewrite='.html', $filename='job.php') {
        if(MOD_REWRITE) {
            return BASE_URL_NOSSL.'/job/'.$friendly_url.'-'.$id.$query_string_rewrite;
        } else {
            return BASE_URL_NOSSL.'/'.$filename.'?id='.$id.$query_string;
        }
    }

    /**
    * Insert job
    * @param array $data
    * @return void
    */
    function insert($data) {
        $data['description'] = Strings::limit_characters($data['description'],$this->PMDR->getConfig('jobs_description_size'));
        $data['website'] = standardize_url($data['website']);
        if(!isset($data['date'])) {
            $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        }
        $id = parent::insert($data);
        $this->updateCategories($id,$data['categories']);
        return $id;
    }

    /**
    * Update job
    * @param array $data
    * @param int $id
    * @return void
    */
    function update($data, $id) {
        $data['url'] = standardize_url($data['url']);
        $data['date_update'] = $this->PMDR->get('Dates')->dateTimeNow();
        parent::update($data, $id);
        $this->updateCategories($id,$data['categories']);
    }

    /**
    * Update job categories
    * @param int $id Job ID
    * @param array $categories Categories
    * @return void
    */
    function updateCategories($id, $categories) {
        if($categories == '') $categories = array();

        if(!is_array($categories)) {
            $categories = array($categories);
        }

        $categories = array_filter(array_unique($categories));
        if(!count($categories)) {
            return false;
        }
        foreach($categories as $category) {
            $value_string .= '('.$id.','.$category.'),';
        }
        $this->db->Execute("DELETE FROM ".T_JOBS_CATEGORIES_LOOKUP." WHERE job_id=?",array($id));
        $this->db->Execute("INSERT INTO ".T_JOBS_CATEGORIES_LOOKUP." (job_id,category_id) VALUES ".trim($value_string,','));
    }

    /**
    * Delete job
    * @param int $id
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_JOBS_CATEGORIES_LOOKUP." WHERE job_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_JOBS." WHERE id=?",array($id));
    }

    /**
    * Delete job category
    * @param int $id Category ID
    * @return void
    */
    function deleteCategory($id) {
        $this->db->Execute("DELETE FROM ".T_JOBS_CATEGORIES_LOOKUP." WHERE category_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_JOBS_CATEGORIES." WHERE id=?",array($id));
        return true;
    }

    /**
    * Insert a job category
    *
    * @param mixed $data
    * @return resource
    */
    function insertCategory($data) {
        return $this->db->Execute("INSERT INTO ".T_JOBS_CATEGORIES." (title,friendly_url,keywords,meta_title,meta_keywords,meta_description) VALUES (?,?,?,?,?,?)",array($data['title'],$data['friendly_url'],$data['keywords'],$data['meta_title'],$data['meta_keywords'],$data['meta_description']));
    }

    /**
    * Update a job category
    *
    * @param array $data
    * @param int $id
    * @return boolean
    */
    function updateCategory($data,$id) {
        return $this->db->Execute("UPDATE ".T_JOBS_CATEGORIES." SET title=?, friendly_url=?,keywords=?,meta_title=?,meta_keywords=?,meta_description=? WHERE id=?",array($data['title'],$data['friendly_url'],$data['keywords'],$data['meta_title'],$data['meta_keywords'],$data['meta_description'],$id));
    }

    /**
    * Get the total number of jobs belonging to a listing
    *
    * @param int $listing_id
    * @return int Count of jobs belonging to listing id
    */
    function getListingJobsCount($listing_id) {
        return $this->db->GetOne("SELECT COUNT(*) AS count FROM ".T_JOBS." WHERE listing_id=?",array($listing_id));
    }
}
?>