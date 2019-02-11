{capture name=path}{l s='Pagar Con PagoFacil' mod='cuentadigital'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{l s='Generar cupon de pago' mod='cuentadigital'}</h2>

{assign var='current_step' value='payment'}
{include file=$tpl_dir./order-steps.tpl}

{if $nbProducts <= 0}
	<p class="warning">{l s='Tu Carro de compra no posee articulos para abonar!!.'}</p>
{else}

<h3>{l s='Imprimir cupon de pago para PagoFacil-RapiPagos-Bapro' mod='cuentadigital'}</h3>
<form action="{$this_path_ssl}validation.php" method="post">
<p>
	<img src="{$this_path}cuentadigitala.gif" alt="{l s='cuenta digital' mod='cuentadigital'}" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='La boleta de pago sera generada atraves de un servidor seguro HTTPS provisto por cuentadigital.com' mod='cuentadigital'}
	<br/><br />
	{l s='Este sistema de pago es valido Solo para Argentina si usted recide en otro pais por favor presione en  otros modos de pagos.' mod='cuentadigital'} <p class="cart_navigation"><a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='bankwire'}</a></p>
</p>
<p style="margin-top:20px;">
	- {l s='Total: ' mod='cuentadigital'}
	{if $currencies|@count > 1}
		{foreach from=$currencies item=currency}
			<span id="amount_{$currency.id_currency}" class="price" style="display:none;">{convertPriceWithCurrency price=$total currency=$currency}</span>
		{/foreach}
	{else}
		<span id="amount_{$currencies.0.id_currency}" class="price">{convertPriceWithCurrency price=$total currency=$currencies.0}</span>
	{/if}
</p>
<p>
	-
	{if $currencies|@count > 1}
		{l s='Este modo de pagos solo acepta PESOS MONEDA ARGENTINA, si esta usando otra moneda sirvase hacer la conversion.' mod='cuentadital'}
		<br /><br />
		{l s='Cambiar a pesos moneda Argentina, selecione $:' mod='cuentadigital'}
		<select id="currency_payement" name="currency_payement" onChange="showElemFromSelect('currency_payement', 'amount_')">
			{foreach from=$currencies item=currency}
				<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
			{/foreach}
		</select>
		<script language="javascript">showElemFromSelect('currency_payement', 'amount_');</script>
	{else}
		{l s='Este metodo de pago solo acepta moneda Argentina:' mod='cuentadigital'}&nbsp;<b>{$currencies.0.name}</b>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}">
	{/if}
</p>
<br />

</p>
<p class="cart_navigation">
	<a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Otros modos de pagos' mod='cuentadigital'}</a>
	<input type="submit" name="submit" value="{l s='Generar cupon de pagos' mod='cuentadigital'}" class="exclusive_large" />
</p>
</form>
{/if}