<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); /* CommonHelper::printArray(json_encode( array_values($dashboardInfo['signupsChartData']) ));die; */?>
<script type="text/javascript">
	$SalesChartKey = <?php echo json_encode( array_keys($dashboardInfo['salesChartData']));?>;
	$SalesChartVal = <?php  echo json_encode( array_values($dashboardInfo['salesChartData']) );?>;
	$signupsKey = <?php echo json_encode( array_keys($dashboardInfo['signupsChartData']));?>;
	$signupsVal = <?php  echo json_encode( array_values($dashboardInfo['signupsChartData']) );?>;
	$SalesEarningsKey = <?php echo json_encode( array_keys($dashboardInfo['salesEarningsChartData']));?>;
	$SalesEarningsVal = <?php  echo json_encode( array_values($dashboardInfo['salesEarningsChartData']) );?>;
	$affiliateSignupsKey = <?php echo json_encode( array_keys($dashboardInfo['affiliateSignupsChartData']) ) ?>;
	$affiliateSignupsVal = <?php echo json_encode( array_values($dashboardInfo['affiliateSignupsChartData']) ) ?>;
	$productsKey = <?php echo json_encode( array_keys($dashboardInfo['productsChartData']) ) ?>;
	$productsVal = <?php echo json_encode( array_values($dashboardInfo['productsChartData']) ) ?>;

</script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<?php if($canView){?>

<!--main panel start here-->
<div class="page">
	<div class="container container-fluid">
		<div class="gap"></div>
		<div class="row">
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class="box box--white box--stats">
					<div class="box__body">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/stats_icon_1.svg" alt="" class="stats__icon">
						<h6 class="-txt-uppercase">
							<?php echo Labels::getLabel('LBL_Order_Sales',$adminLangId); ?>
						</h6>
						<h3 class="counter" data-currency="1" data-count="<?php echo $dashboardInfo["stats"]["totalSales"][4]["totalsales"];?>">0</h3>
						<p>
							<?php echo Labels::getLabel('LBL_This_Month',$adminLangId); ?>- <strong>
								<?php echo CommonHelper::displayMoneyFormat($dashboardInfo["stats"]["totalSales"][2]["totalsales"]);?></strong></p>
						<?php if($objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true)){?>
						<a href="<?php echo CommonHelper::generateUrl('orders');?>" class="stats__link"></a>
						<?php }?>

					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class="box box--white box--stats">
					<div class="box__body">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/stats_icon_2.svg" alt="" class="stats__icon">
						<h6 class="-txt-uppercase">
							<?php echo Labels::getLabel('LBL_Sales_Earnings',$adminLangId); ?>
						</h6>
						<h3 class="counter" data-currency="1" data-count="<?php echo $dashboardInfo["stats"]["totalSales"][4]["totalcommission"];?>">0</h3>
						<p>
							<?php echo Labels::getLabel('LBL_This_Month',$adminLangId); ?>- <strong>
								<?php echo CommonHelper::displayMoneyFormat($dashboardInfo["stats"]["totalSales"][2]["totalcommission"])?></span></strong></p>
						<?php if($objPrivilege->canViewSalesReport(AdminAuthentication::getLoggedAdminId(), true)){?>
						<a href="<?php echo CommonHelper::generateUrl('salesReport');?>" class="stats__link"></a>
						<?php }?>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class="box box--white box--stats">
					<div class="box__body">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/stats_icon_3.svg" alt="" class="stats__icon">
						<h6 class="-txt-uppercase">
							<?php echo Labels::getLabel('LBL_New_Users',$adminLangId); ?>
						</h6>
						<h3 class="counter" data-currency="0" data-count="<?php echo $dashboardInfo["stats"]["totalUsers"]['-1']; ?>">0</h3>
						<p>
							<?php echo Labels::getLabel('LBL_This_Month',$adminLangId); ?>- <strong>
								<?php echo $dashboardInfo["stats"]["totalUsers"]['30']; ?></strong></p>
						<?php if($objPrivilege->canViewUsers(AdminAuthentication::getLoggedAdminId(), true)){?>
						<a href="<?php echo CommonHelper::generateUrl('users');?>" class="stats__link"></a>
						<?php }?>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class="box box--white box--stats">
					<div class="box__body">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/stats_icon_4.svg" alt="" class="stats__icon">
						<h6 class="-txt-uppercase">
							<?php echo Labels::getLabel('LBL_New_Shops',$adminLangId); ?>
						</h6>
						<h3 class="counter" data-currency="0" data-count="<?php echo $dashboardInfo["stats"]["totalShops"]['-1']?>">0</h3>
						<p>
							<?php echo Labels::getLabel('LBL_This_Month',$adminLangId); ?>- <strong>
								<?php echo $dashboardInfo["stats"]["totalShops"]['30']?></strong></p>
						<?php if($objPrivilege->canViewShops(AdminAuthentication::getLoggedAdminId(), true)){?>
						<a href="<?php echo CommonHelper::generateUrl('shops');?>" class="stats__link"></a>
						<?php }?>
					</div>
				</div>
			</div>
		</div>
		<div class="gap"></div>
		<div class="grid grid--tabled">
			<div class="grid__left">
				<div class="box">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Statistics',$adminLangId); ?>
						</h4>
					</div>
					<div class="box__body">
						<div class="tabs_nav_container">
							<ul class="tabs_nav nav nav--floated -clearfix theme--hovercolor">
								<li><a class="active" rel="tabs_1" data-chart="true" href="javascript:void(0)">
										<?php echo Labels::getLabel('LBL_Sales',$adminLangId); ?></a></li>
								<li><a rel="tabs_2" data-chart="true" href="javascript:void(0)">
										<?php echo Labels::getLabel('LBL_Sales_Earnings',$adminLangId); ?></a></li>
								<li><a rel="tabs_3" data-chart="true" href="javascript:void(0)">
										<?php echo Labels::getLabel('LBL_Buyer/Seller_Signups',$adminLangId); ?></a></li>
								<li><a rel="tabs_4" data-chart="true" href="javascript:void(0)">
										<?php echo Labels::getLabel('LBL_Affiliate_Signups',$adminLangId); ?></a></li>
								<li><a rel="tabs_5" data-chart="true" href="javascript:void(0)">
										<?php echo Labels::getLabel('LBL_Products',$adminLangId); ?></a></li>
							</ul>

							<div class="tabs_panel_wrap">
								<!--tab1 start here-->

								<div id="tabs_1" class="tabs_panel" style="width:100%;height:100%">
									<div id="monthlysales--js" class="ct-chart ct-perfect-fourth graph--sales"></div>
								</div>
								<!--tab1 end here-->
								<!--tab2 start here-->

								<div id="tabs_2" class="tabs_panel" style="width:100%;height:100%">
									<div id="monthlysalesearnings--js" class="ct-chart ct-perfect-fourth graph--sales"></div>
								</div>
								<!--tab2 end here-->
								<!--tab3 start here-->

								<div id="tabs_3" class="tabs_panel" style="width:100%;height:100%">
									<div id="monthly-signups--js" class="ct-chart ct-perfect-fourth graph--sales"></div>
								</div>
								<!--tab3 end here-->
								<!-- tab4 [ -->

								<div id="tabs_4" class="tabs_panel" style="width:100%;height:100%">
									<div id="monthly-affiliate-signups--js" class="ct-chart ct-perfect-fourth graph--sales"></div>
								</div>
								<!-- ] -->
								<!-- tab5 [ -->

								<div id="tabs_5" class="tabs_panel" style="width:100%;height:100%">
									<div id="monthly-products--js" class="ct-chart ct-perfect-fourth graph--sales"></div>
								</div>
								<!-- ] -->
							</div>
						</div>

					</div>
				</div>
			</div>

			<div class="grid__right">
				<div class="box">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Traffic',$adminLangId); ?>
						</h4>
						<ul class="actions right">
							<li class="droplink">
								<a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
								<div class="dropwrap">
									<ul class="linksvertical">
										<li><a href="javascript:void(0)" onClick="traficSource('today')">
												<?php echo Labels::getLabel('LBL_Today',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="traficSource('Weekly')">
												<?php echo Labels::getLabel('LBL_Weekly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="traficSource('Monthly')">
												<?php echo Labels::getLabel('LBL_Monthly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="traficSource('Yearly')">
												<?php echo Labels::getLabel('LBL_Yearly',$adminLangId); ?></a></li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
					<div class="box__body ">
						<!--<div class="graph-container"><img src="images/traffic_graph.jpg" style="margin:auto;" alt=""></div>-->
						<div class="graph-container">
							<div id="piechart" class="ct-chart ct-perfect-fourth graph--traffic"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="gap"></div>
		<div class="row">

			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box box--white box--height">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Visitors_Statistics',$adminLangId);?>
						</h4>
					</div>
					<div class="box__body space">
						<div class="graph-container">
							<div id="visitsGraph" class="ct-chart ct-perfect-fourth graph--visitor"></div>
						</div>
						<?php if($dashboardInfo['visitsCount']){ ?>
						<ul class="horizontal_grids">
							<li>
								<?php echo $dashboardInfo['visitsCount']['today']?> <span>
									<?php echo Labels::getLabel('LBL_Today',$adminLangId); ?></span></li>
							<li>
								<?php echo $dashboardInfo['visitsCount']['weekly']?> <span>
									<?php echo Labels::getLabel('LBL_Weekly',$adminLangId); ?></span></li>
							<li>
								<?php echo $dashboardInfo['visitsCount']['lastMonth']?><span>
									<?php echo Labels::getLabel('LBL_last_Month',$adminLangId); ?></span></li>
							<li>
								<?php echo $dashboardInfo['visitsCount']['last3Month']?><span>
									<?php echo Labels::getLabel('LBL_Last_3_Months',$adminLangId); ?></span></li>
						</ul>
						<?php } ?>

					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box box--white box--height">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Conversions_Statistics',$adminLangId); ?>
						</h4>
						<!--ul class="actions right">
                                        <li class="droplink">
                                            <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                                            <div class="dropwrap">
                                                <ul class="linksvertical">
                                                    <li><a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Today',$adminLangId); ?></a></li>
                                                    <li><a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Weekly',$adminLangId); ?></a></li>
                                                    <li><a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Monthly',$adminLangId); ?></a></li>
                                                    <li><a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Yearly',$adminLangId); ?></a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul-->
					</div>
					<div class="box__body space">
						<ul class="horizontal_gridsthird">
							<li><span>
									<?php echo Labels::getLabel('LBL_Added_To_Cart',$adminLangId); ?></span>
								<?php echo $dashboardInfo['conversionStats']['added_to_cart']['%age'];?>% </li>
							<li><span>
									<?php echo Labels::getLabel('LBL_Reached_Checkout',$adminLangId); ?></span>
								<?php echo $dashboardInfo['conversionStats']['reached_checkout']['%age'];?>% </li>
							<li><span>
									<?php echo Labels::getLabel('LBL_Purchased',$adminLangId); ?></span>
								<?php echo $dashboardInfo['conversionStats']['purchased']['%age'];?>%</li>
							<li><span>
									<?php echo Labels::getLabel('LBL_Cancelled',$adminLangId); ?></span>
								<?php echo $dashboardInfo['conversionStats']['cancelled']['%age'];?>%</li>
						</ul>
						<div class="graph-container">
							<div id="conversionStats" class="ct-chart ct-perfect-fourth graph--conversion"></div>
						</div>
					</div>
				</div>
			</div>

		</div>
		<span class="-gap"></span>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box box--white box--scroll">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Top_Products',$adminLangId); ?>
						</h4>
						<ul class="actions right">
							<li class="droplink">
								<a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
								<div class="dropwrap">
									<ul class="linksvertical">
										<li><a href="javascript:void(0)" onClick="topProducts('Today')">
												<?php echo Labels::getLabel('LBL_Today',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topProducts('Weekly')">
												<?php echo Labels::getLabel('LBL_Weekly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topProducts('Monthly')">
												<?php echo Labels::getLabel('LBL_Monthly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topProducts('Yearly')">
												<?php echo Labels::getLabel('LBL_Yearly',$adminLangId); ?></a></li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
					<div class="box__body">
						<div class="scrollbar scrollbar-js">
							<ul class="list list--vertical theme--txtcolor theme--hovercolor topProducts">
								<?php
									$count = 1;
									if(count($dashboardInfo['topProducts'])>0){
										foreach($dashboardInfo['topProducts'] as $row){
											if($count>11){
												break;
											}?>
								<li>
									<?php echo $row['product_name'];?> <span>
										<?php echo $row['sold'];?>
										<?php echo Labels::getLabel('LBL_Sold',$adminLangId); ?></span></li>
								<?php $count++;
										}
									}
									else{
										echo Labels::getLabel("LBL_No_Record_Found",$adminLangId);
									}
									?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box box--white box--scroll">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Top_Search_Items',$adminLangId); ?>
						</h4>
						<ul class="actions right">
							<li class="droplink">
								<a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
								<div class="dropwrap">
									<ul class="linksvertical">
										<li><a href="javascript:void(0)" onClick="getTopSearchKeyword('today')">
												<?php echo Labels::getLabel('LBL_Today',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="getTopSearchKeyword('Weekly')">
												<?php echo Labels::getLabel('LBL_Weekly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="getTopSearchKeyword('Monthly')">
												<?php echo Labels::getLabel('LBL_Monthly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="getTopSearchKeyword('Yearly')">
												<?php echo Labels::getLabel('LBL_Yearly',$adminLangId); ?></a></li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
					<div class="box__body">
						<div class="scrollbar scrollbar-js">
							<ul class="list list--vertical theme--txtcolor theme--hovercolor topSearchKeyword">
								<?php
											 $count=1;
											if(count($dashboardInfo['topSearchKeyword'])>0){
											foreach($dashboardInfo['topSearchKeyword'] as $row){ if($count>11){ break;}?>
								<li>
									<?php echo ($row['search_item']=='')?Labels::getLabel('LBL_Blank_Search',$adminLangId):$row['search_item'];?> <span>
										<?php echo $row['search_count'];?></span></li>
								<?php $count++;}}else{ echo Labels::getLabel('LBL_No_Record_Found',$adminLangId);}  ?>
							</ul>
						</div>
					</div>
				</div>
			</div>

		</div>
		<div class="gap"></div>
		<ul class="caraousel carousel--oneforth carousel--oneforth-js">
			<li>
				<div class="carousel__item">
					<div class="box box--social box--social-fb">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/social_1.svg" alt="" class="box__icon">
						<h3>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['Facebook']['%age'])?$dashboardInfo['socialVisits']['rows']['Facebook']['%age']:0;?>%</h3>
						<p>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['Facebook']['visit'])?$dashboardInfo['socialVisits']['rows']['Facebook']['visit']:0;?>
							<?php echo Labels::getLabel('LBL_Visitors',$adminLangId); ?>
						</p>
					</div>
				</div>
			</li>
			<li>
				<div class="carousel__item">
					<div class="box box--social box--social-tw">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/social_4.svg" alt="" class="box__icon">
						<h3>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['LinkedIn']['%age'])?$dashboardInfo['socialVisits']['rows']['LinkedIn']['%age']:0;?>%</h3>
						<p>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['LinkedIn']['visit'])?$dashboardInfo['socialVisits']['rows']['LinkedIn']['visit']:0;?>
							<?php echo Labels::getLabel('LBL_Visitors',$adminLangId); ?>
						</p>
					</div>
				</div>
			</li>
			<li>
				<div class="carousel__item">
					<div class="box box--social box--social-li">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/social_2.svg" alt="" class="box__icon">
						<h3>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['Twitter']['%age'])?$dashboardInfo['socialVisits']['rows']['Twitter']['%age']:0;?>%</h3>
						<p>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['Twitter']['visit'])?$dashboardInfo['socialVisits']['rows']['Twitter']['visit']:0;?>
							<?php echo Labels::getLabel('LBL_Visitors',$adminLangId); ?>
						</p>
					</div>
				</div>
			</li>
			<li>
				<div class="carousel__item">
					<div class="box box--social box--social-gp">
						<img src="<?php echo CONF_WEBROOT_URL ?>images/social_3.svg" alt="" class="box__icon">
						<h3>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['Google+']['%age'])?$dashboardInfo['socialVisits']['rows']['Google+']['%age']:0;?>%</h3>
						<p>
							<?php echo isset($dashboardInfo['socialVisits']['rows']['Google+']['visit'])?$dashboardInfo['socialVisits']['rows']['Google+']['visit']:0;?>
							<?php echo Labels::getLabel('LBL_Visitors',$adminLangId); ?>
						</p>
					</div>
				</div>
		</ul>
		<div class="gap"></div>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box box--white box--scroll">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Top_Referrers',$adminLangId); ?>
						</h4>
						<ul class="actions right">
							<li class="droplink">
								<a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
								<div class="dropwrap">
									<ul class="linksvertical">
										<li><a href="javascript:void(0)" onClick="topReferers('today')">
												<?php echo Labels::getLabel('LBL_Today',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topReferers('Weekly')">
												<?php echo Labels::getLabel('LBL_Weekly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topReferers('Monthly')">
												<?php echo Labels::getLabel('LBL_Monthly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topReferers('Yearly')">
												<?php echo Labels::getLabel('LBL_Yearly',$adminLangId); ?></a></li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
					<div class="box__body">
						<div class="scrollbar scrollbar-js">
							<ul class="list list--vertical theme--txtcolor theme--hovercolor topReferers">

							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box box--white box--scroll">
					<div class="box__head">
						<h4>
							<?php echo Labels::getLabel('LBL_Top_Countries',$adminLangId); ?>
						</h4>
						<ul class="actions right">
							<li class="droplink">
								<a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
								<div class="dropwrap">
									<ul class="linksvertical">
										<li><a href="javascript:void(0)" onClick="topCountries('today')">
												<?php echo Labels::getLabel('LBL_Today',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topCountries('Weekly')">
												<?php echo Labels::getLabel('LBL_Weekly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topCountries('Monthly')">
												<?php echo Labels::getLabel('LBL_Monthly',$adminLangId); ?></a></li>
										<li><a href="javascript:void(0)" onClick="topCountries('Yearly')">
												<?php echo Labels::getLabel('LBL_Yearly',$adminLangId); ?></a></li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
					<div class="box__body">
						<div class="scrollbar scrollbar-js">
							<ul class="list list--vertical theme--txtcolor theme--hovercolor topCountries ">
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="section box--white">
					<div class="sectionhead">
						<?php if($objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true)){?>
						<a class="themebtn btn-default btn-sm" target='_new' href="<?php echo CommonHelper::generateUrl('Orders'); ?>">
							<?php echo Labels::getLabel('LBL_View_All',$adminLangId); ?></a>
						<?php }?>
						<h4>
							<?php echo Labels::getLabel('LBL_Latest_Orders',$adminLangId); ?>
						</h4>
					</div>
					<div class="sectionbody" id="latest-orders-js">

					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="tabs_nav_container responsive boxbased">
					<ul class="tabs_nav">
						<li><a class="active" rel="tabs_01" href="javascript:void(0)" onClick="searchStatistics('statistics',this)"><i class="icon ion-arrow-graph-up-right"></i>
								<?php echo Labels::getLabel("LBL_Statistics", $adminLangId); ?></a></li>
						<li><a rel="tabs_02" href="javascript:void(0)" onClick="searchStatistics('sellerproducts',this)"><i class="icon ion-bag"></i>
								<?php echo Labels::getLabel("LBL_Seller_Products", $adminLangId); ?></a></li>
						<li><a rel="tabs_03" href="javascript:void(0)" onClick="searchStatistics('shops',this)"><i class="icon ion-ios-cart"></i>
								<?php echo Labels::getLabel("LBL_Shops", $adminLangId); ?></a></li>
						<li><a rel="tabs_04" href="javascript:void(0)" onClick="searchStatistics('signups',this)"><i class="icon ion-android-person"></i>
								<?php echo Labels::getLabel("LBL_Buyer/Seller_Signups", $adminLangId); ?></a></li>
						<li><a rel="tabs_05" href="javascript:void(0)" onClick="searchStatistics('advertisers',this)"><i class="icon ion-android-person"></i>
								<?php echo Labels::getLabel("LBL_Advertiser_Signups", $adminLangId); ?></a></li>
						<li><a rel="tabs_06" href="javascript:void(0)" onClick="searchStatistics('affiliates',this)"><i class="icon ion-android-contact"></i>
								<?php echo Labels::getLabel("LBL_Affiliate_Signups", $adminLangId); ?></a></li>
					</ul>
					<div class="tabs_panel_wrap nopadding">
						<span class="-gap"></span>
						<!-- Statistics [ -->
						<span class="togglehead active" rel="tabs_01">
							<?php echo Labels::getLabel("LBL_Statistics", $adminLangId); ?></span>
						<div id="tabs_01" class="tabs_panel data_panel"></div>
						<!-- ] -->
						<!-- Seller Products[ -->
						<span class="togglehead" rel="tabs_02">
							<?php echo Labels::getLabel("LBL_Seller_Products", $adminLangId); ?></span>
						<div id="tabs_02" class="tabs_panel"></div>
						<!-- ] -->
						<!--Shops List[ -->
						<span class="togglehead" rel="tabs_03">
							<?php echo Labels::getLabel("LBL_Shops", $adminLangId); ?></span>
						<div id="tabs_03" class="tabs_panel"></div>
						<!--] -->
						<!-- Buyer/Seller List[ -->
						<span class="togglehead" rel="tabs_04">
							<?php echo Labels::getLabel("LBL_Buyer/Seller_SignUps", $adminLangId); ?></span>
						<div id="tabs_04" class="tabs_panel"></div>
						<!-- ] -->
						<!-- Advertisers List[ -->
						<span class="togglehead" rel="tabs_05">
							<?php echo Labels::getLabel("LBL_Advertisers", $adminLangId); ?></span>
						<div id="tabs_05" class="tabs_panel"></div>
						<!-- ] -->
						<!-- Affiliates List[ -->
						<span class="togglehead" rel="tabs_06">
							<?php echo Labels::getLabel("LBL_Affiliates", $adminLangId); ?></span>
						<div id="tabs_06" class="tabs_panel"></div>
						<!-- ] -->
					</div>
				</div>
			</div>
		</div>
		<div class="gap"></div>
	</div>
</div>
<?php }else{?>
<div class="page">
	<div class="container container-fluid">
		<div class="row"></div>
	</div>
</div>
<?php }?>
<script type="text/javascript">
	var dataCurrency = '<?php echo CommonHelper::getCurrencySymbol(true); ?>';
	var w = $('.tabs_panel_wrap').width();
	google.load('visualization', '1', {
		'packages': ['corechart', 'bar']
	});
	//set callback
	google.setOnLoadCallback(createChart);

	//callback function
	function createChart() {

		<?php /* if($configuredAnalytics){ */?>

		// Conversions Statistics
		var dataConversion = google.visualization.arrayToDataTable([<?php echo html_entity_decode($dashboardInfo['conversionChatData'],ENT_QUOTES,'UTF-8');?>]);
		var optionConversion = {
			width: $('#conversionStats').width(),
			height: 240,
			'color': '#AEC785',
			legend: {
				position: "none"
			},
			<?php if($layoutDirection=='rtl'){?>
			hAxis: {

				direction: '-1'
			},
			series: {
				0: {
					targetAxisIndex: 1,
				}
			},
			<?php

		} ?>

		};
		var conversion = new google.visualization.ColumnChart(document.getElementById('conversionStats'));
		<?php /* } */?>

		<?php /* if($configuredAnalytics){ */?>

		conversion.draw(dataConversion, optionConversion);
		<?php /* } */ ?>
	}

</script>
