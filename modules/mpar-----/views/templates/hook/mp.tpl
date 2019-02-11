{*
* Hook - Despliegue de Boton de Pago
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*}
<div class="row" id="is_mercadopago">
    <div class="col-xs-12">
        <div class="payment_module" style="background-color: #fbfbfb; border: 1px solid #dddddd;">
            {if empty($init_point)}
                {$error|escape:'htmlall':'UTF-8'}
            {else}
                <div style="color: #bababa; font-size:17px; min-height: 90px;min-width:150px;width: 20%;float:left;text-align: center;padding: 10px;">
                <a href="{$init_point|escape:'htmlall':'UTF-8'}" id="botonMP" name="MP-Checkout" class="{$mpstyle.b_color|escape:'htmlall':'UTF-8'}-{$mpstyle.b_size|escape:'htmlall':'UTF-8'}-{$mpstyle.b_shape|escape:'htmlall':'UTF-8'}-{$mpstyle.b_font|escape:'htmlall':'UTF-8'}-Ar{$mpstyle.b_logo|escape:'htmlall':'UTF-8'}" mp-mode="{if $modal == 1}modal{else}redirect{/if}" onreturn="execute_my_onreturn_mpar">
                    {l s='Pay' mod='mpar'}
                </a>
                </div>
                <div style="padding: 10px;color: #bababa; font-size: 15px; float:left;min-width: 360px;">
                    <strong style="color: rgb(51, 51, 51);  letter-spacing: -1px;">{l s='Pay with Credit Card' mod='mpar'}</strong><br /><span style="color: #bababa;font-weight: bold;  letter-spacing: -1px;">{l s='It can take up to 48 working hours bank.' mod='mpar'}{if $fee >= 0.001}{l s=' Have additional fee.' mod='mpar'}{/if}</span><br /><br />
                    {if $fee >= 0.001}
                        <strong>{l s='Cost of services:' mod='mpar'}</strong> {displayPrice price=$feeTotal}.<br />
                    {/if}
                    {if $is_mp_envios}
                        <strong>{l s='Cost of MercadoEnvio:' mod='mpar'}</strong> {displayPrice price=$shippingTotal}.<br />
                    {/if}
                    {if $is_mp_envios || $fee >= 0.001}
                    <strong>{l s='In MercadoPago must pay:' mod='mpar'}</strong> <b style="color:red">{displayPrice price=$newPrice}</b>
                    {/if}
                </div>
            {/if}
            <br style="clear:both;height:0;line-height:0" />
        </div>
    </div>
</div>
<style>
    #MP-Checkout-dialog {
        z-index: 200000 !important;
    }
</style>
<script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>
<script type="text/javascript">
    function execute_my_onreturn_mpar(data) {
        if (data.collection_status!=null)
            window.location.href="{$back_url|escape:'htmlall':'UTF-8'}?collection_id="+data.collection_id+"&preference_id="+data.preference_id+"&external_reference="+data.external_reference+"&collection_status="+data.collection_status;
    }
    {if $is_mp_envios}
    $(document).ready(function() {
        $('div#is_mercadopago').siblings('*').remove();
    });
    {/if}
</script>

<!-- Modulo desarrollado por Kijam.com -->
