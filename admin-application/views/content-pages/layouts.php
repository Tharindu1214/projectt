<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Content_Pages_Layouts_Instructions',$adminLangId);?></h1>
	</div>
	</div>
	<div class="row">
	<div class="col-sm-12">
		 		
			<section class="section">
				<div class="sectionbody padd15">
					<div class="row">
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<div class="shop-template"> 
							<a rel="facebox" onClick="displayImageInFacebox('<?php echo CommonHelper::generateFullUrl(); ?><?php echo CONF_WEBROOT_FRONT_URL; ?>images/cms_layouts/layout-1.jpg');" href="javascript:void(0)">
								<figure class="thumb--square"><img width="400px;" src="<?php echo CONF_WEBROOT_URL; ?>images/cms_layouts/layout-1.jpg" /></figure>
								<p><?php echo Labels::getLabel('LBL_Layout_1',$adminLangId);?></p>
							</a>
						</div>
					</div>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<div class="shop-template padding20">
							<a rel="facebox" onClick="displayImageInFacebox('<?php echo CommonHelper::generateFullUrl(); ?><?php echo CONF_WEBROOT_FRONT_URL; ?>images/cms_layouts/layout-2.jpg');" href="javascript:void(0)">
								<figure class="thumb--square"><img width="400px;" src="<?php echo CONF_WEBROOT_URL; ?>images/cms_layouts/layout-2.jpg" /></figure>
								<p><?php echo Labels::getLabel('LBL_Layout_2',$adminLangId);?></p>
							</a>
						</div>
					</div>
					</div>
				</div>
			</section>
		 
	</div></div>
</div>
