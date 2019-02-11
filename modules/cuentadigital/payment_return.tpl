{if $status == 'ok'}
	<p>{l s='Por favor imprima el cupon de pago y abone antes de la fecha de vencimiento' mod='cuentadigital'} <br /> 
	<p>Concreta tu pago en efectivo a trav&eacute;s de las numerosas sucursales de la red Pago F&aacute;cil de todo el pa&iacute;s.<br /><font color="#DD0000"> Imprime tu cup&oacute;n de pago. Dicho cup&oacute;n tiene una validez de 7 d&iacute;as, fecha que puede diferir del vencimiento de tu factura. Ver&aacute;s reflejado el pago en Mi cuenta entre 1 y 3 d&iacute;as despu&eacute;s de haberlo realizado.</font></p><p>Para un mejor seguimiento de tu pago, guarda tu factura en tu ordenador. Este cupon Solo se muestra una vez, por ello es aconsejable que lo guardes en tu PC!</p>

		<br />{l s='Se te ha enviado toda esta informacion por mail. Ante cualquier inquietud que tengas ponte en contacto con nosotros' mod='cuentadigital'} <a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='cheque'}</a>. <br />
	</p><p align="center">
	<iframe target="fedex" name="tytplus" id="tytplus" src="https://www.cuentadigital.com/api.php?id={$cuentadigitalIddigital}&codigo=Orden N&ordm; {$id_order}&precio={$total_to_pay}&venc=7&site={$shop_name}&concepto=Compra en {$shop_name} - Orden Numero {$id_order}" marginwidth="0" marginheight="0" frameborder="0" vspace="0" width="520" height="800" scrolling="no"></iframe></P>
{else}
	<p class="warning">
		{l s='Ha ocurrido un error al generar el cupon de pagos, ponte en contacto con nosotros' mod='cuentadigital'} 
		<a href="{$base_dir_ssl}contact-form.php">{l s='contactar al soporte tecnico' mod='cuentadigital'}</a>.
	</p>
{/if}
