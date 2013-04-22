<?php

/**
 * This is the entry point for the APIs
 *
 * @author Francis Genet
 * @package Number_manager_api
 * @version 1.0
 */

// Bootstrap
require_once 'bootstrap.php';

require_once 'lib/restler/restler.php';

use Luracast\Restler\Restler;

// CORS
header('Access-Control-Allow-Headers:Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, X-Auth-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Max-Age:86400');

$r = new Restler();
$r->setSupportedFormats('JsonFormat');
$r->addAPIClass('numbers');
$r->addAPIClass('blocks');
//$r->addAuthenticationClass('AccessControl');
$r->handle();

?>
