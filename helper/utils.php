<?php 

class Utils {
    private static function load_provider_classes($provider_name_list) {
        foreach ($provider_name_list as $provider_name) {
            require_once ROOT_PATH . 'providers/' . $provider_name . '/' . $provider_name . '.php';
        }
    }

    private static function load_provider_objects($provider_name_list) {
        $provider_obj_list = array();

        foreach ($provider_name_list as $provider_name) {
            $class_name = ucfirst($provider_name);
            $new_obj = new $class_name();
            array_push($provider_obj_list, $new_obj);
        }

        return $provider_obj_list;
    }

    public static function get_provider_list() {
        $provider_name_list = array();

        $path = ROOT_PATH . 'providers/';
        foreach (new DirectoryIterator($path) as $file) {
            if($file->isDot()) continue;

            if($file->isDir()) {
                $dir_name = $file->getFilename();
                array_push($provider_name_list, $dir_name);
            }
        }

        Utils::load_provider_classes($provider_name_list);
        return Utils::load_provider_objects($provider_name_list);
    }
}

?>