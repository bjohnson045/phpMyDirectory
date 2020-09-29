<?php
/**
* Class RSS
* Generate formatted RSS feed xml
*/
class RSS {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * Feed title
    * @var string
    */
    var $title;
    /**
    * Feed URL
    * @var string
    */
    var $url;
    /**
    * Feed link
    * @var string
    */
    var $link;
    /**
    * Feed description
    * @var string
    */
    var $description;
    /**
    * Feed image
    * @var string
    */
    var $image = null;
    /**
    * Feed language
    * @var string
    */
    var $language = null;
    /**
    * RSS items
    * @var array
    */
    var $items = array();

    /**
    * RSS Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->language = $this->PMDR->getLanguage('languagecode');
    }

    /**
    * Add feed image
    * @param string $url URL to the image
    * @param string $title Title of the image
    * @param string $link Link to be applied to image
    * @param string $description Image description
    * @param integer $width Image width
    * @param integer $height Image height
    * @return void
    */
    function addChannelImage($url, $title, $link, $description, $width = null, $height = null) {
        $this->image = array('url'=>$url,'title'=>$title,'link'=>$link,'description'=>$description,'width'=>$width,'height'=>$height);
    }

    /**
    * Add feed item
    * @param string $title Item title
    * @param string $link Link applied to item
    * @param string $description Item description
    * @param string $pubDate Item publication date
    * @param string $author Item author
    * @param string $category Item category
    * @param string $comments Author comments
    * @param string $enclosure
    * @param integer $guid
    * @param string $source
    * @return void
    */
    function addItem($title, $link, $description, $pubDate = null, $author = null, $category = null, $comments = null, $enclosure = null, $guid = null, $source = null) {
        $this->items[] = array(
            'title'=>$title,
            'link'=>$link,
            'description'=>$description,
            'pubDate'=>$pubDate,
            'author'=>$author,
            'category'=>$category,
            'comments'=>$comments,
            'enclosure'=>$enclosure,
            'guid'=>$guid,
            'source'=>$source
        );
    }

    /**
    * Get RSS feed xml
    * @return string Feed XML
    */
    function getRSS() {
        $xml = '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        if(!is_null($this->url)) {
            $xml .= '<atom:link href="'.$this->url.'" rel="self" type="application/rss+xml" />';
        }
        $xml .= '<title><![CDATA['.$this->title.']]></title>';
        $xml .= '<link>'.$this->link.'</link>';
        $xml .= '<description><![CDATA['.$this->description.']]></description>';
        $xml .= '<language>'.$this->language.'</language>';
        if(!is_null($this->image)) {
            $xml .= '<image>';
            $xml .= '<title><![CDATA['.$this->image['title'].']]></title>';
            $xml .= '<url>'.$this->image['url'].'</url>';
            $xml .= '<link>'.$this->image['link'].'</link>';
            $xml .= '</image>';
        }
        foreach($this->items as $item) {
            $xml .= '<item>';
            $xml .= '<title><![CDATA['.$item['title'].']]></title>';
            $xml .= '<link>'.trim($item['link']).'</link>';
            if(!is_null($item['guid'])) {
                $xml .= '<guid>'.trim($item['guid']).'</guid>';
            } else {
                $xml .= '<guid>'.trim($item['link']).'</guid>';
            }
            $xml .= '<description><![CDATA['.$item['description'].']]></description>';
            if($item['pubDate'] != '') {
                $xml .= '<pubDate>'.$item['pubDate'].'</pubDate>';
            }
            $xml .= '</item>';
        }
        $xml .= '</channel>';
        $xml .= '</rss>';
        return $xml;
    }

    /**
    * Print RSS feed
    * @return void
    */
    function printRSS() {
        echo $this->getRSS();
    }
}
?>