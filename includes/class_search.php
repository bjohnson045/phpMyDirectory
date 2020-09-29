<?php
/**
* Search class
* Searches listings based on a set of settings and values
*/
class Search {
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
    * Results
    * @var array
    */
    var $results = array();
    /**
    * Results count
    * @var int
    */
    var $resultsCount = 0;
    /**
    * Execution time
    * @var mixed
    */
    var $executionTime = 0;

    /**
    * Search class constructor
    * @param object $PMDR Registry
    * @return Search
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Get search results
    * @param int $limit1
    * @param int $limit2
    */
    function getResults($limit1=null, $limit2=null) {
        $this->limit1 = $limit1;
        $this->limit2 = $limit2;
        $start_time = microtime(1);
        $query = $this->buildQuery();
        $this->results = $this->db->GetAll($query);
        if(!$this->count_separate) {
            $this->resultsCount = $this->db->GetOne("SELECT FOUND_ROWS()");
        } else {
            $this->resultsCount = $this->db->GetOne($this->buildQuery(true));
        }
        $keyword_count = count(preg_split('#\s+#',$this->keywords,null,PREG_SPLIT_NO_EMPTY));
        if($this->resultsCount < $this->queryExpansionResultLimit AND $keyword_count <= $this->queryExpansionWordLimit AND $keyword_count != 0) {
            $this->queryExpansion = true;
            $this->results = $this->db->GetAll($query);
            if(!$this->count_separate) {
                $this->resultsCount = $this->db->GetOne("SELECT FOUND_ROWS()");
            } else {
                $this->resultsCount = $this->db->GetOne($this->buildQuery(true));
            }
        }
        $this->executionTime = microtime(1) - $start_time;
        return $this->results;
    }
}

/**
* Fulltext search subclass
*/
class SearchListingFullText extends Search {
    var $keywords = null;                 // Keywords to search
    var $listing_id = null;               // Listing ID
    var $pricing_id = null;               // Pricing ID
    var $rating = null;                   // Rating
    var $listing_status = null;           // Listing status
    var $email = null;                    // Email
    var $category = null;                 // Category ID or Text
    var $categoryNode = null;             // Category Node
    var $categorySearchChildren = true;   // If true the script will search in all category ID's of the selected category, if false may improve performance
    var $primaryCategoryOnly = false;     // If true, this prevents a join on the category lookup table, useful for large databases
    var $locationSearchChildren = true;
    var $location = null;                 // Location ID or Text
    var $location_id = null;              // Location ID
    var $locationNode = null;             // Location node containing left_ right_ values
    var $locationConvertCodes = false;    // Convert two letter state codes to their state names
    var $locationTryExactMatch = true;    // Check the location input to see if it matches a location exactly.
    var $zip;                             // Zip code of listing
    var $zipMiles;                        // Miles radius to search in
    var $zipAllowPartial = false;         // Allow partial zip code matches, does not work with radius searching (not implemented yet)
    var $zipPartialRegex = null;          // A regular expression to match the zip code against to get a partial match
    var $zipUseDatabase = false;          // Uses the zip code database to lookup the listing coordinates
    var $zipLookup = false;               // Try to find the zip code based on city/state
    var $latitude = null;                 // Latitude for radius searches
    var $longitude = null;                // Longitude for radius searches
    var $joinUser = false;                // Get user information in result set also
    var $joinUserFields = array('login','user_email');  // Fields we get if we join the user
    var $user_id = null;                  // User ID of user to get
    var $queryExpansion = false;          // Use MySQL query expansion in the query, used internally do not change this
    var $queryExpansionResultLimit = 5;   // If results less than this number, query expansion is applied to the query to get some more results, set to 0 to turn off query expansion
    var $queryExpansionWordLimit = 2;     // If the search phrase is this many words or less, query expansion will be applied. (query expansion works better the smaller the search term)
    var $findMatches = true;              // Find matching locations/categories as a part of the search
    var $likeShortWordMax = 3;            // Strip out words no more than this amount of characters
    var $likeShortWordMin = 1;            // Strip out words no less than this amount of characters
    var $likeShortWordLookin = array('l.title','l.keywords','l.location_search_text');   // Use these fields for the LIKE query for short words
    var $likeShortWordRequire = true;     // If short words are included, results have to match these words and any larger ones if they exist, if false it does an OR search
    var $likeShortWordRequireAll = true;  // If more than one short word is included and this is true we use AND to join them in the search, if not we use OR
    var $likeOnlyMatchBeginning = false;  // If turned on a short word will match only the beginning of words
    var $booleanMode = true;             // Use MySQL boolean mode - simply turns it on for use (IN BOOLEAN MODE) but does not do anything specific
    var $booleanMatchAll = false;         // If boolean mode is turned on, add + to the beginning or words so all words must be matched
    var $booleanPartialMatch = true;     // If turned on adds * wildcard to the end of words
    var $matchingCategories = array();    // Holds all matching categories
    var $matchingLocations = array();     // Holds all matching locations
    var $matchingCategoriesFields = array('title','keywords');
    var $matchingLocationsFields = array('title','keywords');
    var $otherFields = array();           // Array of other fields to include in search in format 'name'=>{field_name},'search_for'=>{string}
    var $sortBy = array('priority_calculated'=>'DESC','score'=>'DESC','l.title'=>'ASC');    // Sort order array, keys are the field to sort by, with their value being the direction
    var $sortByDistance = array('zip_distance'=>'ASC','priority_calculated'=>'DESC','score'=>'DESC','l.title'=>'ASC');
    var $titleWeight = 1.5;               // Relevancy weight added to words matching the title
    var $filterStopWords = false;
    var $stripList = array();
    var $spaceCharacters = array(',');    // We do not include a period because of short words like initials like "B.J."
    var $forceLIKE = false;
    var $distanceType = 'miles';
    var $autoSortByDistance = true;
    var $groupBy = array();
    var $searchListingFields = array('l.title','l.description_short','l.keywords','l.location_search_text');
    var $totalResultLimit = 200;
    var $limit1 = null;
    var $limit2 = null;
    var $count_separate = false;
    var $joinOrder = false;
    var $stopWords = array('about','actually','ain\'t','alone','always','and','anyone','apart','aren\'t','asking','awfully','becomes','behind','besides','both','c\'s','cant','changes','comes','contain','couldn\'t','despite','does','down','eg','enough','even','everything','except','five','former','further','given','gone','hadn\'t','have','hello','here\'s','hers','his','however','ie','inasmuch','indicates','inward','it\'ll','keep','knows','latterly','let\'s','look','many','meanwhile','most','myself','nearly','never','no','nor','now','often','on','onto','ought','outside','particularly','plus','que','re','regards','same','second','seemed','selves','seven','shouldn\'t','somebody','sometimes','specified','such','taken','thank','thats','themselves','thereafter','thereupon','they\'re','thorough','through','together','tried','twice','unless','upon','uses','very','wants','we\'d','well','what\'s','where','wherein','while','whole','willing','won\'t','yet','you\'ve','zero','above','after','all','along','am','another','anything','appear','around','associated','be','becoming','being','best','brief','came','cause','clearly','concerning','containing','course','did','doesn\'t','downwards','eight','entirely','ever','everywhere','far','followed','formerly','furthermore','gives','got','happens','haven\'t','help','hereafter','herself','hither','i\'d','if','inc','inner','is','it\'s','keeps','last','least','like','looking','may','merely','mostly','name','necessary','nevertheless','nobody','normally','nowhere','oh','once','or','our','over','per','possible','quite','really','relatively','saw','secondly','seeming','sensible','several','since','somehow','somewhat','specify','sup','tell','thanks','the','then','thereby','these','they\'ve','thoroughly','throughout','too','tries','two','unlikely','us','using','via','was','we\'ll','went','whatever','where\'s','whereupon','whither','whom','wish','wonder','you','your','according','afterwards','allow','already','among','any','anyway','appreciate','as','at','became','been','believe','better','but','can','causes','co','consequently','contains','currently','didn\'t','doing','during','either','especially','every','ex','few','following','forth','get','go','gotten','hardly','having','hence','hereby','hi','hopefully','i\'ll','ignored','indeed','insofar','isn\'t','its','kept','lately','less','liked','looks','maybe','might','much','namely','need','new','non','not','obviously','ok','one','other','ours','overall','perhaps','presumably','qv','reasonably','respectively','say','see','seems','sent','shall','six','someone','somewhere','specifying','sure','tends','thanx','their','thence','therefore','they','think','those','thru','took','truly','un','until','use','usually','viz','wasn\'t','we\'re','were','when','whereafter','wherever','who','whose','with','would','you\'d','yours','able','across','against','almost','although','an','anyhow','anywhere','are','ask','away','become','beforehand','beside','beyond','c\'mon','cannot','certainly','come','considering','could','described','do','done','edu','elsewhere','etc','everyone','example','first','for','from','getting','going','had','hasn\'t','he\'s','here','hereupon','himself','howbeit','i\'ve','in','indicated','into','it\'d','just','known','latter','let','little','mainly','mean','moreover','my','near','neither','nine','noone','novel','off','old','only','otherwise','out','particular','please','provides','rd','regardless','said','says','seem','self','seriously','should','some','sometime','sorry','sub','take','than','that\'s','them','there\'s','theres','they\'ll','this','three','to','towards','trying','unfortunately','up','useful','various','want','we','welcome','what','whenever','whereby','which','whoever','will','without','yes','you\'re','yourselves','a\'s','accordingly','again','allows','also','amongst','anybody','anyways','appropriate','aside','available','because','before','below','between','by','can\'t','certain','com','consider','corresponding','definitely','different','don\'t','each','else','et','everybody','exactly','fifth','follows','four','gets','goes','greetings','has','he','her','herein','him','how','i\'m','immediate','indicate','instead','it','itself','know','later','lest','likely','ltd','me','more','must','nd','needs','next','none','nothing','of','okay','ones','others','ourselves','own','placed','probably','rather','regarding','right','saying','seeing','seen','serious','she','so','something','soon','still','t\'s','th','that','theirs','there','therein','they\'d','third','though','thus','toward','try','under','unto','used','value','vs','way','we\'ve','weren\'t','whence','whereas','whether','who\'s','why','within','wouldn\'t','you\'ll','yourself');

    /**
    * Fulltext search subclass constructor
    * @param object $PMDR Registry
    * @return SearchListingFullText
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Apply boolean formatting to a string
    * @param string $string
    * @return string
    */
    function applyBooleanFormatting($string) {
        // Put all quote enclosed sections into $word_array1
        //$string = str_replace('&quot;','"',$string);
        preg_match_all('/"[^"]+"/u', $string, $words_array1);
        // Remove all quote enclosed sections found above from the string
        $string = str_replace($words_array1[0], '', $string);
        // Split remaining string by spaces into $words_array2, limit to 10
        $words_array2 = preg_split('#\s+#', trim($string),10);
        // Merge the arrays back into the single $words array
        $words = array_merge($words_array1[0], $words_array2);
        // Loop through all words in $words array
        if(is_array($words) AND sizeof($words) > 0) {
            foreach($words as $key=>$value) {
                // If boolean mode is on and partial matching add * wildcard to the end of everything
                if($this->booleanPartialMatch AND substr($value,0,1) != '"' AND $value != '' AND substr($value,-1,1) != '*') {
                    $value = $value.'*';
                }
                // If we want to match all words, add + to the beginning of everything
                if($this->booleanMatchAll) {
                    $value = '+'.$value;
                }
                $words[$key] = $value;
            }
        }
        return implode(' ',$words);
    }

    /**
    * Parse a string into a like query
    * @param string $string String to parse
    * @param mixed $fields Fields to search
    * @return string
    */
    function parseForLIKE($string, $fields) {
        $like_array = array();

        if(!is_array($string)) {
            preg_match_all('/"([^"]+)"/u', $string, $quoted_words);
            $string = str_replace($quoted_words[0], '', $string);
            $string = preg_split('#\s+#',trim($string),-1,PREG_SPLIT_NO_EMPTY);
            $string = array_filter(array_merge($quoted_words[1], $string));
        }

        if(!is_array($fields)) {
            $fields = array($fields);
        }

        foreach($string AS $word) {
            $like_where = array();
            foreach($fields AS $field) {
                if($this->likeOnlyMatchBeginning) {
                    $like_where[] = $field." REGEXP ".$this->PMDR->get('Cleaner')->clean_db('[[:<:]]'.preg_quote(trim($word)),'$');
                } else {
                    $like_where[] = $field." LIKE ".$this->PMDR->get('Cleaner')->clean_db('%'.trim($word).'%');
                }
            }
            $like_array[] = '('.implode(' OR ',$like_where).')';
        }
        return implode(($this->likeShortWordRequireAll ? ' AND ' : ' OR '),$like_array);
    }

    /**
    * Parse a string into fulltext
    * @param string $string String to parse
    * @param mixed $fields Fields to search
    * @param string $title Title to search
    * @param boolean $useQueryExpansion Use query expansion
    */
    function parseForFulltext($string, $fields, $title = null, $useQueryExpansion = 0) {
        if(!is_array($fields)) {
            $fields = array($fields);
        }
        $parse_result = array();
        if($this->booleanMode) {
            $string = $this->applyBooleanFormatting($string);
        }
        $parse_result['where'] =  "MATCH (".implode(',',$fields).") AGAINST (".$this->PMDR->get('Cleaner')->clean_db($string).(($this->booleanMode) ? ' IN BOOLEAN MODE' : (($useQueryExpansion) ? ' WITH QUERY EXPANSION' : '')).")";
        if($this->titleWeight AND !is_null($title) AND $this->titleWeight > 1) {
            $parse_result['score'] =", MATCH (".$title.") AGAINST (".$this->PMDR->get('Cleaner')->clean_db($string).(($this->booleanMode) ? ' IN BOOLEAN MODE' : (($useQueryExpansion) ? ' WITH QUERY EXPANSION' : '')).")*".$this->titleWeight." + MATCH (".implode(',',$fields).") AGAINST (".$this->PMDR->get('Cleaner')->clean_db($string).(($this->booleanMode) ? ' IN BOOLEAN MODE' : (($useQueryExpansion) ? ' WITH QUERY EXPANSION' : '')).") AS score";
        } elseif(array_key_exists('score',$this->sortBy)) {
            $parse_result['score'] =", MATCH (".implode(',',$fields).") AGAINST (".$this->PMDR->get('Cleaner')->clean_db($string).(($this->booleanMode) ? ' IN BOOLEAN MODE' : (($useQueryExpansion) ? ' WITH QUERY EXPANSION' : '')).") AS score";
        }
        return $parse_result;
    }

    private function extractShortWords(&$string) {
        // Find any short words and remove them from the location string
        $short_words = array();
        $found_short_words = array();
        if(preg_match_all('/(^|\s)(\S{'.$this->likeShortWordMin.','.$this->likeShortWordMax.'})(?=$|\s)/u', $string, $found_short_words)) {
            $string = trim(preg_replace('/(^|\s)(\S{1,'.$this->likeShortWordMax.'})(?=$|\s)/u','',$string));
            // Add short words to class variable for later processing
            $short_words = $found_short_words[2];
        }
        return array_filter($short_words);
    }

    public function extractStopWords(&$string) {
        $stop_words = array();
        if($this->filterStopWords) {
            $stop_words = array_filter(array_uintersect(preg_split('#\s+#',$string,NULL,PREG_SPLIT_NO_EMPTY),$this->stopWords,'strcasecmp'));
            $string = str_replace($stop_words,'',$string);
        }
        return $stop_words;
    }

    /**
    * Build the query WHERE string taking into account the field, short words, etc.
    * @param string $string
    * @param array $fields
    * @param mixed $like_words
    */
    private function buildWhereString($string, $fields, $like_words = null) {
        $like_words_query = '';
        if(is_null($like_words)) {
            $stop_words = $this->extractStopWords($string);
            $short_words = $this->extractShortWords($string);
            $like_words = array_merge($stop_words,$short_words);
        }
        if(count($like_words) > 0) {
            $like_words_query = $this->parseForLIKE($like_words,$fields);
        }
        if(!empty($string)) {
            $string_parsed = $this->parseForFulltext($string,$fields);
            if(count($like_words) > 0) {
                return '('.$string_parsed['where'].' AND '.$like_words_query.')';
            } else {
                return $string_parsed['where'];
            }
        } else {
            return $like_words_query;
        }
    }

    private function stemWords($words) {
        $stem_words = preg_split('#\s+#',$words,NULL,PREG_SPLIT_NO_EMPTY);
        if(count($stem_words)) {
            foreach($stem_words AS $stem_word) {
                // We strip iey so boolean searches work 100% otherwise we may have issues.  (i.e. Gallery becomes Galleri)
                $words = str_replace($stem_word,preg_replace('/[iey]$/i','',$this->PMDR->get('Stemmer')->stem($stem_word),1),$words);
            }
        }
        return trim($words);
    }

    /**
    * Add an additional field to the query
    * @param mixed $search_fields Search fields
    * @param mixed $search_values Search values
    * @param boolean $required Require the field
    * @param string $query Query format
    */
    function addAdditionalField($search_fields, $search_values = array(), $required = 0, $query = "{search_field1} LIKE '%{search_value1}%'") {
        if(!is_array($search_fields)) {
            $search_fields = array($search_fields);
        }
        if(!is_array($search_values)) {
            if(strlen($search_values) == 0) {
                return false;
            }
            $search_values = array($search_values);
        } elseif(!count(array_filter($search_values))) {
            return false;
        }
        foreach($search_values as $key=>$value) {
            $query = str_replace('{search_value'.($key+1).'}',$this->PMDR->get('Cleaner')->clean_db($value,false),$query);
        }
        foreach($search_fields as $key=>$field_name) {
            $query = str_replace('{search_field'.($key+1).'}',$field_name,$query);
        }

        $this->otherFields[$required][] = $query;
    }

    /**
    * Build database query
    * @param boolean $count Get the count
    * @return string
    */
    function buildQuery($count = false) {
        $where = array();
        $groupBy = $this->groupBy;
        $this->location_parsed = $this->location;
        $this->category_parsed = $this->category;
        $this->keywords_parsed = $this->keywords;

        // If we want to get the user ID along with the listing
        $select_user = '';
        $join_user = '';
        if($this->joinUser AND !$count) {
            $this->count_separate = true;
            $join_user = "INNER JOIN ".T_USERS." ON listings.user_id=".T_USERS.".id";
            $select_user = '';
            foreach($this->joinUserFields as $userfield) {
                 $select_user .= ', '.T_USERS.'.'.$userfield;
            }
        }

        // Add product check
        $select_order = '';
        $order_join = '';
        if(!empty($this->pricing_id) OR $this->joinOrder) {
            $pricing_match = '';
            if(!empty($this->pricing_id)) {
                if(!is_array($this->pricing_id)) {
                    $this->pricing_id = array($this->pricing_id);
                }
                if(count($this->pricing_id) > 1) {
                    $pricing_match = " AND o.pricing_id IN ('".implode("','",$this->PMDR->get('Cleaner')->clean_db($this->pricing_id))."')";
                } else {
                    $pricing_match = " AND o.pricing_id=".$this->PMDR->get('Cleaner')->clean_db($this->pricing_id[0]);
                }
            }
            $this->count_separate = true;
            $select_order = ', o.id AS order_id';
            $order_join = "INNER JOIN ".T_ORDERS." o ON listings.id = o.type_id AND o.type='listing_membership'$pricing_match";
        }

        // Add ID to search and skip everything else
        if(!empty($this->listing_id)) {
            $where[] = 'l.id='.$this->PMDR->get('Cleaner')->clean_db($this->listing_id);
        } else {
            // Add userid to search
            if(!is_null($this->user_id)) {
                $where[] = 'l.user_id='.$this->PMDR->get('Cleaner')->clean_db($this->user_id);
            }
            // Analyze location data
            if(!empty($this->location_parsed)) {
                // Convert USA state codes to their state names
                if($this->locationConvertCodes) {
                    include(PMDROOT.'/includes/state_codes.php');
                    foreach($state_codes AS $code=>$state) {
                        $state_find[] = '/(?<=^|\W)('.$code.')(?=$|\W)/i';
                        $state_replace[] = $state;
                    }
                    $this->location_parsed = preg_replace($state_find,$state_replace,$this->location_parsed);
                    unset($state_codes,$state_codes_inverse,$state_find,$state_replace,$code,$state);
                }

                // Keep the current text
                $location_string = $this->location_parsed;

                // We do not perform the normal location search if we are doing a radius search on the location
                // or else we miss out on some locations that do not match the location being searched
                if(!$this->zipLookup OR $this->zipMiles == '') {
                    // Look if we match any locations exactly
                    if($this->locationTryExactMatch) {
                        if($found_location = $this->db->GetAll("SELECT id, title FROM ".T_LOCATIONS." WHERE title IN (".$this->PMDR->get('Cleaner')->clean_db($this->location_parsed).",'".implode("','",preg_split('#\W+#u',$this->PMDR->get('Cleaner')->clean_db($this->location_parsed),-1,PREG_SPLIT_NO_EMPTY))."') ORDER BY level DESC")) {
                            // If we have a single match continue, if not we do not do the replace because wrong location could be picked (i.e. cities with same name)
                            if(count($found_location) == 1) {
                                $location_text_check = '';
                                $location_text_conflict = false;
                                if($this->PMDR->getConfig('location_text_1')) {
                                    $location_text_check[] = 'location_text_1=?';
                                }
                                if($this->PMDR->getConfig('location_text_2')) {
                                    $location_text_check[] = 'location_text_2=?';
                                }
                                if($this->PMDR->getConfig('location_text_1')) {
                                    $location_text_check[] = 'location_text_2=?';
                                }

                                if(!empty($location_text_check)) {
                                    if($this->db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE ".implode(' OR ',$location_text_check),array_fill(0,count($location_text_check),$found_location[0]['title']))) {
                                        $location_text_conflict = true;
                                    }
                                }
                                unset($location_text_check);

                                if(!$location_text_conflict) {
                                    // Assign the highest level location id to the location id
                                    $this->location_id = $found_location[0]['id'];
                                    // Strip the found locations out of the search text, the rest will search in location_search_text
                                    foreach($found_location AS $location) {
                                        $location_string = trim(str_ireplace($location['title'],'',$location_string));
                                    }
                                    unset($location);
                                }
                                unset($location_text_conflict);
                            }
                        }
                        unset($found_location);
                    }

                    if($location_string != '') {
                        $stop_words = $this->extractStopWords($location_string);
                        $short_words = $this->extractShortWords($location_string);
                        $like_words = array_merge($stop_words,$short_words);
                        $location_string = Strings::minimize_spacing($location_string);
                        $where[] = $this->buildWhereString($location_string,array('l.location_search_text'),$like_words);
                        if($this->findMatches) {
                            $location_where = $this->buildWhereString($location_string,$this->matchingLocationsFields,$like_words);
                            $this->matchingLocations = $this->db->GetAll("SELECT id, title, friendly_url, friendly_url_path, count_total FROM ".T_LOCATIONS." l WHERE ".($this->PMDR->getConfig('loc_empty_hidden') ? 'count_total > 0 AND' : '')." ".$location_where);
                        }
                        unset($location_where,$location_parsed,$stop_words,$short_words,$like_words_query,$like_words,$location_string);
                    }
                }
            }

            if(!$this->booleanMode) {
                $this->keywords_parsed = str_replace(array('-','+','*','"'),'',$this->keywords_parsed);
            }

            // Replace any character we may consider "spaces" with an actual space
            if(count($this->spaceCharacters)) {
                $this->keywords_parsed = str_replace($this->spaceCharacters,' ',$this->keywords_parsed);
            }

            // Remove any words from the master strip list
            if(count($this->stripList)) {
                $strip_replace = array();
                foreach($this->stripList AS &$strip) {
                    $strip_parts = explode('|',$strip,2);
                    $strip_words[] = '/(^|\s)('.$strip_parts[0].')(?=$|\s)/u';
                    if(isset($strip_parts[1])) {
                        $strip_replace[] = $strip_parts[1];
                    } else {
                        $strip_replace[] = '';
                    }
                }
                unset($strip,$replace);
                $this->keywords_parsed = preg_replace($strip_words,$strip_replace,$this->keywords_parsed);
            }

            // Stem any keywords
            if($this->booleanPartialMatch) {
                $this->keywords_parsed = $this->stemWords($this->keywords_parsed);
            }

            // If we force LIKE searching or we have 1 letter words in the search we do a LIKE search
            if($this->forceLIKE OR ($found_short_words = preg_match_all('/(^|\s)(\S{1})(?=$|\s)/u', $this->keywords_parsed,$matches_test)) > 1)  {
                if($found_short_words) {
                    $this->keywords_parsed = '"'.$this->keywords_parsed.'"';
                    $this->searchListingFields = array('l.title');
                    $this->likeOnlyMatchBeginning = true;
                }
                $where[] = $this->parseForLIKE($this->keywords_parsed,$this->searchListingFields);
                if(is_numeric($this->category_parsed) AND $this->findMatches) {
                    $this->matchingCategories = $this->PMDR->get('Categories')->getMatching($this->parseForLIKE($this->keywords_parsed,$this->matchingCategoriesFields));
                }
            } else {
                $short_words = $this->extractShortWords($this->keywords_parsed);
                $stop_words = $this->extractStopWords($this->keywords_parsed);
                $like_words = array_merge($stop_words,$short_words);


                $this->keywords_parsed = Strings::minimize_spacing($this->keywords_parsed);
                if(count($like_words) > 0) {
                    $like_words_query = $this->parseForLIKE($like_words,$this->likeShortWordLookin);
                }

                $listing_parsed = array();
                if(!empty($this->keywords_parsed)) {
                    $listing_parsed = $this->parseForFulltext($this->keywords_parsed,$this->searchListingFields,'l.title',$this->queryExpansion);
                    if(count($like_words) > 0) {
                        $where[] = '('.$listing_parsed['where'].' '.(($this->likeShortWordRequire) ? 'AND' : 'OR').' '.$like_words_query.')';
                    } else {
                        $where[] = $listing_parsed['where'];
                    }
                    if(count($this->otherFields[0]) > 0) {
                        $where[(count($where)-1)] = '('.$where[(count($where)-1)].' OR '.implode(' OR ',$this->otherFields[0]).')';
                    }
                } else {
                    if(count($like_words) > 0) {
                        if(count($this->otherFields[0]) > 0) {
                            $where[] = '('.$like_words_query.' OR '.implode(' OR ',$this->otherFields[0]).')';
                        } else {
                            $where[] = $like_words_query;
                        }
                    } elseif(value($this->otherFields,0) AND count($this->otherFields[0])) {
                        $where[] = '('.implode(' OR ',$this->otherFields[0]).')';
                    }
                }

                // Get matching categories via fulltext
                if(!is_numeric($this->category_parsed) AND $this->findMatches) {
                    if(trim($this->category_parsed) != '') {
                        if($category_id = $this->PMDR->get('Categories')->getByTitle($this->category_parsed)) {
                            $this->category_parsed = $category_id;
                        }
                    } else {
                        if(count($like_words) > 0) {
                            $like_words_query = $this->parseForLIKE($like_words,$this->matchingCategoriesFields);
                        }
                        if(!empty($this->keywords_parsed)) {
                            $category_parsed = $this->parseForFulltext($this->keywords_parsed,$this->matchingCategoriesFields,'title',false);

                            if(count($like_words) > 0) {
                                $category_where = '('.$category_parsed['where'].' '.(($this->likeShortWordRequire) ? 'AND' : 'OR').' '.$like_words_query.')';
                            } else {
                                $category_where = $category_parsed['where'];
                            }
                        } else {
                            if(count($like_words) > 0) {
                                $category_where = $like_words_query;
                            }
                        }
                        if(isset($category_where)) {
                            $this->matchingCategories = $this->PMDR->get('Categories')->getMatching($category_where);
                        }
                    }
                }
                unset($category_where);
                unset($like_words_query);
                unset($short_words);
                unset($like_words);
                unset($stop_words);
            }

            // Handle category input
            if(is_array($this->category_parsed)) {
                $category_join = "INNER JOIN ".T_LISTINGS_CATEGORIES." lc ON l.id=lc.list_id";
                $category_join .= " AND lc.cat_id IN(".implode(',',$this->PMDR->get('Cleaner')->clean_db($this->category_parsed)).")";
            } elseif(is_numeric($this->category_parsed)) {
                if($this->primaryCategoryOnly) {
                    $category_join = '';
                } else {
                    $category_join = "INNER JOIN ".T_LISTINGS_CATEGORIES." lc ON l.id=lc.list_id";
                }
                // Get the category node to do some checks on it
                if($this->categorySearchChildren) {
                    if(!$this->categoryNode) {
                        if($this->categoryNode = $this->db->GetRow("SELECT id, left_, right_ FROM ".T_CATEGORIES." WHERE id=?",array($this->category_parsed))) {
                            $this->categoryNode['is_leaf'] = (($this->categoryNode['right_']-$this->categoryNode['left_'])==1) ? true : false;
                            if(!$this->categoryNode['is_leaf']) {
                                $this->categoryNode['children'] = $this->db->GetCol("SELECT id FROM ".T_CATEGORIES." WHERE left_ BETWEEN ".$this->categoryNode['left_']." AND ".$this->categoryNode['right_']);
                            }
                        }
                    }
                    if(!$this->categoryNode['is_leaf'] AND $this->categoryNode AND count($this->categoryNode['children'])) {
                        if($this->primaryCategoryOnly) {
                            $where[] = "l.primary_category_id IN (".implode(',',$this->categoryNode['children']).")";
                        } else {
                            $where[] = "lc.cat_id IN (".implode(',',$this->categoryNode['children']).")";
                            // Since a listing could be in more than one of the categories we have to group by so we dont get duplicate results, dont do it if we get count as it gives funky results
                            // Only group by if we are getting results, not the count
                            if(!$count) $groupBy[] = 'l.id';
                        }
                    } else {
                        if($this->primaryCategoryOnly) {
                            $where[] = "l.primary_category_id=".$this->PMDR->get('Cleaner')->clean_db($this->category_parsed);
                        } else {
                            $category_join .= " AND lc.cat_id=".$this->PMDR->get('Cleaner')->clean_db($this->category_parsed);
                        }
                    }
                } else {
                    if($this->primaryCategoryOnly) {
                        $where[] = "l.primary_category_id=".$this->PMDR->get('Cleaner')->clean_db($this->category_parsed);
                    } else {
                        $category_join .= " AND lc.cat_id=".$this->PMDR->get('Cleaner')->clean_db($this->category_parsed);
                    }
                }
            }

            $location_join = '';
            if(!is_null($this->location_id) AND !empty($this->location_id)) {
                if(is_array($this->location_id)) {
                    $where[] = "l.location_id IN (".implode(',',$this->PMDR->get('Cleaner')->clean_db($this->location_id,false)).")";
                } else {
                    if($this->locationSearchChildren) {
                        if(!$this->locationNode) {
                            if($this->locationNode = $this->PMDR->get('Locations')->getNode($this->location_id)) {
                                $this->locationNode['is_leaf'] = (($this->locationNode['right_']-$this->locationNode['left_'])==1) ? true : false;
                                if(!$this->locationNode['is_leaf']) {
                                    $location_join = "INNER JOIN ".T_LOCATIONS." loc ON l.location_id=loc.id";
                                    $this->locationNode['children'] = $this->db->GetCol("SELECT id FROM ".T_LOCATIONS." WHERE left_ BETWEEN ".$this->locationNode['left_']." AND ".$this->locationNode['right_']);
                                }
                            }
                        }
                        if(!$this->locationNode['is_leaf'] AND $this->locationNode) {
                            $where[] = "l.location_id IN (".implode(',',$this->locationNode['children']).")";
                        } else {
                            $where[] = "l.location_id=".$this->PMDR->get('Cleaner')->clean_db($this->location_id);
                        }
                    } else {
                        $where[] = "l.location_id=".$this->PMDR->get('Cleaner')->clean_db($this->location_id);
                    }
                }
            }

            // Add rating check
            if(!empty($this->rating)) {
                $where[] = " rating >=".$this->PMDR->get('Cleaner')->clean_db($this->rating);
            }

            // Add email check
            if(!empty($this->email)) {
                $where[] = " mail=".$this->PMDR->get('Cleaner')->clean_db($this->email);
            }

            // Add required fields to use with AND
            if(value($this->otherFields,1) AND count($this->otherFields[1]) > 0) {
                foreach($this->otherFields[1] as $field_query) {
                    $where[] = ' '.$field_query;
                }
            }

            // Run the partial regex on the zip code if one is set
            if(!is_null($this->zipPartialRegex) AND !empty($this->zip)) {
                $zip_matches = array();
                if(preg_match('/'.$this->zipPartialRegex.'/',$this->zip,$zip_matches)) {
                    if(isset($zip_matches[1]) AND !empty($zip_matches[1])) {
                        $this->zip = $zip_matches[1];
                    }
                }
                unset($zip_matches);
            }
            $zip_equation = '';

            // Do a radius search if we have miles as an input, if not search regular on zip code
            $zip_distance = '';
            $zip_join = '';
            if(!empty($this->zipMiles)) {
                if(!is_null($this->latitude) AND !is_null($this->longitude)) {
                    $zip_equation = "SQRT(POW(69.1*(l.latitude-".$this->latitude."),2)+POW(53*(l.longitude-".$this->longitude."),2))";
                } elseif(!empty($this->zip) OR $this->zipLookup) {
                    if(!empty($this->zip)) {
                        // Get the entered zip code from the database to get its longitude and latitude
                        // If the allow partial setting is turned on, just get the first one that matches
                        if($this->zipAllowPartial) {
                            $zipData = $this->db->GetRow("SELECT * FROM ".T_ZIP_DATA." WHERE REPLACE(zipcode,' ','') LIKE ".$this->PMDR->get('Cleaner')->clean_db(str_replace(' ','',$this->zip)."%")." LIMIT 1");
                        } else {
                            $zipData = $this->db->GetRow("SELECT * FROM ".T_ZIP_DATA." WHERE REPLACE(zipcode,' ','')=?",array(str_replace(' ','',$this->zip)));
                        }
                        $include_location_sql = " OR l.listing_zip='".$zipData['zipcode']."'";
                    } elseif($this->zipLookup AND !empty($this->location_parsed)) {
                        $location_parsed_formatted = '+'.implode(' +',preg_split('/\W+/u',$this->location_parsed));
                        $zipData = $this->db->GetRow("SELECT zipcode, ((MIN(lat)+MAX(lat))/2) AS lat, ((MIN(lon)+MAX(lon))/2) AS lon FROM ".T_ZIP_DATA." WHERE MATCH(city,state) AGAINST (".$this->PMDR->get('Cleaner')->clean_db($location_parsed_formatted)." IN BOOLEAN MODE) GROUP BY city, state");
                        $include_location_sql = " OR MATCH(location_search_text) AGAINST (".$this->PMDR->get('Cleaner')->clean_db($location_parsed_formatted)." IN BOOLEAN MODE)";
                        $include_location_match = ", MATCH(location_search_text) AGAINST (".$this->PMDR->get('Cleaner')->clean_db($location_parsed_formatted)." IN BOOLEAN MODE) AS location_match";
                        array_unshift_assoc($this->sortByDistance,'location_match','DESC');
                        unset($location_parsed_formatted);
                    }
                    // If we found a zip code from the database...
                    if($zipData) {
                        $zip_equation = ($this->distanceType == 'kilometers') ? '1.609344*' : '';
                        if($this->zipUseDatabase) {
                            // Join the appropriate table on zip code so we can get a distance value
                            $zip_join = "INNER JOIN ".T_ZIP_DATA." ON l.listing_zip=".T_ZIP_DATA.".zipcode";
                            $zip_equation = "SQRT(POW(69.1*(lat-".$zipData['lat']."),2)+POW(53*(lon-".$zipData['lon']."),2))";
                        } else {
                            $zip_equation = "SQRT(POW(69.1*(l.latitude-".$zipData['lat']."),2)+POW(53*(l.longitude-".$zipData['lon']."),2))";
                        }
                    } else {
                        // We didn't find the entered zip code or location, so return 0 results by comparing the radius to NULL
                        $zip_equation = "NULL";
                    }
                }
                if(!empty($zip_equation)) {
                    $zip_equation = (($this->distanceType == 'kilometers') ? '1.609344*' : '').$zip_equation;
                    // If we are not getting a simple count, go ahead and query for the distance value truncating to 2 decimal points
                    $zip_distance = ", TRUNCATE($zip_equation,2) AS zip_distance".$include_location_match;
                    // Form the final query with the zip miles input
                    $where[] = " ($zip_equation <= ".$this->PMDR->get('Cleaner')->clean_db($this->zipMiles).$include_location_sql.")";;
                    if(!is_null($this->serviceDistance)) {
                        $where[] = " zip_distance <= ".$this->PMDR->get('Cleaner')->clean_db($this->serviceDistance);
                    }
                    if($this->autoSortByDistance) {
                        $this->sortBy = $this->sortByDistance;
                    }
                }
            }

            if(!empty($this->zip) AND empty($zip_equation)) {
                // If we allow partial matching do a LIKE, if not make it equal the zip
                if($this->zipAllowPartial) {
                    $where[] = " REPLACE(listing_zip,' ','') LIKE ".$this->PMDR->get('Cleaner')->clean_db(str_replace(' ','',$this->zip)."%");
                } else {
                    $where[] = " REPLACE(listing_zip,' ','')=".$this->PMDR->get('Cleaner')->clean_db(str_replace(' ','',$this->zip));
                }
            }
            unset($zip_equation);
        }

        if(!is_null($this->listing_status)) {
            $where[] = " l.status=".$this->PMDR->get('Cleaner')->clean_db($this->listing_status);
        }

        // Piece together the entire query
        $query = "SELECT ";
        if($this->count_separate == false) {
            $query .= "SQL_CALC_FOUND_ROWS";
        }
        if($count) {
            // We do a count by distict here if we need to group by for the categories (prevent duplicates) because when counting you have to make sure the id is distinct
            $query .= !in_array('l.id',$groupBy) ? "COUNT(DISTINCT(l.id)) " : "COUNT(*) as count ";
        } else {
            $query .= ($join_user OR $order_join) ? " listings" : " l";
            $query .=".* $select_user $select_order ";
            if($join_user OR $order_join) {
                $query .= "FROM (SELECT l.* ";
            }
            $query .= value($listing_parsed,'score').' '.$zip_distance.' ';
        }
        $query .= "FROM ".T_LISTINGS." l $category_join $location_join $zip_join ";

        if(count($where)) {
            $query .= 'WHERE '.implode(' AND ',$where);
        }

        if(count($groupBy) > 0) {
            $query .= ' GROUP BY '.implode(',',$groupBy);
        }

        // Order the results
        if(!isset($listing_parsed['score'])) unset($this->sortBy['score']);
        // array_filter is used because unsetting only one element on the array still leaves an array with one blank element
        if(!$count AND count(array_filter($this->sortBy))) {
            $query .= ' ORDER BY ';
            foreach($this->sortBy as $field=>$order) {
                if($field != '') {
                    $query .= $field.' '.$order.',';
                }
            }
            $query = rtrim($query,',');
        }

        if(!$count) {
            if(!is_null($this->limit1)) {
                if($this->limit1 < 0) {
                    $this->limit1 = 0;
                }
                $query .= ' LIMIT '.$this->limit1;
                if(!is_null($this->limit2)) {
                    $query .= ','.$this->limit2;
                }
            } else {
                $query .= ' LIMIT '.$this->totalResultLimit;
            }
        }

        if(!$count AND ($join_user OR $order_join)) {
            $query .= ") AS listings $join_user $order_join";
        }

        return $query;
    }
}
?>