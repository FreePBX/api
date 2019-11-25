<div class="alert alert-info" role="alert"><?php echo _("The tokens listed below are encrypted and therefore you can not copy them below and expect to use them in your application. You can only use the token(s) that are presented to you in your application. This is a security mechanisim so that someone can't steal your credentials by just looking at this page")?></div>
<table id="api_refresh_token_list" class="table table-condensed table-striped"
	data-cache="false"
	data-show-columns="true"
	data-show-toggle="true"
	data-pagination="true"
	data-search="true"
	data-escape="true" 
	data-show-refresh="true"
	data-url="ajax.php?module=api&amp;command=getRefreshTokens"
	data-unique-id="id"
	data-toggle="table">
	<thead>
		<tr>
			<th data-field="token"><?php echo _("Token")?></th>
			<th data-field="app_name"><?php echo _("Application Name")?></th>
			<th data-field="ip_address"><?php echo _("IP Address")?></th>
			<th data-field="expiry" data-formatter="apiTime"><?php echo _("Expiry Time")?></th>
			<th data-field="last_accessed" data-formatter="apiTime"><?php echo _("Last Accessed Time")?></th>
			<th data-field="token" data-formatter="apiRefreshTokenActions"><?php echo _("Actions")?></th>
		</tr>
	</thead>
</table>
