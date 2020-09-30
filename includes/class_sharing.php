<?php
class Sharing {
    /**
    * Registry object
    * @var object
    */
    var $PMDR;
    /**
    * Database object
    * @var Database
    */
    var $db;
    /**
    * Sharing ID
    * Used by some publishers to link an account
    */
    var $id = null;
    /**
    * Share URL
    * @var string
    */
    var $url = 'http://www.addthis.com/bookmark.php?v=20';

    /**
    * Sharing constructor
    * @param object $PMDR
    * @return Sharing
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        if($PMDR->getConfig('addthis_pub_id') != '') {
            $this->id = $PMDR->getConfig('addthis_pub_id');
        }
    }

    /**
    * Get the javascript include required for the sharing platform
    * return string
    */
    function getJavascript() {
        return '<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js"></script>';
    }

    /**
    * Load the javascript
    */
    function loadJavascript() {
        if(!is_null($this->id)) {
            $this->PMDR->loadJavascript('<script type="text/javascript">var addthis_pub="'.$this->id.'";</script>');
        }
        $this->PMDR->loadJavascript($this->getJavascript());
    }

    /**
    * Get the share URL
    * @return string;
    */
    function getURL() {
        return $this->url;
    }

    /**
    * Get the sharing publishers ID
    * @return string
    */
    function getID() {
        return $this->id;
    }

    /**
    * Get display HTML
    */
    function getHTML($url = null, $title = null, $image = null, $compact = false, $event = array(), $class = 'addthis_sharing_toolbox') {
        $this->loadJavascript();
        $template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/share.tpl');
        $template->set('url',$url);
        $template->set('title',$title);
        $template->set('image',$image);
        $template->set('default',is_null($this->id));
        $template->set('class',$class);
        $template->set('compact',$compact);
        if(!empty($event) AND isset($event['action'],$event['type'],$event['type_id'])) {
            $template->set('share_event_action',$event['action']);
            $template->set('share_event_type',$event['type']);
            $template->set('share_event_type_id',$event['type_id']);
        }
        return $template;
    }

    /**
    * Get javascriot to attach a share action on a button
    * @param string $element_id Button ID
    * @param string $url URL to share
    * @param string $title Title to share
    * @return string
    */
    function getButtonScript($element_id = 'id', $url = null, $title = null) {
        if(!is_null($url)) {
            $url = 'url';
        }
        if(!is_null($title)) {
            $title = 'title';
        }
        return 'addthis.button(\'#\'+$(this).attr("'.$element_id.'"), {}, {url: $(this).data("'.$url.'"), title: $(this).data("'.$title.'")});';
    }
}
?>