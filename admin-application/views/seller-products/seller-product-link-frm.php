<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Manage_Product_Links', $adminLangId); ?>
		</h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
				<div class="border-box border-box--space">
					<div class="tabs_nav_container responsive">
						<div class="tabs_panel_wrap">
							<div class="tabs_panel">
								<?php
                $sellerproductLinkFrm->setFormTagAttribute('onsubmit', 'setUpSellerProductLinks(this); return(false);');
                $sellerproductLinkFrm->setFormTagAttribute('class', 'web_form form--horizontal');
                $sellerproductLinkFrm->developerTags['colClassPrefix'] = 'col-md-';
                $sellerproductLinkFrm->developerTags['fld_default_col'] = 12;

                echo $sellerproductLinkFrm->getFormHtml(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
	$("document").ready(function() {
		var selProdId = <?php echo $selprod_id; ?> ;
		$('input[name=\'products_buy_together\']').autocomplete({
			'source': function(request, response) {
				/* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
					response($.map(json, function(item) {
							return { label: item['name'],	value: item['id']	};
						}));
				}); */

				$.ajax({
					url: fcom.makeUrl('sellerProducts', 'autoCompleteProducts'),
					data: {
						keyword: request,
						fIsAjax: 1,
						selProdId: selProdId
					},
					dataType: 'json',
					type: 'post',
					success: function(json) {
						response($.map(json, function(item) {
							return {
								label: item['name'] + '[' + item[
									'product_identifier'] + ']',
								value: item['id']
							};
						}));
					},
				});
			},
			'select': function(item) {
				$('input[name=\'products_buy_together\']').val('');
				$('#productBuyTogether' + item['value']).remove();
				$('#buy-together-products ul').append('<li id="productBuyTogether' + item['value'] +
					'"><i class="remove_buyTogether remove_param ion-android-delete icon"></i> ' +
					item['label'] + '<input type="hidden" name="product_upsell[]" value="' + item[
						'value'] + '" /></li>');
			}
		});
		$('#buy-together-products').on('click', '.remove_buyTogether', function() {
			/* $('#buy-together-products').delegate('.remove_buyTogether', 'click', function() { */
			$(this).parent().remove();
		});
		$('input[name=\'products_related\']').autocomplete({
			'source': function(request, response) {
				/* fcom.ajax(fcom.makeUrl('brands', 'autoComplete'), {keyword:encodeURIComponent(request)}, function(json) {
					response($.map(json, function(item) {
							return { label: item['name'],	value: item['id']	};
						}));
				}); */
				$.ajax({
					url: fcom.makeUrl('sellerProducts', 'autoCompleteProducts'),
					data: {
						keyword: request,
						fIsAjax: 1,
						selProdId: selProdId
					},
					dataType: 'json',
					type: 'post',
					success: function(json) {
						response($.map(json, function(item) {
							return {
								label: item['name'] + '[' + item[
									'product_identifier'] + ']',
								value: item['id']
							};
						}));
					},
				});
			},
			'select': function(item) {
				$('input[name=\'products_related\']').val('');
				$('#productRelated' + item['value']).remove();
				$('#related-products ul').append('<li id="productRelated' + item['value'] +
					'"><i class="remove_related remove_param ion-android-delete icon"></i> ' +
					item['label'] + '<input type="hidden" name="product_related[]" value="' + item[
						'value'] + '" /></li>');
			}
		});
		$('#related-products').on('click', '.remove_related', function() {
			/* $('#related-products').delegate('.remove_related', 'click', function() { */
			$(this).parent().remove();
		});
		<?php foreach ($upsellProducts as $key => $val) {
                    ?>

		$('#buy-together-products ul').append(
			"<li id=\"productBuyTogether<?php echo $val['selprod_id']; ?>\"><i class=\"remove_buyTogether remove_param ion-android-delete icon\"></i> <?php echo $val['product_name']; ?>[<?php echo $val['product_identifier']; ?>]<input type=\"hidden\" name=\"product_upsell[]\" value=\"<?php echo $val['selprod_id']; ?>\" /></li>"
			);
		<?php
                }
    foreach ($relatedProducts as $key => $val) {
        ?>

		$('#related-products ul').append(
			"<li id=\"productRelated<?php echo $val['selprod_id']; ?>\"><i class=\"remove_related remove_param ion-android-delete icon\"></i> <?php echo $val['product_name']; ?>[<?php echo $val['product_identifier']; ?>]<input type=\"hidden\" name=\"product_related[]\" value=\"<?php echo $val['selprod_id']; ?>\" /></li>"
			);
		<?php
    } ?>
	});
</script>