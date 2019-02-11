<?php
/**
* Upgrade de MercadoPago
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_9($mp)
{
    if (!$mp || $mp->name != Mpar::MP_NAME) {
        $mp = new Mpar();
    }
    $mp->log('upgrade_module_3_1_9 init...');
    $order_state = new OrderState((int)Configuration::get(Mpar::MP_PREFIX.'OS_AUTHORIZATION'));
    $order_state->delivery = false;
    if (version_compare(_PS_VERSION_, '1.5.0.1') > 0) {
        $order_state->paid = true;
    }
    $order_state->save();
    if (version_compare(_PS_VERSION_, '1.5.0.0') >= 0) {
        if (!$mp->checkOverride()) {
            return false;
        }
    }
    $mp->log('upgrade_module_3_1_9 end!');
    return true;
}
