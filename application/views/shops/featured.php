<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div id="body" class="body">
    <div class="bg--second pt-3 pb-3">
        <div class="container">
            <div class="section-head section--white--head justify-content-center mb-0">
                <div class="section__heading">
                    <h2><?php echo Labels::getLabel('Lbl_Featured_Shops', $siteLangId); ?></h2>
                </div>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="container">
            <div id="listing"> </div>
            <div id="loadMoreBtnDiv"></div>
        </div>
    </section>
</div>
<?php echo $searchForm->getFormHtml();?>
