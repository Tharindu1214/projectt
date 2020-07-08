<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Language_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		
			<div class="col-sm-12">
				<div class="tabs_nav_container responsive flat">
					<ul class="tabs_nav">
						<li><a href="javascript:void(0)" onclick="editLanguageForm(<?php echo $language_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
						<?php $inactive=($language_id==0)?'fat-inactive':''; ?>
						<li  class="<?php echo $inactive;?>"><a class="active" href="javascript:void(0)" <?php if($language_id>0){?> onclick="mediaForm(<?php echo $language_id ?>);" <?php }?> ><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
					</ul>
					<div class="tabs_panel_wrap">
						<div class="tabs_panel">
							<div id="imageupload_div" class="padd15">
							  <?php if( !empty($flags) ){ ?>
								  <ul class="grids--onefifth  ">
									<?php foreach( $flags as $flagKey => $flagName ){ ?>
									<li>
										<a href="javascript:void(0)" onclick="setImage('<?php echo $flagName;?>',<?php echo $language_id;?>)">
										  <div class="flagWrap <?php echo ( $selectedFlag == $flagName ) ? 'is--active':'';?> " >
											<div class="flagthumb"> <img  src="<?php echo CONF_WEBROOT_FRONT_URL;?>images/flags/<?php echo $flagName;?>" title="<?php echo $flagName;?>" alt="<?php echo $flagName;?>">
											<span><?php echo $flagName;?></span>
											</div>
										  </div>
										</a>
									</li>
									<?php } ?>
								  </ul>
							  <?php }	?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
