<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				 <div class="display no-border">
					<h1><?php echo _("API")?></h1>
					<div role="tabpanel">
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#applications" aria-controls="applications" role="tab" data-toggle="tab"><?php echo _("Applications"); ?></a></li>
							<li role="presentation"><a href="#tokens" aria-controls="tokens" role="tab" data-toggle="tab"><?php echo _("Access Tokens"); ?></a></li>
							<li role="presentation"><a href="#refresh" aria-controls="refresh" role="tab" data-toggle="tab"><?php echo _("Refresh Tokens"); ?></a></li>
							<li role="presentation"><a href="#scopes" aria-controls="scopes" role="tab" data-toggle="tab"><?php echo _("Scope Visualizer"); ?></a></li>
							<li role="presentation"><a href="#gqldoc" aria-controls="gqldoc" role="tab" data-toggle="tab"><?php echo _("GraphQL Documentation"); ?></a></li>
							<li role="presentation"><a href="#graphiql" aria-controls="graphiql" role="tab" data-toggle="tab"><?php echo _("GraphQL Explorer"); ?></a></li>
						</ul>
						<div class="tab-content">
							<div role="tabpanel" id="applications" class="tab-pane display active">
								<?php show_view(__DIR__.'/applicationGrid.php',["url" => $url, "data_api" => $data_api]); ?>
							</div>
							<div role="tabpanel" id="tokens" class="tab-pane display">
								<?php show_view(__DIR__.'/accessTokenGrid.php',[]); ?>
							</div>
							<div role="tabpanel" id="refresh" class="tab-pane display">
								<?php show_view(__DIR__.'/refreshTokenGrid.php',[]); ?>
							</div>
							<div role="tabpanel" id="scopes" class="tab-pane display">
								<?php show_view(__DIR__.'/scopeVisualizer.php',[]); ?>
							</div>
							<div role="tabpanel" id="gqldoc" class="tab-pane display">
								<?php show_view(__DIR__.'/gqldocVisualizer.php',[]); ?>
							</div>
							<div role="tabpanel" id="graphiql" class="tab-pane display">
								<?php show_view(__DIR__.'/graphiql.php',[]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
