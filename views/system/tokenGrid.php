<table id="api_application_list" class="table table-condensed table-striped"
	data-cache="false"
	data-show-columns="true"
	data-show-toggle="true"
	data-pagination="true"
	data-search="true"
	data-show-refresh="true"
	data-url="ajax.php?module=api&amp;command=getTokens"
	data-unique-id="id"
	data-toggle="table">
	<thead>
		<tr>
			<th data-field="token"><?php echo _("Token")?></th>
			<th data-field="ip_address"><?php echo _("IP Address")?></th>
			<th data-field="last_accessed" data-formatter="apiTime"><?php echo _("Last Accessed Time")?></th>
			<th data-field="expiry" data-formatter="apiTime"><?php echo _("Expiry Time")?></th>
		</tr>
	</thead>
</table>
