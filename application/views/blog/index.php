<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($postList)) {
    foreach ($postList as $blogPost) { ?>
        <section class="section bg--second">
            <div class="container">
                <div class="first-fold">
                    <div class="post">
                        <figure class="post_media">
                            <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><img  data-ratio="16:9"src="<?php echo CommonHelper::generateUrl('image', 'blogPostFront', array($blogPost['post_id'], $siteLangId, "LAYOUT1"), CONF_WEBROOT_URL); ?>" alt="<?php echo $blogPost['post_title']?>"></a>
                        </figure>
                        <div class="post_bg">
                            <ul class="post_category">
                                <?php $categoryIds = !empty($blogPost['categoryIds'])?explode(',', $blogPost['categoryIds']):array();
                                $categoryNames = !empty($blogPost['categoryNames'])?explode('~', $blogPost['categoryNames']):array();
                                $categories = array_combine($categoryIds, $categoryNames);
                                foreach ($categories as $id => $name) { ?>
                                    <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $name; ?></a></li>
                                <?php } ?>
                            </ul>
                            <h2 class="post_title"> <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><?php echo $blogPost['post_title']?></a></h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php break;
    } ?>
<?php }?>
    <?php $postList = array_slice($postList, 1);
    if (!empty($postList)) { ?>
        <section class="section">
            <div class="container">
                <div class="row">
                    <?php $count = 1;
                    foreach ($postList as $blogPost) { ?>
                    <div class="col-md-6">
                        <div class="post">
                            <figure class="post_media">
                                <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><img data-ratio="16:9" src="<?php echo CommonHelper::generateUrl('image', 'blogPostFront', array($blogPost['post_id'], $siteLangId, "LAYOUT2"), CONF_WEBROOT_URL); ?>" alt="<?php echo $blogPost['post_title']?>"></a>
                            </figure>
                            <ul class="post_category">
                                <?php $categoryIds = !empty($blogPost['categoryIds'])?explode(',', $blogPost['categoryIds']):array();
                                $categoryNames = !empty($blogPost['categoryNames'])?explode('~', $blogPost['categoryNames']):array();
                                $categories = array_combine($categoryIds, $categoryNames);
                                foreach ($categories as $id => $name) { ?>
                                    <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $name; ?></a></li>
                                <?php } ?>
                            </ul>
                            <h2 class="post_title"> <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><?php echo $blogPost['post_title']?></a></h2>
                            <?php /* <div class="share-button share-button--static-horizontal justify-content-start">
                                <a href="javascript:void(0)" class="social-toggle"><i class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                        </svg>
                                    </i></a>
                                <div class="social-networks">
                                    <ul>
                                        <li class="social-facebook">
                                            <a class="social-link st-custom-button" data-network="facebook" data-url="<?php echo CommonHelper::generateFullUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>/">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-twitter">
                                            <a class="social-link st-custom-button" data-network="twitter">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-pintrest">
                                            <a class="social-link st-custom-button" data-network="pinterest">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-email">
                                            <a class="social-link st-custom-button" data-network="email">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div> */ ?>

                        </div>
                    </div>
                        <?php $count++;
                        if ($count > 2) {
                            break;
                        }
                    } ?>
                </div>
            </div>
        </section>
    <?php } ?>
    <?php if (!empty($featuredPostList)) { ?>
    <section class="section bg-gray">
        <div class="container">
            <div class="section-head">
                <div class="section__heading">
                    <h2><?php echo Labels::getLabel('LBL_Featured_Blogs', $siteLangId); ?></h2>
                </div>
                <?php if (count($featuredPostList) > 4) { ?>
                <div class="section__action">
                    <a class="arrows arrows--left">
                        <svg version="1.1" x="0px" y="0px" viewBox="0 0 31.494 31.494" style="enable-background:new 0 0 31.494 31.494;" xml:space="preserve">
                            <path d="M10.273,5.009c0.444-0.444,1.143-0.444,1.587,0c0.429,0.429,0.429,1.143,0,1.571l-8.047,8.047h26.554
        c0.619,0,1.127,0.492,1.127,1.111c0,0.619-0.508,1.127-1.127,1.127H3.813l8.047,8.032c0.429,0.444,0.429,1.159,0,1.587
        c-0.444,0.444-1.143,0.444-1.587,0l-9.952-9.952c-0.429-0.429-0.429-1.143,0-1.571L10.273,5.009z" />

                        </svg></a>
                    <a class="arrows arrows--right"><svg x="0px" y="0px" viewBox="0 0 31.49 31.49" style="enable-background:new 0 0 31.49 31.49;" xml:space="preserve" width="512px" height="512px">
                            <path
                                d="M21.205,5.007c-0.429-0.444-1.143-0.444-1.587,0c-0.429,0.429-0.429,1.143,0,1.571l8.047,8.047H1.111  C0.492,14.626,0,15.118,0,15.737c0,0.619,0.492,1.127,1.111,1.127h26.554l-8.047,8.032c-0.429,0.444-0.429,1.159,0,1.587  c0.444,0.444,1.159,0.444,1.587,0l9.952-9.952c0.444-0.429,0.444-1.143,0-1.571L21.205,5.007z">
                            </path>
                        </svg></a>
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="js-popular-stories popular-stories">
            <?php foreach ($featuredPostList as $blogPost) { ?>
            <div class="item">
                <div class="post">
                    <figure class="post_media">
                        <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>">
                        <img data-ratio="16:9" src="<?php echo CommonHelper::generateUrl('image', 'blogPostFront', array($blogPost['post_id'], $siteLangId, "FEATURED"), CONF_WEBROOT_URL); ?>" alt="<?php echo $blogPost['post_title']?>"></a>
                    </figure>
                    <ul class="post_category">
                        <?php $categoryIds = !empty($blogPost['categoryIds'])?explode(',', $blogPost['categoryIds']):array();
                        $categoryNames = !empty($blogPost['categoryNames'])?explode('~', $blogPost['categoryNames']):array();
                        $categories = array_combine($categoryIds, $categoryNames);
                        foreach ($categories as $id => $name) { ?>
                            <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $name; ?></a></li>
                        <?php } ?>
                    </ul>
                    <h2 class="post_title"> <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><?php echo mb_substr($blogPost['post_title'], 0, 80); ?></a></h2>
                        <?php /* <div class="share-button share-button--static-horizontal justify-content-start">
                            <a href="javascript:void(0)" class="social-toggle"><i class="icn">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                    </svg>
                                </i></a>
                            <div class="social-networks">
                                <ul>
                                    <li class="social-facebook">
                                        <a class="social-link st-custom-button" data-network="facebook" data-url="<?php echo CommonHelper::generateFullUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>/">
                                            <i class="icn"><svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                                </svg></i>
                                        </a>
                                    </li>
                                    <li class="social-twitter">
                                        <a class="social-link st-custom-button" data-network="twitter">
                                            <i class="icn"><svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                                </svg></i>
                                        </a>
                                    </li>
                                    <li class="social-pintrest">
                                        <a class="social-link st-custom-button" data-network="pinterest">
                                            <i class="icn"><svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                                </svg></i>
                                        </a>
                                    </li>
                                    <li class="social-email">
                                        <a class="social-link st-custom-button" data-network="email">
                                            <i class="icn"><svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                                </svg></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div> */ ?>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
    </section>
    <?php } ?>

    <?php if (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', FatUtility::VAR_INT, 0)) { ?>
    <section class="section bg--first-color">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-6">
                    <div class="blog-subscribers">
                        <h4><?php echo Labels::getLabel('LBL_Get_Weekly_Insights', $siteLangId)?></h4>
                        <p><?php echo Labels::getLabel('LBL_Subscribe_to_our_weekly_newsletter', $siteLangId)?></p>
                        <?php $this->includeTemplate('_partial/footerNewsLetterForm.php', array('blogPage'=>true)); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php } ?>

    <?php $postList = array_slice($postList, 2);
    if (!empty($postList)) { ?>
        <section class="section bg-gray">
            <div class="container">
                <div class="row">
                    <?php foreach ($postList as $blogPost) { ?>
                        <div class="col-md-6">
                            <div class="post">
                                <figure class="post_media">
                                    <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><img data-ratio="16:9" src="<?php echo CommonHelper::generateUrl('image', 'blogPostFront', array($blogPost['post_id'], $siteLangId, "LAYOUT2"), CONF_WEBROOT_URL); ?>" alt="<?php echo $blogPost['post_title']?>"></a>
                                </figure>
                                <ul class="post_category">
                                    <?php $categoryIds = !empty($blogPost['categoryIds'])?explode(',', $blogPost['categoryIds']):array();
                                    $categoryNames = !empty($blogPost['categoryNames'])?explode('~', $blogPost['categoryNames']):array();
                                    $categories = array_combine($categoryIds, $categoryNames);
                                    foreach ($categories as $id => $name) { ?>
                                        <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $name; ?></a></li>
                                    <?php } ?>
                                </ul>
                                <h2 class="post_title"> <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><?php echo $blogPost['post_title']?></a></h2>
                                <?php /* <div class="share-button share-button--static-horizontal justify-content-start">
                                    <a href="javascript:void(0)" class="social-toggle"><i class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                            </svg>
                                        </i></a>
                                    <div class="social-networks">
                                        <ul>
                                            <li class="social-facebook">
                                                <a class="social-link st-custom-button" data-network="facebook" data-url="<?php echo CommonHelper::generateFullUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>/">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-twitter">
                                                <a class="social-link st-custom-button" data-network="twitter">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-pintrest">
                                                <a class="social-link st-custom-button" data-network="pinterest">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-email">
                                                <a class="social-link st-custom-button" data-network="email">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div> */ ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </section>
    <?php } ?>
    <?php if (!empty($popularPostList)) { ?>
    <section class="section ">
        <div class="container">
            <div class="section-head">
                <div class="section__heading">
                    <h2><?php echo Labels::getLabel('LBL_Popular_Blogs', $siteLangId); ?></h2>
                </div>
                <div class="section__action">
                    <a class="arrows arrows--left">
                        <svg version="1.1" x="0px" y="0px" viewBox="0 0 31.494 31.494" style="enable-background:new 0 0 31.494 31.494;" xml:space="preserve">
                            <path d="M10.273,5.009c0.444-0.444,1.143-0.444,1.587,0c0.429,0.429,0.429,1.143,0,1.571l-8.047,8.047h26.554
        c0.619,0,1.127,0.492,1.127,1.111c0,0.619-0.508,1.127-1.127,1.127H3.813l8.047,8.032c0.429,0.444,0.429,1.159,0,1.587
        c-0.444,0.444-1.143,0.444-1.587,0l-9.952-9.952c-0.429-0.429-0.429-1.143,0-1.571L10.273,5.009z" />

                        </svg></a>
                    <a class="arrows arrows--right"><svg x="0px" y="0px" viewBox="0 0 31.49 31.49" style="enable-background:new 0 0 31.49 31.49;" xml:space="preserve" width="512px" height="512px">
                            <path
                                d="M21.205,5.007c-0.429-0.444-1.143-0.444-1.587,0c-0.429,0.429-0.429,1.143,0,1.571l8.047,8.047H1.111  C0.492,14.626,0,15.118,0,15.737c0,0.619,0.492,1.127,1.111,1.127h26.554l-8.047,8.032c-0.429,0.444-0.429,1.159,0,1.587  c0.444,0.444,1.159,0.444,1.587,0l9.952-9.952c0.444-0.429,0.444-1.143,0-1.571L21.205,5.007z">
                            </path>
                        </svg></a>
                </div>
            </div>
        </div>
        <div class="js-popular-stories popular-stories">
            <?php foreach ($popularPostList as $blogPost) { ?>
                <div class="item">
                    <div class="post">
                        <figure class="post_media">
                            <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>">
                            <img data-ratio="16:9" src="<?php echo CommonHelper::generateUrl('image', 'blogPostFront', array($blogPost['post_id'], $siteLangId, "FEATURED"), CONF_WEBROOT_URL); ?>" alt="<?php echo $blogPost['post_title']?>"></a>
                        </figure>
                        <ul class="post_category">
                            <?php $categoryIds = !empty($blogPost['categoryIds'])?explode(',', $blogPost['categoryIds']):array();
                            $categoryNames = !empty($blogPost['categoryNames'])?explode('~', $blogPost['categoryNames']):array();
                            $categories = array_combine($categoryIds, $categoryNames);
                            foreach ($categories as $id => $name) { ?>
                                <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $name; ?></a></li>
                            <?php } ?>
                        </ul>
                        <h2 class="post_title"> <a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><?php echo mb_substr($blogPost['post_title'], 0, 80); ?></a></h2>
                            <?php /* <div class="share-button share-button--static-horizontal justify-content-start">
                                <a href="javascript:void(0)" class="social-toggle"><i class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                        </svg>
                                    </i></a>
                                <div class="social-networks">
                                    <ul>
                                        <li class="social-facebook">
                                            <a class="social-link st-custom-button" data-network="facebook" data-url="<?php echo CommonHelper::generateFullUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>/">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-twitter">
                                            <a class="social-link st-custom-button" data-network="twitter">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-pintrest">
                                            <a class="social-link st-custom-button" data-network="pinterest">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-email">
                                            <a class="social-link st-custom-button" data-network="email">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div> */ ?>
                        </div>
                    </div>
            <?php } ?>
        </div>
    </section>
    <?php } ?>
<script>
    // $('.js-popular-stories').slick(getSlickSliderSettings(4, 4, '<?php echo CommonHelper::getLayoutDirection();?>', false));
    var layoutDirection = '<?php echo CommonHelper::getLayoutDirection();?>';
    var rtl = (layoutDirection == 'rtl') ? true : false;
    $('.js-popular-stories').slick({
        dots: false,
        arrows: false,
        infinite: false,
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 4,
        rtl: rtl,
        responsive: [{
                breakpoint: 1199,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                }
            },
            {
                breakpoint: 1023,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });

    $('.arrows--left').click(function() {
        $('.js-popular-stories').slick('slickPrev');
    })

    $('.arrows--right').click(function() {
        $('.js-popular-stories').slick('slickNext');
    })
</script>
<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>
