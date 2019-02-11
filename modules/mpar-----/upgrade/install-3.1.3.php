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

function upgrade_module_3_1_3($mp)
{
    if (!$mp || $mp->name != Mpar::MP_NAME) {
        $mp = new Mpar();
    }
    $mp->log('upgrade_module_3_1_3 init...');

    if (!isset(Mpar::$mp_style['b_color']) || empty(Mpar::$mp_style['b_color'])) {
        Mpar::$mp_style['b_color'] = 'lightblue';
        Mpar::$mp_style['b_shape'] = 'Ov';
        Mpar::$mp_style['b_size'] = 'M';
        Mpar::$mp_style['b_font'] = 'Ar';
        Mpar::$mp_style['b_logo'] = 'On';
        Configuration::updateValue(Mpar::MP_PREFIX.'STYLE', Tools::jsonEncode(Mpar::$mp_style));
    }

    if (!(bool)Configuration::get(Mpar::MP_PREFIX.'BTRANSFER')) {
        Mpar::$mp_payments['bank_transfer'] = 1;
        Mpar::$mp_payments['atm'] = 1;
        Mpar::$mp_payments['ticket'] = 1;
        Configuration::updateValue(Mpar::MP_PREFIX.'PAYMENTS', Tools::jsonEncode(Mpar::$mp_payments));
    }
    Configuration::deleteByName(Mpar::MP_PREFIX.'BTRANSFER');

    if (version_compare(_PS_VERSION_, '1.5.0.0') >= 0) {
        $mp->registerHook('displayAdminOrder');
        if (version_compare(_PS_VERSION_, '1.5.0.9') >= 0) {
            $shops = Shop::getContextListShopID();
            $shop_groups_list = array();

            /* Sets up Global configuration */
            $id_os_auth = (int)Configuration::get(Mpar::MP_PREFIX.'OS_AUTHORIZATION');
            $id_os_pending = (int)Configuration::get(Mpar::MP_PREFIX.'OS_PENDING');
            $id_os_refused = (int)Configuration::get(Mpar::MP_PREFIX.'OS_REFUSED');
            $mp_sandbox_active = (int)Configuration::get(Mpar::MP_PREFIX.'SANDBOX');
            $mp_modal_active = (int)Configuration::get(Mpar::MP_PREFIX.'MODAL');
            $mp_client_id = Configuration::get(Mpar::MP_PREFIX.'CLIENT_ID');
            $mp_client_secret = Configuration::get(Mpar::MP_PREFIX.'CLIENT_SECRET');
            $mp_fee = (float)Configuration::get(Mpar::MP_PREFIX.'FEE');
            $mp_style = Configuration::get(Mpar::MP_PREFIX.'STYLE');
            $mp_payments = Configuration::get(Mpar::MP_PREFIX.'PAYMENTS');
            $mp_validation_path = Configuration::get(Mpar::MP_PREFIX.'VALIDATION');
            $currency_convert = Configuration::get('currency_convert');

            /* Setup each shop */
            foreach ($shops as $shop_id) {
                $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                /* Sets up configuration */
                Configuration::updateValue(Mpar::MP_PREFIX.'OS_AUTHORIZATION', $id_os_auth, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'OS_PENDING', $id_os_pending, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'OS_REFUSED', $id_os_refused, false, $shop_group_id, $shop_id);

                Configuration::updateValue(Mpar::MP_PREFIX.'SANDBOX', $mp_sandbox_active, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'MODAL', $mp_modal_active, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'CLIENT_ID', $mp_client_id, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'CLIENT_SECRET', $mp_client_secret, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'FEE', $mp_fee, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'STYLE', $mp_style, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'PAYMENTS', $mp_payments, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'VALIDATION', $mp_validation_path, false, $shop_group_id, $shop_id);
                Configuration::updateValue(Mpar::MP_PREFIX.'currency_convert', $currency_convert, false, $shop_group_id, $shop_id);
            }

            /* Sets up Shop Group configuration */
            if (count($shop_groups_list)) {
                foreach ($shop_groups_list as $shop_group_id) {
                    Configuration::updateValue(Mpar::MP_PREFIX.'OS_AUTHORIZATION', $id_os_auth, false, $shop_group_id);
                    Configuration::updateValue(Mpar::MP_PREFIX.'OS_PENDING', $id_os_pending, false, $shop_group_id);
                    Configuration::updateValue(Mpar::MP_PREFIX.'OS_REFUSED', $id_os_refused, false, $shop_group_id);
                    Configuration::updateValue(Mpar::MP_PREFIX.'VALIDATION', $mp_validation_path, false, $shop_group_id);
                    Configuration::updateValue(Mpar::MP_PREFIX.'currency_convert', $currency_convert, false, $shop_group_id);
                }
            }
        }
    } else {
        $mp->registerHook('adminOrder');
    }

    if (version_compare(_PS_VERSION_, '1.6.0.0') >= 0) {
        $hook_dashboard = Hook::getModulesFromHook(Hook::getIdByName('dashboardZoneOne'), $mp->id);
        if (!$hook_dashboard || is_array($hook_dashboard) && count($hook_dashboard) <= 0) {
            $mp->registerHook('dashboardZoneOne');
            $mp->registerHook('dashboardData');
        }
    }
    $mp->log('upgrade_module_3_1_3 end!');
    return true;
}
