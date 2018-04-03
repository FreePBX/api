<!DOCTYPE html>
<html>
	<head>
		<title>Authorize your account</title>
		<link href="http://andrew15.sangoma.tech/admin/assets/css/bootstrap-3.3.7.min.css" rel="stylesheet" type="text/css">
		<link href="http://andrew15.sangoma.tech/admin/assets/css/font-awesome.min-4.7.0.css" rel="stylesheet" type="text/css">
	</head>
	<body style="padding: 5px;">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<h1><?php echo sprintf(_("Authorize %s to use your %s account?"),$app_name,$server);?></h1>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<form>
						<div class="form-group">
							<input type="text" name="username" class="form-control" placeholder="Username">
						</div>
						<div class="form-group">
							<input type="password" name="password" class="form-control" placeholder="Password">
						</div>
						<br>
						<button class="btn btn-primary">Submit</button>
						<button class="btn btn-default">Cancel</button>
					</form>
				</div>
				<div class="col-xs-8 pull-right text-right">
					<img src="http://andrew15.sangoma.tech/admin/<?php echo $image?>">
					<br>
					<strong><?php echo $app_name?></strong>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<br>
					<p class="text-success"><strong><?php echo _("This application is requesting being able to:")?></strong></p>
					<ul>
					<?php foreach($visualScopes as $typeInfo) { ?>
						<li><?php echo $typeInfo['description']?> <?php echo !empty($module['modData']['name']) ? '('.$module['modData']['name'].')' : ''?></li>
					<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</body>
</html>
