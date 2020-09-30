<?php
/**
* Class ServeFile
* Serve a file to the browser
*/
class ServeFile {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var string File Contents
    */
    var $file_contents;
    /**
    * @var string File Name
    */
    var $file_name;
    /**
    * @var string File extension
    */
    var $file_extension;
    /**
    * @var string Content Type
    */
    var $content_type;
    /**
    * @var integer File Size in bytes
    */
    var $file_size;

    /**
    * ServeFile Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __constructor($PMDR) {
        $this->PMDR = $PMDR;
    }

    /**
    * Get Content Type
    * @param string $file_extension File extension
    * @return void
    */
    function getContentType($file_extension) {
        switch($file_extension) {
            case "pdf": $ctype="application/pdf"; break;
            case "exe": $ctype="application/octet-stream"; break;
            case "zip": $ctype="application/zip"; break;
            case "doc": $ctype="application/msword"; break;
            case "xls": $ctype="application/vnd.ms-excel"; break;
            case "csv": $ctype="text/csv"; break;
            case "txt": $ctype="text/plain"; break;
            case "htm":
            case "html": $ctype="text/html"; break;
            case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "vcf": $ctype="text/x-vCard"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpg"; break;
            default: $ctype="application/force-download";
            $this->content_type = $ctype;
        }
    }

    /**
    * Serve file to browser
    * @param string $filename Name of file sent to browser
    * @param string $contents Contents of file
    * @return void
    */
    function serve($filename, $contents=NULL) {
        $this->content_type = $this->getContentType(strtolower(substr(strrchr($filename,"."),1)));
        if($contents != NULL) {
            $this->file_name = $filename;
            $this->file_contents = $contents;
            $this->file_size = strlen($contents);
        } else if(file_exists($filename)) {
            $this->file_contents = file_get_contents($filename);
            $this->file_size = filesize($filename);
            $this->file_name = basename($filename);
        } else {
            return false;
        }

        if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: ".$this->content_type."; charset=".CHARSET);
        header("Content-Disposition: attachment; filename=\"".$this->file_name."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$this->file_size);
        echo $this->file_contents;
        exit();
    }
}
?>