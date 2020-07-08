<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$linksFrm->setFormTagAttribute('class', 'web_form form_horizontal');
if(!empty($polling_type)){
	if($polling_type == Polling::POLLING_TYPE_PRODUCTS){
		$polling_type_text = 'Products';
	} else if($polling_type == Polling::POLLING_TYPE_CATEGORY){
		$polling_type_text = 'Categories';
	}
}
else{
	die(Labels::getLabel('LBL_Required_variables_not_passed.',$adminLangId));
}
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Link',$adminLangId); ?> <?php echo $polling_type_text; ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0)" onclick="pollingForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive=( $polling_id == 0 )?'fat-inactive':'';	
			foreach( $languages as $langId=>$langName ){ ?>
				<li class="<?php echo $inactive;?> "><a href="javascript:void(0);" 
				<?php if($polling_id>0){?> onclick="pollingLangForm(<?php echo $polling_id ?>, <?php echo $langId;?>);" <?php }?>>
				<?php echo $langName;?></a></li>
			<?php } ?>
			<li><a class="active" href="javascript:void(0)" onclick="linksForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_Link',$adminLangId); ?> <?php echo $polling_type_text; ?></a></li>
		</ul>
		<div class="tabs_panel_wrap" style="min-height:300px">
			<div class="tabs_panel">
				<?php echo $linksFrm->getFormHtml(); ?>
				<div id="linked_entities_list" class="col-xs-10" ></div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$("document").ready(function(){
	<?php if( $polling_type == Polling::POLLING_TYPE_PRODUCTS ){?>
	reloadLinkedProducts(<?php echo $polling_id; ?>);
	$('input[name=\'product\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: '<?php echo CommonHelper::generateUrl('Products','autoComplete'); ?>',
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] ,	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			updateLinkedProducts(<?php echo $polling_id; ?>, item['value'] );
		}
	});
	<?php } elseif( $polling_type == Polling::POLLING_TYPE_CATEGORY ){ ?>
	reloadLinkedCategories(<?php echo $polling_id; ?>);
	$('input[name=\'category\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: '<?php echo CommonHelper::generateUrl('ProductCategories','autoComplete'); ?>',
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['prodcat_identifier'] ,	value: item['prodcat_id']	};
					}));
				},
			});
		},
		'select': function(item) {
			updateLinkedCategories(<?php echo $polling_id; ?>, item['value'] );
		}
	});
	<?php } ?>
});
</script>