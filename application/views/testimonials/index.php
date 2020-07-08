<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">

	<div class="bg--second pt-3 pb-3">
      <div class="container">
        <div class="row align-items-center justify-content-center">
          <div class="col-md-12">               
               <div class="section-head section--white--head section--head--center mb-0">
            <div class="section__heading">
                  <h2><?php echo Labels::getLabel('Lbl_Testimonials',$siteLangId); ?></h2>
                <div class="breadcrumbs breadcrumbs--white breadcrumbs--center"> 
				   <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
				</div>
            </div>
        </div> 
        </div>
       
      </div>
    </div>
    </div>
	<section class="section section--gray">
	  <div class="container">
		<div class="cms">
			<div class="list__all" id='listing'></div>
			<div id="loadMoreBtnDiv"></div>
			<?php echo FatUtility::createHiddenFormFromData ( array('page'=>1), array ('name' => 'frmSearchTestimonialsPaging') ); ?>
		</div>	
	  </div>
	</section>
	 
</div>
