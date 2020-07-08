<?php
	if($userFavorite){
	foreach( $userFavorite as $shop ){
		$shopUrl = CommonHelper::generateUrl( 'Shops', 'View', array($shop['shop_id']) );
	?>
<div class="rowrepeated rowrepeated--favs">
	<div class="row">
		<div class="col-md-5 col-sm-5 avtar--info">
			<div class="avtar"><img src="<?php echo CommonHelper::generateUrl('Image','user',array($shop['ufs_user_id'],'thumb','1'));?>" alt="<?php echo $shop['user_name'];?>" title="<?php echo $shop['user_name'];?>"></div>
			<h5><?php echo $shop['user_name'];?></h5>
			<p><?php echo Labels::getLabel('LBL_Favorite_Shop',$siteLangId); ?>: <a href="<?php echo CommonHelper::generateUrl('Custom','FavoriteShops',array($shop['ufs_user_id']));?>"><?php echo $shop['userFavShopcount']; ?></a></p>
		</div>
		<div class="col-md-7 col-sm-7">
			<div class="scroller--items align--right">
				<ul class="listing--items">
				<?php if($shops[$shop['ufs_user_id']]['products']){
						foreach($shops[$shop['ufs_user_id']]['products'] as $product){
							$shopUrl = CommonHelper::generateUrl('Shops','View',array( $product['shop_id'] )); ?>
								<li><a class="item__pic" href="<?php echo $shopUrl; ?>"><img alt="" src="<?php echo CommonHelper::generateUrl('Image','shopLogo',array($product['shop_id'], $siteLangId,'THUMB'));?>"></a></li>
							<?php } } ?>

						<?php if( $shops[$shop['ufs_user_id']]['totalProducts'] <= $totalShopToShow ){ ?>
						<li><a class="item__link" href="<?php echo CommonHelper::generateUrl('Custom','FavoriteShops',array($shop['ufs_user_id']));?>"><span><?php echo Labels::getLabel('LBL_No_More_Shops', $siteLangId); ?></span></a></li>
						<?php }else{ ?>
							<li><a href="<?php echo CommonHelper::generateUrl('Custom','FavoriteShops',array($shop['ufs_user_id']));?>" class="item__link"><span><?php echo str_replace('{n}', $shop['userFavShopcount'], Labels::getLabel('LBL_View_{n}_Favorite(s)', $siteLangId)); ?></span></a></li>
						<?php }?>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php } } else {
	echo Labels::getLabel('LBL_No_record_found!', $siteLangId);
	 }

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSearchWhoFavouriteShopPaging'
) );
 ?>
