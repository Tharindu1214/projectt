<?php defined('SYSTEM_INIT') or die('Invalid usage');
if(!empty($list) && is_array($list)){
$category = 0;
$faqCounter = 0;
$catTab = 1;
foreach($list as $listItem){
	if($listItem['faqcat_id'] != $category){
		if($category != 0){	?>
		</div></div>
	<?php } ?>
	
	<div class="heading3"><?php echo $listItem['faqcat_name']; ?></div>
	<?php
	}
	?>
	
	<div class="faqs-wrapper">
	  <div class="gap"> </div>
	  <h3><?php echo $listItem['faq_title']; ?></h3>
	  <div><?php echo $listItem['faq_content'];?></div>
	  <div class="gap"> </div>
	  <div class=""><!-- <a href="<?php echo CommonHelper::generateUrl('Custom','faq'); ?>" class="btn btn--primary ripplelink"><?php echo Labels::getLabel( 'LBL_Track_Refund', $siteLangId)?></a> --></div>
    </div>
	
	<?php
	if($listItem['faqcat_id'] != $category)
	{
		$category = $listItem['faqcat_id'];
		$catTab++;
	}
	$faqCounter++;
}
}
?>