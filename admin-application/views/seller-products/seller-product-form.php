<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Product_Setup', $adminLangId); ?>
		</h4>
	</div>
	<div class="sectionbody space">
		<div class="tabs_nav_container  flat">
			<?php /* require_once 'sellerCatalogProductTop.php'; */?>

			<div class="tabs_panel_wrap">
				<ul class="tabs_nav tabs_nav--internal">
					<li><a class="active" href="javascript:void(0)"
							onClick="sellerProductForm(<?php echo $product_id;?>,<?php echo $selprod_id;?>)"><?php echo Labels::getLabel('LBL_Basic', $adminLangId); ?></a>
					</li>
					<?php $inactive = ($selprod_id==0)?'fat-inactive':'';
                        foreach ($language as $langId => $langName) {?>
					<li class="<?php echo $inactive ; ?>"><a
							href="javascript:void(0)" <?php if ($selprod_id>0) {?>
							onClick="sellerProductLangForm(<?php echo $selprod_id;?>,<?php echo $langId;?>)" <?php }?>>
							<?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?></a>
					</li>
					<?php } ?>
					<li class="<?php echo $inactive ; ?>"><a
							href="javascript:void(0)" <?php if ($selprod_id>0) {?>
							onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)"
							<?php }?>><?php echo Labels::getLabel('LBL_Link_Warranty_Policies', $adminLangId); ?></a>
					</li>
					<li class="<?php echo $inactive ; ?>"><a
							href="javascript:void(0)" <?php if ($selprod_id>0) {?>
							onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)"
							<?php }?>><?php echo Labels::getLabel('LBL_Link_Return_Policies', $adminLangId); ?></a>
					</li>
				</ul>
				<div class="tabs_panel_wrap">
					<?php
                    $frmSellerProduct->setFormTagAttribute('onsubmit', 'setUpSellerProduct(this); return(false);');
                    $frmSellerProduct->setFormTagAttribute('class', 'web_form form_horizontal');
                    $frmSellerProduct->developerTags['colClassPrefix'] = 'col-md-';
                    $frmSellerProduct->developerTags['fld_default_col'] = 12;
                    $selprod_threshold_stock_levelFld = $frmSellerProduct->getField('selprod_threshold_stock_level');
                    $selprod_threshold_stock_levelFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Alert_stock_level_hint_info', $adminLangId). '</small>';
                    $selprod_threshold_stock_levelFld->setWrapperAttribute('class', 'selprod_threshold_stock_level_fld');
                    $idFld= $frmSellerProduct->getField('selprod_id');
                    $idFld->setFieldTagAttribute('id', 'selprod_id');
                    $shopUserNameFld= $frmSellerProduct->getField('selprod_user_shop_name');
                    $shopUserNameFld->setfieldTagAttribute('readonly', 'readonly');
                    $urlFld= $frmSellerProduct->getField('selprod_url_keyword');
                    $urlFld->htmlAfterField = "<small class='text--small'>" . CommonHelper::generateFullUrl('Products', 'View', array($selprod_id), CONF_WEBROOT_FRONT_URL).'</small>';
                    $urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value,$selprod_id,'post')");
                    $selprodCodEnabledFld = $frmSellerProduct->getField('selprod_cod_enabled');
                    $selprodCodEnabledFld->setWrapperAttribute('class', 'selprod_cod_enabled_fld');
                    echo $frmSellerProduct->getFormHtml(); ?>

				</div>
			</div>
		</div>
	</div>
</section>
<script type="text/javascript">
	$("document").ready(function() {
		var addedByAdmin = <?php echo $product_added_by_admin; ?> ;
		var
			PRODUCT_TYPE_DIGITAL = <?php echo Product::PRODUCT_TYPE_DIGITAL; ?> ;
		var productType = <?php echo $product_type; ?> ;
		var shippedBySeller = <?php echo $shippedBySeller; ?> ;
		if (productType == PRODUCT_TYPE_DIGITAL || shippedBySeller == 0) {
			$(".selprod_cod_enabled_fld").hide();
		}
		/* if( addedByAdmin == 1 )
		{
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
					$("input[name='selprod_user_id']").val( item['value'] );
					$("input[name='selprod_user_shop_name']").val( item['label'] );
				}
			});
		} */
		var INVENTORY_TRACK = <?php echo Product::INVENTORY_TRACK; ?> ;
		var
			INVENTORY_NOT_TRACK = <?php echo Product::INVENTORY_NOT_TRACK; ?> ;

		$("select[name='selprod_track_inventory']").change(function() {
			if ($(this).val() == INVENTORY_TRACK) {
				$("input[name='selprod_threshold_stock_level']").removeAttr("disabled");
			}

			if ($(this).val() == INVENTORY_NOT_TRACK) {
				$("input[name='selprod_threshold_stock_level']").val(0);
				$("input[name='selprod_threshold_stock_level']").attr("disabled", "disabled");
			}
		});

		$("select[name='selprod_track_inventory']").trigger('change');
	});
</script>