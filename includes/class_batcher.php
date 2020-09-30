<?php
class Batcher {
    var $PMDR;
    var $db;
    var $run_count = 100;
    var $run_count_current = 0;
    var $source;

    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
    }

    function printRedirectJavascript() {
        if($this->total != $this->current) {
            echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=batching&current=".$this->current."&source=".$this->getSource()."\"; } window.onload = runAgain;</script>";
        } else {
            echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=complete\"; } window.onload = runAgain;</script>";
        }
    }

    function limitReached() {
        if($this->run_count_current < $this->run_count) {
            return false;
        } else {
            return true;
        }
    }

    function incrementRunCounter($count = 1) {
        $this->run_count_current += $count;
    }

    function loadBatch($source, $current) {
        ob_start();
        $this->setCurrent($current);
    }

    function unloadBatch() {
        $this->current = $this->getCurrent();
        $this->printRedirectJavascript();
        return ob_get_clean();
    }

    function getSource() {
        return $this->source;
    }

    function getCurrent() {
        return $this->current;
    }

    function setCurrent($current) {
        $this->current = $current;
    }
}

class File_Batcher extends Batcher {
    var $file;
    var $file_pointer;

    function setCurrent($current) {
        if($current) {
            fseek($this->file_pointer, $current);
        }
    }

    function dataSourceExists($file = null) {
        if(!is_null($file)) {
            return (!file_exists($file) OR filetype($file) == 'dir');
        } else {
            return (!file_exists($this->source) OR filetype($this->source) == 'dir');
        }
    }

    function loadBatch($source, $current) {
        $this->source = $source;
        $this->file_pointer = fopen($this->source, 'r');
        $this->total = $this->getTotal();
        parent::loadBatch($source, $current);
    }

    function getTotal() {
        return filesize($this->source);
    }

    function getCurrent() {
        return ftell($this->file_pointer);
    }
}

class CSV_Batcher extends File_Batcher {
    var $delimiter = ',';
    var $encapsulator = '"';

    function getBatchRow() {
        if($this->limitReached()) {
            return false;
        }
        $this->incrementRunCounter();
        return fgetcsv($this->file_pointer, 0, $this->delimiter, $this->encapsulator);
    }
}

class Database_Batcher extends Batcher {
    var $query = '';

    function loadBatch($source, $current) {
        $this->source = $source;
        $this->total = $this->getTotal();
        parent::loadBatch($source, $current);
    }

    function getCurrent() {

        return $this->current + $this->run_count_current;
    }

    function getTotal() {
        return $this->db->GetOne("SELECT COUNT(*) FROM ".$this->source);
    }

    function getBatch() {
        $records = $this->db->GetAll($this->query,array((int)$this->current,(int)$this->run_count));
        $this->incrementRunCounter(count($records));
        return $records;
    }
}
?>