<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" form="form_payment" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
				<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
			</div>
			<div class="panel-body">
				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form_payment" class="form-horizontal">
					<ul class="nav nav-tabs">
						<li class="nav-tab active"><a href="#tab_general" data-toggle="tab"><?php echo $text_general; ?></a></li>
						<li class="nav-tab"><a href="#tab_order_status" data-toggle="tab"><?php echo $text_order_status; ?></a></li>
					</ul>
		  
					<div class="tab-content">
						<div class="tab-pane active" id="tab_general">
							<div class="form-group required">
								<label class="col-sm-2 control-label" for="input_api_key"><?php echo $entry_api_key; ?></label>
								<div class="col-sm-10">
									<input type="text" name="crezco_api_key" value="<?php echo $api_key; ?>" placeholder="<?php echo $entry_api_key; ?>" id="input_api_key" class="form-control" />
									<?php if ($error_api_key) { ?>
									<div class="text-danger"><?php echo $error_api_key; ?></div>
									<?php } ?>
								</div>
							</div>
							<div class="form-group required">
								<label class="col-sm-2 control-label" for="input_partner_id"><?php echo $entry_partner_id; ?></label>
								<div class="col-sm-10">
									<input type="text" name="crezco_partner_id" value="<?php echo $partner_id; ?>" placeholder="<?php echo $entry_partner_id; ?>" id="input_partner_id" class="form-control" />
									<?php if ($error_partner_id) { ?>
									<div class="text-danger"><?php echo $error_partner_id; ?></div>
									<?php } ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_environment"><?php echo $entry_environment; ?></label>
								<div class="col-sm-10">
									<select name="crezco_environment" id="input_environment" class="form-control">
										<?php if ($environment == 'production') { ?>
										<option value="production" selected="selected"><?php echo $text_production; ?></option>
										<option value="sandbox"><?php echo $text_sandbox; ?></option>
										<?php } else { ?>
										<option value="production"><?php echo $text_production; ?></option>
										<option value="sandbox" selected="selected"><?php echo $text_sandbox; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_connect"><?php echo $entry_connect; ?></label>
								<div class="col-sm-10">
									<div id="section_connect" <?php if ($user_id) { ?>class="hidden"<?php } ?>>
										<a id="button_connect" class="btn btn-primary"><?php echo $button_connect; ?></a>
									</div>
									<?php if ($user_id) { ?>
									<div id="section_disconnect">
										<p class="alert alert-info"><?php echo $text_connect; ?></p>
										<a id="button_disconnect" class="btn btn-danger"><?php echo $button_disconnect; ?></a>
									</div>
									<?php } ?>
									<input type="hidden" name="crezco_user_id" value="<?php echo $user_id; ?>" id="input_user_id" />
									<input type="hidden" name="crezco_webhook_id" value="<?php echo $webhook_id; ?>" id="input_webhook_id" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_debug"><?php echo $entry_debug; ?></label>
								<div class="col-sm-10">
									<select name="crezco_debug" id="input_debug" class="form-control">
										<?php if ($debug) { ?>
										<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
										<option value="0"><?php echo $text_disabled; ?></option>
										<?php } else { ?>
										<option value="1"><?php echo $text_enabled; ?></option>
										<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
								<div class="col-sm-10">
									<input type="text" name="crezco_total" value="<?php echo $total; ?>" placeholder="<?php echo $entry_total; ?>" id="input_total" class="form-control" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_geo_zone"><?php echo $entry_geo_zone; ?></label>
								<div class="col-sm-10">
									<select name="crezco_geo_zone_id" id="input_geo_zone" class="form-control">
										<option value="0"><?php echo $text_all_zones; ?></option>
										<?php foreach ($geo_zones as $geo_zone) { ?>
										<?php if ($geo_zone['geo_zone_id'] == $geo_zone_id) { ?>
										<option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
										<?php } else { ?>
										<option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
										<?php } ?>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_status"><?php echo $entry_status; ?></label>
								<div class="col-sm-10">
									<select name="crezco_status" id="input_status" class="form-control">
										<?php if ($status) { ?>
										<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
										<option value="0"><?php echo $text_disabled; ?></option>
										<?php } else { ?>
										<option value="1"><?php echo $text_enabled; ?></option>
										<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_sort_order"><?php echo $entry_sort_order; ?></label>
								<div class="col-sm-10">
									<input type="text" name="crezco_sort_order" value="<?php echo $sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input_sort_order" class="form-control" />
								</div>
							</div>
						</div>
						<div class="tab-pane" id="tab_order_status">
							<?php foreach ($setting['order_status'] as $crezco_order_status) { ?>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input_order_status_<?php echo $crezco_order_status['code']; ?>"><?php echo ${$crezco_order_status['name']}; ?></label>
								<div class="col-sm-10">
									<select name="crezco_setting[order_status][<?php echo $crezco_order_status['code']; ?>][id]" id="input_<?php echo $crezco_order_status['code']; ?>_status" class="form-control">
										<?php foreach ($order_statuses as $order_status) { ?>
										<?php if ($order_status['order_status_id'] == $crezco_order_status['id']) { ?>
										<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
										<?php } else { ?>
										<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
										<?php } ?>
										<?php } ?>
									</select>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">

function showAlert(json) {
	$('.alert, .text-danger').remove();
	$('.form-group').removeClass('has-error');
						
	if (json['error']) {
		if (json['error']['warning']) {
			if ($('#modal-dialog').length > 0) {
				$('#modal-dialog .modal-body').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error']['warning'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			} else {
				$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error']['warning'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}
		}				
				
		for (i in json['error']) {
			var element = $('#input_' + i);
					
			if (element.parent().hasClass('input-group')) {
                $(element).parent().after('<div class="text-danger">' + json['error'][i] + '</div>');
			} else {
				$(element).after('<div class="text-danger">' + json['error'][i] + '</div>');
			}
		}				
				
		$('.text-danger').parents('.form-group').addClass('has-error');
	}
			
	if (json['success']) {
		$('#content > .container-fluid').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '  <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	}
}

$('body').on('click', '#button_connect', function() {
	$.ajax({
		type: 'post',
		url: '<?php echo $connect_url; ?>',
		data: $('[name]'),
		dataType: 'json',
		success: function(json) {
			if (json['redirect']) {
				location = json['redirect'];
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('body').on('click', '#button_disconnect', function() {
	if (confirm('<?php echo $text_confirm; ?>')) {		
		$('#input_user_id').val('');
		$('#section_connect').removeClass('hidden');
		$('#section_disconnect').addClass('hidden');
		
		$.ajax({
			type: 'post',
			url: '<?php echo $disconnect_url; ?>',
			data: $('[name]'),
			dataType: 'json',
			success: function(json) {

			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
});

$('body').on('change', '#input_environment', function() {	
	$('#input_user_id').val('');
	$('#section_connect').removeClass('hidden');
	$('#section_disconnect').addClass('hidden');
	
	$.ajax({
		type: 'post',
		url: '<?php echo $disconnect_url; ?>',
		data: $('[name]'),
		dataType: 'json',
		success: function(json) {
			
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

</script> 
<?php echo $footer; ?>