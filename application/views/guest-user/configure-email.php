<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php /* $this->includeTemplate('_partial/dashboardNavigation.php'); */ ?>
<div id="body" class="body" role="main">
   <section class="bg--second pt-3 pb-3">
       <div class="container">
           <div class="section-head section--white--head section--head--center mb-0">
               <div class="section__heading">
                   <h2 class="mb-0"><?php echo Labels::getLabel('Lbl_Change_Email', $siteLangId);?></h2>
                   <p>
                       <?php echo Labels::getLabel('LBL_CONFIGURE_YOUR_EMAIL', $siteLangId);?>
                   </p>
               </div>
           </div>
       </div>
   </section>
   <section class="section">
       <div class="container">
           <div class="row justify-content-center">
               <div class="col-md-4" id="changeEmailFrmBlock">
                   <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
               </div>
           </div>
       </div>
   </section>
</div>
