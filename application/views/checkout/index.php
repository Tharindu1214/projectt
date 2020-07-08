<?php 
defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section bg-gray-dark">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-8 mb-4 mb-md-0 checkout-content-js">

            </div>
            <div class="col-lg-4 ">
                <div class="summary-listing"></div>
                <?php echo FatUtility::decodeHtmlEntities($pageData['epage_content']);?>
            </div>
        </div>
    </div>
</section>
<input id="hasAddress" class="d-none" value = "<?php echo (empty($addresses) || count($addresses) == 0) ? 0 : 1?>">
<script type="text/javascript">
    <?php if (isset($defaultAddress)) { ?>
    $defaultAddress = 1;
    <?php } else { ?>
    $defaultAddress = 0;
    <?php } ?>
</script>
<script type="text/javascript">
    $("document").ready(function() {
        <?php if (empty($addresses) || count($addresses) == 0){ ?>
            showAddressFormDiv();
        <?php } else {?>
            loadShippingSummaryDiv();
        <?php } ?>

        $(document).on("click", ".toggle--collapseable-js", function(e) {
            var prodgroup_id = $(this).attr('data-prodgroup_id');
            $(this).toggleClass("is--active");
            $("#prodgroup_id_" + prodgroup_id).slideToggle();
        });
    });
</script>
