<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section bg-gray-dark">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-8 mb-4 mb-md-0 checkout-content-js">

            </div>
            <div class="col-lg-4 ">
                <div class="summary-listing"></div>
                <?php echo FatUtility::decodeHtmlEntities(nl2br($pageData['epage_content']));?>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
$("document").ready(function(){
    $(document).on("click",".toggle--collapseable-js",function(e){
        var prodgroup_id = $(this).attr('data-prodgroup_id');
        $(this).toggleClass("is--active");
        $("#prodgroup_id_" + prodgroup_id ).slideToggle();
    });
});
</script>
