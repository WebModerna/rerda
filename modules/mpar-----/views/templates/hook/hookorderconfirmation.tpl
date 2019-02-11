{*
* Hook - Confirmacion de Orden
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*}
{if $status == 'ok'}
	<p>{l s='Your order' mod='mpar'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='was processed successfully.' mod='mpar'}
		<br /><br /><span class="bold">{l s='If you pay for a home delivery order will be sent as soon as possible.' mod='mpar'}</span>
		<br /><br />{l s='For any questions or more information, please contact us.' mod='mpar'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='Customer Service' mod='mpar'}</a>.
	</p>
{else}
	{if $status == 'pending'}
		<p class="warning">
			{l s='Your payment was processed but is currently in state <b> Pending </b> means that your payment still not been released by your bank.' mod='mpar'}
		</p>
	{else}
		<p class="warning">
			{l s='Apparently occurred a problem with your payment. If you think this is a mistake on our' mod='mpar'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='Customer Service' mod='mpar'}</a>.
		</p>
	{/if}
{/if}

<!-- Modulo desarrollado por Kijam.com -->
