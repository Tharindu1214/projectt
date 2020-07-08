<div id="body" class="body">
	<div class="bg--second pt-3 pb-3">
		<div class="container">
			<div class="row align-items-center justify-content-between">
				<div class="col-md-8">
					<div class="section-head section--white--head mb-0">
						<div class="section__heading">
							<h2 class="mb-0"><?php echo Labels::getLabel('LBL_SITEMAP',$siteLangId);?></h2>
							<div class="breadcrumbs breadcrumbs--white">
								<?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4"></div>
			</div>
		</div>
	</div>
	<section class="section">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 ">
					
						<div class="sitemapcontainer">
							<?php  if(!empty($contentPages)){ ?>
							<h2>
								<?php echo Labels::getLabel('LBL_CONTENT_PAGES',$siteLangId);?>
							</h2>
							 
								<div class="site-map-list">
									<ul>
										<?php
                        foreach($contentPages as $contentId=> $contentPageName){
                        ?> <li>
											<a href="<?php echo CommonHelper::generateUrl('cms','view',array($contentId));?>">
												<?php echo $contentPageName;?>
											</a>
										</li>
										<?php }?>
									</ul>

								</div>
							 
							<?php
			}
			if($categoriesArr){
			?>

							<h2>
								<?php echo Labels::getLabel('LBL_Categories', $siteLangId);?>
							</h2>
						 
								<div class="site-map-list">
									<?php $this->includeTemplate('_partial/custom/categories-list.php',array('categoriesArr'=>$categoriesArr),false);?>

								</div>
							 

							<?php

			}
			if(!empty($allShops)){ ?>

							<h2>
								<?php echo Labels::getLabel('LBL_Shops',$siteLangId);?>
							</h2>
							 
								<div class="site-map-list">
									<ul>
										<?php foreach($allShops as $shop){
					?>
										<li>
											<a href="<?php echo CommonHelper::generateUrl('Shops','view',array($shop['shop_id']));?>">
												<?php echo $shop['shop_name'];?>
											</a>
										</li>
										<?php }?>
									</ul>
								</div>

							 
							<?php
				}
				
			if(!empty($allBrands)){ ?>

							<h2>
								<?php echo Labels::getLabel('LBL_Brands',$siteLangId);?>
							</h2>
							 
								<div class="site-map-list">
									<ul>
										<?php foreach($allBrands as $brands){
					?>
										<li>
											<a href="<?php echo CommonHelper::generateUrl('Brands','view',array($brands['brand_id']));?>">
												<?php echo $brands['brand_name'];?>
											</a>
										</li>
										<?php }?>
									</ul>
								</div>

							 
							<?php
				}
				?>
						</div>
					
				</div>
			</div>
		</div>


	</section>

</div>