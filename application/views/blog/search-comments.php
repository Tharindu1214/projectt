<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if ($commentsCount) { ?>
<div class="comment even">
    <?php foreach ($blogPostComments as $comment) { ?>
        <article>
            <div class="comment-meta comment-author">
                <img alt="" src="<?php echo CommonHelper::generateUrl('image', 'user', array($comment['bpcomment_user_id'], "THUMB",1), CONF_WEBROOT_FRONT_URL); ?>" class="avatar avatar-60 photo" width="60" height="60">
                <div class="comment-by">
                    <cite><?php echo CommonHelper::displayName($comment['bpcomment_author_name']); ?></cite>
                    <time datetime="<?php echo FatDate::format($comment['bpcomment_added_on']); ?>"><?php echo FatDate::format($comment['bpcomment_added_on']); ?></time>
                    <div class="comment-content">
                        <p><?php echo nl2br($comment['bpcomment_content']); ?></p>
                    </div>
                    <!--<div class="reply">
                        <a rel="nofollow" class="comment-reply-link" href="#comment-3712" onclick="return addComment.moveForm( &quot;comment-3712&quot;, &quot;3712&quot;, &quot;respond&quot;, &quot;4666&quot; )" aria-label="Reply to FATbit Chef">Reply</a> </div>-->
                </div>
            </div>
        </article>
    <?php }
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchCommentsPaging')); ?>
</div>
<?php } else { ?>
    <div class="block--empty p-4">
        <p><?php echo Labels::getLabel('Msg_No_Comments_on_this_blog_post', $siteLangId); ?></p>
    </div>
<?php } ?>
<?php if (!UserAuthentication::isUserLogged()) { ?>
<div class="comment box p-4">
    <span class=""><a href="<?php echo CommonHelper::generateUrl('GuestUser', 'loginForm'); ?>"><?php echo Labels::getLabel('Lbl_Login', $siteLangId); ?> </a> <?php echo Labels::getLabel('Lbl_Login_required_to_post_comment', $siteLangId); ?></span>
</div>
<?php } ?>
