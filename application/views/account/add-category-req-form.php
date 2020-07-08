<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmCategoryReq->setFormTagAttribute('class', 'form form--horizontal');
$frmCategoryReq->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frmCategoryReq->developerTags['fld_default_col'] = 12;
$frmCategoryReq->setFormTagAttribute('onsubmit', 'setupCategoryReq(this); return(false);');
$identifierFld = $frmCategoryReq->getField(CategoryRequest::DB_TBL_PREFIX.'id');
$identifierFld->setFieldTagAttribute('id',CategoryRequest::DB_TBL_PREFIX.'id');
?>

<div class="box__head">
  <h4><?php echo Labels::getLabel('LBL_Request_New_category',$langId); ?></h4>
</div>
<div class="box__body">
  <div class="tabs tabs--small clearfix">
    <ul>
      <li class="is-active" ><a href="javascript:void(0)" onclick="addCategoryReqForm(<?php echo $categoryReqId; ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
      <?php 
			$inactive=($categoryReqId==0)?'fat-inactive':'';	
			foreach($languages as $langId=>$langName){?>
      <li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($categoryReqId>0){?> onclick="addCategoryReqLangForm(<?php echo $categoryReqId ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
      <?php } ?>
    </ul>
  </div>
 
    <?php
		echo $frmCategoryReq->getFormHtml();
	?>
  
</div>
