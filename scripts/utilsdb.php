<?php 

class scripts_utilsdb {
    private static function _get_db_instance() {
        $settings = helper_settings::get_instance();
        // Set the DSN (the string that determines what driver to user and how)
        $dsn = "mysql:host=" . $settings->database->database_host . ";dbname=" . $settings->database->database_name . ";charset=" . $settings->database->database_charset;        
        // Set the driver parameters
        $drvr_params = array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        // Creating a connexion

        try {
            return new PDO($dsn, $settings->database->database_user, $settings->database->database_password, $drvr_params);
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
        }
    }

    private static function _count($db_name) {
        try {
            $db = scripts_utilsdb::_get_db_instance();
            $stmt = $db->query("SELECT id FROM `". $db_name . "`");
            return $stmt->rowCount();
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return 0;
        }
    }

    public static function get_table_list() {
        $settings = helper_settings::get_instance();
        $db = scripts_utilsdb::_get_db_instance();

        $stmt = $db->query("SHOW TABLES");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arr_result = array();
        foreach ($results as $row) {
            array_push($arr_result, $row['Tables_in_' . $settings->database->database_name]);
        }

        return $arr_result;
    }

    public static function truncate($area_code = null) {
        $db = scripts_utilsdb::_get_db_instance();

        foreach (scripts_utilsdb::get_table_list() as $table) {
            try {
                $db->query("TRUNCATE TABLE $table");
            } catch (PDOException $e) {
                echo $e->getMessage() . "\n";
            }
        }
    }

    public static function create_locations_tables($country, $area_code_path){
        echo "Loading $area_code_path...\n";
        $lines = file($area_code_path);
        foreach ($lines as $line_number => $row) {
            $lines[$line_number] = trim($row);
            $location_obj = new models_location(); 
            $location_obj->create_table($country, $lines[$line_number]);
        }

        return true;
    }

    public static function create_city_map($country, $csv_list_paths) {
        echo "Loading $csv_list_paths...\n";
        $file_handle = fopen($csv_list_paths, "r");

        $mastercitymap_obj = new models_citymap($country);
        $mastercitymap_obj->create_table();

        while (($data = fgetcsv($file_handle)) !== FALSE) {
            $citymap_obj = new models_citymap($country);
            $citymap_obj->set_city($data[4]);
            $citymap_obj->set_npa($data[0]);
            $citymap_obj->set_state($data[3]);
            $citymap_obj->insert();
        }

        // Closing the file
        echo "Closing $csv_list_paths...\n";
        fclose($file_handle);
        return true;
    }

    public static function insert_locations($country, $csv_list_paths){
        echo "Loading $csv_list_paths...\n";
        $file_handle = fopen($csv_list_paths, "r");
        while (($data = fgetcsv($file_handle)) !== FALSE) {
            echo "Inserting " . $data[0].$data[1] . "(" . $data[7] . ")...\n";
            $location_obj = new models_location($country, $data[0]);
            $location_obj->set_npanxx($data[0].$data[1]);
            $location_obj->set_company($data[2]);
            $location_obj->set_state($data[3]);
            $location_obj->set_city($data[4]);
            $location_obj->set_county($data[5]);
            $location_obj->set_zipcode($data[6]);
            $location_obj->set_rate_center($data[7]);
            $location_obj->insert();
        }

        // Closing the file
        echo "Closing $csv_list_paths...\n";
        fclose($file_handle);
        return true;
    }

    public static function add_country($name, $iso_code, $local, $toll_free, $vanity, $prefix) {
        $db = scripts_utilsdb::_get_db_instance();
        $settings = helper_settings::get_instance();

        $country = new models_country();
        $country->set_iso_code($iso_code);
        $country->set_local($local);
        $country->set_toll_free($toll_free);
        $country->set_vanity($vanity);
        $country->set_prefix($prefix);
        $country->set_name($name);
        $country->set_flag_url($settings->flags_url . strtoupper($iso_code) . ".png");
        $country->insert();
    }

    public static function count_numbers($country, $area_code_path) {
        $count = 0;
        echo "Loading $area_code_path...\n";
        $lines = file($area_code_path);
        foreach ($lines as $row) {
            $count = $count + scripts_utilsdb::_count($country . '_' . trim($row));
        }

        return $count;
    }

    public static function create_numbers_dbs($country, $area_code_path) {
        echo "Loading $area_code_path...\n";
        $lines = file($area_code_path);
        foreach ($lines as $row) {
            $number_obj = new models_number();
            $number_obj->create_db($country . '_' . trim($row));
        }
    }
}

 ?>