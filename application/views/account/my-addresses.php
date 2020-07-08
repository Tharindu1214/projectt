<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header  row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Manage_Addresses', $siteLangId);?></h2>
            </div>
            <div class="col-auto">
                <div class="content-header-right">
                 <a href="javascript:void(0);" onClick="addAddressForm(0)" class="btn btn--secondary btn--sm"><?php echo Labels::getLabel('LBL_Add_new_address', $siteLangId);?> </a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <div class="cards-content p-4">
                    <div id="listing"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                </div>
            </div>
        </div>
    </div>
</main>
