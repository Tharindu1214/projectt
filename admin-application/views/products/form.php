<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$productFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$productFrm->setFormTagAttribute('onsubmit', 'setupProduct(this); return(false);');

$productFrm->developerTags['colClassPrefix'] = 'col-md-';
$productFrm->developerTags['fld_default_col'] = 12;

if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1 ))
{
	$lengthFld = $productFrm->getField('product_length');
	$lengthFld->setWrapperAttribute( 'class' , 'product_length_fld');
	$lengthFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId).'</small>';

	$widthFld = $productFrm->getField('product_width');
	$widthFld->setWrapperAttribute( 'class' , 'product_width_fld');
	$widthFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId).'</small>';

	$heightFld = $productFrm->getField('product_height');
	$heightFld->setWrapperAttribute( 'class' , 'product_height_fld');
	$heightFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Note:_Used_for_Shipping_Calculation.',$adminLangId).'</small>';

	$dimensionUnitFld = $productFrm->getField('product_dimension_unit');
	$dimensionUnitFld->setWrapperAttribute( 'class' , 'product_dimension_unit_fld');

	$weightFld = $productFrm->getField('product_weight');
	$weightFld->setWrapperAttribute( 'class' , 'product_weight_fld');

	$weightUnitFld = $productFrm->getField('product_weight_unit');
	$weightUnitFld->setWrapperAttribute( 'class' , 'product_weight_unit_fld');
}

$productCodEnabledFld = $productFrm->getField('product_cod_enabled');
$productCodEnabledFld->setWrapperAttribute( 'class' , 'product_cod_enabled_fld');

$productTypeFld = $productFrm->getField('product_type');
$productTypeFld->setfieldTagAttribute( 'onchange' , "addShippingTab(".$product_id.",".Product::PRODUCT_TYPE_DIGITAL.");showHideExtraFields();");

$shippingCountryFld = $productFrm->getField('shipping_country');
$shippingCountryFld->setWrapperAttribute( 'class' , 'not-digital-js');

$shippFreeFld = $productFrm->getField('ps_free');
$shippFreeFld->setWrapperAttribute( 'class' , 'not-digital-js');


if($product_added_by_admin == 1 && $totalProducts >0 ){
	$shopUserName = $productFrm->getField('selprod_user_shop_name');
	$shopUserName->setfieldTagAttribute('readonly','readonly');
}
?>
<script>
	var PRODUCT_TYPE_PHYSICAL = <?php echo Product::PRODUCT_TYPE_PHYSICAL; ?>;
	var PRODUCT_TYPE_DIGITAL = <?php echo Product::PRODUCT_TYPE_DIGITAL; ?>;
	var CONF_PRODUCT_DIMENSIONS_ENABLE = <?php echo FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1 ); ?>;

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

    <h4><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" <?php echo ($product_id) ? "onclick='productForm( ".$product_id.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php
				foreach($languages as $langId=>$langName){?>
					<li class="<?php echo (!$product_id) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php echo ($product_id) ? "onclick='productLangForm( ".$product_id.",".$langId." );'" : ""; ?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
				<?php }
			?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $productFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>
<script  type="text/javascript">
	//var prodTypeDigital = <?php echo Product::PRODUCT_TYPE_DIGITAL;?>;
	//var productId=<?php echo $product_id;?>;
	var product_added_by_admin = <?php echo $product_added_by_admin; ?>;
	var totalProducts = <?php echo $totalProducts; ?>;

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
//debugger;

		$('input[name=\'shipping_country\']').keyup(function(){
			$('input[name=\'ps_from_country_id\']').val('');
		});


	if( product_added_by_admin == 1 && totalProducts ==0 )
	{
		//$('input[name=\'selprod_user_shop_name\']').val('');
		$('input[name=\'selprod_user_shop_name\']').autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: fcom.makeUrl('sellerProducts', 'autoCompleteUserShopName'),
					data: {keyword: request, fIsAjax:1},
					dataType: 'json',
					type: 'post',
					success: function(json) {
						response($.map(json, function(item) {
							return { label: item['user_name'] +' - '+item['shop_identifier'],	value: item['user_id']	};
						}));
					},
				});
			},
			'select': function(item) {
				$("input[name='product_seller_id']").val( item['value'] );
				$("input[name='selprod_user_shop_name']").val( item['label'] );
			}
		});
	}else{
		$('input[name=\'selprod_user_shop_name\']').addClass('readonly-field');
		$('input[name=\'selprod_user_shop_name\']').attr('readonly', true);
	}

	$('input[name=\'selprod_user_shop_name\']').change(function(){
		if($(this).val()==''){
			$("input[name='product_seller_id']").val(0);
		}
	});
});
</script>
