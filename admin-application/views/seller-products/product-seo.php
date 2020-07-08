<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
	<div class="sectionhead">   
		<h4><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
			<?php /*<div class="tabs_nav_container responsive flat">
					<?php require_once('sellerCatalogProductTop.php');?>
				</div>*/?>
				<div class="tabs_nav_container responsive">
					<ul class="tabs_nav tabs_nav--internal">
						<li><a href="javascript:void(0)" onClick="sellerProductForm(<?php echo $product_id;?>,<?php echo $selprod_id;?>)"><?php echo Labels::getLabel('LBL_Basic',$adminLangId); ?></a></li>
						<?php $inactive = ($selprod_id==0)?'fat-inactive':'';		
						foreach($language as $langId => $langName){?>	
						<li><a  class="<?php echo $inactive ; ?>" href="javascript:void(0)" <?php if($selprod_id>0){?> onClick="sellerProductLangForm(<?php echo $selprod_id;?>,<?php echo $langId;?>)" <?php }?>>
							<?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
							<?php }?>
							<li><a class="<?php echo ($ppoint_type == PolicyPoint::PPOINT_TYPE_WARRANTY)?'active':''; ?>" href="javascript:void(0)" onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)"><?php echo Labels::getLabel('LBL_Link_Warranty_Policies',$adminLangId); ?></a></li>
							<li><a class="<?php echo ($ppoint_type == PolicyPoint::PPOINT_TYPE_RETURN)?'active':''; ?>" href="javascript:void(0)" onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)"><?php echo Labels::getLabel('LBL_Link_Return_Policies',$adminLangId); ?></a></li>
						</ul>
						<div class="tabs_panel_wrap">
							<div class="tabs_panel">				
								<?php echo Labels::getLabel('LBL_Loading..',$adminLangId); ?>
							</div>
						</div>
					</div>	
				</div>
			</div>
		</div>
	</section>	