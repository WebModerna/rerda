<?php
/**
* Librerias de MercadoPago
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(_PS_ROOT_DIR_.'/init.php');
include_once(dirname(__FILE__).'/mpar.php');
if (!class_exists('MpMutex')) {
    include_once(dirname(__FILE__).'/lib/mpmutex.php');
}

$mp = new Mpar();
$mp_class_name = 'Mpar';

if (empty(Mpar::$mp_client_id) || empty(Mpar::$mp_client_secret)) {
    die('Module not install');
}
