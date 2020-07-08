<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">
	<div class="section">
	<div class="container">
         <div class="row justify-content-center">
               <div class="col-lg-6">
                       <div class="message message--success cms">
                           <i class="fa fa-check-circle"></i>
							<div class="section-head  section--head--center">
		 <div class="section__heading"><h2><?php echo Labels::getLabel('LBL_Congratulations',$siteLangId);?></h2></div>
		</div>


						   <?php if(!CommonHelper::isAppUser()){ ?>
                           <p><?php echo CommonHelper::renderHtml($textMessage);?></p>
						   <?php }?>
                           <span class="gap"></span>
                       </div>

               </div>

         </div>
    </div></div>
	<div class="gap"></div>
</div>
<script>
/*window.setTimeout(function() {
    window.location.href = fcom.makeUrl('Home');
}, 15000);*/
</script>
