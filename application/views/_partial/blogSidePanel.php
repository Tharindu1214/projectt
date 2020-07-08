<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="right-side-bar">
    <?php if (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', FatUtility::VAR_INT, 0)) { ?>
    <div class="blog-subscribers-inner text-center rounded p-4 mb-4">
        <h3><?php echo Labels::getLabel('LBL_Get_Weekly_Insights', $siteLangId)?></h3>
        <p><?php echo Labels::getLabel('LBL_Subscribe_to_our_weekly_newsletter', $siteLangId)?></p>
        <?php $this->includeTemplate('_partial/footerNewsLetterForm.php', array('blogPage'=>true)); ?>
    </div>
    <?php } ?>
    <?php if (!empty($popularPostList) || (!empty($featuredPostList))) { ?>
    <div class="bg-gray rounded p-4">
        <ul class="js-tabs tabs-blog rounded">
            <?php if (!empty($popularPostList)) { ?>
                <li class="is--active"><a href="#/tab-1"><?php echo Labels::getLabel('LBL_Popular', $siteLangId)?></a></li>
            <?php }?>
            <?php if (!empty($featuredPostList)) { ?>
                <li><a href="#/tab-2"><?php echo Labels::getLabel('LBL_Featured', $siteLangId)?> </a></li>
            <?php }?>
        </ul>
        <div class="tabs-content">
            <?php if (!empty($popularPostList)) { ?>
            <div id="tab-1" class="content-data" style="display: block;">
                <ul>
                    <?php foreach ($popularPostList as $blogPost) { ?>
                    <li>
                        <div class="post">
                            <ul class="post_category">
                                <?php $categoryIds = !empty($blogPost['categoryIds'])?explode(',', $blogPost['categoryIds']):array();
                                $categoryNames = !empty($blogPost['categoryNames'])?explode('~', $blogPost['categoryNames']):array();
                                $categories = array_combine($categoryIds, $categoryNames);
                                foreach ($categories as $id => $name) { ?>
                                    <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $name; ?></a></li>
                                <?php } ?>
                            </ul>
                            <h2 class="post_title"><a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><?php echo mb_substr($blogPost['post_title'], 0, 80); ?></a></h2>
                        </div>
                    </li>
                    <?php }?>
                </ul>
            </div>
            <?php }?>
            <?php if (!empty($featuredPostList)) { ?>
            <div id="tab-2" class="content-data">
                <ul>
                    <?php foreach ($featuredPostList as $blogPost) { ?>
                    <li>
                        <div class="post">
                            <ul class="post_category">
                                <?php $categoryIds = !empty($blogPost['categoryIds'])?explode(',', $blogPost['categoryIds']):array();
                                $categoryNames = !empty($blogPost['categoryNames'])?explode('~', $blogPost['categoryNames']):array();
                                $categories = array_combine($categoryIds, $categoryNames);
                                foreach ($categories as $id => $name) { ?>
                                    <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $name; ?></a></li>
                                <?php } ?>
                            </ul>
                            <h2 class="post_title"><a href="<?php echo CommonHelper::generateUrl('Blog', 'postDetail', array($blogPost['post_id'])); ?>"><?php echo mb_substr($blogPost['post_title'], 0, 80); ?></a></h2>
                        </div>
                    </li>
                    <?php }?>
                </ul>
            </div>
            <?php }?>
        </div>
    </div>
    <?php }?>
</div>
<div class="gap"></div>
<a href="<?php echo CommonHelper::generateUrl('Blog', 'contributionForm'); ?>" class="btn btn--primary btn--lg btn--block ripplelink btn--contribute"> <?php echo Labels::getLabel('Lbl_Contribute', $siteLangId); ?> </a>
<div class="gap"></div>
<?php /*if (!empty($categoriesArr)) { ?>
<h3 class="widget__title -style-uppercase"><?php echo Labels::getLabel('Lbl_categories', $siteLangId); ?></h3>
<div class="">
    <nav class="nav nav--toggled nav--toggled-js">
        <ul class="blog_lnks accordion">
            <?php foreach ($categoriesArr as $cat) { ?>
            <li class="<?php echo (count($cat['children'])>0) ? "has-child" : "" ?>"><a
                    href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($cat['bpcategory_id'])); ?>"><?php echo $cat['bpcategory_name']; echo !empty($cat['countChildBlogPosts'])?" <span class='badge'>($cat[countChildBlogPosts])</span>":''; ?></a>
                <?php if (count($cat['children'])) { ?>
                <span class="link--toggle link--toggle-js"></span>
                <ul style="display:none">
                    <?php foreach ($cat['children'] as $children) { ?>
                    <li><a
                            href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($children['bpcategory_id'])); ?>"><?php echo $children['bpcategory_name']; echo !empty($children['countChildBlogPosts'])?" <span class='badge'>($children[countChildBlogPosts])</span>":''; ?></a>
                        <?php if (count($children['children'])) { ?>
                        <ul class="">
                            <?php foreach ($children['children'] as $subChildren) { ?>
                            <li class="">
                                <a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($subChildren['bpcategory_id'])); ?>"><?php echo $subChildren['bpcategory_name']; ?></a>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php }?>
                    </li>
                    <?php }?>
                </ul>
                <?php }?>
            </li>
            <?php }?>
        </ul>
    </nav>
</div>
<?php }*/?>

<script>
    /* for blog links */
    $('.link--toggle-js').click(function() {
        if ($(this).hasClass('is-active')) {
            $(this).removeClass('is-active');
            $(this).next('.nav--toggled-js > ul > li ul').find('.link--toggle-js').removeClass('is-active');
            $(this).next('.nav--toggled-js > ul > li ul').slideUp();
            $(this).next('.nav--toggled-js > ul > li ul').find('.nav--toggled-js > ul > li ul').slideUp();
            return false;
        }
        $('.link--toggle-js').removeClass('is-active');
        $(this).addClass("is-active");
        $(this).parents('ul').each(function() {
            $(this).siblings('span').addClass('is-active');
        });
        $(this).closest('ul').find('li .nav--toggled-js > ul > li ul').slideUp();
        $(this).next('.nav--toggled-js > ul > li ul').slideDown();
    });
</script>
