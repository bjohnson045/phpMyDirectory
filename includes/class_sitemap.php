<?php
/**
* Class SitemapIndex
* Used to create a google sitemap index
*/
class SitemapIndex {
    /**
    * @var string Sitemap XML
    */
    var $xml;
    /**
    * @var string New Line character used for formatting
    */
    var $newline = "\n";

    /**
    * SitemapIndex Constructor
    */
    function __construct() {}

    /**
    * Get opening line for sitemap index
    * @return string Sitemap opening tag
    */
    function getOpenIndex() {
        return '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."$this->newline";
    }

    /**
    * Get closing tag for sitemap index
    * @return string Sitemap closing tag
    */
    function getCloseIndex() {
        return "</sitemapindex>";
    }

    /**
    * Convert url to proper formatting string
    * @return string URL formatted converting HTML entities
    */
    function getUrlString ($urlString) {
        return htmlspecialchars($urlString, ENT_QUOTES, 'UTF-8');
    }

    /**
    * Write sitemaps to files
    * @return void
    */
    function writeSitemaps() {
        if ($handle = opendir(FILES_PATH.'/sitemaps')) {
            while (false !== ($file = readdir($handle))) {
                $this->addSitemap(BASE_URL.'/files/sitemaps/'.$file,date("Y-m-d",filemtime($filename)));
            }
            closedir($handle);
        }
    }

    /**
    * Get Sitemap Index XML
    * @return string Sitemap XML
    */
    function getXML() {
        //$xml = $this->getHeader();
        $xml .= $this->getOpenIndex();
        $xml .= $this->xml;
        $xml .= $this->getCloseIndex();
        return $xml;
    }

    /**
    * Add sitemap to sitemap index
    * @param string $url Sitemap URL
    * @param string $modified Last modified date
    */
    function addSitemap($url, $modified) {
        $this->xml .= "<sitemap>$this->newline";
        $this->xml .= " <loc>".$this->getUrlString($url)."</loc>$this->newline";
        $this->xml .= " <lastmod>$modified</lastmod>$this->newline";
        $this->xml .= "</sitemap>$this->newline";
    }

    function pingSearchEngine($search_engine_url, $sitemap_url) {
        $url_parts = parse_url($search_engine_url);
        if($fp=fsockopen($url_parts['host'], 80, $errorno, $errstr, 15)) {
            $request = "GET ".$url_parts['path']."?".$url_parts['query'].urlencode($sitemap_url)." HTTP/1.1\r\n";
            $request .= "Connection: Close\r\n\r\n";
            fwrite($fp, $request);

            $response = '';
            while(!feof($fp)) {
                $response .= fgets($fp, 128);
            }
            fclose($fp);

            if(preg_match("/200\sOK/", $response)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

/**
* Class Sitemap
* Used to generate formatted sitemap files for search engines
*/
class Sitemap {
    /**
    * @var string Sitemap XML
    */
    var $xml;
    /**
    * @var string New Line character used for formatting
    */
    var $newline = "\n";

    /**
    * Sitemap Constructor
    */
    function __construct() {}

    /**
    * Get opening tag for sitemap
    * @return string Sitemap opening tag
    */
    function getOpenUrlSet() {
        return '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."$this->newline";
    }

    /**
    * Get closing tag for sitemap
    * @return string Sitemap closing tag
    */
    function getCloseUrlSet() {
        return "</urlset>$this->newLine";
    }

    /**
    * Get properly formatted url
    * @param string $urlString URL to be formatted
    * @return string Formatted url
    */
    function getUrlString ($urlString) {
        return htmlspecialchars($urlString, ENT_QUOTES, 'UTF-8');
    }

    /**
    * Add sitemap URL
    * @param string $url URL to index
    * @param string $modified Last modified date
    * @param string $change How often the url is changed (default = 'daily')
    * @param string $priority Priority of url
    * @return void
    */
    function addURLTag($url, $modified=null, $change=null, $priority=null) {
        $modified = ($modified) ? $modified : date('Y-m-d');
        $this->xml .= " <url>$this->newline";
        $this->xml .= "  <loc>".$this->getURLString($url)."</loc>$this->newline";
        if(!is_null($modified)) {
            $this->xml .= "  <lastmod>$modified</lastmod>$this->newline";
        }
        if(!is_null($change)) {
            $this->xml .= "  <changefreq>$change</changefreq>$this->newline";
        }
        if(!is_null($priority)) {
            $this->xml .= "  <priority>$priority</priority>$this->newline";
        }
        $this->xml .= " </url>$this->newline";
    }

    /**
    * Write sitemaps to file
    * @param string $filename Filename of sitemap
    * @return void
    */
    function writeToFile($filename) {
        if($handle = @gzopen(PMDROOT.FILES_PATH.'sitemaps/'.$filename.'.gz','w+9')) {
            gzwrite($handle,$this->xml);
            gzclose($handle);
        } elseif($handle = fopen(PMDROOT.FILES_PATH.'sitemaps/'.$filename.'.xml', 'w+')) {
            fwrite($handle,$this->xml);
            fclose($handle);
        }
    }

    /**
    * Get sitemap XML
    * @return string Sitemap XML
    */
    function getXML() {
        $xml = $this->getOpenUrlSet();
        $xml .= $this->xml;
        $xml .= $this->getCloseUrlSet();
        return $xml;
    }
}
?>