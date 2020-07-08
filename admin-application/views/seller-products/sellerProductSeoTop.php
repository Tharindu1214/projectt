<div class="container container-fluid container--fluid">
	<div class="tabs--inline clearfix">
		<ul class="tabs_nav tabs_nav--internal">
			<li><a class="<?php echo ($seoActiveTab == 'GENERAL')?'active':''?>" href="javascript:void(0)" onclick="getProductSeoGeneralForm(<?php echo "$selprod_id" ?>);"><?php echo Labels::getLabel('LBL_Basic',$adminLangId);?></a></li>
			<?php 
			$inactive=($metaId==0)?'fat-inactive':'';
			foreach($languages as $langId=>$langName){?>
			<li><a class="<?php echo ($langId == $selprod_lang_id) ? 'active' : ''; ?>" href="javascript:void(0);" <?php if($metaId>0){?> onclick="editProductMetaTagLangForm(<?php echo "$metaId,$langId,'$metaType'" ?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php } ?>
		</ul>
	</div>
</div>
	