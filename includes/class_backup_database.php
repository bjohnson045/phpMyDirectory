<?php
/**
 * MySQL Dump Manager
 */
class Backup_Database {
    /**
    * Database object
    * @var object
    */
    var $db;
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
     * Newline characters
     * @var string
     */
    var $newline;

    /**
     * File handler
     * @var file handle
     */
    var $handler;
    /**
     * File handler
     * @var file handle
     */
    var $compression;

    /**
     * Constructor, setup DB and newline
     * @param object $PMDR
     * @return Backup_Database
     */
    function __construct($PMDR) {
        $this->db = $PMDR->get('DB');
        $this->PMDR = $PMDR;
        $this->newline = "\r\n";
    }

    /**
     * Get list of all table names
     * @return array List of tables
     */
    function getTables() {
        return $this->db->GetCol("SHOW TABLES LIKE '".DB_TABLE_PREFIX."%'");
    }

    /**
     * Dump table create text and inserts
     * @param string $table
     * @return string of creation structure and inserts
     */
    function dumpTable($table) {
        $row = $this->db->GetRow("SHOW CREATE TABLE " . $table);
        $this->writeToFile("# TABLE STRUCTURE FOR TABLE $table" . $this->newline);
        $this->writeToFile("DROP TABLE IF EXISTS `" . $table . "`;" . $this->newline);
        $this->writeToFile(str_replace("\n", $this->newline, $row['Create Table']).';');
        unset($row);
        $this->writeToFile($this->newline . $this->newline);
        $this->getInserts($table);
    }

    /**
     * Get inserts for a table
     * @param string $table
     * @return string of inserts
     */
    function getInserts($table) {
        $this->writeToFile("# DUMPING DATA FOR TABLE $table" . $this->newline);
        $column_names = $this->db->MetaColumnNames($table);
        $index = 0;
        if($total_count = $this->db->GetOne("SELECT COUNT(*) FROM $table")) {
            $this->writeToFile("INSERT INTO ".$table." (".implode(',',$column_names).") VALUES ".$this->newline);
            do {
                // Free the memory so we can get the next set of records
                if(isset($r)) unset($r);

                $r = $this->db->GetAll("SELECT * FROM $table LIMIT $index,30");
                foreach($r as $key=>$record) {
                    $inserts = "";
                    foreach($record as $value) {
                        $prepared_value = $this->PMDR->get('Cleaner')->clean_db($value,false);
                        if((gettype($value) == 'string' OR empty($value)) AND !is_numeric($value)) {
                            $prepared_value = "'".$prepared_value."'";
                        }
                        $inserts .= $prepared_value.",";
                        unset($prepared_value);
                    }
                    unset($value);
                    $this->writeToFile("(".rtrim($inserts,",").")");
                    unset($inserts);
                    if($index+$key+1 >= $total_count) {
                        $this->writeToFile(";".$this->newline);
                    } elseif(($index+$key+1) % 1000 == 0 AND $index !=0) {
                        $this->writeToFile(";".$this->newline);
                        $this->writeToFile("INSERT INTO ".$table." (".implode(',',$column_names).") VALUES ".$this->newline);
                    } else {
                        $this->writeToFile(",".$this->newline);
                    }
                }
                $index += count($r);
            } while($r);
        }
    }

    /**
     * Write backup file
     * @return string for entire backup
     */
    function writeBackup() {
        $this->writeToFile($this->setHeader());
        $count = 0;
        $tables = $this->getTables();
        foreach ($tables as $value) {
            $this->dumpTable($value);
            $this->writeToFile($this->newline . $this->newline);
            $count++;
        }
    }

    /**
     * Dump backup into a file
     * @param string $file
     * @param string $compress
     * @return void
     */
    function createFile($file,$compress) {
        $this->compression = $compress;
        if($compress) {
            $this->handler = gzopen($file, 'w9');
        } else  {
            $this->handler = fopen($file, 'w');
        }
        if(!$this->handler) {
            return false;
        }
        $this->writeBackup();
        $this->closeFile();
    }

    /**
    * Write content to file
    * @param string $content
    */
    function writeToFile($content) {
        if($this->compression) {
            gzwrite($this->handler, $content);
        } else {
            fwrite($this->handler, $content);
        }
    }

    /**
    * Close file handler
    */
    function closeFile() {
        if($this->compression) {
            gzclose($this->handler);
        } else {
            fclose($this->handler);
        }
    }

    /**
     * Setup backup string header
     * @return string Header
     */
    function setHeader() {
        $header = '# Database Backup' . $this->newline;
        $header .= '# Generated: ' . date('M j, Y') . ' at ' . date('H:i') . $this->newline;
        $header .= '# MySQL version: ' . $this->db->version() . $this->newline . $this->newline;
        return $header;
    }
}
?>