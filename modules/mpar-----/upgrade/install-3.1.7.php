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

function upgrade_module_3_1_7($mp)
{
    if (!$mp || $mp->name != Mpar::MP_NAME) {
        $mp = new Mpar();
    }
    $mp->log('upgrade_module_3_1_7 init...');
    Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Mpar::MP_NAME.'_cache` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `cache_id` varchar(100) NOT NULL,
                `data` LONGTEXT NOT NULL,
                `ttl` INT(11) NOT NULL,
                UNIQUE(cache_id),
                INDEX(ttl)
                )');
    $mp->checkOverride();
    $mp->log('upgrade_module_3_1_7 end!');
    return true;
}
