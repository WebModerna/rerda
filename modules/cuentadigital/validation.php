<?php
/***************************************************************************************
// Copyright (c) 1995 - 2009 FOSI TEAM http://www.goldenfoxi.com.ar
// ***GoldenFoxi Todos Los Derechos reservados*** fosi_team@hotmail.com
 ***************************************************************************************/
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cuentadigital.php');

$currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
$mailVars = array(
	'{cuentadigital_iddigital}' => nl2br(Configuration::get('NUMERO_ID_DIGITAL')),);

$iddigital= new CuentaDigital();
$iddigital->validateOrder($cart->id, _PS_OS_CUENTADIGITAL_, $total, $iddigital->displayName, NULL, $mailVars, $currency->id);
$order = new Order($iddigital->currentOrder);
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$iddigital->id.'&id_order='.$iddigital->currentOrder.'&key='.$order->secure_key);
?>