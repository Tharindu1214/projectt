<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($featuredBlogCategories)) { ?>
<div class="nav--centered text--uppercase">
    <ul class='blog-categories-js'>
        <?php foreach ($featuredBlogCategories as $categoryId => $categoryName) { ?>
        <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($categoryId)); ?>"><?php echo $categoryName; ?></a></li>
        <?php } ?>
    </ul>
</div>
<?php }
