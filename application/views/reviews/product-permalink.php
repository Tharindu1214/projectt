<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">


	<div class="bg--second pt-3 pb-3">
		<div class="container">
			<div class="row align-items-center justify-content-between">
				<div class="col-md-8 col-sm-8">
					<div class="prod-info">
						<div class="prod-info__left">
							<div class="product-avtar"><img alt="<?php echo $product['product_name']; ?>" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','product',array($product['product_id'],'SMALL',$product['selprod_id'],0,$siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
							</div>
						</div>
						<div class="prod-info__right">

							<?php if($product['selprod_title']){ ?>
							<h5><?php echo $product['selprod_title']; ?> </h5>
							<p><?php echo $product['product_name']; ?></p>
							<?php } else { ?>
							<h5><?php echo $product['product_name']; ?> </h5>
							<?php } ?>

						</div>

					</div>

				</div>

				<div class="col-md-auto col-sm-auto"><a href="<?php echo CommonHelper::generateUrl('Reviews','product',array($product['selprod_id'])); ?>" class="btn btn--primary"><?php echo Labels::getLabel('Lbl_View_All_Reviews',$siteLangId); ?></a></div>


			</div>
		</div>
	</div>


	<section class="section">
		<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-7">
			<div id="itemRatings">
				<div class="section__head">
					<h4><?php echo Labels::getLabel('Lbl_Review_of',$siteLangId).' '. (($product['selprod_title']) ? $product['selprod_title'] .' - '.$product['product_name'] : $product['product_name']) , ' ' ,Labels::getLabel('Lbl_by',$siteLangId),' : ',$reviewData['user_name'] ;?></h4>
				</div>
				<div class=" listing__all">
					<ul class="reviews-list mt-5">
						<li>
							<div class="row">
								<div class="col-md-4">
									<div class="profile-avatar">
										<div class="profile__dp">
											<img src="<?php echo CommonHelper::generateUrl('Image', 'user', array($reviewData['spreview_postedby_user_id'],'thumb',true)); ?>" alt="<?php echo $reviewData['user_name']; ?>"></div>
										<div class="profile__bio">
											<div class="title"><?php echo Labels::getLabel('Lbl_By', $siteLangId) ; ?> <?php echo CommonHelper::displayName($reviewData['user_name']); ?> <span class="dated"><?php echo Labels::getLabel('Lbl_On_Date', $siteLangId) , ' ',FatDate::format($reviewData['spreview_posted_on']); ?></span></div>
											<div class="yes-no">
												<ul>
													<li><a href="javascript:undefined;" onclick='markReviewHelpful(<?php echo FatUtility::int($reviewData['spreview_id']); ?>,1);return false;' class="yes"><img src="<?php echo CONF_WEBROOT_URL; ?>images/thumb-up.png" alt="<?php echo Labels::getLabel('LBL_Helpful', $siteLangId); ?>"> (<?php echo $reviewHelpfulData['helpful']; ?>) </a></li>
													<li><a href="javascript:undefined;" onclick='markReviewHelpful("<?php echo $reviewData['spreview_id']; ?>",0);return false;' class="no"><img src="<?php echo CONF_WEBROOT_URL; ?>images/thumb-down.png" alt="<?php echo Labels::getLabel('LBL_Not_Helpful', $siteLangId); ?>"> (<?php echo $reviewHelpfulData['notHelpful']; ?>) </a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-8">
									<div class="reviews-desc">
										<div class="products__rating"> <i class="icn"><svg class="svg">
													<use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
												</svg></i> <span class="rate"><?php echo round($reviewData["prod_rating"], 1); ?></span> </div>
										<div class="cms">
											<p><strong><?php echo $reviewData['spreview_title']; ?></strong></p>
											<p>
												<?php echo nl2br($reviewData['spreview_description']); ?>
											</p>
										</div>
									</div>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div></div>
		</div>
		</div>
	</section>
	<div class="gap"></div>
</div>
<script>
	var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE',$siteLangId); ?>';
	var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS',$siteLangId); ?>';
</script>
