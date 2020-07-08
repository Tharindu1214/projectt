<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$shop_city = $shopData['shop_city'];
$shop_state = ( strlen($shopData['shop_city']) > 0 ) ? ', '. $shopData['shop_state_name'] : $shopData['shop_state_name'];
$shop_country = ( strlen($shop_state) > 0 ) ? ', '.$shopData['shop_country_name'] : $shopData['shop_country_name'];
$shopLocation = $shop_city . $shop_state. $shop_country;
?>
<div class="bg--second pt-3 pb-3">
   <div class="container container--fixed">
	   <div class="row">
		   <div class="col-md-8 col-sm-8">
			   <div class="cell">
					   <div class="shop-info">
						    <h3><?php echo $shopData['shop_name']; ?></h3>
						   <p><?php echo $shopLocation; ?> <?php echo Labels::getLabel('LBL_Opened_on', $siteLangId); ?> <?php echo FatDate::format($shopData['shop_created_on']); ?></p>
					   </div>
				</div>
		   </div>
		   <div class="col-md-4 col-sm-4 align--right"><a href="<?php echo CommonHelper::generateUrl('Shops', 'View', array($shopData['shop_id'])); ?>" class="btn btn--primary"><?php echo Labels::getLabel('LBL_Back_to_Shop', $siteLangId); ?></a>

           </div>
	   </div>
   </div>
</div>
                   <div class="section section--info clearfix">
                         <div class="section__head">
                             <h4><?php echo str_replace('{n}', $userFavoriteCount, Labels::getLabel('LBL_Who_Favorited_This?_{n}_Peoples(s)', $siteLangId)); ?></h4>
                         </div>
                         <div class="section__body">
							<?php echo $searchForm->getFormHtml();?>
                             <div class="box box--white" id="shopFavListing">

                             </div>
							 <div id="loadMoreBtnDiv"></div>
                         </div>
                   </div>
