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

function upgrade_module_3_1_4($mp)
{
    if (!$mp || $mp->name != Mpar::MP_NAME) {
        $mp = new Mpar();
    }
    $mp->log('upgrade_module_3_1_4 init...');
    if (version_compare(_PS_VERSION_, '1.5.0.0') >= 0) {
        if (!$mp->checkOverride()) {
            return false;
        }
    }
    try {
        Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.Mpar::MP_NAME.'` 
                ADD `topic` VARCHAR(100) AFTER `id_shop`');
    } catch (PrestaShopDatabaseException $e) {
        //Se ignora...
    }
    Mpar::installCarriers();
    $id_currency = Currency::getIdByIsoCode(Mpar::MP_CURRENCY_SHIPPING);
    $id_country = Country::getByIso(Mpar::MP_ISO_COUNTRY);
    if (version_compare(_PS_VERSION_, '1.5.0.0') >= 0) {
        $actual_context = Shop::getContext();
        Shop::setContext(Shop::CONTEXT_ALL);
        $mp->registerHook('displayBeforeCarrier');
        $mp->registerHook('displayBackOfficeHeader');
        $shops = Shop::getContextListShopID();
        foreach ($shops as $shop_id) {
            Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_country` 
                (id_module, id_shop, id_country) VALUES
                ('.(int)$mp->id.', '.(int)$shop_id.', '.(int)$id_country.')');
            Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` 
                (id_module, id_shop, id_currency) VALUES
                ('.(int)$mp->id.', '.(int)$shop_id.', '.(int)$id_currency.')');
        }
        Shop::setContext($actual_context);
    } else {
        $mp->registerHook('beforeCarrier');
        $mp->registerHook('backOfficeHeader');
        Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_country` 
            (id_module, id_country) VALUES
            ('.(int)$mp->id.', '.(int)$id_country.')');
        Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` 
            (id_module, id_currency) VALUES
            ('.(int)$mp->id.', '.(int)$id_currency.')');
    }
    $mp->log('upgrade_module_3_1_4 end!');
    return true;
}
