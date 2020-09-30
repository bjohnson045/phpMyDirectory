<?php
include(realpath(__DIR__.'/../../defaults.php'));

if(!defined('CONSOLE') OR !CONSOLE) {
    exit('Access denied.');
}

// Get the command name, i.e. "database_sync"
$command_name = $PMDR->get('CLI')->getCommand();

// If a valid command name exists, try to load it
if($command_name) {
    // Include the file name corresponding to the command name
    include(PMDROOT_ADMIN.'/cli/'.$command_name.'.php');
    // Make sure the class name matches the command name and file name
    if(!class_exists($command_name)) {
        $PMDR->get('CLI')->writeLine('Command "'.$command_name.'" not properly defined');
        return false;
    // Make sure the class contains a "run" method
    } elseif(!method_exists($command_name,'run')) {
        $PMDR->get('CLI')->writeLine('Command "'.$command_name.'" run method not properly defined');
    } else {
        try {
            // Try to initialize the command.  This could fail it invalid options are passed or --help is passed.
            $command = new $command_name($PMDR);
            // Run the command
            if($command->run()) {
                $PMDR->get('CLI')->writeLine('Command completed.');
            }
        } catch (Exception $e) {
            // The command did not run due to --help or invalid commands.
        }
    }
}
exit(0);
?>