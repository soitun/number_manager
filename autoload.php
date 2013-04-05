<?php 

/**
 * Auto-loader
 * @author Francis Genet
 * @package Number_manager
 */
class NumberManagerAutoload {
    /**
     * Setup anything required to make our provisioner class work
     */
    public static function setup() {
        // Register auto-loader. When classes are requested that aren't loaded
        spl_autoload_register(array(
            'NumberManagerAutoload',
            'autoload'
        ));
    }

    public static function autoload($class) {       
        // If for some reason we get here and the class is already loaded, return
        if (class_exists($class, FALSE)) {
            return TRUE;
        }

        // Try to include the class
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        if (is_file(PROVISIONER_BASE . $file)) {
            require_once(PROVISIONER_BASE . $file);

            return TRUE;
        }
        
        return FALSE;
    }
}

NumberManagerAutoload::setup();