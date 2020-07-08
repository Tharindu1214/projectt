<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSearchCustomProduct->setFormTagAttribute( 'onsubmit', 'searchCustomProducts(this); return(false);' );
$frmSearchCustomProduct->setFormTagAttribute('class', 'form');
$frmSearchCustomProduct->developerTags['colClassPrefix'] = 'col-md-';
$frmSearchCustomProduct->developerTags['fld_default_col'] = 12;

$keyFld = $frmSearchCustomProduct->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->setWrapperAttribute('class','col-sm-6');
$keyFld->developerTags['col'] = 8;

$submitBtnFld = $frmSearchCustomProduct->getField('btn_submit');
$submitBtnFld->value=Labels::getLabel('LBL_Search',$siteLangId);
$submitBtnFld->setFieldTagAttribute('class','btn--block');
$submitBtnFld->setWrapperAttribute('class','col-sm-3');
$submitBtnFld->developerTags['col'] = 2;

$cancelBtnFld = $frmSearchCustomProduct->getField('btn_clear');
$cancelBtnFld->value=Labels::getLabel("LBL_Clear", $siteLangId);
$cancelBtnFld->setFieldTagAttribute('class','btn--block');
$cancelBtnFld->setWrapperAttribute('class','col-sm-3');
$cancelBtnFld->developerTags['col'] = 2;
?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
	<div class="content-wrapper content-space">
		<div class="content-header row justify-content-between mb-3">
			<div class="col-md-auto">
				<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
				<h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Product',$siteLangId); ?></h2>
			</div>
		</div>
		<div class="content-body">
			<div class="cards">
				<div class="cards-header p-4">
					<h5 class="cards-title"><?php echo Labels::getLabel('LBL_My_Products_list',$siteLangId); ?></h5>
					<div class="action">
						<div class="">
							<a href="javascript:void(0)" onclick="addCatalogPopup()" class = "btn btn--primary btn--sm"><?php echo Labels::getLabel( 'LBL_Add_New_Product', $siteLangId);?></a>
                            <a href="<?php echo CommonHelper::generateUrl('seller','catalog' );?>" class="btn btn--primary-border btn--sm"><?php echo Labels::getLabel('LBL_Products_List', $siteLangId);?></a>
						</div>
					</div>
				</div>
				<div class="cards-content pl-4 pr-4 ">
					<div class="replaced">
						<?php echo $frmSearchCustomProduct->getFormHtml(); ?>
					</div>
					<span class="gap"></span>
					<?php echo $frmSearchCustomProduct->getExternalJS();?>
					<div id="listing">
						<?php echo Labels::getLabel('LBL_Loading..',$siteLangId); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
