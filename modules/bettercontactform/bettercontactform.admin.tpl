<img width="194" height="25" style="float:left; margin-right:15px;" src="../modules/bettercontactform/logo.jpg">
<div class="clear">&nbsp;</div>

<form action="{$submitAction}" method="post">
	<fieldset>
		<legend><img alt="" src="/modules/bettercontactform/contact.gif" title="">{l s='Settings'}</legend>
        <label>{l s='Enabled'}</label>

		<div class="margin-form">
			<input id="is_enabled" name="is_enabled" type="radio" value="1" {if $isEnabled === '1'}checked="checked"{/if}>
            <label class="t" for="is_enabled"><img alt="{l s='Enabled'}" src="../img/admin/enabled.gif" title="{l s='Enabled'}"></label>
            
            <input id="is_enabled" name="is_enabled" type="radio" value="0" {if $isEnabled === '0'}checked="checked"{/if}>
            <label class="t" for="is_enabled"><img alt="{l s='Disabled'}" src="../img/admin/disabled.gif" title="{l s='Disabled'}"></label>

			<p class="clear">{l s='Activate Better Contact Form on frontend'}.</p>
		</div> <label>{l s='Embedded Code'}</label>

		<div class="margin-form">
			<textarea cols="53" name="code_snippet" rows="6">{$codeSnippet}</textarea>
			<p class="clear">Put your Better Contact Form code here.</p>
		</div>

		<div class="margin-form">
			If you don't have the "Better Contact Form" code yet, please <a href="//bettercontactform.com/contact/form/builder" target="_create_contact_form">Create Yours Contact Form here</a>.
		</div>

		<div class="margin-form">
			<input class="button" name="submitBetterContactForm" type="submit" value="{l s='Save Configuration'}">
		</div>
		<br>
		<div class="margin-form">
			This module is developed by <a href="http://bettercontactform.com" target="_bettercontactform">BetterContactForm.com</a>. If you have any questions or comments, please <a href="http://bettercontactform.com/discussion" target="_discussion">leave your feedback here</a>.
		</div>
	</fieldset>
</form>