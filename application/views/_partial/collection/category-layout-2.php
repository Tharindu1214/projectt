<?php
	if( isset( $collections ) && count($collections) ){
		foreach( $collections as $collection_id => $row ){
			/* category listing design [ */
			if( isset($row['categories']) && count( $row['categories'] ) ) { ?>
					<section class="section section--gray">
						<div class="container">
							<div class="section-head">
								<?php echo ($row['collection_name'] != '') ? ' <div class="section__heading"><h2>' . $row['collection_name'] .'</h2></div>' : ''; ?>

								<?php if( $row['totCategories'] > Collections::LIMIT_CATEGORY_LAYOUT2 ){ ?>
									<div class="section__action"> <a href="<?php echo CommonHelper::generateUrl('Collections','View',array($row['collection_id']));?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </div>
								<?php }  ?>
							</div>
							<div class="row">
							<?php foreach($row['categories'] as $category) { ?>
								<div class="col-xl-3 col-lg-6 col-sm-6 column">
									<div class="top-categories">
										<div class="cat-img"><img data-ratio="4:1" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('Category', 'banner', array($category['prodcat_id'] , $siteLangId, 'MEDIUM', applicationConstants::SCREEN_DESKTOP)), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt= "<?php echo $category['prodcat_name']; ?> " title= "<?php echo $category['prodcat_name']; ?> "></div>
										<div class="cat-tittle"> <a href="<?php echo CommonHelper::generateUrl('Category', 'View', array($category['prodcat_id'] )); ?>"> <?php echo $category['prodcat_name']; ?></a></div>
										<div class="cat-list">
											<ul>
												<?php $i=1; foreach($category['subCategories'] as $subCat) { ?>
												<li> <a href="<?php echo CommonHelper::generateUrl('Category', 'View', array($subCat['prodcat_id'] )); ?>"><?php echo $subCat['prodcat_name']; ?></a> </li>
												<?php $i++; if($i > 5) break; } ?>
												<?php /* if($i > 5) { ?>
												<li class="last-link"> <a href="<?php echo CommonHelper::generateUrl('Category'); ?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </li>
												<?php } */ ?>
											</ul>
										</div>
									</div>
								</div>
							<?php }?>
							</div>
						</div>
					</section>
			<?php }
		}
	}	/* ] */
?>
