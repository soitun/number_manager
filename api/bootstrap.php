<?php 

/**
 * Bootstrap
 * @author Francis Genet
 * @package Number_manager_api
 */

// Define ENV constants
// "dev" or "prod"
define("ENVIRONMENT", "dev");

// Define default directories
define("ROOT_PATH", dirname(__FILE__) . '/');
define("LOGS_PATH", ROOT_PATH . 'logs/');

// Include our auto-loader
require_once(ROOT_PATH . 'autoload.php');