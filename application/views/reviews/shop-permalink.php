<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$shop_city = $shop['shop_city'];
$shop_state = ( strlen($shop['shop_city']) > 0 ) ? ', '. $shop['shop_state_name'] : $shop['shop_state_name'];
$shop_country = ( strlen($shop_state) > 0 ) ? ', '.$shop['shop_country_name'] : $shop['shop_country_name'];
$shopLocation = $shop_city . $shop_state. $shop_country;
?> <div id="body" class="body">
    <div class="bg--second pt-3 pb-3">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-md-8">
                    <div class="section-head section--white--head mb-0">
                        <div class="section__heading">
                            <h2><?php echo $shop['shop_name']; ?></h2>
                            <p><?php echo $shopLocation; ?> <?php echo Labels::getLabel('LBL_Opened_on', $siteLangId); ?> <?php echo FatDate::format($shop['shop_created_on']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-auto col-sm-auto">
                    <a href="<?php echo CommonHelper::generateUrl('Shops', 'view', array($shop['shop_id'])); ?>" class="btn btn--primary d-block"><?php echo Labels::getLabel('Lbl_Back_to_Shop', $siteLangId); ?></a></div>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div id="itemRatings">
                        <div class="section__head">
                            <h4><?php echo Labels::getLabel('Lbl_Review_of', $siteLangId).' '. $shop['shop_name'] , ' ' ,Labels::getLabel('Lbl_by', $siteLangId),' : ',$reviewData['user_name'] ;?></h4>
                        </div>
                        <div class="rowx listing__all">
                            <ul class="reviews-list mt-5">
                                <li>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="profile-avatar">
                                                <div class="profile__dp">
                                                    <img src="<?php echo CommonHelper::generateUrl('Image', 'user', array($reviewData['spreview_postedby_user_id'],'thumb',true)); ?>" alt="<?php echo $reviewData['user_name']; ?>"></div>
                                                <div class="profile__bio">
                                                    <div class="title"><?php echo Labels::getLabel('Lbl_By', $siteLangId) ; ?> <?php echo CommonHelper::displayName($reviewData['user_name']); ?> <span
                                                            class="dated"><?php echo Labels::getLabel('Lbl_On_Date', $siteLangId) , ' ',FatDate::format($reviewData['spreview_posted_on']); ?></span></div>
                                                    <div class="yes-no">
                                                        <ul>
                                                            <li><a href="javascript:undefined;" onclick='markReviewHelpful(<?php echo FatUtility::int($reviewData['spreview_id']); ?>,1);return false;' class="yes <?php echo 'rev_'.FatUtility::int($reviewData['spreview_id']).'_1'; ?>"><img
                                                                        src="<?php echo CONF_WEBROOT_URL; ?>images/thumb-up.png" alt="<?php echo Labels::getLabel('LBL_Helpful', $siteLangId); ?>"> <span>(<?php echo $reviewHelpfulData['helpful']; ?>)</span></a>
                                                            </li>
                                                            <li><a href="javascript:undefined;" onclick='markReviewHelpful("<?php echo $reviewData['spreview_id']; ?>",0);return false;' class="no <?php echo 'rev_'.FatUtility::int($reviewData['spreview_id']).'_0'; ?>"><img
                                                                        src="<?php echo CONF_WEBROOT_URL; ?>images/thumb-down.png" alt="<?php echo Labels::getLabel('LBL_Not_Helpful', $siteLangId); ?>">
                                                                    <span>(<?php echo $reviewHelpfulData['notHelpful']; ?>)</span> </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="reviews-desc">
                                                <div class="products__rating"> <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                                        </svg></i> <span class="rate"><?php echo round($reviewData["shop_rating"], 1); ?></span> </div>
                                                <div class="cms">
                                                    <p><strong><?php echo $reviewData['spreview_title']; ?></strong></p>
                                                    <p> <?php echo nl2br($reviewData['spreview_description']); ?> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="gap"></div>
</div>
<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS', $siteLangId); ?>';
</script>
</div>
