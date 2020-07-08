<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
  <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 ">
            <div class="message message--success align--center cms"> 
			<i class="fa fa-check-circle"></i>
			<div class="section-head  section--head--center">
		 <div class="section__heading"><h2><?php echo Labels::getLabel('MSG_Congratulations',$siteLangId);?></h2></div>
		</div>
             
              <!--<h3><?php // echo Labels::getLabel('LBL_Registration_Successful',$siteLangId);?> </h3>-->
              <p><?php echo $registrationMsg; ?> </p>
            </div>
        </div>
    </div>
  </div>
</section>
