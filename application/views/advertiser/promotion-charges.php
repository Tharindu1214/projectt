<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/advertiser/advertiserDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header justify-content-between row mb-4">
            <div class="content-header-left col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Promotion_Charges', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="cards">
                        <!-- <div class="cards-header p-4">
                            <h5 class="cards-title "><?php echo Labels::getLabel('LBL_Promotion_Charges', $siteLangId);?></h5>
                        </div> -->
                        <div class="cards-content pt-2 pl-4 pr-4 ">
                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
