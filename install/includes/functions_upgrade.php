<?php
function syncEmailTemplates() {
    global $db, $PMDR;
    $templates = include(PMDROOT.'/install/database/email_templates.php');
    $existing_templates = $db->GetAssoc("SELECT id, type, from_address, from_name, reply_name, reply_address, custom FROM ".T_EMAIL_TEMPLATES);
    foreach($templates AS $template_id=>$template) {
        if(!isset($existing_templates[$template_id])) {
            $db->Execute("INSERT INTO ".T_EMAIL_TEMPLATES." (id, type, from_name, reply_name, reply_address) VALUES(?,?,?,?,?)",$template);
        } else {
            $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET type=?, from_name=?, reply_name=?, reply_address=? WHERE id=?",
            array($template['type'],$template['from_name'],$template['reply_name'],$template['reply_address'],$template_id));
        }
    }
    foreach($existing_templates AS $template_id=>$template) {
        if(!isset($templates[$template_id]) AND $template['custom'] == 0) {
            $PMDR->get('Email_Templates')->delete($template_id);
        }
    }
}

function syncSettings() {
    global $db;
    if(!$settings = include(PMDROOT.'/install/database/settings.php')) {
        return false;
    }
    $existing_settings = $db->GetAssoc("SELECT varname, grouptitle, value, optioncode, optioncode_type, optioncode_parse_type, validationcode FROM ".T_SETTINGS);
    foreach($settings AS $setting_id=>$setting) {
        if(!isset($existing_settings[$setting_id])) {
            $db->Execute("INSERT INTO ".T_SETTINGS." (varname, grouptitle, value, optioncode, optioncode_type, optioncode_parse_type, validationcode) VALUES (?,?,?,?,?,?,?)",$setting);
        } else {
            $db->Execute("UPDATE ".T_SETTINGS." SET grouptitle=?, optioncode=?, optioncode_type=?, optioncode_parse_type=?, validationcode=? WHERE varname=?",
            array($setting['grouptitle'],$setting['optioncode'],$setting['optioncode_type'],$setting['optioncode_parse_type'],$setting['validationcode'],$setting_id));
        }
    }
    $settings_group_ignore = $db->GetCol("SELECT id FROM ".T_PLUGINS);
    $settings_group_ignore_query = '';
    if(count($settings_group_ignore)) {
        $settings_group_ignore_query = "AND grouptitle NOT IN('".implode("','",$settings_group_ignore)."')";
    }
    foreach($existing_settings AS $setting_id=>$setting) {
        if(!isset($settings[$setting_id])) {
            $db->Execute("DELETE FROM ".T_SETTINGS." WHERE varname=? AND grouptitle NOT LIKE 'custom%' $settings_group_ignore_query",array($setting_id));
        }
    }
}

function syncLanguage() {
    global $db;

    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."temp_language_phrases");
    $language_table_structure = include(PMDROOT.'/install/database/structure.php');
    $language_table_structure = $language_table_structure['language_phrases'];
    $charset = defined('DB_CHARSET') ? DB_CHARSET : '';
    $collate = defined('DB_COLLATE') ? DB_COLLATE : '';
    createTable(DB_TABLE_PREFIX.'temp_language_phrases', $language_table_structure, $charset, $collate);
    loadPhrases(DB_TABLE_PREFIX.'temp_');

    $ignore_sections = $db->GetCol("SELECT id FROM ".T_PLUGINS);
    $ignore_sections[] = 'custom';
    $ignore_sections[] = 'setting_custom';

    $ignore_phrases = array();
    if($labels = $db->GetCol("SELECT variablename FROM ".T_LANGUAGE_PHRASES." WHERE variablename LIKE 'general_locations_levels_%' OR variablename LIKE 'general_categories_levels_%'")) {
        $ignore_phrases = array_merge($ignore_phrases,$labels);
    }

    $email_template_parts = array(
        'name',
        'subject',
        'body_html',
        'body_plain'
    );
    if($email_templates = $db->GetCol("SELECT CONCAT('email_templates_',id,'_') FROM ".T_EMAIL_TEMPLATES." WHERE custom=1")) {
        $ignore_phrases_sql = '';
        foreach($email_templates AS $email_template) {
            foreach($email_template_parts AS $email_template_part) {
                $ignore_phrases[] = $email_template.$email_template_part;
            }
        }
        $ignore_phrases_sql = " AND l1.variablename NOT IN ('".implode("','",$ignore_phrases)."')";
    }
    $db->Execute("DELETE l1.* FROM ".DB_TABLE_PREFIX."language_phrases l1 LEFT JOIN ".DB_TABLE_PREFIX."temp_language_phrases l2 ON l1.section=l2.section AND l1.variablename=l2.variablename WHERE l2.variablename IS NULL AND l1.section NOT IN ('".implode("','",$ignore_sections)."') $ignore_phrases_sql");
    $db->Execute("INSERT INTO ".DB_TABLE_PREFIX."language_phrases (section,variablename,content,updated) SELECT section, variablename, content, 1 FROM ".DB_TABLE_PREFIX."temp_language_phrases WHERE (section,variablename) NOT IN (SELECT section,variablename FROM ".DB_TABLE_PREFIX."language_phrases)");
    $db->Execute("UPDATE ".DB_TABLE_PREFIX."language_phrases l1, ".DB_TABLE_PREFIX."temp_language_phrases l2 SET l1.content=l2.content, l1.updated=1 WHERE l1.section=l2.section AND l1.variablename=l2.variablename AND BINARY l1.content != BINARY l2.content AND l1.languageid=-1");
    $db->Execute("DROP TABLE ".DB_TABLE_PREFIX."temp_language_phrases");
}

function syncPermissions() {
    global $db;
    $permissions = include(PMDROOT.'/install/database/users_permissions.php');
    $db->Execute("DELETE FROM ".T_USERS_PERMISSIONS);
    $permission_sql = '';
    foreach($permissions AS $permission) {
        $permission_sql .= "(".$db->clean($permission)."),";
    }
    $db->Execute("INSERT INTO ".T_USERS_PERMISSIONS." (id) VALUES ".rtrim($permission_sql,','));
    $db->Execute("DELETE FROM ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." WHERE permission_id NOT IN (SELECT id FROM ".T_USERS_PERMISSIONS.")");
}

function generateUpgradeQueue($version) {
    global $db;

    $queue = array();

    $versions = array();
    $files = scandir(PMDROOT.'/install/upgrade/');
    foreach($files AS $file) {
        if(!preg_match('/(\d\-\d\-\d)b?\.php/',$file,$match)) continue;
        $new_version = str_replace('-','.',$match[1]);
        if(version_compare($version,$new_version,'<=')) {
            $versions[] = $match[1];
        }
    }

    foreach($versions AS $new_version) {
        $queue[] = array(
            'type'=>'file',
            'file_name'=>PMDROOT.'/install/upgrade/'.$new_version.'.php',
            'function'=>'presync_'.str_replace('-','_',$new_version)
        );
    }

    $queue = array_merge($queue,generateStructureDifference());

    foreach($versions AS $new_version) {
        $queue[] = array(
            'type'=>'file',
            'file_name'=>PMDROOT.'/install/upgrade/'.$new_version.'.php',
            'function'=>'postsync_'.str_replace('-','_',$new_version)
        );
    }

    $queue = array_merge($queue,generateQueueFunctions());

    return $queue;
}

function generateQueueFunctions() {
    $queue = array();
    $queue[] = array('type'=>'function','name'=>'syncEmailTemplates','message'=>'Updating email templates');
    $queue[] = array('type'=>'function','name'=>'syncSettings','message'=>'Updating settings');
    $queue[] = array('type'=>'function','name'=>'syncLanguage','message'=>'Updating language phrases');
    $queue[] = array('type'=>'function','name'=>'syncPermissions','message'=>'Updating permissions');
    return $queue;
}

function generateStructureDifference() {
    global $db;
    $structure = include(PMDROOT.'/install/database/structure.php');
    $current = $db->convertToArray(DB_TABLE_PREFIX);
    $difference = structureDifference($structure,$current,DB_TABLE_PREFIX);
    $queue = array();
    foreach($difference['tables'] AS $table) {
        $queue[] = array(
            'type'=>'table',
            'name'=>DB_TABLE_PREFIX.$table,
            'structure'=>$structure[$table],
            'charset'=>DB_CHARSET,
            'collation'=>''
        );
        if(defined('DB_COLLATE')) {
            $queue['collation'] = DB_COLLATE;
        }
    }
    foreach($difference['fields'] AS $action=>$fields) {
        foreach($fields AS $field) {
            $queue[] = array(
                'type'=>'field',
                'action'=>$action,
                'table'=>DB_TABLE_PREFIX.$field['table'],
                'field'=>$field['field'],
                'field_data'=>$structure[$field['table']]['fields'][$field['field']]
            );
        }
    }
    foreach($difference['keys'] AS $action=>$keys) {
        foreach($keys AS $key) {
            $queue[] = array(
                'type'=>'key',
                'action'=>$action,
                'table'=>DB_TABLE_PREFIX.$key['table'],
                'key'=>$key['key'],
                'key_data'=>$structure[$key['table']]['keys'][$key['key']]
            );
        }
    }
    return $queue;
}

function structureDifference($structure, $old_structure, $prefix) {
    $difference = array(
        'tables'=>array(),
        'fields'=>array(),
        'keys'=>array()
    );
    foreach($structure AS $table_name=>$table) {
        if(isset($old_structure[$table_name])) {
            $previous_field = null;
            foreach($table['fields'] AS $field_name=>$field) {
                if(isset($old_structure[$table_name]['fields'][$field_name])) {
                    foreach($field AS $attribute=>$attribute_value) {
                        if($attribute_value !== $old_structure[$table_name]['fields'][$field_name][$attribute]) {
                            $difference['fields']['change'][] = array(
                                'table'=>$table_name,
                                'field'=>$field_name
                            );
                            continue;
                        }
                    }
                } else {
                    $difference['fields']['add'][] = array(
                        'table'=>$table_name,
                        'field'=>$field_name,
                        'after'=>$previous_field
                    );
                }
                $previous_field = $field_name;
            }
            if(isset($table['keys']) AND count($table['keys'])) {
                foreach($table['keys'] AS $key_name=>$key) {
                    if(isset($old_structure[$table_name]['keys'][$key_name])) {
                        if($key['type'] != $old_structure[$table_name]['keys'][$key_name]['type']) {
                            $difference['keys']['change'][] = array(
                                'table'=>$table_name,
                                'key'=>$key_name
                            );
                        } elseif(count(array_diff($key['fields'],$old_structure[$table_name]['keys'][$key_name]['fields']))) {
                            $difference['keys']['change'][] = array(
                                'table'=>$table_name,
                                'key'=>$key_name
                            );
                        }
                    } else {
                        $difference['keys']['add'][] = array(
                            'table'=>$table_name,
                            'key'=>$key_name
                        );
                    }
                }
            }
        } else {
            $difference['tables'][] = $table_name;
        }
    }
    foreach($old_structure AS $table_name=>$table) {
        if(isset($structure[$table_name]) AND isset($table['keys']) AND count($table['keys'])) {
            foreach($table['keys'] AS $key_name=>$key) {
                if(!isset($structure[$table_name]['keys'][$key_name])) {
                    $difference['keys']['drop'][] = array(
                        'table'=>$table_name,
                        'key'=>$key_name
                    );
                }
            }
        }
    }
    return $difference;
}

function processQueueItem($queue_item) {
    global $db;
    $message = '';
    switch($queue_item['type']) {
        case 'file':
            if(strstr($queue_item['function'],'presync')) {
                $message = 'Processing pre-sync '.str_replace('-','.',basename($queue_item['file_name'],".php")).' changes';
            } else {
                $message = 'Processing post-sync '.str_replace('-','.',basename($queue_item['file_name'],".php")).' changes';
            }
            require($queue_item['file_name']);
            if(function_exists($queue_item['function'])) {
                $queue_item['function']();
            }
            break;
        case 'table':
            $message = 'Creating table "'.$queue_item['name'].'"';
            createTable($queue_item['name'],$queue_item['structure'],$queue_item['charset'],$queue_item['collation']);
            break;
        case 'field':
            if($queue_item['action'] == 'add') {
                $message = 'Adding database field "'.$queue_item['field'].'" in table "'.$queue_item['table'].'"';
                $db->AddColumn($queue_item['table'], $queue_item['field'], $queue_item['field_data']['type'], $queue_item['field_data']['null'], $queue_item['field_data']['default'], $queue_item['field_data']['extra'], $queue_item['field_data']['after']);
            } elseif($queue_item['action'] == 'change') {
                $message = 'Updating database field "'.$queue_item['field'].'" in table "'.$queue_item['table'].'"';
                $db->ChangeColumn($queue_item['table'], $queue_item['field'], $queue_item['field_data']['type'], $queue_item['field_data']['null'], $queue_item['field_data']['default'], $queue_item['field_data']['extra']);
            }
            break;
        case 'key':
            if($queue_item['action'] == 'add' OR $queue_item['action'] == 'change') {
                $message = 'Adding database index "'.implode(',',$queue_item['key_data']['fields']).'" in table "'.$queue_item['table'].'"';
                $db->AddIndex($queue_item['table'], $queue_item['key_data']['fields'], $queue_item['key_data']['type'], $queue_item['key']);
            } elseif($queue_item['action'] == 'drop') {
                $message = 'Dropping index "'.$queue_item['key'].'" in table "'.$queue_item['table'].'"';
                $db->DropIndex($queue_item['table'],$queue_item['key']);
            }
            break;
        case 'function':
            if(function_exists($queue_item['name'])) {
                $queue_item['name']();
                $message = $queue_item['message'];
            }
            break;
        default:
            $message = 'Unknown queue item type "'.$queue_item['type'].'" with debug data: '.print_r($queue_item,true);
            break;
    }
    return $message;
}
?>