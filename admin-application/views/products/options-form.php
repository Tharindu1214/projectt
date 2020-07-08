<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmOptions->setFormTagAttribute('class', 'web_form form_horizontal');
$frmOptions->developerTags['colClassPrefix'] = 'col-md-';
$frmOptions->developerTags['fld_default_col'] = 12;
?>
<section class="section">
<div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_Product_Options_Management_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
 <div class="row">
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active"  <?php echo ($product_id) ? "onclick='productOptionsForm( ".$product_id.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId);?></a></li>	
			<li class="<?php echo (!$product_id) ? 'fat-inactive' : ''; ?>"><a <?php echo ($product_id) ? "onclick='upcForm( ".$product_id.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_Setup',$adminLangId);?></a></li>			
		</ul>
		<div class="tabs_panel_wrap" style="min-height: 500px;">
			<div class="tabs_panel">
				<?php echo $frmOptions->getFormHtml(); ?>
				<div id="product_options_list" class="col-xs-10" ></div>
			</div>			
		</div>
	</div>
</div></div></div></section>
<script type="text/javascript">
$("document").ready(function(){
	$('input[name=\'option_name\']').autocomplete({
		'source': function(request, response) {			
			$.ajax({
				url: fcom.makeUrl('options', 'autoComplete'),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] + ' (' + item['option_identifier'] + ')',	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			updateProductOption(<?php echo $product_id; ?>, item['value'] );
		}
	});
});
</script>