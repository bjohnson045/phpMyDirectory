<?php
/**
* Imports
*
* Import CSV files for listings, categories locations
*/
class Imports extends TableGateway{
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Imports contructor
    * @param object $PMDR
    * @return Imports
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_IMPORTS;
    }

    /**
    * Insert an import into the database
    * @param mixed $data
    */
    function insert($data) {
        $this->db->Execute("INSERT INTO ".T_IMPORTS." (name,delimiter,encapsulator,map,data,date,rate,scheduled,notifications) VALUES (?,?,?,?,?,NOW(),100,?,?)",array($data['name'],$data['delimiter'],$data['encapsulator'],serialize($data['map']),serialize($data['data']),$data['scheduled'],$data['notifications']));
        return $this->db->Insert_ID();
    }

    /**
    * Increment the number of records imported
    * @param int $id
    * @param int $add
    */
    function increaseCount($id, $add  = 1) {
        $this->db->Execute("UPDATE LOW_PRIORITY ".T_IMPORTS." SET import_count=import_count+? WHERE id=?",array($add,$id));
    }

    /**
    * Increment the number of errors during an import
    * @param int $id
    * @param int $add
    */
    function increaseErrorCount($id, $add) {
        $this->db->Execute("UPDATE LOW_PRIORITY ".T_IMPORTS." SET error_count=error_count+? WHERE id=?",array($add,$id));
    }

    /**
    * Clear the data of an import
    * @param int $id
    */
    function clear($id) {
        // Delete orders, listings, invoices
        $imported_orders = $this->db->GetAll("SELECT id FROM ".T_ORDERS." WHERE import_id=?",array($id));
        foreach($imported_orders as $order) {
            $this->PMDR->get('Orders')->delete($order['id']);
            $invoices = $this->db->GetAll("SELECT id FROM ".T_INVOICES." WHERE order_id=?",array($order['id']));
            foreach($invoices AS $invoice) {
                $this->db->Execute("DELETE FROM ".T_INVOICES." WHERE id=?",array($invoice['id']));
                $this->db->Execute("DELETE FROM ".T_TRANSACTIONS." WHERE invoice_id=?",array($invoice['id']));
            }
        }

        // Delete users
        $users = $this->PMDR->get('Users');
        $imported_users = $this->db->GetAll("SELECT id FROM ".T_USERS." WHERE import_id=?",array($id));
        foreach($imported_users as $user) {
            $users->delete($user['id']);
        }

        // Delete categories
        $categories = $this->PMDR->get('Categories');
        $imported_categories = $this->db->GetAll("SELECT id FROM ".T_CATEGORIES." WHERE import_id=?",array($id));
        foreach($imported_categories as $category) {
            $categories->delete($category['id']);
        }

        // Delete locations
        $locations = $this->PMDR->get('Locations');
        $imported_locations = $this->db->GetAll("SELECT id FROM ".T_LOCATIONS." WHERE import_id=?",array($id));
        foreach($imported_locations as $location) {
            $locations->delete($location['id']);
        }
    }

    /**
    * Delete an import
    * @param int $id
    */
    function delete($id) {
        // Delete import record and file
        parent::delete($id);
        unlink_file(TEMP_UPLOAD_PATH.'import_'.$id.'.csv');
        unlink_file(TEMP_UPLOAD_PATH.'import_'.$id.'_log.csv');
        // Clear all records
        $this->clear($id);
    }

    /**
    * Check the integrity of a delimiter and file
    * @param string $file
    * @param string $delimiter
    * @param string $encapsulator
    * @param int $expected_count
    */
    function checkDelimiter($file, $delimiter, $encapsulator, $expected_count = null) {
        if(!$handle = fopen($file,'r')) {
            return false;
        }
        if(empty($encapsulator)) {
            $encapsulator = '"';
        }
        $header = fgetcsv($handle, 0, $delimiter, $encapsulator);
        fclose($handle);
        if($header) {
            if(!is_null($expected_count)) {
                if(count($header) >= $expected_count) {
                    return true;
                }
            } else {
                if(count($header) > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Run a scheduled import
    * @return mixed Boolean false on no run, import information on success
    */
    function runScheduled() {
        // Cancel any abandoned imports (no acivity within 24 hours)
        $this->db->Execute("UPDATE ".T_IMPORTS." SET status='failed' WHERE (status='running' OR status='pending') AND date_activity < DATE_SUB(NOW(),INTERVAL 1 DAY)");

        // Let's make sure another import is not running for some reason
        // This should never happen since CRON runs consecutively but it is a nice check to be sure.
        if(!$import = $this->db->GetRow("SELECT * FROM ".T_IMPORTS." WHERE status='running' AND scheduled=1")) {
            $import = $this->db->GetRow("SELECT * FROM ".T_IMPORTS." WHERE status='pending' AND scheduled=1");
        }
        if($import) {
            // Change it to running so we know which one is currently running
            if($import['status'] == 'pending') {
                $this->db->Execute("UPDATE ".T_IMPORTS." SET status='running' WHERE id=?",array($import['id']));
            }

            return $this->run($import['id']);
        } else {
            return false;
        }
    }

    /**
    * Run an import
    * @param int $id
    */
    function run($id) {
        @ini_set('auto_detect_line_endings',true);

        $import = $this->db->GetRow("SELECT * FROM ".T_IMPORTS." WHERE id=?",array($id));

        if($import['scheduled']) {
            // Figure out the server script timeout value
            $timeout = ini_get('max_execution_time');
            if((int) ini_get('max_execution_time') < 1800) {
                @set_time_limit(1800);
                $timeout = ini_get('max_execution_time');
            }

            // Low timeout values? Lower our rate minute if the timeout value is under 5 minutes
            $rate_minutes = 5; // Default to 5 minutes
            if($timeout / 60 < $rate_minutes) {
                $rate_minutes = $timeout / 60;
            }
            $import['rate'] = $import['rate'] * $rate_minutes;
        }
        $import['data'] = unserialize($import['data']);
        $import['map'] = unserialize($import['map']);

        if(!isset($import['data']['statistics'])) {
            $import['data']['statistics'] = array(
                'orders'=>0,
                'listings'=>0,
                'categories'=>0,
                'locations'=>0,
                'users'=>0,
                'invoices'=>0,
                'errors'=>0
            );
        }

        $file = TEMP_UPLOAD_PATH.'imports_'.$import['id'].'.csv';
        $filesize = filesize($file);

        if(!$handle = fopen($file,'r')) {
            trigger_error('Unable to open import file:'.TEMP_UPLOAD_PATH.'import_'.$id.'.csv');
            return false;
        }

        fseek($handle, $import['position']);

        if($import['position'] == 0) {
            if($import['notifications']) {
                $this->PMDR->get('Email_Templates')->send('admin_import_status',array('variables'=>array('import_name'=>$import['name'],'import_status'=>'Starting')));
            }
            // Remove header column
            fgetcsv($handle, 0, $import['delimiter'], $import['encapsulator']);
            $import['position_line']++;
        }

        $count = 0;
        $message = array();
        $errors = array();
        $time_start = microtime(true);
        $category_cache = array();
        $location_cache = array();
        $user_id_cache = array();
        $user_username_cache = array();
        $product_cache = array();

        $membership_fields = $this->db->MetaColumnNames(T_MEMBERSHIPS);
        unset($membership_fields[0]);

        $time_start = microtime(true);

        while($count < $import['rate'] AND ($data = fgetcsv($handle, 0, $import['delimiter'], $import['encapsulator'])) !== false) {
            if(count($data) == 1 AND is_null($data[0])) {
                $errors[($import['position_line']+$count)][] = 'Blank line';
            }
            // Setup array $input to have a key => value setup based on the selected mapping of fields to columns
            $input = array();
            if(isset($import['map']) AND is_array($import['map'])) {
                foreach($import['map'] as $field=>$index) {
                    $input[$field] = $data[$index];
                }
            }

            /***** IMPORT CATEGORY *****/
            if($import['data']['category_columns']) {
                if(empty($input['category1'])) {
                    $errors[($import['position_line']+$count)][] = 'Empty category';
                }
            }
            $categories = array();
            $parts_count = null;
            $input['categories'] = array();
            for($x=0; $x < $import['data']['category_columns']; $x++) {
                if(trim($input['category'.($x+1)]) == '') break;
                $cats = explode('::',$input['category'.($x+1)]);

                // If we are numeric, no processing is needed, break out of the loop
                if(is_numeric($cats[0])) {
                    $input['categories'] = $cats;
                    break;
                } else {
                    // If we are not consistent in the counts then we break because something is wrong
                    if(($parts_count != count($cats) AND !is_null($parts_count))) {
                        $categories = array();
                        break;
                    }
                    $parts_count = count($cats);
                    foreach($cats as $cat_key=>$cat) {
                        // If we find an empty category then we break because something is wrong
                        // We do not clear $categories because if importing multiple categories per listing
                        if(trim($cat) == '' AND $x == 0) {
                            break;
                        }
                        $categories[$cat_key][] = $cat;
                    }
                }
            }

            if($categories) {
                foreach($categories as $category) {
                    $category_id = 1;
                    foreach($category as $level=>$category_name) {
                        $category_name = trim($category_name);
                        if(isset($category_cache[md5($category_name.$category_id.($level+1))])) {
                            $category_id = $category_cache[md5($category_name.$category_id.($level+1))];
                            $message[] = 'CATEGORY: Existing category found '.$category_name.' (ID: '.$category_id.')';
                        } elseif(!($found_id=$this->PMDR->get('Categories')->getNodeByNameAndParent($category_name,$category_id,($level+1)))) {
                            $new_category = array(
                                'placement'=>'subcategory',
                                'placement_id'=>$category_id,
                                'title'=>$category_name,
                                'friendly_url'=>strval($input['category_friendly_url'.($level+1)]),
                                'description'=>strval($input['category_description'.($level+1)]),
                                'description_short'=>strval($input['category_description_short'.($level+1)]),
                                'keywords'=>strval($input['category_keywords'.($level+1)]),
                                'meta_title'=>strval($input['category_meta_title'.($level+1)]),
                                'meta_description'=>strval($input['category_meta_description'.($level+1)]),
                                'meta_keywords'=>strval($input['category_meta_keywords'.($level+1)]),
                                'import_id'=>$id
                            );
                            if(!empty($input['category_link'.($level+1)])) {
                                if(valid_url(strval($input['category_link'.($level+1)]))) {
                                    $new_category['link'] = strval($input['category_link'.($level+1)]);
                                } else {
                                    $errors[($import['position_line']+$count)][] = 'Invalid category link: '.$input['category_link'.($level+1)];
                                }
                            }
                            $new_id = $this->PMDR->get('Categories')->insert($new_category);
                            $category_cache[md5($category_name.$category_id.($level+1))] = $new_id;
                            $category_id = $new_id;
                            $this->db->Execute("INSERT INTO ".T_CATEGORIES_FIELDS." (category_id,field_id) SELECT ".$category_id.", id FROM ".T_FIELDS);
                            unset($fields_parts);
                            unset($field_id);
                            $message[] = 'CATEGORY: Imported '.$category_name.' (ID: '.$category_id.')';
                            $import['data']['statistics']['categories']++;
                        } else {
                            $category_cache[md5($category_name.$category_id.($level+1))] = $found_id;
                            $category_id = $found_id;
                            $message[] = 'CATEGORY: Existing category found '.$category_name.' (ID: '.$category_id.')';
                        }

                        if((count($category)-1) == $level AND !in_array($category_id,$input['categories'])) {
                            $input['categories'][] = $category_id;
                        }
                    }
                }
                unset($fields);
            }

            /***** IMPORT LOCATION *****/
            if($import['data']['location_columns']) {
                if(empty($input['location1'])) {
                    $errors[($import['position_line']+$count)][] = 'Empty location';
                }
            }

            $current_location_id = 1;

            for($x=0; $x < $import['data']['location_columns']; $x++) {
                $level = $x+1;
                if(($location = trim($input['location'.($level)])) == '') {
                    $message[] = 'LOCATION: Skipping empty location.';
                    break;
                }
                if(is_numeric($location)) {
                    if(isset($location_cache[md5($location.$current_location_id.$level)])) {
                        $current_location_id = $location;
                    } elseif(!$this->db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id=?",array($location))) {
                        $location_cache[md5($location.$current_location_id.$level)] = 1;
                        $current_location_id = 1;
                        break;
                    } else {
                        $location_cache[md5($location.$current_location_id.$level)] = $location;
                        $current_location_id = $location;
                    }
                } else {
                    if(isset($location_cache[md5($location.$current_location_id.$level)])) {
                        $current_location_id = $location_cache[md5($location.$current_location_id.$level)];
                        if($level == $import['data']['location_columns']) {
                            $message[] = 'LOCATION: Duplicate location found '.$location.' (ID: '.$current_location_id.')';
                        } else {
                            $message[] = 'LOCATION: Existing location found '.$location.' (ID: '.$current_location_id.')';
                        }
                    } elseif(!($found_id=$this->PMDR->get('Locations')->getNodeByNameAndParent($location,$current_location_id,$level))) {
                        $new_location = array(
                            'placement'=>'subcategory',
                            'placement_id'=>$current_location_id,
                            'title'=>$location,
                            'friendly_url'=>strval($input['location_friendly_url'.($level+1)]),
                            'description'=>strval($input['location_description'.($level)]),
                            'description_short'=>strval($input['location_description_short'.($level)]),
                            'keywords'=>strval($input['location_keywords'.($level)]),
                            'meta_title'=>strval($input['location_meta_title'.($level)]),
                            'meta_description'=>strval($input['location_meta_description'.($level)]),
                            'meta_keywords'=>strval($input['location_meta_keywords'.($level)]),
                            'import_id'=>$id
                        );
                        if(!empty($input['location_link'.($level)])) {
                            if(valid_url(strval($input['location_link'.($level)]))) {
                                $new_category['link'] = strval($input['location_link'.($level)]);
                            } else {
                                $errors[($import['position_line']+$count)][] = 'Invalid location link: '.$input['location_link'.($level)];
                            }
                        }
                        if($new_id = $this->PMDR->get('Locations')->insert($new_location)) {
                            $location_cache[md5($location.$current_location_id.$level)] = $new_id;
                            $current_location_id = $new_id;
                            $message[] = 'LOCATION: Imported '.$location.' (ID: '.$current_location_id.')';
                            $import['data']['statistics']['locations']++;
                        } else {
                            $message[] = 'LOCATION: Import failed '.$location;
                            break;
                        }
                    } else {
                        $location_cache[md5($location.$current_location_id.$level)] = $found_id;
                        if($level == $import['data']['location_columns']) {
                            $message[] = 'LOCATION: Duplicate location found '.$location.' (ID: '.$current_location_id.')';
                        } else {
                            $message[] = 'LOCATION: Existing location found '.$location.' (ID: '.$found_id.')';
                        }
                        $current_location_id = $found_id;
                    }
                }
            }
            $input['location_id'] = $current_location_id;

            // Setup password, if none entered use "password", encrypt with md5()
            if (trim($input['password']) == '') {
                $input['password'] = Strings::random(12);
            }

            /***** IMPORT USER *****/
            if(trim($input['login']) == '' AND trim($input['title']) != '') {
                do {
                    $rand_str = Strings::random(5);
                    $input['login'] = 'login'.$rand_str;
                } while ($this->db->GetRow("SELECT id FROM ".T_USERS." WHERE login=?",array($input['login'])));

                $user_array = array(
                    'login'=>$input['login'],
                    'user_email'=>(string) $input['mail'],
                    'pass'=>$input['password'],
                    'import_id'=>$import['id'],
                    'user_groups'=>array(4)
                );
                $input['user_id'] = $this->PMDR->get('Users')->insert($user_array);
                if($import['data']['send_registration_email'] AND !empty($input['mail'])) {
                    $this->PMDR->get('Email_Templates')->queue('user_registration',array('to'=>$input['mail'],'variables'=>array('user_password'=>$input['password']),'user_id'=>$input['user_id']));
                }
                $message[] = 'USER: Imported '.$user_array['login'].' - '.$user_array['user_email'].' (ID: '.$input['user_id'].', Password: '.$input['password'].')';
                $import['data']['statistics']['users']++;
            } elseif(trim($input['login']) != '') {
                if(is_numeric($input['login']) AND (isset($user_id_cache[$input['login']]) OR $this->db->GetOne("SELECT COUNT(*) FROM ".T_USERS." WHERE id=?",array($input['login'])))) {
                    $input['user_id'] = $input['login'];
                    $user_id_cache[$input['login']] = true;
                } elseif(isset($user_username_cache[$input['login']])) {
                    $input['user_id'] = $user_username_cache[$input['login']];
                } elseif(!($user_id = $this->db->GetOne("SELECT id FROM ".T_USERS." WHERE login=?",array($input['login'])))) {
                    $user_array = array(
                        'login'=>$input['login'],
                        'user_email'=>(string) $input['mail'],
                        'pass'=>$input['password'],
                        'import_id'=>$import['id'],
                        'user_groups'=>array(4)
                    );
                    $input['user_id'] = $this->PMDR->get('Users')->insert($user_array);
                    $message[] = 'USER: Imported '.$user_array['login'].' - '.$user_array['user_email'].' (ID: '.$input['user_id'].', Password: '.$input['password'].')';
                    if($import['data']['send_registration_email'] AND !empty($input['mail'])) {
                          $this->PMDR->get('Email_Templates')->queue('user_registration',array('to'=>$input['mail'],'variables'=>array('user_password'=>$input['password']),'user_id'=>$input['user_id']));
                    }
                    $import['data']['statistics']['users']++;
                    $user_username_cache[$input['login']] = $input['user_id'];
                } else {
                    $user_username_cache[$input['login']] = $user_id;
                    $input['user_id'] = $user_id;
                }
            }

            /***** IMPORT LISTING *****/
            if(trim($input['title']) == '') {
                $message[] = 'LISTING: NOT IMPORTED - Empty title';
            } elseif($input['location_id'] == '') {
                $message[] = 'LISTING: NOT IMPORTED - Empty location - '.$input['title'];
            } elseif(!count($input['categories'])) {
                $message[] = 'LISTING: NOT IMPORTED - No categories - '.$input['title'];
            } else {
                if(!isset($input['pricing_id']) OR trim($input['pricing_id']) == '' OR !is_numeric($input['pricing_id'])) $input['pricing_id'] = $import['data']['pricing_id'];

                if(!isset($product_cache[$input['pricing_id']][$input['user_id']])) {
                    if(!$product = $this->PMDR->get('Products')->getByPricingID($input['pricing_id'],$input['user_id'])) {
                        $errors[($import['position_line']+$count)][] = 'Invalid pricing ID.';
                        $product = $this->PMDR->get('Products')->getByPricingID($import['data']['pricing_id'],$input['user_id']);
                        $product_cache[$import['data']['pricing_id']][$input['user_id']] = $product;
                    } else {
                        $product_cache[$input['pricing_id']][$input['user_id']] = $product;
                    }
                } else {
                    $product = $product_cache[$input['pricing_id']][$input['user_id']];
                }
                if(!$product) {
                    $message[] = 'LISTING: NOT IMPORTED - Bad product ID - '.$input['title'];
                } else {
                    if($input['www'] != '' AND !valid_url($input['www'])) {
                        $errors[($import['position_line']+$count)][] = 'Invalid URL';
                    }

                    $listing_array = array(
                        'user_id'=>$input['user_id'],
                        'title'=>$input['title'],
                        'friendly_url'=>(string) strtolower(((isset($input['friendly_url']) AND $input['friendly_url'] != '') ? Strings::rewrite($input['friendly_url']) : Strings::rewrite($input['title']))),
                        'description'=>(string) Strings::limit_characters($input['description'],$product['description_size']),
                        'description_short'=>(string) Strings::limit_characters($input['description_short'],$product['short_description_size']),
                        'meta_description'=>(string) $input['meta_description'],
                        'meta_keywords'=>(string) $input['meta_keywords'],
                        'keywords'=>(string) $input['keywords'],
                        'location_id'=>(int) $input['location_id'],
                        'phone'=>(string) $input['phone'],
                        'fax'=>(string) $input['fax'],
                        'listing_address1'=>(string) $input['listing_address1'],
                        'listing_address2'=>(string) $input['listing_address2'],
                        'listing_zip'=>(string) $input['listing_zip'],
                        'location_text_1'=>(string) $input['location_text_1'],
                        'location_text_2'=>(string) $input['location_text_2'],
                        'location_text_3'=>(string) $input['location_text_3'],
                        'latitude'=>(float) $input['latitude'],
                        'longitude'=>(float) $input['longitude'],
                        'www'=>standardize_url((string) $input['www']),
                        'ip'=>get_ip_address(),
                        'date'=>$this->PMDR->get('Dates')->dateTimeNow(),
                        'mail'=>(string) $input['mail'],
                        'primary_category_id'=>(int) $input['categories'][0],
                        'comment'=>(string) $input['comment'],
                        'facebook_page_id'=>(string) $input['facebook_page_id'],
                        'twitter_id'=>(string) $input['twitter_id'],
                        'google_page_id'=>(string) $input['google_page_id'],
                        'linkedin_id'=>(string) $input['linkedin_id'],
                        'linkedin_company_id'=>(string) $input['linkedin_company_id'],
                        'pinterest_id'=>(string) $input['pinterest_id'],
                        'youtube_id'=>(string) $input['youtube_id'],
                        'foursquare_id'=>(string) $input['foursquare_id'],
                        'instagram_id'=>(string) $input['instagram_id'],
                        'status'=>(((($product['total'] == 0.00 OR !$import['data']['create_invoice']) AND $product['activate'] == 'payment') OR $product['activate'] == 'immediate' OR ($product['activate'] == 'approved' AND $input['status'] != 'pending')) ? 'active' : 'pending')
                    );
                    if(!isset($input['claimed']) OR !intval($input['claimed'])) {
                        $listing_array['claimed'] = 0;
                    } else {
                        $listing_array['claimed'] = 1;
                    }
                    if(!empty($input['www'])) {
                        if(valid_url(strval($input['www']))) {
                            $listing_array['www'] = standardize_url(strval($input['www']));
                        } else {
                            $errors[($import['position_line']+$count)][] = 'Invalid website URL: '.$input['www'];
                        }
                    }
                    if(isset($input['logo_url']) AND !empty($input['logo_url'])) {
                        if(valid_url($input['logo_url'])) {
                            $listing_array['logo'] = (string) $input['logo_url'];
                        } else {
                            $errors[($import['position_line']+$count)][] = 'Invalid Logo URL';
                        }
                    }

                    $listing_array['location_search_text'] = trim(preg_replace('/,+/',',',$this->PMDR->get('Locations')->getPathString($input['location_id']).','.$input['listing_address1'].','.$input['listing_address2'].','.$input['location_text_1'].','.$input['location_text_2'].','.$input['location_text_3'].','.$input['listing_zip'].','.$this->PMDR->getConfig('map_city_static').','.$this->PMDR->getConfig('map_state_static').','.$this->PMDR->getConfig('map_country_static')),',');

                    foreach($membership_fields as $field) {
                        if(strstr($field,'custom_')) {
                            $listing_array[str_replace('_allow','',$field)] = (string) $input[str_replace('_allow','',$field)];
                            $listing_array[$field] = (string) $product[$field];
                        } else {
                            $listing_array[$field] = (string) $product[$field];
                        }
                    }

                    $listing_id = $this->PMDR->get('Listings')->insert($listing_array);
                    $this->PMDR->get('Listings')->updateCategories($listing_id, $input['categories'], $input['categories'][0]);
                    $this->PMDR->get('Listings')->updateMembership($listing_id);
                    $import['data']['statistics']['listings']++;

                    if($product['total'] != 0.00 AND $import['data']['create_invoice']) {
                        $invoice_id = $this->PMDR->get('Invoices')->insert(
                            array(
                                'user_id'=>$input['user_id'],
                                'type'=>$product['type'],
                                'type_id'=>$listing_id,
                                'date_due'=>$product['next_due_date'],
                                'subtotal'=>$product['subtotal'],
                                'tax'=>(float) $product['tax'],
                                'tax_rate'=>(float) $product['tax_rate'],
                                'tax2'=>(float) $product['tax2'],
                                'tax_rate2'=>(float) $product['tax_rate2'],
                                'total'=>$product['total'],
                                'next_due_date'=>$product['future_due_date'],
                                'product_name'=>$product['product_name'],
                                'product_group_name'=>$product['product_group_name'],
                                'product_title'=>$input['title']
                            )
                        );
                        $import['data']['statistics']['invoices']++;
                    }

                    $order_id = $this->PMDR->get('Orders')->insert(
                        array(
                            'order_id'=>$this->PMDR->get('Orders')->getRandomOrderID(),
                            'type'=>$product['type'],
                            'type_id'=>$listing_id,
                            'invoice_id'=>(int) $invoice_id,
                            'user_id'=>$input['user_id'],
                            'date'=>$this->PMDR->get('Dates')->dateTimeNow(),
                            'status'=>((isset($input['status']) AND $input['status'] == 'pending') ? 'pending' : 'active'),
                            'amount_recurring'=>$product['recurring_total'],
                            'period'=>$product['period'],
                            'period_count'=>$product['period_count'],
                            'next_due_date'=>$product['next_due_date'],
                            'next_invoice_date'=>$product['next_invoice_date'],
                            'future_due_date'=>$product['future_due_date'],
                            'pricing_id'=>$product['pricing_id'],
                            'taxed'=>$product['taxed'],
                            'upgrades'=>$product['upgrades'],
                            'renewable'=>$product['renewable'],
                            'suspend_overdue_days'=>$product['suspend_overdue_days'],
                            'ip_address'=>get_ip_address(),
                            'import_id'=>$import['id']
                        )
                    );

                    $import['data']['statistics']['orders']++;

                    $this->PMDR->get('Invoices')->update(array('order_id'=>$order_id),$invoice_id);
                    $message[] = 'LISTING: Imported '.$input['title'].' (ID: '.$listing_id.') with login '.$input['login'];
                    unset($invoice_id);
                    unset($order_id);
                    unset($listing_id);
                }
            }

            $this->PMDR->get('Imports')->increaseCount($import['id']);
            $count++;
        }
        $current = ftell($handle);
        fclose($handle);

        // Get the run time of the import, error up
        $run_time = ceil((microtime(true) - $time_start) / 60);

        if(count($message)) {
            if($log_file = fopen(TEMP_UPLOAD_PATH.'import_'.$id.'_log.txt',($import['position'] == 0 ? 'w' : 'a'))) {
                fwrite($log_file,implode("\r\n",$message)."\r\n");
                fclose($log_file);
            }
        }

        if(count($errors)) {
            if($error_file = fopen(TEMP_UPLOAD_PATH.'import_'.$id.'_errors.txt',($import['position'] == 0 ? 'w' : 'a'))) {
                foreach($errors AS $line=>$error) {
                    foreach($error AS $error_message) {
                        $import['data']['statistics']['errors']++;
                        fwrite($error_file,'Line '.$line.': '.$error_message."\r\n");
                    }
                }
                fclose($error_file);
            }
        }

        $new_rate = $import['rate'];
        if($import['scheduled']) {
            $new_rate = round($import['rate'] / $run_time);
            if($new_rate > 10000) {
                $new_rate = 10000;
            }
        }
        $this->PMDR->get('Imports')->update(array('data'=>serialize($import['data']),'error_count'=>intval($import['data']['statistics']['errors']),'rate'=>$new_rate,'position'=>$current,'position_line'=>($import['position_line']+$count),'date_activity'=>$this->PMDR->get('Dates')->dateTimeNow()),$import['id']);

        $return = array(
            'percent'=>floor(($current*100) / $filesize),
            'statistics'=>$import['data']['statistics'],
            'failed'=>$import['data']['failed']
        );

        if($return['percent'] >= 100) {
            $this->PMDR->get('Products')->syncProducts();
            $this->PMDR->get('Locations')->updateLanguageVariables();
            $this->PMDR->get('Categories')->updateLanguageVariables();
            $this->PMDR->get('Locations')->resetChildRowIDs();
            $this->PMDR->get('Categories')->resetChildRowIDs();
            $this->PMDR->get('Imports')->update(array('status'=>'complete','date_complete'=>$this->PMDR->get('Dates')->dateTimeNow()),$import['id']);
            if($import['notifications']) {
                $this->PMDR->get('Email_Templates')->send('admin_import_status',array('variables'=>array('import_name'=>$import['name'],'import_status'=>'Complete')));
            }
        }

        return $return;
    }
}
?>