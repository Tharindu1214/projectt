<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Products_Performance_Report', $siteLangId);?></h2>
            </div>
            <div class="col-auto">
            <div class="content-header-right"> <a href="javascript:void(0)" id="performanceReportExport" onclick="exportProdPerformanceReport('DESC')" class="btn btn--secondary btn--sm btn--block"><?php echo Labels::getLabel('LBL_Export', $siteLangId);?></a></div>
            </div>
        </div>
        <div class="content-body">           
           
            <div class="gap"></div>
            <div class="tabs tabs--small tabs--scroll setactive-js">
                                    <ul>
                                        <li class="is-active"><a href="javascript:void(0);" onClick="topPerformingProducts()"><?php echo Labels::getLabel('LBL_Top_Performing_Products', $siteLangId);?></a></li>
                                        <li><a href="javascript:void(0);" onClick="badPerformingProducts()"><?php echo Labels::getLabel('LBL_Most_Refunded_Products_Report', $siteLangId);?></a></li>
                                        <li><a href="javascript:void(0);" onClick="mostWishListAddedProducts()"><?php echo Labels::getLabel('LBL_Most_WishList_Added_Products', $siteLangId);?></a></li>
                                    </ul>
                                </div>
                                
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">                      
                       <div class="cards-content pl-4 pr-4 ">
                       <div id="listingDiv"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                       </div>
                    </div>
                </div>
            </div>
            
            
        </div>
    </div>
</main>
