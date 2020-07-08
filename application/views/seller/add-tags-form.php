<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmTag->setFormTagAttribute('class', 'form form--horizontal');
$frmTag->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frmTag->developerTags['fld_default_col'] = 12;
$frmTag->setFormTagAttribute('onsubmit', 'setupTag(this); return(false);');
?>

<div class="box__head">
  <h4><?php echo Labels::getLabel('LBL_Add_Tags',$langId); ?></h4>
</div>
<div class="box__body">
  <div class="tabs tabs--small tabs--scroll clearfix">
    <ul>
      <li class="is-active"><a  href="javascript:void(0)" onclick="addTagForm(<?php echo $tag_id ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
      <?php
			$inactive=($tag_id==0)?'fat-inactive':'';
			foreach($languages as $langId=>$langName){?>
      <li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($tag_id>0){?> onclick="addTagLangForm(<?php echo $tag_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
      <?php } ?>
    </ul>
  </div>
  <div class="tabs__content form">
    <?php
		echo $frmTag->getFormHtml();
		?>
  </div>
</div>
