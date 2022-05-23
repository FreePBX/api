var host = window.location.protocol+"//"+window.location.host;

$(".fa-clipboard").click(function() {
	$("#copytmp").val("");
	$("#copytmp").val($("."+this.id).text());
	copyURL = document.getElementById("copytmp");
	copyURL.select();
	document.execCommand("copy");
});

$(".clipboard-um").click(function() {
	$("#copytmpum").val($("."+this.id).text());
	copyURL = document.getElementById("copytmpum");
	copyURL.select();
	document.execCommand("copy");
});

$(".api-select").click( function(){
	var id = this.id;
	$("#"+id).addClass("active");
	switch(id){
		case "HTTP":
			if($("#HTTPS").hasClass("active")){
				$("#HTTPS").removeClass("active");			
			}		
			if($("#ADMINPorts").hasClass("active")){
				if($("#acp").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#acp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}				
			}
			if($("#APIPorts").hasClass("active")){
				if($("#restapi").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#restapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}	
			}		
			break;
		case "HTTPS":
			if($("#HTTP").hasClass("active")){
				$("#HTTP").removeClass("active");			
			}	
			if($("#ADMINPorts").hasClass("active")){
				if($("#sslacp").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslacp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}					
			}
			if($("#APIPorts").hasClass("active")){
				if($("#sslrestapi").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslrestapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}	
			}	
			break;
		case "APIPorts":
			if($("#ADMINPorts").hasClass("active")){
				$("#ADMINPorts").removeClass("active");
			}
			if($("#HTTP").hasClass("active")){
				if($("#restapi").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#restapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}	
			}
			if($("#HTTPS").hasClass("active")){
				if($("#sslrestapi").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslrestapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}	
			}	
			break;
		case "ADMINPorts":
			if($("#APIPorts").hasClass("active")){
				$("#APIPorts").removeClass("active");
			}
			if($("#HTTP").hasClass("active")){
				if($("#acp").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#acp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}		
			}
			if($("#HTTPS").hasClass("active")){
				if($("#sslacp").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslacp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled").show();
				}	
			}			
			break;
	}
	$(".url").text(urlAPI);
});

$(".api-select-um").click( function(){
	var id = this.id;
	$("#"+id).addClass("active");
	switch(id){
		case "HTTPum":
			if($("#HTTPSum").hasClass("active")){
				$("#HTTPSum").removeClass("active");			
			}		
			if($("#ADMINPortsum").hasClass("active")){
				if($("#acp").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#acp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}				
			}
			if($("#APIPortsum").hasClass("active")){
				if($("#restapi").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#restapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}	
			}		
			break;
		case "HTTPSum":
			if($("#HTTPum").hasClass("active")){
				$("#HTTPum").removeClass("active");			
			}	
			if($("#ADMINPortsum").hasClass("active")){
				if($("#sslacp").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslacp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}					
			}
			if($("#APIPortsum").hasClass("active")){
				if($("#sslrestapi").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslrestapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}	
			}	
			break;
		case "APIPortsum":
			if($("#ADMINPortsum").hasClass("active")){
				$("#ADMINPortsum").removeClass("active");
			}
			if($("#HTTPum").hasClass("active")){
				if($("#restapi").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#restapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}	
			}
			if($("#HTTPSum").hasClass("active")){
				if($("#sslrestapi").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslrestapi").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}	
			}	
			break;
		case "ADMINPortsum":
			if($("#APIPortsum").hasClass("active")){
				$("#APIPortsum").removeClass("active");
			}
			if($("#HTTPum").hasClass("active")){
				if($("#acp").val() != "disabled"){
					var urlAPI = "http://"+$("#fqdn").val()+":"+$("#acp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}		
			}
			if($("#HTTPSum").hasClass("active")){
				if($("#sslacp").val() != "disabled"){
					var urlAPI = "https://"+$("#fqdn").val()+":"+$("#sslacp").val()+"/admin";
					$(".url-list").show();
					$(".proto-disabled-um").hide();
				}
				else{
					$(".url-list").hide();
					$(".proto-disabled-um").show();
				}	
			}			
			break;
	}
	$(".url").text(urlAPI);
});

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
		allowed_scopes: $("#api_app_allowed_scopes").val(),
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
					id: res.client_id,
					username: res.owner,
					grant_type: res.type,
					client_id: res.client_id,
					allowed_scopes: res.allowed_scopes
				}
			})
			$('#api-app').modal('hide')
			$('#api-info').modal('show')
			if($("#sysadmin").val() == "enabled"){
				$(".button-filter").show();
		
				if($(".url-token").text().search("http://") == -1){
					$("#HTTPS").click();
					var https = true;
				}
				else{
					$("#HTTP").click();
					var http = true;
				};
		
				if($("#restapi").val() != "" && http){
					$("#APIPorts").click();
				}
		
				if($("#sslrestapi").val() != "" && https){
					$("#APIPorts").click();
				}
			}
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
			$("#api-app-title").text(_('Browser-based/Single Page app'))
			$("#api-app-description").html(_("The 'Browser-based/Single Page app', also known as 'Implicit Code Grant', is similar to the 'Web-server App' with two distinct differences")+"<br>"+_("It is intended to be used for user-agent-based clients (e.g. single page web apps) that can’t keep a client secret because all of the application code and storage is easily accessible")+"<br>"+_("Secondly instead of the authorization server returning an authorization code which is exchanged for an access token, the authorization server returns an access token"));
		break;
		case "authorization_code":
			$("#api-app-title").text(_("Web-server App"))
			$("#api-app-description").html(_("The 'Web-server App', also known as 'Authorization Code Grant', should be very familiar if you’ve ever signed into a web app using your Facebook or Google account"));
		break;
		case "password":
			$("#api-app-title").text(_('Native app'))
			$("#api-app-description").text("The 'Native app', also known as 'Resource owner password credentials grant', is a great user experience for trusted first party clients both on the web and in native applications");
		break;
		case "client_credentials":
			$("#api-app-title").text(_('Machine-to-Machine app'))
			$("#api-app-description").text("The 'Machine-to-Machine app', also known as 'Client credentials grant', is suitable for machine-to-machine authentication, for example for use in a cron job which is performing maintenance tasks over an API. Another example would be a client making requests to an API that don’t require user’s permission");
		break;
	}
})

$("#urlModal").on('shown.bs.modal', function(){
	if($("#sysadmin").val() == "enabled"){
		$(".button-filter").show();

		if($(".url-token").text().search("http://") == -1){
			$("#HTTPSum").click();
			var https = true;
		}else{
			$("#HTTPum").click();
			var http = true;
		};

		if($("#restapi").val() != "" && http){
			$("#APIPortsum").click();
		}

		if($("#sslrestapi").val() != "" && https){
			$("#APIPortsum").click();
		}
	}
});

$('#api_application_list').on('post-body.bs.table', function() {
	$(".api-delete").click(function() {
		if(confirm(_("Are you sure you wish to delete this application?"))) {
			var client_id = $(this).data("client_id");
			var user = $(this).data("user") !== "null" ? $(this).data("user") : null;
			$.post("ajax.php?module=api&command=remove_application",{
				client_id: client_id,
				user: user
			})
			.done(function(res) {
				if(res.status) {
					$('#api_application_list').bootstrapTable('refresh');
					$('#api_access_token_list').bootstrapTable('refresh');
					$('#api_refresh_token_list').bootstrapTable('refresh');
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
		$('#api-info').modal('show');
		if($("#sysadmin").val() == "enabled"){
			$(".button-filter").show();

			if($(".url-token").text().search("http://") == -1){
				$("#HTTPS").click();
				var https = true;
			}else{
				$("#HTTP").click();
				var http = true;
			};
	
			if($("#restapi").val() != "" && http){
				$("#APIPorts").click();
			}

			if($("#sslrestapi").val() != "" && https){
				$("#APIPorts").click();
			}
		}

		$("#api-info .client_secret_container").addClass("hidden");
		$("#api-info .client_id").text($(this).data("client_id"));
		$("#api-info .allowed_scopes").text($(this).data("allowed_scopes"));
	})
});

$('#api_access_token_list').on('post-body.bs.table', function() {
	$(".api-delete-access-token").click(function() {
		if(confirm(_("Are you sure you wish to delete this access token?"))) {
			var id = $(this).data("id");
			$.post("ajax.php?module=api&command=remove_access_token",{
				id: id
			})
			.done(function(res) {
				if(res.status) {
					$('#api_access_token_list').bootstrapTable('refresh');
					$('#api_refresh_token_list').bootstrapTable('refresh');
				} else {
					alert(res.message)
				}
			})
			.fail(function() {
				alert( "error" );
			})
		}
	})
});

$('#api_refresh_token_list').on('post-body.bs.table', function() {
	$(".api-delete-refresh-token").click(function() {
		if(confirm(_("Are you sure you wish to delete this refresh token?"))) {
			var id = $(this).data("id");
			$.post("ajax.php?module=api&command=remove_refresh_token",{
				id: id
			})
			.done(function(res) {
				if(res.status) {
					$('#api_refresh_token_list').bootstrapTable('refresh');
				} else {
					alert(res.message)
				}
			})
			.fail(function() {
				alert( "error" );
			})
		}
	})
});

$("#api-application-regenerate").click(function() {
	if(confirm(_("Are you sure you wish to regenerate this application? This will break any clients using the old credentials"))) {
		var id = $("#api-info .client_id").text();
		$.post("ajax.php?module=api&command=regenerate_application",{client_id:id})
		.done(function(res) {
			if(res.status) {
				$('#api_application_list').bootstrapTable('refresh');
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
	$("#api-info .allowed_scopes").text(res.allowed_scopes);
}

function grantType(value, row, index, field) {
	switch(value) {
		case "implicit":
			return _('Browser-based/Single Page app')
		break;
		case "authorization_code":
			return _("Web-server App")
		break;
		case "password":
			return _('Native app')
		break;
		case "client_credentials":
			return _('Machine-to-Machine app')
		break;
	}
}

function apiUsername(value, row, index, field) {
	return (value !== null ? value : _('System'));
}

function apiActions(value, row, index, field) {
	return '<a class="clickable api-delete" data-id="'+value+'" data-client_id="'+row.client_id+'" data-user="'+row.owner+'" data-index="'+index+'"><i class="fa fa-trash"></i></a> <a class="clickable api-view" data-id="'+value+'" data-index="'+index+'" data-client_id="'+row.client_id+'" data-user="'+row.owner+'" data-allowed_scopes="'+row.allowed_scopes+'"><i class="fa fa-eye"></i></a>';
}

function apiTime(value, row, index, field) {
	return moment.unix(value).format(datetimeformat);
}

function apiRefreshTokenActions(value, row, index, field) {
	return '<a class="clickable api-delete-refresh-token" data-id="'+value+'" data-index="'+index+'"><i class="fa fa-trash"></i></a>';
}

function apiAccessTokenActions(value, row, index, field) {
	return '<a class="clickable api-delete-access-token" data-id="'+value+'" data-index="'+index+'"><i class="fa fa-trash"></i></a>';
}

var automated = false;
$(function () {
	$.getJSON( "ajax.php?module=api&command=getJSTreeScopes", function( data ) {
		$('#jstree_demo_div').jstree({
			'core' : {
				'check_callback' : true,
				'data' :data ,
			},
			checkbox: {
	      three_state : true, // to avoid that fact that checking a node also check others
	      whole_node : true,  // to avoid checking the box just clicking the node
	      tie_selection : false // for checking without selecting and selecting without checking
	    },

			"plugins" : ["search", "checkbox"]
		}).on("check_node.jstree uncheck_node.jstree uncheck_all.jstree check_all.jstree", function(e, data) {
			var checked = $(this).jstree("get_top_checked");
			$("#scope-area").val(checked.join(" "))
			$("#scope-doc").val(checked.join(" "))
			$("#scope-explorer").val(checked.join(" "))
			automated = true; //prevent loop
			$.each($('#scope_visualizer_list').bootstrapTable("getData"), function(k,v) {
				$('#scope_visualizer_list').bootstrapTable("uncheck",k)
			})
			//$('#scope_visualizer_list').bootstrapTable("uncheckAll")
			$('#scope_visualizer_list').bootstrapTable("checkBy", {field:"scope", values:checked})
			automated = false; //end prevention
		}).on("after_open.jstree after_close.jstree", function(e, data) {
			//$("#scope-area").css("height",$('#jstree_demo_div').height()+'px');
		});
	});
});
$('#scope_visualizer_list').on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function (e) {
	//prevent loop
	if(automated) {
		return;
	}
	var checked = $('#scope_visualizer_list').bootstrapTable('getAllSelections');
	var check = [];
	$.each(checked, function(k,v) {
		check.push(v.scope)
	})
	$('#jstree_demo_div').jstree("uncheck_all")
	$('#jstree_demo_div').jstree("check_node",check)
});

$("#api_types").change(function() {
	var sel = $("#api_types").val();
	$('#scope_visualizer_list').bootstrapTable('filterBy',{type:sel});
})

$("#copy-scopes").click(function() {
	$("#scope-area").select();
	document.execCommand('copy');
	fpbxToast("Copied")
})

$("#generate-docs").click(function() {
	if(!$("#scope-doc").val().length) {
		alert("Please define a valid scope")
		return;
	}
	$("#generate-docs").prop("disabled",true)
	$("#doc-container")[0].src = ""
	$.post( "ajax.php?module=api&command=generatedocs", { scopes: $("#scope-doc").val(), host: host },function( data ) {
		$("#doc-container")[0].src = "modules/api/docs/index.html"
		$("#doc-buttons").removeClass("hidden")
	})
	.always(function() {
		$("#generate-docs").prop("disabled",false)
	})
})

$("#docs-home").click(function() {
	$("#doc-container")[0].src = "modules/api/docs/index.html"
})
$("#docs-back").click(function() {
	$("#doc-container")[0].contentWindow.history.go(-1);
})
$("#docs-forward").click(function() {
	$("#doc-container")[0].contentWindow.history.go(1);
})
