{*
* Hook - Despliegue descripcion del pago
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*}

{if $backwardcompatible}
    <fieldset id="mpar_status">
        <legend><img src="../img/admin/money.gif">{l s='MercadoPago' mod='mpar'}</legend>
        <b>{l s='Order ID' mod='mpar'}: </b> {$order_id|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='MercadoPago Payment ID' mod='mpar'}: </b> {$mp_validation.mp_op_id|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='Order Price: ' mod='mpar'}</b> {$mp_validation.price|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='Fee Price: ' mod='mpar'}</b> {$mp_validation.fee|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
        <b>MercadoEnvios {l s='Shipping Price: ' mod='mpar'}</b> {$mp_validation.shipping|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='Total Payment: ' mod='mpar'}</b> {$mp_validation.total|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='Client Name: ' mod='mpar'}</b> {$mp_validation.client_name|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='Client Identification: ' mod='mpar'}</b> {$mp_validation.identification|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='Last Sync' mod='mpar'}: </b> {$mp_last_sync|escape:'htmlall':'UTF-8'}<br />
        <b>{l s='Last Status' mod='mpar'}: </b> {$mp_status|escape:'htmlall':'UTF-8'}
        {if $has_refund}
            <br /><b>{l s='Refund Date: ' mod='mpar'}</b> {$has_refund.date_create|round:2|escape:'htmlall':'UTF-8'}
        {/if} 
    </fieldset>
{else}
    <div class="row" id="mpar_status">
        <div class="col-lg-12">
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-credit-card"></i>
                     {l s='MercadoPago' mod='mpar'}
                </div>
                <div class="well">
                    <b>{l s='Order ID' mod='mpar'}: </b> {$order_id|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='MercadoPago Payment ID' mod='mpar'}: </b> {$mp_validation.mp_op_id|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='MercadoPago Order ID' mod='mpar'}: </b> {$mp_validation.mp_order_id|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='Order Price: ' mod='mpar'}</b> {$mp_validation.price|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='Fee Price: ' mod='mpar'}</b> {$mp_validation.fee|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
                    <b>MercadoEnvios {l s='Shipping Price: ' mod='mpar'}</b> {$mp_validation.shipping|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='Total Payment: ' mod='mpar'}</b> {$mp_validation.total|round:2|escape:'htmlall':'UTF-8'} {$mp_validation.currency_id|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='Client Name: ' mod='mpar'}</b> {$mp_validation.client_name|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='Client Identification: ' mod='mpar'}</b> {$mp_validation.identification|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='Last Sync' mod='mpar'}: </b> {$mp_last_sync|escape:'htmlall':'UTF-8'}<br />
                    <b>{l s='Last Status' mod='mpar'}: </b> {$mp_status|escape:'htmlall':'UTF-8'}
                    {if $has_refund}
                        <br /><b>{l s='Refund Date: ' mod='mpar'}</b> {$has_refund.date_create|round:2|escape:'htmlall':'UTF-8'}
                    {/if} 
                </div>
            </div>
        </div>
    </div>
{/if}
<script>
    $('#mpar_status').prependTo($('#mpar_status').parent()); 
</script>

