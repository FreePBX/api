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
				<strong><?php echo ('Client ID')?></strong>:<span class="client_id"></span>
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
<script>
var app_type;
$(".api-add-app").click(function() {
	app_type = $(this).data("id")
});
$('#api-save-app').click(function () {
	if(!$("#api_app_name").val().length) {
		warnInvalid($("#api_app_name"),_("Please enter a valid application name"))
		return
	}
	if(!$("#api_app_description").val().length) {
		warnInvalid($("#api_app_description"),_("Please enter a valid application description"))
		return
	}
	var data = {
		type: app_type,
		name: $("#api_app_name").val(),
		description: $("#api_app_description").val(),
		website: $("#api_app_website").val(),
		redirect: $("#api_app_redirect").val(),
		user: $("input[name=user]").length ? $("input[name=user]").val() : null
	}
	$.post("ajax.php?module=api&command=add_application",data)
	.done(function(res) {
		if(res.status) {
			var count = $('#api_application_list').bootstrapTable('getData').length
			$('#api_application_list').bootstrapTable('insertRow',{
				index: count + 1,
				row: {
					name: data.name,
					description: data.description,
					id: res.client_id
				}
			})
			$('#api-app').modal('hide')
			$('#api-info').modal('show')
			apiUpdateapplicationCredentials(res)
		} else {
			alert(res.message)
		}
	})
	.fail(function() {
		alert( "error" );
	})
})
$('#api-app').on('show.bs.modal', function () {
	$(".app_reset").val("")
	switch(app_type) {
		case "implicit":
		case "authorization_code":
			$('.api_app_redirect_group').removeClass("hidden")
		break;
		default:
			$('.api_app_redirect_group').addClass("hidden")
		break;
	}
	switch(app_type) {
		case "implicit":
			$("#api-app-title").text(_("Web-server App"))
			$("#api-app-description").html(_("The 'Web-server App' should be very familiar if you’ve ever signed into a web app using your Facebook or Google account"));
		break;
		case "authorization_code":
			$("#api-app-title").text(_('Browser-based/Single Page app'))
			$("#api-app-description").html(_("The 'Browser-based/Single Page app' is similar to the 'Web-server App' with two distinct differences")+"<br>"+_("It is intended to be used for user-agent-based clients (e.g. single page web apps) that can’t keep a client secret because all of the application code and storage is easily accessible")+"<br>"+_("Secondly instead of the authorization server returning an authorization code which is exchanged for an access token, the authorization server returns an access token"));
		break;
		case "password":
			$("#api-app-title").text(_('Native app'))
			$("#api-app-description").text("The 'Native app' is a great user experience for trusted first party clients both on the web and in native applications");
		break;
		case "client_credentials":
			$("#api-app-title").text(_('Machine-to-Machine app'))
			$("#api-app-description").text("The 'Machine-to-Machine app' is suitable for machine-to-machine authentication, for example for use in a cron job which is performing maintenance tasks over an API. Another example would be a client making requests to an API that don’t require user’s permission");
		break;
	}
})

$('#api_application_list').on('post-body.bs.table', function() {
	$(".api-delete").click(function() {
		if(confirm(_("Are you sure you wish to delete this application?"))) {
			var id = $(this).data("id");
			$.post("ajax.php?module=api&command=remove_application",{
				client_id:id,
				user: $("input[name=user]").length ? $("input[name=user]").val() : null
			})
			.done(function(res) {
				if(res.status) {
					$('#api_application_list').bootstrapTable('remove', {field: 'id', values: [id]});
				} else {
					alert(res.message)
				}
			})
			.fail(function() {
				alert( "error" );
			})
		}
	})

	$(".api-view").click(function() {
		$('#api-info').modal('show')
		$("#api-info .client_secret_container").addClass("hidden")
		$("#api-info .client_id").text($(this).data("id"));
	})
});
$("#api-application-regenerate").click(function() {
	if(confirm(_("Are you sure you wish to regenerate this application? This will break any clients using the old credentials"))) {
		var id = $("#api-info .client_id").text();
		$.post("ajax.php?module=api&command=regenerate_application",{client_id:id})
		.done(function(res) {
			if(res.status) {
				$('#api_application_list').bootstrapTable('remove', {field: 'id', values: [id]});
				var count = $('#api_application_list').bootstrapTable('getData').length
				$('#api_application_list').bootstrapTable('insertRow',{
					index: count + 1,
					row: {
						name: res.name,
						description: res.description,
						id: res.client_id,
						user: $("input[name=user]").length ? $("input[name=user]").val() : null
					}
				})
				apiUpdateapplicationCredentials(res)
			} else {
				alert(res.message)
			}
		})
		.fail(function() {
			alert( "error" );
		})
	}
})

function apiUpdateapplicationCredentials(res) {
	if(res.client_secret && res.client_secret.length) {
		$("#api-info .client_secret_container").removeClass("hidden")
		$("#api-info .client_secret").text(res.client_secret);
	} else {
		$("#api-info .client_secret_container").addClass("hidden")
		$("#api-info .client_secret").text("");
	}
	$("#api-info .client_id").text(res.client_id);
}

function apiActions(value, row, index, field) {
	return '<a class="clickable api-delete" data-id="'+value+'" data-index="'+index+'"><i class="fa fa-trash"></i></a> <a class="clickable api-view" data-id="'+value+'" data-index="'+index+'"><i class="fa fa-eye"></i></i></a>';
}
</script>
