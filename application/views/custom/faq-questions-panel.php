<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if(isset($listCategories) && is_array($listCategories) ){
$catTab = 1; ?>
<div class="faqs-wrapper">
  <?php foreach($listCategories as $faqCat){ ?>
  <div class="acc_ctrl">
	<h6><a class="selectedFaq" data-id="<?php echo $faqCat['faq_id']; ?>" data-cat-id="<?php echo $faqCat['faqcat_id']; ?>"><?php echo $faqCat['faq_title']; ?></a></h6>
  </div>
  <?php } ?>
</div>

<?php }?>
