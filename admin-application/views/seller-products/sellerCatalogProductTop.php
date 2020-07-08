<?php $inactive = ($selprod_id==0)?'fat-inactive':'';	 ?>
<ul class="tabs_nav">
	<li>
		<a class="<?php echo ($activeTab == 'GENERAL')?'active':''?>"
			href="javascript:void(0)"
			onClick="sellerProductForm(<?php echo $product_id;?>,<?php echo $selprod_id;?>)">
			<?php echo Labels::getLabel('LBL_General', $adminLangId);?></a>
	</li>
	<li>
		<a class="<?php echo ($activeTab == 'SEO')?'active':''; echo $inactive;?>"
			href="javascript:void(0)" <?php if ($selprod_id>0) {?>onClick="getProductSeoGeneralForm(<?php echo $selprod_id;?>)"<?php }?>>
			<?php echo Labels::getLabel('LBL_Seo', $adminLangId);?></a>
	</li>
	<li>
		<a class="<?php echo ($activeTab == 'SPECIAL_PRICE')?'active':''; echo $inactive;?>"
			href="javascript:void(0)" <?php if ($selprod_id>0) {?>onClick="sellerProductSpecialPrices(<?php echo $selprod_id;?>)"<?php }?>>
			<?php echo Labels::getLabel('LBL_Special_Price', $adminLangId);?></a>
	</li>
	<li>
		<a class="<?php echo ($activeTab == 'VOLUME_DISCOUNT')?'active':''; echo $inactive;?>"
			href="javascript:void(0)" <?php if ($selprod_id>0) {?>onClick="sellerProductVolumeDiscounts(<?php echo $selprod_id;?>)"<?php }?>>
			<?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId);?></a>
	</li>
	<li>
		<a class="<?php echo ($activeTab == 'LINKS')?'active':''; echo $inactive;?>"
			href="javascript:void(0)" <?php if ($selprod_id>0) {?>onClick="sellerProductLinkFrm(<?php echo $selprod_id;?>)"<?php }?>>
			<?php echo Labels::getLabel('LBL_Links', $adminLangId);?></a>
	</li>
	<?php if ($product_type == Product::PRODUCT_TYPE_DIGITAL) {?>
	<li>
		<a class="<?php echo ($activeTab == 'DOWNLOADS')?'active':''; echo $inactive;?>"
			href="javascript:void(0)" <?php if ($selprod_id>0) {?>onClick="sellerProductDownloadFrm(<?php echo $selprod_id;?>)"<?php }?>>
			<?php echo Labels::getLabel('LBL_Downloads', $adminLangId);?></a>
	</li>
	<?php }?>
</ul>