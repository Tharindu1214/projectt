<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<ul class="breadcrumb flat">
			<li><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/home.png" alt=""> </a></li>
		    <li><a href="<?php echo Utilities::generateUrl('ppcpaymentmethods'); ?>">PPC Payment Methods</a></li>
		    <li>Payment Method Setup</li>
		</ul>
		<div class="container container-fluid">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Payment Method Settings - <?php echo $payment_settings["pmethod_name"]?></h4></div>
						
                        <div class="sectionbody">                            
                             <?php echo $frm->getFormHtml(); ?>                        
						</div>	
															
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>