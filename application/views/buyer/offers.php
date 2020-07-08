<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php $this->includeTemplate('_partial/buyerDashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header justify-content-between row mb-4">
            <div class="content-header-left col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Offers', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <!-- <div class="cards-header p-4">
                    <h5 class="cards-title"><?php echo Labels::getLabel('LBL_My_Offers', $siteLangId); ?></h5>
                </div> -->
                <div class="cards-content p-4">
                    <div id="listing" class="row"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS', $siteLangId); ?>';
</script>
