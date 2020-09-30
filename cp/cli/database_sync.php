<?php
class Database_Sync extends CLI_Command {
    /**
    * CLI object
    * @var Object CLI
    */
    var $cli;
    /**
    * The default options for this command
    * @var array
    */
    var $options = array();

    /**
    * Return the help text for this command
    * @return string
    */
    function help() {
        return "
Database Sync

Sync the current database with new structure, settings, language phrases, email templates, and permissions.

Usage:
  database_sync
  database_sync -- help

Options:
  --help    Show this screen.
";
    }

    /**
    * Run a database sync
    * @return boolean true on success
    */
    function run() {
        if(!$this->db->canSync()) {
            $this->cli->error('Unable to sync, /install/ does not exist.');
        } else {
            $messages = $this->db->sync();
            foreach($messages AS $message) {
                $this->cli->writeLine($message);
            }
            $this->cli->writeLine('Sync complete');
        }
        return true;
    }
}
?>
