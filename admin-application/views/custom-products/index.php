<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
	$frmSearch->setFormTagAttribute ( 'class', 'web_form last_td_nowrap' );
	$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchListing(this); return(false);' );
	$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
	$frmSearch->developerTags['fld_default_col'] = 4;
?>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Manage_Custom_Catalog_Requests',$adminLangId); ?> </h5>
							<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
					</div>
				</div>
				<section class="section searchform_filter">
					<div class="sectionhead">
						<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
					</div>
					<div class="sectionbody space togglewrap" style="display:none;">
						<?php echo $frmSearch->getFormHtml(); ?>    
					</div>
				</section>
				<!--<div class="col-sm-12">-->
				<section class="section">						
					<div class="sectionbody">
						<div class="tablewrap" >
							<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
						</div> 
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
<script>
$("document").ready(function(){
	var PRODUCT_TYPE_PHYSICAL = <?php echo Product::PRODUCT_TYPE_PHYSICAL; ?>;
	var PRODUCT_TYPE_DIGITAL = <?php echo Product::PRODUCT_TYPE_DIGITAL; ?>;
});
</script>
