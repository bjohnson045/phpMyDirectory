<?php
/**
 * Class Users
 */
class Users extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
     * Class Users Constructor
     * @param object $PMDR Registry
     * @return void
     */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_USERS;
    }

    /**
    * Get a user record from the database
    * @param int $id User ID
    * @return array User details
    */
    function getRow($id) {
        if(!$user = parent::getRow($id)) {
            return false;
        }
        if($user_login_provider = $this->db->GetRow("SELECT login_id, login_provider FROM ".T_USERS_LOGIN_PROVIDERS." WHERE user_id=?",array($user['id']))) {
            $user = array_merge($user, $user_login_provider);
        }
        return $user;
    }

    /**
    * Front end users search
    *
    * @param string $keyword
    * @param int $limit1
    * @param int $limit2
    * @return array
    */
    function search($keyword = null, $limit1, $limit2) {
        $user_display_name_sql = $this->getDisplayNameSQL();
        $query = "SELECT SQL_CALC_FOUND_ROWS *, $user_display_name_sql FROM ".T_USERS;
        if(!is_null($keyword) AND !empty($keyword)) {
            $query .= " WHERE MATCH(user_first_name,user_last_name,user_organization) AGAINST (".$this->PMDR->get('Cleaner')->clean_db($keyword).")";
        }
        $query .= " ORDER BY user_last_name, user_first_name LIMIT ".$limit1.", ".$limit2;
        return $this->db->GetAll($query);
    }

    /**
    * Get user display name SQL
    * @return string
    */
    function getDisplayNameSQL() {
        if(!$this->PMDR->getConfig('user_display_name')) {
            switch($this->PMDR->getConfig('user_display_name_format')) {
                case 'first_last':
                    $user_display_name_sql = "CONCAT(user_first_name,' ',user_last_name)";
                    break;
                case 'first_initial_last':
                    $user_display_name_sql = "CONCAT(IF(user_first_name='','',CONCAT(LEFT(user_first_name, 1),'. ')),user_last_name)";
                    break;
                case 'first_last_initial':
                    $user_display_name_sql = "CONCAT(user_first_name,' ',IF(user_last_name='','',CONCAT(LEFT(user_last_name, 1),'.')))";
                    break;
                case 'username':
                    $user_display_name_sql = 'login';
                    break;
            }
            $user_display_name_sql .= ' AS display_name';
        } else {
            $user_display_name_sql = 'display_name';
        }
        return $user_display_name_sql;
    }

    /**
    * Find user by email, taking into account case insensitive
    * @param string $email User email address
    * @return array User array
    */
    function findByEmail($email) {
        return $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE LOWER(".T_USERS.".user_email)=?",array(strtolower(trim($email))));
    }

    /**
    * Insert User
    * @param array $user_array User Array
    * @return integer User ID
    */
    function insert($data) {
        if(!isset($data['cookie_salt'])) {
            $data['cookie_salt'] = $this->PMDR->get('Authentication')->generateSalt();
        }
        if(!isset($data['password_salt'])) {
            $data['password_salt'] = $this->PMDR->get('Authentication')->generateSalt();
        }
        $data['pass'] = $this->PMDR->get('Authentication')->encryptPassword($data['pass'],$data['password_salt']);

        $data['created'] = $this->PMDR->get('Dates')->dateTimeNow();
        $data['date_updated'] = $this->PMDR->get('Dates')->dateTimeNow();

        $data['password_hash'] = $this->PMDR->get('Authentication')->encryption;

        // The comment must be set since its a text field which can not have a default value.
        if(!isset($data['user_comment'])) {
            $data['user_comment'] = '';
        }

        $fields = $this->PMDR->get('Fields')->getFields('users');
        if(is_array($fields) AND sizeof($fields) > 0) {
            foreach($fields as $value) {
                if(is_array($data['custom_'.$value['id']])) {
                    $data['custom_'.$value['id']] = @implode("\n",$data['custom_'.$value['id']]);
                }
            }
        }

        $insert_id = parent::insert($data);

        $this->updateUserGroups($data,$insert_id);

        if(is_array(value($data,'email_lists'))) {
            foreach($data['email_lists'] as $email_list_id) {
                $this->db->Execute("INSERT INTO ".T_EMAIL_LISTS_LOOKUP." (user_id,list_id) VALUES (?,?)",array($insert_id,$email_list_id));
            }
        }

        if(!empty($data['profile_image'])) {
            $this->updateProfileImage($insert_id, $data['profile_image']);
        }

        $this->PMDR->get('Plugins')->run_hook('user_add',$insert_id);

        return $insert_id;
    }

    /**
    * Update User
    * @param array $data User array
    * @param integer $id User ID
    * @return boolean True if successful update
    */
    function update($data, $id) {
        // If a password was entered make sure to encrypt it and add it to the query.
        if(!empty($data['pass'])) {
            $data['password_salt'] = $this->PMDR->get('Authentication')->generateSalt();
            $data['pass'] = $this->PMDR->get('Authentication')->encryptPassword($data['pass'],$data['password_salt']);
            $data['password_hash'] = $this->PMDR->get('Authentication')->encryption;
        } else {
            unset($data['pass']);
        }
        $this->updateUserGroups($data,$id);
        if(isset($data['email_lists'])) {
            $this->db->Execute("DELETE FROM ".T_EMAIL_LISTS_LOOKUP." WHERE user_id=?",array($id));
            if(is_array($data['email_lists'])) {
                foreach($data['email_lists'] as $email_lists_id) {
                    $this->db->Execute("INSERT INTO ".T_EMAIL_LISTS_LOOKUP." (user_id,list_id) VALUES (?,?)",array($id,$email_lists_id));
                }
            }
            unset($data['email_lists']);
        }

        if($data['delete_profile_image']) {
            @unlink(find_file(PROFILE_IMAGES_PATH.$id.'.*'));
        }

        if(!empty($data['profile_image'])) {
            $this->updateProfileImage($id, $data['profile_image']);
            unset($data['profile_image']);
        }

        $fields = $this->PMDR->get('Fields')->getFields('users');
        if(is_array($fields) AND sizeof($fields) > 0) {
            foreach($fields as $value) {
                if(is_array($data['custom_'.$value['id']])) {
                    $data['custom_'.$value['id']] = @implode("\n",$data['custom_'.$value['id']]);
                }
            }
        }

        $data['date_updated'] = $this->PMDR->get('Dates')->dateTimeNow();
        return parent::update($data,$id);
    }

    /**
    * Delete user
    * @param integer $userid User ID
    * @return void
    */
    function delete($userid) {
        $this->PMDR->get('Plugins')->run_hook('user_delete',$userid);

        if($orders = $this->db->GetCol("SELECT id FROM ".T_ORDERS." WHERE user_id=?",array($userid))) {
            foreach($orders AS $order) {
                $this->PMDR->get('Orders')->delete($order);
            }
        }

        if($listings = $this->db->GetCol("SELECT id FROM ".T_LISTINGS." WHERE user_id=?",array($userid))) {
            foreach($listings AS $listing) {
                $this->PMDR->get('Listings')->delete($listing);
            }
        }

        $foreign_tables = array(
            T_BLOG,
            T_BLOG_COMMENTS,
            T_BLOG_FOLLOWERS,
            T_CANCELLATIONS,
            T_CONTACT_REQUESTS,
            T_EMAIL_LISTS_LOOKUP,
            T_EMAIL_QUEUE,
            T_EMAIL_LOG,
            T_EMAIL_MARKETING_QUEUE,
            T_EVENTS,
            T_EVENTS_RSVP,
            T_FAVORITES,
            T_INVOICES,
            T_LOG,
            T_LISTINGS_CLAIMS,
            T_LISTINGS_SUGGESTIONS,
            T_RATINGS,
            T_REVIEWS,
            T_REVIEWS_COMMENTS,
            T_REVIEWS_QUALITY,
            T_SEARCH_LOG,
            T_SESSIONS,
            T_TRANSACTIONS,
            T_UPDATES,
            T_USERS_API_KEYS,
            T_USERS_CARDS,
            T_USERS_GROUPS_LOOKUP,
            T_USERS_LOGIN_PROVIDERS
        );

        foreach($foreign_tables AS $table) {
            $this->db->Execute("DELETE FROM ".$table." WHERE user_id=?",array($userid));
        }

        $this->db->Execute("DELETE m, mp FROM ".T_MESSAGES." m LEFT JOIN ".T_MESSAGES_POSTS." mp ON m.id=mp.message_id WHERE (m.user_id_from=? OR m.user_id_to=?)",array($userid,$userid));
        $this->db->Execute("DELETE FROM ".T_USERS." WHERE id=?",array($userid));

        @unlink(find_file(PROFILE_IMAGES_PATH.$userid.'.*'));
    }

    /**
    * Uppdate a users user groups
    *
    * @param array $data
    * @param int $id User ID
    */
    function updateUserGroups($data,$id) {
        if(isset($data['user_groups']) AND !empty($data['user_groups'])) {
            $this->db->Execute("DELETE FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id=?",array($id));
            foreach($data['user_groups'] as $group_id) {
                $this->db->Execute("INSERT INTO ".T_USERS_GROUPS_LOOKUP." (user_id,group_id) VALUES (?,?)",array($id,$group_id));
            }
        }
    }

    /**
    * Verify the password reset code for the user
    * @param integer $user User ID
    * @param string $hash User password hash
    * @return string|boolean String of new password, or false if failed authentication
    */
    function verifyPasswordResetCode($user, $verify_password) {
        if($verify_password == $user['password_verify']) {
            return $this->resetPassword($user['id']);
        } else {
            return false;
        }
    }

    /**
    * Verify the password is correct for a user ID
    * @param int $id
    * @param string $password
    * @return boolean
    */
    function verifyPassword($id, $password) {
        if($user = $this->db->GetRow("SELECT id, pass, password_salt, password_hash FROM ".T_USERS." WHERE id=?",array($id))) {
            if($this->PMDR->get('Authentication')->encryptPassword($password,$user['password_salt'],$user['password_hash']) == $user['pass']) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
    * Set password verification code of user
    * @param integer $id User ID
    * @param string $hash User password hash
    * @return boolean True if updated
    */
    function setPasswordVerificationCode($id, $hash) {
        return $this->db->Execute("UPDATE ".T_USERS." SET password_verify=MD5(CONCAT('".$hash."',user_email)) WHERE id=?",array($id));
    }

    /**
    * Get user tax rate
    * @param integer $id User ID
    * @return float Tax rate
    */
    function getTaxRate($id) {
        $tax_rate = 0.00;
        $taxes = $this->db->GetAll("SELECT level, tax_rate FROM ".T_USERS." u, ".T_TAX." t WHERE u.id=? AND u.tax_exempt=0 AND ((user_country=country AND state='') OR (user_country=country AND user_state=state) OR (state='' AND country='')) GROUP BY t.id, level",array($id));
        $tax_rates = array(1=>0.00,2=>0.00);
        foreach($taxes as $rate) {
            if($rate['level'] == 1) {
                $tax_rates[1] += (float) $rate['tax_rate'];
            } else {
                $tax_rates[2] += (float) $rate['tax_rate'];
            }
        }
        return $tax_rates;
    }

    /**
    * Update user profile image
    * @param int $user_id
    * @param mixed $image_file
    */
    function updateProfileImage($user_id, $image_file) {
        $options = array(
            'width'=>$this->PMDR->getConfig('profile_image_width'),
            'height'=>$this->PMDR->getConfig('profile_image_height'),
            'enlarge'=>$this->PMDR->getConfig('profile_image_enlarge'),
            'crop'=>true,
            'remove_old'=>true
        );
        return $this->PMDR->get('Image_Handler')->process($image_file,PROFILE_IMAGES_PATH.$user_id.'.*',$options);
    }

    /**
    * Get user permissions
    * @param int $user_id
    * @return array
    */
    function getPermissions($user_id) {
        return $this->db->GetCol("
            SELECT ugpl.permission_id
            FROM
                ".T_USERS_GROUPS_LOOKUP." AS ugl,
                ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." AS ugpl
            WHERE
                ugl.user_id=? AND
                ugl.group_id=ugpl.group_id",
            array($user_id)
        );
    }

    /**
    * Reset user password
    * @param int $user_id
    * @return string New password
    */
    function resetPassword($user_id) {
        $new_pass = Strings::random(8);
        $new_salt = $this->PMDR->get('Authentication')->generateSalt();
        $this->db->Execute("UPDATE ".T_USERS." SET pass=?, password_salt=?, password_hash=? WHERE id=?",array($this->PMDR->get('Authentication')->encryptPassword($new_pass,$new_salt),$new_salt,$this->PMDR->get('Authentication')->encryption,$user_id));
        return $new_pass;
    }

    /**
    * Remove user from all email lists
    * @param int $user_id
    * @param string $token
    * @return boolean
    */
    function unsubscribeAll($user_id,$token) {
        $user = $this->db->GetRow("SELECT id, user_email FROM ".T_USERS." WHERE id=?",array($user_id));
        if($token == hash('sha256',$user_id.$user['user_email'].SECURITY_KEY)) {
            $this->db->Execute("DELETE FROM ".T_EMAIL_LISTS_LOOKUP." WHERE user_id=?",array($user_id));
            return true;
        } else {
            return false;
        }
    }

    /**
    * Merge two users
    * @param int $from User ID to merge
    * @param int $to User ID to merge to
    * @param bool $delete_from Delete the original user
    * @return bool
    */
    function merge($from, $to, $delete_from = true) {
        if(!$user_from = $this->db->GetRow("SELECT id, login FROM ".T_USERS." WHERE id=?",array($from))) {
            return false;
        }
        if(!$user_to = $this->db->GetRow("SELECT id, login FROM ".T_USERS." WHERE id=?",array($to))) {
            return false;
        }

        $this->db->Execute("UPDATE ".T_BLOG." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_BLOG_COMMENTS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_CANCELLATIONS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_CONTACT_REQUESTS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_EMAIL_QUEUE." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_EMAIL_LOG." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_EVENTS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_EVENTS_RSVP." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_FAVORITES." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_INVOICES." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_LISTINGS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_LISTINGS_CLAIMS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_LISTINGS_SUGGESTIONS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_LOG." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_MESSAGES." SET user_id_from=? WHERE user_id_from=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_MESSAGES." SET user_id_to=? WHERE user_id_to=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_MESSAGES_POSTS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_ORDERS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_RATINGS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_REVIEWS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_REVIEWS_COMMENTS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE IGNORE ".T_REVIEWS_QUALITY." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("DELETE FROM ".T_REVIEWS_QUALITY." WHERE user_id=?",array($user_from['id']));
        $this->db->Execute("UPDATE ".T_SEARCH_LOG." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_TRANSACTIONS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_UPDATES." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_USERS_CARDS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));
        $this->db->Execute("UPDATE ".T_USERS_LOGIN_PROVIDERS." SET user_id=? WHERE user_id=?",array($user_to['id'],$user_from['id']));

        $lists = $this->db->GetCol("SELECT list_id FROM ".T_EMAIL_LISTS_LOOKUP." WHERE user_id=?",array($user_from['id']));
        foreach($lists as $lists_id) {
            $this->db->Execute("INSERT IGNORE INTO ".T_EMAIL_LISTS_LOOKUP." (user_id,list_id) VALUES (?,?)",array($user_to['id'],$lists_id));
        }

        if($delete_from) {
            $this->db->Execute("DELETE FROM ".T_SESSIONS." WHERE user_id=?",array($user_from['id']));
            $this->delete($user_from['id']);
        }

        return true;
    }

    /**
    * Get profile image
    *
    * @param int $id
    * @param string $email
    * @return string Image URL
    */
    function getProfileImage($id,$email='') {
        $profile_image = get_file_url(PROFILE_IMAGES_PATH.$id.'.*');
        if(!$profile_image AND $this->PMDR->getConfig('gravatar') AND $email!='') {
            $profile_image = URL_SCHEME.'://www.gravatar.com/avatar/'.md5(Strings::strtolower(trim($email))).'.jpg?s='.$this->PMDR->getConfig('profile_image_width').'&d=mm';
        }
        return $profile_image;
    }
}
?>