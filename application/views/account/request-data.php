<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
	<div class="content-header row justify-content-between mb-3">
		<div class="col-md-auto">
			<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
			<h2 class="content-header-title"><?php echo Labels::getLabel('Lbl_Request_My_Data',$siteLangId);?></h2>
		</div>
	</div>
	<div class="content-body">
		<div class="cards">
			<div class="cards-header p-4">
				<h5 class="cards-title"><?php echo Labels::getLabel('Lbl_Request_My_Data',$siteLangId);?></h5>
			</div>
			<div class="cards-content pl-4 pr-4 ">
				<div class="row">
					<div class="col-md-8" id="requestDataFrmBlock">
						<?php echo Labels::getLabel('LBL_Loading..',$siteLangId); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
  </div>
</main>