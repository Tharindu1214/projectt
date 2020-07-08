<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupCommission(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Commission_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="border-box border-box--space">
			<?php echo $frm->getFormHtml(); ?>
		</div>
	</div>										
</section>
<script type="text/javascript">
$("document").ready(function(){
	$('input[name=\'user_name\']').autocomplete({
		'source': function(request, response) {			
			$.ajax({
				url: fcom.makeUrl('Commission', 'userAutoComplete'),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'],	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			$('input[name=\'user_name\']').val(item['label']);
			$('input[name=\'commsetting_user_id\']').val(item['value']);
		}
	});
	
	$('input[name=\'product\']').autocomplete({
		'source': function(request, response) {			
			$.ajax({
				url: fcom.makeUrl('Commission', 'productAutoComplete'),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'],	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			$('input[name=\'product\']').val(item['label']);
			$('input[name=\'commsetting_product_id\']').val(item['value']);
		}
	});
	
	
	$('input[name=\'user_name\']').keyup(function(){
		$('input[name=\'commsetting_user_id\']').val('');
	});
	
	$('input[name=\'product\']').keyup(function(){
		$('input[name=\'commsetting_product_id\']').val('');
	});
});
</script>