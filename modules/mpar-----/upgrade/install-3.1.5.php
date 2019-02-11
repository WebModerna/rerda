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

function upgrade_module_3_1_5($mp)
{
    if (!$mp || $mp->name != Mpar::MP_NAME) {
        $mp = new Mpar();
    }
    $mp->log('upgrade_module_3_1_5 init...');
    Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Mpar::MP_NAME.'_refunds` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_order` INT(11) NOT NULL,
                `mp_op_id` varchar(100) NOT NULL,
                `date_create` DATETIME NOT NULL,
                `response` TEXT NOT NULL,
                INDEX(id_order),
                INDEX(mp_op_id)
                )');
    $mp->registerHook('updateOrderStatus');
    $status_refound = array();
    $status_refound[] = (int)Configuration::get('PS_OS_CANCELED');
    $status_refound[] = (int)Configuration::get('PS_OS_REFUND');
    $status_refound[] = (int)Configuration::get('PS_OS_ERROR');
    Configuration::updateValue(Mpar::MP_PREFIX.'STATUS_REFOUND', Tools::jsonEncode($status_refound));
    if (version_compare(_PS_VERSION_, '1.5.0.9') >= 0) {
        $shops = Shop::getContextListShopID();
        $shop_groups_list = array();

        /* Setup each shop */
        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }
            /* Sets up configuration */
            Configuration::updateValue(
                Mpar::MP_PREFIX.'STATUS_REFOUND',
                Tools::jsonEncode($status_refound),
                false,
                $shop_group_id,
                $shop_id
            );
        }

        /* Sets up Shop Group configuration */
        if (count($shop_groups_list)) {
            foreach ($shop_groups_list as $shop_group_id) {
                Configuration::updateValue(
                    Mpar::MP_PREFIX.'STATUS_REFOUND',
                    Tools::jsonEncode($status_refound),
                    false,
                    $shop_group_id
                );
            }
        }
    }
    $mp->log('upgrade_module_3_1_5 end!');
    return true;
}
