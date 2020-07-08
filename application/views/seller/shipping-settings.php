<?php 
defined('SYSTEM_INIT') or die('Invalid Usage');
$frmShippingSettings->setFormTagAttribute('onSubmit', 'searchOrderReturnRequests(this); return false;');
$frmShippingSettings->setFormTagAttribute('class', 'form');
$frmShippingSettings->developerTags['colClassPrefix'] = 'col-md-';
$frmShippingSettings->developerTags['fld_default_col'] = 12;

$cityFld = $frmShippingSettings->getField('city_list');
$cityFld->setWrapperAttribute('class', 'col-lg-4');
$cityFld->developerTags['col'] = 4;
$cityFld->developerTags['noCaptionTag'] = true;

$shcmpnFld = $frmShippingSettings->getField('shipping_company');
$shcmpnFld->setWrapperAttribute('class', 'col-lg-4');
$shcmpnFld->developerTags['col'] = 4;
$shcmpnFld->developerTags['noCaptionTag'] = true;

$shcmpnFld = $frmShippingSettings->getField('businessdays');
$shcmpnFld->setWrapperAttribute('class', 'col-lg-4');
$shcmpnFld->developerTags['col'] = 4;
$shcmpnFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmShippingSettings->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmShippingSettings->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>


<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
    <div class="content-header  row justify-content-between mb-3">
        <div class="col-md-auto">
            <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
            <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Shipping_Settings', $siteLangId); ?></h2>
        </div>
        <div class="row col-md-<?php echo ($recordCount == 0) ? 5 : 4; ?>">
            <div class="col-md-<?php echo ($recordCount == 0) ? 5 : 6; ?>">
                <button class="btn--block btn btn--primary" onclick="optionForm(21)">Import</button>
            </div>
            <div class="col-md-<?php echo ($recordCount == 0) ? 7 : 6; ?>">
                <a href="shipping-setting-export"><button class="btn--block btn btn--primary-border">Export <?php echo ($recordCount == 0) ? 'Sample Data' : ''; ?></button></a>
            </div>
            <?php if($recordCount == 0){ ?>
            <small class="text--small" style="margin-top:10px;padding-left:15px;">You have no Shipping Data. Export Sample data</small>
            <?php }else{ ?>
            <small class="text--small" style="margin-top:10px;padding-left:15px;">It will delete all existing record and replace with new record.</small>
            <?php } ?>
        </div>
    </div>
    <div class="content-body">
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="cards">
                    <div class="cards-content pt-4 pl-4 pr-4 pb-0">
                        <div class="replaced">
                            <?php
                           $submitFld = $frmShippingSettings->getField('btn_submit');
                            $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');

                            $fldClear= $frmShippingSettings->getField('btn_clear');
                            $fldClear->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
                            echo $frmShippingSettings->getFormHtml();
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="cards">
                    <div class="cards-content pt-2 pl-4 pr-4">
                        <div id="shippingSettingsListing">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</main>
