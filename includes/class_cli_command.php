<?php
/**
* Class CLI
*/
class CLI_Command {
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
        $this->cli = $PMDR->get('CLI');
        $this->options['help'] = false;
        if(!$this->parseOptions()) {
            throw new Exception('Command not run');
        }
    }

    /**
    * Parse the command options
    * @return boolean true or false on successful parsing
    */
    function parseOptions() {
        $options = $this->cli->getParams($this->options);
        if($options[0]['help']) {
            $this->cli->writeLine($this->help());
            return false;
        } elseif(!empty($options[1])) {
            $this->cli->writeLine('Invalid options, see --help');
            return false;
        }
        return true;
    }
}
?>