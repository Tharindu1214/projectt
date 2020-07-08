<?php
if(!empty($collections)){ ?>

	<?php switch( $collection['collection_type'] ){
		case Collections::COLLECTION_TYPE_PRODUCT: ?>
			<div class="row listing-products -listing-products listing-products--grid ">
				<?php $this->includeTemplate('products/products-list.php',array('products'=>$collections,'pageCount'=>$pageCount,'recordCount'=>$recordCount,'siteLangId'=>$siteLangId,'colMdVal'=>3),false);	?>
			</div>
		<?php break;
		case Collections::COLLECTION_TYPE_CATEGORY:
			$this->includeTemplate('category/categories-list.php',array('categoriesArr'=>$collections,'siteLangId'=>$siteLangId),false);
		break;
		
		case Collections::COLLECTION_TYPE_SHOP:
			$this->includeTemplate('shops/search.php',array('allShops'=>$collections,'siteLangId'=>$siteLangId,'totalProdCountToDisplay'=>$totalProdCountToDisplay),false);
		break;
		
		case Collections::COLLECTION_TYPE_BRAND:
			$this->includeTemplate('brands/brands-list.php',array('brandsArr'=>$collections,'siteLangId'=>$siteLangId),false);
		break;
	} ?>	
<?php } else {
	$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId),false);
}

/* $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
	'name' => 'frmSearchCollectionsPaging'
)); */