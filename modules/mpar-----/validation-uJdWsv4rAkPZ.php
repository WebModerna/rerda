<?php
/**
* Capturador de IPN
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*/

$mp = null;
$mp_class_name = null;

include(dirname(__FILE__).'/files.php');

if (!$mp) {
    if (_PS_VERSION_ >= '1.5') {
        Tools::redirect('index.php?controller=cart');
    } else {
        Tools::redirect('cart.php');
    }
    exit;
}

header('Content-type: text/plain');

$rand_mutex = rand();
$mp->log('validation['.$rand_mutex.']: '.print_r($_GET, true).print_r($_POST, true));
$result = $mp->validateMercadoPago(Tools::getValue('topic'), Tools::getValue('id'));

if (!Tools::getValue('id')) {
    die('ID Desconocido');
}

$mutex = new MpMutex('mp_validation_'.$mp_class_name);

$mp->log('validation-['.$rand_mutex.']: init lock mp_validation_'.$mp_class_name.' - '.Tools::getValue('topic').Tools::getValue('id'));

while (!$mutex->lock()) {
    sleep(.5);
}

$mp->log('validation-['.$rand_mutex.']: lock '.Tools::getValue('topic').Tools::getValue('id'));

if ($result['status'] && $result['order_id']) {
    $id_cart = (int)$result['order_id'];
    $cart = new Cart($id_cart);
    if (!Validate::isLoadedObject($cart)) {
        $mutex->releaseLock();
        exit;
    }
    
    $mp->log('validation-['.$rand_mutex.']: cart-'.$id_cart.' '.Tools::getValue('topic').Tools::getValue('id'));

    $customer = new Customer((int)$cart->id_customer);
    $status_act = $result['status'];

    $id_order = Order::getOrderByCartId($id_cart);
    $currency = new Currency((int)$cart->id_currency);

    $context = Context::getContext();
    $context->cart = $cart;
    $context->cookie->id_currency = (int)$cart->id_currency;
    $context->customer = $customer;
    $context->currency = $currency;

    if ($id_order > 0) {
        $mp->log('validation-['.$rand_mutex.']: order-'.$id_order.' '.Tools::getValue('topic').Tools::getValue('id'));
        $order = new Order($id_order);
        $status_mp = (int)Db::getInstance()->getValue('SELECT `status` FROM `'._DB_PREFIX_.$mp_class_name::MP_NAME.'`
                WHERE `id_order` = '.(int)$id_order);
        $status_ps = (int)$order->getCurrentState();
        $cancel_status = array(
            (int)Configuration::get('PS_OS_CANCELED'),
            (int)Configuration::get('PS_OS_ERROR'),
            (int)Configuration::get('PS_OS_PREPARATION'),
            (int)Configuration::get('PS_OS_DELIVERED'),
            (int)Configuration::get('PS_OS_REFUND'),
            (int)Configuration::get('PS_OS_ERROR'),
            (int)Configuration::get('PS_OS_OUTOFSTOCK'),
            (int)Configuration::get('PS_OS_OUTOFSTOCK_PAID'),
            (int)Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
        );
        if (in_array($status_ps, $cancel_status) || ($status_mp > 0 && $status_mp != $status_ps)) {
            $status_act = $status_ps;
        } else {
            if ($status_act != $status_ps) {
                $order->setCurrentState($status_act);
            }
            Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.$mp_class_name::MP_NAME.'` 
                SET
                    `next_retry` = '.(int)(time() + 6 * 60 * 60).',
                    `status` = '.(int)$status_act.'
                WHERE id_order = '.(int)$id_order);
        }
    } else {
        $msg = $mp->lang('Order Price: ').Tools::ps_round($mp->getRate(Tools::strtoupper($result['currency_id']), Tools::strtoupper($currency->iso_code)) * (float)$result['price'], 2);
        $msg .= ' '.$currency->iso_code.'<br />'."\n";
        $msg .= $mp->lang('Fee Price: ').Tools::ps_round($mp->getRate(Tools::strtoupper($result['currency_id']), Tools::strtoupper($currency->iso_code)) * (float)$result['fee'], 2);
        $msg .= ' '.$currency->iso_code.'<br />'."\n";
        $msg .= $mp->lang('Shipping Price: ').Tools::ps_round($mp->getRate(Tools::strtoupper($result['currency_id']), Tools::strtoupper($currency->iso_code)) * (float)$result['shipping'], 2);
        $msg .= ' '.$currency->iso_code.'<br />'."\n";
        $msg .= $mp->lang('Total Payment: ').Tools::ps_round($mp->getRate(Tools::strtoupper($result['currency_id']), Tools::strtoupper($currency->iso_code)) * (float)$result['total'], 2);
        $msg .= ' '.$currency->iso_code.'<br />'."\n";
        if (Tools::strtoupper($result['currency_id']) != Tools::strtoupper($currency->iso_code)) {
            $msg .= ' '.$mp->lang('The customer payment through');
            $msg .= ' '.Tools::strtoupper($result['currency_id']);
            $msg .= ' '.$mp->lang('and this can generate additional commissions due to currency conversions.');
        }
        $mp->log('validation-['.$rand_mutex.']: validateOrder-'.$id_cart.' '.Tools::getValue('topic').Tools::getValue('id'));
        $mp->validateOrder(
            $id_cart,
            $status_act,
            $cart->getOrderTotal(true, Cart::BOTH),
            $mp->displayName,
            $msg,
            array(),
            (int)$cart->id_currency,
            false,
            $customer->secure_key
        );
        $mp->log('validation-['.$rand_mutex.']: validateOrder-'.$id_cart.'-end '.Tools::getValue('topic').Tools::getValue('id'));
    }
    if (!Validate::isLoadedObject($order)) {
        $order = new Order(Order::getOrderByCartId($id_cart));
    }

    if (Validate::isLoadedObject($order)) {
        Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.$mp_class_name::MP_NAME.'` (id_order, id_shop, 
                    `topic`, mp_op_id, status, next_retry)
                        VALUES
                    ('.(int)$order->id.', '.(int)$order->id_shop.', 
                     \''.pSQL(Tools::getValue('topic')).'\', 
                     \''.pSQL(Tools::getValue('id')).'\', 
                     '.(int)$order->getCurrentState().', 
                     '.(int)(time() + 6 * 60 * 60).')');
    }
}
$mp->log('validation-['.$rand_mutex.']: releaseLock '.Tools::getValue('topic').Tools::getValue('id'));
$mutex->releaseLock();
