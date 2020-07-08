<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">
    <div class="bg--second pt-3 pb-3">
        <div class="container ">
            <div class="row align-items-center justify-content-between">
                <div class="col-md-8 col-sm-8">
                    <div class="prod-info">
                        <div class="prod-info__left">
                            <div class="product-avtar"><img alt="<?php echo $product['product_name']; ?>"
                                    src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'],'SMALL',$product['selprod_id'],0,$siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                            </div>
                        </div>
                        <div class="prod-info__right">
                            <?php if ($product['selprod_title']) { ?>
                            <h5><?php echo $product['selprod_title']; ?> </h5>
                            <p><?php echo $product['product_name']; ?></p>
                            <?php } else { ?>
                            <h5><?php echo $product['product_name']; ?> </h5>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-auto col-sm-auto"><a href="<?php echo CommonHelper::generateUrl('Products', 'view', array($product['selprod_id'])); ?>"
                        class="btn btn--primary d-block"><?php echo Labels::getLabel('Lbl_Back_to_Product', $siteLangId); ?></a></div>


            </div>
        </div>
    </div>


    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div id="itemRatings">
                        <div class="section__head">
                            <h4><?php echo Labels::getLabel('Lbl_Reviews_of', $siteLangId).' '. (($product['selprod_title']) ? $product['selprod_title'] .' - '.$product['product_name'] : $product['product_name']);?></h4>
                            <?php echo $frmReviewSearch->getFormHtml(); ?>
                        </div>
                        <div class="section__body">
                            <?php $this->includeTemplate('_partial/product-reviews-list.php', array('reviews'=>$reviews,'siteLangId'=>$siteLangId,'product_id'=>$product['product_id'],'canSubmitFeedback' => $canSubmitFeedback), false); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

</div>
