<div id="crezco_form">
	<?php if ($error_warning) { ?>
	<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
	<?php } ?>
	<div class="buttons">
		<div class="pull-right">
			<input type="button" value="<?php echo $button_confirm; ?>" id="crezco_button_confirm" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" />
		</div>
	</div>
</div>
<script type="text/javascript">

$('#crezco_button_confirm').on('click', function() {	
	$.ajax({
		type: 'post',
		url: 'index.php?route=extension/payment/crezco/confirm',
		data: '',
		dataType: 'json',
		beforeSend: function() {
			$('#crezco_button_confirm').button('loading');
		},
		complete: function() {
			$('#crezco_button_confirm').button('reset');
		},
		success: function(json) {
			showCrezcoAlert(json);
			
			if (json['redirect']) {
				location = json['redirect'];	
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

function showCrezcoAlert(json) {
	$('#crezco_form .alert').remove();
				
	if (json['error']) {
		if (json['error']['warning']) {
			$('#crezco_form').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><button type="button" class="close" data-dismiss="alert">&times;</button> ' + json['error']['warning'] + '</div>');
		}
	}
}

</script>