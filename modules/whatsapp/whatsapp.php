<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class whatsapp extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'whatsapp';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'fyazilim.com';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Whatsapp Chat');
        $this->description = $this->l('Add your number to whatsapp');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
		return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
		return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
		$whatasppno = 0;
		if (((bool)Tools::isSubmit('telekle')) == true) {
			$telefon = Tools::getValue('telefon');
		
			if( Validate::isPhoneNumber($telefon))
			{
				$varmi = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'whatsapp WHERE id_whatsapp = 1');
				if ($varmi)
				{
					Db::getInstance()->update('whatsapp', array(
						'telefon'      => pSQL($telefon),
					), 'id_whatsapp  = 1');
				}
				else
				{
					Db::getInstance()->insert('whatsapp', array(
						'telefon'      => pSQL($telefon),
					));
				}
			}
		}
		$iso_code = $this->context->language->iso_code;
		$numara = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'whatsapp WHERE id_whatsapp = 1');
		$whatasppno = $numara['telefon'];
		$this->context->smarty->assign(array(
			'whatasppno' => $whatasppno,
			'whataspp_module_dir' => $this->_path,
			'lang_iso' => $iso_code,
		));
        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/whatsapp.js');
        $this->context->controller->addCSS($this->_path.'/views/css/whatsapp.css');
    }

    public function hookDisplayFooter()
    {
        $no = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'whatsapp WHERE id_whatsapp = 1');
		$whatasppno = $no['telefon'];
		$this->context->smarty->assign(array(
			'whatasppno' => $whatasppno,
			'whataspp_module_dir' => $this->_path,
		));
		return $this->context->smarty->fetch($this->local_path.'views/templates/front/footer.tpl');
    }
}
