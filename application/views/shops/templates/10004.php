<?php defined('SYSTEM_INIT') or die('Invalid Usage');?>
<?php $haveBannerImage = AttachedFile::getMultipleAttachments( AttachedFile::FILETYPE_SHOP_BANNER, $shop['shop_id'], '' , $siteLangId ); ?>
<section class="section bg-onbar">
    <div class="container">
        <div class="shop-info">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="shop-name">
                                <h5><?php echo $shop['shop_name']; ?> <span class="blk-txt"><?php echo Labels::getLabel('LBL_Shop_Opened_By', $siteLangId); ?> <strong> <?php echo $shop['user_name'];?> </strong></span></h5>

                            </div>
                            <div class="products__rating"> <i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                    </svg></i> <span class="rate"><?php echo round($shopRating,1),' ',Labels::getLabel('Lbl_Out_of',$siteLangId),' ', '5';  if($shopTotalReviews){ ?> - <a href="<?php echo CommonHelper::generateUrl('Reviews','shop',array($shop['shop_id'])); ?>"><?php echo $shopTotalReviews , ' ' , Labels::getLabel('Lbl_Reviews',$siteLangId); ?></a><?php } ?> </span>
                            </div>
                            <div class="share-this">
                                <span><i class="icn share"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                        </svg></i><?php echo Labels::getLabel('LBL_Share',$siteLangId); ?></span>
                                <a class="social-link st-custom-button" data-network="facebook">
                                    <i class="icn"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                        </svg></i>
                                </a>
                                <a class="social-link st-custom-button" data-network="twitter">
                                    <i class="icn"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                        </svg></i>
                                </a>
                                <a class="social-link st-custom-button" data-network="pinterest">
                                    <i class="icn"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                        </svg></i>
                                </a>
                                <a class="social-link st-custom-button" data-network="email">
                                    <i class="icn"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                        </svg></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="shop-btn-group">
                        <?php $showAddToFavorite = true; if(UserAuthentication::isUserLogged() && (!User::isBuyer()) ) $showAddToFavorite = false; ?>
                        <?php if($showAddToFavorite) { ?>
                            <a href="javascript:void(0)" onclick="toggleShopFavorite(<?php echo $shop['shop_id']; ?>);" class="btn btn--primary btn--sm <?php echo ($shop['is_favorite']) ? 'is-active' : ''; ?>" id="shop_<?php echo $shop['shop_id']; ?>"><i class="icn"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#heart" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#heart"></use>
                                </svg></i><?php echo Labels::getLabel('LBL_Love', $siteLangId);  echo " ".$shop['shop_name']; ?> !</a>
                        <?php }?>
                        <?php $showMoreButtons = true; if (UserAuthentication::isUserLogged() && UserAuthentication::getLoggedUserId(true) == $shop['shop_user_id']) $showMoreButtons = false; ?>
                        <?php if($showMoreButtons){ ?>
                            <a href="<?php echo CommonHelper::generateUrl('Shops','ReportSpam', array($shop['shop_id'])); ?>" class="btn btn--primary btn--sm"><i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#report" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#report"></use>
                                    </svg></i><?php echo Labels::getLabel('LBL_Report_Spam',$siteLangId); ?></a>

                            <a href="<?php echo CommonHelper::generateUrl('shops','sendMessage',array($shop['shop_id'])); ?>" class="btn btn--primary btn--sm"><i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#send-msg" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#send-msg"></use>
                                    </svg></i><?php echo Labels::getLabel('LBL_Send_Message', $siteLangId); ?></a>
                        <?php }?>
                    </div>   
                </div>
            </div>
        </div>
    </div>
</section>
<section class="shop-nav-wrapper">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="shop-logo-wrapper">
                    <div class="shop-logo"><img src="<?php echo CommonHelper::generateUrl('image','shopLogo',array($shop['shop_id'],$siteLangId,'SMALL')); ?>" alt="<?php echo $shop['shop_name']; ?>"></div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="shop-nav">
                    <?php
                    $variables= array('template_id'=>$template_id, 'shop_id'=>$shop['shop_id'],'collectionData'=>$collectionData,'action'=>$action,'siteLangId'=>$siteLangId);
                    $this->includeTemplate('shops/shop-layout-navigation.php',$variables,false);  ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="">
    <?php if( $haveBannerImage ){ ?>
    <div class="shops-sliders" dir="<?php echo CommonHelper::getLayoutDirection();?>">
    <?php foreach($haveBannerImage as $banner){ ?>
    <div class="item"><img src="<?php echo CommonHelper::generateUrl('image','shopBanner',array($banner['afile_record_id'],$siteLangId,'TEMP2',$banner['afile_id'])); ?>" alt="<?php echo Labels::getLabel('LBL_Shop_Banner', $siteLangId); ?>"></div>
    <?php } ?>
    </div>
  <?php } ?>
</section>
<section class="section">
    <div class="container">
        <div class="gap"></div>
        <div class="shop-profile">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box--space shop-avatar">
                        <div class="shoper__dp"><img src="<?php echo CommonHelper::generateUrl('image','User',array($shop['shop_user_id'])); ?>"></div>
                        <div class="profile__bio">
                            <div class="title">
                                <h6><?php echo $shop['user_name'];?> <span class="deg blk-txt"> <?php echo Labels::getLabel('LBL_Shop_Owner', $siteLangId); ?></span></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <?php if($shop['shop_description']){?>
            <div class="divider divider--single"></div>
            <div class="box box--space shop-information">
                <p><strong><?php echo Labels::getLabel('LBL_Information', $siteLangId); ?></strong></p>
                <p><?php echo nl2br($shop['shop_description']);?></p>
            </div>
            <?php } ?>
        </div>
        <div class="gap"></div>
    </div>
</section>
<script>
$currentPageUrl = '<?php echo CommonHelper::generateFullUrl('Shops','view',array($shopId)); ?>';
</script>
