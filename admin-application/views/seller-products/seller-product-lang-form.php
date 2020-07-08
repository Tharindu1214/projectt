<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
				<div class="tabs_nav_container responsive flat">
					<?php /* require_once('sellerCatalogProductTop.php'); */?>
					<div class="tabs_panel_wrap ">
						<ul class="tabs_nav tabs_nav--internal">
							<li><a href="javascript:void(0)" onClick="sellerProductForm(<?php echo $product_id;?>,<?php echo $selprod_id;?>)"><?php echo Labels::getLabel('LBL_Basic',$adminLangId); ?></a></li>
							<?php	
							foreach($language as $langId => $langName){?>	
							<li><a class="<?php echo ($formLangId == $langId)?'active':'' ; ?>" href="javascript:void(0)" onClick="sellerProductLangForm(<?php echo $selprod_id;?>,<?php echo $langId;?>)">
								<?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
								<?php }?>
								<li><a href="javascript:void(0)" onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)"><?php echo Labels::getLabel('LBL_Link_Warranty_Policies',$adminLangId); ?></a></li>
								<li><a href="javascript:void(0)" onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)"><?php echo Labels::getLabel('LBL_Link_Return_Policies',$adminLangId); ?></a></li>
							</ul>
							<div class="tabs_panel_wrap">
									<?php
									$frmSellerProdLangFrm->setFormTagAttribute('onsubmit','setUpSellerProductLang(this); return(false);');
									$frmSellerProdLangFrm->setFormTagAttribute('class','web_form form_horizontal layout--'.$formLayout);
									$frmSellerProdLangFrm->developerTags['colClassPrefix'] = 'col-md-';
									$frmSellerProdLangFrm->developerTags['fld_default_col'] = 12;

									echo $frmSellerProdLangFrm->getFormHtml(); 
									?>
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>
		</section>