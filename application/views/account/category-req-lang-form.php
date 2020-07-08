<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$categoryReqLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
$categoryReqLangFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$categoryReqLangFrm->developerTags['fld_default_col'] = 12;
$categoryReqLangFrm->setFormTagAttribute('onsubmit', 'setupCategoryReqLang(this); return(false);');
$categoryFld = $categoryReqLangFrm->getField('scategoryreq_name');
$categoryFld->setFieldTagAttribute('onblur','checkUniqueCategoryName(this,$("input[name=lang_id]").val(),'.$categoryReqId.')');

?>
<div class="box__head">
	<h4><?php echo Labels::getLabel('LBL_Request_New_Category',$siteLangId); ?></h4>
</div>

<div class="box__body">		
	<div class="tabs tabs--small clearfix">
		<ul>
			<li><a href="javascript:void(0)" onclick="addCategoryReqForm(<?php echo $categoryReqId ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
			<?php 
			$inactive=($categoryReqId==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){
				?>
				<li class="<?php echo $inactive;?> <?php echo ($langId == $categoryReqLangId) ? 'is-active' : ''; ?>"><a href="javascript:void(0);" <?php if($categoryReqId>0){?> onclick="addCategoryReqLangForm(<?php echo $categoryReqId ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php } ?>
		</ul>
	</div>
	<div class="tabs tabs--small tabs tabs--scroll clearfix">
		<?php
		echo $categoryReqLangFrm->getFormHtml();
		?>
	</div>
</div>