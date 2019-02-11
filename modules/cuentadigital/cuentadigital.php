<?php
/***************************************************************************************
// Copyright (c) 1995 - 2009 FOSI TEAM http://www.goldenfoxi.com.ar
// ***GoldenFoxi Todos Los Derechos reservados*** fosi_team@hotmail.com
 ***************************************************************************************/
class CuentaDigital extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public  $details;
	public  $owner;
	public	$address;

	public function __construct()
	{
		$this->name = 'cuentadigital';
		$this->tab = 'Payment';
		$this->version = 0.4;
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('NUMERO_ID_DIGITAL'));
		if (isset($config['NUMERO_ID_DIGITAL']))
			$this->iddigital = $config['NUMERO_ID_DIGITAL'];
		

		parent::__construct(); /* The parent construct is required for translations */

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Cuenta Digital');
		$this->description = $this->l('Aceptar pagos atraves de cuenta digital: PagoFacil, Bapropagos,etc.');
		$this->confirmUninstall = $this->l('Hola esta por eliminar esta configuracion¿deseas hacer esto?');
		if (!isset($this->iddigital ))
			$this->warning = $this->l('Esta plataforma de pagos requiere de configuracion para su correcto funcionamiento');
		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('no has seleccionado una moneda para este modulo');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
			return false;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('NUMERO_ID_DIGITAL')
				OR !parent::uninstall())
			return false;
	}

	private function _postValidation()
	{
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['iddigital']))
				$this->_postErrors[] = $this->l('El nuemero ID de su cuenta digital es requerido para el correcto funcionamiento del modulo.');
			
		}
	}

	private function _postProcess()
	{
		if (isset($_POST['btnSubmit']))
		{
			Configuration::updateValue('NUMERO_ID_DIGITAL', $_POST['iddigital']);
			
		}
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Listo Configuracion guardada!').'</div>';
	}

	private function _displayCuentaDigital()
	{
		$this->_html .= '<img src="../modules/Cuentadigital/cuentadigitala.gif" style="float:left; margin-right:15px;"><b>'.$this->l('Instalando este modulo permitirá la impresion de boletas PagoFacil en tiempo real.').'</b><p style="max-width:800px;">
		'.$this->l('EL estado de pedido sera pendiente por defecto, luego cuando perciba el pago en su cuenta digital usted debera realizar el cambio del estado manualmente en el area de administracion de su tienda').'</p>
		'.$this->l('Modulo Powered by GoldenFoxi; www.goldenfoxi.com.ar').'<br /><br /><br />';
	}

	private function _displayForm()
	{
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="max-width:800px;">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Configuracion Modulo Cuenta Digital').'</legend>
				<table border="0" width="auto" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Ingrese su nuemro ID de cuenta digital').'.<br /><br /></td></tr>
					<tr><td width="130" style="height: 35px;"></td><td><input type="text" name="iddigital" value="'.htmlentities(Tools::getValue('iddigital', $this->iddigital), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /><p>'.$this->l('Este Numero le es proporcionado a cada usuario al crear una cueta en cuenta digital, si no sabe su numero porfavor ingrese a su cuenta digital o pongase en contacto con el proveedor del servicio.').'</p></td></tr>
												
					
					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Guardar y generar configurarcion').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayCuentaDigital();
		$this->_displayForm();

		return $this->_html;
	}

	public function execPayment($cart)
	{
		global $cookie, $smarty;

		$smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cookie->id_currency,
			'currencies' => $this->getCurrency(),
			'total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
			'isoCode' => Language::getIsoById(intval($cookie->id_lang)),
			'cuentadigitalIddigital' => nl2br2($this->iddigital),
			 
			'this_path' => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'payment_execution.tpl');
	}

	public function hookPayment($params)
	{
		global $smarty;

		$smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		global $smarty;
		$state = $params['objOrder']->getCurrentState();
		if ($state == _PS_OS_CUENTADIGITAL_ OR $state == _PS_OS_OUTOFSTOCK_)
			$smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false, false),
				'cuentadigitalIddigital' => nl2br2($this->iddigital),
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
		else
			$smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}

}

?>