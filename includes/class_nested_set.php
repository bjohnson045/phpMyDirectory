<?php
/**
 * Nested Set Class
 * Uses Nested Set Structure with added level field in the database for easy retrieval
 */
class Nested_Set {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Database
    * @var object
    */
    var $db;

    /**
    * Nested set constructor
    * @param object $PMDR
    * @return Nested_Set
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /*
    * Shift nested set container values
    * Adds $delta to all left_ and right_ values that are >= $first.  $delta can be negative.
    * @param int $first
    * @param int $delta
    */
    function shiftValues($first, $delta) {
        $this->db->Execute("UPDATE ".$this->table." SET left_=left_+$delta WHERE left_>=$first");
        $this->db->Execute("UPDATE ".$this->table." SET right_=right_+$delta WHERE right_>=$first");
    }

    /**
    * Shift a range of nested set container values
    * Adds $delta to all left_and right_ values that are >= $first and <= $last.  $delta can be negative.
    * @param int $first
    * @param int $last
    * @param int $delta
    * @return mixed Shifted first/last values of node array.
    */
    function shiftRange($first, $last, $delta) {
        $this->db->Execute("UPDATE ".$this->table." SET left_=left_+$delta WHERE left_>=$first AND left_<=$last");
        $this->db->Execute("UPDATE ".$this->table." SET right_=right_+$delta WHERE right_>=$first AND right_<=$last");
        return array('left_'=>$first+$delta, 'right_'=>$last+$delta);
    }

    /**
    * Update parent_id
    * @param int $node_id
    * @param int $parent_id
    */
    function updateParentID($node_id, $parent_id) {
        $this->db->Execute("UPDATE ".$this->table." SET parent_id=? WHERE id=?",array($parent_id,$node_id));
    }

    /**
    * Inserts a new first child node given a parent node
    * @param mixed $node Parent node
    * @param mixed $extended_data
    */
    function newFirstChild($node) {
        $data = array();
        if(!is_array($node)) {
            $node = $this->getNode($node);
        }
        if(!$node) {
            return false;
        }
        $data['parent_id'] = $node['id'];
        $data['left_'] = $node['left_']+1;
        $data['right_'] = $node['left_']+2;
        $data['level'] = $node['level']+1;
        $this->shiftValues($data['left_'], 2);
        return $data;
    }

    /**
    * Inserts a new last child node given a parent node
    * @param mixed $node Parent node
    * @param mixed $extended_data
    */
    function newLastChild($node) {
        $data = array();
        if(!is_array($node)) {
            $node = $this->getNode($node);
        }
        if(!$node) {
            return false;
        }
        $data['parent_id'] = $node['id'];
        $data['left_'] = $node['right_'];
        $data['right_'] = $node['right_']+1;
        $data['level'] = $node['level']+1;
        $this->shiftValues($data['left_'], 2);
        return $data;
    }

    /**
    * Inserts a previous sibling node given a sibling node
    * @param mixed $node Parent node
    * @param mixed $extended_data
    */
    function newPreviousSibling($node){
        $data = array();
        if(!is_array($node)) {
            $node = $this->getNode($node);
        }
        if(!$node) {
            return false;
        }
        $data['parent_id'] = $node['parent_id'];
        $data['left_'] = $node['left_'];
        $data['right_'] = $node['left_']+1;
        $data['level'] = $node['level'];
        $this->shiftValues($data['left_'], 2);
        return $data;
    }

    /**
    * Inserts a next sibling node given a sibling node
    * @param mixed $node Parent node
    * @param mixed $extended_data
    */
    function newNextSibling($node) {
        $data = array();
        if(!is_array($node)) {
          $node = $this->getNode($node);
        }
        if(!$node) {
            return false;
        }
        $data['parent_id'] = $node['parent_id'];
        $data['left_'] = $node['right_']+1;
        $data['right_'] = $node['right_']+2;
        $data['level'] = $node['level'];
        $this->shiftValues($data['left_'], 2);
        return $data;
    }

    /**
    * Move a node and all children to the next sibling of a destination
    * @param mixed $src
    * @param mixed $dst
    * @return mixed
    */
    function moveToNextSibling($src, $dst) {
        if(!is_array($dst)) {
            $dst = $this->getNode($dst);
        }
        if(!is_array($src)) {
            $src = $this->getNode($src);
        }
        if(!$dst OR !$src) {
            return false;
        }
        $this->updateParentID($src['id'],$dst['parent_id']);
        return $this->moveSubtree($src, $dst['right_']+1, $dst['level']);
    }

    /**
    * Move a node and all children to the previous sibling of a destination
    * @param mixed $src
    * @param mixed $dst
    * @return mixed
    */
    function moveToPreviousSibling($src, $dst) {
        if(!is_array($dst)) {
            $dst = $this->getNode($dst);
        }
        if(!is_array($src)) {
            $src = $this->getNode($src);
        }
        if(!$dst OR !$src) {
            return false;
        }
        $this->updateParentID($src['id'],$dst['parent_id']);
        return $this->moveSubtree($src, $dst['left_'], $dst['level']);
    }

    /**
    * Move a node and all children to the first child of a destination
    * @param mixed $src
    * @param mixed $dst
    * @return mixed
    */
    function moveToFirstChild($src, $dst) {
        if(!is_array($dst)) {
            $dst = $this->getNode($dst);
        }
        if(!is_array($src)) {
            $src = $this->getNode($src);
        }
        if(!$dst OR !$src) {
            return false;
        }
        $this->updateParentID($src['id'],$dst['id']);
        return $this->moveSubtree($src, $dst['left_']+1, $dst['level']+1);
    }

    /**
    * Move a node and all children to the last child of a destination
    * @param mixed $src
    * @param mixed $dst
    * @return mixed
    */
    function moveToLastChild($src, $dst) {
        if(!is_array($dst)) {
            $dst = $this->getNode($dst);
        }
        if(!is_array($src)) {
            $src = $this->getNode($src);
        }
        if(!$dst OR !$src) {
            return false;
        }
        $this->updateParentID($src['id'],$dst['id']);
        return $this->moveSubtree($src, $dst['right_'], $dst['level']+1);
    }

    /**
    * Mode a subtree
    * @param array $src Source node
    * @param mixed $to left_ value of the destination
    * @param mixed $dst_level Destination level
    * @return mixed
    */
    function moveSubtree($src, $to, $dst_level) {
        $treesize = $src['right_']-$src['left_']+1;
        $this->shiftValues($to, $treesize);

        // Was src shifted also?
        if($src['left_'] >= $to) {
            $src['left_'] += $treesize;
            $src['right_'] += $treesize;
        }
        // Now there's enough room next to target to move the subtree
        $newpos = $this->shiftRange($src['left_'], $src['right_'], $to-$src['left_']);
        // Correct values after source
        $this->shiftValues($src['right_']+1, -$treesize);
        // Was dst shifted also?
        if($src['left_'] <= $to){
            $newpos['left_'] -= $treesize;
            $newpos['right_'] -= $treesize;
        }
        $level_difference = $dst_level - $src['level'];

        $this->db->Execute("UPDATE ".$this->table." SET level=level+$level_difference WHERE left_>=".$newpos['left_']." AND right_<=".$newpos['right_']);
        return $newpos;
    }

    /**
    * Delete a node including its children
    * @param mixed $node
    */
    function delete($node) {
        if(!is_array($node)) {
            $node = $this->getNode($node);
        }
        if($node) {
            $leftanchor = $node['left_'];
            $res = $this->db->Execute("DELETE FROM ".$this->table." WHERE left_>=".$node['left_']." AND right_<=".$node['right_']);
            $this->shiftValues($node['right_']+1, $node['left_'] - $node['right_'] -1);
            return $this->getNodeWhere("left_<".$leftanchor." ORDER BY left_ DESC");
        } else {
            return false;
        }
    }

    /**
    * Delete the entire tree
    * @return boolean
    */
    function deleteTree() {
        return $this->db->Execute("DELETE FROM ".$this->table." WHERE id != 1");
    }

    /**
    * Get a node
    * @param int $id
    * @param string $where
    * @return array
    */
    function getNode($id, $where= '') {
        if($where != '') $where = ' AND '.$where;
        return $this->db->GetRow("SELECT * FROM ".$this->table." WHERE id=?".$where,array($id));
    }

    /**
    * Get a node based on certain conditions
    * @param string $where
    */
    function getNodeWhere($where) {
        $node_result['left_'] = 0;
        $node_result['right_'] = 0;
        $row = $this->db->GetRow("SELECT * FROM ".$this->table." WHERE ".$where);
        if($row) {
            $node_result = $row;
        }
        return $node_result;
    }

    /**
    * Determine if a node is a leaf
    * @param mixed $node
    * @return boolean
    */
    function isLeaf($node) {
        if(!is_array($node)) {
            $node = $this->getNode($node);
        }
        return (($node['right_']-$node['left_'])==1);
    }

    /**
    * Get all leaf nodes
    * @return array
    */
    function getLeafNodes($node_id = null) {
        if(!is_null($node_id)) {
            $row = $this->getNode($node_id);
            if(!$row) return array();
            $result = $this->db->GetAll("SELECT id, title FROM ".$this->table." WHERE left_=(right_ - 1) AND left_ BETWEEN '".($row["left_"]+1)."' AND '".$row["right_"]."' ORDER BY left_");
        } else {
            $result = $this->db->GetAll("SELECT id, title FROM ".$this->table." WHERE left_=(right_ - 1) ORDER BY left_");
        }

        return $result;
    }

    /**
    * Get children nodes
    * @param int $node_id
    * @param int $levels
    * @param string $where
    */
    function getChildren($node_id, $levels=1, $where='') {
        if($levels == 1) {
            $records = $this->db->GetAll("SELECT * FROM ".$this->table." WHERE parent=? ORDER BY left_ ASC",array($node_id));
        } else {
            $row = $this->getNode($node_id);
            if (!$row) return array();
            $records = $this->db->GetAll("SELECT * FROM ".$this->table." WHERE left_ BETWEEN '".($row["left_"]+1)."' AND '".$row["right_"]."'".(($levels > 0) ? "AND level BETWEEN ".($row['level']+1)." AND ".($row['level']+intval($levels)) : "")." $where ORDER BY left_");
        }
        return $records;
    }

    /**
    * Get all top level root nodes
    * @return array
    */
    function getRoots() {
        return $this->db->GetAll("SELECT * FROM ".$this->table." AS node INNER JOIN ".$this->table." AS parent ON node.left_ BETWEEN parent.left_ AND parent.right_ GROUP BY node.id HAVING level=1 ORDER BY node.left_");
    }

    /**
    * Get the path to a node
    * @param int $node_id
    * @return array
    */
    function getPath($node_id)    {
        $result = $this->db->GetAll("SELECT * FROM ".$this->table." AS node INNER JOIN ".$this->table." AS parent ON node.left_ BETWEEN parent.left_ AND parent.right_ WHERE node.id = ? ORDER BY parent.left_;",array($node_id));
        array_shift($result); // get rid of ROOT
        return $result;
    }

    /**
    * Get parent IDs of a node
    * The nodes are returned in order down the tree
    * @param int $node_id
    * @return array
    */
    function getParentIDArray($node_id) {
        if(empty($node_id)) return array();
        $result = array_unique($this->db->GetCol("SELECT parent.id FROM ".$this->table." AS node INNER JOIN ".$this->table." AS parent ON node.left_ > parent.left_ AND node.left_ < parent.right_ WHERE node.id IN (".implode(',',(array) $node_id).") ORDER BY parent.left_;"));
        array_shift($result); // get rid of ROOT
        return $result;
    }

    /**
    * Migrate and adjacency list to a nested set by looping over the list and adding children as needed.
    * @param mixed $adjacencyCat
    */
    function migrateAdjacencyListToNested($adjacency_list) {
        if($adjacency_list) {
            $this->addRoot($adjacency_list[0]["category_id"],$adjacency_list[0]["title"]);
            if(is_array($adjacency_list)) {
                for($i=1; $i < sizeof($adjacency_list); $i++) {
                    $this->addChild($adjacency_list[$i]["p"],$adjacency_list[$i]["category_id"],$adjacency_list[$i]["title"]);
                }
            }
        }
    }

    /**
    * Output a tree with appropriate indentation
    */
    function printTree() {
        $rows = $this->db->GetAll("SELECT id, title, level FROM ".$this->table." ORDER BY left_");
        foreach($rows as $row) {
            for($i=0; $i < $row['level']; $i++) {
                echo '----';
            }
            echo '('.$row['id'].') '.$row['title'].'<br />';
        }
    }

    /**
    * Get a tree
    * @param mixed $fields
    * @param mixed $where
    */
    function getTree($fields=array('*'), $where = array()) {
        $query = "SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE level>=1";
        if(count($where) > 0) {
            foreach($where as $field=>$value) {
                $query .= " AND".$field."=".$this->db->Clean($value);
            }
        }
        return  $this->db->GetAll($query." ORDER BY left_");
    }

    /**
    * Rebuild all level values
    */
    function updateLevels() {
        $rows = $this->getLevels();

        foreach($rows as $row) {
            $this->db->Execute("UPDATE ".$this->table." SET level=? WHERE id=?",array($row['depth'],$row['id']));
        }
    }

    /**
    * Get the lowest depth
    * @return int Depth level
    */
    function getDepth() {
        return $this->db->GetOne("SELECT MAX(level) AS level FROM ".$this->table);
    }

    /**
    * Get the levels for all nodes
    * @return array Results
    */
    function getLevels() {
        return $this->db->GetAll("SELECT node.id, (COUNT(parent.id) - 1) AS depth
        FROM ".$this->table." AS node INNER JOIN ".$this->table." AS parent
        ON node.left_ BETWEEN parent.left_ AND parent.right_
        GROUP BY node.id
        ORDER BY node.left_ ASC");
    }

    /**
    * Get the size of the tree or if a node ID is supplied a subtree
    * @param int $node_id ID to get size of
    * @return int Size of tree/subtree
    */
    function getSize($node_id = null) {
        return $this->db->GetOne("SELECT (right_-left_+1) DIV 2 FROM ".$this->table." WHERE ".($node_id!==null ? "id=".intval($node_id) : "left_=1"));
    }
}
?>