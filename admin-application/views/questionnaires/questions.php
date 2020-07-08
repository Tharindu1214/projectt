<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Linked_Questions',$adminLangId); ?> (<?php echo $questionnaireData['questionnaire_name']; ?>)</h1>
		<?php echo  $frmSearch->getFormHtml(); ?>
	</div>
	<div class="col-sm-12">
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Questions_List',$adminLangId); ?> </h4>
			<?php if($canEdit){ ?>
			<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="linkQuestionsForm(<?php echo $questionnaireId ?>)";><?php echo Labels::getLabel('LBL_Link_Questions',$adminLangId); ?></a>
			<?php } ?>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="linkedQuestionsListing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>