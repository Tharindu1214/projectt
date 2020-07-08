<?php
	if( isset( $collections ) && count($collections) ){

		$counter = 1;

		foreach( $collections as $collection_id => $row ){

			/* category listing design [ */
			if( isset($row['categories']) && count( $row['categories'] ) ) { ?>
					<section class="section section--gray">
						<div class="container">
							<div class="section-head  section--head--center">
								<?php echo ($row['collection_name'] != '') ? ' <div class="section__heading"><h2>' . $row['collection_name'] .'</h2></div>' : ''; ?>

								<?php if( $row['totCategories'] > Collections::LIMIT_CATEGORY_LAYOUT1 ){ ?>
									<div class="section__action"> <a href="<?php echo CommonHelper::generateUrl('Collections','View',array($row['collection_id']));?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </div>
								<?php }  ?>
							</div>
							<?php include('category-layout-product-list.php'); ?>
						</div>
					</section>
			<?php }
		}
	}	/* ] */
?>
