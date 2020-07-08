<?php $inactive = ($product_id==0)?'fat-inactive':''; ?>
<ul>
	<li class="<?php echo ($activeTab == 'GENERAL')?'is-active':''?>">
	<a href="javascript:void(0)" onClick="customProductForm(<?php echo $product_id;?>,<?php echo $prodcat_id;?>)">
	<?php echo Labels::getLabel('LBL_General',$siteLangId);?></a></li>
	<li class="<?php echo ($activeTab == 'LINKS')?'is-active':''; echo $inactive;?>"><a href="javascript:void(0)" <?php if($product_id>0){?>onClick="customProductLinks(<?php echo $product_id;?>)"<?php }?>>
	<?php echo Labels::getLabel('LBL_Links',$siteLangId);?></a></li>
	<li class="<?php echo ($activeTab == 'OPTIONS')?'is-active':''; echo $inactive;?>">
	<a href="javascript:void(0)" <?php if($product_id>0){?>onClick="sellerCustomProductOptions(<?php echo $product_id;?>)"<?php }?>>
	<?php echo Labels::getLabel('LBL_Options',$siteLangId);?></a></li>
	<li class="<?php echo ($activeTab == 'SPECIFICATIONS')?'is-active':''; echo $inactive;?>">
	<a href="javascript:void(0)" <?php if($product_id>0){?>onClick="sellerCustomProductSpecifications(<?php echo $product_id;?>)"<?php }?>>
	<?php echo Labels::getLabel('LBL_Specifications',$siteLangId);?></a></li>
</ul>
