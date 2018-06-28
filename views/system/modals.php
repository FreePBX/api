<div class="modal fade" id="api-app" tabindex="-1" role="dialog" aria-labelledby="api-app">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="api-app-title"></h4>
			</div>
			<div class="modal-body">
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="api-app-info-heading">
						<h4 class="panel-title">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#api-app-info" aria-expanded="false" aria-controls="api-app-info">
								<?php echo _("Learn more about this API type")?>
							</a>
						</h4>
					</div>
					<div id="api-app-info" class="panel-collapse collapse" role="tabpanel" aria-labelledby="api-app-info-heading">
						<div class="panel-body" id="api-app-description"></div>
					</div>
				</div>
				<div class="form-group">
					<label for="api_app_name"><?php echo _("Your App Name")?></label>
					<input type="text" class="form-control app_reset" id="api_app_name" placeholder="Application Name">
				</div>
				<div class="form-group">
					<label for="api_app_description"><?php echo _("Description")?></label>
					<textarea class="form-control app_reset" id="api_app_description" placeholder="Description"></textarea>
				</div>
				<div class="form-group api_app_redirect_group">
					<label for="api_app_website"><?php echo _("Website")?></label>
					<input type="text" class="form-control app_reset" id="api_app_website" placeholder="Website">
				</div>
				<div class="form-group api_app_redirect_group">
					<label for="api_app_redirect"><?php echo _("Redirect URI")?></label>
					<input type="text" class="form-control app_reset" id="api_app_redirect" placeholder="Application Name">
					<br>
					<small><?php echo _("The redirect uri specifies where we redirect users after they have chosen whether or not to authenticate your application")?></small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close")?></button>
				<button type="button" class="btn btn-primary" id="api-save-app"><?php echo _("Add Application")?></button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="api-info">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo _("Client Credentials")?></h4>
			</div>
			<div class="modal-body">
				<strong><?php echo ('Token URL')?></strong>:<span><?php echo $url?>/api/api/token</span><br/>
				<strong><?php echo ('Authorization URL')?></strong>:<span><?php echo $url?>/api/api/authorize</span><br/>
				<strong><?php echo ('GraphQL URL')?></strong>:<span><?php echo $url?>/api/api/gql</span><br/>
				<strong><?php echo ('Rest URL')?></strong>:<span><?php echo $url?>/api/api/rest</span><br/>
				<strong><?php echo ('Client ID')?></strong>:<span class="client_id"></span><br/>
				<div class="client_secret_container">
					<div class='alert alert-info'><?php echo _("Please copy your secret as you will not be able to retrieve it later")?></div>
					<strong><?php echo ('Client Secret')?></strong>:<span class="client_secret"></span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" id="api-application-regenerate"><?php echo _('Regenerate Credentials')?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Close')?></button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="urlModal" tabindex="-1" role="dialog" aria-labelledby="urlModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Modal title</h4>
			</div>
			<div class="modal-body">
				<div class="panel panel-default">
					<div class="panel-heading"><?php echo sprintf(_("%s URLs"),"OAuth 2.0")?></div>
					<div class="panel-body">
						<h5>Authorize: <?php echo $url?>/api/api/authorize</h5>
						<h5>Token: <?php echo $url?>/api/api/token</h5>
						<h5>Resource: <?php echo $url?>/api/api/resource</h5>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading"><?php echo sprintf(_("%s URLs"),"GraphQL")?></div>
					<div class="panel-body">
						<h5>API: <?php echo $url?>/api/api/gql</h5>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading"><?php echo sprintf(_("%s URLs"),"RESTful")?></div>
					<div class="panel-body">
						<h5>API: <?php echo $url?>/api/api/rest</h5>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
