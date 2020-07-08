<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmCategoryReq->setFormTagAttribute('class', 'web_form form_horizontal');
$frmCategoryReq->setFormTagAttribute('onsubmit', 'setupCategoryReq(this); return(false);');
$frmCategoryReq->setValidatorJsObjectName('brandRequestFormValidator');

$fld = $frmCategoryReq->getField('status');
$fld->setFieldTagAttribute('onChange','showHideCommentBox(this.value)');

$fldBl = $frmCategoryReq->getField('comments');
$fldBl->htmlBeforeField = '<span id="div_comments_box" class="hide">Reason for Cancellation';
$fldBl->htmlAfterField = '</span>';
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Category_Request_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addCategoryReqForm(<?php echo $categoryReqId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive=($categoryReqId==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($categoryReqId>0){?> onclick="addCategoryReqLangForm(<?php echo $categoryReqId ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frmCategoryReq->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
