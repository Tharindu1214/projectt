<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if( isset( $collections ) && count($collections) ){

	$counter = 1;

	foreach( $collections as $collection_id => $row ){ ?>
<?php if( isset($row['products']) && count($row['products']) ) {
	?>

<section class="section bg--second-color">
	<div class="container">
		<div class="section-head section--white--head">
			<div class="section__heading">
				<h2><?php echo ($row['collection_name'] != '') ? $row['collection_name'] : ''; ?></h2>
			</div>
			<?php if( $row['totProducts'] > 6 ){ ?>
				<div class="section__action"><a href="<?php echo CommonHelper::generateUrl('Collections','View',array($row['collection_id']));?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </div>
			<?php }  ?>
		</div>
		<div class="js-collection-corner collection-corner product-listing" dir="<?php echo CommonHelper::getLayoutDirection();?>">
			<?php foreach( $row['products'] as $product ){ ?>
				<?php include('product-layout-1-list.php'); ?>
			<?php } ?>
		</div>
	</div>
</section>

<?php } ?>
<?php $counter++; }
} ?>
