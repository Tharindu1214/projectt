<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionhead">  
		<h4><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
				<div class="tabs_nav_container  flat">
				
					<ul class="tabs_nav">
						<li><a  <?php echo ($preqId) ? "onclick='productForm( ".$preqId.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
						<li><a class="active" <?php echo ($preqId) ? "onclick='sellerProductForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info',$adminLangId); ?></a></li>
						<li><a  <?php echo ($preqId) ? "onclick='customCatalogSpecifications( ".$preqId." );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId );?></a></li>
						<?php 
							foreach($languages as $langId=>$langName){?>
								<li class="<?php echo (!$preqId) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php echo ($preqId) ? "onclick='productLangForm( ".$preqId.",".$langId." );'" : ""; ?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
							<?php }
						?>
						<?php if(!empty($productOptions) && count($productOptions)>0) { ?>
						<li><a <?php echo ($preqId) ? "onClick='customEanUpcForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup',$adminLangId); ?></a></li>
						<?php } ?>
						<li><a <?php echo ($preqId) ? "onclick='updateStatusForm( ".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status',$adminLangId); ?></a></li>
					</ul>
						<div class="tabs_panel_wrap">							
								<?php
								$frmSellerProduct->setFormTagAttribute('onsubmit','setupSellerProduct(this); return(false);');
								$frmSellerProduct->setFormTagAttribute('class','web_form form_horizontal');
								$frmSellerProduct->developerTags['colClassPrefix'] = 'col-md-';
								$frmSellerProduct->developerTags['fld_default_col'] = 12;
								$selprod_threshold_stock_levelFld = $frmSellerProduct->getField('selprod_threshold_stock_level');
								$selprod_threshold_stock_levelFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Alert_stock_level_hint_info', $adminLangId). '</small>';
								$selprod_threshold_stock_levelFld->setWrapperAttribute( 'class' , 'selprod_threshold_stock_level_fld');									
								$urlFld = $frmSellerProduct->getField('selprod_url_keyword');	
								$selprodCodEnabledFld = $frmSellerProduct->getField('selprod_cod_enabled');
								$selprodCodEnabledFld->setWrapperAttribute( 'class' , 'selprod_cod_enabled_fld');
								echo $frmSellerProduct->getFormHtml(); ?>
						</div>
				
			</div>
		</div>
	</section>
<script type="text/javascript">
$("document").ready(function(){	
	var PRODUCT_TYPE_DIGITAL = <?php echo Product::PRODUCT_TYPE_DIGITAL; ?>;
	var productType = <?php echo $productReqRow['product_type']; ?>;	
	if( productType == PRODUCT_TYPE_DIGITAL)
	{
		$(".selprod_cod_enabled_fld").hide();
	}
	
	var INVENTORY_TRACK = <?php echo Product::INVENTORY_TRACK; ?>;
	var INVENTORY_NOT_TRACK = <?php echo Product::INVENTORY_NOT_TRACK; ?>;
	
	$("select[name='selprod_track_inventory']").change(function(){
		if( $(this).val() == INVENTORY_TRACK ){
			$("input[name='selprod_threshold_stock_level']").removeAttr("disabled");
		}
		
		if( $(this).val() == INVENTORY_NOT_TRACK ){
			$("input[name='selprod_threshold_stock_level']").val(0);
			$("input[name='selprod_threshold_stock_level']").attr("disabled", "disabled");
		}
	});
	
	$("select[name='selprod_track_inventory']").trigger('change');
});
</script>
