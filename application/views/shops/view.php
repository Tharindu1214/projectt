<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$bgUrl = CommonHelper::generateFullUrl('Image', 'shopBackgroundImage', array($shop['shop_id'], $siteLangId, 0, 0, $template_id)); ?>
<div id="body" class="body template-<?php echo $template_id;?>" role="main">
    <?php
        $shopData = array_merge($data, array( 'template_id'=>$template_id,'collectionData'=>$collectionData,'action'=>$action,'shopTotalReviews'=>$shopTotalReviews,'shopRating'=>$shopRating,'socialPlatforms'=>$socialPlatforms));
        $this->includeTemplate('shops/templates/'.$template_id.'.php', $shopData, false);
    ?>
<?php echo $this->includeTemplate('products/listing-page.php', $shopData, false); ?>
<?php if (!empty($collectionData)) { ?>
<section class="section pt-0">
    <div class="container container-fluid">
        <div class="js-shop-slider shop-slider">
            <?php foreach ($collectionData as $collection) {?>
            <div class="item">
                <figure><img class="" src="<?php echo CommonHelper::generateUrl('Image', 'shopCollectionImage', array($collection['scollection_id'], $siteLangId,'SHOP'));?>" alt="" data-ratio="16:9"></figure>
                <div class="overlay-content">
                    <h4><?php echo $collection['scollection_name'];?></h4>
                    <!--<p>From the runway to your wardrobe</p>-->
                    <a href="<?php echo CommonHelper::generateUrl('Shops', 'collection', array($shop['shop_id'], $collection['scollection_id']))?>" class="link"><?php echo Labels::getLabel('MSG_Explore', $siteLangId)?></a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>
<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>
<script>
    $(document).ready(function(){
        $('.js-shop-slider').slick( getSlickSliderSettings(3, 1, langLbl.layoutDirection, false, {1199: 3,1024: 2,767: 1,480: 1}) );

        $('.social-toggle').on('click', function() {
            $(this).next().toggleClass('open-menu');
        });
        
        $("body").mouseup(function(e){ 
            if (1 > $(event.target).parents('.social-toggle').length && $('.social-toggle').next().hasClass('open-menu')) {
                $('.social-toggle').next().toggleClass('open-menu');
            }
        });
    });
</script>
