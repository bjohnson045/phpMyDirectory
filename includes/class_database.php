<?php
/**
* Database class
*/
class Database {
    /**
    * Database link/connection
    * @var resource
    */
    var $connection;
    /**
    * Debug mode
    * @var boolean
    */
    var $debug = false;
    /**
    * List of queries
    * @var array
    */
    var $query_list;
    /**
    * Query count
    * @var int
    */
    var $query_count = 0;
    /**
    * Table to be used for query logging
    * @var mixed
    */
    var $log_sql_table = null;
    /**
    * Suppress error messages
    * @var boolean
    */
    var $suppress_errors = false;
    /**
    * Current/last statement
    */
    var $statement;
    /**
    * Last insert ID
    */
    var $insert_id;

    /**
    * Dabase Constructor
    * @return Database
    */
    function __construct() {}

    /**
    * Connect to the database
    * @param string $host
    * @param string $user
    * @param string $pass
    * @param string $name
    * @param string $type
    * @param mixed $charset
    * @param mixed $collation
    */
    function Connect($host, $user, $pass, $name, $port, $charset = null, $collation = null) {
        $options = array();
        if(!is_null($charset) AND $charset != '') {
            $collation_query = "SET NAMES '".$charset."'";
            if(!is_null($collation) AND $collation != '') {
                $collation_query .= " COLLATE '".$collation."'";
            }
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = $collation_query;
        }
        try {
            $this->connection = new PDO('mysql:dbname='.$name.';host='.$host.';port='.$port,$user,$pass,$options);
        } catch (PDOException $e) {
            return false;
        }
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        if(!$this->suppress_errors AND error_reporting() != 0) {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->connection;
    }

    /**
    * Set the table to log queries to
    * @param string $table
    */
    function LogSQL($table) {
        $this->log_sql_table = $table;
    }

    /**
    * Get the PDO value type
    * @param mixed $value
    * @return int PDO value type
    */
    function getValueType($value) {
        switch(gettype($value)) {
            case 'integer':
                $type = PDO::PARAM_INT;
                break;
            case 'boolean':
                $type = PDO::PARAM_BOOL;
                break;
            case 'NULL':
                $type = PDO::PARAM_NULL;
                break;
            default:
                if($value === null OR $value === 'NULL') {
                    $type = PDO::PARAM_NULL;
                } else {
                    $type = PDO::PARAM_STR;
                }
        }
        return $type;
    }

    /**
    * Clean incoming strings
    * @param string $str
    */
    function Clean($str, $quotes = true) {
        // Convert to an integer if numeric excluding numbers starting with 0 or other characters such as "+"
        if(is_numeric($str) AND is_numeric(substr($str,0,1)) AND substr($str,0,1) != '0') {
            if(intval($str) == floatval($str)) {
                $str = intval($str);
            }
        }
        $type = $this->getValueType($str);
        if($str === 'NULL') {
            $str = NULL;
        }
        $str = $this->connection->quote($str,$type);
        if(!$quotes) {
            $str = trim($str,"'");
        }
        return $str;
    }

    /**
    * Clean database table names
    * @param string $table_name
    * @return string Cleaned table name
    */
    function CleanIdentifier($value) {
        if(!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/',$value)) {
            return false;
        }
        return '`'.$value.'`';
    }

    /**
    * Get order by string
    * Used when the order could be variable based on input parameters
    * @param mixed $sort
    * @param mixed $direction
    * @param mixed $default
    * @return mixed
    */
    function OrderBy($sort, $direction = null, $default = null) {
        if(empty($sort)) {
            if(!is_null($default)) {
                return 'ORDER BY '.$default;
            } else {
                return '';
            }
        }
        $order_by = 'ORDER BY '.$this->CleanIdentifier($sort);
        if(!is_null($direction) AND !empty($direction) AND ($direction == 'ASC' OR $direction == 'DESC')) {
            $order_by .= ' '.$direction;
        }
        return $order_by;
    }

    /**
    * Perform a query
    * @param string $sql
    * @param array $variables
    * @param boolean $debug
    * @return resource
    */
    function Query($sql, $variables = array(),$debug=true) {
        if(!is_array($variables)) {
            $variables = array($variables);
        }
        $variables = array_values($variables);
        if(!is_null($this->log_sql_table) AND $debug) {
            $time_start = microtime(true);
        }

        try {
            $this->statement = $this->connection->prepare($sql);
            foreach($variables AS $key=>$variable) {
                $type = $this->getValueType($variable);
                if($variable === 'NULL') {
                    $variable = NULL;
                }
                $this->statement->bindValue($key+1,$variable,$type);
            }
        } catch(PDOException $e) {
            throw $e;
        }
        try {
            $this->statement->execute();
        } catch(PDOException $e) {
            if($e->errorInfo[1]) {
                $error_code = ' ('.$e->errorInfo[1].')';
            }
            throw new Exception('Database'.$error_code.' '.$e->getMessage().' in file '.$e->getFile().' on line '.$e->getLine().' (SQL: '.$sql.')',1);
        }

        $this->insert_id = $this->connection->lastInsertId();

        if(!is_null($this->log_sql_table) AND $debug) {
            $this->query_list[] = array('query'=>$sql,'parameters'=>$variables,'time'=>number_format(round(microtime(1)-$time_start,6),6));
            if(!strstr($sql,$this->log_sql_table)) {
                $previous_statement = $this->statement;
                $this->Query("INSERT INTO ".$this->log_sql_table." SET created=NOW(), sql_query=?, sql_query_hash=?, url=?, timer=?",array($sql,md5($sql.URL),URL,number_format(round(microtime(1)- $time_start,6),6)),false);
                $this->statement = $previous_statement;
                unset($previous_statement);
            } else {
                $this->query_count++;
            }
        } else {
            $this->query_count++;
        }
        if($this->debug AND $debug) {
            echo $sql.'<br /><br />';
        }
        return $this->statement;
    }

    /**
    * Execute a query
    * @param string $sql
    * @param array $variables
    * @return resource
    */
    function Execute($sql,$variables = array()) {
        return $this->Query($sql,$variables);
    }

    /**
    * Get a single value from a query
    * @param string $sql
    * @param array $variables
    * @return string
    */
    function GetOne($sql, $variables = array(), $column = 0) {
        return $this->Query($sql,$variables)->fetchColumn($column);
    }

    /**
    * Get an associative array from a query using the first field in the query as the key
    * @param string $sql
    * @param array $variables
    * @return array
    */
    function GetAssoc($sql,$variables=array(),$grouped = false) {
        $result = $this->Query($sql,$variables);
        $num_fields = $result->columnCount();
        $result_array = array();
        $type = ($num_fields > 2 ? PDO::FETCH_ASSOC : PDO::FETCH_NUM);
        while($row = $result->fetch($type)) {
            if($num_fields == 2) {
                $result_array[$row[0]] = $row[1];
            } elseif($num_fields == 1) {
                $result_array[] = $row[1];
            } else {
                if(!$grouped) {
                $result_array[array_shift($row)] = $row;
                } else {
                    $result_array[array_shift($row)][] = $row;
                }
            }
        }
        return $result_array;
    }

    /**
    * Get a single row from a query
    * @param string $sql
    * @param array $variables
    * @return array
    */
    function GetRow($sql,$variables=array()) {
        $statement = $this->Query($sql,$variables);
        $results = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $results;
    }

    /**
    * Get all results in an array
    * @param string $sql
    * @param array $variables
    * @return array
    */
    function GetAll($sql, $variables=array()) {
        return $this->Query($sql,$variables)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
    * Get a column from the results in an array
    * @param string $sql
    * @param array $variables
    * @param boolean $column
    */
    function GetCol($sql, $variables=array(), $column = 0) {
        return $this->Query($sql,$variables)->fetchAll(PDO::FETCH_COLUMN,$column);
    }

    /**
    * Get the insert ID of last insert query
    * @return int
    */
    function Insert_ID() {
        return $this->insert_id;
    }

    /**
    * Get the number fo found rows in last query
    * @return int
    */
    function FoundRows() {
        return $this->GetOne("SELECT FOUND_ROWS()");
    }

    /**
    * Get number of affected rows from last query
    * @return int
    */
    function Affected_Rows() {
        return $this->statement->rowCount();
    }

    /**
    * Check if a table exists
    * @param string $table
    */
    function TableExists($table) {
        return in_array($table,$this->MetaTables());
    }

    /**
    * Rename a table
    * @param string $old
    * @param string $new
    * @param boolean $overwrite
    */
    function RenameTable($old,$new,$overwrite=false) {
        if(!$this->TableExists($old)) {
            return false;
        }
        if($this->TableExists($new)) {
            if($overwrite) {
                $this->DropTable($new);
            } else {
                return false;
            }
        }
        $this->Execute("RENAME TABLE ".$this->CleanIdentifier($old)." TO ".$this->CleanIdentifier($new));
    }

    /**
    * Rename a table column
    * @param string $table
    * @param string $old
    * @param string $new
    * @param boolean $overwrite
    */
    function RenameColumn($table,$old,$new,$overwrite=false) {
        if(!$this->ColumnExists($table,$old)) {
            return false;
        }
        if($this->ColumnExists($table,$new)) {
            if($overwrite) {
                $this->DropColumn($table,$new);
            } else {
                return false;
            }
        }
        $this->Execute($this->RenameColumnSQL($table,$old,$new));
    }

    /**
    * Get the SQL to rename a column
    *
    * @param string $table Table
    * @param string $old Old column name
    * @param string $new New column name
    */
    function RenameColumnSQL($table,$old,$new) {
        $current_column = $this->GetRow("SHOW COLUMNS FROM ".$table." LIKE '".$old."'");
        $query = "ALTER IGNORE TABLE ".$this->CleanIdentifier($table)." CHANGE ".$this->CleanIdentifier($old)." ".$this->CleanIdentifier($new)." ";
        $query .= $current_column['Type'];
        if($current_column['Null'] == 'NO') {
            $query .= " NOT";
        }
        $query .= " NULL ";
        if(!is_null($current_column['Default'])) {
            $query .= ' default \''.$current_column['Default'].'\'';
        }
        $query .= $current_column['Extra'];
        return $query;
    }

    /**
    * Add a column to a table
    * @param string $table
    * @param string $column
    * @param string $type
    * @param int $size
    * @param boolean $unsigned
    * @param boolean $null
    * @param string $after
    * @param mixed $default
    */
    function AddColumn($table, $column, $type, $null, $default = null, $extra = '', $after = null) {
        if($this->ColumnExists($table,$column)) {
            return false;
        }
        return $this->Execute($this->AddColumnSQL($table,$column,$type,$null,$default,$extra,$after));
    }

    /**
    * Get the SQL for adding a column
    * @param string $table
    * @param string $column
    * @param string $type
    * @param boolean $null
    * @param mixed $default
    * @param string $extra
    * @param mixed $after
    */
    function AddColumnSQL($table, $column, $type, $null, $default = null, $extra = '', $after = null) {
        $query = "ALTER TABLE ".$this->CleanIdentifier($table)." ADD ".$this->CleanIdentifier($column)." $type";
        $query .= ' '.$extra;
        if(!$null) {
            $query .= " NOT";
        }
        $query .= " NULL";
        if(!is_null($default)) {
            $query .= ' default \''.$default.'\'';
        }
        if(!is_null($after)) {
            $query .= " AFTER `$after`";
        }
        return $query;
    }

    /**
    * Modify a column
    * @param string $table
    * @param string $column
    * @param string $type
    * @param int $size
    * @param boolean $unsigned
    * @param boolean $null
    * @param string $after
    * @param mixed $default
    */
    function ChangeColumn($table, $column, $type, $null, $default = null, $extra= '', $after = null) {
        if($fulltext = $this->GetRow("SHOW INDEX FROM ".$this->CleanIdentifier($table)." WHERE Column_name='".$column."' AND Index_type='FULLTEXT'")) {
            $fulltext_field_names = $this->GetCol("SHOW INDEX FROM ".$this->CleanIdentifier($table)." WHERE Key_name=?",array($fulltext['Key_name']),4);
            $this->DropIndex($table,$fulltext['Key_name']);
        }

        $return = $this->Execute($this->ChangeColumnSQL($table,$column,$type,$null,$default,$extra,$after));
        if($fulltext) {
            $this->AddIndex($table,$fulltext_field_names,'fulltext',$fulltext['Key_name']);
        }
        return $return;
    }

    /**
    * Change column SQL
    *
    * @param string $table
    * @param string $column
    * @param string $type
    * @param boolean $null
    * @param mixed $default
    * @param string $extra
    * @param mixed $after
    */
    function ChangeColumnSQL($table, $column, $type, $null, $default = null, $extra= '', $after = null) {
        $query = "ALTER TABLE ".$this->CleanIdentifier($table)." CHANGE ".$this->CleanIdentifier($column)." ".$this->CleanIdentifier($column)." $type";
        if(!$null) {
            $query .= " NOT";
        }
        $query .= " NULL ".$extra;
        $no_default = array(
            'text',
            'mediumtext',
            'blob',
            'longblob'
        );
        if(!is_null($default)) {
            $query .= ' default \''.$default.'\'';
        } elseif(!in_array($type,$no_default)) {
            $this->Execute("ALTER TABLE ".$this->CleanIdentifier($table)." ALTER ".$this->CleanIdentifier($column)." DROP DEFAULT");
        }
        if(!is_null($after)) {
            $query .= " AFTER `$after`";
        }
        return $query;
    }

    /**
    * Drop a table
    * @param string $table
    */
    function DropTable($table) {
        $this->Execute("DROP TABLE IF EXISTS ".$table);
    }

    /**
    * Drop a column from a table
    * @param string $table
    * @param string $column
    */
    function DropColumn($table, $columns) {
        if(!$this->TableExists($table)) {
            return false;
        }
        if(!is_array($columns)) {
            $columns = array($columns);
        }
        foreach($columns AS $column) {
            if($this->ColumnExists($table, $column)) {
                $this->Execute("ALTER TABLE `$table` DROP `$column`");
            }
        }
    }

    /**
    * Check if a table column exists
    * @param string $table
    * @param string $column
    */
    function ColumnExists($table,$column) {
        return in_array($column,$this->MetaColumnNames($table));
    }

    /**
    * Add an index to a table
    * @param string $table
    * @param string $columns
    * @param string $type
    * @param string $name
    */
    function AddIndex($table, $columns, $type = 'INDEX', $name = NULL) {
        if(!is_array($columns)) {
            $columns = array($columns);
        }
        if(is_null($name)) {
            $name = rtrim(substr(implode('_',$columns),0,64),'_');
        }
        $query = '';
        if($this->IndexExists($table,$name)) {
            $query = 'DROP INDEX `'.$name.'`,';
        }
        if($name != 'PRIMARY') {
            $name = $this->CleanIdentifier($name);
        } else {
            $name = '';
        }
        return $this->Execute("ALTER IGNORE TABLE ".$this->CleanIdentifier($table)." $query ADD $type $name (`".implode('`,`',$columns)."`)");
    }

    /**
    * Check if an index exists in a table
    * @param string $table
    * @param string $index
    */
    function IndexExists($table,$index) {
        $key = $this->GetAll("SHOW INDEX FROM ".$table." WHERE Key_name=?",array($index));
        if($key) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Drop a table index
    * @param string $table
    * @param string $index
    */
    function DropIndex($table,$index) {
        if(!$this->TableExists($table)) {
            return false;
        }
        if(!$this->IndexExists($table,$index)) {
            return false;
        }
        $this->Execute("ALTER TABLE ".$this->CleanIdentifier($table)." DROP INDEX ".$this->CleanIdentifier($index));
    }

    /**
    * Get column names from a table
    * @param string $table
    * @return array
    */
    function MetaColumnNames($table) {
        return $this->GetCol("SHOW COLUMNS FROM ".$table);
    }

    /**
    * Get a list of tables
    * @return array
    */
    function MetaTables() {
        return $this->GetCol("SHOW TABLES");
    }

    /**
    * Get primary key from a table
    * @param string $table
    * @return array
    */
    function MetaPrimaryKeys($table) {
        $primary_keys = array();
        $fields = $this->GetAll("SHOW COLUMNS FROM ".$this->Clean($table));
        foreach($fields as $field) {
            if($field['Key'] == 'PRI') {
                $primary_keys[] = $field['Field'];
            }
        }
        return $primary_keys;
    }

    /**
    * Get keys from a table
    * @param string $table
    * @return array
    */
    function MetaKeys($table) {
        $keys = array();
        $fields = $this->GetAll("SHOW COLUMNS FROM ".$this->Clean($table));
        foreach($fields as $field) {
            if($field['Key'] != '') {
                $keys[] = $field['Field'];
            }
        }
        return $keys;
    }

    /**
    * Get a formatted error message
    * @param string $sql
    */
    function getErrorMessage() {
        $message = '<b>Database error</b>.<br /><br />';
        $message .= '<b>Query:</b><br />'.htmlspecialchars($this->statement->queryString,ENT_QUOTES).'<br /><br />';
        $error = $this->statement->errorInfo();
        $message .= '<b>Error:</b><br /> ('.$error[1].') '.$error[2].'<br /><br />';
        return $message;
    }

    /**
    * Convert all tables to an array structure
    * @param string $table_prefix
    */
    function convertToArray($table_prefix = null, $tables = array()) {
        $data = array();
        if(count($tables)) {
            foreach($tables AS &$table) {
                $table = $table_prefix.$table;
            }
            $tables = "'".implode("','",$tables)."'";
            $tables = $this->GetCol("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema=? AND TABLE_NAME IN (".$tables.")",array(DB_NAME));
        } elseif(!is_null($table_prefix)) {
            $tables = $this->GetCol("SHOW TABLES RLIKE '".$table_prefix.".*'");
        } else {
            $tables = $this->GetCol("SHOW TABLES");
        }
        foreach($tables AS $table) {
            $table_data = $this->GetAll("DESCRIBE ".$table);
            $table_key_data = $this->GetAll("SHOW INDEX FROM ".$table);
            $table_name = preg_replace('/^'.$table_prefix.'/','',$table,1);
            foreach($table_data AS $field) {
                $data[$table_name]['fields'][$field['Field']] = array(
                    'type'=>$field['Type'],
                    'null'=>($field['Null'] == 'YES' ? true : false),
                    'extra'=>$field['Extra']
                );
                if($field['Null'] == 'NO') {
                    if($field['Default'] === NULL) {
                        $data[$table_name]['fields'][$field['Field']]['default'] = NULL;
                    } elseif($field['Default'] === '') {
                        $data[$table_name]['fields'][$field['Field']]['default'] = '';
                    } else {
                        $data[$table_name]['fields'][$field['Field']]['default'] = $field['Default'];
                    }
                } else {
                    if(empty($field['Default'])) {
                        $data[$table_name]['fields'][$field['Field']]['default'] = NULL;
                    } else {
                        $data[$table_name]['fields'][$field['Field']]['default'] = $field['Default'];
                    }
                }
            }
            foreach($table_key_data AS $key) {
                if(isset($data[$table_name]['keys'][$key['Key_name']])) {
                    array_push($data[$table_name]['keys'][$key['Key_name']]['fields'],$key['Column_name']);
                } else {
                    $data[$table_name]['keys'][$key['Key_name']]['fields'] = array($key['Column_name']);
                }
                if($key['Key_name'] == 'PRIMARY') {
                    $data[$table_name]['keys'][$key['Key_name']]['type'] = 'PRIMARY KEY';
                } elseif($key['Index_type'] == 'BTREE') {
                    if($key['Non_unique'] == 0) {
                        $data[$table_name]['keys'][$key['Key_name']]['type'] = 'UNIQUE';
                    } else {
                        $data[$table_name]['keys'][$key['Key_name']]['type'] = 'KEY';
                    }
                } elseif($key['Index_type'] == 'FULLTEXT') {
                    $data[$table_name]['keys'][$key['Key_name']]['type'] = 'FULLTEXT';
                }
            }
            $data[$table_name]['character_set'] = $this->getTableCharacterSet($table);
            $data[$table_name]['engine'] = $this->getTableType($table);
        }
        return $data;
    }

    /**
    * Create a temporary table of an existing table
    * @param mixed $name New table name
    * @param mixed $existing Existing table name
    */
    function createTemporaryTableCopy($name, $existing, $viewable = false) {
        $statement = $this->Query("CREATE ".($viewable ? "" : "TEMPORARY")." TABLE IF NOT EXISTS ".$this->CleanIdentifier($name)." LIKE ".$this->CleanIdentifier($existing).";");
        $statement->closeCursor();
    }

    /**
    * Get the character set of a table
    * @param table $table Table name
    */
    function getTableCharacterSet($table) {
        $create_table = $this->GetRow("SHOW CREATE TABLE ".$this->CleanIdentifier($table));
        preg_match('/DEFAULT CHARSET=(.+)/',$create_table['Create Table'],$matches);
        if(!$matches) {
            preg_match('/CHARACTER SET (.+)/',$create_table['Create Table'],$matches);
        }
        return $matches[1];
    }

    /**
    * Get a table type
    * @param table $table Table name
    */
    function getTableType($table) {
        $create_table = $this->GetRow("SHOW CREATE TABLE ".$this->CleanIdentifier($table));
        preg_match('/ENGINE=([^\s]+)\s/',$create_table['Create Table'],$matches);
        return $matches[1];
    }

    /**
    * Get database server version
    * @return string Version
    */
    function version() {
        return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
    * Check if the database can be synced
    * @return boolean true or false
    */
    function canSync() {
        return file_exists(PMDROOT.'/install/database/structure.php');
    }

    /**
    * Sync the database
    * @return array Messages from the sync
    */
    function sync() {
        include(PMDROOT.'/install/includes/functions.php');
        include(PMDROOT.'/install/includes/functions_upgrade.php');
        $queue = generateStructureDifference();
        $queue = array_merge($queue,generateQueueFunctions());
        $messages = array();
        foreach($queue AS $queue_item) {
            $messages[] = processQueueItem($queue_item);
        }
        return $messages;
    }

    /**
    * Close database connection
    */
    function Close() {
        $this->statement = NULL;
        $this->connection = NULL;
    }

    /**
    * Database destructor
    * Closes database connection
    */
    function __destruct() {
        $this->Close();
    }
}
?>