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
							<a class="collapsed" data-toggle="collapse" href="#api-app-info" role="button" aria-expanded="false" aria-controls="api-app-info">
								<?php echo _("Learn more about this API type")?>
							</a>
						</h4>
					</div>
					<div  id="api-app-info" class="panel-collapse collapse">
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
				<div class="form-group">
					<label for="api_app_allowed_scopes"><?php echo _("Allowed Scopes")?></label>
					<textarea class="form-control app_reset" id="api_app_allowed_scopes"></textarea>
					<br>
					<small><?php echo _("Use the Scope Visualizer to paste valid scopes into this text box to restrict this application to be only able to use these scopes. Leave blank to not have any scope restrictions")?></small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close")?></button>
				<button type="button" class="btn btn-primary" id="api-save-app"><?php echo _("Add Application")?></button>
			</div>
		</div>
	</div>
</div>
<!--- Modal api-info ---> 
<div class="modal fade" tabindex="-1" role="dialog" id="api-info">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo _("Client Credentials")?></h4>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">					
							<div class="col-md-2">
							</div>
							<div class="col-md-8 text-center button-filter" style="display:none">
								<div id="api-filter" class="btn-group" role="group">
									<button class="btn btn-secondary api-select" type="button" style="display:none"></button>			 
									<button class="btn btn-secondary api-select left-btn-group" id="HTTP" type="button">HTTP</button> 
									<button class="btn btn-secondary api-select" id="HTTPS" type="button">HTTPS</button> 
									<button class="btn btn-secondary api-select" id="ADMINPorts"  type="button"><?php echo _("Admin Ports")?></button> 
									<button class="btn btn-secondary api-select right-btn-group" id="APIPorts" type="button"><?php echo _("API Ports")?></button>
									<button class="btn btn-secondary api-select" type="button" style="display:none"></button>
								</div>
							</div>
							<div class="col-md-2">
							</div>
						</div>
					</div>
				</div>
				<br>
				<div class="col-md-12">
					<div class="">
						<div class="list-group">
							<a href="#" class="list-group-item list-group-item-action active"><strong><?php echo _("URLs")?></strong></a>
							<div class="list-group-item">
								<div class="container-fluid" >
									<div class="row no-gutter url-list">
										<div class="col-md-2 text-right">
											<strong><span><?php echo ('Token URL')?>:</span></strong>&nbsp;
										</div>
										<div class="col-md-10 text-left">
											<span class="url-token"><span class="url"><?php echo $url?></span>/api/api/token</span> <i class="fa fa-clipboard" id="url-token" title="<?php echo _("Copy to clipboard")?>"></i>
										</div>
									</div>
									<div class="row no-gutter url-list">
										<div class="col-md-2 text-right">
											<strong><?php echo ('Authori. URL')?>:<span></strong>&nbsp;
										</div>
										<div class="col-md-10 text-left">
											<span class="url-authorize"><span class="url"><?php echo $url?></span>/api/api/authorize</span> <i class="fa fa-clipboard" id="url-authorize" title="<?php echo _("Copy to clipboard")?>"></i>
										</div>
									</div>
									<div class="row no-gutter url-list">
										<div class="col-md-2 text-right ">
											<strong><?php echo ('GraphQL URL')?>:<span></strong>&nbsp;
										</div>
										<div class="col-md-10 text-left">
											<span class="url-gql"><span class="url"><?php echo $url?></span>/api/api/gql</span> <i class="fa fa-clipboard" id="url-gql" title="<?php echo _("Copy to clipboard")?>"></i>
										</div>
									</div>
									<div class="row no-gutter url-list">
										<div class="col-md-2 text-right">
											<strong><?php echo ('Rest URL')?>:<span></strong>&nbsp;
										</div>
										<div class="col-md-10 text-left">
											<span class="url-rest"><span class="url"><?php echo $url?></span>/api/api/rest</span> <i class="fa fa-clipboard" id="url-rest" title="<?php echo _("Copy to clipboard")?>"></i>
										</div>
									</div>
									<div class="row no-gutter proto-disabled" style="display:none">
										<div class="col-md-12 text-center">
											<h4><?php echo _("Protocol Disabled") ?></h4>
										</div>
									</div>
								</div>
							</div>
							<a href="#" class="list-group-item list-group-item-action active"><strong><?php echo ('Client ID')?></strong></a>
							<div class="list-group-item">							
								<span class="client_id"></span> <i class="fa fa-clipboard" id="client_id" title="<?php echo _("Copy to clipboard")?>"></i>
							</div>
							<a href="#" class="list-group-item list-group-item-action active"><strong><?php echo ('Allowed Scopes')?></strong></a>
							<div class="list-group-item">							
								<span class="allowed_scopes"></span> <i class="fa fa-clipboard" id="allowed_scopes" title="<?php echo _("Copy to clipboard")?>"></i>
							</div>
							<span class="client_secret_container">
								<a href="#" class="list-group-item list-group-item-action active"><?php echo "<b>"._('Client Secret') ."</b> <i>"._('(Copy your secret as you will not be able to retrieve it later).')."</i>" ?></a>
								<div class="list-group-item">						
									<span class="client_secret"></span>  <i class="fa fa-clipboard" id="client_secret" title="<?php echo _("Copy to clipboard")?>"></i>
								</div>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" id="api-application-regenerate"><?php echo _('Regenerate Credentials')?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Close')?></button>
				<input type="text" value="" id="copytmp">
				<input type="hidden" id="restapi" value="<?php echo !empty($data_api["API"]["HTTP"]) ? $data_api["API"]["HTTP"] : ""; ?>">
				<input type="hidden" id="sslrestapi" value="<?php echo !empty($data_api["API"]["HTTPS"]) ? $data_api["API"]["HTTPS"] : ""; ?>">
				<input type="hidden" id="acp" value="<?php echo !empty($data_api["ACP"]["HTTP"]) ? $data_api["ACP"]["HTTP"] : ""; ?>">
				<input type="hidden" id="sslacp" value="<?php echo !empty($data_api["ACP"]["HTTPS"]) ? $data_api["ACP"]["HTTPS"] : ""; ?>">
				<input type="hidden" id="fqdn" value="<?php echo str_replace(array("http://", "https://", "/admin"),array("","",""), $url)?>">
				<input type="hidden" id="sysadmin" value="<?php echo $data_api["sa"] ;?>">
			</div>
		</div>
	</div>
</div>
<!--- AND Modal api-info ---> 

<div class="modal fade" id="urlModal" tabindex="-1" role="dialog" aria-labelledby="urlModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _("API URLs")?></h4>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">					
							<div class="col-md-2">
							</div>
							<div class="col-md-8 text-center button-filter" style="display:none">
								<div id="api-filter" class="btn-group" role="group">
									<button class="btn btn-secondary api-select-um" type="button" style="display:none"></button>			 
									<button class="btn btn-secondary api-select-um left-btn-group" id="HTTPum" type="button">HTTP</button> 
									<button class="btn btn-secondary api-select-um" id="HTTPSum" type="button">HTTPS</button> 
									<button class="btn btn-secondary api-select-um" id="ADMINPortsum"  type="button"><?php echo _("Admin Ports")?></button> 
									<button class="btn btn-secondary api-select-um right-btn-group" id="APIPortsum" type="button"><?php echo _("API Ports")?></button>
									<button class="btn btn-secondary api-select-um" type="button" style="display:none"></button>
								</div>
							</div>
							<div class="col-md-2">
							</div>
						</div>
					</div>
				</div>
				<br>
				<div class="panel panel-default">
					<div class="panel-heading active"><?php echo sprintf(_("%s URLs"),"OAuth 2.0")?></div>
					<div class="panel-body">
						<h5 class="url-list">Authorize: <span class="url-authorize-um"><span class="url"><?php echo $url?></span>/api/api/authorize</span> <i class="fa fa-clipboard clipboard-um" id="url-authorize-um" title="<?php echo _("Copy to clipboard")?>"></i></h5>
						<h5 class="url-list">Token: <span class="url-token-um"><span class="url"><?php echo $url?></span>/api/api/token</span> <i class="fa fa-clipboard clipboard-um" id="url-token-um" title="<?php echo _("Copy to clipboard")?>"></i></h5>
						<h5 class="url-list">Resource: <span class="url-resource-um"><span class="url"><?php echo $url?></span>/api/api/resource</span> <i class="fa fa-clipboard clipboard-um" id="url-resource-um" title="<?php echo _("Copy to clipboard")?>"></i></h5>
						<h5 class="proto-disabled-um" style="display:none"><?php echo _("Protocol Disabled") ?></h5>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading active"><?php echo sprintf(_("%s URLs"),"GraphQL")?></div>
					<div class="panel-body">
						<h5 class="url-list">API: <span class="url-gql-um"><span class="url"><?php echo $url?></span>/api/api/gql</span>  <i class="fa fa-clipboard clipboard-um" id="url-gql-um" title="<?php echo _("Copy to clipboard")?>"></i></h5>
						<h5 class="proto-disabled-um" style="display:none"><?php echo _("Protocol Disabled") ?></h5>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading active"><?php echo sprintf(_("%s URLs"),"RESTful")?></div>
					<div class="panel-body">
						<h5 class="url-list">API: <span class="url-restapi-um"><span class="url"><?php echo $url?></span>/api/api/rest</span>  <i class="fa fa-clipboard clipboard-um" id="url-restapi-um" title="<?php echo _("Copy to clipboard")?>"></i></h5>
						<h5 class="proto-disabled-um" style="display:none"><?php echo _("Protocol Disabled") ?></h5>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<input type="text" value="" id="copytmpum" style="color: white; border-color: white; font-size: 0px;">
			</div>
		</div>
	</div>
</div>