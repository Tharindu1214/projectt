<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Manage_Attributes',$adminLangId); ?> ---- (<?php echo $attrgrp_row['attrgrp_name']; ?>)</h1>
	<div class="tabs_nav_container responsive flat">		
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				
				<ul class="grids--onefourth">
					<?php foreach($attributes as $attr) {?>
					<li>
						<div class="logoWrap">
							<div class="logothumb">
								<?php echo ($attr['attr_name']!='') ? $attr['attr_name'].'<br/>' : '';
								echo '<small>('.$attr['attr_identifier'].')</small>'; ?>
								<ul class="actions">
									<li><a href="javascript:void(0);" onClick="langForm(<?php echo $attr['attr_id'];?>,'<?php echo $admin_default_lang; ?>');" class=""><i class="ion-edit icon"></i></a></li>
								</ul>
								<?php //CommonHelper::printArray($attr); ?>
							</div>
						</div>
					</li>
					<?php } ?>
				</ul>
				
			</div>
		</div>
	</div>
</div>
