<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
/* $this->includeTemplate('_partial/blogTopFeaturedCategories.php'); */ ?>
<section class="section post-detail">
    <div class="container">
        <div class="row">
            <div class="col-xl-9 col-lg-8 mb-4 mb-md-0">
                <div class="posted-content">
                    <div class="posted-media">
                        <?php if (!empty($post_images)) { ?>
                        <div class="post__pic">
                            <?php foreach ($post_images as $post_image) { ?>
                            <div class="item"><img data-ratio="16:9" src="<?php echo FatUtility::generateUrl('image', 'blogPostFront', array($post_image['afile_record_id'], $post_image['afile_lang_id'], "LAYOUT1", 0, $post_image['afile_id']), CONF_WEBROOT_FRONT_URL); ?>"
                                    alt="<?php echo $post_image['afile_name']; ?>"></div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="post-data">
                        <div class="post-meta-detail">
                            <div class="post--title"><?php echo $blogPostData['post_title']; ?></div>
                            <div class="posted-by">
                                <span class="auther"><?php echo Labels::getLabel('Lbl_By', $siteLangId); ?> <?php echo $blogPostData['post_author_name']; ?></span>
                                <span class="time"><?php echo FatDate::format($blogPostData['post_added_on']); ?></span><span class="time"><?php $categoryIds = !empty($blogPostData['categoryIds']) ? explode(',', $blogPostData['categoryIds']) : array();
                                    $categoryNames = !empty($blogPostData['categoryNames']) ? explode('~', $blogPostData['categoryNames']) : array();
                                    $categories = array_combine($categoryIds, $categoryNames); ?>
                                    <?php if (!empty($categories)) {
                                        echo Labels::getLabel('Lbl_in', $siteLangId);
                                        foreach ($categories as $id => $name) {
                                            if ($name == end($categories)) { ?>
                                                <a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>" class="text--dark"><?php echo $name; ?></a>
                                                <?php break;
                                            } ?>
                                            <a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>" class="text--dark"><?php echo $name; ?></a>,
                                        <?php }
                                    } ?></span>
                                <div class="share-button share-button--static-horizontal justify-content-start">
                                    <a href="javascript:void(0)" class="social-toggle"><i class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                            </svg>
                                        </i></a>
                                    <div class="social-networks">
                                        <ul>
                                            <li class="social-facebook">
                                                <a class="social-link st-custom-button" data-network="facebook" data-url="<?php echo CommonHelper::generateFullUrl('Blog', 'postDetail', array($blogPostData['post_id'])); ?>/">
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
                                </div>
                            </div>
                            <?php /*<ul class="likes-count">
                                <!--<li><i class="icn-like"><img src="<?php echo CONF_WEBROOT_URL; ?>images/eye.svg"></i>500 Views</li>-->
                                <?php if ($blogPostData['post_comment_opened']) { ?>
                                <li><i class="icn-msg"><img src="<?php echo CONF_WEBROOT_URL; ?>images/comments.svg"></i><?php echo $commentsCount,' ',Labels::getLabel('Lbl_Comments', $siteLangId); ?></li>
                                <?php  } ?>
                            </ul>*/ ?>
                        </div>
                        <div class="divider"></div>
                        <div class="post__detail">
                            <?php echo FatUtility::decodeHtmlEntities($blogPostData['post_description']); ?>
                        </div>
                    </div>
                    <?php if ($blogPostData['post_comment_opened']) { ?>
                        <?php echo $srchCommentsFrm->getFormHtml(); ?>
                        <div class="gap"></div>
                        <div class="comments rounded border" id="container--comments">
                            <h2><?php echo ($commentsCount)? sprintf(Labels::getLabel('Lbl_Comments(%s)', $siteLangId), $commentsCount):Labels::getLabel('Lbl_Comments', $siteLangId); ?></h2>
                            <div id="comments--listing"> </div>
                            <div id="loadMoreCommentsBtnDiv"></div>
                        </div>
                    <?php } ?>
                    <?php if ($blogPostData['post_comment_opened'] && UserAuthentication::isUserLogged() && isset($postCommentFrm)) { ?>
                     <div class="gap"></div>
                    <div id="respond" class="comment-respond rounded">
                        <h2><?php echo Labels::getLabel('Lbl_Leave_A_Comment', $siteLangId); ?></h2>
                        <?php
                        $postCommentFrm->setFormTagAttribute('class', 'form');
                        $postCommentFrm->setFormTagAttribute('onsubmit', 'setupPostComment(this);return false;');
                        $postCommentFrm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
                        $postCommentFrm->developerTags['colClassPrefix'] = 'col-md-';
                        $postCommentFrm->developerTags['fld_default_col'] = 12;
                        $nameFld = $postCommentFrm->getField('bpcomment_author_name');
                        $nameFld->addFieldTagAttribute('readonly', true);
                        $nameFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Name', $siteLangId));
                        $nameFld->developerTags['col'] =6;
                        $emailFld = $postCommentFrm->getField('bpcomment_author_email');
                        $emailFld->addFieldTagAttribute('readonly', true);
                        $emailFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Email_Address', $siteLangId));
                        $emailFld->developerTags['col'] =6;
                        $commentFld = $postCommentFrm->getField('bpcomment_content');
                        $commentFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Message', $siteLangId));
                        echo $postCommentFrm->getFormHtml(); ?>
                    </div>
                   
                    <?php }?>
                    
                </div>
            </div>
            <div class="col-xl-3 col-lg-4">
                <?php $this->includeTemplate('_partial/blogSidePanel.php', array('popularPostList' => $popularPostList, 'featuredPostList' => $featuredPostList)); ?>
            </div>
            <!--<div class="col-md-3 colums__right">
            <div class="wrapper--adds" >
              <div class="grids" id="div--banners"> </div>
            </div>
          </div>-->
        </div>
    </div>
</section>
<script>
    var boolLoadComments = (<?php echo FatUtility::int($blogPostData['post_comment_opened']); ?>) ? true : false;
    /* for social sticky */
    $(window).scroll(function() {
        body_height = $(".post-data").position();
        scroll_position = $(window).scrollTop();
        if (body_height.top < scroll_position)
            $(".post-data").addClass("is-fixed");
        else
            $(".post-data").removeClass("is-fixed");

    });
</script>
<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>
