<?php
/**
* Paging Class
* Pages results from database queries or arrays
*/
class Paging {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Current page
    * @var int
    */
    var $currentPage = 1;
    /**
    * Number of results displayed per page
    * @var int
    */
    var $resultsNumber = 10;
    /**
    * Number of page links
    * @var int
    */
    var $linksNumber = 10;
    /**
    * Starting limit of records on current page
    * @var int
    */
    var $limit1;
    /**
    * Number of results to get per page
    * @var int
    */
    var $limit2;
    /**
    * Total number of results in set
    * @var int
    */
    var $totalResults = 0;
    /**
    * All paging details data store
    * @var array
    */
    var $resultArray = array();
    /**
    * Variable used in URL to determine page number
    * @var string
    */
    var $pageVariable = 'page';
    /**
    * Mod rewrite flag to determine URL format
    * @var boolean
    */
    var $modRewrite = MOD_REWRITE;
    /**
    * All results on one page
    * @var boolean
    */
    var $all_one_page = false;
    /**
    * Hide first page number in URL to prevent duplicate content
    * @var boolean
    */
    var $hide_first_page = true;

    /**
    * Paging class constructor
    * @param object $PMDR Registry
    * @param int $total_results Total number of results in set
    * @return Paging
    */
    function __construct($PMDR, $parameters = array()) {
        $this->PMDR = $PMDR;
        if(isset($parameters['page_size'])) {
            $this->resultsNumber = $parameters['page_size'];
        }
        // Get the current page
        $this->currentPage = $this->getCurrentPage();
        // Get the previous page
        $this->previousPage = $this->getPreviousPage();
        // Set the number of results per page
        $this->setResultsNumber($this->resultsNumber);
        // If we already know the total results, set it
        if(isset($parameters['total_results'])) {
            $this->setTotalResults($parameters['total_results']);
        }
    }

    /**
    * Set the number of results per page
    * @param int $number Number of results
    */
    function setResultsNumber($number) {
        $this->resultsNumber = $number;
        $this->limit1 = $this->getStartLimit();
        $this->limit2 = $number;
    }

    /**
    * Set total number of results
    * @param int $number Number of total results
    */
    function setTotalResults($number) {
        if($this->all_one_page) {
            $this->setResultsNumber($number);
        }
        $this->totalResults = $number;
        $this->totalPages = $this->getTotalPages();
    }

    /**
    * Get all paging details in an array
    * @param mixed $results_number Number of results, null if a value is not passed in
    * @param mixed $total_results Number of total results, null if a value is not passed in
    * @return array Paging details
    */
    function getPageArray($results_number = null, $total_results = null) {
        if(!is_null($results_number)) {
            $this->setResultsNumber($results_number);
        }
        if(!is_null($total_results)) {
            $this->setTotalResults($total_results);
        }
        // Get all paging data
        $this->resultArray = array(
           'previous_page' => $this->previousPage,
           'next_page' => $this->getNextPage(),
           'current_page' => $this->currentPage,
           'total_pages' => $this->totalPages,
           'total_results' => $this->totalResults,
           'page_numbers' => $this->getNumbers(),
           'limit1' => $this->limit1,
           'limit2' => $this->limit2,
           'start_offset' => $this->getStartOffset(), // First record number, taking into account that DB records start at 0 (so we add 1)
           'end_offset' => $this->getEndOffset(),  // Last record number, taking into account that DB records start at 0
           'page_size' => $this->resultsNumber,
           'first_url' => false,
           'previous_url' => false,
           'next_url' => false,
           'last_url' => false,
           'page_sizes' => array()
        );

        if(!$this->all_one_page AND $this->resultArray['total_results'] > 10) {
            $this->resultArray['page_sizes'] = array(
                10=>rebuild_url(array('page_size'=>10),array('page','page_size')),
                20=>rebuild_url(array('page_size'=>20),array('page','page_size')),
                50=>rebuild_url(array('page_size'=>50),array('page','page_size')),
                100=>rebuild_url(array('page_size'=>100),array('page','page_size'))
            );
        }

        // Get the HTML generated from the paging data
        $this->loadHTML();

        return $this->resultArray;
    }

    /**
    * Get total number of pages
    * @return int Number of pages
    */
    function getTotalPages() {
        if($this->totalResults == 0 OR $this->resultsNumber == 0) {
            return 0;
        } else {
            return ceil($this->totalResults / $this->resultsNumber);
        }
    }

    /**
    * Get start limit in set of records
    * @return int Start limit
    */
    function getStartLimit() {
        $start_limit = $this->resultsNumber * ($this->currentPage - 1);
        return ($start_limit ? $start_limit : 0);
    }

    /**
    * Get start offset to be used in database queries
    * @return int Start offset
    */
    function getStartOffset() {
        if($this->totalResults == 0 OR $this->resultsNumber == 0) {
            return 0;
        }
        return $this->getStartLimit() + 1;
    }

    /**
    * Get end offset to be used in database queries
    * @return int End offset
    */
    function getEndOffset() {
        if(($offset = $this->currentPage * $this->resultsNumber) > $this->totalResults) {
            return $this->totalResults;
        } else {
            return $offset;
        }
    }

    /**
    * Get current page number
    * @return int Page number
    */
    function getCurrentPage() {
        return (isset($_GET[$this->pageVariable]) AND !empty($_GET[$this->pageVariable])) ? intval($_GET[$this->pageVariable]) : $this->currentPage;
    }

    /**
    * Get previous page number
    * @return int Previous page number
    */
    function getPreviousPage() {
        return ($this->currentPage > 1) ? $this->currentPage - 1 : false;
    }

    /**
    * Get next page number
    * @return int Previous page number
    */
    function getNextPage() {
        return ($this->currentPage < $this->totalPages) ? $this->currentPage + 1 : false;
    }

    /**
    * Get start page number to show range of page numbers
    * @return int Start number
    */
    function getStartNumber() {
        $links_per_page_half = floor($this->linksNumber / 2);
        if($this->currentPage <= $links_per_page_half || $this->totalPages <= $this->linksNumber) {
            return 1;
        } elseif($this->currentPage >= ($this->totalPages - $links_per_page_half)) {
            return $this->totalPages - $this->linksNumber + 1;
        } else {
            return $this->currentPage - $links_per_page_half;
        }
    }

    /**
    * Get end page number to show range of page numbers
    * @return int End number
    */
    function getEndNumber() {
        return ($this->totalPages < $this->linksNumber) ? $this->totalPages : $this->getStartNumber() + $this->linksNumber - 1;
    }

    /**
    * Get array of page numbers
    * @return array Page numbers
    */
    function getNumbers() {
        $numbers = array();
        for($i=$this->getStartNumber(); $i<=$this->getEndNumber(); $i++) {
            $numbers[] = $i;
        }
        return $numbers;
    }

    /**
    * Get URL for page numbers
    * @param int $number Page number
    * @return stirng URL for page number
    */
    function getURL($number) {
        if($this->hide_first_page AND $number == 1) {
            return rebuild_url(array(),array('page'));
        } else {
            return rebuild_url(array('page'=>$number));
        }
    }

    /**
    * Load all HTML from the page data
    */
    function loadHTML() {
        $page_numbers = $this->resultArray['page_numbers'];
        $this->resultArray['page_numbers'] = array();

        if($this->resultArray['current_page']!= 1) {
            $this->resultArray['first_url'] = $this->getURL(1);
        }

        if($this->resultArray['previous_page']) {
            $this->resultArray['previous_url'] = $this->getURL($this->resultArray['previous_page']);
            $this->PMDR->set('previous_url',$this->resultArray['previous_url']);
        }

        foreach($page_numbers as $key=>$page_number) {
            $this->resultArray['page_numbers'][$key]['number'] = $page_number;
            $this->resultArray['page_numbers'][$key]['url'] = $this->getURL($page_number);
        }

        if($this->resultArray['next_page']) {
            $this->resultArray['next_url'] = $this->getURL($this->resultArray['next_page']);
            $this->PMDR->set('next_url',$this->resultArray['next_url']);
        }
        if($this->resultArray['current_page'] < $this->resultArray['total_pages']) {
            $this->resultArray['last_url'] = $this->getURL($this->resultArray['total_pages']);
        }
        if(!$this->modRewrite) {
            $this->resultArray['page_select'] = '<select id="page" name="page" class="page-numbers" onchange="window.location = \''.rebuild_url(array(),array('page'),true).'page=\'+$(\'#page\').val()">';

            foreach($page_numbers as $page_number) {
                $this->resultArray['page_select'] .= '<option value="'.$page_number.'"';
                if($this->resultArray['current_page'] == $page_number) {
                    $this->resultArray['page_select'] .= ' selected';
                }
                $this->resultArray['page_select'] .= '>'.$page_number.'</option>';
            }
            $this->resultArray['page_select'] .= '</select>';
        }
    }
}
?>