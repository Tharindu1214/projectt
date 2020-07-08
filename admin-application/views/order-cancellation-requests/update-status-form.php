<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupStatus(this); return(false);');
$frm->developerTags['colClassPrefix']='col-md-';
$frm->developerTags['fld_default_col'] = 8;

$frm->getField('ocrequest_status')->setFieldTagAttribute('id','ocrequest_status');
$frm->getField('ocrequest_refund_in_wallet')->setFieldTagAttribute('id','ocrequest_refund_in_wallet');

$frm->getField('ocrequest_refund_in_wallet')->setWrapperAttribute('class','wrapper-ocrequest_refund_in_wallet hide');
$frm->getField('ocrequest_admin_comment')->setWrapperAttribute('class','wrapper-ocrequest_admin_comment hide');

?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Update_Status_Setup',$adminLangId); ?></h4>
    </div>
	<div class="sectionbody space">
		<?php if($orderRewardUsed){?>
		<h3><?php echo str_replace('{rewardpoint}',$orderRewardUsed,Labels::getLabel('MSG_{rewardpoint}_reward_point_used._which_will_not_credit_back_automatically',$adminLangId));?></h3>
		<?php }?>
		<div class="border-box border-box--space">
			<?php echo $frm->getFormHtml(); ?>
		</div>	
	</div>
</section>

<script language="javascript">
$(document).ready(function(){
	$('#ocrequest_refund_in_wallet').change(function(){
		if($(this).is(':checked')){
			$('.wrapper-ocrequest_admin_comment').removeClass('hide');
		} else{
			$('.wrapper-ocrequest_admin_comment').addClass('hide');
		}
	});
	
	$('#ocrequest_status').change(function(){
		if($(this).val() === '1'){
			$('.wrapper-ocrequest_refund_in_wallet').removeClass('hide');
			$('#ocrequest_refund_in_wallet').change();
		} else{
			$('.wrapper-ocrequest_refund_in_wallet').addClass('hide');
			$('.wrapper-ocrequest_admin_comment').addClass('hide');
		}
	});
	
});	
</script>