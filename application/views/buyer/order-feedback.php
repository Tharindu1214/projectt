<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('Buyer', 'setupOrderFeedback'));
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 8;
?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Order_Feedback', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <div class="cards-header p-4">
                    <h5 class="cards-title">
                        <?php echo Labels::getLabel('LBL_Product', $siteLangId),' : ',(!empty($opDetail['op_selprod_title']) ? $opDetail['op_selprod_title'] : $opDetail['op_product_name']) ,' | ', Labels::getLabel('LBL_Shop', $siteLangId),' : ', $opDetail['op_shop_name'] ; ?>
                    </h5>
                </div>
                <div class="cards-content pl-4 pr-4 ">
                    <?php echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</main>
<script type="text/javascript">
    $(document).ready(function() {
        $('.star-rating').barrating({
            showSelectedRating: false
        });
    });
</script>
