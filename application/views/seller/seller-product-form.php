<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<script type="text/javascript">
    var product_id = <?php echo $product_id ;?>;
    var selprod_id = <?php echo $selprod_id ;?>;
</script>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Inventory_Setup', $siteLangId); ?></h2>
            </div>
            <div class="col-md-auto">
                <div class="action">
                    <div class="">
                        <a href="<?php echo CommonHelper::generateUrl('seller', 'products');?>" class="btn btn--primary btn--sm "><?php echo Labels::getLabel('LBL_Back_To_My_Inventory', $siteLangId)?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div id="listing"> </div>
        </div>
    </div>
</main>
