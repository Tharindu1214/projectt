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
	<div class="acc_ctrl">
	  <h4><?php echo $listItem['faq_title']; ?></h4>
	</div>
	<div class="acc_panel">
	  <p>
		<?php echo $listItem['faq_content']; ?>
	  </p>
	</div>

	<?php
	if($listItem['faqcat_id'] != $category)
	{
		$category = $listItem['faqcat_id'];
		$catTab++;
	}
	$faqCounter++;
}
if(isset($showViewAllButton) && $showViewAllButton == true){
?>
<div class="gap"> </div>
<a href="<?php echo CommonHelper::generateUrl('Custom','faq'); ?>" class="btn btn--primary ripplelink"><?php echo Labels::getLabel( 'LBL_View_All', $siteLangId)?></a>
<div class="gap"> </div>
<?php
}
}
?>
