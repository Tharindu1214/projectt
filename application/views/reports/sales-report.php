<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frmSrch->setFormTagAttribute('onSubmit', 'searchSalesReport(this); return false;');
    $frmSrch->setFormTagAttribute('class', 'form');
    $frmSrch->developerTags['colClassPrefix'] = 'col-lg-2 col-md-';
    $frmSrch->developerTags['fld_default_col'] = 2;

    $submitBtnFld = $frmSrch->getField('date_from');
    $submitBtnFld->developerTags['noCaptionTag'] = true;

    $submitBtnFld = $frmSrch->getField('date_to');
    $submitBtnFld->developerTags['noCaptionTag'] = true;

    $submitBtnFld = $frmSrch->getField('btn_submit');
    $submitBtnFld->setFieldTagAttribute('class', 'btn--block');
    $submitBtnFld->developerTags['noCaptionTag'] = true;

    $cancelBtnFld = $frmSrch->getField('btn_clear');
    $cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
    $cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-header p-4">
                            <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId);?></h5>
                            <div class="action"><?php echo '<a href="javascript:void(0)" onClick="exportSalesReport()" class="btn btn--secondary btn--block btn--sm">'.Labels::getLabel('LBL_Export', $siteLangId).'</a>'; ?></div>
                        </div>
                        <div class="cards-content pl-4 pr-4 pb-0">
                                <?php if (empty($orderDate)) { ?>
                                <div class="replaced">
                                    <?php
                                    $submitFld = $frmSrch->getField('btn_submit');
                                    $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');

                                    $fldClear= $frmSrch->getField('btn_clear');
                                    $fldClear->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
                                    echo $frmSrch->getFormHtml();
                                    ?>
                                </div>
                                <?php  } else {
                                        echo  $frmSrch->getFormHtml();
                                } ?>


                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-content pt-2 pl-4 pr-4">
                            <div id="listingDiv"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
