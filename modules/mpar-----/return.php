<?php
/**
* Captura retorno de MercadoPago
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


$rand_mutex = rand();
$mp->log('return['.$rand_mutex.']: '.print_r($_GET, true).print_r($_POST, true));

$result = $mp->validateMercadoPago('payment', Tools::getValue('collection_id'));

$mp->log('return-['.$rand_mutex.']: init lock mp_validation_'.$mp_class_name.' - '.Tools::getValue('external_reference'));
$mutex = new MpMutex('mp_validation_'.$mp_class_name);
while (!$mutex->lock()) {
    sleep(.5);
}
$mp->log('return-['.$rand_mutex.']: lock '.Tools::getValue('external_reference'));

if (Tools::getValue('collection_status')) {
    if (Tools::getValue('collection_status') == 'null') {
        $mp->log('return-['.$rand_mutex.']: releaseLock1 '.Tools::getValue('external_reference'));
        $mutex->releaseLock();
        if (_PS_VERSION_ >= '1.5') {
            Tools::redirect('index.php?controller=cart');
        } else {
            Tools::redirect('cart.php');
        }
        exit;
    }
    $id_cart = (int)Tools::getValue('external_reference');

    $cart = new Cart($id_cart);
    if (!Validate::isLoadedObject($cart)) {
        $mp->log('return-['.$rand_mutex.']: releaseLock2 '.Tools::getValue('external_reference'));
        $mutex->releaseLock();
        Tools::redirect('cart.php');
        exit;
    }

    $id_order = (int)Order::getOrderByCartId($id_cart);

    if ($id_order <= 0) {
        if ($result['status'] && $result['order_id']) {
            if ($id_cart != (int)$result['order_id']) {
                $mp->log('return-['.$rand_mutex.']: releaseLock3 '.Tools::getValue('external_reference'));
                $mutex->releaseLock();
                Tools::redirect('cart.php');
                exit;
            }

            $cart = new Cart($id_cart);
            $customer = new Customer((int)$cart->id_customer);

            $status_act = $result['status'];
            $currency = new Currency((int)$cart->id_currency);
            $context = Context::getContext();
            $context->cart = $cart;
            $context->cookie->id_currency = (int)$cart->id_currency;
            $context->customer = $customer;
            $context->currency = $currency;
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
            $mp->log('return-['.$rand_mutex.']: validateOrder-'.$id_cart.' '.Tools::getValue('external_reference'));
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
            $mp->log('return-['.$rand_mutex.']: validateOrder-'.$id_cart.'-end '.Tools::getValue('external_reference'));

            $order = new Order(Order::getOrderByCartId($id_cart));
            $id_order = $order->id;

            if (Validate::isLoadedObject($order)) {
                Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.$mp_class_name::MP_NAME.'` (id_order, 
                            id_shop, `topic`, mp_op_id, status, next_retry)
                                VALUES
                            ('.(int)$order->id.', 
                             '.(int)$order->id_shop.', 
                             \'payment\', 
                             \''.pSQL(Tools::getValue('collection_id')).'\', 
                             '.(int)$order->getCurrentState().', 
                             '.(int)(time() + 6 * 60 * 60).')');
            }
        }
    }
    if ($id_order > 0) {
        $mp->log('return-['.$rand_mutex.']: releaseLock4 '.Tools::getValue('external_reference'));
        $mutex->releaseLock();
        $order = new Order($id_order);
        $customer = new Customer((int)$cart->id_customer);
        if (_PS_VERSION_ >= '1.5') {
            Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$mp->id.'&id_order='.$order->id.'&key='.$customer->secure_key);
        } else {
            Tools::redirect('order-confirmation.php?id_cart='.$cart->id.'&id_module='.$mp->id.'&id_order='.$order->id.'&key='.$customer->secure_key);
        }
        exit;
    }
}

$mp->log('return-['.$rand_mutex.']: releaseLock5 '.Tools::getValue('external_reference'));
$mutex->releaseLock();
if (_PS_VERSION_ >= '1.5') {
    Tools::redirect('index.php?controller=cart');
} else {
    Tools::redirect('cart.php');
}
