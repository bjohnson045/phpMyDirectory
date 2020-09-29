<?php
/**
* Class TableList
* Used to create an HTML table list of records.  Integrates paging and uses specified template
*/
class TableList {
    /**
    * Registry
    * @var object Registry
    */
    var $PMDR;
    /**
    * Table Columns
    * @var array
    */
    var $columns = array();
    /**
    * Table Columns index
    * @var array
    */
    var $columns_index = array();
    /**
    * Template object used for layout
    * @var object
    */
    var $template;
    /**
    * Template file path/name used for rows
    * @var string
    */
    var $columns_template = null;
    /**
    * Template object used for layout
    * @var object
    */
    var $page_template;
    /**
    * Paging data used for display
    * @var array
    */
    var $page_data;
    /**
    * Records to display
    * @var array
    */
    var $records;
    /**
    * Display all records on one page despite paging data
    * @var boolean
    */
    var $all_one_page = false;
    /**
    * Number of records per page
    * @var integer
    */
    var $page_size = 10;
    /**
    * Content to show when there are now results
    * @var string
    */
    var $empty_content = '';
    /**
    * Encapsulate the table list in a form
    * @var boolean
    */
    var $form = false;
    /**
    * Paging object
    * @var object Paging
    */
    var $paging;
    /**
    * Checkbox options for each result
    * @var mixed
    */
    var $checkbox_options = array();
    /**
    * Column to use for the checkbox values
    * @var string
    */
    var $checkbox_value = null;
    /**
    * Enable sorting via jQuery
    * @var boolean
    */
    var $sortable = false;
    /**
    * Table name to use for sorting
    */
    var $sortable_table = null;
    /**
    * Label to use for sorting
    */
    var $sortable_label = null;

    /**
    * TableList Constructor
    * @param object Registry
    * @param string Template File
    */
    function __construct($PMDR, $parameters = array()) {
        $this->PMDR = $PMDR;
        $this->paging = $this->PMDR->get('Paging',$parameters);
        if(isset($parameters['mod_rewrite'])) {
            $this->paging->modRewrite = $parameters['mod_rewrite'];
        } else {
            $this->paging->modRewrite = false;
        }
        if(isset($parameters['template'])) {
            $this->template = $this->PMDR->getNew('Template',$parameters['template']);
        }
    }

    /**
    * Add a column to the column array
    * @param string $name Name of the column used to associate data
    * @param string $title Label of the column
    * @return void
    */
    function addColumn($name, $title=null, $sort = false, $nowrap = false, $style = null) {
        if(is_null($title)) {
            if($name == 'manage') {
                if(!($title = $this->PMDR->getLanguage(PMD_SECTION.'_'.$name))) {
                    $title = null;
                }
            } elseif(!($title = $this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$name))) {
                $title = null;
            }
        }

        if($name == 'manage') {
            $nowrap = true;
        }

        $this->columns[] = array('name'=>$name,'title'=>$title,'nowrap'=>$nowrap,'style'=>$style,'sort_url'=>false,'sort_image'=>false);
        $this->columns_index[$name] = count($this->columns)-1;
        if($sort) {
            $this->addSorting($name);
        }
    }

    /**
    * Add sorting to the column(s)
    * @param mixed $columns Columns to add sorting to
    * @return void
    */
    function addSorting($columns) {
        if(!is_array($columns)) {
            $columns = array($columns);
        }
        foreach($columns AS $column) {
            if(isset($this->columns[$this->columns_index[$column]])) {
                $sort = rebuild_url(array('sort'=>$column,'sort_direction'=>((isset($_GET['sort_direction']) AND $_GET['sort_direction'] == 'ASC') ? 'DESC' : 'ASC')));
                if(isset($_GET['sort']) AND $_GET['sort'] == $column) {
                    if($_GET['sort_direction'] == 'ASC') {
                        $sort_image = 'down';
                    } else {
                        $sort_image = 'up';
                    }
                }
                $this->columns[$this->columns_index[$column]]['sort_url'] = $sort;
                $this->columns[$this->columns_index[$column]]['sort_image'] = isset($sort_image) ? (string) $sort_image : false;
            }
            unset($sort_image);
        }
    }

    /**
    * Add checkboxes to each row
    * @param array $options Options for the dropdown
    * @param string $value Column to be used for the values
    * @param boolean $form Encapsulate the table with a form
    */
    function addCheckbox($options, $value = 'id', $form = true) {
        $this->checkbox_options = $options;
        $this->form = $form;
        $this->checkbox_value = $value;
    }

    /**
    * Add records
    * @param array $records Records to display
    * @return void
    */
    function addRecords($records) {
        $this->records = $records;
    }

    /**
    * Set template to use for display
    * @param string $template Template File
    * @return void
    */
    function setTemplate($template) {
        $this->template = $template;
    }

    /**
    * Set the total number of results we will be displaying.  Used for generating page data.
    * @param integer $result_count Number of results
    * @param integer $page_size Number of records to show per page
    * @return void
    */
    function setTotalResults($result_count, $page_size = null) {
        if(!is_numeric($result_count)) {
            trigger_error('Results amount not an integer.',E_USER_WARNING);
            $result_count = 0;
        }
        $result_count = intval($result_count);
        if(!is_null($page_size)) {
            $this->paging->setResultsNumber($page_size);
        }
        if($this->all_one_page) {
            $this->paging->all_one_page = $this->all_one_page;
            $this->paging->setResultsNumber($result_count);
        }
        $this->paging->setTotalResults($result_count);
        $this->page_data = $this->paging->getPageArray();
    }

    /**
    * Adds a paging object to the table list
    * @param object $paging
    */
    function addPaging($paging) {
        $this->paging = $paging;
        $this->page_data = $this->paging->getPageArray();
    }

    /**
    * Make the table sortable
    * @param string $table Table to use for sorting
    * @param string $label Label to use for sorting
    */
    function sortable($table, $label = 'Order') {
        $this->sortable = true;
        $this->sortable_table = $table;
        $this->sortable_label = $label;
        $this->all_one_page = true;
    }

    /**
    * Return the template ready for display
    * @return object Template
    */
    function render() {
        $this->page_template->set('page',$this->page_data);
        $this->template->set('columns_template',(!is_null($this->columns_template) ? $this->columns_template : false));
        $this->template->set('page_navigation',$this->page_template);
        $this->template->set('columns',$this->columns);
        $this->template->set('page',$this->page_data);
        $this->template->set('records',$this->records);
        $this->template->set('form',$this->form);
        $this->template->set('empty_content',$this->empty_content);
        $this->template->set('checkbox_options',$this->checkbox_options);
        $this->template->set('checkbox_value',$this->checkbox_value);
        $this->template->set('table_summary','');
        $this->template->set('all_one_page',$this->all_one_page);
        $this->template->set('sortable',$this->sortable);
        $this->template->set('sortable_table',$this->sortable_table);
        $this->template->set('sortable_label',$this->sortable_label);
        return $this->template;
    }

    /**
    * Add all of the table list data and variables to a template
    * @param object $template
    * @return void
    */
    function addToTemplate(&$template) {
        $this->page_template->set('page',$this->page_data);
        $template->set('columns_template',(!is_null($this->columns_template) ? $this->columns_template : false));
        $template->set('page_navigation',$this->page_template);
        $template->set('columns',$this->columns);
        $template->set('page',$this->page_data);
        $template->set('records',$this->records);
        $template->set('form',$this->form);
        $template->set('empty_content',$this->empty_content);
        $template->set('checkbox_options',$this->checkbox_options);
        $template->set('checkbox_value',$this->checkbox_value);
        $template->set('table_summary','');
        $template->set('all_one_page',$this->all_one_page);
        $template->set('sortable',$this->sortable);
        $template->set('sortable_table',$this->sortable_table);
        $template->set('sortable_label',$this->sortable_label);
    }
}
?>