<?php
/**
 * Class UsersGroups
 * Handles user groups
 */
class UsersGroups extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * UsersGroups Constructors
    * @param object $PMDR Registry
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_USERS_GROUPS;
    }

    /**
    * Insert a user group
    *
    * @param array $data
    * @return int Group ID
    */
    function insert($data) {
        $id = parent::insert($data);
        $this->updatePermissions($data,$id);
        return $id;
    }

    /**
    * Update a user group
    *
    * @param array $data
    * @param int $id
    * @return void
    */
    function update($data,$id) {
        $this->updatePermissions($data,$id);
        parent::update($data,$id);
    }

    /**
    * Update group permissions
    *
    * @param array $data
    * @param int $id Group ID
    */
    function updatePermissions($data,$id) {
        $this->db->Execute("DELETE FROM ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." WHERE group_id=?",array($id));
        $permissions = array();
        if($data['administrator']) {
            $permissions[] = 'admin_administrator';
        }
        if($data['advertiser']) {
            $permissions[] = 'user_advertiser';
        }
        if($data['user']) {
            $permissions[] = 'user_user';
        }
        if(isset($data['administrator_permissions']) AND !empty($data['administrator_permissions'])) {
            $permissions = array_merge($permissions,$data['administrator_permissions']);
        }
        if(isset($data['user_permissions']) AND !empty($data['user_permissions'])) {
            $permissions += array_merge($permissions,$data['user_permissions']);
        }
        foreach($permissions as $permission_id) {
            $this->db->Execute("INSERT INTO ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." (group_id,permission_id) VALUES (?,?)",array($id,$permission_id));
        }
    }

    /**
    * Delete User Group
    * @param integer $id Group ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." WHERE group_id=?",array($id));
        parent::delete($id);
    }

    /**
    * Check if user group has users
    * @param integer $id Group ID
    * @return integer User count
    */
    function hasUsers($id) {
        return $this->db->GetOne("SELECT COUNT(*) as count FROM ".T_USERS_GROUPS_LOOKUP." WHERE group_id=?",array($id));
    }
}
?>