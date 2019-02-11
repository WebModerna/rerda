<?php
/***************************************************************************************
// Copyright (c) 1995 - 2009 FOSI TEAM http://www.goldenfoxi.com.ar
// ***GoldenFoxi Todos Los Derechos reservados*** fosi_team@hotmail.com
 ***************************************************************************************/
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cuentadigital.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
$iddigital = new CuentaDigital();
echo $iddigital->execPayment($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>