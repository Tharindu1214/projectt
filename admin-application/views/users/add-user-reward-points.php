<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class','web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupUserRewardPoints(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;	
?>

<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_User_Reward_Points',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
	  <div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0)" onclick="addReward(<?php echo $userId ?>);"><?php echo Labels::getLabel('LBL_Reward_Points',$adminLangId); ?></a></li>
			<li><a class="active" href="javascript:void(0)" onclick="addUserRewardPoints(<?php echo $userId ?>);"><?php echo Labels::getLabel('LBL_Add_New',$adminLangId); ?></a></li>								
		</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php 					
						echo $frm->getFormHtml(); 
					?>
				</div>
			</div>						
		</div>
	</div>						
</section>