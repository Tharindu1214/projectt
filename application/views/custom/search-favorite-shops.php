<?php 
if(!empty($userFavoriteShops)){
foreach($userFavoriteShops as $val){?>
	<div class="rowrepeated">
		<div class="row">
			<div class="col-md-5 col-sm-5">
				<h5><a href="<?php echo CommonHelper::generateUrl('Shops','view' , array($val['shop_id'])); ?>" target='_new'><?php echo $val['shop_name'];?></a></h5>
				<p><?php echo $val['state_name'].','.$val['country_name'];?></a> </p>
				
				<?php if (UserAuthentication::isUserLogged()) { if($val['ufs_user_id'] == UserAuthentication::getLoggedUserId()){?><a class="link--normal" href="javascript:void(0);" onClick="unFavoriteShopFavorite(<?php echo $val['shop_id']; ?>)"><?php echo Labels::getLabel('LBL_UnFavorite_to_Shop', $siteLangId); ?></a><?php }}?>

			</div>
			<div class="col-md-7 col-sm-7">
				<div class="scroller--items align--right">
					<ul class="listing--items">
						<?php if(!empty($val['products'])){
							foreach($val['products'] as $product){?>
								<li><a class="item__pic" href="<?php echo CommonHelper::generateUrl('Products','View',array($product['product_id'])); ?>"><img alt="" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image','product', array($product['product_id'], "SMALL", $product['selprod_id'], 0, $siteLangId),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"></a></li>								
						<?php } }?>
						<?php if( $val['totalProducts'] <= $totalProdCountToDisplay ){ ?>
						<li><a class="item__link" href="<?php echo CommonHelper::generateUrl('Custom','FavoriteShops',array($val['ufs_user_id']));?>"><span><?php echo Labels::getLabel('LBL_No_More_Products', $siteLangId); ?></span></a></li>	
						<?php }else{ ?>
							<li><a href="<?php echo CommonHelper::generateUrl('shops','view',array($val['shop_id']));?>" class="item__link"><span><?php echo str_replace('{n}', $val['totalProducts'], Labels::getLabel('LBL_View_{n}_Product(s)', $siteLangId)); ?></span></a></li>
						<?php }?>
					</ul>
				</div>
			</div>
		</div>
	</div>
<?php }}else { 
	echo Labels::getLabel('LBL_No_record_found!', $siteLangId);
} 

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSearchfavoriteShopsPaging'
) );

/*$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false); */ ?>	 
