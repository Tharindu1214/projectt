	<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
	<div class="box__head">
       <h4><?php  echo $productDetails['product_name'];?></h4>

    </div>
	<?php
	$shippingFrm->setFormTagAttribute('class', 'form ');
	$shippingFrm->setFormTagAttribute('onsubmit', 'setupSellerShipping(this); return(false);');
	$countryFld = $shippingFrm->getField('shipping_country');

	$shippingFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
    $shippingFrm->developerTags['fld_default_col'] = 12;
	$countryFld = $shippingFrm->getField('shipping_country');
	$countryFld->setWrapperAttribute('class','col-md-6');
	$submitFld = $shippingFrm->getField('btn_submit');
	$cancelFld = $shippingFrm->getField('btn_cancel');
	$cancelFld->setFieldTagAttribute('onClick','searchCatalogProducts()');
	$submitFld->attachField($cancelFld);
	echo $shippingFrm->getFormHTML();
	?>
		<script >


			var productOptions =[];
			var dv =$("#listing");
		$(document).ready(function(){
			/* Shipping Information */
			$('input[name=\'shipping_country\']').autocomplete({
				'source': function(request, response) {

					$.ajax({
						url: fcom.makeUrl('seller', 'countries_autocomplete'),
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
						$('input[name=\'ps_from_country_id\']').val(item.value);
						$('input[name=\'shipping_country\']').val(item.label);
				}

			});

			$('input[name=\'shipping_country\']').keyup(function(){
				$('input[name=\'product_shipping_country\']').val('');
			})


			var productId=<?php echo $product_id;?>;

			addShippingTab(productId);

		});</script>
