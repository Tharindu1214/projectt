<div class="heading3"><?php echo Labels::getLabel('LBL_Advertise_With_Us',$siteLangId);?></div>
<div class="registeration-process">
	<ul>
	  <li><a href="#"><?php echo Labels::getLabel('LBL_Details',$siteLangId);?></a></li>
	  <li><a href="#"><?php echo Labels::getLabel('LBL_Company_Details',$siteLangId);?></a></li>
	  <li class="is--active"><a href="#"><?php echo Labels::getLabel('LBL_Confirmation',$siteLangId);?></a></li>
	</ul>
</div>
<div class="message message--success align--center cms"> 
<i class="fa fa-check-circle"></i>
	<div class="section-head  section--head--center">
		 <div class="section__heading"><h2><?php echo Labels::getLabel('MSG_Congratulations',$siteLangId);?>!</h2></div>
		</div>
	 
	<p><?php echo $success_message; ?></p>
	<a href="<?php echo CommonHelper::generateUrl('guest-user','login-form'); ?>" class="btn btn--primary"><?php echo Labels::getLabel('Lbl_Login',$siteLangId);?></a>
</div>