<?php
/*
* 2007-2015 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Datafiscal extends Module
{
	public function __construct()
	{
		$this->name = 'datafiscal';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'help2presta.com';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Datafiscal');
		$this->description = $this->l('Displays a banner at the bottom with fiscal data.');
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		return
			parent::install() &&
			$this->registerHook('displayFooter') &&
			$this->registerHook('displayHeader') &&
			$this->disableDevice(Context::DEVICE_MOBILE);
	}




	public function uninstall()
	{
		Configuration::deleteByName('DATAFISCAL_IMG');
		Configuration::deleteByName('DATAFISCAL_LINK');
		return parent::uninstall();
	}

	public function hookDisplayFooter($params)
	{
		if (!$this->isCached('datafiscal.tpl', $this->getCacheId()))
		{
			$imgname = Configuration::get('DATAFISCAL_IMG', $this->context->language->id);

			if ($imgname && file_exists(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$imgname))
				$this->smarty->assign('banner_img', $this->context->link->protocol_content.Tools::getMediaServer($imgname).$this->_path.'img/'.$imgname);

			$this->smarty->assign(array(
				'banner_link' => Configuration::get('DATAFISCAL_LINK', $this->context->language->id),
			    'banner_class' => Configuration::get('DATAFISCAL_CLASS'),

			));
		}

		return $this->display(__FILE__, 'datafiscal.tpl', $this->getCacheId());
	}

	

	

	public function hookDisplayHeader($params)
	{
		$this->context->controller->addCSS($this->_path.'datafiscal.css', 'all');
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitStoreConf'))
		{
			$languages = Language::getLanguages(false);
			$values = array();
			$update_images_values = false;

			foreach ($languages as $lang)
			{
				if (isset($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']])
					&& isset($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']]['tmp_name'])
					&& !empty($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']]['tmp_name']))
				{
					if ($error = ImageManager::validateUpload($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']], 4000000))
						return $error;
					else
					{
						$ext = substr($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']]['name'], strrpos($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']]['name'], '.') + 1);
						$file_name = md5($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']]['name']).'.'.$ext;

						if (!move_uploaded_file($_FILES['DATAFISCAL_IMG_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$file_name))
							return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
						else
						{
							if (Configuration::hasContext('DATAFISCAL_IMG', $lang['id_lang'], Shop::getContext())
								&& Configuration::get('DATAFISCAL_IMG', $lang['id_lang']) != $file_name)
								@unlink(dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.Configuration::get('DATAFISCAL_IMG', $lang['id_lang']));

							$values['DATAFISCAL_IMG'][$lang['id_lang']] = $file_name;
						}
					}

					$update_images_values = true;
				}

				$values['DATAFISCAL_LINK'][$lang['id_lang']] = Tools::getValue('DATAFISCAL_LINK_'.$lang['id_lang']);
			}

			if ($update_images_values)
				Configuration::updateValue('DATAFISCAL_IMG', $values['DATAFISCAL_IMG']);

			Configuration::updateValue('DATAFISCAL_LINK', $values['DATAFISCAL_LINK']);
			Configuration::updateValue('DATAFISCAL_CLASS', Tools::getValue('DATAFISCAL_CLASS'));

			$this->_clearCache('datafiscal.tpl');
			return $this->displayConfirmation($this->l('The settings have been updated.'));
		}
		return '';
	}

	public function getContent()
	{
		return $this->_displayInfo().$this->postProcess().$this->renderForm();
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'file_lang',
						'label' => $this->l('Image'),
						'name' => 'DATAFISCAL_IMG',
						'desc' => $this->l('Upload a image for the fiscal data'),
						'lang' => true,
					),
					array(
						'type' => 'text',
						'lang' => true,
						'label' => $this->l('Afip Link'),
						'name' => 'DATAFISCAL_LINK',
						'desc' => $this->l('Enter the link like https://servicios1.afip.gov.ar/clavefiscal/qr/response.aspx?qr=ZcZiXycAWJ,,')
					),
					array(
						'type' => 'text',
						'label' => $this->l('CSS Class'),
						'name' => 'DATAFISCAL_CLASS',
						'desc' => $this->l('Set a custom class to add to the block (add in datafiscal.css file)')
					),
				
				),
				'submit' => array(
					'title' => $this->l('Save')
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->module = $this;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitStoreConf';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'uri' => $this->getPathUri(),
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}
  private function _displayInfo()
    {
      
	   
        return $this->display(
            __FILE__,
            'views/templates/hook/infos.tpl'
        );
    }
	public function getConfigFieldsValues()
	{
		$languages = Language::getLanguages(false);
		$fields = array();

		foreach ($languages as $lang)
		{
			$fields['DATAFISCAL_IMG'][$lang['id_lang']] = Tools::getValue('DATAFISCAL_IMG_'.$lang['id_lang'], Configuration::get('DATAFISCAL_IMG', $lang['id_lang']));
			$fields['DATAFISCAL_LINK'][$lang['id_lang']] = Tools::getValue('DATAFISCAL_LINK_'.$lang['id_lang'], Configuration::get('DATAFISCAL_LINK', $lang['id_lang']));

		}
            $fields['DATAFISCAL_CLASS'] = Tools::getValue(('DATAFISCAL_CLASS'), Configuration::get('DATAFISCAL_CLASS'));

		return $fields;
	}
}
