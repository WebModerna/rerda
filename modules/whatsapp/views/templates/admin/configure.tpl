{*
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
*}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Whatsapp Number' mod='whatsapp'}</h3>
	<form class="form-horizontal" enctype="multipart/form-data" action="" method="POST">
		  <div class="form-group">
			<label for="input1" class="col-sm-2 control-label">{l s='Tel' mod='whatsapp'}</label>
			<div class="col-sm-8">
			  <input type="text" class="form-control" name="telefon" id="input1" value="{$whatasppno|escape:'html':'UTF-8'}" placeholder="{l s='05XXXX' mod='whatsapp'}">
			</div>
		  </div>

		  <div class="form-group">
			<div class="col-sm-offset-2 col-sm-8">
			  <button type="submit" name="telekle" class="btn btn-default ">{l s='Save or Update' mod='whatsapp'}</button>
			</div>
		  </div>
	</form>
</div>

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Sopport And Other Product' mod='whatsapp'}</h3>
	<div class="row urunler">
	<div class="col-sm-3">
		<a href="http://www.fyazilim.com/{if $lang_iso == 'tr'}tr/iletisim{else}en/contact-us{/if}" target="_blank">
			<img src="{$whataspp_module_dir|escape:'html':'UTF-8'}/views/img/iletisim.png" />
		</a>
		<h4>Support</h4>
	</div>
	<div class="col-sm-3">
		<a href="http://www.fyazilim.com/{$lang_iso|escape:'html':'UTF-8'}/13-prestashop-modulleri" target="_blank">
			<img src="{$whataspp_module_dir|escape:'html':'UTF-8'}/views/img/urun.png" />
		</a>
		<h4>Free Prestashop Modules</h4>
	</div>
	<div class="col-sm-3">
		<a href="http://www.fyazilim.com/{$lang_iso|escape:'html':'UTF-8'}/16-ucretsiz-temalar" target="_blank">
			<img src="{$whataspp_module_dir|escape:'html':'UTF-8'}/views/img/urun.png" />
		</a>
		<h4>Free Prestashop Templates</h4>
	</div>
	<div class="col-sm-3">
		<a href="http://www.fyazilim.com/{$lang_iso|escape:'html':'UTF-8'}/12-prestashop-temalari" target="_blank">
			<img src="{$whataspp_module_dir|escape:'html':'UTF-8'}/views/img/urun.png" />
		</a>
		<h4>All Prestashop Templates</h4>
	</div>
	</div>
</div>