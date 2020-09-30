<?php
/**
* Statistics Class
*/
class Statistics {
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
    * Statistics constructor
    * @param object $PMDR
    * @return Statistics
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
    }

    /**
    * Insert a statistic
    * @param string $type
    * @param int $type_id
    */
    function insert($type,$type_id) {
        if($this->PMDR->getConfig('statistics_disable') OR BOT) {
            return false;
        }

        $ip = get_ip_address();

        if(!$this->db->GetOne("SELECT COUNT(*) FROM ".T_STATISTICS_RAW." WHERE type=? AND type_id=? AND ip_address=? AND DATE(date) = CURDATE()",array($type,$type_id,$ip))) {
            $this->db->Execute("INSERT INTO ".T_STATISTICS_RAW." (date,type,type_id,ip_address) VALUES (NOW(),?,?,?)",array($type,$type_id,$ip));

            switch($type) {
                case 'banner_click':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_BANNERS." SET clicks=clicks+1 WHERE id=?",array($type_id));
                    break;
                case 'banner_impression':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_BANNERS." SET impressions=impressions+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_impression':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET impressions=impressions+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_search_impression':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET search_impressions=search_impressions+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_website':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET website_clicks=website_clicks+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_email':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET emails=emails+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_banner_impression':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET banner_impressions=banner_impressions+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_banner_click':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET banner_clicks=banner_clicks+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_share':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET shares=shares+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_email_view':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET email_views=email_views+1 WHERE id=?",array($type_id));
                    break;
                case 'listing_phone_view':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET phone_views=phone_views+1 WHERE id=?",array($type_id));
                    break;
                case 'blog_impression':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_BLOG." SET impressions=impressions+1 WHERE id=?",array($type_id));
                    break;
                case 'location_impression':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LOCATIONS." SET impressions=impressions+1 WHERE id=?",array($type_id));
                    break;
                case 'location_impression_search':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_LOCATIONS." SET impressions_search=impressions_search+1 WHERE id=?",array($type_id));
                    break;
                case 'category_impression':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_CATEGORIES." SET impressions=impressions+1 WHERE id=?",array($type_id));
                    break;
                case 'category_impression_search':
                    $this->db->Execute("UPDATE LOW_PRIORITY ".T_CATEGORIES." SET impressions_search=impressions_search+1 WHERE id=?",array($type_id));
                    break;
            }
        }
    }

    /**
    * Get statistics based on type, listing, and optional date range
    * @param string $type
    * @param int $listing_id
    * @param string $date_start
    * @param string $date_end
    * @return array
    */
    function getStatistics($type,$listing_id,$date_start=null,$date_end=null) {
        $types = array(
            'listing_impression'=>0,
            'listing_search_impression'=>0,
            'listing_website'=>0,
            'listing_email'=>0,
            'listing_banner_impression'=>0,
            'listing_banner_click'=>0,
            'listing_share'=>0,
            'listing_email_view'=>0,
            'listing_phone_view'=>0,
        );

        switch($type) {
            case 'today':
                $start_date = $end_date = $this->PMDR->get('Dates')->dateNow();
                break;
            case 'yesterday':
                $start_date = $end_date = $this->PMDR->get('Dates')->date('Y-m-d',strtotime('yesterday'));
                break;
            case 'last_7':
                $start_date = $this->PMDR->get('Dates')->date('Y-m-d',strtotime('-7 day'));
                $end_date = $this->PMDR->get('Dates')->dateNow();
                break;
            case 'last_30':
                $start_date = $this->PMDR->get('Dates')->date('Y-m-d',strtotime('-30 day'));
                $end_date = $this->PMDR->get('Dates')->dateNow();
                break;
            case 'this_month':
                $start_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,date('n'),1,date('Y')));
                $end_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,date('n')+1,0,date('Y')));
                break;
            case 'last_month':
                $start_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,date('n')-1,1,date('Y')));
                $end_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,date('n'),0,date('Y')));
                break;
            case 'this_year':
                $start_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,1,1,date('Y')));
                $end_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,12,31,date('Y')));
                break;
            case 'last_year':
                $start_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,1,1,date('Y')-1));
                $end_date = $this->PMDR->get('Dates')->date('Y-m-d',mktime(0,0,0,12,31,date('Y')-1));
                break;
            case 'date_range':
                $start_date = $date_start;
                $end_date = $date_end;
                break;
            case 'all_time':
            default:
                $type = 'all_time';
                $start_date = $end_date = null;
                break;
        }

        if(!is_null($start_date) AND !is_null($end_date)) {
            $date_sql = "AND date BETWEEN '$start_date' AND '$end_date'";
        }

        if($this->getGroupType($type) == 'daily') {
            $statistics = $this->db->GetAll("SELECT date, type, COALESCE(SUM(count),0) AS count FROM ".T_STATISTICS." WHERE type_id=? $date_sql GROUP BY date, type ORDER BY date ASC",array($listing_id));
            foreach($statistics AS $statistic) {
                if(!is_array($statistics_parsed[$statistic['date']])) {
                    $statistics_parsed[$statistic['date']] = $types;
                }
                $statistics_parsed[$statistic['date']][$statistic['type']] = $statistic['count'];
            }
        } else {
            $statistics = $this->db->GetAll("SELECT IFNULL(MONTH(date),0) AS month, IFNULL(YEAR(date),0) AS year, type, COALESCE(SUM(count),0) AS count FROM ".T_STATISTICS." WHERE type_id=? $date_sql GROUP BY month, year, type ORDER BY date DESC",array($listing_id));
            foreach($statistics AS $statistic) {
                if(!is_array($statistics_parsed[$statistic['year']][$statistic['month']])) {
                    $statistics_parsed[$statistic['year']][$statistic['month']] = $types;
                }
                $statistics_parsed[$statistic['year']][$statistic['month']][$statistic['type']] = $statistic['count'];
            }
        }
        return $statistics_parsed;
    }

    /**
    * Get the grouping type depending on date range
    * @param string $type
    * @return string
    */
    function getGroupType($type) {
        $group_types = array(
            'daily'=>array(
                'today',
                'yesterday',
                'last_7',
                'last_30',
                'this_month',
                'last_month'
            ),
            'monthly'=>array(
                'this_year',
                'last_year',
                'all_time'
            )
        );
        if(in_array($type,$group_types['daily'])) {
            return 'daily';
        } else {
            return 'monthly';
        }
    }
}
?>