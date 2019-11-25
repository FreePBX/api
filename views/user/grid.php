<div id="toolbar-api-applications">
	<div class="dropdown" style="display:inline-block;">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			<i class="fa fa-plus">&nbsp;</i><?php echo _("Add Application")?> <span class="caret"></span>
		</button>
		<ul class="dropdown-menu" role="menu">
			<li data-toggle="modal" data-target="#api-app" data-id="authorization_code" class="api-add-app clickable"><a><i class="fa fa-plus"></i> <strong><?php echo _('Web-server app')?></strong></a></li> <!-- Authorization Code Grant (Explicit) -->
			<li data-toggle="modal" data-target="#api-app" data-id="implicit" class="api-add-app clickable"><a><i class="fa fa-plus"></i> <strong><?php echo _('Browser-based/Single Page app')?></strong></a></li> <!-- Authorization Code Grant (Implicit) -->
			<li data-toggle="modal" data-target="#api-app" data-id="password" class="api-add-app clickable"><a><i class="fa fa-plus"></i> <strong><?php echo _('Native app')?></strong></a></li> <!-- Password Grant -->
			<li data-toggle="modal" data-target="#api-app" data-id="client_credentials" class="api-add-app clickable"><a><i class="fa fa-plus"></i> <strong><?php echo _('Machine-to-Machine app')?></strong></a></li> <!-- Client Credentials Grant -->
		</ul>
	</div>
</div>
<table id="api_application_list" class="table table-condensed table-striped"
	data-cache="false"
	data-show-columns="true"
	data-show-toggle="true"
	data-pagination="true"
	data-escape="true" 
	data-search="true"
	data-toolbar="#toolbar-api-applications"
	data-toggle="table">
	<thead>
		<tr>
			<th data-field="name"><?php echo _("Application name")?></th>
			<th data-field="description"><?php echo _("Application Description")?></th>
			<th data-field="id" data-formatter="apiActions"><?php echo _("Actions")?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($applications as $application) { ?>
			<tr>
				<td><?php echo $application['name']?></td>
				<td><?php echo $application['description']?></td>
				<td><?php echo $application['client_id']?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php show_view(__DIR__."/modals.php",["applications" => $applications])?>
