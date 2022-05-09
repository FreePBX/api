<h3><?php echo _("Generate GraphQL Documentation based on scopes")?></h3>
<h4><?php echo _("Paste Scope(s) Here (You can generate scopes in the Scope Visualizer Tab)")?></h4>
<textarea id="scope-doc" class="form-control"></textarea>
<button id="generate-docs" class="btn btn-primary"><?php echo _("Generate Documentation")?></button>
<br>
<br>
<div id="doc-buttons" class="d-none">
	<button id="docs-back" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i></button>
	<button id="docs-forward" class="btn btn-primary btn-sm"><i class="fa fa-arrow-right"></i></button>
	<button id="docs-home" class="btn btn-primary btn-sm"><i class="fa fa-home"></i></button>
</div>
<iframe id="doc-container"></iframe>
