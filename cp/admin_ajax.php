<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

// Load the language so CHARSET gets populated
$PMDR->loadLanguage();

// Check authentication, and if not authenticated return a 500 error that the AJAX error handler can process
if(!$PMDR->get('Authentication')->authenticate(array('redirect'=>false))) {
    header('HTTP/1.0 401 Unauthorized', true, '401');
    exit(BASE_URL_ADMIN);
}

// Check the from variable to ensure it matches to prevent CSRF and return a 500 error that the AJAX error handler can process
if(!isset($_POST[COOKIE_PREFIX.'from']) OR empty($_POST[COOKIE_PREFIX.'from']) OR $_POST[COOKIE_PREFIX.'from'] != $_COOKIE[COOKIE_PREFIX.'from']) {
    header('HTTP/1.0 500 Internal Server Error', true, '500');
    exit('Bad Token');
}

// Need to add charset to all ajax responses where we don't use common_header.php
header('Content-Type: text/html; charset='.CHARSET);

// Ajax actions
switch($_POST['action']) {
    case 'message_add':
        $template = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_message.tpl');
        $message_types = array($_POST['type']=>array($_POST['message']));
        $template->set('message_types',$message_types);
        echo $template->render();
        break;
    case 'update_ordering':
        parse_str($_POST['data'],$_POST['data']);
        foreach($_POST['data'] as $key=>$value) {
            if(!strstr($key,'order')) continue;
            $db->Execute("UPDATE ".$db->CleanIdentifier($_POST['table'])." SET ordering=? WHERE id=?",array($value,substr(strrchr($key, '_'), 1)));
        }
        $PMDR->addMessage('success','Order updated!');
        break;
    case 'update_ordering_inline':
        foreach($_POST['order'] as $key=>$value) {
            $db->Execute("UPDATE ".$db->CleanIdentifier($_POST['table'])." SET ordering=? WHERE id=?",array($key,substr(strrchr($value, '_'), 1)));
        }
        break;
    case 'admin_maintenance_db_find_get_columns':
        echo json_encode($db->MetaColumnNames($_POST['table']));
        break;
    case 'admin_email_schedules_get_templates':
        $PMDR->loadLanguage('email_templates');
        $email_templates = $db->GetAssoc("SELECT id, id FROM ".T_EMAIL_TEMPLATES." WHERE type=? ORDER BY id",array($_POST['type']));
        foreach($email_templates AS $key=>$value) {
            $email_templates[$key] = $PMDR->getLanguage('email_templates_'.$value.'_name');
            if(strstr($value,'admin_')) {
                $email_templates[$key] .= ' (Administrator)';
            }
        }
        echo json_encode($email_templates);
        break;
    case 'admin_languages_translate_check':
        // Check permissions
        $PMDR->get('Authentication')->checkPermission('admin_languages_edit');

        // Check status of key
        $result = $PMDR->get('Google_Translate')->checkApiKey();

        $message = '';
        switch($result) {
            case Google_Translate::KEY_OKAY:
                $message = 'valid';
                break;
            case Google_Translate::KEY_DISABLED:
                $message = 'disabled';
                break;
            case Google_Translate::KEY_INVALID:
                $message = 'invalid';
                break;
            default:
                $message = 'error';
                break;
        }
        echo json_encode($message);
        break;
    case 'admin_languages_translate':
        $PMDR->loadLanguage(array('admin_languages'));
        $PMDR->get('Authentication')->checkPermission('admin_languages_edit');

        if(((int) $_POST['start']) == 0) {
            $csv_output = "\"".$PMDR->getLanguage('admin_languages_section')."\",\"".$PMDR->getLanguage('admin_languages_variable_name')."\",\"".$PMDR->getLanguage('admin_languages_content')."\",\"".$PMDR->getLanguage('admin_languages_content_translated')."\"\r\n";
            $handle = fopen(TEMP_UPLOAD_PATH.'Language_'.$_POST['to'].'.csv', 'w');
            fwrite($handle, $csv_output);
            fclose($handle);
        }

        $phrases = $db->GetAll("SELECT section, variablename, content FROM ".T_LANGUAGE_PHRASES." WHERE languageid=-1 LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
        $handle = fopen(TEMP_UPLOAD_PATH.'Language_'.$_POST['to'].'.csv', 'a');
        $count = (int) $_POST['start'];
        $csv_output = '';
        foreach($phrases as $phrase) {
            // Auto translating does not work well with email templates so we skip them
            if($phrase['section'] == 'email_templates') {
                $count++;
                continue;
            }
            $translation = $PMDR->get('Google_Translate')->translate($phrase['content'],'en',$_POST['to']);
            $csv_output .= "\"$phrase[section]\",\"$phrase[variablename]\",\"".str_replace('"','""',$phrase['content'])."\",\"".$translation."\"\r\n";
            $count++;
        }
        fwrite($handle, $csv_output);
        fclose($handle);

        $return = array(
            'percent'=>floor(($count*100) / $db->GetOne("SELECT COUNT(*) FROM ".T_LANGUAGE_PHRASES." WHERE languageid=-1")),
            'translated'=>count($phrases),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num'],
            'to'=>$_POST['to']
        );

        echo json_encode($return);
        break;
    case 'admin_maintenance_duplicates':
        $result = array();
        $result['type'] = $_POST['type'];
        switch($_POST['type']) {
            case 'user_ip':
                $result['results'] = $db->GetAll("SELECT logged_ip, COUNT(id) AS count FROM ".T_USERS." WHERE logged_ip != '' GROUP BY logged_ip HAVING count > 1 ORDER BY count DESC");
                break;
            case 'user_name':
                $result['results'] = $db->GetAll("SELECT user_first_name, user_last_name, CONCAT(user_first_name,user_last_name) name, COUNT(id) AS count FROM ".T_USERS." GROUP BY user_first_name, user_last_name HAVING name!='' AND count > 1 ORDER BY count DESC");
                break;
            case 'user_phone':
                $result['results'] = $db->GetAll("SELECT user_phone, COUNT(id) AS count FROM ".T_USERS." GROUP BY user_phone HAVING user_phone!='' AND count > 1 ORDER BY count DESC");
                break;
            case 'listing_title':
                $result['results'] = $db->GetAll("SELECT title, COUNT(id) AS count FROM ".T_LISTINGS." GROUP BY title HAVING count > 1 ORDER BY count DESC");
                break;
            case 'listing_www':
                $result['results'] = $db->GetAll("SELECT www, COUNT(id) AS count FROM ".T_LISTINGS." GROUP BY www HAVING www!='' AND count > 1 ORDER BY count DESC");
                break;
            case 'listing_phone':
                $result['results'] = $db->GetAll("SELECT phone, COUNT(id) AS count FROM ".T_LISTINGS." GROUP BY phone HAVING phone!='' AND count > 1 ORDER BY count DESC");
                break;
            default:
                $result = null;
                break;
        }
        if(!$result['results']) {
            $result = null;
        }
        sleep(1);
        echo json_encode($result);
        break;
    case 'admin_maintenance_images':
        switch($_POST['type']) {
            case 'logos':
                $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS id, logo_extension FROM ".T_LISTINGS." WHERE logo_extension != '' LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
                $thumbnail_options = array(
                    'width'=>$PMDR->getConfig('image_logo_thumb_width'),
                    'height'=>$PMDR->getConfig('image_logo_thumb_height'),
                    'enlarge'=>$PMDR->getConfig('image_logo_thumb_small'),
                    'crop'=>$PMDR->getConfig('image_logo_thumb_crop')
                );
                $options = array(
                    'width'=>$PMDR->getConfig('image_logo_width'),
                    'height'=>$PMDR->getConfig('image_logo_height'),
                    'enlarge'=>$PMDR->getConfig('image_logo_small')
                );
                foreach($records AS $record) {
                    if(file_exists(LOGO_PATH.$record['id'].'.'.$record['logo_extension'])) {
                        $PMDR->get('Image_Handler')->process(LOGO_PATH.$record['id'].'.'.$record['logo_extension'],LOGO_PATH.$record['id'].'.'.$record['logo_extension'],$options);
                        $PMDR->get('Image_Handler')->process(LOGO_PATH.$record['id'].'.'.$record['logo_extension'],LOGO_THUMB_PATH.$record['id'].'.'.$record['logo_extension'],$thumbnail_options);
                    }
                }
                break;
            case 'images':
                $options = array(
                    'width'=>$PMDR->getConfig('gallery_thumb_width'),
                    'height'=>$PMDR->getConfig('gallery_thumb_height'),
                    'enlarge'=>$PMDR->getConfig('gallery_thumb_small'),
                    'crop'=>$PMDR->getConfig('gallery_thumb_crop')
                );
                $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS id, extension FROM ".T_IMAGES." LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
                foreach($records AS $record) {
                    if(file_exists(IMAGES_PATH.$record['id'].'.'.$record['extension'])) {
                        $PMDR->get('Image_Handler')->process(IMAGES_PATH.$record['id'].'.'.$record['extension'],IMAGES_THUMBNAILS_PATH.$record['id'].'.'.$record['extension'],$options);
                    }
                }
                break;
            case 'classifieds':
                $options = array(
                    'width'=>$PMDR->getConfig('classified_thumb_width'),
                    'height'=>$PMDR->getConfig('classified_thumb_height'),
                    'enlarge'=>$PMDR->getConfig('classified_thumb_small'),
                    'crop'=>$PMDR->getConfig('classified_thumb_crop')
                );
                $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS id, classified_id, extension FROM ".T_CLASSIFIEDS_IMAGES." LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
                foreach($records AS $record) {
                    if(file_exists(CLASSIFIEDS_PATH.$record['id'].'.'.$record['extension'])) {
                        $PMDR->get('Image_Handler')->process(CLASSIFIEDS_PATH.$record['id'].'.'.$record['extension'],CLASSIFIEDS_THUMBNAILS_PATH.$record['classified_id'].'-'.$record['id'].'.'.$record['extension'],$options);
                    }
                }
                break;
            case 'profile_images':
                $options = array(
                    'width'=>$PMDR->getConfig('profile_image_width'),
                    'height'=>$PMDR->getConfig('profile_image_height'),
                    'enlarge'=>$this->PMDR->getConfig('profile_image_enlarge'),
                    'crop'=>true
                );
                $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS id FROM ".T_USERS." LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
                foreach($records AS $record) {
                    if(file_exists($file = find_file(PROFILE_IMAGES_PATH.$record['id'].'.*'))) {
                        $PMDR->get('Image_Handler')->process($file,$file,$options);
                    }
                }
                break;
        }
        $return = array(
            'rebuilt'=>count($records),
            'percent'=>(!$records ? 100 : floor((($_POST['start']+count($records))*100) / $db->FoundRows())),
            'type'=>$_POST['type'],
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']

        );
        echo json_encode($return);
        break;
    case 'admin_categories_sort':
        $PMDR->loadLanguage(array('admin_categories'));
        $PMDR->get('Authentication')->checkPermission('admin_categories_edit');

        if(((int) $_POST['start']) == 0) {
            $PMDR->get('Categories')->initializeSort();
        }

        $percent_complete = $PMDR->get('Categories')->processSort($_POST['start'], $_POST['num']);

        $return = array(
            'percent'=>$percent_complete,
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );

        if($percent_complete == 100) {
            if($PMDR->get('Categories')->finalizeSort())  {
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_categories_sorted'));
                $return['result'] = 'success';
            } else {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_categories_sort_failed'));
                $return['result'] = 'error';
            }
        }
        echo json_encode($return);
        break;
    case 'admin_classifieds_categories_sort':
        $PMDR->loadLanguage(array('admin_classifieds_categories'));

        if(((int) $_POST['start']) == 0) {
            $PMDR->get('Classifieds_Categories')->initializeSort();
        }

        $percent_complete = $PMDR->get('Classifieds_Categories')->processSort($_POST['start'], $_POST['num']);

        $return = array(
            'percent'=>$percent_complete,
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );

        if($percent_complete == 100) {
            if($PMDR->get('Classifieds_Categories')->finalizeSort())  {
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_classifieds_categories_sorted'));
                $return['result'] = 'success';
            } else {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_classifieds_categories_sort_failed'));
                $return['result'] = 'error';
            }
        }
        echo json_encode($return);
        break;
    case 'admin_locations_sort':
        $PMDR->loadLanguage(array('admin_locations'));
        $PMDR->get('Authentication')->checkPermission('admin_locations_edit');

        if(((int) $_POST['start']) == 0) {
            $create_table = $db->GetRow("SHOW CREATE TABLE ".T_LOCATIONS);
            // Rename current table to a tmp table
            $db->Execute("DROP TABLE IF EXISTS ".T_LOCATIONS."_tmp");
            $db->Execute("RENAME TABLE ".T_LOCATIONS." TO ".T_LOCATIONS."_tmp");
            // Create new locations table that we will populate with sorted values
            $db->Execute($create_table['Create Table']);
        }
        $records = $db->GetAll("SELECT * FROM ".T_LOCATIONS."_tmp ORDER BY level, title LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
        $count = (int) $_POST['start'];
        foreach($records as $record) {
            if($record['level'] == '0') {
                $db->Execute("INSERT INTO ".T_LOCATIONS." SET id=?, title=?, level=?, left_=?, right_=?, parent_id=?, impressions=?",array(1,'ROOT',0,0,1,NULL,0));
            } else {
                // Get the parent of the current record so we know where to insert it
                $record['placement_id'] = $record['parent_id'];
                $record['placement'] = 'subcategory';
                // Insert the location, if it fails we set completion to 100% and break so it will restore the old data
                if(!$PMDR->get('Locations')->insert($record)) {
                    $return['percent'] = 100;
                    break;
                }
            }
            $count++;
        }

        $return = array(
            'percent'=>floor(($count*100) / $db->GetOne("SELECT COUNT(*) FROM ".T_LOCATIONS."_tmp")),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );

        if($return['percent'] == 100) {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_LOCATIONS) == $db->GetOne("SELECT COUNT(*) FROM ".T_LOCATIONS."_tmp"))  {
                // Delete original table
                $db->Execute("DROP TABLE ".T_LOCATIONS."_tmp");
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_locations_sorted'));
                $return['result'] = 'success';
            } else {
                // If counts are not equal we delete the new table, and restore the old table
                $db->Execute("DROP TABLE ".T_LOCATIONS);
                $db->Execute("RENAME TABLE ".T_LOCATIONS."_tmp  TO ".T_LOCATIONS);
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_locations_sort_failed'));
                $return['result'] = 'error';
            }
        }
        echo json_encode($return);
        break;
    case 'select_user':
        if(isset($_POST['id'])) {
            echo $db->GetOne("SELECT login FROM ".T_USERS." WHERE id=?",array($_POST['id']));
        } else {
            $search = '';
            if(isset($_POST['search'])) {
                $search = "WHERE user_first_name LIKE ".$db->Clean($_POST['search']."%")." OR user_last_name LIKE ".$db->Clean($_POST['search']."%")." OR user_email LIKE ".$db->Clean($_POST['search']."%")." OR login LIKE ".$db->Clean($_POST['search']."%");
            }
            $users = $db->GetAll("SELECT id, user_first_name, user_last_name, login, user_email FROM ".T_USERS." $search ORDER BY login ASC LIMIT ?,?",array(intval($_POST['start']),intval($_POST['num'])));
            if(count($users)) {
                echo '<table class="table table-bordered">';
                echo '<thead><tr><th>ID</th><th>Name</th><th>Login</th><th>Email</th></tr></thead><tbody>';
                foreach($users as $user) {
                    echo '<tr>';
                    echo '<td>'.$user['id'].'</td>';
                    if(trim($user['user_first_name'].' '.$user['user_last_name']) == '') {
                        echo '<td>-</td>';
                    } else {
                        echo '<td>'.$user['user_first_name'].' '.$user['user_last_name'].'</td>';
                    }
                    echo '<td>'.$user['login'].'</td>';
                    echo '<td>'.$user['user_email'].'</td>';
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            } else {
                echo 'No Results';
            }
        }
        break;
    case 'select_listing':
        if(isset($_POST['id'])) {
            echo $db->GetOne("SELECT title FROM ".T_LISTINGS." WHERE id=?",array($_POST['id']));
        } else {
            $search = '';
            if(isset($_POST['search'])) {
                $search = "WHERE title LIKE ".$db->Clean($_POST['search']."%")." OR id=".$db->Clean($_POST['search']);
            }
            $records = $db->GetAll("SELECT id, title FROM ".T_LISTINGS." $search ORDER BY title ASC LIMIT ?,?",array(intval($_POST['start']),intval($_POST['num'])));
            if(count($records)) {
                echo '<div class="table-list"><table class="table table-bordered table-hover">';
                echo '<thead><tr><th>ID</th><th>Title</th></tr></thead><tbody>';
                foreach($records AS $record) {
                    echo '<tr>';
                    echo '<td>'.$record['id'].'</td>';
                    echo '<td>'.$record['title'].'</td>';
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            } else {
                echo 'No Results';
            }
        }
        break;
    case 'admin_categories_expand':
    case 'admin_locations_expand':
    case 'admin_classifieds_categories_expand':
        if($_POST['action'] == 'admin_categories_expand') {
            $table = T_CATEGORIES;
            $tree_object = $PMDR->get('Categories');
            $file = 'admin_categories.php';
            $query_string_name = 'category';
            $count_file = 'admin_listings.php';
        } elseif($_POST['action'] == 'admin_classifieds_categories_expand') {
            $table = T_CLASSIFIEDS_CATEGORIES;
            $tree_object = $PMDR->get('Classifieds_Categories');
            $file = 'admin_classifieds_categories.php';
            $query_string_name = 'category';
            $count_file = 'admin_classifieds.php';
        } else {
            $table = T_LOCATIONS;
            $tree_object = $PMDR->get('Locations');
            $file = 'admin_locations.php';
            $query_string_name = 'location';
            $count_file = 'admin_listings.php';
        }

        $parent = $db->GetRow("SELECT id, left_, right_, level FROM $table WHERE id=?",array($_POST['value']));
        $records = $db->GetAll("SELECT id, title, description_short, level, left_, right_, count_total FROM $table WHERE left_ > ? AND right_ < ? AND level=?+1 ORDER BY left_",array($parent['left_'],$parent['right_'],$parent['level']));
        $data = '';
        foreach($records AS $record) {
            $data .= '
            <tr class="'.$parent['id'].'_child">
                <td><input name="table_list_checkboxes[]" class="table-list-checkbox-input" type="checkbox" value="'.$record['id'].'"/></td>
                <th scope="row">'.$record['id'].'</th>
                <td style="padding-left: '.((($record['level']-1)*15)+10).'px">';
                if(!$tree_object->isLeaf($record)) {
                    $data .= '<div id="'.$record['id'].'" class="collapsed">'.$record['title'].'</div>';
                } else {
                    $data .= '<span style="margin-left: 15px;">'.$record['title'].'</span></td>';
                }
                $data .= '<td><a href="'.$count_file.'?'.$query_string_name.'='.$record['id'].'">'.$record['count_total'].'</a></td>';
                $data .= '<td>'.$record['description_short'].'</td><td>';
                $data .= $PMDR->get('HTML')->icon('edit',array('href'=>BASE_URL_ADMIN.'/'.$file.'?action=edit&id='.$record['id']));
                $data .= $PMDR->get('HTML')->icon('delete',array('href'=>BASE_URL_ADMIN.'/'.$file.'?action=delete&id='.$record['id']));
                $data .= '</td>
            </tr>';
        }
        echo $data;
        break;
    case 'admin_categories_search':
        echo $PMDR->get('Categories')->quickSearch($_POST['value']);
        break;
    case 'admin_classifieds_categories_search':
        echo $PMDR->get('Classifieds_Categories')->quickSearch($_POST['value']);
        break;
    case 'admin_locations_search':
        $records = $db->GetAll("SELECT id, title FROM ".T_LOCATIONS." WHERE title LIKE ".$db->Clean($_POST['value']."%")." ORDER BY title LIMIT 20");

        $data = '';
        if(count($records)) {
            foreach($records AS $record) {
                $record_path = $PMDR->get('Locations')->getPath($record['id']);
                foreach($record_path AS $key=>$path) {
                    if($key != 0) {
                        $data .= ' > ';
                    }
                    $data .= '<a href="'.BASE_URL_ADMIN.'/admin_locations.php?action=edit&id='.$path['id'].'">';
                    $data .= $path['title'];
                    $data .= '</a>';

                }
                $data .= '<br />';
            }
        } else {
            $data = 'No Results';
        }
        echo $data;
        break;
    case 'admin_quick_search':
        $template = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_quick_search.tpl');
        if(!is_numeric($_POST['value'])) {
            if($listings = $db->GetAll("SELECT id, user_id, title, MATCH(title) AGAINST (".$db->Clean($_POST['value'].'*')." IN BOOLEAN MODE) AS relevance FROM ".T_LISTINGS." WHERE MATCH(title) AGAINST (".$db->Clean($_POST['value'].'*')." IN BOOLEAN MODE) ORDER BY relevance DESC, title LIMIT 10")) {
                $template->set('listings',$listings);
            } elseif($listings = $db->GetAll("SELECT id, user_id, title FROM ".T_LISTINGS." WHERE mail=? LIMIT 1",array($_POST['value']))) {
                $template->set('listings',$listings);
            }
            if($users = $db->GetAll("SELECT id, login, user_first_name, user_last_name FROM ".T_USERS." WHERE user_first_name LIKE ".$db->Clean($_POST['value']."%")." OR user_last_name LIKE ".$db->Clean($_POST['value']."%")." OR user_email LIKE ".$db->Clean($_POST['value']."%")." OR login LIKE ".$db->Clean($_POST['value']."%")." ORDER BY user_first_name, user_last_name LIMIT 10")) {
                foreach($users AS $key=>$result) {
                    if(trim($result['user_first_name'].$result['user_last_name']) != '') {
                        $users[$key]['name'] = trim($result['user_first_name'].' '.$result['user_last_name']);
                    } else {
                        $users[$key]['name'] = $result['login'];
                    }
                }
                $template->set('users',$users);
            }
            if($classifieds = $db->GetAll("SELECT id, title FROM ".T_CLASSIFIEDS." WHERE match(title) AGAINST (".$db->Clean($_POST['value'].'*')." IN BOOLEAN MODE) ORDER BY title LIMIT 10")) {
                $template->set('classifieds',$classifieds);
            }
            if($transactions = $db->GetAll("SELECT id, transaction_id FROM ".T_TRANSACTIONS." WHERE transaction_id LIKE ".$db->Clean($_POST['value']."%")." ORDER BY transaction_id LIMIT 10")) {
                $template->set('transactions',$transactions);
            }
            if($settings = $db->GetAll("SELECT s.*, l.content FROM ".T_LANGUAGE_PHRASES." l INNER JOIN ".T_SETTINGS." s ON l.variablename = CONCAT('setting_',s.varname,'_title') WHERE l.section='settings' AND (l.content LIKE ".$db->Clean($_POST['value']."%")." OR s.varname LIKE ".$db->Clean($_POST['value']."%").")")) {
                foreach($settings AS $key=>$setting) {
                    if($setting['varname'] == 'reciprocal_field' AND !ADDON_LINK_CHECKER) {
                        unset($settings[$key]);
                        continue;
                    }
                    if(($setting['grouptitle'] == 'blog' OR strstr($setting['varname'],'blog')) AND !ADDON_BLOG) {
                        unset($settings[$key]);
                        continue;
                    }
                }
                if(count($settings)) {
                    $template->set('settings',$settings);
                }
            }
        } else {
            if($listings = $db->GetAll("SELECT id, user_id, title FROM ".T_LISTINGS." WHERE id=?",array($_POST['value']))) {
                $template->set('listings',$listings);
            }
            if($users= $db->GetAll("SELECT id, login, user_first_name, user_last_name FROM ".T_USERS." WHERE id=?",array($_POST['value']))) {
                foreach($users AS $key=>$result) {
                    if(trim($result['user_first_name'].$result['user_last_name']) != '') {
                        $users[$key]['name'] = trim($result['user_first_name'].' '.$result['user_last_name']);
                    } else {
                        $users[$key]['name'] = $result['login'];
                    }
                }
                $template->set('users',$users);
            }
            if($classifieds = $db->GetAll("SELECT id, title FROM ".T_CLASSIFIEDS." WHERE id=?",array($_POST['value']))) {
                $template->set('classifieds',$classifieds);
            }
            if($invoice = $db->GetRow("SELECT i.id, i.user_id, u.user_first_name, u.user_last_name, u.login FROM ".T_INVOICES." i INNER JOIN ".T_USERS." u ON i.user_id=u.id WHERE i.id=?",array($_POST['value']))) {
                if(trim($invoice['user_first_name'].$invoice['user_last_name']) != '') {
                    $invoice['user_name'] = trim($invoice['user_first_name'].' '.$invoice['user_last_name']);
                } else {
                    $invoice['user_name'] = $invoice['login'];
                }
                $template->set('invoice',$invoice);
            }
            if($order = $db->GetRow("SELECT o.id, o.order_id, o.user_id, u.user_first_name, u.user_last_name, u.login FROM ".T_ORDERS." o INNER JOIN ".T_USERS." u ON o.user_id=u.id WHERE o.id=? OR o.order_id=?",array($_POST['value'],$_POST['value']))) {
                if(trim($order['user_first_name'].$order['user_last_name']) != '') {
                    $order['user_name'] = trim($order['user_first_name'].' '.$order['user_last_name']);
                } else {
                    $order['user_name'] = $order['login'];
                }
                $template->set('order',$order);
            }
        }
        if($users OR $listings OR $invoice OR $order OR $settings OR $classifieds) {
            $template->set('results',true);
        }
        echo $template->render();
        break;
    case 'autocomplete_listings_www':
        $data = '';
        if(isset($_POST['value'])) {
            $wwws = $db->GetCol("SELECT www FROM ".T_LISTINGS." WHERE www LIKE ".$db->Clean($_POST['value']."%")." LIMIT 50");
            foreach($wwws as $www) {
                $data .= '<a href="#">'.$www.'</a><br />';
            }
        }
        if($data == '') {
            $data = 'No Results';
        }
        echo $data;
        break;
    case 'autocomplete_listings_email':
        $data = '';
        if(isset($_POST['value'])) {
            $emails = $db->GetCol("SELECT mail FROM ".T_LISTINGS." WHERE mail LIKE ".$db->Clean($_POST['value']."%")." LIMIT 50");
            foreach($emails as $email) {
                $data .= '<a href="#">'.$email.'</a><br />';
            }
        }
        if($data == '') {
            $data = 'No Results';
        }
        echo $data;
        break;
    case 'admin_zip_codes_import':
        // For MAC line endings
        @ini_set('auto_detect_line_endings',true);
        $PMDR->loadLanguage(array('admin_zip_codes'));
        $PMDR->get('Authentication')->checkPermission('admin_zip_codes_import');
        $filesize = filesize($_POST['file_path']);
        $handle = fopen($_POST['file_path'],'r');
        fseek($handle, $_POST['start']);
        if($_POST['start'] == 0) {
            // Check the first row to make sure we have some lat/lon values in the 2nd/3rd columns
            // If not we assume this is a header row and skip it
            $first_row = fgetcsv($handle, $_POST['num'], ',');
            if(is_numeric($first_row[1]) AND is_numeric($first_row[2])) {
                fseek($handle, 0);
            }
            unset($first_row);
        }
        $count = 0;
        while(($data = fgetcsv($handle, $_POST['num'], ',')) !== FALSE AND $count < 1000) {
            // Skip the import if we don't have numeric data for the coordinates
            if(is_numeric($data[1]) AND is_numeric($data[2])) {
                if(count($data) == 3) {
                    $PMDR->get('Zip_Codes')->replace(array('zipcode'=>(string) $data[0],'lat'=>(string) $data[1],'lon'=>(string) $data[2]));
                } elseif(count($data) == 7) {
                    $PMDR->get('Zip_Codes')->replace(array('zipcode'=>(string) $data[0],'lat'=>(string) $data[1],'lon'=>(string) $data[2], 'state'=>$data[3], 'state_abbreviation'=>$data[4], 'city'=>$data[5], 'country'=>$data[6]));
                }
            }
            $count++;
        }
        $current = ftell($handle);
        fclose($handle);

        $return = array(
            'percent'=>floor(($current*100) / $filesize),
            'start'=>(int) $current,
            'num'=>(int) $_POST['num'],
            'file_path'=>$_POST['file_path']
        );

        if($return['percent'] >= 100) {
            $PMDR->addMessage('success',$PMDR->getLanguage('admin_zip_codes_import_complete'));
        }

        echo json_encode($return);
        break;
    case 'admin_import':
        ignore_user_abort(true);
        $PMDR->loadLanguage(array('admin_import','admin_imports','email_templates'));
        $PMDR->get('Authentication')->checkPermission('admin_import');

        $import_results = $PMDR->get('Imports')->run($_POST['id']);

        $return = array(
            'percent'=>$import_results['percent'],
            'id'=>$_POST['id'],
            'statistics'=>$import_results['statistics']
        );

        echo json_encode($return);
        break;
    case 'admin_export':
        $PMDR->loadLanguage(array('admin_export','general_locations'));
        $PMDR->get('Authentication')->checkPermission('admin_export');
        $export = $db->GetRow("SELECT * FROM ".T_EXPORTS." WHERE id=?",array($_POST['id']));
        if($export) {
            $export['data'] = array_filter(explode(',',$export['data']));
            $export['categories'] = array_filter(explode(',',$export['categories']));
            $export['products'] = array_filter(explode(',',$export['products']));
            $fields = $PMDR->get('Fields')->getFieldLabels('listings');
            $category_labels = $PMDR->get('Categories')->getLevelLabels();
            $location_labels = $PMDR->get('Locations')->getLevelLabels();

            if(((int) $_POST['start']) == 0) {
                $file = fopen(TEMP_UPLOAD_PATH.'/Export'.$_POST['id'].'.csv','w');
                $columns = array();
                foreach($export['data'] AS $data) {
                    if($data == 'id') {
                        $columns[] = 'ID';
                    } elseif($data == 'category') {
                        foreach($category_labels as $label) {
                            $columns[] = $label;
                        }
                    } elseif($data == 'location') {
                        foreach($location_labels as $label) {
                            $columns[] = $label;
                        }
                    } elseif(preg_match('/custom_([0-9]+)/',$data,$field_id)) {
                        $columns[] = $fields[$field_id[0]];
                    } else {
                        $columns[] =  $PMDR->getLanguage('admin_export_listing_'.$data);
                    }
                }
                if(count($columns)) {
                    fwrite($file,'"'.implode('"'.$export['delimiter'].'"',$columns)."\"\r\n");
                }
            } else {
                $file = fopen(TEMP_UPLOAD_PATH.'/Export'.$_POST['id'].'.csv','a');
            }

            if(is_array($export['categories']) AND count($export['categories']) > 0) {
                $category_sql = "LEFT JOIN ".T_LISTINGS_CATEGORIES." lc ON l.id=lc.list_id WHERE lc.cat_id IN(".implode(',',$export['categories']).")";
            }
            if(is_array($export['products']) AND count($export['products']) > 0) {
                $products_sql = "AND o.pricing_id IN (".implode(',',$export['products']).")";
            }
            $listings = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS l.*, o.pricing_id FROM ".T_LISTINGS." l INNER JOIN ".T_ORDERS." o ON l.id=o.type_id AND o.type='listing_membership' $products_sql $category_sql ORDER BY l.id LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
            $total_listings = $db->FoundRows();
            $total = ($export['amount'] != 0 AND $export['amount'] < $total_listings) ? $export['amount'] : $total_listings;
            $count = (int) $_POST['start'];

            // Fix variables for the listing data
            if(in_array('address1',$export['data'])) {
                $export['data'][array_search('address1',$export['data'])] = 'listing_address1';
            }
            if(in_array('address2',$export['data'])) {
                $export['data'][array_search('address2',$export['data'])] = 'listing_address2';
            }
            if(in_array('zip',$export['data'])) {
                $export['data'][array_search('zip',$export['data'])] = 'listing_zip';
            }
            if(in_array('email',$export['data'])) {
                $export['data'][array_search('email',$export['data'])] = 'mail';
            }
            // Add setting to export one line per listing per category.
            foreach($listings AS $listing) {
                if($count >= $total) {
                    break;
                }

                if($export['format'] == 'primary_category') {
                    $categories = array($listing['primary_category_id']);
                } else {
                    $categories = $db->GetCol("SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=?",array($listing['id']));
                }
                $category_paths = array();
                foreach($categories AS $category) {
                    $category_paths[] = $PMDR->get('Categories')->getPath($category);
                }

                $locations = $PMDR->get('Locations')->getPath($listing['location_id']);
                foreach($locations as $loc_key=>$loc_value) {
                    $listing['location_'.($loc_key+1)] = $loc_value['title'];
                }
                unset($loc_key,$loc_value,$locations);

                foreach($categories AS $category_key=>$category) {
                    $row_data = array();

                    foreach($export['data'] AS $data) {
                        if($data == 'category') {
                            for($x=0; $x < count($category_labels); $x++) {
                                if($export['format'] == 'import') {
                                    $paths = array();
                                    foreach($category_paths AS $category_path) {
                                        $paths[] = $category_path[$x]['title'];
                                    }
                                    $row_data[] = implode('::',$paths);
                                } elseif($export['format'] == 'primary_category') {
                                    $row_data[] = $category_paths[0][$x]['title'];
                                } else {
                                    $row_data[] = $category_paths[$category_key][$x]['title'];
                                }
                            }
                        } elseif($data == 'location') {
                            foreach($location_labels AS $location_key=>$label) {
                                $row_data[] = $listing[$location_key];
                            }
                            unset($location_key,$label);
                        } elseif($data == 'logo_url') {
                            $row_data[] = get_file_url(LOGO_PATH.$listing['id'].'.'.$listing['logo_extension']);
                        } else {
                            $row_data[] =  $listing[$data];
                        }
                    }

                    fwrite($file,'"'.implode('"'.$export['delimiter'].'"',str_replace('"','""',$row_data))."\"\r\n");
                    if($export['format'] == 'import') {
                        break;
                    }
                }
                $count++;
            }
            fclose($file);
        } else {
            // error
        }

        $return = array(
            'percent'=>(($total_listings == 0) ? 100 : floor(($count*100) / $total)),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num'],
            'id'=>$_POST['id']
        );

        if($return['percent'] >= 100) {
            $PMDR->addMessage('success','Done!');
        }

        echo json_encode($return);
        break;
    case 'admin_maintenance_coordinates':
        $count = $_POST['count'];
        $max = $_POST['max'];
        $possible = $_POST['possible'];

        $limit = 20;

        // Quick check to prevent double checking if there are less than 20 unchecked.
        // Required because $max may have been set to an odd number.
        $remaining = $max - $count;
        if($remaining < $limit) {
            $limit = $remaining;
        }

        $listings = $db->GetAll('SELECT SQL_CALC_FOUND_ROWS id, location_id, listing_address1, listing_address2, listing_zip, location_text_1, location_text_2, location_text_3 FROM '.T_LISTINGS." WHERE latitude = '0.0000000000' AND address_allow=1 AND zip_allow=1 ORDER BY coordinates_date_checked ASC LIMIT $limit");

        // Check how many invalid addresses there are total (on the first run)
        if($possible == 0) {
            $possible = $db->FoundRows();
            if($possible < $max) {
                // This will prevent repeatedly rechecking invalid addresses.
                $max = $possible;
            }
        }

        // Increment the total count.
        $count = $count + count($listings);

        // Check if there are more.
        // Note: 0 < 0 == false, so will be false if there are none to check.
        $more = ($count < $max);

        $coordinates_failed = array();

        // GEOCODE
        $map = $PMDR->get('Map');
        foreach($listings as $data) {
            $locations = $PMDR->get('Locations')->getPath($data['location_id']);
            foreach($locations as $loc_key=>$loc_value) {
                $data['location_'.($loc_key+1)] = $loc_value['title'];
                if($loc_value['disable_geocoding']) {
                    $data['disable_geocoding'] = true;
                }
            }
            if(!$data['disable_geocoding']) {
                $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $data[$PMDR->getConfig('map_country')];
                $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $data[$PMDR->getConfig('map_state')];
                $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $data[$PMDR->getConfig('map_city')];
                if($coordinates = $map->getGeocode($data['listing_address1'], $map_city, $map_state, $map_country, $data['listing_zip'])) {
                    if(abs($coordinates['lat']) > 0 AND abs($coordinates['lon']) > 0) {
                        $db->Execute("UPDATE ".T_LISTINGS." SET latitude=?, longitude=? WHERE id=?",array($coordinates['lat'],$coordinates['lon'],$data['id']));
                    }
                }
            }

            if(!$coordinates OR abs($coordinates['lat']) == 0) {
                $db->Execute("UPDATE ".T_LISTINGS." SET coordinates_date_checked=NOW() WHERE id=?",array($data['id']));
                $coordinates_failed[] = $data['id'];
            }
        }
        unset($map);
        unset($listings);
        unset($data);

        $return = array(
            'count' => (int) $count,
            'max' => (int) $max,
            'possible' => (int) $possible,
            'percent' => (!$more ? 100 : floor($count *100 / $max)),
            'failed' => $coordinates_failed
        );
        echo json_encode($return);
        break;
    case 'admin_maintenance_recount_listings':
        $records = array(1);
        if($_POST['start'] == 0) {
            $db->Execute("UPDATE ".T_CATEGORIES." SET count=0");
            $db->Execute("UPDATE ".T_CATEGORIES." c,
            (SELECT cat.id AS id, COUNT(lc.list_id) AS count
            FROM ".T_CATEGORIES." AS cat, ".T_LISTINGS_CATEGORIES." AS lc, ".T_LISTINGS." AS l
            WHERE cat.id = lc.cat_id AND lc.list_id=l.id AND l.status='active' GROUP BY cat.id) cd
            SET c.count=cd.count WHERE c.id=cd.id");
        }
        if($_POST['start'] == 1) {
            $db->Execute("UPDATE ".T_CATEGORIES." SET count_total=0");
            $db->Execute("UPDATE ".T_CATEGORIES." c,
            (SELECT parent.id AS id, SUM(node.count) as count
            FROM ".T_CATEGORIES." AS parent, ".T_CATEGORIES." AS node
            WHERE node.left_ BETWEEN parent.left_ AND parent.right_
            GROUP BY parent.id) cd
            SET c.count_total = cd.count WHERE c.id=cd.id");
        }
        if($_POST['start'] == 2) {
            $db->Execute("UPDATE ".T_LOCATIONS." SET count=0");
            $db->Execute("UPDATE ".T_LOCATIONS." lc,
            (SELECT loc.id AS id, COUNT(l.id) AS count
            FROM ".T_LOCATIONS." AS loc, ".T_LISTINGS." AS l
            WHERE loc.id=l.location_id AND l.status='active' GROUP BY loc.id) ld
            SET lc.count=ld.count WHERE lc.id=ld.id");
        }
        if($_POST['start'] == 3) {
            $db->Execute("UPDATE ".T_LOCATIONS." SET count_total=0");
            $db->Execute("UPDATE ".T_LOCATIONS." l,
            (SELECT parent.id AS id, SUM(node.count) as count
            FROM ".T_LOCATIONS." AS parent, ".T_LOCATIONS." AS node
            WHERE node.left_ BETWEEN parent.left_ AND parent.right_
            GROUP BY parent.id) ld
            SET l.count_total = ld.count WHERE l.id=ld.id");
        }

        $return = array(
            'rebuilt'=>count($records),
            'percent'=>(!$records ? 100 : floor((($_POST['start']+count($records))*100) / 4)),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );
        echo json_encode($return);
        break;
    case 'admin_maintenance_rebuild_search_index':
        $listings = $db->GetAll("SELECT id, location_id, listing_address1, listing_address2, location_text_1, location_text_2, location_text_3, listing_zip FROM ".T_LISTINGS." ORDER BY id LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
        foreach($listings AS $listing) {
            $location_search_text = trim(preg_replace('/,+/',',',$PMDR->get('Locations')->getPathString($listing['location_id']).','.$listing['listing_address1'].','.$listing['listing_address2'].','.$listing['location_text_1'].','.$listing['location_text_2'].','.$listing['location_text_3'].','.$listing['listing_zip'].','.$PMDR->getConfig('map_city_static').','.$PMDR->getConfig('map_state_static').','.$PMDR->getConfig('map_country_static')),',');
            $db->Execute("UPDATE ".T_LISTINGS." SET location_search_text=? WHERE id=?",array($location_search_text,$listing['id']));
        }
        $count = $_POST['start'] + count($listings);
        $return = array(
            'rebuilt'=>(int) $count,
            'percent'=>(!$listings ? 100 : floor(($count*100) / $db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS))),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );
        echo json_encode($return);
        break;
    case 'rewrite':
        echo Strings::rewrite($_POST['text']);
        break;
    case 'admin_search_log_export':
        $PMDR->loadLanguage(array('admin_search_log'));
        $PMDR->get('Authentication')->checkPermission('admin_search_log_view');
        if(((int) $_POST['start']) == 0) {
            @unlink(TEMP_UPLOAD_PATH.'search_log.csv');

            $header = array(
                $PMDR->getLanguage('admin_search_log_keywords'),
                $PMDR->getLanguage('admin_search_log_count'),
                $PMDR->getLanguage('admin_search_log_results'),
                $PMDR->getLanguage('admin_search_log_ip'),
                $PMDR->getLanguage('admin_search_log_date'),
                $PMDR->getLanguage('admin_search_log_parameters')
            );

            $csv_output = "\"".implode('","',$header)."\"\r\n";

            $handle = fopen(TEMP_UPLOAD_PATH.'search_log.csv', 'w');
            fwrite($handle, $csv_output);
            fclose($handle);
        }

        $records = $db->GetAll("SELECT * FROM ".T_SEARCH_LOG." ORDER BY date DESC LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
        $handle = fopen(TEMP_UPLOAD_PATH.'search_log.csv', 'a');
        $count = (int) $_POST['start'];
        $csv_output = '';
        foreach($records AS $record) {
            $output = array(
                $record['keywords'],
                $record['count'],
                $record['results'],
                $record['ip'],
                $record['date']
            );
            $terms_output = '';
            foreach(unserialize($record['terms']) AS $key=>$value) {
                $terms_output .= $key.': '.$value."\n";
            }
            $output[] = $terms_output;

            $csv_output .= '"'.implode('","',$output).'"'."\r\n";
            $count++;
        }
        fwrite($handle, $csv_output);
        fclose($handle);
        usleep(10000);
        $return = array(
            'percent'=>(!$records ? 100 : floor(($count*100) / $db->GetOne("SELECT COUNT(*) FROM ".T_SEARCH_LOG))),
            'exported'=>count($records),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );

        echo json_encode($return);
        break;
    case 'admin_email_log_view':
        $_POST['id'] = ltrim(strrchr($_POST['id'],'_'),'_');
        $PMDR->loadLanguage(array('admin_email','admin_email_log'));
        if($email = $db->GetRow("SELECT el.*, u.user_email FROM ".T_EMAIL_LOG." el INNER JOIN ".T_USERS." u ON el.user_id=u.id WHERE el.id=?",array($_POST['id']))) {
            $template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_log_email.tpl');
            $template_content->set('to',$email['user_email']);
            $template_content->set('subject',$email['subject']);
            $template_content->set('id',$email['id']);
            $template_content->set('body_plain',nl2br($email['body_plain']));
            $template_content->set('body_html',$email['body_html']);
            echo $template_content->render();
        }
        echo '';
        break;
    case 'admin_email_queue_view':
        $PMDR->loadLanguage(array('admin_email','admin_email_queue','email_templates'));
        if($email_preview_template = $PMDR->get('Email_Queue')->getPreview($_POST['id'])) {
            echo $email_preview_template->render();
        }
        echo '';
        break;
    case 'admin_email_templates_show_variables':
        $PMDR->loadLanguage(array('admin_email_templates'));
        $template_side_menu_box = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_side_menu_box.tpl');
        $template_side_menu_box->set('id','email_template_variables');
        $template_side_menu_box->set('title',$PMDR->getLanguage('admin_email_templates_variables'));
        $template_side_menu_box->set('content',$PMDR->get('Email_Templates')->getVariablesTemplate(null,$_POST['type']));
        echo $template_side_menu_box->render();
        break;
    case 'admin_check_table':
        usleep(50000);
        $check = $db->GetRow("CHECK TABLE ".$_POST['table']);
        echo $check['Msg_text'];
        break;
    case 'admin_calendar':
        $PMDR->loadLanguage('admin_calendar');
        $orders_due = $db->GetAll("SELECT id, order_id, DATE(next_due_date) AS date FROM ".T_ORDERS." WHERE UNIX_TIMESTAMP(next_due_date) BETWEEN ? AND ?",array($_POST['start'],$_POST['end']));
        $data = array();
        $id = 1;
        foreach($orders_due AS $order_due) {
            $data[] = array (
                'id'=>$id,
                'title'=>$PMDR->getLanguage('admin_calendar_order_due').' (ID: '.$order_due['id'].')',
                'start'=>$order_due['date'],
                'end'=>$order_due['date'],
                'allDay'=>true,
                'url'=>BASE_URL_ADMIN.'/admin_orders.php?id='.$order_due['id']
            );
            $id++;
        }
        $invoices_due = $db->GetAll("SELECT id, DATE(date_due) AS date FROM ".T_INVOICES." WHERE UNIX_TIMESTAMP(date_due) BETWEEN ? AND ?",array($_POST['start'],$_POST['end']));
        foreach($invoices_due AS $invoice_due) {
            $data[] = array (
                'id'=>$id,
                'title'=>$PMDR->getLanguage('admin_calendar_invoice_due').' (ID: '.$invoice_due['id'].')',
                'start'=>$invoice_due['date'],
                'end'=>$invoice_due['date'],
                'allDay'=>true,
                'url'=>BASE_URL_ADMIN.'/admin_invoices.php?id='.$invoice_due['id']
            );
            $id++;
        }
        if(ADDON_DISCOUNT_CODES) {
            $discount_codes = $db->GetAll("SELECT id, code, DATE(date_start) AS date_start, DATE(date_expire) AS date_expire FROM ".T_DISCOUNT_CODES." WHERE UNIX_TIMESTAMP(date_start) BETWEEN ? AND ? OR UNIX_TIMESTAMP(date_expire) BETWEEN ? AND ?",array($_POST['start'],$_POST['end'],$_POST['start'],$_POST['end']));
            foreach($discount_codes AS $discount_code) {
                $data[] = array (
                    'id'=>$id,
                    'title'=>$PMDR->getLanguage('admin_calendar_discount_active').' ('.$discount_code['code'].')',
                    'start'=>$discount_code['date_start'],
                    'end'=>$discount_code['date_expire'],
                    'allDay'=>true,
                    'url'=>BASE_URL_ADMIN.'/admin_discount_codes.php?action=edit&id='.$discount_code['id']
                );
                $id++;
            }
        }
        if(ADDON_BLOG) {
            $blog_posts = $db->GetAll("SELECT id, title, date_publish FROM ".T_BLOG." WHERE UNIX_TIMESTAMP(date_publish) BETWEEN ? AND ?",array($_POST['start'],$_POST['end']));
            foreach($blog_posts AS $blog_post) {
                $data[] = array (
                    'id'=>$id,
                    'title'=>$PMDR->getLanguage('admin_calendar_blog_published').' ('.$blog_post['id'].')',
                    'start'=>$blog_post['date_publish'],
                    'end'=>$blog_post['date_publish'],
                    'allDay'=>true,
                    'url'=>BASE_URL_ADMIN.'/admin_blog.php?action=edit&id='.$blog_post['id']
                );
                $id++;
            }
        }
        $email_campaigns = $db->GetAll("SELECT id, title, date_sent FROM ".T_EMAIL_CAMPAIGNS." WHERE UNIX_TIMESTAMP(date_sent) BETWEEN ? AND ?",array($_POST['start'],$_POST['end']));
        foreach($email_campaigns AS $email_campaign) {
            $data[] = array (
                'id'=>$id,
                'title'=>$PMDR->getLanguage('admin_calendar_email_campaign_sent').' ('.$email_campaign['id'].')',
                'start'=>$email_campaign['date_sent'],
                'end'=>$email_campaign['date_sent'],
                'allDay'=>true,
                'url'=>BASE_URL_ADMIN.'/admin_email_campaigns.php?action=edit&id='.$email_campaign['id']
            );
            $id++;
        }
        echo json_encode($data);
        break;
    case 'admin_categories_export':
        $PMDR->loadLanguage(array('admin_categories'));
        $PMDR->get('Authentication')->checkPermission('admin_categories_edit');

        if(((int) $_POST['start']) == 0) {
            $PMDR->get('Categories')->exportInitialize();
        }

        $percent = $PMDR->get('Categories')->exportProcess($_POST['start'],$_POST['num']);

        $return = array(
            'percent'=>$percent,
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );

        echo json_encode($return);
        break;
    case 'admin_classifieds_categories_export':
        $PMDR->loadLanguage(array('admin_classifieds_categories'));

        if(((int) $_POST['start']) == 0) {
            $PMDR->get('Classifieds_Categories')->exportInitialize();
        }

        $percent = $PMDR->get('Classifieds_Categories')->exportProcess($_POST['start'],$_POST['num']);

        $return = array(
            'percent'=>$percent,
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );

        echo json_encode($return);
        break;
    case 'admin_locations_export':
        $PMDR->loadLanguage(array('admin_locations'));
        $PMDR->get('Authentication')->checkPermission('admin_locations_edit');

        if(((int) $_POST['start']) == 0) {
            @unlink(TEMP_UPLOAD_PATH.'locations_export.csv');
            $location_labels = $PMDR->get('Locations')->getLevelLabels();
            $csv_output = "\"".implode('","',$location_labels)."\"\r\n";
            $handle = fopen(TEMP_UPLOAD_PATH.'locations_export.csv', 'w');
            fwrite($handle, $csv_output);
            fclose($handle);
        }

        $records = $db->GetAll("SELECT * FROM ".T_LOCATIONS." WHERE id!=1 ORDER BY left_ ASC LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
        $handle = fopen(TEMP_UPLOAD_PATH.'locations_export.csv', 'a');
        $count = (int) $_POST['start'];
        $csv_output = '';
        foreach($records AS $record) {
            $location_path = $PMDR->get('Locations')->getPath($record['id']);
            $output = array();
            foreach($location_path AS $location) {
                $output[] = $location['title'];
            }
            $csv_output .= '"'.implode('","',$output).'"'."\r\n";
            $count++;
        }
        fwrite($handle, $csv_output);
        fclose($handle);
        usleep(10000);
        $return = array(
            'percent'=>(!$records ? 100 : floor(($count*100) / ($db->GetOne("SELECT COUNT(*) FROM ".T_LOCATIONS) - 1))),
            'exported'=>count($records),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num']
        );

        echo json_encode($return);
        break;
    case 'admin_transactions_export':
        $PMDR->loadLanguage(array('admin_transactions'));
        $PMDR->get('Authentication')->checkPermission('admin_transactions');

        if(((int) $_POST['start']) == 0) {
            @unlink(TEMP_UPLOAD_PATH.'transactions_export.csv');
            $columns = array(
                $PMDR->getLanguage('admin_transactions_userid'),
                $PMDR->getLanguage('admin_transactions_userid'),
                $PMDR->getLanguage('admin_transactions_gateway'),
                $PMDR->getLanguage('admin_transactions_id'),
                $PMDR->getLanguage('admin_transactions_invoice_id'),
                $PMDR->getLanguage('admin_transactions_date'),
                $PMDR->getLanguage('admin_transactions_description'),
                $PMDR->getLanguage('admin_transactions_amount')
            );
            $csv_output = "\"".implode('","',$columns)."\"\r\n";
            $handle = fopen(TEMP_UPLOAD_PATH.'transactions_export.csv', 'w');
            fwrite($handle, $csv_output);
            fclose($handle);
        }
        $where = '';
        if(isset($_POST['year']) AND intval($_POST['year']) > 0) {
            $where = "WHERE YEAR(t.date) = ".$PMDR->get('Cleaner')->clean_db($_POST['year']);
        }
        $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS t.user_id, u.login AS user, t.gateway_id, t.transaction_id, t.invoice_id, t.date, t.description, t.amount, u.user_first_name, u.user_last_name FROM ".T_TRANSACTIONS." t INNER JOIN ".T_USERS." u ON t.user_id=u.id $where ORDER BY t.date ASC LIMIT ?,?",array((int) $_POST['start'],(int) $_POST['num']));
        $handle = fopen(TEMP_UPLOAD_PATH.'transactions_export.csv', 'a');
        $count = (int) $_POST['start'];
        $csv_output = '';
        foreach($records AS $record) {
            $record['user'] = trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
            $record['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
            unset($record['user_first_name'],$record['user_last_name']);
            $csv_output .= '"'.implode('","',$record).'"'."\r\n";
            $count++;
        }
        fwrite($handle, $csv_output);
        fclose($handle);
        usleep(10000);
        $return = array(
            'percent'=>(!$records ? 100 : floor(($count*100) / $db->GetOne("SELECT COUNT(*) FROM ".T_TRANSACTIONS))),
            'exported'=>count($records),
            'start'=>(int) $_POST['start'],
            'num'=>(int) $_POST['num'],
            'year'=>(int) $_POST['year']
        );

        echo json_encode($return);
        break;
    case 'random_string':
        if(!isset($_POST['length']) OR !$_POST['length']) {
            $_POST['length'] = 10;
        }
        echo Strings::random($_POST['length'],false,true,true);
        break;
    case 'admin_link_checker_check':
        $PMDR->loadLanguage(array('admin_link_checker'));
        $PMDR->get('Authentication')->checkPermission('admin_link_checker_check');

        if($count_total = $PMDR->get('LinkChecker')->getFailedCount()) {
            $count = (int) $_POST['start'];
            $results = $PMDR->get('LinkChecker')->CheckLinks($_POST['num'],0,0,1,$count);
            $count += $results['processed'];
            $return = array(
                'percent'=>floor(($count*100) / $count_total),
                'start'=>(int) $_POST['start'],
                'num'=>(int) $_POST['num']
            );
        } else {
            $return = array(
                'percent'=>100
            );
        }
        echo json_encode($return);
        break;
    case 'view_listing_summary':
        $template = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_listings_preview.tpl');
        if($listing = $db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=?",array($_POST['id']))) {
            foreach($listing AS $key=>$value) {
                $template->set($key,$value);
            }
            echo $template->render();
        }
        break;
    case 'user_preview':
        $PMDR->loadLanguage(array('admin_users'));
        $template = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_preview.tpl');
        $user = $db->GetRow("SELECT * FROM ".T_USERS." WHERE id=?",array($_POST['id']));
        foreach($user AS $key=>$value) {
            $template->set($key,$value);
        }
        echo $template->render();
        break;
    case 'pricing_free':
        echo $db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS_PRICING." WHERE id=? AND price!=0.00",array($_POST['id']));
        break;
    case 'admin_generate_api_key':
        $user = $db->GetRow("SELECT id, login FROM ".T_USERS." WHERE id=?",array($_POST['id']));
        $username = hash('sha256',$user['login'].SECURITY_KEY);
        $password_show = hash('sha256',md5(uniqid(rand(), true)).SECURITY_KEY);
        $password_store = hash('sha256',$password_show.SECURITY_KEY);
        $db->Execute("REPLACE INTO ".T_USERS_API_KEYS." (user_id,username,password) VALUES (?,?,?)",array($user['id'],$username,$password_store));
        echo $password_show;
        break;
    case 'admin_banner_types_character_limit':
        echo $db->GetOne("SELECT character_limit FROM ".T_BANNER_TYPES." WHERE id=?",array($_POST['id']));
        break;
    case 'admin_user_groups_warning':
        echo $db->GetOne("SELECT COUNT(*) FROM ".T_USERS_GROUPS." WHERE administrator=1 AND id=?",array($_POST['id']));
        break;
}
?>