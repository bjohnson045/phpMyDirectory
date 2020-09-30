<?php
/**
* Class CLI
*/
class CLI {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * CLI Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Get a command name from the arguments
    * @return string Command name
    */
    function getCommand() {
        if(count($_SERVER['argv']) < 2) {
            $this->writeLine("Command not specified");
            return false;
        }
        if(!file_exists(PMDROOT_ADMIN.'/cli/'.$_SERVER['argv'][1].'.php')) {
            $this->writeLine('Command "'.$_SERVER['argv'][1].'" not found');
            return false;
        }
        return $_SERVER['argv'][1];
    }

    /**
    * Write text
    * @param string $text
    * @param string $stream
    */
    function write($text, $stream=STDOUT) {
        fwrite($stream, $text);
    }

    /**
    * Write a line of text including a newline
    * @param string $text
    * @param string $stream
    */
    function writeLine($text, $stream=STDOUT) {
        $this->write($text.PHP_EOL, $stream);
    }

    /**
    * Process the command parameters
    * @param array $default_options
    * @return array
    */
    function getParams(array $default_options) {
        $options = array();
        $invalid = array();

        if(empty($_SERVER['argv'])) {
            return array($options, $invalid);
        }

        $args = $_SERVER['argv'];

        unset($args[0],$args[1]);

        foreach($args as $arg) {
            if(substr($arg,0,2) === '--') {
                $value = substr($arg, 2);
                $parts = explode('=', $value);
                $key   = $parts[0];
                if(count($parts) == 1) {
                    $value = true;
                } else {
                    $value = $parts[1];
                }
                if(isset($default_options[$key])) {
                    $options[$key] = $value;
                } else {
                    $invalid[] = $arg;
                }
            }
        }

        $options = array_merge($default_options,$options);
        return array($options, $invalid);
    }

    /**
    * Send an error
    * @param string $text
    * @param int $code
    * @return void
    */
    function error($text, $code=0) {
        $this->writeLine($text.PHP_EOL,STDERR);
        exit($code);
    }
}
?>