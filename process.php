<?php

/**
 * Entry point for the number manager
 *
 * @author Francis Genet
 * @package Number_manager
 */

$argv[0] = "process.php";
$argv[1] = "utils";
$argv[2] = "insert_locations";
$argv[3] = "US";
$argv[4] = "npaa.csv";

require_once 'bootstrap.php';

$time_start = microtime(true);

$command = $argv[1];

// Just making sure that everything is where it should be
if(!isset($command)) {
    die("Usage: php process.php <database / utils> [<args>]\n");
}

switch ($command) {
    case 'database':
        $arg = $argv[2];
        $file = $argv[3];

        if (!isset($arg))
            die("Argument missing\n");

        if (!file_exists($file))
            die("Could not find the file\n");

        $db_obj = new scripts_database($file);

        switch ($arg) {
            case 'create':
                echo "Generating main database... \n";
                $db_obj->create();
                break;
                
            case 'update':
                echo "Updating main database... \n";
                $db_obj->update();
                break;

            case 'create_tollfree':
                echo "Generating tollfree database... \n";
                $db_obj->create_tollfree();
                break;

            case 'update_tollfree':
                echo "Updating tollfree database... \n";
                $db_obj->update_tollfree();
                break;

            default:
                echo("ERROR: Wrong argument\n");
                echo("Available args: create(_tollfree) / update(_tollfree)\n");
                break;
        }
        break;

    case 'utils':
        if (!isset($argv[2]))
            die("Argument missing\n");

        switch ($argv[2]) {
            case 'list':
                $list = scripts_utilsdb::get_table_list();
                foreach ($list as $table) {
                    echo $table . "\n";
                }
                break;

            case 'create_locations_tables':
                $country = $argv[3];
                $area_code_path = $argv[4];
                scripts_utilsdb::create_locations_tables($country, $area_code_path);
                break;

            case 'insert_locations':
                $country = $argv[3];
                $csv_list_path = $argv[4];
                scripts_utilsdb::insert_locations($country, $csv_list_path);
                break;

            case 'truncate':
                scripts_utilsdb::truncate($argv[3]);
                break;

            case 'add_country':
                $name = $argv[3];
                $iso_code = $argv[4];
                $local = $argv[5];
                $toll_free = $argv[6];
                $vanity = $argv[7];
                $prefix = $argv[8];
                scripts_utilsdb::add_country($name, $iso_code, $local, $toll_free, $vanity, $prefix);
                break;
            
            default:
                echo("ERROR: Wrong argument\n");
                echo("Available args: list / truncate\n");
                break;
        }
        break;

    default:
        echo("ERROR: Wrong command\n");
        echo("Available commands: sync_db / sync_city\n");
        break;
}

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "Execution time: $time seconds\n";