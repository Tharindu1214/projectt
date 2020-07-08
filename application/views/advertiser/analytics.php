<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/advertiser/advertiserDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
	<div class="content-header justify-content-between row mb-4">
		<div class="content-header-left col-md-auto">
			<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
			<h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Promotion_Analytics',$siteLangId);?></h2>
		</div>
	</div>
	<div class="content-body">
		<div class="row">
			<div class="col-md-12">
				<div class="cards">
					<div class="cards-header p-4">
						<h5 class="cards-title "><?php echo ucfirst($promotionDetails['promotion_name']);?></h5>
						<div class="btn-group">
							<a href="<?php echo CommonHelper::generateUrl('advertiser','promotions');?>" class="btn btn--primary btn--sm"><?php echo Labels::getLabel('LBL_My_promotions',$siteLangId);?></a>
						</div>
					</div>
					<div class="cards-content pl-4 pr-4 ">
						<div class="replaced">
						<?php 
							$searchForm->setFormTagAttribute('class', 'form');
							$searchForm->setFormTagAttribute('onsubmit', 'searchAnalytics(this); return false;');
							$searchForm->developerTags['colClassPrefix'] = 'col-md-';
							$searchForm->developerTags['fld_default_col'] = 4;
							$fldSubmit = $searchForm->getField('btn_submit');
							
                            $dateFromFld = $searchForm->getField('date_from');
                            $dateFromFld->setFieldTagAttribute('class', 'field--calender');
                            $dateFromFld->setWrapperAttribute('class', 'col-lg-2');
                            $dateFromFld->developerTags['col'] = 2;
                            $dateFromFld->developerTags['noCaptionTag'] = true;

                            $dateToFld = $searchForm->getField('date_to');
                            $dateToFld->setFieldTagAttribute('class', 'field--calender');
                            $dateToFld->setWrapperAttribute('class', 'col-lg-2');
                            $dateToFld->developerTags['col'] = 2;
                            $dateToFld->developerTags['noCaptionTag'] = true;
                            
							$submitBtnFld = $searchForm->getField('btn_submit');
							$submitBtnFld->setFieldTagAttribute('class','btn--block');
							$submitBtnFld->setWrapperAttribute('class','col-sm-6 ');
                            $submitBtnFld->developerTags['col'] = 2;
                            $submitBtnFld->developerTags['noCaptionTag'] = true;

							$cancelBtnFld = $searchForm->getField('btn_clear');
							$cancelBtnFld->setFieldTagAttribute('class','btn--block');
							$cancelBtnFld->setWrapperAttribute('class','col-sm-6 ');
							$cancelBtnFld->developerTags['col'] = 2;
                            $cancelBtnFld->developerTags['noCaptionTag'] = true;
                            
                            echo $searchForm->getFormHTML();?>
						</div>
						<div id="ppcListing"></div>
					</div>      
				</div>
			</div>
		</div>
    </div>
 </div>
</main>