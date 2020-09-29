<?php
/**
* Events Class
*/
class Events extends TableGateway {
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
        $this->table = T_EVENTS;
    }

    /**
    * Get an Event by ID
    * @param int $id Event ID
    * @return array
    */
    function getRow($id) {
        $event = $this->db->GetRow("SELECT  e.*, l.title AS listing_title, l.friendly_url AS listing_friendly_url FROM ".T_EVENTS." e LEFT JOIN ".T_LISTINGS." l ON e.listing_id=l.id WHERE e.id=?",array($id));
        $event['categories'] = $this->db->GetCol("SELECT category_id FROM ".T_EVENTS_CATEGORIES_LOOKUP." WHERE event_id=?",array($id));
        return $event;
    }

    /**
    * Get event URL
    * @param int $id
    * @param string $friendly_url
    * @param string $query_string
    * @param string $query_string_rewrite
    * @param string $filename
    * @return string
    */
    function getURL($id, $friendly_url, $query_string='', $query_string_rewrite='.html', $filename='event.php') {
        if(MOD_REWRITE) {
            return BASE_URL_NOSSL.'/event/'.$friendly_url.'-'.$id.$query_string_rewrite;
        } else {
            return BASE_URL_NOSSL.'/'.$filename.'?id='.$id.$query_string;
        }
    }

    /**
    * Insert event
    * @param array $data
    * @return void
    */
    function insert($data) {
        $data['description'] = Strings::limit_characters($data['description'],$this->PMDR->getConfig('event_description_size'));
        $data['website'] = standardize_url($data['website']);
        if($data['recurring_type'] != 'monthly') {
            $data['recurring_monthly'] = '';
        }
        if(!isset($data['date'])) {
            $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        }
        if(!empty($data['location'])) {
            $map = $this->PMDR->get('Map');
            if($coordinates = $map->getGeocode($data['location'])) {
                if(abs($coordinates['lat']) > 0 AND abs($coordinates['lon']) > 0) {
                    $data['latitude'] = $coordinates['lat'];
                    $data['longitude'] = $coordinates['lon'];
                }
            }
        }
        $id = parent::insert($data);
        if(isset($data['image'])) {
            $this->updateLogo($data,$id);
        }
        $this->updateCategories($id,$data['categories']);
        $this->updateRecurring($id);
        return $id;
    }

    /**
    * Update event
    * @param array $data
    * @param int $id
    * @return void
    */
    function update($data, $id) {
        if(isset($data['website'])) {
            $data['website'] = standardize_url($data['website']);
        }
        $data['date_update'] = $this->PMDR->get('Dates')->dateTimeNow();
        if(isset($data['recurring_type']) AND $data['recurring_type'] != 'monthly') {
            $data['recurring_monthly'] = '';
        }
        if($data['delete_image']) {
            @unlink(find_file(EVENT_PATH.$id.'.*'));
            @unlink(find_file(EVENT_THUMB_PATH.$id.'.*'));
        }
        if(isset($data['image'])) {
            $image_handler = $this->PMDR->get('Image_Handler');
            if($image_handler->verifyImage($data['image'])) {
                $data['image_extension'] = $image_handler->file_format;
                $this->updateLogo($data,$id);
            }
        }
        if(!isset($data['recalculate_coordinates']) AND !empty($data['location'])) {
            $map = $this->PMDR->get('Map');
            if($coordinates = $map->getGeocode($data['location'])) {
                if(abs($coordinates['lat']) > 0 AND abs($coordinates['lon']) > 0) {
                    $data['latitude'] = $coordinates['lat'];
                    $data['longitude'] = $coordinates['lon'];
                }
            }
        }
        parent::update($data, $id);
        if(isset($data['categories'])) {
            $this->updateCategories($id,$data['categories']);
        }
        $this->updateRecurring($id);
    }

    /**
    * Update the recurring dates for an event
    * @param int $id
    */
    function updateRecurring($id) {
        $recurring_dates = array();
        $event = $this->db->GetRow("SELECT id, recurring, date_start, date_end, recurring_interval, recurring_end, recurring_type, recurring_days, recurring_monthly FROM ".T_EVENTS." WHERE id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_EVENTS_DATES." WHERE event_id=?",array($id));
        if(!$event['recurring']) {
            $this->db->Execute("INSERT INTO ".T_EVENTS_DATES." (event_id,date_start,date_end) VALUES (?,?,?)",array($event['id'],$event['date_start'],$event['date_end']));
        } else {
            if($this->PMDR->get('Dates')->isZero($event['recurring_end'])) {
                $recurring_end = new DateTime($event['date_start']);
                $recurring_end->add(new DateInterval('P3Y'));
                $this->db->Execute("UPDATE ".T_EVENTS." SET recurring_end=? WHERE id=?",array($recurring_end->format('Y-m-d H:i:s'),$id));
            } else {
                $recurring_end = new DateTime($event['recurring_end']);
            }
            $date = new DateTime($event['date_start']);
            if(!empty($event['date_end'])) {
                $date_end = new DateTime($event['date_end']);
            }
            if($event['recurring_type'] == 'daily') {
                while($date < $recurring_end) {
                    if(isset($date_end)) {
                        $recurring_dates[] = array('start'=>$date->format('Y-m-d H:i:s'),'end'=>$date_end->format('Y-m-d H:i:s'));
                    } else {
                        $recurring_dates[] = array('start'=>$date->format('Y-m-d H:i:s'),'end'=>null);
                    }
                    $date->add(new DateInterval('P'.intval($event['recurring_interval']).'D')); // P1D means a period of 1 day
                    if(isset($date_end)) {
                        $date_end->add(new DateInterval('P'.intval($event['recurring_interval']).'D')); // P1D means a period of 1 day
                    }
                }
            } elseif($event['recurring_type'] == 'weekly') {
                if(isset($date_end)) {
                    $recurring_dates[] = array('start'=>$event['date_start'],'end'=>$event['date_end']);
                } else {
                    $recurring_dates[] = array('start'=>$event['date_start'],'end'=>null);
                }
                $recurring_days = array_filter(explode(',',$event['recurring_days']),'strlen');
                while($recurring_end > $date) {
                    foreach($recurring_days AS $recurring_day) {
                        $weekday_name = $this->PMDR->get('Dates')->getWeekDayName($recurring_day);
                        $date_temp = clone $date;
                        $date_temp->modify('next '.$weekday_name);
                        // This might be a PHP bug, the time gets lost on modify.  We have to add it back.
                        $date_temp->setTime((int)$date->format('H'), (int)$date->format('i'), (int)$date->format('s'));
                        if(isset($date_end)) {
                            $date_end_temp = clone $date_end;
                            $date_end_temp->modify('next '.$weekday_name);
                            $date_end_temp->setTime((int)$date_end->format('H'), (int)$date_end->format('i'), (int)$date_end->format('s'));
                        }
                        // If the next weekday date is less than the recurring end date, add it to the list
                        if($date_temp < $recurring_end) {
                            if(isset($date_end)) {
                                $recurring_dates[] = array('start'=>$date_temp->format('Y-m-d H:i:s'),'end'=>$date_end_temp->format('Y-m-d H:i:s'));
                            } else {
                                $recurring_dates[] = array('start'=>$date_temp->format('Y-m-d H:i:s'),'end'=>null);
                            }
                        } else {
                            break;
                        }
                    }
                    $date->add(new DateInterval('P'.intval($event['recurring_interval']).'W'));
                    if(isset($date_end)) {
                        $date_end->add(new DateInterval('P'.intval($event['recurring_interval']).'W'));
                    }
                }
            } elseif($event['recurring_type'] == 'monthly') {
                if($event['recurring_monthly'] == 'week') {
                    // Determine which week the start day is (ie. second tuesday)
                    $week_position = ceil($date->format('j') / 8);
                    // Get the weekday name (ie. sunday)
                    $weekday = $date->format('l');
                    if(isset($date_end)) {
                        $week_position_end = ceil($date_end->format('j') / 8);
                        $weekday_end = $date_end->format('l');
                    }
                }
                while($recurring_end > $date) {
                    if(isset($date_end)) {
                        $recurring_dates[] = array('start'=>$date->format('Y-m-d').' '.date('H:i:s',strtotime($event['date_start'])),'end'=>$date_end->format('Y-m-d').' '.date('H:i:s',strtotime($event['date_end'])));
                    } else {
                        $recurring_dates[] = array('start'=>$date->format('Y-m-d').' '.date('H:i:s',strtotime($event['date_start'])),'end'=>null);
                    }
                    if($event['recurring_monthly'] == 'week') {
                        $date->modify($this->PMDR->get('Dates')->getDayOccurance($week_position).' '.$weekday.' of next month');
                        if(isset($date_end)) {
                            $date_end->modify($this->PMDR->get('Dates')->getDayOccurance($week_position_end).' '.$weekday_end.' of next month');
                        }
                    } else {
                        $date->modify('+'.$event['recurring_interval'].' month');
                        if(isset($date_end)) {
                            $date_end->modify('+'.$event['recurring_interval'].' month');
                        }
                    }
                }
            } elseif($event['recurring_type'] == 'yearly') {
                while($recurring_end > $date) {
                    if(isset($date_end)) {
                        $recurring_dates[] = array('start'=>$date->format('Y-m-d H:i:s'),'end'=>$date_end->format('Y-m-d H:i:s'));
                        $date_end->modify('+'.$event['recurring_interval'].' year');
                    } else {
                        $recurring_dates[] = array('start'=>$date->format('Y-m-d H:i:s'),'end'=>null);
                    }
                    $date->modify('+'.$event['recurring_interval'].' year');
                }
            }
            if(count($recurring_dates)) {
                $recurring_sql = '';
                foreach($recurring_dates AS $recurring_date) {
                    $recurring_sql .= ",(".$id.",'".$recurring_date['start']."','".$recurring_date['end']."')";
                }
                $this->db->Execute("INSERT INTO ".T_EVENTS_DATES." (event_id,date_start,date_end) VALUES ".ltrim($recurring_sql,','));
            }
        }
    }

    /**
    * Update an event image
    * @param array $data
    * @param int $event_id
    */
    function updateLogo($data, $event_id) {
        $options = array(
            'width'=>$this->PMDR->getConfig('event_image_width'),
            'height'=>$this->PMDR->getConfig('event_image_height'),
            'enlarge'=>$this->PMDR->getConfig('event_image_small'),
            'remove_existing'=>true
        );
        if($extension = $this->PMDR->get('Image_Handler')->process($data['image'],EVENT_IMAGES_PATH.$event_id.'.*',$options)) {
            $options = array(
                'width'=>$this->PMDR->getConfig('event_thumb_width'),
                'height'=>$this->PMDR->getConfig('event_thumb_height'),
                'enlarge'=>$this->PMDR->getConfig('event_thumb_small'),
                'crop'=>$this->PMDR->getConfig('event_thumb_crop'),
                'remove_existing'=>true
            );
            $this->PMDR->get('Image_Handler')->process($data['image'],EVENT_IMAGES_THUMB_PATH.$event_id.'.*',$options);
            $this->update(array('image_extension'=>$extension),$event_id);
        }
    }

    /**
    * Update event categories
    * @param int $id Event ID
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
        $this->db->Execute("DELETE FROM ".T_EVENTS_CATEGORIES_LOOKUP." WHERE event_id=?",array($id));
        $this->db->Execute("INSERT INTO ".T_EVENTS_CATEGORIES_LOOKUP." (event_id,category_id) VALUES ".trim($value_string,','));
    }

    /**
    * Delete event
    * @param int $id
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_EVENTS_CATEGORIES_LOOKUP." WHERE event_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_EVENTS_DATES." WHERE event_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_EVENTS_RSVP." WHERE event_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_EVENTS." WHERE id=?",array($id));
        @unlink(find_file(EVENT_IMAGES_PATH.$id.'.*'));
        @unlink(find_file(EVENT_IMAGES_THUMB_PATH.$id.'.*'));
    }

    /**
    * Delete event category
    * @param int $id Category ID
    * @return void
    */
    function deleteCategory($id) {
        $this->db->Execute("DELETE FROM ".T_EVENTS_CATEGORIES_LOOKUP." WHERE category_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_EVENTS_CATEGORIES." WHERE id=?",array($id));
        return true;
    }

    /**
    * Get a range of events based on start/end time
    * @param string $start
    * @param string $end
    * @return array
    */
    function getRangeTimestamp($start, $end) {
        return $this->db->GetAll("SELECT e.id, e.title, e.friendly_url, e.description_short, ed.date_start, ed.date_end, e.color FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id WHERE ed.date_start BETWEEN ? AND ? AND status='active'",array($start,$end));
    }

    /**
    * RSVP to an event
    * @param int $id
    * @param int $user_id
    */
    function rsvp($id, $user_id) {
        $this->db->Execute("REPLACE INTO ".T_EVENTS_RSVP." (event_id,user_id,date) VALUES (?,?,NOW())",array($id,$user_id));
    }

    /**
    * Cancel a RSVP to an event
    * @param int $id
    * @param int $user_id
    */
    function rsvpCancel($id, $user_id) {
        $this->db->Execute("DELETE FROM ".T_EVENTS_RSVP." WHERE event_id=? AND user_id=?",array($id,$user_id));
    }
    
    /**
    * Get the RSVP of a user for an event
    * 
    * @param int $id
    * @param int $user_id
    */
    function getUserRSVP($id,$user_id) {
        return $db->GetOne("SELECT COUNT(*) FROM ".T_EVENTS_RSVP." WHERE event_id=? AND user_id",array($id,$user_id));
    }

    /**
    * Insert event category
    * @param array $data
    * @return boolean
    */
    function insertCategory($data) {
        return $this->db->Execute("INSERT INTO ".T_EVENTS_CATEGORIES." (title,friendly_url,keywords,meta_title,meta_keywords,meta_description) VALUES (?,?,?,?,?,?)",array($data['title'],$data['friendly_url'],$data['keywords'],$data['meta_title'],$data['meta_keywords'],$data['meta_description']));
    }

    /**
    * Update event category
    * @param array $data
    * @param int $id
    * @return boolean
    */
    function updateCategory($data,$id) {
        return $this->db->Execute("UPDATE ".T_EVENTS_CATEGORIES." SET title=?, friendly_url=?,keywords=?,meta_title=?,meta_keywords=?,meta_description=? WHERE id=?",array($data['title'],$data['friendly_url'],$data['keywords'],$data['meta_title'],$data['meta_keywords'],$data['meta_description'],$id));
    }

    /**
    * Get event categories associative array for select fields
    * @return array Categories
    */
    function getCategoriesSelect() {
        return $this->db->GetAssoc("SELECT id, title FROM ".T_EVENTS_CATEGORIES." ORDER BY title");
    }

    /**
    * Get the number of events for a listing
    * @param int $listing_id
    * @return int Number of events belonging to $listing_id
    */
    function getListingCount($listing_id, $active=false) {
        $active_sql = "";
        if($active == true) {
            $active_sql = "AND status='active'";
        }
        return $this->db->GetOne("SELECT COUNT(*) AS count FROM ".T_EVENTS." WHERE listing_id=? $active_sql",array($listing_id));
    }

    /**
    * Update event status
    * @param int $id
    * @param string $status
    */
    function updateStatus($id,$status) {
        $this->db->Execute("UPDATE ".T_EVENTS." SET status=? WHERE id=?",array($status,$id));
    }

    /**
    * Activate event
    * @param int $id
    */
    function activate($id) {
        $this->updateStatus($id,'active');
    }
    
    /**
    * Get future events
    * 
    * @param int $limit1
    * @param int $limit2
    * @return array
    */
    function getFuture($limit1, $limit2) {
        $records = $this->db->GetAll("
            SELECT
                e.id, e.title, ed.date_start, e.friendly_url, e.image_extension
            FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id
            WHERE e.status='active' AND ed.date_end > NOW() ORDER BY ed.date_start ASC LIMIT ?,?",array($limit1,$limit2)
        );
        return $this->formatResults($records);
    }
    
    function getUpcoming($limit) {
        $results = $this->db->GetAll("SELECT
                            e.id,
                            status,
                            title,
                            description_short,
                            friendly_url,
                            ed.date_start,
                            ed.date_end,
                            image_extension
                          FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id
                          WHERE
                            e.status = 'active' AND
                            ed.date_start > NOW()
                          ORDER BY ed.date_start ASC
                          LIMIT 0, ?",array(intval($limit))
        );
                          
        return $this->formatResults($results);                        
    }
    
    function getNew($limit) {
        $results = $this->db->GetAll("SELECT
                        e.id,
                        user_id,
                        status,
                        title,
                        description_short,
                        friendly_url,
                        date,
                        ed.date_start,
                        ed.date_end,
                        phone,
                        image_extension
                      FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id
                      WHERE
                        status = 'active'
                      ORDER BY e.date DESC
                      LIMIT 0, ?",intval($limit));
        return $this->formatResults($results);
    }
    
    /**
    * Get events for a specific listing
    * 
    * @param int $listing_id
    * @param int $limit1
    * @param int $limit2
    * @return array
    */
    function getListingEvents($listing_id, $limit1, $limit2) {
        $records = $this->db->GetAll("SELECT
                    e.id,
                    title,
                    description_short,
                    friendly_url,
                    date,
                    ed.date_start,
                    ed.date_end,
                    phone,
                    image_extension
                  FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id
                  WHERE
                    listing_id = ? AND
                    status = 'active' AND
                    ed.date_end > NOW()
                  ORDER BY ed.date_start DESC
                  LIMIT ?, ?",array($listing_id,$limit1,$limit2));
                  
        return $this->formatResults($records);
    }

    /**
    * Get other events from the same listing ID
    *
    * @param int $event_id
    * @param int $listing_id
    * @return array
    */
    function getOtherListingEvents($event_id,$listing_id) {
        return $this->db->GetAll("SELECT id, title, friendly_url FROM ".T_EVENTS." WHERE id!=? AND listing_id=? AND status='active' LIMIT 10",array($event_id,$listing_id));
    }

    /**
    * Get current and future event dates
    * 
    * @param int $event_id
    * @return array
    */
    function getCurrentDates($event_id, $limit=20) {
        return $this->db->GetAll("SELECT date_start, date_end FROM ".T_EVENTS_DATES." WHERE event_id=? AND date_start >= NOW() ORDER BY date_start ASC LIMIT ?",array($event_id,$limit));
    }
        
    /** 
    * Get past dates for an event
    * 
    * @param int $event_id
    * @return array
    */
    function getPastDates($event_id, $limit=10) {
        return $this->db->GetAll("SELECT date_start, date_end FROM ".T_EVENTS_DATES." WHERE event_id=? AND date_start < NOW() ORDER BY date_start ASC LIMIT ?",array($event_id,$limit));
    }
    
    /**
    * Format event results such as dates, urls, etc
    * 
    * @param array $results
    * @param boolean $replace Replace the existing values with the formatted values
    * @return array
    */
    function formatResults($results,$replace=true) {
        foreach($results as $key=>$result) {
            if($replace) {
                $results[$key]['date'] = $this->PMDR->get('Dates_Local')->formatDateTime($result['date']);
                $results[$key]['date_start'] = $this->PMDR->get('Dates_Local')->formatDateTime($result['date']);
                $results[$key]['date_end'] = $this->PMDR->get('Dates_Local')->formatDateTime($result['date']);    
            } else {    
                $results[$key]['date_formatted'] = $this->PMDR->get('Dates_Local')->formatDateTime($result['date']);
                $results[$key]['date_start_formatted'] = $this->PMDR->get('Dates_Local')->formatDateTime($result['date']);
                $results[$key]['date_end_formatted'] = $this->PMDR->get('Dates_Local')->formatDateTime($result['date']);
            }
            if(isset($result['id']) AND isset($result['image_extension']) AND $image_url = get_file_url_cdn(EVENT_IMAGES_THUMB_PATH.$result['id'].'.'.$result['image_extension'])) {
                $results[$key]['image_url'] = $image_url;
            }
            if(isset($result['id']) AND isset($result['friendly_url'])) {
                $results[$key]['url'] = $this->getURL($result['id'],$result['friendly_url']);
            }
        }
        return $results;
    }

    /**
    * Generate the event in iCalendar format and serve
    * @param int $id Event ID
    */
    function downloadiCal($id) {
        $event = $this->getRow($id);
        $title = Strings::rewrite($event['title']);
        $date = $this->PMDR->get('Dates')->formatDate($event['date'],'Ymd\THis\Z');
        $date_start = $this->PMDR->get('Dates')->formatDate($event['date_start'],'Ymd\THis\Z');
        $date_end = $this->PMDR->get('Dates')->formatDate($event['date_end'],'Ymd\THis\Z');
        $uid = $title.'-'.$event['id'].'-'.$date;
        $content = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:'.$uid.'@'.parse_url(URL, PHP_URL_HOST).'
DTSTAMP:'.$date;
        if($event['contact_name'] != '' OR $event['email'] != '') {
            $content .= '
ORGANIZER;';
            if($event['contact_name'] != '') {
                $content .= 'CN='.$event['contact_name'];
            }
            if($event['email'] != '') {
                $content .= 'MAILTO:'.$event['email'];
            }
        }
        $content .= '
DTSTART:'.$date_start.'
DTEND:'.$date_end.'
SUMMARY:'.$event['title'].'
LOCATION:'.str_replace("\n",'\n',$event['location']).'
URL:'.$this->getURL($event['id'],$event['friendly_url']).'
DESCRIPTION:'.str_replace("\n",'\n',$event['description']).'
END:VEVENT
END:VCALENDAR';
        $this->PMDR->get('ServeFile')->serve($title.'.ical',$content);
    }
}
?>