<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BetterContactForm extends Module
{
	public function __construct() {
		$this->name = 'bettercontactform';
		if (version_compare(_PS_VERSION_, '1.4.0.0') >= 0)
			$this->tab = 'front_office_features';
		else
			$this->tab = 'Blocks';
		$this->version = '1.0.0';

		parent::__construct();

		$this->displayName = $this->l('Better Contact Form');
		$this->description = $this->l('Better Contact Form helps your customers comfortable to send message quickly and easily on any devices (desktop, laptop, smartphone, tablet...)');
	}
	
	public function install() {
		return (parent::install()
			&& Configuration::updateValue('bettercontactform_enabled', true)
			&& Configuration::updateValue('bettercontactform_script', '')
			&& $this->registerHook('displayFooter'));
	}
	
	public function uninstall() {
		//Delete configuration
		return (Configuration::deleteByName('bettercontactform_enabled')
			&& Configuration::deleteByName('bettercontactform_script')
			&& parent::uninstall());
	}
	
	public function getContent() {
		$this->_clearCache('bettercontactform.admin.tpl');
		// If we try to update the settings
		if (isset($_POST['submitBetterContactForm'])) {
			Configuration::updateValue('bettercontactform_enabled', ((isset($_POST['is_enabled']) && $_POST['is_enabled'] != '') ? $_POST['is_enabled'] : false));
			Configuration::updateValue('bettercontactform_script', ((isset($_POST['code_snippet']) && $_POST['code_snippet'] != '') ? $_POST['code_snippet'] : ''), true);
			$this->_clearCache('bettercontactform.tpl');
		}
		$this->smarty->assign(array(
			'displayName' => $this->displayName,
			'submitAction' => Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']),
			'isEnabled' => Tools::safeOutput(Configuration::get('bettercontactform_enabled')),
			'codeSnippet' => Configuration::get('bettercontactform_script'),
		));
		$html = $this->display(__FILE__, 'bettercontactform.admin.tpl', $this->getCacheId());
		return $html;
	}
	
	public function hookFooter($params) {
		if (!$this->isCached('bettercontactform.tpl', $this->getCacheId()))
			$this->smarty->assign(array(
				'isEnabled' => Configuration::get('bettercontactform_enabled'),
				'codeSnippet' => Configuration::get('bettercontactform_script'),
			));
		return $this->display(__FILE__, 'bettercontactform.tpl', $this->getCacheId());
	}
}
?>
