<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">

	<div class="bg--second pt-3 pb-3">
      <div class="container container--fixed">
        <div class="row align-items-center">
          <div class="col-md-8">
              <div class="section-head section--white--head mb-0">
            <div class="section__heading">
                  <h2 class="mb-0">FAQ</h2>
                <div class="breadcrumbs breadcrumbs--white">
				  <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
				</div>
            </div>
        </div>                
          </div>
          <div class="col-md-4 align--right"></div>
        </div>
      </div>
    </div>


	<section class="section bg--white">
	  <div class="container">
		<div class="row">
		  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div id="listing"></div>
		  </div>
		  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<h3><?php echo Labels::getLabel( 'LBL_Few_More_Questions', $siteLangId)?></h3>
			<div id="categoryPanel"></div>
		  </div>
		</div>
	
	
	    <div class="gap"></div>
		<div class="divider"></div>
		<div class="text-center  pt-5">
		  <h3><?php echo Labels::getLabel( 'LBL_Still_need_help', $siteLangId)?> ?</h3>
		  <a href="<?php echo CommonHelper::generateUrl('custom' , 'contact-us'); ?>" class="btn btn--secondary" ><?php echo Labels::getLabel( 'LBL_Contact_Customer_Care', $siteLangId)?> </a>
		</div>

	  </div>
	</section>

</div>
<script>
var faqcatId = '<?php echo $faqCatId ?>';
var faqId = '<?php echo $faqId ?>';
</script>
