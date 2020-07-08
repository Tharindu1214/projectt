<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if ($reviewsList) { ?>
    <ul class="reviews-list mt-5">
    <?php foreach ($reviewsList as $review) { ?>
        <li>
        <div class="row">
            <div class="col-md-4">
                <div class="profile-avatar">
                    <div class="profile__dp">
                        <img src="<?php echo CommonHelper::generateUrl('Image', 'user', array($review['spreview_postedby_user_id'],'thumb',true)); ?>" alt="<?php echo $review['user_name']; ?>"></div>
                    <div class="profile__bio">
                        <div class="title"><?php echo Labels::getLabel('Lbl_By', $siteLangId) ; ?> <?php echo CommonHelper::displayName($review['user_name']); ?> <span
                                class="dated"><?php echo Labels::getLabel('Lbl_On_Date', $siteLangId) , ' ',FatDate::format($review['spreview_posted_on']); ?></span></div>
                        <div class="yes-no">
                            <ul>
                                <li><a href="javascript:undefined;" onclick='markReviewHelpful(<?php echo FatUtility::int($review['spreview_id']); ?>,1);return false;' class="yes"><img src="<?php echo CONF_WEBROOT_URL; ?>images/thumb-up.png"
                                            alt="<?php echo Labels::getLabel('LBL_Helpful', $siteLangId); ?>"> (<?php echo $review['helpful']; ?>) </a></li>
                                <li><a href="javascript:undefined;" onclick='markReviewHelpful("<?php echo $review['spreview_id']; ?>",0);return false;' class="no"><img src="<?php echo CONF_WEBROOT_URL; ?>images/thumb-down.png"
                                            alt="<?php echo Labels::getLabel('LBL_Not_Helpful', $siteLangId); ?>"> (<?php echo $review['notHelpful']; ?>) </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="reviews-desc">
                    <div class="products__rating"> <i class="icn"><svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                            </svg></i> <span class="rate"><?php echo round($review["prod_rating"], 1); ?></span> </div>
                    <div class="cms">
                        <p><strong><?php echo $review['spreview_title']; ?></strong></p>
                        <p>
                            <span class='lessText'>
                                <?php echo CommonHelper::truncateCharacters($review['spreview_description'], 200, '', '', true); ?>
                            </span>
                            <?php if (strlen($review['spreview_description']) > 200) { ?>
                                <span class='moreText hidden'>
                                    <?php echo nl2br($review['spreview_description']); ?>
                                </span>
                            <a class="readMore link--arrow" href="javascript:void(0);">
                                <?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId) ; ?> </a>
                            <?php } ?>
                        </p>
                        <!-- <a class="btn btn--secondary btn--primary-border mt-3" href="<?php echo CommonHelper::generateUrl('Reviews', 'productPermalink', array($review['spreview_selprod_id'] , $review['spreview_id'])) ?>"><?php echo Labels::getLabel('Lbl_Permalink', $siteLangId); ?> </a> -->
                    </div>
                </div>
            </div>
        </div>
    </li>
    <?php } ?>
</ul>
<div class="align--center  mt-4"><a href="<?php echo CommonHelper::generateUrl('Reviews', 'Product', array($selprod_id)); ?>"
        class="btn btn--secondary btn--primary-border"><?php echo Labels::getLabel('Lbl_Showing_All', $siteLangId).' '.count($reviewsList).' '.Labels::getLabel('Lbl_Reviews', $siteLangId) ; ?> </a></div> <?php echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchReviewsPaging')); ?>
<?php } else {
    // $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false);
}?>
