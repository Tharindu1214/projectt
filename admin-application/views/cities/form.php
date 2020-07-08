<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupState(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$frm->getField('city_country_id')->setFieldTagAttribute('onChange','getStatesByCid(this.value,'.$city_state_id.',\'#user_form_state_id\')');
$frm->getField('city_state_id')->setFieldTagAttribute('id','user_form_state_id');

?>
<section class="section">
	<div class="sectionhead">
		
		<h4><?php echo Labels::getLabel('LBL_City_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">	

			<div class="col-sm-12">
				<h1><?php //echo Labels::getLabel('LBL_State_Setup',$adminLangId); ?></h1>
				<div class="tabs_nav_container responsive flat">
					<ul class="tabs_nav">
						<li><a class="active" href="javascript:void(0)" onclick="editStateForm(<?php echo $city_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
						<?php 
						$inactive=($city_id==0)?'fat-inactive':'';	
						foreach($languages as $langId=>$langName){?>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($city_id>0){?> onclick="editStateLangForm(<?php echo $city_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
						<?php } ?>
					</ul>
					<div class="tabs_panel_wrap">
						<div class="tabs_panel">
							<?php echo $frm->getFormHtml(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<script language="javascript">
$(document).ready(function(){
	getStatesByCid('<?php echo $city_country_id; ?>', '<?php echo $city_state_id; ?>' ,'#user_form_state_id');
});
</script>