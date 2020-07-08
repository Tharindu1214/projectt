<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="cards-header p-4">
	<h5 class="cards-title"><?php echo Labels::getLabel('LBL_Product_Setup',$siteLangId); ?></h5>
</div>
<div class="cards-content pt-3 pl-4 pr-4 ">
<div class="tabs tabs--small   tabs--scroll clearfix">
		<?php require_once('sellerCatalogProductTop.php');?>
	</div>
	<div class="tabs__content form">
		<div class="row">
			<?php echo Labels::getLabel('LBL_Loading..',$siteLangId); ?>
		</div>
	</div>
</div>
