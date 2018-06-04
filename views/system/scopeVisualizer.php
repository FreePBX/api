<div class="row">
	<div class="col-md-12">
		<h3><?php echo _('Selected Scopes')?> <button type="button" id="copy-scopes" class="btn btn-primary btn-sm"><i class="fa fa-files-o"></i></button></h3>
		<textarea id="scope-area" class="form-control" readonly></textarea>
	</div>
</div>
<br>
<div class="row">
	<div class="col-md-6">
		<h3><?php echo _('Scope Tree')?></h3>
		<div id="jstree_demo_div"></div>
	</div>
	<div class="col-md-6">
		<h3><?php echo _('Scope List')?></h3>
		<div id="toolbar-all">
			<!--
			<strong><?php echo _('Show API types')?></strong>
			<select id="api_types" class="bsmultiselect " name="pbx_modules[]" multiple="multiple">
				<option value="gql" selected>GraphQL</option>
				<option value="rest" selected>REST</option>
			</select>
		-->
		</div>
		<table id="scope_visualizer_list" class="table table-condensed table-striped"
			data-cache="false"
			data-toolbar="#toolbar-all"
			data-show-columns="true"
			data-show-toggle="true"
			data-pagination="true"
			data-search="true"
			data-show-refresh="false"
			data-url="ajax.php?module=api&amp;command=getScopes"
			data-unique-id="scope"
			data-toggle="table">
			<thead>
				<tr>
					<th data-checkbox="true"></th>
					<th data-field="scope"><?php echo _("Scope")?></th>
					<th data-field="typeName"><?php echo _("API Type")?></th>
					<th data-field="moduleName"><?php echo _("Module")?></th>
					<th data-field="description"><?php echo _("Description")?></th>
				</tr>
			</thead>
		</table>
	</div>
</div>
