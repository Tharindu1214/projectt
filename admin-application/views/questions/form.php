<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupQuestion(this); return(false);');
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Manage_Questions',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="questionForm(<?php echo $qbank_id ?> , <?php echo $question_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php 
			$inactive = ($question_id == 0)?'fat-inactive':'';
			foreach($languages as $langId=>$langName){?>
				<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($question_id>0){?> onclick="questionLangForm(<?php echo $question_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>