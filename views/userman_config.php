<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-12">
						<?php show_view(__DIR__."/user/grid.php",["applications" => $applications])?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
		</div>
	</div>
</div>
<?php show_view(__DIR__."/user/modals.php",["applications" => $applications])?>
