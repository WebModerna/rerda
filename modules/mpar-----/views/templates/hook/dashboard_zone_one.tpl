{*
* Hook - Dashboard Balance MercadoPago
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*}
<section id="mpar" class="panel widget{if $allow_push} allow_push{/if}">
	<div class="panel-heading">
		<i class="icon-money"></i> {l s='Balance MercadoPago' mod='mpar'}
		<span class="panel-heading-action">
			<a class="list-toolbar-btn" href="#" onclick="refreshDashboard('mpar'); return false;" title="refresh">
				<i class="process-icon-refresh"></i>
			</a>
		</span>
	</div>
	<section id="dash_live" class="loading">
		<ul class="data_list_large">
			<li>
				<span class="data_label size_l">
					{l s='Total' mod='mpar'}
					<small class="text-muted"><br/>
						{l s='Total amount in Account' mod='mpar'}
					</small>
				</span>
				<span class="data_value size_xxl">
					<span id="mpar_total_amount"></span>
				</span>
			</li>
			<li>
				<span class="data_label size_l">
					{l s='Available' mod='mpar'}
					<small class="text-muted"><br/>
						{l s='Total available amount in Account' mod='mpar'}
					</small>
				</span>
				<span class="data_value size_xxl">
					<span id="mpar_available_balance"></span>
				</span>
			</li>
			<li>
				<span class="data_label size_l">
					{l s='Unavailable' mod='mpar'}
					<small class="text-muted"><br/>
						{l s='Total unavailable amount in Account' mod='mpar'}
					</small>
				</span>
				<span class="data_value size_xxl">
					<span id="mpar_unavailable_balance"></span>
				</span>
			</li>
		</ul>
	</section>
</section>
