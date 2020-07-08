<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Manage_Theme_Color',$adminLangId); ?> </h5>
							<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
					</div>
				</div>


<!--<div class="row">
	<div class="col-sm-12"> -->
		<h1><?php //echo Labels::getLabel('LBL_Manage_Theme_Color',$adminLangId); ?> </h1>	
			<section class="section searchform_filter">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 
					$search->setFormTagAttribute ( 'onsubmit', 'searchThemeColor(this); return(false);');
					$search->setFormTagAttribute ( 'id', 'frmSearch' );
					$search->setFormTagAttribute ( 'class', 'web_form' );
					$search->developerTags['colClassPrefix'] = 'col-md-';					
					$search->developerTags['fld_default_col'] = 6;					
					
					$search->getField('keyword')->addFieldtagAttribute('class','search-input');
					$search->getField('btn_clear')->addFieldtagAttribute('onclick','clearSearch();');
					
					echo  $search->getFormHtml();
				?>    
			</div>
		</section> 		
	<!--</div>
	<div class="col-sm-12">--> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Theme_Color_Listing',$adminLangId); ?></h4>
		
		</div>
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