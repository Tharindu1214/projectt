<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="page">
	<div class="container container-fluid">
		<div class="row">
		   <div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_My_Profile',$adminLangId);  ?></h5>
							<?php $this->includeTemplate('_partial/header/header-breadcrumb.php');  ?>
						</div>
					</div>

				</div>	<div class="row" id="profileInfoFrmBlock">
					<?php  echo Labels::getLabel('LBL_Loading..',$adminLangId); ?>
					</div>	
				<!--div class="section">
					<div class="sectionhead">
						<h4><?php  /* echo Labels::getLabel('LBL_My_Profile',$adminLangId); */ ?></h4>
					</div>
					<div class="containerwhite sectionbody space" id="profileInfoFrmBlock">
						<?php /*  echo Labels::getLabel('LBL_Loading..',$adminLangId); */  ?>
					</div>
				</div -->
			</div>
		</div>
	</div>
</div
