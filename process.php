<?php

/**
 * Entry point for the number manager
 *
 * @author Francis Genet
 * @package Number_manager
 */

require_once 'bootstrap.php';

// Just making sure that everything is where it should be
if(!isset($argv[1]) || !isset($argv[2])) {
    die("Usage: php process.php <command> <area_code_file_path>\n");
}

$command = $argv[1];
$farea_code = $argv[2];

if(!file_exists($farea_code))
    die("Could not find the file\n");

switch ($command) {
    case 'sync_db':
        $sync_obj = new scripts_syncdb($farea_code);
        $sync_obj->sync();
        break;

    case 'sync_city':
        $sync_obj = new scripts_synccity();
        $sync_obj->sync();

    default:
        echo("ERROR: Wrong command\n");
        echo("Available commands: sync_db / sync_city\n");
}