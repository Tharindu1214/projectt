<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header  row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Shop_Details', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body" id="shopFormBlock">
            <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
        </div>
    </div>
</main>
<script>
    $(document).ready(function() {
        <?php if ($tab==USER::RETURN_ADDRESS_ACCOUNT_TAB && !$subTab) {?>
        returnAddressForm();
        <?php } elseif ($subTab) {?>
        returnAddressLangForm(<?php echo $subTab;?>);
        <?php } else { ?>
        shopForm();
        <?php } ?>
    });
</script>
