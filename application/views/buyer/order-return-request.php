<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmOrderReturnRequest->setFormTagAttribute('class', 'form form--horizontal');
$frmOrderReturnRequest->setFormTagAttribute('onsubmit', 'setupOrderReturnRequest(this); return(false);');
$frmOrderReturnRequest->developerTags['colClassPrefix'] = 'col-md-';
$frmOrderReturnRequest->developerTags['fld_default_col'] = 6;

$orRequestTypeFld = $frmOrderReturnRequest->getField('orrequest_type');
$orRequestTypeFld->setOptionListTagAttribute('class', 'list-inline');
?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Order_Return/Refund/Replace_Request', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">

                <div class="cards-content pt-4 pl-4 pr-4 pb-0">
                    <?php echo $frmOrderReturnRequest->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</main>
