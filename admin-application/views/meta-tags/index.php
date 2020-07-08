<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Meta_Tags_Setup',$adminLangId); ?> </h5>
							<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
					</div>
				</div>

		<div class="tabs_nav_container vertical">
			 <ul class="tabs_nav">
				<?php $itr = 0; foreach($tabsArr as $metaType => $metaDetail){
					?>
					<li><a class="<?php echo ($activeTab==$itr)?'active':''?>" href="javascript:void(0)" onClick="listMetaTags(<?php echo "'$metaType'";?>)"><?php echo $metaDetail['name'];?></a></li>
				<?php $itr++; }?>			
			</ul>
			<div class="tabs_panel_wrap">
				<div class="tabs_nav_container" id="frmBlock"> 
				</div>
			</div>
		</div>
	</div>		
</div>
</div>
</div>