<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if( isset( $collections ) && count($collections) ){

	$counter = 1;

	foreach( $collections as $collection_id => $row ){  ?>
<?php if( isset($row['products']) && count($row['products']) > 0 ) {
	?>

<section class="section">
	<div class="container">
		<div class="section-head">
			<div class="section__heading">
				<h2><?php echo ($row['collection_name'] != '') ? $row['collection_name'] : ''; ?></h2>
			</div>
			<?php if( $row['totProducts'] > $row['collection_primary_records'] ){ ?>
				<div class="section__action"><a href="<?php echo CommonHelper::generateUrl('Collections','View',array($row['collection_id']));?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </div>
			<?php }  ?>
		</div>
		<div class="row trending-corner product-listing" dir="<?php echo CommonHelper::getLayoutDirection();?>">
			<?php foreach( $row['products'] as $product ){ ?>
			<div class="col-xl-2 col-lg-4 col-md-4 col-6 column">
				<?php include('product-layout-1-list.php'); ?>
			</div>
			<?php } ?>
		</div>
	</div>
</section>

<?php } ?>
<?php $counter++; }
} ?>
