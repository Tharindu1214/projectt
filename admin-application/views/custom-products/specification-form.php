<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
<div class="sectionhead">   
    <h4><?php echo Labels::getLabel('LBL_Custom_Catalog_Request',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a <?php echo ($preqId) ? "onClick='productForm( ".$preqId.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<li><a <?php echo ($preqId) ? "onClick='sellerProductForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info',$adminLangId); ?></a></li>
			<li><a class="active" <?php echo ($preqId) ? "onclick='customCatalogSpecifications( ".$preqId." );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId );?></a></li>
			<?php foreach($languages as $langId=>$langName){?>
			<li class="<?php echo (!$preqId) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php echo ($preqId) ? "onClick='productLangForm( ".$preqId.",".$langId." );'" : ""; ?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
			<?php } ?>
			<?php if(count($productOptions)>0) { ?>
			<li><a <?php echo ($preqId) ? "onClick='customEanUpcForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup',$adminLangId); ?></a></li>
			<?php } ?>
			<li><a <?php echo ($preqId) ? "onClick='updateStatusForm( ".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php  $specCount= count($productSpecifications['prod_spec_name'][CommonHelper::getLangId()]); ?>
				<form name="frmProductSpec" method="post" id="frm_fat_id_frmProductSpec" class="web_form form_horizontal" onsubmit="setupCustomCatalogSpecification(this,<?php echo $preqId; ?>); return(false);">	
				<?php 
				$totalSpec =0;
				$count =0;
				if($specCount>0){
				foreach($productSpecifications['prod_spec_name'][CommonHelper::getLangId()] as $specKey=>$specval){
					$totalSpec = $specKey; ?>
					<div class="form__cover  nopadding--bottom specification" id="specification<?php echo $specKey; ?>">
						<?php if( key($productSpecifications['prod_spec_name'][CommonHelper::getLangId()]) != $specKey ) { ?>
							<div class="divider"></div>
							<div class="gap"></div>
						<?php }?>
						<?php foreach($languages as $langId=>$langName){ ?>
						<div class="row">
							<div class="col-lg-1 col-md-1 col-sm-4 col-xs-12">
								<div class="row"> 
									<div class="col-md-12">
									   <div class="field-set">
										 <div class="caption-wraper">
										   <div class="h3"><strong><?php  echo $langName;?></strong></div>
										 </div>
									   </div>
									</div> 
								 </div>
							</div>
							<div class="col-lg-5 col-md-5 col-sm-4 col-xs-12">
							   <div class="field-set">
								 <div class="field-wraper">
								   <div class="field_cover">
									<input class="psec-name-js <?php echo 'layout--'.Language::getLayoutDirection($langId); ?>" title="<?php echo Labels::getLabel('LBL_Specification_Name',$adminLangId)?>" value="<?php echo $productSpecifications['prod_spec_name'][$langId][$specKey];?>" placeholder="<?php echo Labels::getLabel('LBL_Specification_Name',$adminLangId)?>" type="text" name="prod_spec_name[<?php echo $langId ?>][<?php echo $specKey;?>]">
								   </div>
								 </div>
								</div>
							</div>
							<div class="col-lg-5 col-md-5 col-sm-4 col-xs-12">
							   <div class="field-set">
								 <div class="field-wraper">
								   <div class="field_cover">
									<input class="<?php echo 'layout--'.Language::getLayoutDirection($langId); ?>" title="<?php echo Labels::getLabel('LBL_Specification_Value',$adminLangId)?>" type="text" value="<?php echo $productSpecifications['prod_spec_value'][$langId][$specKey];?>" placeholder="<?php echo Labels::getLabel('LBL_Specification_Value',$adminLangId)?>" name="prod_spec_value[<?php echo $langId ?>][<?php echo $specKey;?>]">
								   </div>
								 </div>
							   </div>
							</div>
							<?php if( $langId == key( array_slice( $languages, -1, 1, TRUE ) )){ ?>
							<div class="col-lg-1 col-md-1 col-sm-4 col-xm-12 align--right">
								<?php if($count != 0) { ?>
								<button type="button" onclick="removeSpecDiv(<?php echo $specKey ?>);" class="btn btn--secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Remove',$adminLangId)?>"><i class="ion-minus-round"></i></button>
								<?php } ?>
							</div>
							<?php } ?>
						</div>
						<?php  }  ?>
					</div>
					<?php $count++; }  } else{ ?>
					<div class="form__cover nopadding--bottom specification" id="specification0">
					<?php foreach($languages as $langId=>$langName){ ?>
						<div class="row">
							<div class="col-lg-1 col-md-1 col-sm-4 col-xs-12">
								<div class="row"> 
									<div class="col-md-12">
									   <div class="field-set">
										 <div class="caption-wraper">
											<div class="h3"><strong><?php  echo $langName;?></strong></div>
										 </div>
									   </div>
									</div> 
								 </div>
							</div>
							<div class="col-lg-5 col-md-5 col-sm-4 col-xs-12">
							   <div class="field-set">
								 <div class="field-wraper">
								   <div class="field_cover">
									<input class="<?php echo 'layout--'.Language::getLayoutDirection($langId); ?> psec-name-js" title="<?php echo Labels::getLabel('LBL_Specification_Name',$adminLangId)?>" placeholder="<?php echo Labels::getLabel('LBL_Specification_Name',$adminLangId)?>" type="text" name="prod_spec_name[<?php echo $langId ?>][0]" value="">
								   </div>
								 </div>
								 </div>
							</div>
							<div class="col-lg-5 col-md-5 col-sm-4 col-xs-12">
								<div class="field-set">
									<div class="field-wraper">
									   <div class="field_cover">
										<input class="<?php echo 'layout--'.Language::getLayoutDirection($langId); ?>" title="<?php echo Labels::getLabel('LBL_Specification_Value',$adminLangId)?>" placeholder="<?php echo Labels::getLabel('LBL_Specification_Value',$adminLangId)?>" type="text" name="prod_spec_value[<?php echo $langId ?>][0]" value="">
									   </div>
									</div>
								</div>
							</div>
						</div>
						<?php  } ?>
					</div>
					<?php } ?>
					<div id="addSpecFields"></div>
					
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-4 col-xm-12">
							<button type="button" class="btn btn--secondary ripplelink right" title="<?php echo Labels::getLabel('LBL_Shipping',$adminLangId)?>" onclick="getCustomCatalogSpecificationForm();"><i class="ion-plus-round"></i></button>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-4 col-xm-12">
							<div class="field-set">
								<div class="caption-wraper">
									<label class="field_label"></label>
								</div>
								<div class="field-wraper">
									<div class="field_cover">
										<input title="" type="submit" name="btn_submit" value="<?php echo Labels::getLabel('LBL_Save_Changes',$adminLangId)?>">
									</div>
								</div>
							</div>
						</div>
					</div>
					<input title="" type="hidden" name="product_id" value="<?php echo $preqId; ?>">
					<input title="" type="hidden" name="prodspec_id" value="0">
				</form>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>

<script>
var buttonClick = <?php echo $totalSpec; ?>;
</script>