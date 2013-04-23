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
}

 ?>