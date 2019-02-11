<?php
/**
* Modulo MercadoPago Pro
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/lib/mercadopagoar.php';

class Mpar extends PaymentModule
{
    const COUNTRY_NAME = 'Argentina';
    const REGISTER_URL = 'https://www.mercadopago.com/mla/registration';
    const SECRET_URL = 'https://www.mercadopago.com/mla/herramientas/aplicaciones';
    const IPN_URL = 'https://www.mercadopago.com/mla/herramientas/notificaciones';
    const MPENVIOS_REGISTER_URL = 'http://envios.mercadolibre.com.ar/optin/doOptin';
    const MP_PREFIX = 'MPAR_';
    const MP_NAME = 'mpar';
    const MP_SHIPPING_COUNTRY = 'MLA';
    const MP_CURRENCY_SHIPPING = 'ARS';
    const MP_SUFIX = '-AR';
    const MP_ISO_COUNTRY = 'ar';
    const MP_CLASS = 'MercadoPagoAR';

    public static $mp_client_id;
    public static $mp_client_secret;
    public static $mp_cache;
    public static $mp_fee;
    public static $mp_sandbox_active;
    public static $mp_modal_active;
    public static $mp_validation_path;
    public static $mp_style;
    public static $mp_payments;
    public static $mp_status_refound;
    public static $mp_default_width;
    public static $mp_default_height;
    public static $mp_default_depth;
    public static $mp_default_weight;
    public static $mp_free_shipping;
    public static $mp_shippings;
    public static $mp_shipping_active;
    public static $mp_shipping_calc_mode;
    public static $currency_convert;
    public static $site_url;
    public static $mp;
    private static $mp_verify;
    private static $mp_me;

    private static $dimensionUnit = '';
    private static $weightUnit = '';
    private $dimensionUnitList = array('CM' => 'CM', 'IN' => 'IN', 'CMS' => 'CM', 'INC' => 'IN');
    private $weightUnitList = array('KG' => 'KGS', 'KGS' => 'KGS', 'LBS' => 'LBS', 'LB' => 'LBS');
    public $id_carrier;
    public $context;

    public function __construct()
    {
        $this->name = 'mpar';
        $this->tab = 'payments_gateways';
        $this->version = '3.1.10';
        $this->author = 'Kijam.com';
        $this->module_key = '529acf1abc2bd9b8772b9b42c46b47bb';
        $this->limited_countries = array(self::MP_ISO_COUNTRY);

        if (version_compare(_PS_VERSION_, '1.6.0.0') >= 0) {
            $this->bootstrap = true;
        }

        parent::__construct();

        $this->displayName = $this->l('MercadoPago Argentina Pro');
        $this->description = $this->l('MercadoPago platform only valid payment for Argentina');

        if (!is_array(self::$mp_cache)) {
            self::$mp_cache = array();
        }
        /** Backward compatibility */
        if (version_compare(_PS_VERSION_, '1.5') < 0) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }

        $this->context = Context::getContext();
        self::$site_url = Tools::htmlentitiesutf8(((bool)Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].__PS_BASE_URI__);
        self::$mp_validation_path = Configuration::get(self::MP_PREFIX.'VALIDATION');
        self::$currency_convert = (array)Tools::jsonDecode(Configuration::get('currency_convert'), true);
        if (!isset(self::$mp_verify) || self::$mp_verify == null) {
            self::$mp = false;
            self::$mp_verify = false;
            self::$mp_me = false;
        }
        if (version_compare(_PS_VERSION_, '1.5.0.9') >= 0) {
            $id_shop = Shop::getContextShopID();
            $id_shop_group = Shop::getContextShopGroupID();
            if ($id_shop) {
                $shop = new Shop($id_shop);
                self::$site_url = (bool)Configuration::get('PS_SSL_ENABLED')?'https://'.$shop->domain_ssl:'http://'.$shop->domain;
                self::$site_url .= $shop->getBaseURI(true);
            }
            self::$mp_shippings = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'CARRIER', null, $id_shop_group, $id_shop), true);
            self::$mp_shipping_active = (int)Configuration::get(self::MP_PREFIX.'SHIPPING_ACTIVE', null, $id_shop_group, $id_shop);
            self::$mp_free_shipping = (string)Configuration::get(self::MP_PREFIX.'FREE_SHIPPING', null, $id_shop_group, $id_shop);
            self::$mp_shipping_calc_mode = (string)Configuration::get(self::MP_PREFIX.'SHIPPING_MODE', null, $id_shop_group, $id_shop);
            self::$mp_sandbox_active = (int)Configuration::get(self::MP_PREFIX.'SANDBOX', null, $id_shop_group, $id_shop);
            self::$mp_modal_active = (int)Configuration::get(self::MP_PREFIX.'MODAL', null, $id_shop_group, $id_shop);
            self::$mp_client_id = Configuration::get(self::MP_PREFIX.'CLIENT_ID', null, $id_shop_group, $id_shop);
            self::$mp_client_secret = Configuration::get(self::MP_PREFIX.'CLIENT_SECRET', null, $id_shop_group, $id_shop);
            self::$mp_fee = (float)Configuration::get(self::MP_PREFIX.'FEE', null, $id_shop_group, $id_shop);
            self::$mp_style = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'STYLE', null, $id_shop_group, $id_shop), true);
            self::$mp_payments = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'PAYMENTS', null, $id_shop_group, $id_shop), true);
            self::$mp_status_refound = (array)Tools::jsonDecode(Configuration::get(
                self::MP_PREFIX.'STATUS_REFOUND',
                null,
                $id_shop_group,
                $id_shop
            ), true);
            self::$mp_default_width = (int)Configuration::get(self::MP_PREFIX.'WIDTH', null, $id_shop_group, $id_shop);
            self::$mp_default_height = (int)Configuration::get(self::MP_PREFIX.'HEIGHT', null, $id_shop_group, $id_shop);
            self::$mp_default_depth = (int)Configuration::get(self::MP_PREFIX.'DEPTH', null, $id_shop_group, $id_shop);
            self::$mp_default_weight = (int)Configuration::get(self::MP_PREFIX.'WEIGHT', null, $id_shop_group, $id_shop);
        } else {
            self::$mp_shippings = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'CARRIER'), true);
            self::$mp_shipping_active = (int)Configuration::get(self::MP_PREFIX.'SHIPPING_ACTIVE');
            self::$mp_shipping_calc_mode = (string)Configuration::get(self::MP_PREFIX.'SHIPPING_MODE');
            self::$mp_free_shipping = (string)Configuration::get(self::MP_PREFIX.'FREE_SHIPPING');
            self::$mp_sandbox_active = (int)Configuration::get(self::MP_PREFIX.'SANDBOX');
            self::$mp_modal_active = (int)Configuration::get(self::MP_PREFIX.'MODAL');
            self::$mp_client_id = Configuration::get(self::MP_PREFIX.'CLIENT_ID');
            self::$mp_client_secret = Configuration::get(self::MP_PREFIX.'CLIENT_SECRET');
            self::$mp_fee = (float)Configuration::get(self::MP_PREFIX.'FEE');
            self::$mp_style = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'STYLE'), true);
            self::$mp_payments = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'PAYMENTS'), true);
            self::$mp_status_refound = (array)Tools::jsonDecode(
                Configuration::get(self::MP_PREFIX.'STATUS_REFOUND'),
                true
            );
            self::$mp_default_width = (int)Configuration::get(self::MP_PREFIX.'WIDTH');
            self::$mp_default_height = (int)Configuration::get(self::MP_PREFIX.'HEIGHT');
            self::$mp_default_depth = (int)Configuration::get(self::MP_PREFIX.'DEPTH');
            self::$mp_default_weight = (int)Configuration::get(self::MP_PREFIX.'WEIGHT');
        }

        if (!self::$mp_verify && Module::isInstalled($this->name)) {
            if (!isset(self::$mp_shippings['mp_ps']) || count(self::$mp_shippings) == 0) {
                self::log('installCarriers on construct');
                self::installCarriers();
            }
            if (empty(self::$mp_client_id) || empty(self::$mp_client_secret)) {
                $this->warning = $this->l('The client_id and client_secret fields are required to validate payments');
            } else {
                $cache_id = 'mp_verify_'.md5(self::$mp_client_id.'-'.self::$mp_client_secret.'-'.self::$mp_sandbox_active);
                $mp_class = self::MP_CLASS;
                $mp_instance = new $mp_class(self::$mp_client_id, self::$mp_client_secret);
                if (self::getCache($cache_id) == 'valid') {
                    self::log('verifyMercadoPago: -> from cache: '.$cache_id);
                    self::$mp = $mp_instance;
                    if (self::$mp_sandbox_active) {
                        self::$mp->sandbox_mode(true);
                    }
                } else {
                    self::$mp = $mp_instance;
                    if (self::$mp_sandbox_active) {
                        self::$mp->sandbox_mode(true);
                    }
                    if (!$this->verifyMercadoPago()) {
                        self::$mp = false;
                        $this->error[] = $this->l('The client_id and client_secret is invalid or wrong, please verify this. Otherwise, check your library Curl supports HTTPS for SSL connections.');
                    }
                    self::setCache($cache_id, self::$mp?'valid':'invalid');
                }
                self::$mp_verify = true;
                $carriers = array();
                $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
                $count_carriers = 0;
                foreach ($carriers as $carrier) {
                    if (in_array($carrier['id_carrier'], self::$mp_shippings['mp_ps'])) {
                        ++$count_carriers;
                    }
                }
                if ($count_carriers == 0) {
                    $this->warning .= $this->l('"MercadoEnvios" it is not active in the shipping option');
                } else {
                    // Checking Unit
                    self::$dimensionUnit = isset($this->dimensionUnitList[Tools::strtoupper(Configuration::get('PS_DIMENSION_UNIT'))]) ? $this->dimensionUnitList[Tools::strtoupper(Configuration::get('PS_DIMENSION_UNIT'))] : false;
                    self::$weightUnit = isset($this->weightUnitList[Tools::strtoupper(Configuration::get('PS_WEIGHT_UNIT'))]) ? $this->weightUnitList[Tools::strtoupper(Configuration::get('PS_WEIGHT_UNIT'))] : false;
                    if (!self::$weightUnit || !$this->weightUnitList[self::$weightUnit]) {
                        $this->warning .= $this->l('\'Weight Unit is invalid (Only valid: LB or KG).\'');
                    }
                    if (!self::$dimensionUnit || !$this->dimensionUnitList[self::$dimensionUnit]) {
                        $this->warning .= $this->l('\'Dimension Unit is invalid (Only valid: CM or IN).\'');
                    }
                }
            }
        }

        /* For 1.4.3 and less compatibility */
        $update_config = array(self::MP_PREFIX.'OS_PENDING',
                            self::MP_PREFIX.'OS_AUTHORIZATION',
                            self::MP_PREFIX.'OS_REFUSED',
                            'PS_OS_CHEQUE',
                            'PS_OS_PAYMENT',
                            'PS_OS_PREPARATION',
                            'PS_OS_SHIPPING',
                            'PS_OS_CANCELED',
                            'PS_OS_REFUND',
                            'PS_OS_ERROR',
                            'PS_OS_OUTOFSTOCK',
                            'PS_OS_BANKWIRE',
                            'PS_OS_PAYPAL',
                            'PS_OS_WS_PAYMENT');
        foreach ($update_config as $u) {
            if (!Configuration::get($u) && defined('_'.$u.'_')) {
                Configuration::updateValue($u, constant('_'.$u.'_'));
            }
        }

        //For updates...
        if (file_exists(dirname(__FILE__).'/validation.php')) {
            if (!empty(self::$mp_validation_path) && self::$mp_validation_path != 'validation.php') {
                @rename(
                    dirname(__FILE__).'/'.self::$mp_validation_path,
                    dirname(__FILE__).'/bk_'.time().'_'.self::$mp_validation_path
                );
                @rename(dirname(__FILE__).'/validation.php', dirname(__FILE__).'/'.self::$mp_validation_path);
            }
        }

    }

    public static function log($data)
    {
        if (!is_dir(_PS_MODULE_DIR_.self::MP_NAME.'/logs')) {
            @mkdir(_PS_MODULE_DIR_.self::MP_NAME.'/logs');
        }

        if (!is_dir(_PS_MODULE_DIR_.self::MP_NAME.'/logs/'.date('Y-m'))) {
            @mkdir(_PS_MODULE_DIR_.self::MP_NAME.'/logs/'.date('Y-m'));
        }

        $fp = fopen(_PS_MODULE_DIR_.self::MP_NAME.'/logs/'.date('Y-m').'/log-'.date('Y-m-d').'.log', 'a');

        fwrite($fp, "----------------------------------\n");
        fwrite($fp, 'DATE: '.date('Y-m-d H:i:s')."\n");
        fwrite($fp, "----------------------------------\n\n");
        fwrite($fp, $data);
        fwrite($fp, "\n\n----------------------------------\n\n");
        fwrite($fp, "----------------------------------\n\n");
        fclose($fp);
    }

    public function verifyMercadoPago()
    {
        if (self::$mp_me) {
            return self::$mp_me;
        }
        $cache_id = 'mp_me_'.md5(self::$mp_client_id.'-'.self::$mp_client_secret.'-'.self::$mp_sandbox_active);
        $me = false;
        if ($me = self::getCache($cache_id)) {
            if (isset($me['status'])) {
                if ($me['status'] >= 400) {
                    self::log('ERROR-verifyMercadoPago-ME: '.print_r($me, true));
                    return false;
                }
                self::log('verifyMercadoPago-ME: from cache '.$cache_id);
                if ($me && isset($me['response'])) {
                    self::$mp_me = $me['response'];
                    return $me['response'];
                }
            }
        }
        try {
            if (self::$mp) {
                $cache_id = 'mp_me_'.md5(self::$mp_client_id.'-'.self::$mp_client_secret.'-'.self::$mp_sandbox_active);
                $me = self::$mp->get_me();
                self::setCache($cache_id, $me);
                if (!$me || !isset($me['status']) || (int)$me['status'] >= 400) {
                    self::log('ERROR-verifyMercadoPago-ME: '.print_r($me, true));
                    return false;
                }
                self::log('verifyMercadoPago-ME: cache '.$cache_id.' -> '.print_r($me, true));
                if (isset($me['response'])) {
                    self::$mp_me = $me['response'];
                    return $me['response'];
                }
            }
        } catch (Exception $error) {
            self::log('ERROR-verifyMercadoPago: '.print_r($error, true));
            return false;
        }
        return false;
    }

    public function getBalance()
    {
        $me = $this->verifyMercadoPago();
        if ($me) {
            $balance = self::$mp->get_balance($me['id']);
            self::log('getBalance: '.print_r($balance, true));
            return $balance && isset($balance['response'])?$balance['response']:false;
        }
        return false;
    }

    public function createOrderState($validation_name)
    {
        if ((int)Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION')) {
            $order_state = new OrderState((int)Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION'));
            @unlink(dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
            $order_state->delete();
        }

        if ((int)Configuration::get(self::MP_PREFIX.'OS_PENDING')) {
            $order_state = new OrderState((int)Configuration::get(self::MP_PREFIX.'OS_PENDING'));
            @unlink(dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
            $order_state->delete();
        }

        if ((int)Configuration::get(self::MP_PREFIX.'OS_REFUSED')) {
            $order_state = new OrderState((int)Configuration::get(self::MP_PREFIX.'OS_REFUSED'));
            @unlink(dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
            $order_state->delete();
        }

        $order_state = new OrderState();
        $order_state->name = array();

        foreach (Language::getLanguages() as $language) {
            $order_state->name[$language['id_lang']] = $this->l('Payment accepted in MercadoPago').self::MP_SUFIX;
            $order_state->template[$language['id_lang']] = 'payment';
        }

        $order_state->send_email = true;
        $order_state->color = '#00FF00';
        $order_state->hidden = false;
        $order_state->delivery = false;
        if (version_compare(_PS_VERSION_, '1.5.0.1') > 0) {
            $order_state->paid = true;
        }
        $order_state->logable = true;
        $order_state->invoice = true;

        if ($order_state->add()) {
            $source = dirname(__FILE__).'/view/img/state_ms_1.gif';
            $destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
            @copy($source, $destination);
        }
        $id_os_auth = (int)$order_state->id;

        $order_state = new OrderState();
        $order_state->name = array();

        foreach (Language::getLanguages() as $language) {
            $order_state->name[$language['id_lang']] = $this->l('Payment pending MercadoPago').self::MP_SUFIX;
        }

        $order_state->send_email = false;
        $order_state->color = '#DDEEFF';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        if (version_compare(_PS_VERSION_, '1.5.0.1') > 0) {
            $order_state->paid = false;
        }
        $order_state->invoice = false;

        if ($order_state->add()) {
            $source = dirname(__FILE__).'/view/img/state_ms_2.gif';
            $destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
            @copy($source, $destination);
        }
        $id_os_pending = (int)$order_state->id;

        $order_state = new OrderState();
        $order_state->name = array();

        foreach (Language::getLanguages() as $language) {
            $order_state->name[$language['id_lang']] = $this->l('Payment declined in MercadoPago').self::MP_SUFIX;
            $order_state->template[$language['id_lang']] = 'order_canceled';
        }

        $order_state->send_email = true;
        $order_state->color = '#FF0000';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        if (version_compare(_PS_VERSION_, '1.5.0.1') > 0) {
            $order_state->paid = false;
        }
        $order_state->invoice = false;
        if ($order_state->add()) {
            $source = dirname(__FILE__).'/view/img/state_ms_3.gif';
            $destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
            @copy($source, $destination);
        }
        $id_os_refused = (int)$order_state->id;

        $status_refound = array();
        $status_refound[] = (int)Configuration::get('PS_OS_CANCELED');
        $status_refound[] = (int)Configuration::get('PS_OS_REFUND');
        $status_refound[] = (int)Configuration::get('PS_OS_ERROR');
        if (version_compare(_PS_VERSION_, '1.5.0.9') >= 0) {
            $actual_context = Shop::getContext();
            Shop::setContext(Shop::CONTEXT_ALL);
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
                    self::MP_PREFIX.'VALIDATION',
                    $validation_name,
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'OS_AUTHORIZATION',
                    $id_os_auth,
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'OS_PENDING',
                    $id_os_pending,
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'OS_REFUSED',
                    $id_os_refused,
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'WIDTH',
                    '16',
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'HEIGHT',
                    '11',
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'DEPTH',
                    '10',
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'WEIGHT',
                    '100',
                    false,
                    $shop_group_id,
                    $shop_id
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'STATUS_REFOUND',
                    Tools::jsonEncode($status_refound),
                    false,
                    $shop_group_id,
                    $shop_id
                );
            }

            /* Sets up Shop Group configuration */
            if (count($shop_groups_list)) {
                foreach ($shop_groups_list as $shop_group_id) {
                    Configuration::updateValue(self::MP_PREFIX.'VALIDATION', $validation_name, false, $shop_group_id);
                    Configuration::updateValue(self::MP_PREFIX.'OS_AUTHORIZATION', $id_os_auth, false, $shop_group_id);
                    Configuration::updateValue(self::MP_PREFIX.'OS_PENDING', $id_os_pending, false, $shop_group_id);
                    Configuration::updateValue(self::MP_PREFIX.'OS_REFUSED', $id_os_refused, false, $shop_group_id);
                    Configuration::updateValue(
                        self::MP_PREFIX.'WIDTH',
                        '16',
                        false,
                        $shop_group_id
                    );
                    Configuration::updateValue(
                        self::MP_PREFIX.'HEIGHT',
                        '11',
                        false,
                        $shop_group_id
                    );
                    Configuration::updateValue(
                        self::MP_PREFIX.'DEPTH',
                        '10',
                        false,
                        $shop_group_id
                    );
                    Configuration::updateValue(
                        self::MP_PREFIX.'WEIGHT',
                        '100',
                        false,
                        $shop_group_id
                    );
                    Configuration::updateValue(
                        self::MP_PREFIX.'STATUS_REFOUND',
                        Tools::jsonEncode($status_refound),
                        false,
                        $shop_group_id
                    );
                }
            }
            Shop::setContext($actual_context);
        }
        /* Sets up Global configuration */
        Configuration::updateValue(self::MP_PREFIX.'VALIDATION', $validation_name);
        Configuration::updateValue(self::MP_PREFIX.'OS_AUTHORIZATION', $id_os_auth);
        Configuration::updateValue(self::MP_PREFIX.'OS_PENDING', $id_os_pending);
        Configuration::updateValue(self::MP_PREFIX.'OS_REFUSED', $id_os_refused);
        Configuration::updateValue(
            self::MP_PREFIX.'WIDTH',
            '10'
        );
        Configuration::updateValue(
            self::MP_PREFIX.'HEIGHT',
            '10'
        );
        Configuration::updateValue(
            self::MP_PREFIX.'DEPTH',
            '10'
        );
        Configuration::updateValue(
            self::MP_PREFIX.'WEIGHT',
            '100'
        );
        Configuration::updateValue(self::MP_PREFIX.'STATUS_REFOUND', Tools::jsonEncode($status_refound));
        return true;
    }

    private function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, Tools::strlen($characters) - 1)];
        }

        return $random_string;
    }

    public function checkOverride()
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0') < 0) {
            return true;
        }
        @copy(dirname(__FILE__).'/../../override/classes/Cart.php', dirname(__FILE__).'/override/'.time().'-old_cart.txt');
        try {
            $this->uninstallOverrides();
        } catch (Exception $e) {
                //Se ignora...
        }
        try {
            return $this->installOverrides();
        } catch (Exception $e) {
            $this->_errors[] = sprintf(Tools::displayError('Unable to install override: %s'), $e->getMessage());
            $this->uninstallOverrides();
            return false;
        }
    }

    public function install()
    {
        if (!function_exists('curl_version')) {
            $this->_errors[] = $this->l('Curl not installed');
            return false;
        }

        @copy(dirname(__FILE__).'/../../override/classes/Cart.php', dirname(__FILE__).'/override/'.time().'-old_cart.txt');
        $validation_rand = $this->generateRandomString(12);
        $validation_name = 'validation-'.$validation_rand.'.php';
        if (!@rename(dirname(__FILE__).'/validation.php', dirname(__FILE__).'/'.$validation_name)) {
            $validation_name = 'validation.php';
        }

        $db_created = Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::MP_NAME.'` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_order` INT(11) NOT NULL,
                `id_shop` INT(11) NOT NULL,
                `topic` varchar(100) NOT NULL,
                `mp_op_id` varchar(100) NOT NULL,
                `status` INT(11) NOT NULL,
                `next_retry` INT(11) NOT NULL,
                UNIQUE(mp_op_id),
                INDEX(id_order),
                INDEX(id_shop),
                INDEX(next_retry, status)
                )');

        if (!$db_created) {
            $this->_errors[] = $this->l('Failed to create the table in the Database');
        } else {
            $db_created = Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::MP_NAME.'_refunds` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_order` INT(11) NOT NULL,
                `mp_op_id` varchar(100) NOT NULL,
                `date_create` DATETIME NOT NULL,
                `response` TEXT NOT NULL,
                INDEX(id_order),
                INDEX(mp_op_id)
                )');
            if (!$db_created) {
                $this->_errors[] = $this->l('Failed to create the table in the Database');
            } else {
                $db_created = Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::MP_NAME.'_cache` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `cache_id` varchar(100) NOT NULL,
                    `data` LONGTEXT NOT NULL,
                    `ttl` INT(11) NOT NULL,
                    UNIQUE(cache_id),
                    INDEX(ttl)
                    )');
                if (!$db_created) {
                    $this->_errors[] = $this->l('Failed to create the table in the Database');
                }
            }
        }
        $is_14 = version_compare(_PS_VERSION_, '1.5.0.0') < 0;
        $is_16 = version_compare(_PS_VERSION_, '1.6.0.0') >= 0;
        $result = $db_created && parent::install()
            && $this->registerHook('orderConfirmation')
            && $this->registerHook('payment')
            && $this->registerHook('updateOrderStatus')
            && ($is_14?$this->registerHook('adminOrder'):$this->registerHook('displayAdminOrder'))
            && ($is_14?$this->registerHook('header'):$this->registerHook('displayHeader'))
            && ($is_14?$this->registerHook('beforeCarrier'):$this->registerHook('displayBeforeCarrier'))
            && ($is_14?$this->registerHook('backOfficeHeader'):$this->registerHook('displayBackOfficeHeader'))
            && ($is_16?$this->registerHook('dashboardZoneOne'):true)
            && ($is_16?$this->registerHook('dashboardData'):true)
            && Configuration::updateValue(self::MP_PREFIX.'VALIDATION', $validation_name);

        if (!$result && $db_created) {
            Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::MP_NAME.'`');
            Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::MP_NAME.'_refunds`');
        }

        if (!$result && $validation_name != 'validation.php') {
            @rename(dirname(__FILE__).'/'.$validation_name, dirname(__FILE__).'/validation.php');
        }

        if ($result) {
            self::installCarriers();
            $this->createOrderState($validation_name);
            $id_currency = Currency::getIdByIsoCode(self::MP_CURRENCY_SHIPPING);
            $id_country = Country::getByIso(self::MP_ISO_COUNTRY);
            if ($is_14) {
                Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_country` 
                    (id_module, id_country) VALUES
                    ('.(int)$this->id.', '.(int)$id_country.')');
                Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` 
                    (id_module, id_currency) VALUES
                    ('.(int)$this->id.', '.(int)$id_currency.')');
            } else {
                $actual_context = Shop::getContext();
                Shop::setContext(Shop::CONTEXT_ALL);
                $shops = Shop::getContextListShopID();
                foreach ($shops as $shop_id) {
                    Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_country` 
                        (id_module, id_shop, id_country) VALUES
                        ('.(int)$this->id.', '.(int)$shop_id.', '.(int)$id_country.')');
                    Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` 
                        (id_module, id_shop, id_currency) VALUES
                        ('.(int)$this->id.', '.(int)$shop_id.', '.(int)$id_currency.')');
                }
                Shop::setContext($actual_context);
            }
        }

        return $result;
    }

    public static function installCarriers()
    {
        $mp_shippings = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'CARRIER'), true);
        if (isset($mp_shippings['ps_mp']) && count($mp_shippings['ps_mp']) > 0) {
            return;
        }
        $id_country = Country::getByIso(self::MP_ISO_COUNTRY);
        $id_zone = array();
        $id_zone[] = (int)Country::getIdZone($id_country);
        $states = State::getStatesByIdCountry($id_country);
        if ($states) {
            foreach ($states as $state) {
                if ((int)$state['id_zone'] > 0 && !in_array((int)$state['id_zone'], $id_zone)) {
                    $id_zone[] = (int)$state['id_zone'];
                }
            }
        }
        $class_name = self::MP_CLASS;
        $shippings = $class_name::get_shipping_list(self::MP_SHIPPING_COUNTRY);
        self::log('installCarriers: '.print_r($shippings, true));
        $shipping_data = array();
        foreach ($shippings as $shipping) {
            if ($shipping['site_id'] == self::MP_SHIPPING_COUNTRY) {
                if (is_array($shipping['shipping_modes']) && count($shipping['shipping_modes']) > 0 && !in_array('me2', $shipping['shipping_modes'])) {
                    continue;
                }
            }
            if ($shipping['status'] == 'active') {
                $carrierConfig = array(
                    'name' => $shipping['name'],
                    'url' => '',
                    'id_tax_rules_group' => 0,
                    'deleted' => 0,
                    'shipping_handling' => false,
                    'range_behavior' => 0,
                    'delay' => array('es' => 'Cálculo Dinámico', 'en' => 'Dynamic calculation'),
                    'id_zone' => $id_zone,
                    'is_module' => true,
                    'shipping_external' => true,
                    'external_module_name' => self::MP_NAME,
                    'need_range' => true,
                    'active' => true
                );
                $id_carrier = self::installExternalCarrier($carrierConfig);
                if (!$id_carrier || $id_carrier < 1) {
                    self::log('Failed to create the carrier MercadoEnvios');
                    return false;
                } else {
                    self::log('New Carrier['.$id_carrier.']: '.print_r($carrierConfig, true));
                }
                $shipping_data['ps_mp'][$id_carrier] = $shipping['id'];
                $shipping_data['mp_ps'][$shipping['id']] = $id_carrier;
            }
        }
        self::refreshCarrierList($shipping_data);
    }

    public static function refreshCarrierList($shipping_data)
    {
         Configuration::updateValue(self::MP_PREFIX.'CARRIER', Tools::jsonEncode($shipping_data));
        if (version_compare(_PS_VERSION_, '1.5.0.9') >= 0) {
            $actual_context = Shop::getContext();
            Shop::setContext(Shop::CONTEXT_ALL);
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
                    self::MP_PREFIX.'CARRIER',
                    Tools::jsonEncode($shipping_data),
                    false,
                    $shop_group_id,
                    $shop_id
                );
            }

            /* Sets up Shop Group configuration */
            if (count($shop_groups_list)) {
                foreach ($shop_groups_list as $shop_group_id) {
                    Configuration::updateValue(
                        self::MP_PREFIX.'CARRIER',
                        Tools::jsonEncode($shipping_data),
                        false,
                        $shop_group_id
                    );
                }
            }
            Shop::setContext($actual_context);
        }
    }

    public static function installExternalCarrier($config)
    {
        $carrier = new Carrier();
        $carrier->name = $config['name'];
        $carrier->url = $config['url'];
        $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
        $carrier->active = $config['active'];
        $carrier->deleted = $config['deleted'];
        $carrier->delay = $config['delay'];
        $carrier->shipping_handling = $config['shipping_handling'];
        $carrier->range_behavior = $config['range_behavior'];
        $carrier->is_module = $config['is_module'];
        $carrier->shipping_external = $config['shipping_external'];
        $carrier->external_module_name = $config['external_module_name'];
        $carrier->need_range = $config['need_range'];
        $carrier->active = $config['active'];
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) && isset($config['delay'][$language['iso_code']])) {
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            } elseif ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) && isset($config['delay']['en'])) {
                $carrier->delay[(int)$language['id_lang']] = $config['delay']['en'];
            }
        }
        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'carrier_group',
                    array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])),
                    'INSERT'
                );
            }
            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '1000000000';
            $rangePrice->add();
            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '1000000000';
            $rangeWeight->add();
            if (is_array($config['id_zone'])) {
                foreach ($config['id_zone'] as $id_zone) {
                    Db::getInstance()->autoExecute(
                        _DB_PREFIX_.'carrier_zone',
                        array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)$id_zone),
                        'INSERT'
                    );
                    Db::getInstance()->autoExecuteWithNullValues(
                        _DB_PREFIX_.'delivery',
                        array('id_carrier' => (int)($carrier->id),
                            'id_range_price' => (int)($rangePrice->id),
                            'id_range_weight' => null,
                            'id_zone' => (int)$id_zone,
                            'price' => '0'),
                        'INSERT'
                    );
                    Db::getInstance()->autoExecuteWithNullValues(
                        _DB_PREFIX_.'delivery',
                        array('id_carrier' => (int)($carrier->id),
                            'id_range_price' => null,
                            'id_range_weight' => (int)($rangeWeight->id),
                            'id_zone' => (int)$id_zone,
                            'price' => '0'),
                        'INSERT'
                    );
                }
            } else {
                Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'carrier_zone',
                    array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)$config['id_zone']),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_.'delivery',
                    array('id_carrier' => (int)($carrier->id),
                        'id_range_price' => (int)($rangePrice->id),
                        'id_range_weight' => null,
                        'id_zone' => (int)$config['id_zone'],
                        'price' => '0'),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_.'delivery',
                    array('id_carrier' => (int)($carrier->id),
                        'id_range_price' => null,
                        'id_range_weight' => (int)($rangeWeight->id),
                        'id_zone' => (int)$config['id_zone'],
                        'price' => '0'),
                    'INSERT'
                );
            }
            // Copy Logo
            @copy(dirname(__FILE__).'/views/img/carrier.png', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg');

            // Return ID Carrier
            return (int)($carrier->id);
        }
        return false;
    }

    public function uninstall()
    {
        if ((int)Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION')) {
            $order_state = new OrderState((int)Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION'));
            @unlink(dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
            $order_state->delete();
        }

        if ((int)Configuration::get(self::MP_PREFIX.'OS_PENDING')) {
            $order_state = new OrderState((int)Configuration::get(self::MP_PREFIX.'OS_PENDING'));
            @unlink(dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
            $order_state->delete();
        }

        if ((int)Configuration::get(self::MP_PREFIX.'OS_REFUSED')) {
            $order_state = new OrderState((int)Configuration::get(self::MP_PREFIX.'OS_REFUSED'));
            @unlink(dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
            $order_state->delete();
        }
        if (self::$mp_validation_path) {
            @rename(dirname(__FILE__).'/'.self::$mp_validation_path, dirname(__FILE__).'/validation.php');
        }
        foreach (self::$mp_shippings['mp_ps'] as $id_carrier) {
            $carrier = new Carrier($id_carrier);
            $carrier->deleted = true;
            $carrier->active = false;
            $carrier->save();
        }
        Configuration::deleteByName(self::MP_PREFIX.'STATUS_REFOUND');
        Configuration::deleteByName(self::MP_PREFIX.'CARRIER');
        Configuration::deleteByName(self::MP_PREFIX.'SHIPPING_MODE');
        Configuration::deleteByName(self::MP_PREFIX.'SHIPPING_ACTIVE');
        Configuration::deleteByName(self::MP_PREFIX.'WIDTH');
        Configuration::deleteByName(self::MP_PREFIX.'HEIGHT');
        Configuration::deleteByName(self::MP_PREFIX.'DEPTH');
        Configuration::deleteByName(self::MP_PREFIX.'WEIGHT');
        Configuration::deleteByName(self::MP_PREFIX.'FREE_SHIPPING');
        Configuration::deleteByName(self::MP_PREFIX.'OS_AUTHORIZATION');
        Configuration::deleteByName(self::MP_PREFIX.'OS_PENDING');
        Configuration::deleteByName(self::MP_PREFIX.'OS_REFUSED');
        Configuration::deleteByName(self::MP_PREFIX.'SANDBOX');
        Configuration::deleteByName(self::MP_PREFIX.'MODAL');
        Configuration::deleteByName(self::MP_PREFIX.'CLIENT_ID');
        Configuration::deleteByName(self::MP_PREFIX.'CLIENT_SECRET');
        Configuration::deleteByName(self::MP_PREFIX.'FEE');
        Configuration::deleteByName(self::MP_PREFIX.'VALIDATION');
        Configuration::deleteByName(self::MP_PREFIX.'STYLE');

        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::MP_NAME.'`');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::MP_NAME.'_refunds`');
        return (parent::uninstall());
    }

    public function hookDashboardZoneOne($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/dashboard_zone_one.tpl');
    }

    public function hookDashboardData($params)
    {
        $balance = $this->getBalance();
        return array(
            'data_value' => array(
                self::MP_NAME.'_total_amount' => $balance['total_amount'].' '.$balance['currency_id'],
                self::MP_NAME.'_available_balance' => $balance['available_balance'].' '.$balance['currency_id'],
                self::MP_NAME.'_unavailable_balance' => $balance['unavailable_balance'].' '.$balance['currency_id'],
            ),
            'data_trends' => array(
            ),
            'data_list_small' => array(
            ),
            'data_chart' => array(
            ),
        );
    }

    public function hookDisplayHeader($params)
    {
        return $this->hookHeader($params);
    }

    public function hookHeader($params)
    {
        $valid = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
                        SELECT * FROM `'._DB_PREFIX_.self::MP_NAME.'`
                        WHERE `next_retry` < '.(int)time().' AND 
                        
                        (`status` = '.(int)Configuration::get(self::MP_PREFIX.'OS_PENDING').'
                            OR
                         `status` = '.(int)Configuration::get('PS_OS_PREPARATION').'
                            OR
                         `status` = '.(int)Configuration::get('PS_OS_SHIPPING').') LIMIT 3');
        $cancel_status = array(
            (int)Configuration::get('PS_OS_CANCELED'),
            (int)Configuration::get('PS_OS_ERROR'),
            (int)Configuration::get('PS_OS_DELIVERED'),
            (int)Configuration::get('PS_OS_REFUND'),
            (int)Configuration::get('PS_OS_ERROR'),
            (int)Configuration::get('PS_OS_OUTOFSTOCK'),
            (int)Configuration::get('PS_OS_OUTOFSTOCK_PAID'),
            (int)Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
        );
        foreach ($valid as &$pago) {
            $order = new Order($pago['id_order']);
            if (Validate::isLoadedObject($order)) {
                $status_act = $order->getCurrentState();
                if (!in_array($status_act, $cancel_status) && $status_act == $pago['status']) {
                    $result = $this->validateMercadoPago($pago['topic'], $pago['mp_op_id']);
                    if (isset($result['status'])) {
                        if ($result['status'] != $order->getCurrentState()) {
                            $order->setCurrentState($result['status']);
                            $status_act = $order->getCurrentState();
                        }
                    }
                    Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.self::MP_NAME.'` 
                        SET
                            `next_retry` = '.(int)(time() + 6 * 60 * 60).',
                            `status` = '.(int)$status_act.'
                        WHERE id = '.(int)$pago['id']);
                } else {
                    Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.self::MP_NAME.'` 
                        SET
                            `next_retry` = '.(int)(time() + 6 * 60 * 60).',
                            `status` = '.(int)Configuration::get('PS_OS_DELIVERED').'
                        WHERE id = '.(int)$pago['id']);
                }
            }
        }
        return '';
    }

    public function hookDisplayAdminOrder($params)
    {
        if (!isset($params['id_order'])) {
            return '';
        }
        $order_id = (int)$params['id_order'];
        $valid = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
                        SELECT * FROM `'._DB_PREFIX_.self::MP_NAME.'`
                        WHERE `id_order` = '.(int)$order_id);
        if (isset($valid[0])) {
            $valid = $valid[0];
        }
        if (!$valid || !isset($valid['mp_op_id'])) {
            return '';
        }
        $has_refund = Db::getInstance()->ExecuteS('SELECT *
                        FROM `'._DB_PREFIX_.self::MP_NAME.'_refunds`
                        WHERE `id_order` = '.(int)$order_id);
        if (isset($has_refund[0])) {
            $has_refund = $has_refund[0];
        }
        $validation = $this->validateMercadoPago($valid['topic'], $valid['mp_op_id']);
        if ($validation && isset($validation['status'])) {
            $order = new Order($params['id_order']);
            $cancel_status = array(
                (int)Configuration::get('PS_OS_CANCELED'),
                (int)Configuration::get('PS_OS_ERROR'),
                (int)Configuration::get('PS_OS_DELIVERED'),
                (int)Configuration::get('PS_OS_REFUND'),
                (int)Configuration::get('PS_OS_ERROR'),
                (int)Configuration::get('PS_OS_OUTOFSTOCK'),
                (int)Configuration::get('PS_OS_OUTOFSTOCK_PAID'),
                (int)Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
            );
            $status_act = $order->getCurrentState();
            if ($status_act != $validation['status'] && !in_array($status_act, $cancel_status) && $valid['status'] == $status_act) {
                $order->setCurrentState($validation['status']);
                Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.self::MP_NAME.'` 
                    SET 
                        `next_retry` = '.(int)(time() + 6 * 60 * 60).',
                        `status` = '.(int)$order->getCurrentState().'
                    WHERE id = '.(int)$valid['id']);
            }
        }
        $order_state = new OrderState($valid['status']);
        $this->context->smarty->assign(array(
            'order_id' => $order_id,
            'mp_data' => $valid,
            'mp_validation' => $validation,
            'has_refund' => $has_refund,
            'mp_last_sync' => date('Y-m-d H:i:s', $valid['next_retry'] - 6 * 60 * 60),
            'mp_status' => $order_state->name[$this->context->language->id],
            'backwardcompatible' => _PS_VERSION_ < '1.6'
        ));
        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl');
    }

    public function hookAdminOrder($params)
    {
        return $this->hookDisplayAdminOrder($params);
    }

    public function hookOrderConfirmation($params)
    {
        if (!$this->active) {
            return;
        }

        if ($params['objOrder']->module != $this->name) {
            return;
        }

        switch ($params['objOrder']->getCurrentState()) {
            case Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION'):
            case Configuration::get('PS_OS_PREPARATION'):
            case Configuration::get('PS_OS_SHIPPING'):
                $this->context->smarty->assign(array('status' => 'ok', 'id_order' => $params['objOrder']->id));
                break;
            case Configuration::get(self::MP_PREFIX.'OS_PENDING'):
                $this->context->smarty->assign(array('status' => 'pending', 'id_order' => $params['objOrder']->id));
                break;
            default:
                $this->context->smarty->assign('status', 'failed');
                break;
        }

        return $this->display(__FILE__, 'views/templates/hook/hookorderconfirmation.tpl');
    }

    private function preProcess()
    {
        if (Tools::isSubmit('submitModule')) {
            self::$mp_client_id = Tools::getValue('client_id');
            self::$mp_client_secret = Tools::getValue('client_secret');
            self::$mp_fee = (float)Tools::getValue('fee');
            self::$mp_sandbox_active = (int)Tools::getValue('sandbox');
            self::$mp_modal_active = (int)Tools::getValue('modal');
            self::$mp_default_width = (int)Tools::getValue('width');
            self::$mp_default_height = (int)Tools::getValue('height');
            self::$mp_default_depth = (int)Tools::getValue('depth');
            self::$mp_default_weight = (int)Tools::getValue('weight');
            self::$mp_free_shipping = (string)Tools::getValue('free_shipping');
            self::$mp_shipping_calc_mode = (string)Tools::getValue('shipping_mode');
            self::$mp_shipping_active = (int)Tools::getValue('shipping_active');
            if (version_compare(_PS_VERSION_, '1.5.0.9') >= 0) {
                $id_shop = Shop::getContextShopID();
                $id_shop_group = Shop::getContextShopGroupID();
                Configuration::updateValue(
                    self::MP_PREFIX.'WIDTH',
                    self::$mp_default_width,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'HEIGHT',
                    self::$mp_default_height,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'DEPTH',
                    self::$mp_default_depth,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'WEIGHT',
                    self::$mp_default_weight,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'SHIPPING_ACTIVE',
                    self::$mp_shipping_active,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'SHIPPING_MODE',
                    self::$mp_shipping_calc_mode,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'FREE_SHIPPING',
                    self::$mp_free_shipping,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'CLIENT_ID',
                    self::$mp_client_id,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'CLIENT_SECRET',
                    self::$mp_client_secret,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'FEE',
                    self::$mp_fee,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'SANDBOX',
                    self::$mp_sandbox_active,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'MODAL',
                    self::$mp_modal_active,
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'STYLE',
                    Tools::jsonEncode(Tools::getValue('mpstyle')),
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'STATUS_REFOUND',
                    Tools::jsonEncode(Tools::getValue('status_refound')),
                    false,
                    $id_shop_group,
                    $id_shop
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'PAYMENTS',
                    Tools::jsonEncode(Tools::getValue('mppayment')),
                    false,
                    $id_shop_group,
                    $id_shop
                );
                self::$mp_style = (array)Tools::jsonDecode(Configuration::get(
                    self::MP_PREFIX.'STYLE',
                    null,
                    $id_shop_group,
                    $id_shop
                ), true);
                self::$mp_payments = (array)Tools::jsonDecode(Configuration::get(
                    self::MP_PREFIX.'PAYMENTS',
                    null,
                    $id_shop_group,
                    $id_shop
                ), true);
                self::$mp_status_refound = (array)Tools::jsonDecode(Configuration::get(
                    self::MP_PREFIX.'STATUS_REFOUND',
                    null,
                    $id_shop_group,
                    $id_shop
                ), true);
            } else {
                Configuration::updateValue(self::MP_PREFIX.'WIDTH', self::$mp_default_width);
                Configuration::updateValue(self::MP_PREFIX.'HEIGHT', self::$mp_default_height);
                Configuration::updateValue(self::MP_PREFIX.'DEPTH', self::$mp_default_depth);
                Configuration::updateValue(self::MP_PREFIX.'WEIGHT', self::$mp_default_weight);
                Configuration::updateValue(self::MP_PREFIX.'CLIENT_ID', self::$mp_client_id);
                Configuration::updateValue(self::MP_PREFIX.'CLIENT_SECRET', self::$mp_client_secret);
                Configuration::updateValue(self::MP_PREFIX.'SHIPPING_ACTIVE', (int)self::$mp_shipping_active);
                Configuration::updateValue(self::MP_PREFIX.'SHIPPING_MODE', self::$mp_shipping_calc_mode);
                Configuration::updateValue(self::MP_PREFIX.'FREE_SHIPPING', self::$mp_free_shipping);
                Configuration::updateValue(self::MP_PREFIX.'FEE', self::$mp_fee);
                Configuration::updateValue(self::MP_PREFIX.'SANDBOX', self::$mp_sandbox_active);
                Configuration::updateValue(self::MP_PREFIX.'MODAL', self::$mp_modal_active);
                Configuration::updateValue(self::MP_PREFIX.'STYLE', Tools::jsonEncode(Tools::getValue('mpstyle')));
                Configuration::updateValue(
                    self::MP_PREFIX.'STATUS_REFOUND',
                    Tools::jsonEncode(Tools::getValue('status_refound'))
                );
                Configuration::updateValue(
                    self::MP_PREFIX.'PAYMENTS',
                    Tools::jsonEncode(Tools::getValue('mppayment'))
                );
                self::$mp_style = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'STYLE'), true);
                self::$mp_payments = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'PAYMENTS'), true);
                self::$mp_status_refound = (array)Tools::jsonDecode(Configuration::get(self::MP_PREFIX.'STATUS_REFOUND'), true);
            }
            if (empty(self::$mp_client_id) || empty(self::$mp_client_secret)) {
                return '<div class="bootstrap">
                    <div class="alert alert-danger"><img src="../img/admin/error.png"/>
                        '.$this->l('The client_id and client_secret fields are required to validate payments').'
                        </div>
                    </div>';
            } else {
                $mp_class = self::MP_CLASS;
                self::$mp = new $mp_class(self::$mp_client_id, self::$mp_client_secret);
            }
            return '<div class="conf confirm"><img src="../img/admin/ok.gif"/>'.$this->l('Update Settings').'</div>';
        }
        return '';
    }

    public function getRate($from, $to)
    {
        if ($from == $to) {
            return 1.0;
        }

        if (isset(self::$currency_convert[$from])
                && isset(self::$currency_convert[$from][$to])
                && self::$currency_convert[$from][$to]['time'] > time() - 60 * 60 * 12) {
                    return (float)self::$currency_convert[$from][$to]['rate'];
        }

        $headers = array(
                'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Host:download.finance.yahoo.com',
                'Connection:keep-alive',
                'Connection:keep-alive',
                'User-Agent:Mozilla/5.0 (Windows NT 6.3) AppleWebKit/53 (KHTML, like Gecko) Chrome/37 Safari/537.36');

        $ch = curl_init('http://download.finance.yahoo.com/d/quotes.csv?e=.csv&f=l1&s='.$from.$to.'=X');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        self::$currency_convert[$from][$to]['time'] = time();
        self::$currency_convert[$from][$to]['rate'] = (float)$result;

        Configuration::updateValue('currency_convert', Tools::jsonEncode(self::$currency_convert));

        return self::$currency_convert[$from][$to]['rate'];
    }

    public function validateMercadoPago($topic, $mp_op_id)
    {
        if (!$mp_op_id) {
            return false;
        }
        $status_act = false;
        $merchant_order_info = false;
        $payment_info = false;
        $shipping_info = false;
        if ($topic == null || !$topic || Tools::strlen($topic) < 1 || $topic == 'payment') {
            try {
                $payment_info = self::$mp->get_payment_info($mp_op_id);
                if ($payment_info['status'] >= 400) {
                    self::log('ERROR-validateMercadoPago['.$mp_op_id.']->get_payment_info:
                    '.print_r($payment_info, true));
                    return false;
                }
            } catch (Exception $e) {
                self::log('ERROR-validateMercadoPago['.$mp_op_id.']->get_payment_info: '.print_r($e, true));
                return false;
            }
            self::log('validateMercadoPago['.$mp_op_id.']->get_payment_info: '.print_r($payment_info, true));
            if (isset($payment_info['response']) && isset($payment_info['response']['collection'])) {
                $payment_info = $payment_info['response']['collection'];
                try {
                    if (isset($payment_info['merchant_order_id'])) {
                        $merchant_order_info = self::$mp->get_merchant_order($payment_info['merchant_order_id']);
                        if ($merchant_order_info['status'] >= 400) {
                            self::log('ERROR-validateMercadoPago['.$mp_op_id.']->get_merchant_order:
                            '.print_r($merchant_order_info, true));
                            return false;
                        }
                    }
                } catch (Exception $e) {
                    self::log('ERROR-validateMercadoPago['.$mp_op_id.']->get_merchant_order: '.print_r($e, true));
                    return false;
                }
                self::log('validateMercadoPago['.$mp_op_id.']->get_merchant_order: 
                    '.print_r($merchant_order_info, true));
            }
        } else {
            try {
                $merchant_order_info = self::$mp->get_merchant_order($mp_op_id);
                if ($merchant_order_info['status'] >= 400) {
                    self::log('ERROR-validateMercadoPago['.$mp_op_id.']->get_merchant_order:
                    '.print_r($merchant_order_info, true));
                    return false;
                }
            } catch (Exception $e) {
                self::log('ERROR-validateMercadoPago['.$mp_op_id.']->get_merchant_order: '.print_r($e, true));
                return false;
            }
            self::log('validateMercadoPago['.$mp_op_id.']->get_merchant_order: 
                '.print_r($merchant_order_info, true));
        }
        if (isset($merchant_order_info['response']) && isset($merchant_order_info['response']['total_amount'])) {
            $merchant_order_info = $merchant_order_info['response'];
            $paid_amount = 0;
            if (isset($merchant_order_info['shipments'])) {
                if (is_array($merchant_order_info['shipments']) && count($merchant_order_info['shipments']) > 0) {
                    foreach ($merchant_order_info['shipments'] as $shipping) {
                        $shipping_info = $shipping;
                    }
                }
            }
            foreach ($merchant_order_info['payments'] as $payment) {
                if (!$payment_info) {
                    try {
                        $payment_info = self::$mp->get_payment_info($payment['id']);
                        if ($payment_info['status'] >= 400) {
                            self::log('ERROR-validateMercadoPago['.$payment['id'].']->get_payment_info:
                            '.print_r($payment_info, true));
                            $payment_info = $payment;
                        } else if (isset($payment_info['response']) && isset($payment_info['response']['collection'])) {
                            $payment_info = $payment_info['response']['collection'];
                            self::log('validateMercadoPago['.$mp_op_id.']->get_payment_info: '.print_r($payment_info, true));
                        }
                    } catch (Exception $e) {
                        self::log('ERROR-validateMercadoPago['.$payment['id'].']->get_payment_info: '.print_r($e, true));
                        $payment_info = $payment;
                    }
                }
                if ($payment['status'] == 'approved') {
                    $paid_amount += $payment['total_paid_amount'];
                }
            }
            if ($paid_amount >= $merchant_order_info['total_amount'] + $merchant_order_info['shipping_cost']) {
                $status_act = Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION');
                if ($shipping_info) {
                    if ($shipping_info['shipping_mode'] == 'me2') {
                        switch($shipping_info['status']) {
                            case 'pending':
                            case 'ready_to_ship':
                                $status_act = Configuration::get('PS_OS_PREPARATION');
                                break;
                            case 'shipped':
                                $status_act = Configuration::get('PS_OS_SHIPPING');
                                break;
                            case 'delivered':
                                $status_act = Configuration::get('PS_OS_DELIVERED');
                                break;
                            case 'not_delivered':
                            case 'cancelled':
                                $status_act = Configuration::get('PS_OS_CANCELED');
                                break;
                        }
                    }
                }
            }
        }
        $ret = array();
        if (!$status_act && !$payment_info) {
            return false;
        } elseif (!$status_act) {
            if (!isset($payment_info['status']) || !isset($payment_info['order_id'])) {
                $ret['errors'][] = 'Error: '.print_r($payment_info, true);
            } else {
                switch ($payment_info['status']) {
                    case 'approved':
                        $status_act = Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION');
                        break;
                    case 'refunded':
                    case 'cancelled':
                    case 'rejected':
                        $status_act = Configuration::get(self::MP_PREFIX.'OS_REFUSED');
                        break;
                    default:
                        $status_act = Configuration::get(self::MP_PREFIX.'OS_PENDING');
                        break;
                }
            }
        }
        $fee = ((float)self::$mp_fee) / 100.0;
        if (isset($merchant_order_info['external_reference'])) {
            $ret['fee'] = (float)$merchant_order_info['total_amount'] * $fee;
            $ret['price'] = (float)$merchant_order_info['total_amount'] - $ret['fee'];
            $ret['shipping'] = $merchant_order_info['shipping_cost'];
            $ret['mp_op_id'] = $payment_info['id'];
            $ret['mp_order_id'] = $merchant_order_info['id'];
            $ret['order_id'] = (int)$merchant_order_info['external_reference'];
        } else {
            $total_paid_amount = (float)$payment_info['total_paid_amount'] - (float)$payment_info['shipping_cost'];
            $ret['fee'] = $total_paid_amount * $fee;
            $ret['price'] = (float)$total_paid_amount - $ret['fee'];
            $ret['shipping'] = $payment_info['shipping_cost'];
            $ret['mp_op_id'] = $payment_info['id'];
            $ret['order_id'] = (int)$payment_info['external_reference'];
        }
        if (isset($payment_info['payer']) && isset($payment_info['payer']['identification'])) {
            $ret['identification'] = $payment_info['payer']['identification']['type'].' '.$payment_info['payer']['identification']['number'];
        } else {
            $ret['identification'] = $this->l('Not available');
        }
        if (isset($payment_info['payer']) && isset($payment_info['payer']['first_name'])) {
            $ret['client_name'] = trim($payment_info['payer']['first_name'].' '.$payment_info['payer']['last_name']);
        } else {
            $ret['client_name'] = $this->l('Not available');
        }
        $ret['total'] = $ret['price'] + $ret['fee'] + $ret['shipping'];
        $ret['status'] = $status_act;
        $ret['currency_id'] = $payment_info['currency_id'];
        $ret['message1'] = Tools::jsonEncode($merchant_order_info).'\n';
        $ret['message2'] = Tools::jsonEncode($payment_info).'\n';
        self::log('validateMercadoPago['.$mp_op_id.']->return: '.print_r($ret, true));
        return $ret;
    }

    private function displayPresentation()
    {
        $out = '
        <fieldset class="width">
            <legend><img src="../img/admin/contact.gif" />
                '.sprintf($this->l('Get an account MercadoPago %s'), self::COUNTRY_NAME).'
            </legend>
            <p>
                '.$this->l('For get a account in MercadoPago, please following link:')
                .' <a href="'.self::REGISTER_URL.'" class="link" target="_blank" >&raquo; '.$this->l('Link').' &laquo;</a>
            </p>
        </fieldset>';
        $out .= '
        <fieldset class="width">
            <legend><img src="../img/admin/contact.gif" />
                '.$this->l('Documentation:').'
            </legend>
            <p>
                <b>
                <a style="color: red" href="'.self::$site_url.'modules/'.$this->name.'/docs/'.$this->l('readme_en').'.pdf" 
                class="link" target="_blank" >&raquo; '.$this->l('How to configure this module').' &laquo;
                </a></b>
            </p>
        </fieldset>';
        return $out;
    }

    public function getContent()
    {
        $str = '';
        if (version_compare(_PS_VERSION_, '1.5.0.9') >= 0) {
            $str = $this->getWarningMultishopHtml();
            if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
                return $str.$this->getShopContextError();
            }
        }

        $str .= $this->preProcess();
        if (!self::$mp && self::$mp_client_id && self::$mp_client_secret) {
            $mp_class = self::MP_CLASS;
            self::$mp = new $mp_class(self::$mp_client_id, self::$mp_client_secret);
        }
        if (self::$mp) {
            $cache_id = 'mp_me_'.md5(self::$mp_client_id.'-'.self::$mp_client_secret.'-'.self::$mp_sandbox_active);
            $me = self::$mp->get_me();
            self::setCache($cache_id, $me);
            if ($me && isset($me['status']) && (int)$me['status'] < 400) {
                if (isset($me['response'])) {
                    self::$mp_me = $me['response'];
                }
            }
        }

        if (!$this->verifyMercadoPago()) {
            $str .= '<div class="bootstrap">
                <div class="alert alert-danger">
                    <img src="../img/admin/error.png"/>
                    '.$this->l('The client_id and client_secret is invalid or wrong, please verify this. Otherwise, check your library Curl supports HTTPS for SSL connections.').'
                 </div>
             </div>';
        }

        $order_states = OrderState::getOrderStates(Context::getContext()->employee->id_lang);
        $str .= '<h2>'.$this->displayName.'</h2>'
        .$this->displayPresentation()
        .'<br />
        <form action="'.Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']).'" method="post">
            <fieldset class="width">
                <legend><img src="../img/admin/contact.gif" />'.$this->l('Configuration').'</legend>
                <label>'.$this->l('Client_id').' <sup>*</sup></label>
                <div class="margin-form">
                    <input type="text" size="20" name="client_id" value="'.Tools::safeOutput(self::$mp_client_id).'" />
                    <p><a href="'.self::SECRET_URL.'" target="_blank">'.$this->l('Search this information here').'</a></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Client_secret').' <sup>*</sup></label>
                <div class="margin-form">
                    <input type="text" size="20" name="client_secret" value="'.Tools::safeOutput(self::$mp_client_secret).'" />
                    <p><a href="'.self::SECRET_URL.'" target="_blank">'.$this->l('Search this information here').'</a></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('% Commission charge a customer for using MercadoPago:').' <sup>*</sup></label>
                <div class="margin-form">
                    <input type="text" size="20" name="fee" value="'.Tools::htmlentitiesUTF8(self::$mp_fee).'" />
                    <p>'.$this->l('Only decimal value, for example:').' 6.99</p>
                </div>
                <br class="clear" style="clear:both" />
                <label><span style="color:red !important;">'.$this->l('CHANGE').'</span> 
                    '.$this->l('URL of state payments:').' <sup>*</sup></label>
                <div class="margin-form">
                    <p>'.self::$site_url.'modules/'.$this->name.'/'.self::$mp_validation_path.'</p>
                    <p><a href="'.self::IPN_URL.'" target="_blank">'.$this->l('Change this information here').'</a></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Exclude Payment Method').' <sup>*</sup></label>
                <div class="margin-form">
                    <p><input
           '.(isset(self::$mp_payments['bank_transfer']) && self::$mp_payments['bank_transfer'] == '1'?'checked':'').'
                        type="checkbox" name="mppayment[bank_transfer]" value="1" /> '.$this->l('Bank Transfer').'</p>
                    <p><input
           '.(isset(self::$mp_payments['atm']) && self::$mp_payments['atm'] == '1'?'checked':'').' type="checkbox" 
                    name="mppayment[atm]" value="1" /> '.$this->l('ATM Bank Transfer').'</p>
                    <p><input
           '.(isset(self::$mp_payments['ticket']) && self::$mp_payments['ticket'] == '1'?'checked':'').' type="checkbox" 
                    name="mppayment[ticket]" value="1" /> '.$this->l('Ticket').'</p>
                    <p><input
           '.(isset(self::$mp_payments['credit_card']) && self::$mp_payments['credit_card'] == '1'?'checked':'').'
                    type="checkbox" name="mppayment[credit_card]" value="1" /> '.$this->l('Payment by credit card').'</p>
                    <p><input
           '.(isset(self::$mp_payments['debit_card']) && self::$mp_payments['debit_card'] == '1'?'checked':'').'
                    type="checkbox" name="mppayment[debit_card]" value="1" /> '.$this->l('Payment by debit card').'</p>
                    <p><input
           '.(isset(self::$mp_payments['prepaid_card']) && self::$mp_payments['prepaid_card'] == '1'?'checked':'').'
                    type="checkbox" name="mppayment[prepaid_card]" value="1" />
                        '.$this->l('Payment by prepaid card').'</p>
                </div>
                <br class="clear" style="clear:both" />
                <label>
                '.$this->l('Modal Window (By clicking on "Pay" MercadoPago window opens on the Same Page)').'
                <sup>*</sup></label>
                <div class="margin-form">
                    <p><select name="modal"><option value="0">'.$this->l('No').'</option><option value="1"
                    '.(self::$mp_modal_active?'selected':'').'>'.$this->l('Yes').'</option></select></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Color button').' <sup>*</sup></label>
                <div class="margin-form">
                    <p><select name="mpstyle[b_color]">
                        <option 
                        '.(isset(self::$mp_style['b_color']) && self::$mp_style['b_color'] == 'blue'?'selected':'').' value="blue">
                            '.$this->l('Blue').'
                        </option>
                        <option 
                        '.(isset(self::$mp_style['b_color']) && self::$mp_style['b_color'] == 'orange'?'selected':'').' value="orange">
                            '.$this->l('Orange').'
                        </option>
                        <option 
                        '.(isset(self::$mp_style['b_color']) && self::$mp_style['b_color'] == 'red'?'selected':'').' value="red">
                            '.$this->l('Red').'
                        </option>
                        <option 
                        '.(isset(self::$mp_style['b_color']) && self::$mp_style['b_color'] == 'green'?'selected':'').' value="green">
                            '.$this->l('Green').'
                        </option>
                        <option 
                        '.(isset(self::$mp_style['b_color']) && self::$mp_style['b_color'] == 'lightblue'?'selected':'').' value="lightblue">
                            '.$this->l('LightBlue').'</option>
                        <option 
                        '.(isset(self::$mp_style['b_color']) && self::$mp_style['b_color'] == 'grey'?'selected':'').' value="grey">
                            '.$this->l('Grey').'
                        </option>
                    </select></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Size button').' <sup>*</sup></label>
                <div class="margin-form">
                    <p><select name="mpstyle[b_size]">
                        <option '.(isset(self::$mp_style['b_size']) && self::$mp_style['b_size'] == 'L'?'selected':'').' value="L">
                            '.$this->l('Large').'
                        </option>
                        <option '.(isset(self::$mp_style['b_size']) && self::$mp_style['b_size'] == 'M'?'selected':'').' value="M">
                            '.$this->l('Medium').'
                        </option>
                        <option '.(isset(self::$mp_style['b_size']) && self::$mp_style['b_size'] == 'S'?'selected':'').' value="S">
                            '.$this->l('Small').'
                        </option>
                    </select></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Shape style button').' <sup>*</sup></label>
                <div class="margin-form">
                    <p><select name="mpstyle[b_shape]">
                        <option '.(isset(self::$mp_style['b_shape']) && self::$mp_style['b_shape'] == 'Sq'?'selected':'').' value="Sq">
                            '.$this->l('Square').'
                        </option>
                        <option '.(isset(self::$mp_style['b_shape']) && self::$mp_style['b_shape'] == 'Rn'?'selected':'').' value="Rn">
                            '.$this->l('Rounded').'
                        </option>
                        <option '.(isset(self::$mp_style['b_shape']) && self::$mp_style['b_shape'] == 'Ov'?'selected':'').' value="Ov">
                            '.$this->l('Oval').'
                        </option>
                    </select></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Font style button').' <sup>*</sup></label>
                <div class="margin-form">
                    <p><select name="mpstyle[b_font]">
                        <option '.(isset(self::$mp_style['b_font']) && self::$mp_style['b_font'] == 'Ar'?'selected':'').' value="Ar">
                            '.$this->l('Arial').'
                        </option>
                        <option '.(isset(self::$mp_style['b_font']) && self::$mp_style['b_font'] == 'Tr'?'selected':'').' value="Tr">
                            '.$this->l('Trebuchet').'
                        </option>
                        <option '.(isset(self::$mp_style['b_font']) && self::$mp_style['b_font'] == 'Ge'?'selected':'').' value="Ge">
                            '.$this->l('Georgia').'
                        </option>
                    </select></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Logos in button').' <sup>*</sup></label>
                <div class="margin-form">
                    <p><select name="mpstyle[b_logo]">
                        <option '.(isset(self::$mp_style['b_logo']) && self::$mp_style['b_logo'] == 'All'?'selected':'').' value="All">
                            '.$this->l('All payment methods').'
                        </option>
                        <option '.(isset(self::$mp_style['b_logo']) && self::$mp_style['b_logo'] == 'On'?'selected':'').' value="On">
                            '.$this->l('Only for immediate accreditation payment methods').'
                        </option>
                    </select></p>
                </div>
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Refound money if the order has one of these status').' [CTRL+click] <sup>*</sup></label>
                <div class="margin-form">
                    <p><select id="status_refound" name="status_refound[]" multiple style="height:100px">';
        foreach ($order_states as &$l) {
            $str .= '<option '.(in_array((int)$l['id_order_state'], self::$mp_status_refound)?'selected':'').' 
                value="'.$l['id_order_state'].'">'.$l['name'].'</option>';
        }
        $str .= '</select></p>
                </div>';
        if (self::$mp && self::$mp_me && isset(self::$mp_me['shipping_modes'])) {
            if (!in_array('me2', self::$mp_me['shipping_modes'])) {
                $str .= '
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('MercadoEnvios').' <sup>*</sup> ('.$this->l('This option not work in Test Mode').')
                    </label>
                    <div class="margin-form">
                    <p><b>
                    <a href="'.self::MPENVIOS_REGISTER_URL.'" target="_blank" style="color:red">
                        '.$this->l('Click here for active MercadoEnvios in your account.').'
                    </a></b></p>
                    <br class="clear" style="clear:both" />
                    <p>
                    <a href="javascript:void(location.reload(true))">
                        '.$this->l('Reload this page if you have already registered successfully in MercadoEnvios.').'
                    </a></p>
                </div>';
            } else {
                $str .= '
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('MercadoEnvios').' <sup>*</sup> ('.$this->l('This option not work in Test Mode').')
                    </label>
                    <div class="margin-form">
                    <p><select name="shipping_active" id="shipping_active">
                        <option '.(self::$mp_shipping_active?'selected':'').' value="1">'.$this->l('Active').'
                        </option>
                        <option '.(!self::$mp_shipping_active?'selected':'').' value="0">'.$this->l('Deactivated').'
                        </option>
                    </select></p>
                </div>
                <div id="mp_shipping">
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('Size package mode').' <sup>*</sup></label>
                    <div class="margin-form">
                        <p><select name="shipping_mode">
                        <option '.(self::$mp_shipping_calc_mode == 'sum_side'?'selected':'').' value="sum_side">
                            '.$this->l('Adding all sides of each product.').'</option>
                        <option '.(self::$mp_shipping_calc_mode == 'longer_side'?'selected':'').' value="longer_side">
                            '.$this->l('Use the longer sides of each product.').'</option>
                        </select></p>
                    </div>
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('Weight default product (In grams):').'<sup>*</sup></label>
                    <div class="margin-form">
                        <input type="text" size="20" name="weight"
                            value="'.Tools::htmlentitiesUTF8(self::$mp_default_weight).'" />
                        <p>'.$this->l('Only numbers value, for example:').' 7</p>
                    </div>
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('Width default product (In centimeters):').'<sup>*</sup></label>
                    <div class="margin-form">
                        <input type="text" size="20" name="width" 
                            value="'.Tools::htmlentitiesUTF8(self::$mp_default_width).'" />
                        <p>'.$this->l('Only numbers value, for example:').' 7</p>
                    </div>
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('Height default product (In centimeters):').'<sup>*</sup></label>
                    <div class="margin-form">
                        <input type="text" size="20" name="height" 
                            value="'.Tools::htmlentitiesUTF8(self::$mp_default_height).'" />
                        <p>'.$this->l('Only numbers value, for example:').' 7</p>
                    </div>
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('Depth default product (In centimeters):').'<sup>*</sup></label>
                    <div class="margin-form">
                        <input type="text" size="20" name="depth" 
                            value="'.Tools::htmlentitiesUTF8(self::$mp_default_depth).'" />
                        <p>'.$this->l('Only numbers value, for example:').' 7</p>
                    </div>
                    <br class="clear" style="clear:both" />
                    <label>'.$this->l('Free Shipping for').' <sup>*</sup></label>
                    <div class="margin-form">
                        <p><select name="free_shipping">
                            <option value="">'.$this->l('Disable free shipping').'</option>';

                $mp_class = self::MP_CLASS;
                $shippings = $mp_class::get_shipping_list(self::MP_SHIPPING_COUNTRY);
                foreach ($shippings as $shipping) {
                    if ($shipping['site_id'] == self::MP_SHIPPING_COUNTRY) {
                        if ($shipping['status'] == 'active' && ($shipping['shipping_modes'] && !in_array('me2', $shipping['shipping_modes']))) {
                            continue;
                        }
                    }
                    $str .= '<option '.(self::$mp_free_shipping == $shipping['id']?'selected':'');
                    $str .= ' value="'.$shipping['id'].'">'.$shipping['name'].'</option>';
                }
                $str .= '    </select></p>
                    </div>
                </div>
                <script>
                    '.(!self::$mp_shipping_active?'$("#mp_shipping").hide();':'').'
                    $("#shipping_active").change(function(){
                        if ($("#shipping_active").val()*1) {
                            $("#mp_shipping").show();
                        } else {
                            $("#mp_shipping").hide();
                        }
                    });
                </script>';
            }
        } else {
            $str .= '
                <br class="clear" style="clear:both" />
                <label>'.$this->l('MercadoEnvios').' <sup>*</sup>
                </label>
                <div class="margin-form">
                <p><b style="color:red">
                    '.$this->l('You must configure the client_id and client_secret to activate MercadoEnvios.').'
                </b></p>
            </div>';
        }
        $str .= '
                <br class="clear" style="clear:both" />
                <label>'.$this->l('Test mode').' <sup>*</sup></label>
                <div class="margin-form">
                    <p><select name="sandbox">
                        <option value="0">'.$this->l('Deactivated').'</option>
                        <option value="1" '.(self::$mp_sandbox_active?'selected':'').'>'.$this->l('Active').'</option>
                        </select></p>
                    <p>
                        <p>'.$this->l('According to the payment method you use, you can generate the simulated payment report with a particular state.').'</p>
                        <p>'.$this->l('Choose between different means of payment and generates a specific answer:').'</p>
                        <ul class="bulleted">
                            <li>
                                <p>'.$this->l('<strong> Money note: </strong> The amount of money in mind is fixed. Does not end if you use more than one payment and does not affect your actual balance.').'</p>
                                <p>'.$this->l('To test, enter any key and the state will:').' <b>'.$this->l('approved').'</b>.</p>
                            </li>
                            <li>
                                <p>'.$this->l('<strong>Credit cards:</strong> You can use any Security Code and Expire Date.').'</p>
                                <p>'.$this->l('To test, select one of the following cards according to the state you want to obtain:').'</p>
                                <ul class="squared">
                                    <li><p>Visa Nº 4444444444440008: <b>'.$this->l('approved').'</b>.</p></li>
                                    <li><p>Mastercard Nº 5031111111116601: <b>'.$this->l('pending').'</b>.</p></li>
                                </ul>
                            </li>
                            <li>
                                <p><strong>'.$this->l('Ticket deposit or coupon:').'</strong></p>
                                <p>'.$this->l('When testing, you\'ll get the state').' <b>'.$this->l('pending').'
                                </b>.</p>
                            </li>
                        </ul>
                    </p>
                </div>
                <br /><center><input type="submit" name="submitModule" value="'.$this->l('Save Change').'"
                    class="button" /></center>
            </fieldset>
        </form>';
        return $str;
    }

    public function hookPayment($params)
    {
        if (self::$mp_fee === false || self::$mp === false) {
            return '';
        }

        $currency = new Currency((int)$params['cart']->id_currency);
        //$lang = new Language((int)$params['cart']->id_lang);
        $customer = new Customer((int)$params['cart']->id_customer);
        $address = new Address((int)$params['cart']->id_address_invoice);
        //$country = new Country((int)$address->id_country, (int)$params['cart']->id_lang);
        //$products = $params['cart']->getProducts();
        $fee = 100.0 / (100.0 - (float)self::$mp_fee);

        //$url = (_PS_VERSION_ < '1.5') ? 'order-confirmation.php' : 'index.php?controller=order-confirmation';
        $total_price = 0;
        $shipping_price = 0;
        $is_mp_envios = false;
        if (self::$mp_shipping_active && isset(self::$mp_shippings['ps_mp'][$params['cart']->id_carrier])) {
            $total_price = $params['cart']->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
            $shipping_price = $params['cart']->getOrderTotal(true, Cart::BOTH) - $total_price;
            $is_mp_envios = true;
        } else {
            $total_price = $params['cart']->getOrderTotal(true, Cart::BOTH);
        }
        $preference_data = array(
            'items' => array(
                array(
                    'id' => $params['cart']->id,
                    'title' => "Carrito No: {$params['cart']->id} - ".$address->firstname.' '.$address->lastname,
                    'quantity' => 1,
                    'currency_id' => $currency->iso_code,
                    'unit_price' => $fee * $total_price
                )
            ),
            'back_urls' => array(
                'success'=> self::$site_url.'modules/'.$this->name.'/return.php',
                'failure'=> self::$site_url.'modules/'.$this->name.'/return.php',
                'pending'=> self::$site_url.'modules/'.$this->name.'/return.php'
            ),

            'external_reference' => $params['cart']->id,
            'payer'=> array(
                'email' => $customer->email,
                'name' =>  $customer->firstname.' '.$customer->lastname
            )
        );
        if (!self::$mp_sandbox_active && self::$mp_shipping_active && $is_mp_envios) {
            $address = new Address($params['cart']->id_address_delivery);
            $preference_data['shipments'] = array();
            $preference_data['shipments']['mode'] = 'me2';
            $preference_data['shipments']['local_pickup'] = false;
            $products = $params['cart']->getProducts();
            $dim = $this->getWebserviceShippingDim($products);
            $preference_data['shipments']['dimensions'] = "{$dim['width']}x{$dim['height']}x{$dim['depth']},{$dim['weight']}";
            $preference_data['shipments']['zip_code'] = preg_replace('/[^0-9]/', '', $address->postcode);
            $preference_data['shipments']['default_shipping_method'] = self::$mp_shippings['ps_mp'][$params['cart']->id_carrier];
            $preference_data['shipments']['free_methods'] = array();
            if ($shipping_price < 0.1) {
                //foreach (self::$mp_shippings['ps_mp'] as $cid) {
                    //$preference_data['shipments']['free_methods'][] = array('id' => (int)$cid);
                    $preference_data['shipments']['free_methods'][] = array('id' => (int)self::$mp_shippings['ps_mp'][$params['cart']->id_carrier]);
                //}
            } elseif (self::$mp_free_shipping && Tools::strlen(self::$mp_free_shipping) > 0) {
                $preference_data['shipments']['free_methods'][] = array('id' => (int)self::$mp_free_shipping);
                if (self::$mp_free_shipping == self::$mp_shippings['ps_mp'][$params['cart']->id_carrier]) {
                    $shipping_price = 0;
                }
            }
        }
        if (!empty(self::$mp_payments) && is_array(self::$mp_payments)) {
            foreach (self::$mp_payments as $k => $v) {
                if ((int)$v) {
                    $preference_data['payment_methods']['excluded_payment_types'][] = array('id' => $k);
                }
            }
        }

        $mp_params = array();

        try {
            $preference = self::$mp->create_preference($preference_data);
            if ($preference['status'] >= 400) {
                $mp_params['error'] = $this->l('Error conectando a MercadoPago: ').' ['.$preference['status'].']
                - '.$preference['response']['message'];
            }
        } catch (Exception $error) {
            self::log('ERROR-create_preference: '.print_r($error, true));
            $mp_params['error'] = $this->l('Error conectando a MercadoPago: ').' ['.$error->getCode().']
                - '.$error->getMessage();
        }

        $mp_params['price']         = $fee * $total_price;
        $mp_params['is_mp_envios']  = !self::$mp_sandbox_active && self::$mp_shipping_active && $is_mp_envios;
        $mp_params['item_id']       = $params['cart']->id;
        $mp_params['back_url']      = self::$site_url.'modules/'.$this->name.'/return.php';
        $mp_params['modal']         = self::$mp_modal_active;
        $mp_params['mpstyle']       = self::$mp_style;
        $mp_params['init_point']    = self::$mp_sandbox_active?$preference['response']['sandbox_init_point']:$preference['response']['init_point'];

        $this->context->smarty->assign($mp_params);
        $this->context->smarty->assign('fee', self::$mp_fee);
        $this->context->smarty->assign('feeTotal', $mp_params['price'] - $total_price);
        $this->context->smarty->assign('shippingTotal', $shipping_price);
        $this->context->smarty->assign('newPrice', $mp_params['price'] + $shipping_price);

        return $this->display(__FILE__, 'views/templates/hook/mp.tpl');
    }

    public function hookUpdateOrderStatus($params)
    {
        if (!self::$mp) {
            return '';
        }

        if (in_array((int)$params['newOrderStatus']->id, self::$mp_status_refound)) {
            $id_order = $params['id_order'];
            $has_mp_order = Db::getInstance()->ExecuteS('SELECT *
                                                FROM `'._DB_PREFIX_.self::MP_NAME.'`
                                                WHERE `id_order` = '.(int)$id_order);
            if ($has_mp_order && isset($has_mp_order[0])) {
                if (isset($has_mp_order[0])) {
                    $has_mp_order = $has_mp_order[0];
                }
                $has_refund = Db::getInstance()->ExecuteS('SELECT *
                                                FROM `'._DB_PREFIX_.self::MP_NAME.'_refunds`
                                                WHERE `id_order` = '.(int)$id_order);
                if (!$has_refund) {
                    $payment = $this->validateMercadoPago($has_mp_order['topic'], $has_mp_order['mp_op_id']);
                    switch ($payment['status']) {
                        case (int)Configuration::get(self::MP_PREFIX.'OS_AUTHORIZATION'):
                        case (int)Configuration::get('PS_OS_PREPARATION'):
                        case (int)Configuration::get('PS_OS_SHIPPING'):
                        case (int)Configuration::get('PS_OS_DELIVERED'):
                            try {
                                $result = self::$mp->refund_payment($has_mp_order['mp_op_id']);
                                if ($result['status'] >= 400) {
                                    self::log('ERROR-refund_payment: '.print_r($result, true));
                                    return '';
                                }
                                Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.self::MP_NAME.'_refunds` 
                                    (id_order, mp_op_id, date_create, response) VALUES
                                    (   '.(int)$id_order.', 
                                        \''.pSQL($has_mp_order['mp_op_id']).'\',
                                        NOW(),
                                        \''.pSQL(Tools::jsonEncode($result)).'\'
                                    )');
                                self::log('refund_payment: '.print_r($has_mp_order, true).' -> '.print_r($result, true));
                            } catch (Exception $error) {
                                self::log('Refound error: '.print_r($has_mp_order, true).' -> '.print_r($error, true));
                            }
                            break;
                        default:
                            return '';
                    }
                }
            }
        }
        return '';
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        if (!self::$mp || !self::$mp_shipping_active) {
            return 0;
        }
        $id_country = Country::getByIso(self::MP_ISO_COUNTRY);
        $address = new Address($params->id_address_delivery);
        $delivery = false;
        if (!Validate::isLoadedObject($address)) {
            $delivery = $this->context->cookie->postcode;
        } else {
            $delivery = $address->postcode;
            if ($id_country != $address->id_country) {
                return 0;
            }
        }
        $products = $params->getProducts();
        $dim = $this->getWebserviceShippingDim($products);
        $fee = 100.0 / (100.0 - (float)self::$mp_fee);
        $params_mp = array(
            'dimensions' => "{$dim['width']}x{$dim['height']}x{$dim['depth']},{$dim['weight']}",
            'zip_code' => preg_replace('/[^0-9]/', '', $delivery),
            'item_price' => Tools::ps_round($params->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING) * $fee, 2),
            'free_method' => '', //self::$mp_free_shipping, se calcula el precio igual.
            //la diferencia se calcula en el boton de pago.
        );
        if ($params_mp['item_price'] < 1.0) {
            $params_mp['item_price'] = 1.0;
        }
        $currency = new Currency((int)$params->id_currency);
        $params_mp['item_price'] = Tools::ps_round($params_mp['item_price'] * $this->getRate($currency->iso_code, self::MP_CURRENCY_SHIPPING), 2);
        $response = false;
        $cache_id = 'get_shipping_price_'.md5(var_export($params_mp, true));
        if ($response = self::getCache($cache_id)) {
            self::log('get_shipping_price: '.var_export($params_mp, true).' -> from cache: '.$cache_id);
        } else {
            try {
                $response = self::$mp->get_shipping_price($params_mp);
                self::setCache($cache_id, $response);
                self::log('get_shipping_price[1-'.$cache_id.']: 
                    '.print_r($params_mp, true).'
                    '.print_r($products, true).'
                    -> '.print_r($response, true));
            } catch (Exception $e) {
                self::log('ERROR-getOrderShippingCost-get_shipping_price: '.print_r($e, true));
                return 0.0;
            }
        }
        if (isset($response['response']) && isset($response['response']['options'])) {
            $shipping_options = $response['response']['options'];
            $is_new_carrier = false;
            foreach ($shipping_options as $shipping_option) {
                if (isset(self::$mp_shippings['mp_ps'][$shipping_option['shipping_method_id']])) {
                    $carrier = new Carrier(self::$mp_shippings['mp_ps'][$shipping_option['shipping_method_id']]);
                    if (Validate::isLoadedObject($carrier) && !$carrier->deleted) {
                        continue;
                    }
                    unset(self::$mp_shippings['ps_mp'][self::$mp_shippings['mp_ps'][$shipping_option['shipping_method_id']]]);
                    unset(self::$mp_shippings['mp_ps'][$shipping_option['shipping_method_id']]);
                }
                $id_country = Country::getByIso(self::MP_ISO_COUNTRY);
                $id_zone = array();
                $id_zone[] = (int)Country::getIdZone($id_country);
                $states = State::getStatesByIdCountry($id_country);
                if ($states) {
                    foreach ($states as $state) {
                        if ((int)$state['id_zone'] > 0 && !in_array((int)$state['id_zone'], $id_zone)) {
                            $id_zone[] = (int)$state['id_zone'];
                        }
                    }
                }
                $carrierConfig = array(
                    'name' => $shipping_option['name'],
                    'url' => '',
                    'id_tax_rules_group' => 0,
                    'deleted' => 0,
                    'shipping_handling' => false,
                    'range_behavior' => 0,
                    'delay' => array('es' => 'Cálculo Dinámico', 'en' => 'Dynamic calculation'),
                    'id_zone' => $id_zone,
                    'is_module' => true,
                    'shipping_external' => true,
                    'external_module_name' => self::MP_NAME,
                    'need_range' => true,
                    'active' => true
                );
                $id_carrier = self::installExternalCarrier($carrierConfig);
                if (!$id_carrier || $id_carrier < 1) {
                    self::log('Failed to create the carrier MercadoEnvios');
                } else {
                    self::$mp_shippings['mp_ps'][$shipping_option['shipping_method_id']] = $id_carrier;
                    self::$mp_shippings['ps_mp'][$id_carrier] = $shipping_option['shipping_method_id'];
                    self::log('New Carrier['.$id_carrier.']: '.print_r($carrierConfig, true));
                    $is_new_carrier = true;
                }
            }
            if ($is_new_carrier) {
                self::refreshCarrierList(self::$mp_shippings);
            }
            foreach ($shipping_options as $shipping_option) {
                if ($this->id_carrier == self::$mp_shippings['mp_ps'][$shipping_option['shipping_method_id']]) {
                    return $shipping_option['cost'] * $this->getRate($shipping_option['currency_id'], $currency->iso_code);
                }
            }
        }
        return 0;
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params, 0);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        return $this->hookBackOfficeHeader($params);
    }

    public function hookBackOfficeHeader($params)
    {
        $js = '<script>var id_carriers = ['.implode(',', self::$mp_shippings['mp_ps']).'];';
        $js .= '$(document).ready(function(){';
        if (version_compare(_PS_VERSION_, '1.5.0.0') >= 0) {
            $js .= '$("table.carrier tr").each(function() {
                var tr_id = $(this).attr("id");
                for(var i in id_carriers) {
                    var re = new RegExp("tr_[0-9]+_"+id_carriers[i]+"_[0-9]+");
                    if ((tr_id+"").match(re)) {
                        $("#"+tr_id+" > td").first().html("");
                        $("#"+tr_id+" > td").last().html("");
                        $("#"+tr_id).attr("onclick", "");
                        $("#"+tr_id+" > td").attr("onclick", "");
                    }
                }
            });';
        } else {
            $js .= '$("table tr", $("#submitFiltercarrier").parent("form")).each(function(){ 
                if (id_carriers.indexOf(1*$($("td", this)[1]).text()) >= 0) {
                    $("td", this).first().html("");
                    $("td", this).last().html("");
                    $("td", this).attr("onclick", "");
                    $("td", this).attr("onclick", "");
                }
            });';
        }
        $js .= '});</script>';
        return $js;
    }

    public function hookBeforeCarrier($params)
    {
        return $this->hookDisplayBeforeCarrier($params);
    }

    public function hookDisplayBeforeCarrier($params)
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0') >= 0) {
            return;
        }
        $carriers = $params['carriers'];
        $cart = $params['cart'];
        $id_country = Country::getByIso(self::MP_ISO_COUNTRY);
        $address = new Address($cart->id_address_delivery);
        $delivery = false;
        if (!Validate::isLoadedObject($address) || $id_country != $address->id_country || !$this->active || self::$mp_sandbox_active || !self::$mp || !self::$mp_shipping_active) {
            $carriers_return = array();
            foreach ($carriers as $carrier) {
                if (!in_array($carrier['id_carrier'], self::$mp_shippings['mp_ps'])) {
                    $carriers_return[] = $carrier;
                }
            }
            $this->context->smarty->assign(array('carriers' => $carriers_return));
            return;
        }

        $delivery = false;
        if (!Validate::isLoadedObject($address)) {
            $delivery = Context::getContext()->cookie->postcode;
        } else {
            $delivery = $address->postcode;
        }
        $delivery = preg_replace('/[^0-9]/', '', $delivery);
        $products = $cart->getProducts();
        $dim = $this->getWebserviceShippingDim($products);
        $fee = 100.0 / (100.0 - (float)self::$mp_fee);
        $params_mp = array(
            'dimensions' => "{$dim['width']}x{$dim['height']}x{$dim['depth']},{$dim['weight']}",
            'zip_code' => $delivery,
            'item_price' => Tools::ps_round($cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING) * $fee, 2),
            'free_method' => '',
            //self::$mp_free_shipping, se calcula el precio igual.
            //la diferencia se calcula en el boton de pago.
        );
        if ($params_mp['item_price'] < 1.0) {
            $params_mp['item_price'] = 1.0;
        }
        $currency = new Currency((int)$cart->id_currency);
        $params_mp['item_price'] = Tools::ps_round($params_mp['item_price'] * $this->getRate($currency->iso_code, self::MP_CURRENCY_SHIPPING), 2);
        $response = false;
        if (Tools::strlen($delivery) > 2) {
            $cache_id = 'get_shipping_price_'.md5(var_export($params_mp, true));
            if ($response = self::getCache($cache_id)) {
                self::log('get_shipping_price: '.print_r($params_mp, true).' 
                            -> from cache: '.$cache_id);
            } else {
                try {
                    $response = self::$mp->get_shipping_price($params_mp);
                    self::setCache($cache_id, $response);
                    self::log('get_shipping_price[2-'.$cache_id.']:
                        '.print_r($params_mp, true).'
                        '.print_r($products, true).'
                        -> '.print_r($response, true));
                } catch (Exception $e) {
                    self::log('ERROR-getDeliveryOptionList-get_shipping_price: '.print_r($e, true));
                    $carriers_return = array();
                    foreach ($carriers as $carrier) {
                        if (!in_array($carrier['id_carrier'], self::$mp_shippings['mp_ps'])) {
                            $carriers_return[] = $carrier;
                        }
                    }
                    $this->context->smarty->assign(array('carriers' => $carriers_return));
                    return;
                }
            }
        }
        $delay_days = array();
        $delays = array();
        if ($response && isset($response['response']) && isset($response['response']['options'])) {
            $shipping_options = $response['response']['options'];
            foreach ($shipping_options as $shipping_option) {
                $delays[] = $shipping_option['shipping_method_id'];
                $delay_days[$shipping_option['shipping_method_id']] = Tools::ps_round($shipping_option['speed']['shipping'] / 24, 0);
            }
        }
        $carriers_return = array();
        foreach ($carriers as $carrier) {
            $id_carrier = $carrier['id_carrier'];
            if (!in_array($id_carrier, self::$mp_shippings['mp_ps'])) {
                $carriers_return[] = $carrier;
            } elseif (in_array(self::$mp_shippings['ps_mp'][$id_carrier], $delays)) {
                $d = $delay_days[self::$mp_shippings['ps_mp'][$id_carrier]].' - ';
                $d .= ($delay_days[self::$mp_shippings['ps_mp'][$id_carrier]] + 1).' ';
                $d .= $this->l('days').'.';
                $carrier['delay'] = $d;
                $carriers_return[] = $carrier;
            }
        }
        $this->context->smarty->assign(array('carriers' => $carriers_return));
    }

    public function getWebserviceShippingDim(&$products)
    {
        $width = 0;
        $height = 0;
        $depth = 0;
        $weight = 0;

        foreach ($products as &$product) {
            if ($product['weight']) {
                if (self::$weightUnit == 'KGS') {
                    $product['weight2'] = $product['weight'] * 1000;
                } elseif (self::$weightUnit == 'LBS') {
                    $product['weight2'] = $product['weight'] * 453.59237;
                } else {
                    $product['weight2'] = 0;
                }
            } else {
                $product['weight2'] = 0;
            }
            if (self::$dimensionUnit == 'CM') {
                $product['width2'] = $product['width'];
                $product['height2'] = $product['height'];
                $product['depth2'] = $product['depth'];
            } elseif (self::$dimensionUnit == 'IN') {
                $product['width2'] = $product['width'] * 2.54;
                $product['height2'] = $product['height'] * 2.54;
                $product['depth2'] = $product['depth'] * 2.54;
            } else {
                $product['width2'] = 0;
                $product['height2'] = 0;
                $product['depth2'] = 0;
            }
        }
        if (self::$mp_shipping_calc_mode == 'longer_side') {
            foreach ($products as $p) {
                if ($p['width2'] && $p['width2'] > $width) {
                    $width = $p['width2'];
                }
                if ($p['height2'] && $p['height2'] > $height) {
                    $height = $p['height2'];
                }
                if ($p['depth2'] && $p['depth2'] > $depth) {
                    $depth = $p['depth2'];
                }
                if ($p['weight2']) {
                    $weight += ($p['weight2'] * $p['quantity']);
                } else {
                    $weight += self::$mp_default_weight;
                }
            }
        } else {
            foreach ($products as $p) {
                $width += ($p['width2'] > 0.01 ? $p['width2'] : self::$mp_default_width) * $p['quantity'];
                $height += ($p['height2'] > 0.01 ? $p['height2'] : self::$mp_default_height) * $p['quantity'];
                $depth += ($p['depth2'] > 0.01 ? $p['depth2'] : self::$mp_default_depth) * $p['quantity'];
                $weight += ($p['weight2'] > 0.1 ? $p['weight2'] : self::$mp_default_weight) * $p['quantity'];
            }
        }
        return array(
            'width' => Tools::ps_round($width > 0.01 ? $width : self::$mp_default_width, 0),
            'height' => Tools::ps_round($height > 0.01 ? $height : self::$mp_default_height, 0),
            'depth' => Tools::ps_round($depth > 0.01 ? $depth : self::$mp_default_depth, 0),
            'weight' => Tools::ps_round($weight > 0.1 ? $weight : self::$mp_default_weight, 0),
        );
        
    }

    public function lang($str)
    {
        return $this->l($str);
    }

    protected function getWarningMultishopHtml()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return '<p class="alert alert-warning">'.
                        $this->l('You cannot change setting from a "All Shops" or a "Group Shop" context, select directly the shop you want to edit').
                    '</p>';
        } else {
            return '';
        }
    }

    protected function getShopContextError()
    {
        return '<p class="alert alert-danger">'.
                            sprintf($this->l('You cannot edit setting from a "All Shops" or a "Group Shop" context')).
                    '</p>';
    }
    public static function getCache($cache_id)
    {
        $data = false;
        if (isset(self::$mp_cache[$cache_id]) && ($data = self::$mp_cache[$cache_id])) {
            return $data;
        }
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_) {
            $cache = Cache::getInstance();
            if ($data = $cache->get($cache_id)) {
                return $data;
            }
        }
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.self::MP_NAME.'_cache`
                WHERE ttl < '.(int)time());
        $d = Db::getInstance()->getValue('SELECT `data` FROM `'._DB_PREFIX_.self::MP_NAME.'_cache`
                WHERE `cache_id` = \''.pSQL($cache_id).'\'');
        if ($d) {
            $data = unserialize($d);
        }
        return $data;
    }
    public static function setCache($cache_id, $value, $ttl = 21600)
    {
        self::$mp_cache[$cache_id] = $value;
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_) {
            $cache = Cache::getInstance();
            if ($cache->set($cache_id, $value, $ttl)) {
                return true;
            }
        }
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.self::MP_NAME.'_cache`
                WHERE ttl < '.(int)time().' OR cache_id = \''.pSQL($cache_id).'\'');
        return Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.self::MP_NAME.'_cache`
                    (`cache_id`, `data`, `ttl`) VALUES
                    (\''.pSQL($cache_id).'\',
                     \''.pSQL(serialize($value)).'\',
                     '.(int)(time() + $ttl).')');
    }
}
