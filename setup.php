<?php

/**
 * Deployment tool for the number manager
 *
 * @author Francis Genet
 * @package Number_manager
 */

require_once 'bootstrap.php';

$time_start = microtime(true);
$country = 'US';
$area_code_path = 'area_code_US.txt';
$csv_list_path = 'locations.csv';

scripts_utilsdb::create_locations_tables($country, $area_code_path);
scripts_utilsdb::insert_locations($country, $csv_list_path);

scripts_utilsdb::create_city_map($country, $csv_list_path);

$name = 'United States';
$iso_code = 'US';
$local = '1';
$toll_free = '800 888 877 855 866';
$vanity = '1';
$prefix = '1';

scripts_utilsdb::add_country($name, $iso_code, $local, $toll_free, $vanity, $prefix);

scripts_utilsdb::create_numbers_dbs($country, $area_code_path);

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "Execution time: $time seconds\n";