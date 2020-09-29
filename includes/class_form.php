<?php
/**
* Class Form
* Handle form creation, auto generation, and error processing
*/
class Form {
    /**
    * Registry
    * @var Object
    */
    var $PMDR;
    /**
    * Form action URL
    * @var string
    */
    var $action;
    /**
    * Form method (GET or POST)
    * @var string
    */
    var $method;
    /**
    * Encoding type for the form (i.e. multipart/form-data)
    * @var string
    */
    var $enctype;
    /**
    * Form ID
    * @var string
    */
    var $id;
    /**
    * Form name
    * @var string
    */
    var $name = null;
    /**
    * Form target URL
    * @var string
    */
    var $target = null;
    /**
    * CSS class for the form
    * @var string
    */
    var $class;
    /**
    * Assign access keys to the field labels
    * @var boolean
    */
    var $assign_access_keys = false;
    /**
    * Array of errors from submitted form
    * @var array
    */
    var $errors = array();
    /**
    * Array of fieldsets in form
    * @var array
    */
    var $fieldsets = array();
    /**
    * Array of all form elements/fields
    * @var array
    */
    var $elements = array();
    /**
    * Array of pickers used to select data for a form field
    * @var array
    */
    var $pickers = array();
    /**
    * Access keys - keep track to avoid duplicates
    * @var array
    */
    var $access_keys = array();
    /**
    * Validator objects used to validate input
    * @var array
    */
    var $validators = array();
    /**
    * Help notices next to fields
    * @var array
    */
    var $help = array();
    /**
    * Notes underneath fields
    * @var array
    */
    var $notes = array();
    /**
    * Label width for longer labels, overrides css class
    * @var int
    */
    var $label_width = null;
    /**
    * Allowed HTML elements for specific form fields using index as the field name
    * @var mixed
    */
    var $allowed_html = array();
    /**
    * Default allowed HTML tags used for all fields if field specific allowed html tags are not set
    * @var mixed
    */
    var $allowed_html_tags = '';
    /**
    * Suffix for field labels
    * @var string
    */
    var $label_suffix = ':';
    /**
    * Text placed after fields if a field is required
    * @var string
    */
    var $required_text = '*';
    /**
    * Field dependencies
    */
    var $dependencies = array();
    /**
    * Form attributes (i.e. class, style, etc)
    */
    var $attributes = array();

    /**
    * Form Constructor
    * @param object $PMDR Registry
    * @param string $action Form action
    * @param string $method Form method
    * @return void
    */
    function __construct($PMDR, $action='', $method='POST', $attributes = array()) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $this->action = $action;
        $this->method = $method;
        $this->id = $this->name = uniqid('form-');
        $this->attributes = $attributes;
    }

    /**
    * Get the template for a field type
    * @param string $type
    * @return object Template object
    */
    function getFormTemplate($type = '') {
        return $this->PMDR->getNew('Template',$this->getFormTemplateFile($type));
    }

    /**
    * Get the template path
    * @param string $type
    * @return string Path
    */
    function getFormTemplatePath() {
        if(PMD_SECTION == 'public' OR PMD_SECTION == 'members') {
            $path = PMDROOT.TEMPLATE_PATH.'blocks/';
        } else {
            $path = PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_';
        }
        return $path;
    }

    /**
    * Get the template file name/path for a field type
    * @param string $type
    * @return string File name/path
    */
    function getFormTemplateFile($type = '') {
        if(!empty($type)) {
            $type = '_'.$type;
        }
        return $this->getFormTemplatePath().'form'.$type.'.tpl';
    }

    /**
    * Used to clean the form output utilizing the cleaner class
    * @param mixed $mixed
    * @param boolean $strip
    * @return mixed
    */
    function clean_output($mixed) {
        return $this->PMDR->get('Cleaner')->clean_form_output($mixed);
    }

    /**
    * Add field set to form
    * @param string $name Name of fieldset
    * @param array $attributes Fieldset attributes (legend, or help text)
    * @return void
    */
    function addFieldSet($name, $attributes = array()) {
        if(!isset($attributes['legend'])) {
            if($this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$name)) {
                $attributes['legend'] = $this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$name);
            }
        }
        if(!isset($attributes['help'])) {
            if($this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$name.'_help')) {
                $attributes['help'] = $this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$name.'_help');
            }
        }
        if(isset($this->fieldsets[$name])) {
            $this->fieldsets[$name] = array_merge($this->fieldsets[$name],$attributes);
        } else {
            $this->fieldsets[$name] = $attributes;
        }
        if(isset($attributes['help']) AND ($fieldset_help_template = $this->getFormTemplate('fieldset_help'))) {
            $fieldset_help_template->set('name',$name);
            $fieldset_help_template->set('help',$attributes['help']);
            $this->fieldsets[$name]['help'] = $fieldset_help_template->render();
            unset($fieldset_help_template);
        } else {
            $this->fieldsets[$name]['help'] = null;
        }
    }

    function addDependency($name, $parameters) {
        $this->dependencies[$name] = $parameters;
    }

    function processDependency($name) {
        if(isset($this->dependencies[$name])) {
            $dependency = $this->dependencies[$name];
            if(!is_array($dependency['value'])) {
                $dependency['value'] = array($dependency['value']);
            }
            if($dependency['type'] == 'display') {
                $javascript = '
                <script type="text/javascript">
                $(document).ready(function() {
                    $("#'.$name.'-control-group").hide();';
                        if($this->elements[$dependency['field']]['type'] == 'radio' OR $this->elements[$dependency['field']]['type'] == 'checkbox') {
                            $javascript .= '
                            $("input[name=\''.$dependency['field'].'\']").change(function() {
                                if($("input[name=\''.$dependency['field'].'\']:checked").val() == null && $.inArray(0,'.json_encode($dependency['value']).') > -1) {
                                    $("#'.$name.'-control-group").show();
                                } else if($.inArray($("input[name=\''.$dependency['field'].'\']:checked").val(),'.json_encode($dependency['value']).') > -1) {
                                    $("#'.$name.'-control-group").show();
                                } else {
                                    $("#'.$name.'-control-group").hide();
                                }
                            });
                            $("input[name=\''.$dependency['field'].'\']").trigger("change");
                            ';
                        } else {
                            $javascript .= '
                            $("#'.$dependency['field'].'").change(function() {
                                if($.inArray($(this).val(),'.json_encode($dependency['value']).') > -1) {
                                    $("#'.$name.'-control-group").show();
                                } else {
                                    $("#'.$name.'-control-group").hide();
                                }
                            });
                            $("#'.$dependency['field'].'").trigger("change");
                            ';
                        }
                        $javascript .= '
                });
                </script>';
                $this->PMDR->loadJavascript($javascript,50);
            }
        }
    }

    /**
    * Add field
    * @param string $id
    * @param string $type (text, textarea, select, select_multiple, checkbox, radio, etc)
    * @param array $attributes (value, options, label, etc)
    */
    function addField($id, $type, $attributes = array()) {
        switch($type) {
            case 'tree_select_expanding':
            case 'tree_select_expanding_checkbox':
            case 'tree_select_expanding_radio':
                $this->PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/dynatree/skin/ui.dynatree.css" />',20);
                $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/plugins/jquery.cookies.js"></script>',20);
                $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/dynatree/jquery.dynatree.min.js"></script>',20);
                break;
            case 'date':
                $formats = array(
                    'm-d-Y'=>'mm dd yy',
                    'd-m-Y'=>'dd mm yy',
                    'Y-m-d'=>'yy mm dd',
                    'Y-d-m'=>'yy dd mm',
                );
                $this->PMDR->loadJavascript('
                <script type="text/javascript">
                $(document).ready(function() {
                    $("#'.$id.'").datepicker({
                        showOn: "focus",
                        defaultDate: "'.$this->PMDR->get('Dates_Local')->formatDateOutput($this->PMDR->get('Dates_Local')->dateNow()).'",
                        dateFormat: "'.str_replace(' ',$this->PMDR->getConfig('date_format_input_seperator'),$formats[$this->PMDR->getConfig('date_format_input')]).'",
                        changeYear: true,
                        changeMonth: true,
                        yearRange: "'.(date('Y')-100).':'.(date('Y')+30).'",
                        closeText: "X",
                        yearRange: "'.(date('Y')-100).':'.(date('Y')+30).'",
                        dayNames: ["'.implode('","',$this->PMDR->get('Dates')->getWeekDays()).'"],
                        dayNamesShort: ["'.implode('","',$this->PMDR->get('Dates')->getWeekDays(true)).'"],
                        dayNamesMin: ["'.implode('","',$this->PMDR->get('Dates')->getWeekDays(true,true)).'"],
                        monthNamesShort: ["'.implode('","',$this->PMDR->get('Dates')->getMonths(true)).'"],
                        monthNames: ["'.implode('","',$this->PMDR->get('Dates')->getMonths()).'"]
                    })
                });
                </script>',30);
                unset($formats);
                break;
            case 'datetime':
                $formats = array(
                    'm-d-Y'=>'mm dd yy',
                    'd-m-Y'=>'dd mm yy',
                    'Y-m-d'=>'yy mm dd',
                    'Y-d-m'=>'yy dd mm',
                );
                $this->PMDR->loadJavascript('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/plugins/jquery.timepicker.css" />',30);
                $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/plugins/jquery.timepicker.js"></script>',30);
                $this->PMDR->loadJavascript('
                <script type="text/javascript">
                $(document).ready(function() {
                    $("#'.$id.'").datetimepicker({
                        showOn: "focus",
                        defaultDate: "'.$this->PMDR->get('Dates_Local')->formatDateOutput($this->PMDR->get('Dates_Local')->dateNow()).'",
                        hour: '.$this->PMDR->get('Dates_Local')->getHour(true).',
                        minute: '.$this->PMDR->get('Dates_Local')->getMinute().',
                        dateFormat: "'.str_replace(' ',$this->PMDR->getConfig('date_format_input_seperator'),$formats[$this->PMDR->getConfig('date_format_input')]).'",
                        changeYear: true,
                        changeMonth: true,
                        ampm: '.($this->PMDR->getConfig('time_format_input') == 24 ? 'false' : 'true').',
                        yearRange: "'.(date('Y')-100).':'.(date('Y')+30).'",
                        closeText: "X",
                        currentText: "'.$this->PMDR->getLanguage('now').'",
                        timeText: "'.$this->PMDR->getLanguage('time').'",
                        hourText: "'.$this->PMDR->getLanguage('hour').'",
                        minuteText: "'.$this->PMDR->getLanguage('minute').'",
                        dayNames: ["'.implode('","',$this->PMDR->get('Dates')->getWeekDays()).'"],
                        dayNamesShort: ["'.implode('","',$this->PMDR->get('Dates')->getWeekDays(true)).'"],
                        dayNamesMin: ["'.implode('","',$this->PMDR->get('Dates')->getWeekDays(true,true)).'"],
                        monthNamesShort: ["'.implode('","',$this->PMDR->get('Dates')->getMonths(true)).'"],
                        monthNames: ["'.implode('","',$this->PMDR->get('Dates')->getMonths()).'"]
                    });
                });</script>',30);
                unset($formats);
                break;
            case 'htmleditor':
                $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/ckeditor/ckeditor.js"></script>',20);
                $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/ckeditor/adapters/jquery.js"></script>',25);
                break;
            case 'security_image':
                if(!$captcha = $this->PMDR->get('Captcha')) {
                    trigger_error('Invalid Captcha');
                } else {
                    if($javascript = $captcha->loadJavascript()) {
                        $this->PMDR->loadJavascript($javascript);
                    }
                    unset($javascript);
                }
                break;
            case 'password':
                if(isset($attributes['strength']) AND $attributes['strength']) {
                    $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/plugins/jquery.password_strength.js"></script>',15);
                }
                break;
            case 'color':
                $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/colorpicker/jquery.minicolors.js"></script>',15);
                $this->PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/colorpicker/jquery.minicolors.css" />',20);
                break;
            case 'currency':
                $this->addValidator($id, new Validate_Currency());
                break;
            case 'url':
                $this->addValidator($id, new Validate_URL(false));
                break;
            case 'text_tags':
                $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN.'bootstrap/js/bootstrap-tokenfield.js"></script>',15);
                $this->PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN.'bootstrap/css/bootstrap-tokenfield.css" />',20);
                break;
        }
        $attributes['type'] = $type;
        if(!isset($attributes['id'])) {
            $attributes['id'] = $id;
        }
        if(!isset($attributes['name'])) {
            $attributes['name'] = $id;
        }
        if(isset($attributes['class'])) {
            if(!is_array($attributes['class'])) {
                $attributes['class'] = array($attributes['class']);
            }
        } else {
            $attributes['class'] = array();
        }
        if(!isset($attributes['label'])) {
            if($this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$id)) {
                $attributes['label'] = $this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$id);
            } elseif($type == 'submit') {
                $attributes['label'] = $this->PMDR->getLanguage(PMD_SECTION.'_submit');
            }
        }
        if(!isset($attributes['help'])) {
            if($this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$id.'_help')) {
                $attributes['help'] = $this->PMDR->getLanguage(basename($_SERVER['PHP_SELF'],'.php').'_'.$id.'_help');
            }
        }
        if(isset($attributes['help']) AND !empty($attributes['help'])) {
            $attributes['help'] = '<a id="'.$attributes['id'].'_help" href="#" tabindex="-1">?</a> <script type="text/javascript">tooltip(\''.$attributes['id'].'_help\',\''.Strings::nl2br_replace(htmlspecialchars($attributes['help'], ENT_QUOTES)).'\',\''.(isset($attributes['help_title']) ? $attributes['help_title'] : '') .'\');</script>';
        } else {
            $attributes['help'] = null;
        }

        if(isset($attributes['fieldset'])) {
            $this->fieldsets[$attributes['fieldset']]['elements'][] = $id;
        } elseif($type == 'submit') {
            $this->fieldsets['submit']['elements'][] = $id;
        } else {
            end($this->fieldsets);
            $attributes['fieldset'] = key($this->fieldsets);
            $this->fieldsets[$attributes['fieldset']]['elements'][] = $id;
            reset($this->fieldsets);
        }

        if(!isset($attributes['no_trim'])) {
            $attributes['no_trim'] = false;
        }

        $this->elements[$id] = $attributes;

        if(isset($attributes['counter']) AND in_array($type,array('textarea','text'))) {
            $this->PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/plugins/jquery.charcounter.js"></script>',15);
            $this->PMDR->loadJavascript('
            <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function(){$("#'.$attributes['name'].'").charCounter('.$attributes['counter'].',{container_template:'.$this->PMDR->get('Cleaner')->output_js($this->getFormTemplate('counter')->render()).'});});
                //]]>
            </script>',20);
        }
    }

    /**
    * Set allowed HTML for a specific field
    * @param string $name Field name
    * @param array $tags HTML tags
    * @param array $attributes HTML attributes
    * @return void
    */
    function setAllowedHTML($name, $tags, $attributes) {
        if(!is_null($tags)) {
            $this->allowed_html[$name]['tags'] = $tags;
            $this->allowed_html[$name]['attributes'] = $attributes;
        } else {
            $this->allowed_html[$name] = null;
        }
    }

    /**
    * Get allowed HTML tags/attributes for a specific field
    * @param string $name Field name
    * @return void
    */
    function getAllowedHTML($name) {
        if(!isset($this->allowed_html[$name])) {
            return $this->allowed_html_tags;
        } else {
            return $this->allowed_html[$name];
        }
    }

    /**
    * Get field counter HTML
    * @param string $name Field name
    * @return void
    */
    function getFieldCounterHTML($name) {
        if(isset($this->elements[$name]['counter']) AND in_array($this->elements[$name]['type'],array('textarea','text','htmleditor'))) {
            return '<span id="'.$name.'_counter" class="counter">'.$this->elements[$name]['counter'].'</span>';
        }
    }

    /**
    * Add field picker
    * @param string $name Element name
    * @param string $type Type of the picker
    * @param array $paramters Array used for picker configuration
    * @return void
    */
    function addPicker($name, $type, $image = null, $parameters = array()) {
        switch($type) {
            case 'coordinates':
                if($coordinates_link = $this->getFormTemplate('coordinates_link')) {
                    $this->pickers[$name] = '
                    <script type="text/javascript">
                    $(function(){
                        $("#'.$name.'_window_link").click(function(e) {
                            e.preventDefault();
                            var longitude = document.getElementById("longitude").value;
                            var latitude = document.getElementById("latitude").value;';

                            if(!empty($parameters['coordinates'])) {
                                $coordinates = explode(',',$parameters['coordinates']);
                                if(count($coordinates) == 2) {
                                    $this->pickers[$name] .= '
                                    if((longitude == "" && latitude == "") || (longitude == 0.0000000000 && latitude == 0.0000000000)) {
                                        longitude = '.$coordinates[1].';
                                        latitude = '.$coordinates[0].';
                                    }'."\n";
                                }
                                unset($coordinates);
                            }

                            $this->pickers[$name] .= 'var map_url = "'.PMDROOT_RELATIVE.'/includes/data.php?type=get_map&latitude="+latitude+"&longitude="+longitude';

                            if(!empty($parameters['zoom'])) {
                                $this->pickers[$name] .= '+"&zoomLevel='.$parameters['zoom'].'"';
                            }

                            if(isset($parameters['marker']) ) {
                                $this->pickers[$name] .= '+"&marker='.$parameters['marker'].'"';
                            }

                            $this->pickers[$name] .= ';';
                            $this->pickers[$name] .= '
                            $(\'<div style="width: 100%"><iframe style="width: 100%; height: 100%" scrolling="no" frameborder="0" id="'.$name.'_iframe" src="\'+map_url+\'" /></div>\').dialog({
                                title: \''.$parameters['label'].'\',
                                autoOpen: true,
                                width: 525,
                                height: 400,
                                resizable: false,
                                open: function() { $(\''.$name.'_iframe\').attr(\'src\',map_url); }
                            });
                        });
                    });
                    </script>';
                    $coordinates_link->set('id',$name);
                    $coordinates_link->set('label',$parameters['label']);
                    $this->pickers[$name] .= $coordinates_link->render();
                }
                unset($coordindates_link);
            default:
                break;
        }
    }

    /**
    * Add element note
    * Adds text below the field
    * @param string $element Element name
    * @param string $note Note text
    * @return void
    */
    function addFieldNote($element, $note) {
        $this->notes[$element][] = $note;
    }

    /**
    * Get field note
    * @param string $element Field name
    * @return string
    */
    function getFieldNote($element) {

        return implode('<br>',$this->notes[$element]);
    }

    /**
    * Has field note
    * @param string $element Field name
    * @return string
    */
    function hasFieldNote($element) {
        return isset($this->notes[$element]);
    }

    /**
    * Get field set help
    * @param string $element Element name
    * @return string Help javascript string
    */
    function getFieldSetHelp($element) {
        return $this->fieldsets[$element]['help'];
    }

    /**
    * Get field set HTML
    * @param string $fieldset Fieldset name
    * @return string Generated HTML
    */
    function getFieldSetHTML($fieldset) {
        $html = '';
        if(isset($this->fieldsets[$fieldset]['elements']) AND count($this->fieldsets[$fieldset]['elements'])) {
            foreach($this->fieldsets[$fieldset]['elements'] AS $element) {
                $html .= $this->getFieldHTML($element);
            }
            return $html;
        }
    }

    /**
    * Add javascript to an element
    * @param string $field_name Element name
    * @param string $javascript Javascript code
    * @return void
    */
    function addJavascript($field_name, $attribute_name, $javascript) {
        $this->elements[$field_name][$attribute_name] = $javascript;
    }

    /**
    * Add validator processed on form submission
    * @param string $element Element name
    * @param string $validator Validator class name
    * @return void
    */
    function addValidator($element, $validator) {
        $this->validators[$element][] = $validator;
        // We don't want to set 'required' to false here because of the case where we have multiple validators
        if($validator->required) {
            $this->elements[$element]['required'] = true;
        }
    }

    /**
    * Set form ID
    * @param string $id
    */
    function setID($id) {
        $this->id = $id;
    }

    /**
    * Set form name
    * If default form ID is set, override the ID with the name also
    * @param string $name
    */
    function setName($name) {
        $this->name = $name;
        if(preg_match('/^form\-.+/',$this->id)) {
            $this->setID($name);
        }
    }

    function setAttributes($attributes) {
        $this->attributes = $attributes;
    }

    /**
    * Get the form open HTML tag
    * @return string Form HTML
    */
    function getFormOpenHTML($attributes = array()) {
        $default_attributes = array(
            'class'=>'form-horizontal',
            'action'=>$this->action,
            'id'=>$this->id,
            'name'=>$this->name,
            'method'=>strtolower($this->method)
        );
        if($this->enctype != '') {
            $default_attributes['enctype'] = $this->enctype;
        }
        if(!is_null($this->target)) {
            $default_attributes['target'] = $this->target;
        }
        $attributes = array_merge($default_attributes,$this->attributes,$attributes);
        $attributes_string = '';
        foreach($attributes AS $key=>$value) {
            $attributes_string .= ' '.$key.'="'.$value.'"';
        }
        $form_open_template = $this->getFormTemplate('open');
        $form_open_template->set('attributes',$attributes_string);
        return $form_open_template->render();
    }

    /**
    * Get the form closing HTML tag
    * @return string Form HTML
    */
    function getFormCloseHTML() {
        return '
        <script type="text/javascript">
        $(window).unload(function() {
            $("#'.$this->id.'").attr("submitted","false");
        });
        $(document).ready(function() {
            $("#'.$this->id.'").submit(function(event) {
                if($("#'.$this->id.'").attr("submitted") == "true") {
                    event.preventDefault();
                    return false;
                } else {
                    $("#'.$this->id.'").attr("submitted","true");
                }
            });
        });
        </script>
        <input type="hidden" name="'.COOKIE_PREFIX.'from" value="'.$this->clean_output((isset($_COOKIE[COOKIE_PREFIX.'from']) ? $_COOKIE[COOKIE_PREFIX.'from'] : constant(COOKIE_PREFIX.'from'))).'" />
        <input type="hidden" name="bot_check" value="" />
        </form>';
    }

    /**
    * Get field picker
    * @param string $name Field name
    * @return string Picker HTML
    */
    function getFieldPicker($name) {
        return $this->pickers[$name];
    }

    /**
    * Parse field attributes to build element
    * @param string $element Field name
    * @param array $exclude Exclude attributes from being included
    * @return string
    */
    function getAttributesString($element,$exclude=array()) {
        return HTML::attributesString('input',$this->elements[$element],$exclude);
    }

    /**
    * Parse field attributes to build element
    * @param string $element Field name
    * @param array $exclude Exclude attributes from being included
    * @return string
    */
    function getFieldsetAttributesString($fieldset_name,$exclude=array()) {
        $fieldset = $this->fieldsets[$fieldset_name];
        $loop_attributes = array_intersect(array_keys($fieldset),array('name','class','style','form'));
        $loop_attributes = array_diff($loop_attributes,$exclude);
        $attributes_string = '';
        foreach($loop_attributes as $a) {
            $attributes_string .= ' '.$a.'="'.$element[$a].'"';
        }
        if(isset($element['class'])) {
            if(!is_array($element['class'])) {
                $element['class'] = array_filter(array($element['class']));
            }
            if(count($element['class'])) {
                $attributes_string .= ' class="'.implode(' ',array_unique((array) $element['class'])).'"';
            }
        }
        return $attributes_string;
    }

    /**
    * Parse wrapper attributes to build wrapper
    * @param string $element Field name
    * @return string
    */
    function getWrapperAttributesString($element) {
        $wrapper_attributes = $this->elements[$element]['wrapper_attributes'];
        $loop_attributes = array_intersect(array_keys($wrapper_attributes),array('style','onclick','onmouseover','onmouseout'));
        foreach($loop_attributes as $a) {
            $attributes_string .= ' '.$a.'="'.$wrapper_attributes[$a].'"';
        }
        if(isset($wrapper_attributes['class']) AND count($wrapper_attributes['class'])) {
            $attributes_string .= ' class="'.implode(' ',array_unique((array) $wrapper_attributes['class'])).'"';
        }
        return $attributes_string;
    }

    function getFieldGroup($element, $attributes = array()) {
        if(!isset($this->elements[$element])) {
            return false;
        }
        $this->mergeFieldAttributes($element,$attributes);
        $field_template = $this->getFormTemplate('field');
        if($this->elements[$element]['type'] != 'hidden') {
            $field_template->set('id',$element);
            if(isset($this->elements[$element]['wrapper_attributes'])) {
                $field_template->set('wrapper_attributes',$this->getWrapperAttributesString($element));
            }
            $field_template->set('type','type-'.$this->elements[$element]['type']);
            $field_template->set('label',$this->getFieldLabel($element));
            $field_template->set('field',$this->getFieldHTML($element));
            if(array_key_exists($element,$this->pickers)) {
                $field_template->set('picker',$this->pickers[$element]);
            }
            if(isset($this->elements[$element]['counter'])) {
                $field_template->set('counter',$this->getFieldCounterHTML($element));
            }
            if($this->hasFieldNote($element)) {
                $field_template->set('notes',$this->getFieldNote($element));
            }
            if(value($this->elements[$element],'error') == true) {
                $field_template->set('error',true);
            }
        } else {
            $field_template->set('hidden',true);
            $field_template->set('field',$this->getFieldHTML($element));
        }
        return $field_template->render();
    }

    function getFormActions() {
        $template = $this->getFormTemplate('actions');
        if(count((array) $this->fieldsets['submit']['elements'])) {
            $actions = array();
            foreach((array) $this->fieldsets['submit']['elements'] as $button) {
                $actions[] = $this->getFieldHTML($button);
            }
            $template->set('actions',$actions);
            return $template->render();
        } else {
            return false;
        }
    }

    function mergeFieldAttributes($name, $new) {
        if(!count($new)) {
            return false;
        }
        if(isset($new['class']) AND !is_array($new['class'])) {
            $new['class'] = array($new['class']);
        }
        $this->elements[$name] = array_merge($this->elements[$name],$new);
    }

    /**
    * Get element HTML
    * Process the element adding all necesarry parts and return HTML
    * @param string $name Field name
    * @return string Element HTML
    */
    function getFieldHTML($name,$attributes = array()) {
        $this->mergeFieldAttributes($name,$attributes);
        $element = $this->elements[$name];

        $this->processDependency($name);

        if(isset($element['value'])) {
            $element['value'] = $this->clean_output($element['value']);
        } else {
            $element['value'] = '';
        }

        if(isset($element['implode']) AND $element['implode'] AND !is_array($element['value'])) {
            $element['value'] = explode((isset($element['implode_character']) ? $element['implode_character'] : "\n"),$element['value']);
        }

        switch($element['type']) {
            case 'text':
                if($template_text = $this->getFormTemplate('text')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
           case 'text_group':
                if($template_text = $this->getFormTemplate('text_group')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    if(isset($element['prepend'])) {
                        $template_text->set('prepend',$element['prepend']);
                    } elseif(isset($element['append'])) {
                        $template_text->set('append',$element['append']);
                    }
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'text_tags':
                if($template_text = $this->getFormTemplate('text_tags')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'text_unlimited':
                if($template_text = $this->getFormTemplate('text_unlimited')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'text_select':
                if($template_text = $this->getFormTemplate('text_select')) {
                    if(empty($element['value'])) {
                        $element['value'] = array();
                    }
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('name',$element['name']);
                    $template_text->set('id',$element['id']);
                    $template_text->set('limit',$element['limit']);
                    $template_text->set('counter',max(array(count($element['value']),1)));
                    $template_text->set('options',json_encode(array_values($element['options'])));
                    $template_text->set('first_value',array_shift($element['value']));
                    $template_text->set('values',array_filter($element['value']));
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'text_autocomplete':
                if($template_text = $this->getFormTemplate('text_autocomplete')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    $template_text->set('id',$element['id']);
                    $template_text->set('data',$element['data']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'url':
                if($template_text = $this->getFormTemplate('url')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class','name')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'url_title':
                if($template_text = $this->getFormTemplate('url_title')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class','name')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $url_parts = explode('|',$element['value']);
                    if(count($url_parts) == 2 AND valid_url($url_parts[1])) {
                        $template_text->set('url_title',$url_parts[0]);
                        $template_text->set('url',$url_parts[1]);
                    } else {
                        $template_text->set('url_title','');
                        $template_text->set('url','');
                    }
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'url_title_multiple':
                if($template_text = $this->getFormTemplate('url_title_multiple')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class','name')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $url_lines = explode("\n",$element['value']);
                    $values = array();
                    foreach($url_lines AS $line) {
                        $line_parts = explode('|',$line);
                        if(count($line_parts) == 2 AND valid_url($line_parts[1])) {
                            $values[] = array('url_title'=>$line_parts[0],'url'=>$line_parts[1]);
                        }
                    }
                    $first_value = array_shift($values);
                    $template_text->set('first_value',$first_value);
                    $template_text->set('values',$values);
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'currency':
                if($template_currency = $this->getFormTemplate('currency')) {
                    $template_currency->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_currency->set('class',implode(' ',$element['class']));
                    $template_currency->set('value',format_number($element['value']));
                    $template_currency->set('name',$element['name']);
                    return $template_currency->render();
                } else {
                    return '';
                }
                break;
            case 'date':
                if($template_date = $this->getFormTemplate('date')) {
                    $template_date->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_date->set('class',implode(' ',$element['class']));
                    $template_date->set('value',$this->PMDR->get('Dates_Local')->formatDateOutput($element['value']));
                    $template_date->set('name',$element['name']);
                    return $template_date->render();
                } else {
                    return '';
                }
                break;
            case 'datetime':
                if($template_datetime = $this->getFormTemplate('datetime')) {
                    $template_datetime->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_datetime->set('class',implode(' ',$element['class']));
                    $template_datetime->set('value',$this->PMDR->get('Dates_Local')->formatDateTimeOutput($element['value']));
                    $template_datetime->set('name',$element['name']);
                    return $template_datetime->render();
                } else {
                    return '';
                }
                break;
            case 'password':
                if($template_password = $this->getFormTemplate('password')) {
                    $template_password->set('plaintext',value($element,'plaintext'));
                    $template_password->set('generate',value($element,'generate'));
                    $template_password->set('strength',value($element,'strength'));
                    $template_password->set('strength_label',value($element,'strength_label'));
                    $template_password->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_password->set('class',implode(' ',$element['class']));
                    $template_password->set('value',$element['value']);
                    $template_password->set('name',$element['name']);
                    return $template_password->render();
                } else {
                    return '';
                }
                break;
            case 'textarea':
                // Textarea tags/value needs to be on a new line due to a quirk where new line gets removed from the first line
                if($template_textarea = $this->getFormTemplate('textarea')) {
                    $template_textarea->set('id',$element['id']);
                    $template_textarea->set('value',$element['value']);
                    $template_textarea->set('label',$element['label']);
                    $template_textarea->set('fullscreen',value($element,'fullscreen'));
                    $template_textarea->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_textarea->set('spellcheck',value($element,'spellcheck'));
                    $template_textarea->set('class',implode(' ',$element['class']));
                    $template_textarea->set('value',$element['value']);
                    return $template_textarea->render();
                } else {
                    return '';
                }
                break;
            case 'select':
                if($template_select = $this->getFormTemplate('select')) {
                    if(is_array($element['value'])) {
                        $element['value'] = $element['value'][0];
                    }
                    if(isset($element['first_option']) AND !is_array($element['first_option'])) {
                        $element['first_option'] = array(''=>$element['first_option']);
                    }
                    $template_select->set('first_options',value($element,'first_option'));
                    $template_select->set('id',$element['id']);
                    $template_select->set('value',$this->PMDR->get('Cleaner')->unclean_html($element['value']));
                    $template_select->set('label',$element['label']);
                    $template_select->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_select->set('class',implode(' ',$element['class']));
                    $template_select->set('options',value($element,'options',array()));
                    return $template_select->render();
                } else {
                    return '';
                }
                break;
            case 'select_multiple':
                if($template_select = $this->getFormTemplate('select_multiple')) {
                    if(!is_array($element['value'])) {
                        if(empty($element['value'])) {
                            $element['value'] = array();
                        } else {
                            $element['value'] = array($element['value']);
                        }
                    }
                    $template_select->set('id',$element['id']);
                    $template_select->set('name',$name);
                    $template_select->set('value',$this->PMDR->get('Cleaner')->unclean_html($element['value']));
                    $template_select->set('label',$element['label']);
                    $template_select->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_select->set('class',implode(' ',$element['class']));
                    $template_select->set('options',$element['options']);
                    return $template_select->render();
                } else {
                    return '';
                }
                break;
            case 'tree_select':
            case 'tree_select_group':
                if($template_select = $this->getFormTemplate('select_tree')) {
                    if(is_array($element['value'])) {
                        $element['value'] = $element['value'][0];
                    }
                    if(isset($element['first_option']) AND !is_array($element['first_option'])) {
                        $element['first_option'] = array(''=>$element['first_option']);
                    }
                    $option_html = '';
                    $closing_tags = array();
                    foreach($element['options'] as $value=>$option) {
                        $value = $this->clean_output($value);
                        foreach($closing_tags as $level=>$tag) {
                            if($level >= $option['level']) {
                                $option_html .= array_pop($closing_tags);
                            }
                        }

                        if((($option['left_']+1) == $option['right_']) OR $element['type'] != 'tree_select_group') {
                            $option_html .= '<option value="'.$value.'"';
                            if($value == $element['value']) {
                                $option_html .= ' selected="selected"';
                            }
                            $option_html .= '>';
                            for ($x = 1; $x < $option['level']; $x++) {
                                $option_html .= '&nbsp;&nbsp;&nbsp;';
                            }
                            $option_html .= $option['title'].'</option>';
                        } else {
                            $option_html .= '<optgroup label="';
                            for ($x = 1; $x < $option['level']; $x++) {
                                $option_html .= '&nbsp;&nbsp;&nbsp;';
                            }
                            $option_html .= $option['title'].'">';
                            $closing_tags[$option['level']] = '</optgroup>';
                        }
                    }
                    foreach($closing_tags as $tag) {
                        $option_html .= $tag;
                    }
                    $template_select->set('first_options',$element['first_option']);
                    $template_select->set('id',$element['id']);
                    $template_select->set('value',$this->PMDR->get('Cleaner')->unclean_html($element['value']));
                    $template_select->set('label',$element['label']);
                    $template_select->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_select->set('class',implode(' ',$element['class']));
                    $template_select->set('options',$option_html);
                    $template_select->set('name',$name);
                    if(isset($element['limit'])) {
                        $template_select->set('limit',intval($element['limit']));
                        $template_select->set('limit_over',$this->PMDR->getLanguage('limit_over',array($element['limit'])));
                    }
                    return $template_select->render();
                } else {
                    return '';
                }
                break;
            case 'tree_select_multiple':
            case 'tree_select_multiple_group':
                if($template_select = $this->getFormTemplate('select_multiple_tree')) {
                    $option_html = '';
                    $closing_tags = array();
                    foreach($element['options'] as $value=>$option) {
                        $value = $this->clean_output($value);
                        foreach($closing_tags as $level=>$tag) {
                            if($level >= $option['level']) {
                                $option_html .= array_pop($closing_tags);
                            }
                        }
                        if((($option['left_']+1) == $option['right_']) OR $element['type'] != 'tree_select_multiple_group') {
                            $option_html .= '<option value="'.$value.'"';
                            foreach((array) $element['value'] as $element_value) {
                                if($value == $element_value) {
                                    $option_html .= ' selected="selected"';
                                }
                            }
                            $option_html .= '>';
                            for ($x = 1; $x < $option['level']; $x++) {
                                $option_html .= '&nbsp;&nbsp;&nbsp;';
                            }
                            $option_html .= $option['title'].'</option>';
                        } else {
                            $option_html .= '<optgroup label="';
                            for ($x = 1; $x < $option['level']; $x++) {
                                $option_html .= '&nbsp;&nbsp;&nbsp;';
                            }
                            $option_html .= $option['title'].'">';
                            $closing_tags[$option['level']] = '</optgroup>';
                        }
                    }
                    foreach($closing_tags as $tag) {
                        $option_html .= $tag;
                    }
                    $template_select->set('id',$element['id']);
                    $template_select->set('value',$this->PMDR->get('Cleaner')->unclean_html($element['value']));
                    $template_select->set('label',$element['label']);
                    $template_select->set('attributes',$this->getAttributesString($name,array('class','name')));
                    $template_select->set('class',implode(' ',$element['class']));
                    $template_select->set('options',$option_html);
                    $template_select->set('name',$name);
                    if(isset($element['limit'])) {
                        $template_select->set('limit',intval($element['limit']));
                        $template_select->set('limit_over',$this->PMDR->getLanguage('limit_over',array($element['limit'])));
                    }
                    return $template_select->render();
                } else {
                    return '';
                }
                break;
            case 'tree_select_expanding':
            case 'tree_select_expanding_radio':
            case 'tree_select_expanding_checkbox':
                if(!is_array($element['value'])) {
                    $element['value'] = array($element['value']);
                }
                if(!isset($element['checkall'])) {
                    $element['checkall'] = true;
                }
                if($template_tree_expanding = $this->getFormTemplate('select_tree_expanding')) {
                    $template_tree_expanding->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_tree_expanding->set('class',implode(' ',$element['class']));
                    $template_tree_expanding->set('value',implode(',',$element['value']));
                    if($element['type'] == 'tree_select_expanding_checkbox' AND $element['limit']) {
                        $template_tree_expanding->set('limit',$element['limit']);
                    }
                    if($element['type'] == 'tree_select_expanding_checkbox' AND $element['checkall']) {
                        $template_tree_expanding->set('checkall',$element['checkall']);
                    }
                    $template_tree_expanding->set('name',$element['name']);
                    $template_tree_expanding->set('label',$element['label']);
                    $template_tree_expanding->set('type',$element['options']['type']);
                    $template_tree_expanding->set('search',(isset($element['options']['search']) AND $element['options']['search']));
                    $javascript = '
                    <script type="text/javascript">
                    $(document).ready(function(){
                        $("#'.$name.'_tree_checkall, #'.$name.'_tree_uncheckall").click(function(e) {
                            e.preventDefault();
                            $("#'.$name.'_tree_div").dynatree("option","initAjax", {
                                type: "post",
                                url: "'.PMDROOT_RELATIVE.'/includes/data.php?'.http_build_query($element['options']).'",
                                data: {
                                    checkall: $(this).attr("id") == "'.$name.'_tree_checkall"
                                }
                            });
                            $("#'.$name.'_tree_div").dynatree("getTree").reload(function() { this.options.onSelect(); });
                        });
                        $("#'.$name.'_tree_div").dynatree({
                            title: "'.$name.'_tree",
                            minExpandLevel: 1,
                            debugLevel: '.(DEBUG_MODE ? '2' : '0').',
                            keyboard: true,
                            persist: false,
                            imagePath: "'.BASE_URL.'/includes/jquery/dynatree/skin/",
                            autoCollapse: false,
                            activeVisible: true,
                            autoFocus: false,
                            initAjax: {
                                type: "post",
                                url: "'.PMDROOT_RELATIVE.'/includes/data.php?'.http_build_query($element['options']).'",
                                data: {
                                    value: "'.implode(',',$element['value']).'"
                                }
                            },
                            onLazyRead: function(node) {
                                node.appendAjax({
                                    type: "post",
                                    url: "'.PMDROOT_RELATIVE.'/includes/data.php?'.http_build_query($element['options']).'",
                                    data: {
                                        id: node.data.key
                                    }
                                })
                            },
                            ajaxDefaults: {
                                cache: false,
                                timeout: 0,
                                dataType: "json"
                            },
                            strings: {
                                loading: "Loading…",
                                loadError: "Load error!"
                            },
                            fx: { height: "toggle", duration: 200 },
                            cookieId: "'.$name.'",
                            cookie: {
                                expires: null // Days or Date; null: session cookie
                            },
                            generateIds: false,
                            idPrefix: "'.$name.'-",
                            keyPathSeparator: ",",
                            noLink: false,
                        ';
                        if($element['type'] != 'tree_select_expanding') {
                            if($element['type'] == 'tree_select_expanding_checkbox') {
                                $javascript .= 'checkbox: true,';
                                if(!isset($element['options']['select_mode'])) {
                                    $javascript .= 'selectMode: 2,';
                                } else {
                                    $javascript .= 'selectMode: '.$element['options']['select_mode'].',';
                                }
                            } else {
                                $javascript .= 'classNames: {checkbox: "dynatree-radio"},';
                                $javascript .= 'checkbox: true,';
                                $javascript .= 'selectMode: 1,';
                            }
                        }
                        $javascript .= '
                        onClick: function(node,event) {';
                            if($element['type'] != 'tree_select_expanding') {
                                $javascript .= '
                                if(node.getEventTargetType(event) == "title") {
                                    node.toggleSelect();
                                }';
                            }
                        $javascript .= '
                        },';
                        if(isset($element['limit'])) {
                            $javascript .= '
                            onQuerySelect: function(flag, node) {
                                if(flag && $("#'.$name.'_tree_div").dynatree("getTree").getSelectedNodes().length+1 > '.(intval($element['limit'])).') {
                                    $("#'.$name.'_tree_check_limit").addClass("text-error");
                                    alert("'.$this->PMDR->getLanguage('limit_over',array($element['limit'])).'");
                                    return false;
                                } else {
                                    $("#'.$name.'_tree_check_limit").removeClass("text-error");
                                }
                            },';
                        }
                        $javascript .= '
                        onSelect: function(flag, node) {
                            $("#'.$name.'").val(
                                $.map($("#'.$name.'_tree_div").dynatree("getTree").serializeArray(), function(value, index) {
                                    return value.value;
                                }).join(",")
                            );
                            $("#'.$name.'_tree_check_limit").text($("#'.$name.'_tree_div").dynatree("getTree").getSelectedNodes().length);
                            '.$element['onselect'].'
                        },
                        onPostInit: function(isReloading, isError) {
                            if($("#'.$name.'_tree_div").dynatree("getTree").count() == 1) {
                                $("#'.$name.'_tree_div").html("-");
                                $("#'.$name.'_tree_check_links").hide();
                            }
                            $("#'.$name.'_tree_check_limit").text($("#'.$name.'_tree_div").dynatree("getTree").getSelectedNodes().length);
                        }
                        })';
                        if($element['options']['search']) {
                        $javascript .= '
                        $("#'.$name.'_search").keyup(function () {
                            var '.$name.'_search_length = $("#'.$name.'_search").val().length;
                            if('.$name.'_search_length > 2 || ('.$name.'_search_length > 0 && !isNaN($("#'.$name.'_search").val()))) {
                                $.ajax({
                                    type: "get",
                                    url: "'.PMDROOT_RELATIVE.'/includes/data.php",
                                    data: ({
                                        type: "admin_'.$element['options']['type'].'_search",
                                        value: $("#'.$name.'_search").val(),
                                        name: "'.$name.'",
                                        template_path: "'.$this->getFormTemplatePath().'"
                                    }),
                                    success: function(data) {
                                        if(data.length) {
                                            $("#'.$name.'_search").qtip("option","content.text",data);
                                            $("#'.$name.'_search").qtip("toggle",true);
                                        } else {
                                            $("#'.$name.'_search").qtip("toggle",false);
                                        }
                                    }
                                });
                            } else {
                                $("#'.$name.'_search").qtip("toggle",false);
                            }
                        });
                        // If we focus back in the search form, trigger key up so the results show again
                        $("#'.$name.'_search").focus(function() {
                            $("#'.$name.'_search").trigger("keyup");
                        });
                        $("#'.$name.'_search").qtip({
                            id: "'.$name.'_search_results",
                            show: { delay: 0, event: false },
                            hide: {
                                fixed: true,
                                delay: 100,
                                event: "blur"
                            },
                            style: {
                                tip: { corner: "top left", size: { x: 20, y: 8 } }
                            },
                            position: { at: "bottom left", my: "top left", adjust: { x: 5 }},
                            content: " ",
                            events: {
                                render: function(event, api) {
                                    $("#qtip-'.$name.'_search_results").on("click","a.'.$name.'_search_link", function(e){
                                         e.preventDefault();
                                         $("#'.$name.'_tree_div").dynatree("getTree").loadKeyPath($(this).data("id-path"), function(node, status){
                                            if(status == "loaded") {
                                                node.expand();
                                            } else if(status == "ok") {
                                                node.makeVisible();
                                                node.select(1);
                                                $("#'.$name.'_search").val("");
                                            }
                                         });
                                    });
                                }
                            }
                        });';
                        }
                        $javascript .= '
                    });
                    </script>
                    ';
                    $template_tree_expanding->set('javascript',$javascript);
                    return $template_tree_expanding->render();
                } else {
                    return '';
                }
                break;
            case 'tree_select_cascading':
                $element['options']['data_type'] = $element['options']['type'];
                unset($element['options']['type']);
                // This could be an array because we load listing categories in this manner.  Make sure it is a string.
                if(is_array($element['value'])) {
                    $element['value'] = $element['value'][0];
                }
                if(!is_string($element['value'])) {
                    $element['value'] = '';
                }

                if($template_tree_select_cascading = $this->getFormTemplate('select_cascading_wrapper')) {
                    $template_tree_select_cascading->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_tree_select_cascading->set('class',implode(' ',$element['class']));
                    $template_tree_select_cascading->set('value',$element['value']);
                    $template_tree_select_cascading->set('name',$element['name']);
                    $template_tree_select_cascading->set('label',$element['label']);
                    $template_tree_select_cascading->set('type',$element['options']['data_type']);
                    $template_tree_select_cascading->set('search',(isset($element['options']['search']) AND $element['options']['search']));

                    $javascript = '
                    <script type="text/javascript">
                    function '.$name.'_refresh(value) {
                        $.ajax({
                            type: "get",
                            url: "'.PMDROOT_RELATIVE.'/includes/data.php?'.http_build_query($element['options']).'",
                            data: ({
                                type: "tree_select_cascading",
                                name: "'.$name.'",
                                value: value,
                                template_path: "'.$this->getFormTemplatePath().'"
                            }),
                            success: function(data) {
                                $("#'.$name.'_container").html(data);
                            }
                        });
                    }
                    $(document).ready(function() {
                        $("#'.$name.'_container").on("change", "select", function(event) {
                            if($(this).val() == "") {
                                '.$name.'_refresh($(this).prevAll("select").last().val());
                            } else {
                                '.$name.'_refresh($(this).val());
                            }
                        });
                        '.$name.'_refresh($("#'.$element['id'].'").val());
                    ';
                    if($element['options']['search']) {
                        $javascript .= '
                        $("#'.$name.'_search").keyup(function () {
                            var '.$name.'_search_length = $("#'.$name.'_search").val().length;
                            if('.$name.'_search_length > 2 || ('.$name.'_search_length > 0 && !isNaN($("#'.$name.'_search").val()))) {
                                $.ajax({
                                    type: "get",
                                    url: "'.PMDROOT_RELATIVE.'/includes/data.php",
                                    data: ({
                                        type: "admin_'.$element['options']['data_type'].'_search",
                                        value: $("#'.$name.'_search").val(),
                                        name: "'.$name.'",
                                        template_path: "'.$this->getFormTemplatePath().'"
                                    }),
                                    success: function(data) {
                                        if(data.length) {
                                            $("#'.$name.'_search").qtip("option","content.text",data);
                                            $("#'.$name.'_search").qtip("toggle",true);
                                        } else {
                                            $("#'.$name.'_search").qtip("toggle",false);
                                        }
                                    }
                                });
                            } else {
                                $("#'.$name.'_search").qtip("toggle",false);
                            }
                        });
                        // If we focus back in the search form, trigger key up so the results show again
                        $("#'.$name.'_search").focus(function() {
                            $("#'.$name.'_search").trigger("keyup");
                        });
                        $("#'.$name.'_search").qtip({
                            id: "'.$name.'_search_results",
                            show: { delay: 0, event: false },
                            hide: {
                                fixed: true,
                                delay: 100,
                                event: "blur"
                            },
                            style: {
                                tip: { corner: "top left", size: { x: 20, y: 8 } }
                            },
                            position: { at: "bottom left", my: "top left", adjust: { x: 5 }},
                            content: " ",
                            events: {
                                render: function(event, api) {
                                    $("#qtip-'.$name.'_search_results").on("click","a.'.$name.'_search_link", function(e){
                                         e.preventDefault();
                                         '.$name.'_refresh($(this).data("id"));
                                         $("#'.$name.'_search").val("");
                                    });
                                }
                            }
                        });';
                    }
                    $javascript .= '});</script>';
                    $template_tree_select_cascading->set('javascript',$javascript);
                    return $template_tree_select_cascading->render();
                } else {
                    return '';
                }
                break;
            case 'tree_select_cascading_multiple':
                $element['options']['data_type'] = $element['options']['type'];
                unset($element['options']['type']);
                if(!is_array($element['value'])) {
                    $element['value'] = array($element['value']);
                }

                if($template_tree_select_cascading = $this->getFormTemplate('select_cascading_wrapper')) {
                    $template_tree_select_cascading->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_tree_select_cascading->set('class',implode(' ',$element['class']));
                    $template_tree_select_cascading->set('value',$element['value']);
                    $template_tree_select_cascading->set('name',$element['name']);
                    $template_tree_select_cascading->set('label',$element['label']);
                    $template_tree_select_cascading->set('type',$element['options']['data_type']);
                    $template_tree_select_cascading->set('search',(isset($element['options']['search']) AND $element['options']['search']));

                    $javascript = '
                    <script type="text/javascript">
                    function '.$name.'_refresh(selected) {
                        $.ajax({
                            type: "get",
                            url: "'.PMDROOT_RELATIVE.'/includes/data.php?'.http_build_query($element['options']).'",
                            data: ({
                                type: "tree_select_cascading_multiple",
                                name: "'.$name.'",
                                select_value: selected,
                                value: $("input[id=\''.$element['id'].'\']").map(function(){return $(this).val();}).get().join(","),
                                template_path: "'.$this->getFormTemplatePath().'"
                            }),
                            success: function(data) {
                                $("#'.$name.'_container").html(data);
                            }
                        });
                    }
                    $(document).ready(function() {
                        $("#'.$name.'_container").on("click", ".'.$name.'_remove_link", function(event) {
                            event.preventDefault();
                            $(this).parent().remove();
                            '.$name.'_refresh();
                        });
                        $("#'.$name.'_container").on("click", "#'.$name.'_add", function(event) {
                            event.preventDefault();
                            $("#'.$name.'_container").append("<input type=\"hidden\" id=\"'.$name.'\" name=\"'.$name.'[]\" value=\""+$("#'.$name.'_container select[value!=\'\']").last().val()+"\">");
                            '.$name.'_refresh();
                        });
                        $("#'.$name.'_container").on("change", "select", function(event) {
                            event.preventDefault();
                            if($(this).val() == "") {
                                '.$name.'_refresh($(this).prevAll("#'.$name.'_container select[value!=\'\']").last().val());
                            } else {
                                '.$name.'_refresh($(this).val());
                            }
                        });
                        '.$name.'_refresh();';
                    if($element['options']['search']) {
                        $javascript .= '
                        $("#'.$name.'_search").keyup(function () {
                            var '.$name.'_search_length = $("#'.$name.'_search").val().length;
                            if('.$name.'_search_length > 2 || ('.$name.'_search_length > 0 && !isNaN($("#'.$name.'_search").val()))) {
                                $.ajax({
                                    type: "get",
                                    url: "'.PMDROOT_RELATIVE.'/includes/data.php",
                                    data: ({
                                        type: "admin_'.$element['options']['data_type'].'_search",
                                        value: $("#'.$name.'_search").val(),
                                        name: "'.$name.'",
                                        template_path: "'.$this->getFormTemplatePath().'"
                                    }),
                                    success: function(data) {
                                        if(data.length) {
                                            $("#'.$name.'_search").qtip("option","content.text",data);
                                            $("#'.$name.'_search").qtip("toggle",true);
                                        } else {
                                            $("#'.$name.'_search").qtip("toggle",false);
                                        }
                                    }
                                });
                            } else {
                                $("#'.$name.'_search").qtip("toggle",false);
                            }
                        });
                        // If we focus back in the search form, trigger key up so the results show again
                        $("#'.$name.'_search").focus(function() {
                            $("#'.$name.'_search").trigger("keyup");
                        });
                        $("#'.$name.'_search").qtip({
                            id: "'.$name.'_search_results",
                            show: { delay: 0, event: false },
                            hide: {
                                fixed: true,
                                delay: 100,
                                event: "blur"
                            },
                            style: {
                                tip: { corner: "top left", size: { x: 20, y: 8 } }
                            },
                            position: { at: "bottom left", my: "top left", adjust: { x: 5 }},
                            content: " ",
                            events: {
                                render: function(event, api) {
                                    $("#qtip-'.$name.'_search_results").on("click","a.'.$name.'_search_link", function(e){
                                         e.preventDefault();
                                         '.$name.'_refresh($(this).data("id"));
                                         $("#'.$name.'_search").val("");
                                    });
                                }
                            }
                        });';
                    }
                    $javascript .= '});</script>';
                    $template_tree_select_cascading->set('javascript',$javascript);
                    return $template_tree_select_cascading->render();
                } else {
                    return '';
                }
                break;
            case 'checkbox':
                if($template = $this->getFormTemplate('checkbox_wrapper')) {
                    $template->set('id',$element['id']);
                    $template->set('columns',value($element,'columns'));
                    $template->set('html',null);

                    $checkboxes = array();

                    // Remove empty values, we do it here so it does not change for multiple select/dropdowns
                    if(is_array($element['value'])) {
                        $element['value'] = array_filter((array) $element['value'],'strlen');
                    }
                    // If options are not set or if we have a options array with no valid values.
                    if(!isset($element['options']) OR !count(array_filter($element['options'],'strlen'))) {
                        if($template_checkbox = $this->getFormTemplate('checkbox')) {
                            if(is_array($element['value'])) {
                                $element['value'] = $element['value'][0];
                            }
                            $template_checkbox->set('html',null);
                            if(!empty($element['value'])) {
                                $this->elements[$name]['checked'] = 'checked';
                            } else {
                                $element['value'] = 1;
                            }
                            $html = '<input type="checkbox"'.$this->getAttributesString($name).' value="'.$this->clean_output($element['value']).'" /> ';
                            if(!empty($element['html'])) {
                                $template_checkbox->set('option',$element['html']);
                            }
                            $template_checkbox->set('field',$html);
                            $checkboxes[] = $template_checkbox->render();
                        }
                        unset($template_checkbox);
                    } else {
                        $count = 0;
                        $new_column_indexes = array();
                        foreach($element['options'] as $value=>$option) {
                            if($template_checkbox = $this->getFormTemplate('checkbox')) {
                                $value = $this->clean_output($value);
                                if(isset($element['columns'])) {
                                    $per_column = ceil(count($element['options']) / $element['columns']);
                                    if(($count % $per_column) == 0 AND $count != 0) {
                                        $new_column_indexes[] = $count-1;
                                    }
                                }

                                $html = '<input type="checkbox" id="'.$name.'_'.$count.'"'.str_replace('name="'.$name.'"','name="'.$name.'[]"',$this->getAttributesString($name,array('id'))).' value="'.$value.'"';
                                foreach((array) $element['value'] as $element_value) {
                                    if(trim($value) == trim($element_value)) {
                                        $html .= ' checked="checked"';
                                    }
                                }
                                $html .= ' />';
                                $template_checkbox->set('field',$html);
                                if(is_array($option)) {
                                    foreach($option AS $option_key=>$option_value) {
                                        $template_checkbox->set($option_key,$option_value);
                                    }
                                } else {
                                    $template_checkbox->set('option',$option);
                                }
                                if(isset($element['html']) AND is_array($element['html'])) {
                                    $template_checkbox->set('html',array_shift($element['html']));
                                } else {
                                    $template_checkbox->set('html',null);
                                }

                                $checkboxes[] = $template_checkbox->render();
                                $count++;
                            }
                            unset($template_checkbox);
                        }
                        $template->set('new_column_indexes',$new_column_indexes);
                        $template->set('checkall',isset($element['checkall']));
                    }
                    $template->set('fields',$checkboxes);
                    return $template->render();
                } else {
                    return '';
                }
                break;
            case 'radio':
                if($template = $this->getFormTemplate('radio_wrapper')) {
                    $template->set('id',$element['id']);
                    $radios = array();
                    $id_index = 0;
                    foreach($element['options'] as $value=>$option) {
                        if($template_radio = $this->getFormTemplate('radio')) {
                            // Unclean HTML is used in case of characters like ' or " since we filter all incoming values
                            $value = $this->clean_output($value);
                            $html = '<input type="radio" id="'.$name.'_'.$id_index.'"'.$this->getAttributesString($name,array('id')).' value="'.$value.'"';
                            if($value == $element['value']) {
                                $html .= ' checked="checked"';
                            }
                            $html .= ' />';
                            $template_radio->set('inline',value($element,'inline'));
                            $template_radio->set('field',$html);
                            $template_radio->set('option',$option);
                            $radios[] = $template_radio->render();
                            $id_index++;
                        }
                        unset($template_radio);
                    }
                    $template->set('html',value($element,'html',''));
                    $template->set('fields',$radios);
                    return $template->render();
                } else {
                    return '';
                }
                break;
            case 'htmleditor':
                if($template_text = $this->getFormTemplate('htmleditor')) {
                    if(isset($element['counter'])) {
                        $template_text->set('counter',$element['counter']);
                    }
                    if(isset($element['options'])) {
                        $template_text->set('options',$element['options']);
                    }
                    if(PMD_SECTION != 'admin') {
                        $allowed_tags = $this->PMDR->getConfig('allowed_html_tags');
                        if(strstr($allowed_tags,'*[style]')) {
                            $style = '{*}';
                        } else {
                            $style = '';
                        }
                        $tags_string = $this->PMDR->get('HTML')->tagsToString($this->PMDR->get('HTML')->tagsToArray($allowed_tags),$style.'; ',',');
                        $template_text->set('allowed_tags',$tags_string);
                        if($element['listing_id']) {
                            $template_text->set('browse',$element['browse']);
                            $template_text->set('listing_id',$element['listing_id']);
                        }
                    }
                    $template_text->set('fullpage',(value($element,'fullpage') == 'true'));
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'select_window':
                if($select_window_template = $this->getFormTemplate('select_window')) {
                    $html .= '
                    <script type="text/javascript">
                    $(document).ready(function() {
                        $("#'.$name.'_window_search").keyup(function () {
                            if($("#'.$name.'_window_search").val().length > 2) {
                                $.ajax({
                                    data: ({ action: "'.$element['options'].'", start: 0, num: 100, search: $("#'.$name.'_window_search").val() }),
                                    success: function(data) {
                                        $("#'.$name.'_window").dialog("option","width",700);
                                        $("#'.$name.'_window").dialog("option","height",450);
                                        $("#'.$name.'_window").dialog("option","position","center");
                                        $("#'.$name.'_window_content").html(data);
                                        $("#'.$name.'_window_content tbody tr").hover(function() {$(this).css("cursor","pointer")});
                                        $("#'.$name.'_window_content tbody tr").click(function() {
                                            $("#'.$name.'").attr("value",$("td:eq(0)", this).text());
                                            $("#'.$name.'_display").html($("td:eq(1)", this).text());
                                            $("#'.$name.'_window").dialog("close");
                                        });
                                    },
                                    dataType: "html"
                                })
                            }
                        });

                        $("#'.$name.'_window").dialog({
                            title: \''.$element['label'].'\',
                            autoOpen: false,
                            width: 240,
                            height: "auto",
                            resizable: true,
                            draggable: true,
                            zindex: 10000,
                            open: function() { }
                        });
                        $("#'.$name.'_window_link").click(function(e) {
                            e.preventDefault();
                            $("#'.$name.'_window").dialog("open");
                        });';
                        if($element['value'] != '') {
                            $html .= '
                            $.ajax({ data: ({ action: "'.$element['options'].'", id: "'.$element['value'].'"}),
                                success: function(data) { $("#'.$name.'_display").html(data); },
                                dataType: "html"
                            });';
                        }
                    $html .= '
                    });
                    </script>';
                    $select_window_template->set('id',$name);
                    $select_window_template->set('field','<input type="hidden"'.$this->getAttributesString($name).' value="'.$element['value'].'" />');
                    $select_window_template->set('label',$element['label']);
                    if(isset($element['icon'])) {
                        $select_window_template->set('icon',$this->PMDR->get('HTML')->icon($element['icon']));
                    }
                    $html .= $select_window_template->render();
                    unset($select_window_template);
                    return $html;
                } else {
                    return '';
                }
                break;
            case 'number_toggle':
                if($template_text = $this->getFormTemplate('number_toggle')) {
                    $template_text->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_text->set('class',implode(' ',$element['class']));
                    $template_text->set('value',$element['value']);
                    $template_text->set('name',$element['name']);
                    return $template_text->render();
                } else {
                    return '';
                }
                break;
            case 'stars':
                if($template_stars = $this->getFormTemplate('stars')) {
                    if(!isset($element['width'])) {
                        $element['width'] = 16;
                    }
                    if($element['value'] == '') {
                        $element['value'] = 0;
                    }
                    $image_width = $element['value']*$element['width'];
                    $template_stars->set('id',$element['id']);
                    $template_stars->set('name',$name);
                    $template_stars->set('label',$element['label']);
                    $template_stars->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_stars->set('class',implode(' ',$element['class']));
                    $template_stars->set('value',$element['value']);
                    $template_stars->set('width',$element['width']);
                    $template_stars->set('image_width',$image_width);
                    return $template_stars->render();
                } else {
                    return '';
                }
                break;
            case 'file':
                if(value($element,'multiple')) {
                    if($file_multiple_template = $this->getFormTemplate('file_multiple')) {
                        $html .= '
                        <script type="text/javascript">
                        $(document).ready(function() {
                            $("#'.$element['id'].'_add_input").click(function () {
                                $("#'.$element['id'].'_container").append("<br /><input type=\"file\" name=\"'.$name.'[]\">");
                        ';
                        if(isset($element['limit']) AND $element['limit'] > 0) {
                            $html .= '
                            if($("#'.$element['id'].'_container > input").length == '.$element['limit'].') {
                                $("#'.$element['id'].'_add_input").hide();
                            }';
                        }
                        $html .= '
                            return false;
                            });
                        });
                        </script>';
                        $file_multiple_template->set('id',$element['id']);
                        $file_multiple_template->set('field','<input name="'.$name.'[]" type="file"'.$this->getAttributesString($name,array('name','multiple')).' />');
                        $html .= $file_multiple_template->render();
                        unset($file_multiple_template);
                        return $html;
                    } else {
                        return '';
                    }
                } else {
                    if($file_template = $this->getFormTemplate('file')) {
                        $this->elements[$name]['class'][] = 'file';
                        $file_template->set('id',$element['id']);
                        $file_template->set('field','<input type="file"'.$this->getAttributesString($name).' />');
                        if(!empty($element['delete_url'])) {
                            $file_template->set('delete_url',$element['delete_url']);
                        }
                        if(!empty($element['url_image'])) {
                            $file_template->set('url_image',$element['url_image']);
                        }
                        if(isset($element['value'])) {
                            $file_template->set('value',$element['value']);
                        }
                        if(isset($element['options']['url_allow'])) {
                            $file_template->set('url_allow',$element['options']['url_allow']);
                        }
                        $html = $file_template->render();
                        unset($file_template);
                        return $html;
                    } else {
                        return '';
                    }
                }
                break;
            case 'security_image':
                if(!$captcha = $this->PMDR->get('Captcha')) {
                    trigger_error('Invalid Captcha');
                } else {
                    return $captcha->getHTML($this->elements[$name]);
                }
                break;
            case 'products_select':
                if($template_select = $this->getFormTemplate('select_product')) {
                    if(is_array($element['value'])) {
                        $element['value'] = $element['value'][0];
                    }
                    if(isset($element['first_option']) AND !is_array($element['first_option'])) {
                        $element['first_option'] = array(''=>$element['first_option']);
                    }
                    $template_select->set('first_options',$element['first_option']);
                    $template_select->set('id',$element['id']);
                    $template_select->set('value',$this->PMDR->get('Cleaner')->unclean_html($element['value']));
                    $template_select->set('label',$element['label']);
                    $template_select->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_select->set('class',implode(' ',$element['class']));
                    $template_select->set('html',$element['html']);

                    $options = $this->PMDR->get('DB')->GetAssoc("SELECT GROUP_CONCAT(pp.id SEPARATOR ',') AS pricing_ids, p.name FROM ".T_PRODUCTS." p INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id GROUP BY p.id ORDER BY p.ordering ASC");
                    $template_select->set('options',$options);
                    return $template_select->render();
                } else {
                    return '';
                }
                break;
            case 'products_pricing_select':
                $this->elements[$name]['class'][] = 'select';
                $html = '<select'.$this->getAttributesString($name).'>';
                if(isset($element['first_option'])) {
                    if(is_array($element['first_option'])) {
                        foreach($element['first_option'] as $value=>$option) {
                            $value = $this->clean_output($value);
                            $html .= '<option value="'.$value.'">'.$option.'</option>';
                        }
                    } else {
                        $html .= '<option value="">'.$element['first_option'].'</option>';
                    }
                }
                if(is_array($element['value'])) {
                    $element['value'] = $element['value'][0];
                }
                $products = $this->PMDR->get('DB')->GetAll("SELECT p.id, p.name, pp.id AS pricing_id FROM ".T_PRODUCTS." p INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id ORDER BY p.ordering ASC");
                foreach($products AS $product) {
                    $html .= '<option value="'.$product['pricing_ids'].'"';
                    if($product['pricing_ids'] == $this->PMDR->get('Cleaner')->unclean_html($element['value'])) {
                        $html .= ' selected="selected"';
                    }
                    $html .= '>'.$product['name'].'</option>';
                }
                $html .= $element['html'];
                $html .= '</select>';
                return $html;
                break;
            case 'hours':
                if(!empty($element['value']) AND $element['value'] != '24') {
                    $hours = unserialize($this->PMDR->get('Cleaner')->unclean_html($element['value']));
                }
                if(!is_array($element['options'])) {
                    $element['options'] = array();
                }
                $days = $this->PMDR->get('Dates')->getWeekDays(true);
                if($template_hours = $this->getFormTemplate('hours')) {
                    $template_hours->set('name',$element['name']);
                    $template_hours->set('value',$element['value']);
                    if($element['options']['hours_24'] == true) {
                        $template_hours->set('hours_24',true);
                        $template_hours->Set('hours_24_label',$element['options']['hours_24_label']);
                    }
                    if(is_array($hours) AND count($hours)) {
                        $selected_html .= '';
                        foreach($hours AS $hour) {
                            $hour_parts = explode(' ',$hour);
                            if($template_hours_selected = $this->getFormTemplate('hours_selected')) {
                                $template_hours_selected->set('name',$element['name']);
                                $template_hours_selected->set('day',$days[$hour_parts[0]]);
                                $template_hours_selected->set('time1',$this->PMDR->get('Dates')->formatTime(strtotime($hour_parts[1])));
                                $template_hours_selected->set('time2',$this->PMDR->get('Dates')->formatTime(strtotime($hour_parts[2])));
                                $template_hours_selected->set('hour',$hour);
                                $selected_html .= $template_hours_selected->render();
                            }
                        }
                        $template_hours->set('hours_selected',$selected_html);
                    }
                    $javascript = '
                    <script type="text/javascript">
                    $(document).ready(function() {
                        $("#'.$name.'_24_hours").change(function() {
                            $("#'.$name.'_container_options").toggle();
                        });
                        $("#'.$name.'_add").click(function() {
                            $.ajax({
                                type: "get",
                                url: "'.PMDROOT_RELATIVE.'/includes/data.php?'.http_build_query($element['options']).'",
                                data: ({
                                    type: "hours",
                                    name: "'.$name.'",
                                    day: $("#'.$name.'_weekday option:selected").text(),
                                    time1: $("#'.$name.'_start option:selected").text(),
                                    time2: $("#'.$name.'_end option:selected").text(),
                                    template_path: "'.$this->getFormTemplatePath().'",
                                    value: $("#'.$name.'_weekday").val()+" "+$("#'.$name.'_start").val()+" "+$("#'.$name.'_end").val()
                                }),
                                success: function(data) {
                                    $("#'.$name.'_display").append(data);
                                    $("#'.$name.'_weekday").val((parseInt($("#'.$name.'_weekday").val())+1)%7);
                                }
                            });
                        });
                        $("#'.$name.'_display").on("click","a",function(event) {
                            event.preventDefault();
                            $(this).parent().fadeOut(400,"swing",function() {
                                $(this).remove();
                            });
                        }).sortable().disableSelection();

                    });
                    </script>
                    ';
                    $template_hours->set('javascript',$javascript);
                    $times = $this->PMDR->get('Dates')->getTimeBlocks();
                    $days_options = '';
                    foreach($days AS $key=>$day) {
                        $days_options .= '<option value="'.$key.'">'.$day.'</option>';
                    }
                    $template_hours->set('days_options',$days_options);
                    $times_options = '';
                    foreach($times AS $time) {
                        $times_options .= '<option value="'.date('G:i',strtotime($time)).'">'.$time.'</option>';
                    }
                    $template_hours->set('times_options',$times_options);
                    return $template_hours->render();
                } else {
                    return '';
                }
                break;
            case 'color':
                if($template_color = $this->getFormTemplate('color')) {
                    $template_color->set('value',$element['value']);
                    $template_color->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_color->set('class',implode(' ',$element['class']));
                    $template_color->set('name',$element['name']);
                    $template_color->set('id',$element['id']);
                    if(isset($element['predefined'])) {
                        $template_color->set('predefined',true);
                        $colors = array(
                            '9A9CFF',
                            '5484ED',
                            'A4BDFC',
                            '46D6DB',
                            '7AE7BF',
                            '51B749',
                            'FBD75B',
                            'FFB878',
                            'FF887C',
                            'DC2127',
                            'DBADFF',
                            'E1E1E1'
                        );
                        $template_color->set('predefined_colors',$colors);
                    }
                    return $template_color->render();
                } else {
                    return '';
                }
                break;
            case 'submit':
                if($template_submit = $this->getFormTemplate('submit')) {
                    if($element['value'] != '') {
                        $template_submit->set('value',$element['value']);
                    } else {
                        $template_submit->set('value',$element['label']);
                    }
                    $template_submit->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_submit->set('class',implode(' ',$element['class']));
                    $template_submit->set('name',$element['name']);
                    return $template_submit->render();
                } else {
                    return '';
                }
                break;
            case 'image':
                if($template_submit = $this->getFormTemplate('submit_image')) {
                    if($element['value'] != '') {
                        $template_submit->set('value',$element['value']);
                    } else {
                        $template_submit->set('value',$element['label']);
                    }
                    $template_submit->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_submit->set('class',implode(' ',$element['class']));
                    $template_submit->set('name',$element['name']);
                    $template_submit->set('alt',$element['html']);
                    return $template_submit->render();
                } else {
                    return '';
                }
                break;
            case 'hidden':
                return '<input type="hidden"'.$this->getAttributesString($name).' value="'.$element['value'].'" />';
                break;
            case 'readonly':
                if($template_readonly = $this->getFormTemplate('readonly')) {
                    $template_readonly->set('value',$element['value']);
                    $template_readonly->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_readonly->set('class',implode(' ',$element['class']));
                    $template_readonly->set('name',$element['name']);
                    if(isset($element['options']) AND isset($element['options'][$element['value']])) {
                        $template_readonly->set('value_display',$element['options'][$element['value']]);
                    } else {
                        $template_readonly->set('value_display',$element['value']);
                    }
                    return $template_readonly->render();
                } else {
                    return '';
                }
                break;
            case 'custom':
                if($template_custom = $this->getFormTemplate('custom')) {
                    if($element['html']  != '') {
                        $template_custom->set('html',$element['html'] );
                    }
                    $template_custom->set('value',$element['value']);
                    $template_custom->set('attributes',$this->getAttributesString($name,array('class')));
                    $template_custom->set('class',implode(' ',$element['class']));
                    $template_custom->set('name',$element['name']);
                    return $template_custom->render();
                } else {
                    return '';
                }
                break;
            default:
                return 'Invalid Type';
        }
    }

    /**
    * Get Element label
    * Process access keys and labels
    * @param string $name Element name
    * @return string Label HTML
    */
    function getFieldLabel($name, $suffix = '') {
        if($label_template = $this->getFormTemplate('field_label')) {
            $access_key_html = '';
            $html = '';
            if($this->assign_access_keys) {
                $access_key_index = 0;
                do {
                    $access_key = (substr($this->elements[$name]['label'],$access_key_index,1));
                    $access_key_index++;
                } while (in_array(strtolower($access_key), $this->access_keys));

                $this->access_keys[] = strtolower($access_key);
                $access_key_html = ' accesskey="'.$access_key.'"';
                $this->elements[$name]['label'] = substr_replace($this->elements[$name]['label'],'<u>'.$access_key.'</u>',($access_key_index-1),1);
            }
            $attributes = (($this->label_width) ? 'style="width: '.$this->label_width.'"' : '').' for="'.$name.'"'.$access_key_html;
            $label_template->set('attributes',$attributes);
            if(is_array($this->elements[$name]['class'])) {
                $label_template->set('classes',implode(' ',$this->elements[$name]['class']));
            }
            $label_template->set('label',$this->elements[$name]['label']);
            if(!empty($this->elements[$name]['label']) AND $this->elements[$name]['label'] != '&nbsp;') {
                $label_template->set('suffix',$this->label_suffix);
            }
            $label_template->set('help',$this->elements[$name]['help']);
            if(value($this->elements[$name],'required') == true) {
                $label_template->set('required_text',$this->required_text);
            }
            return $label_template->render();
        } else {
            return '';
        }
    }

    /**
    * Get field set label
    * @param string $name Label name
    * @return string Label HTML
    */
    function getFieldSetLabel($name) {
        return $this->fieldsets[$name]['legend'];
    }

    /**
    * Get field values
    * @param array $elements Fields to get
    * @return array Field values with keys as field name, value as field value
    */
    function getValues($elements = array()) {
        $return_array = array();
        if(($count = count($elements)) > 0) {
            if($count == 1) return array($elements[0]=>$this->elements[$elements[0]]['value']);
            foreach($elements as $name) {
                if($element[$name]['type'] != 'submit') {
                    $return_array[$name] = $this->elements[$name]['value'];
                }
            }
        } else {
            foreach($this->elements as $name=>$element) {
                if($element['type'] != 'submit') {
                    $return_array[$name] = $element['value'];
                }
            }
        }
        return $return_array;
    }

    /**
    * Get single element value
    * @param string $element Field name
    * @return mixed Element value
    */
    function getFieldValue($element) {
        return $this->elements[$element]['value'];
    }

    /**
    * Check if an field is in the form
    * @param string $name Field name
    * @return boolean
    */
    function fieldExists($name) {
        return isset($this->elements[$name]);
    }

    /**
    * Check if an field has type hidden
    * @param string $name Field name
    * @return boolean
    */
    function isFieldHidden($name) {
        return ($this->elements[$name]['type'] == 'hidden') ? true : false;
    }

    /**
    * Check if form was submitted
    * @param string $submitButton Submit button name
    * @param boolean $checkHiddenField Used for security to check a hidden field coming from the script
    * @return boolean True if submitted
    */
    function wasSubmitted($submitButton, $checkHiddenField = true) {
        if($this->method == 'POST') {
            if(!isset($_POST[$submitButton]) OR !isset($_POST['bot_check']) OR $_POST['bot_check'] != '') {
                return false;
            }
            if($checkHiddenField) {
                if(empty($_COOKIE[COOKIE_PREFIX.'from']) OR !isset($_COOKIE[COOKIE_PREFIX.'from']) OR $_COOKIE[COOKIE_PREFIX.'from'] != $_POST[COOKIE_PREFIX.'from']) {
                    return false;
                }
            }
        } else {
            if(!isset($_GET[$submitButton]) OR !isset($_GET['bot_check']) OR $_GET['bot_check'] != '') {
                return false;
            }
            if($checkHiddenField) {
                if(empty($_COOKIE[COOKIE_PREFIX.'from']) OR !isset($_COOKIE[COOKIE_PREFIX.'from']) OR $_COOKIE[COOKIE_PREFIX.'from'] != $_GET[COOKIE_PREFIX.'from']) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
    * Get raw form value
    * @param string $name
    * @return mixed
    */
    function getRawValue($name) {
        $method = '_'.$this->method;
        global ${$method};
        return ${$method}[$name];
    }

    /**
    * Load input values from submission
    * @param array Source array to check for fields
    * @return array Values submitted
    */
    function loadValues($source = null) {
        if(!is_null($source)) {
            foreach($this->elements as $name=>$element) {
                if(isset($source[$name])) {
                    if($this->elements[$name]['type'] == 'text_select') {
                        $source[$name] = array_filter($source[$name]);
                    }
                    switch($this->elements[$name]['type']) {
                        case 'checkbox':
                        case 'select_multiple':
                        case 'text_select':
                            if(!is_array($source[$name]) AND strstr($source[$name],"\n")) {
                                $source[$name] = explode("\n",$source[$name]);
                            }
                            break;
                    }
                    $this->elements[$name]['value'] = $source[$name];
                }
            }
        } else {
            $method = '_'.$this->method;
            global ${$method}; // not sure of this
            foreach($this->elements as $name=>$element) {
                if(!isset(${$method}[$name]) AND $this->elements[$name]['type'] == 'checkbox') {
                    if(is_array($this->elements[$name]['options']) AND count($this->elements[$name]['options']) < 2) {
                        ${$method}[$name] = '0';
                    } else {
                        ${$method}[$name] = array();
                    }
                }
                if(isset($this->elements[$name]['implode']) AND $this->elements[$name]['implode']) {
                    ${$method}[$name] = implode((isset($this->elements[$name]['implode_character']) ? $this->elements[$name]['implode_character'] : "\n"),(array) ${$method}[$name]);
                }
                if($this->elements[$name]['type'] == 'htmleditor') {
                    ${$method}[$name] = preg_replace("/(\r\n|\n|\r|\t)/",'',trim(${$method}[$name]));
                }

                switch($this->elements[$name]['type']) {
                    case 'file':
                        if(isset(${$method}[$name.'_url']) AND !empty(${$method}[$name.'_url']) AND valid_url(${$method}[$name.'_url'])) {
                            $this->elements[$name]['value'] = $this->PMDR->get('Cleaner')->clean_input(${$method}[$name.'_url']);
                        } else {
                            if($_FILES[$name]['size'] == 0) {
                                $this->elements[$name]['value'] = '';
                            } else {
                                $this->elements[$name]['value'] = $_FILES[$name];
                            }
                        }
                        break;
                    case 'tree_select_multiple':
                    case 'tree_select_multiple_group':
                        if(empty(${$method}[$name]) OR !is_array(${$method}[$name])) {
                            $this->elements[$name]['value'] = array();
                        } else {
                            $this->elements[$name]['value'] = $this->PMDR->get('Cleaner')->clean_input(${$method}[$name], $this->getAllowedHTML($name), $this->elements[$name]['no_trim']);
                        }
                        break;
                    case 'tree_select_expanding':
                    case 'tree_select_expanding_checkbox':
                        $this->elements[$name]['value'] = preg_split('/[,]+/',$this->PMDR->get('Cleaner')->clean_input(${$method}[$name]),-1,PREG_SPLIT_NO_EMPTY);
                        break;
                    case 'date':
                        $this->elements[$name]['value'] = $this->PMDR->get('Dates')->formatDateInput($this->PMDR->get('Cleaner')->clean_input(${$method}[$name]));
                        break;
                    case 'datetime':
                        if(empty(${$method}[$name])) {
                            $this->elements[$name]['value'] = null;    
                        } else {
                            $this->elements[$name]['value'] = $this->PMDR->get('Dates')->formatDateTimeInput($this->PMDR->get('Cleaner')->clean_input(${$method}[$name]),$this->elements[$name]['time']);
                        }
                        break;
                    case 'hours':
                        if(isset(${$method}[$name.'_24_hours']) AND ${$method}[$name.'_24_hours'] == '24') {
                            $this->elements[$name]['value'] = '24';
                        } else {
                            $this->elements[$name]['value'] = serialize(${$method}[$name]);
                        }
                        break;
                    case 'currency':
                        $this->elements[$name]['value'] = format_number_currency_input(${$method}[$name]);
                        break;
                    case 'url_title':
                        $this->elements[$name]['value'] = $this->PMDR->get('Cleaner')->clean_input(${$method}[$name.'_title']).'|'.$this->PMDR->get('Cleaner')->clean_input(${$method}[$name]);
                        break;
                    case 'url_title_multiple':
                        $values = array();
                        foreach(${$method}[$name.'_title'] AS $key=>$value) {
                            $values[] = $this->PMDR->get('Cleaner')->clean_input($value).'|'.$this->PMDR->get('Cleaner')->clean_input(${$method}[$name][$key]);
                        }
                        $this->elements[$name]['value'] = implode("\n",$values);
                        unset($key,$value,$values);
                        break;
                    case 'text_select':
                        $this->elements[$name]['value'] = array_filter(${$method}[$name]);
                        break;
                    case 'text_unlimited':
                        if(!isset(${$method}[$name]) OR ${$method}[$name] == '') {
                            $this->elements[$name]['value'] = NULL;
                        } else {
                            $this->elements[$name]['value'] = $this->PMDR->get('Cleaner')->clean_input(${$method}[$name]);
                        }
                        break;
                    default:
                        if(isset(${$method}[$name])) {
                            $this->elements[$name]['value'] = $this->PMDR->get('Cleaner')->clean_input(${$method}[$name], $this->getAllowedHTML($name), $this->elements[$name]['no_trim']);
                        } else {
                            $this->elements[$name]['value'] = '';
                        }
                        break;
                }
            }
        }
        return $this->getValues();
    }

    /**
    * Validate field values
    * @return boolean True if validated
    */
    function validate() {
        foreach($this->validators as $element=>$element_validators) {
            foreach($element_validators as $validator_string) {
                if(!$validator_string->validate((property_exists($validator_string,'raw') AND $validator_string->raw) ? $this->getRawValue($element) : $this->elements[$element]['value'])) {
                    $this->errors[] = sprintf($validator_string->error,$this->elements[$element]['label']);
                    $this->elements[$element]['class'][] = 'error';
                    $this->elements[$element]['error'] = true;
                }
            }
        }
        if(count($this->errors) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Add an error to the form
    * @param string $error Error message
    * @param string $element Field name
    * @return void
    */
    function addError($error,$element=null) {
        if(!is_null($element)) {
            $this->errors[] = sprintf($error,$this->elements[$element]['label']);
            $this->elements[$element]['class'][] = 'error';
        } else {
            $this->errors[] = $error;
        }
    }

    /**
    * Parse error array
    * @return string Errors parsed
    */
    function parseErrorsForTemplate() {
        return $this->errors;
    }

    /**
    * Set field attribute
    * @param string $name Field name
    * @param string $attribute Attribute name
    * @param mixed $value Value to set the attribute to
    */
    function setFieldAttribute($name, $attribute, $value) {
        if(isset($this->elements[$name])) {
            $this->elements[$name][$attribute] = $value;
        }
    }

    /**
    * Delete a field from the form
    * @param string $name Field name
    * @return void
    */
    function deleteField($name) {
        $this->removeValidator($name);
        foreach($this->fieldsets as $fieldset_key=>$fieldset) {
            foreach($fieldset['elements'] as $element_key=>$element) {
                if($element == $name) {
                    unset($this->fieldsets[$fieldset_key]['elements'][$element_key]);
                }
            }
        }
        unset($this->elements[$name]);
    }

    /**
    * Delete a field set and all fields from the form
    * @param string $name Field name
    */
    function deleteFieldSet($name) {
        foreach($this->fieldsets[$name]['elements'] as $element_name) {
            $this->removeValidator($name);
            $this->deleteField($element_name);
        }
        unset($this->fieldsets[$name]);
    }

    /**
    * Remove validator from a field
    * @param string $name Field name
    */
    function removeValidator($name) {
        unset($this->validators[$name]);
    }

    /**
    * Automatically generate the form
    * @return string Form HTML
    */
    function toHTML() {
        if($form_template = $this->getFormTemplate()) {
            $form_template->set('open',$this->getFormOpenHTML());
            $fieldset_template_array = array();
            foreach($this->fieldsets as $fieldset_name=>$fieldset) {
                if($fieldset_name != 'submit' AND $fieldset_name != 'hidden') {
                    if(!empty($fieldset['template'])) {
                        $fieldset_template = $this->getFormTemplate($fieldset['template']);
                    } else {
                        $fieldset_template = $this->getFormTemplate('fieldset');
                    }
                    $fieldset_template->set('fieldset_attributes',$this->getFieldsetAttributesString($fieldset_name));
                    if(!empty($fieldset['legend'])) {
                        $fieldset_template->set('legend',$fieldset['legend'].$fieldset['help']);
                    }
                    $field_template_array = array();
                    if(is_array($fieldset['elements'])) {
                        foreach($fieldset['elements'] as $element) {
                            $field_template_array[] = $this->getFieldGroup($element);
                        }
                    }
                    $fieldset_template->set('fields',$field_template_array);
                    $fieldset_template_array[] = $fieldset_template->render();
                }
            }
            $form_template->set('fieldsets',$fieldset_template_array);
            $form_template->set('actions',$this->getFormActions());
            if(isset($this->fieldsets['hidden'])) {
                $hidden_fields = '';
                foreach((array) $this->fieldsets['hidden']['elements'] as $element) {
                    $hidden_fields .= $this->getFieldHTML($element);
                }
                $form_template->set('hidden_fields',$hidden_fields);
            }
            $form_template->set('close',$this->getFormCloseHTML());
            return $form_template->render();
        } else {
            return '';
        }
    }

    /**
    * Merge current form with another form
    * @param object $form Form to merge with
    */
    function mergeWithForm($form) {
        if($form->action != '') {
            $this->action = $form->action;
        }
        $this->fieldsets = array_merge_recursive((array) $this->fieldsets,(array) $form->fieldsets);
        $this->counters = array_merge((array) $this->counters, (array) $form->counters);
        $this->elements = array_merge((array) $this->elements, (array) $form->elements);
        $this->filters = array_merge((array) $this->filters, (array) $form->filters);
        $this->help = array_merge((array) $this->help,(array) $form->help);
        $this->notes = array_merge((array) $this->notes,(array) $form->notes);
        $this->pickers = array_merge((array) $this->pickers, (array) $form->pickers);
        $this->validators = array_merge((array) $this->validators,(array) $form->validators);
        unset($form);
        return $this;
    }
}
?>