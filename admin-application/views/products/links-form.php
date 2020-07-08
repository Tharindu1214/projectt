<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmLinks->setFormTagAttribute('class', 'web_form form_horizontal');
$frmLinks->setFormTagAttribute('onsubmit', 'setupProductLinks(this); return(false);');
$frmLinks->addHiddenField('','product_id',$productId);
$fld_product_name = $frmLinks->getField('product_name');
$fld_product_name->setFieldTagAttribute('readonly','readonly');
$fld_product_name->setFieldTagAttribute('disabled','disabled');
$fld_brand = $frmLinks->getField('brand_name');

$frmLinks->developerTags['colClassPrefix'] = 'col-md-';
$frmLinks->developerTags['fld_default_col'] = 12;
//$fld_brand->setFieldTagAttribute('autocomplete','off');
//$fld_brand->setFieldTagAttribute('onKeyUp','brandsAutoComplete(this)');

?>

<section class="section">
<div class="sectionhead">

    <h4><?php echo Labels::getLabel('LBL_Product_Links_Management_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
 <div class="row">

<div class="col-sm-12">

	<div class="tabs_nav_container responsive flat ovrflow-none">
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frmLinks->getFormHtml(); ?>
				<div id="product_links_list" class="col-xs-10" ></div>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>
<script type="text/javascript">
$("document").ready(function(){
	$('input[name=\'brand_name\']').autocomplete({
		'source': function(request, response) {
			/* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
				response($.map(json, function(item) {
						return { label: item['name'],	value: item['id']	};
					}));
			}); */
			$.ajax({
				url: fcom.makeUrl('brands', 'autoComplete'),
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
			$('input[name=\'brand_name\']').val(item['label']);
			$('input[name=\'product_brand_id\']').val(item['value']);
		}
	});
	
	$('input[name=\'brand_name\']').keyup(function(){
		$('input[name=\'product_brand_id\']').val('');
	});

	$('input[name=\'choose_links\']').autocomplete({
	'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('productCategories', 'links_autocomplete'),
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
		updateProductLink(<?= $productId;?>, item['value'] );
		}
	});	
	
});
</script>
