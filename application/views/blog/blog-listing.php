<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($postList)) {
    foreach ($postList as $blogPost) { ?>
    <div class="col-md-6 mb-5">
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
    <?php
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmBlogSearchPaging'));
    $pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToSearchPage');
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false); ?>
<?php } else { ?>
    <div class="post box box--white">
        <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false); ?>
    </div>
<?php } ?>
<?php $this->includeTemplate('_partial/shareThisScript.php');?>
