<?php
/**
 * Category Tree Class
 * Uses Nested Set Structure with added level field in the database for easy retrieval
 */
class Tree_Gateway extends Nested_Set {
    /**
    * Get the total count (including sub nodes) of a node
    * @param int $id
    * @return int
    */
    function getCount() {
        return intval($this->db->GetOne("SELECT COUNT(*) FROM ".$this->table)) - 1;
    }

    /**
    * Update the friendly URL path of a node
    * @param int $id
    * @return string New path
    */
    function updateFriendlyPath($id) {
        // Get the path for the current ID
        $node = $this->db->GetRow("SELECT node.left_, node.right_, node.friendly_url_path as old_path, node.friendly_url_path_hash as old_path_hash,
                GROUP_CONCAT(parent.friendly_url ORDER BY parent.left_ SEPARATOR '/') as path
                FROM ".$this->table." AS node INNER JOIN ".$this->table." AS parent
                ON node.left_ BETWEEN parent.left_ AND parent.right_
                WHERE node.id = ?
                GROUP BY node.friendly_url
                ORDER BY parent.left_",array($id));

        // Strip off root and add a slash if necesarry
        $new_path = (strstr($node['path'],'/') ? substr(strstr($node['path'],'/'),1) : $node['path']).'/';

        if(md5($new_path) != $node['old_path_hash']) {
            if(!empty($node['old_path'])) {
                $this->db->Execute("REPLACE INTO ".T_REDIRECTS." (type,type_id,url_hash,url,date_redirected) SELECT '".$this->type."', id, friendly_url_path_hash, REPLACE(friendly_url_path,'".$node['old_path']."','".$new_path."'), NOW() FROM ".$this->table ." WHERE left_ >= ".$node['left_']." AND right_ <= ".$node['right_']);
            }
            // Update the node we just edited
            $this->db->Execute("UPDATE ".$this->table." SET friendly_url_path=?, friendly_url_path_hash=? WHERE id=?",array($new_path,md5($new_path),$id));

            // Update all children of this node
            $this->db->Execute("UPDATE ".$this->table." SET friendly_url_path=REPLACE(friendly_url_path,'".$node['old_path']."','".$new_path."') WHERE left_ > ".$node['left_']." AND right_ < ".$node['right_']);

            // This is done in two queries to prevent bad hash values from being created
            $this->db->Execute("UPDATE ".$this->table." SET friendly_url_path_hash=MD5(friendly_url_path) WHERE left_ > ".$node['left_']." AND right_ < ".$node['right_']);

        }
        return $new_path;
    }

    /**
    * Get all children of a node
    * @param int $id Parent ID to get children of
    * @param int $levels The number of levels of subnodes to retreive (depth)
    * @param string $where Option WHERE SQL to add to the query
    * @param array $fields Database fields to retreive
    * @return array Results
    */
    function getChildren($id, $levels=1, $child_limit=null, $where='', $fields=array('id','level', 'title', 'left_', 'right_', 'count', 'count_total', 'friendly_url', 'friendly_url_path', 'link', 'impressions', 'description_short', 'hidden', 'no_follow', 'display_columns', 'closed', 'small_image_url')) {
        $row = $this->getNode($id);
        if(!$row) return array();
        if($levels != 2) {
            $child_limit = NULL;
        }
        $query = "SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE left_ BETWEEN '".($row["left_"]+1)."' AND '".$row["right_"]."'";
        if($levels > 0) {
            if(!is_null($child_limit)) {
                $query .= " AND (level=".($row['level']+1)." OR (level=".($row['level']+$levels)." AND child_row_id <= ".($child_limit+1)."))";
            } else {
                $query .= " AND level BETWEEN ".($row['level']+1)." AND ".($row['level']+$levels);
            }
        }
        $query .= " $where ORDER BY left_";
        return $this->db->GetAll($query);
    }

    /**
    * Get a node by name filtered by the parent id
    * @param string $title Node title
    * @param int $parent_id Parent ID to look for the node title under
    * @param int $level The level to look in
    * @return int|false Node ID or false if not found
    */
    function getNodeByNameAndParent($title, $parent_id, $level=1) {
        $parent = $this->getNode($parent_id);

        if (!$parent) {
            return false;
        } else  {
            $category = $this->db->GetRow("SELECT * FROM ".$this->table." WHERE title=? AND left_>? AND right_<? AND level=? ORDER BY left_ DESC",array($title,$parent['left_'],$parent['right_'],$level));
            return ($category) ? $category['id'] : false;
        }
    }

    /**
    * Get all root nodes
    * @return array Results
    */
    function getRoots() {
        return $this->db->GetAll("SELECT id, title, friendly_url, friendly_url_path, level AS depth, count, count_total, hidden, link, no_follow FROM ".$this->table." WHERE level = 1 ORDER BY left_");
    }

    /**
    * Reset child row IDs.
    * Numbers and stores the row IDs within each subcategory group
    */
    function resetChildRowIDs() {
        $this->db->Execute("UPDATE ".$this->table." a, (SELECT IF(@previous_parent_id = parent_id, @row := @row +1, @row :=1) AS ROW,
        @previous_parent_id := parent_id, parent_id, id
        FROM ".$this->table." JOIN (SELECT @row :=0, @previous_parent_id :=0) i ORDER BY parent_id, left_) aa
        SET a.child_row_id = aa.row WHERE a.parent_id = aa.parent_id AND a.id = aa.id");
    }

    /**
    * Get a comma separated list of titles of each level for a node (the path)
    * @param int $id Node ID
    * @return string Path
    */
    function getPathString($id) {
        $result = $this->db->GetCol("
            SELECT parent.title
            FROM ".$this->table." AS node INNER JOIN ".$this->table." AS parent
            ON node.left_ BETWEEN parent.left_ AND parent.right_
            WHERE node.id=?
            ORDER BY parent.left_;",array($id));
        array_shift($result); // get rid of ROOT
        return implode(',',array_reverse($result));
    }

    /**
    * Get the full tree
    * @param array $fields Database fields to retreive
    * @param string $where Additional WHERE SQL to add to the query
    * @return array Results
    */
    function getTree($fields=array('id','title','friendly_url','friendly_url_path','description','level','left_','right_','count','count_total','hidden'), $where = array()) {
        $query = "SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE level>=1";
        if(count($where) > 0) {
            foreach($where as $field=>$value) {
                $query .= " AND".$field."=".$this->db->Clean($value);
            }
        }
        return  $this->db->GetAll($query." ORDER BY left_");
    }

    /**
    * Get a node ID by the friendly URL
    * @param string $url Friendly URL
    * @return int Node ID
    */
    function getIDByURL($url) {
        if(!MOD_REWRITE) {
            return $url;
        } else {
            if(substr($url,-1) != '/') {
                $url .= '/';
            }
            return $this->db->GetOne("SELECT id FROM ".$this->table." WHERE friendly_url_path_hash=?",array(md5($url)));
        }
    }

    /**
    * Convert a path array into readable format
    * @param array $path_array Path array consiting of node titles
    * @param string $separator Separator in between titles
    * @param boolean $linked  Hyperlink the titles
    * @param string $style CSS class to add to the links
    * @return string Formatted path
    */
    function getPathDisplay($path_array, $separator=' | ', $linked=true, $style='', $target = null) {
        if(!is_array($path_array) OR empty($path_array)) {
            return '';
        }
        foreach($path_array as $value) {
            if($linked) {
                $path .= '<a ';
                if(!is_null($target)) {
                    $path .= 'target="'.$target.'" ';
                }
                if($value['no_follow']) {
                    $path .= 'rel="noindex,nofollow" ';
                }
                if($style != '') {
                    $path .= 'class="'.$style.'" ';
                }
                $url = $value['link'] != '' ? $value['link'] : $this->getURL($value['id'], $value['friendly_url_path']);
                $path .= 'href="'.$url.'">'.$value['title'].'</a> '.$separator.' ';
            } else {
                $path .= $value['title'];
                if(!empty($separator)) {
                    $path .= $separator;
                }
            }
        }
        return trim($path, ' '.$separator);
    }

    /**
    * Check if a Node ID is a child of another Node ID
    * @param int $id Node ID to check
    * @param int $id2 Parent node ID to check under
    * @return boolean True or false if $id is a child of $id2
    */
    function isAChildOf($id, $id2) {
        if($nodes = $this->db->GetAssoc("SELECT id, left_, right_ FROM ".T_CATEGORIES." WHERE id=? OR id=?",array($id,$id2))) {
            if(count($nodes) != 2) {
                return false;
            }
            if($nodes[$id]['left_'] > $nodes[$id2]['left_'] AND $nodes[$id]['right_'] < $nodes[$id2]['right_']) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>