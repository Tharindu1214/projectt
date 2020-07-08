<?php 
defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$customProductFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$customProductFrm->setFormTagAttribute('onsubmit', 'setupProduct(this); return(false);');

$customProductFrm->developerTags['colClassPrefix'] = 'col-md-';
$customProductFrm->developerTags['fld_default_col'] = 12;
if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1 )){	
	$lengthFld = $customProductFrm->getField('product_length');	
	$lengthFld->setWrapperAttribute( 'class' , 'product_length_fld');	
	$lengthFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId).'</small>';
		
	$widthFld = $customProductFrm->getField('product_width');	
	$widthFld->setWrapperAttribute( 'class' , 'product_width_fld');
	$widthFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId).'</small>';
		
	$heightFld = $customProductFrm->getField('product_height');	
	$heightFld->setWrapperAttribute( 'class' , 'product_height_fld');
	$heightFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId).'<small>';

	$dimensionUnitFld = $customProductFrm->getField('product_dimension_unit');	
	$dimensionUnitFld->setWrapperAttribute( 'class' , 'product_dimension_unit_fld');

	$weightFld = $customProductFrm->getField('product_weight');	
	$weightFld->setWrapperAttribute( 'class' , 'product_weight_fld');

	$weightUnitFld = $customProductFrm->getField('product_weight_unit');	
	$weightUnitFld->setWrapperAttribute( 'class' , 'product_weight_unit_fld');
}
$productCodEnabledFld = $customProductFrm->getField('product_cod_enabled');
$productCodEnabledFld->setWrapperAttribute( 'class' , 'product_cod_enabled_fld');

$productTypeFld = $customProductFrm->getField('product_type');
$productTypeFld->setfieldTagAttribute( 'onchange' , "showHideExtraFields();");

$shippingCountryFld = $customProductFrm->getField('shipping_country');	
$shippingCountryFld->setWrapperAttribute( 'class' , 'not-digital-js');

$shippFreeFld = $customProductFrm->getField('ps_free');	
$shippFreeFld->setWrapperAttribute( 'class' , 'not-digital-js');
	
/* $productEanUpcFld = $customProductFrm->getField('product_upc');
$productEanUpcFld->addFieldTagAttribute( 'onBlur', 'validateEanUpcCode(this.value)'); */
/* if($product_added_by_admin == 1 && $totalProducts >0 ){
	$shopUserName = $customProductFrm->getField('selprod_user_shop_name');
	$shopUserName->setfieldTagAttribute('readonly','readonly');
} */
?>
<script>
	var PRODUCT_TYPE_PHYSICAL = <?php echo Product::PRODUCT_TYPE_PHYSICAL; ?>;
	var PRODUCT_TYPE_DIGITAL = <?php echo Product::PRODUCT_TYPE_DIGITAL; ?>;
	
	function  showHideExtraFields() {
		var e = document.getElementById("product_type");
		var type = e.options[e.selectedIndex].value;
		if( type == PRODUCT_TYPE_PHYSICAL ){
			$(".product_length_fld").show();
			$(".product_width_fld").show();
			$(".product_height_fld").show();
			$(".product_dimension_unit_fld").show();
			$(".product_weight_fld").show();
			$(".product_weight_unit_fld").show();
			$(".product_cod_enabled_fld").show();
		}
		
		if( type == PRODUCT_TYPE_DIGITAL ){
			$(".product_length_fld").hide();
			$(".product_width_fld").hide();
			$(".product_height_fld").hide();
			$(".product_dimension_unit_fld").hide();
			$(".product_weight_fld").hide();
			$(".product_weight_unit_fld").hide();
			$(".product_cod_enabled_fld").hide();
		}
    }	</script>
<section class="section">
<div class="sectionhead">   
    <h4><?php echo Labels::getLabel('LBL_Custom_Catalog_Request',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" <?php echo ($preqId) ? "onClick='productForm( ".$preqId.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<li><a <?php echo ($preqId) ? "onClick='sellerProductForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info',$adminLangId); ?></a></li>
			<li><a  <?php echo ($preqId) ? "onclick='customCatalogSpecifications( ".$preqId." );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId );?></a></li>
			<?php foreach($languages as $langId=>$langName){?>
			<li class="<?php echo (!$preqId) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php echo ($preqId) ? "onClick='productLangForm( ".$preqId.",".$langId." );'" : ""; ?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
			<?php } ?>
			<?php if(count($productOptions)>0) { ?>
			<li><a <?php echo ($preqId) ? "onClick='customEanUpcForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup',$adminLangId); ?></a></li>
			<?php }?>
			<li><a <?php echo ($preqId) ? "onClick='updateStatusForm( ".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $customProductFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>
<script  type="text/javascript">
function removeProductTag(id){
	$('#product-tag'+id).remove();;
}
function removeProductOption(id){
	$('#product-option'+id).remove();;
}

$("document").ready(function(){
	$('input[name=\'brand_name\']').autocomplete({
		'source': function(request, response) {			
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
	
	$('input[name=\'category_name\']').autocomplete({
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
			$('input[name=\'category_name\']').val(item['label']);
			$('input[name=\'product_category_id\']').val(item['value']);
		}
	});
	
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
			$('input[name=\'option_name\']').val('');
			$('#product-option' + item['value']).remove();
			$('#product-option-js').append('<li id="product-option' + item['value'] + '"><span class="left" ><a href="javascript:void(0)" title="Remove" onClick="removeProductOption('+ item['value'] +');"><i class="icon ion-close" data-option-id="' + item['value'] + '"></i></a></span><span class="left">' + item['label'] +'<input type="hidden" value="'+ item['value'] +'"  name="product_option[]"></span></li>');			
		}
	});
	
	var options = new Array();
	<?php  if(!empty($productOptions)){
	foreach($productOptions as $key => $val){ ?>
		options.push('<?php echo $val; ?>');
	<?php } } ?>
	var data = {'options':options};
	fcom.ajax(fcom.makeUrl('CustomProducts', 'loadCustomProductOptionss'), data, function(t) {
		$('#product-option-js').html(t);
	});	
		
	$('input[name=\'tag_name\']').autocomplete({
		'source': function(request, response) {			
			$.ajax({
				url: fcom.makeUrl('tags', 'autoComplete'),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] + ' (' + item['tag_identifier'] + ')',	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			$('input[name=\'tag_name\']').val('');
			$('#product-tag' + item['value']).remove();
			$('#product-tag-js').append('<li id="product-tag' + item['value'] + '"><span class="left "><a href="javascript:void(0)" title="Remove" onClick="removeProductTag('+ item['value'] +');"><i class="icon ion-close remove_tag-js" data-tag-id="'+ item['value'] +'"></i></a></span><span class="left">' +item['label']+'<input type="hidden" value="'+ item['value'] +'"  name="product_tags[]"></span></li></li>');
		}
	});
	
	var tags = new Array();
	<?php if(!empty($productTags)){
	foreach($productTags as $key => $val){ ?>
		tags.push('<?php echo $val; ?>');
	<?php } } ?>
	var data = {'tags':tags};
	fcom.ajax(fcom.makeUrl('CustomProducts', 'loadCustomProductTags'), data, function(t) {
		$('#product-tag-js').html(t);
	});	
});

var prodTypeDigital = <?php echo Product::PRODUCT_TYPE_DIGITAL;?>;
var productId = <?php echo $preqId;?>;
	
var productOptions =[];
var dv =$("#listing");

$(document).ready(function(){
	/* Shipping Information */
	$('input[name=\'shipping_country\']').autocomplete({
		'source': function(request, response) {
		
			$.ajax({
				url: fcom.makeUrl('products', 'countries_autocomplete'),
				data: {keyword: request,fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {					
						return { 
							label: item['name'] ,
							value: item['id']
						};
					}));
				},
			});
		},
		'select': function(item) {
				$('input[name=\'shipping_country\']').val(item.label);
				$('input[name=\'ps_from_country_id\']').val(item.value);
		}
			 
	});

	$('input[name=\'shipping_country\']').keyup(function(){
		$('input[name=\'ps_from_country_id\']').val('');
	});
	
	$('select[name=\'product_type\']').change(function(){
		addShippingTab(productId,prodTypeDigital);
	});
	addShippingTab(productId,prodTypeDigital);
});
</script>