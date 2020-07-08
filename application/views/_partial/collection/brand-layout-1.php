<?php
if( isset( $collections ) && count($collections) ){


	$counter = 1;

	foreach( $collections as $collection_id => $row ){ 	/* brand listing design [ */
			if( isset($row['brands']) && count( $row['brands'] ) ) { ?>
		<section class="section">
			<div class="container">
				<div class="section-head  section--head--center">
				 <?php echo ($row['collection_name'] != '') ? ' <div class="section__heading"><h2>' . $row['collection_name'] .'</h2></div>' : ''; ?>

				<?php if( $row['totBrands'] > Collections::LIMIT_BRAND_LAYOUT1 ){ ?>
					<div class="section__action"> <a href="<?php echo CommonHelper::generateUrl('Collections','View',array($row['collection_id']));?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </div>
				<?php }  ?>
				</div>
				<div class="top-brand-list">
                    <ul>
                        <?php $i=0; foreach( $row['brands'] as $brand ){	?>
                        <li> <a href="<?php echo CommonHelper::generateUrl('brands', 'View', array($brand['brand_id'] )); ?>">
                            <!--<div class="brands-img">
                                <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('Image','brandImage',array($brand['brand_id'],$siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>" data-ratio="1:1 (600x600)" alt="<?php echo $brand['brand_name']; ?>" title="<?php echo $brand['brand_name']; ?>">
                            </div>-->
                            <div class="brands-logo">
                                <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'brand', array($brand['brand_id'] , $siteLangId, 'COLLECTION_PAGE')), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt= "<?php echo $brand['brand_name']; ?>" title="<?php echo $brand['brand_name']; ?>">
                            </div> </a> 
                        </li>
                        <?php $i++;
                        /* if($i==Collections::COLLECTION_LAYOUT5_LIMIT) break;*/ }  ?>
                    </ul>
				</div>
			</div>
		</section>
		<?php }
		}
}
?>
