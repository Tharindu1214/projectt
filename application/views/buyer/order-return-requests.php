<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$frmOrderReturnRequestsSrch->setFormTagAttribute('onSubmit', 'searchOrderReturnRequests(this); return false;');
$frmOrderReturnRequestsSrch->setFormTagAttribute('class', 'form');
$frmOrderReturnRequestsSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmOrderReturnRequestsSrch->developerTags['fld_default_col'] = 12;

$keywordFld = $frmOrderReturnRequestsSrch->getField('keyword');
$keywordFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
// $keywordFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Search_in_Order_Id/Invoice_number,_Product_Name,_Brand_Name,_SKU,_Model,_,Request_reference_number', $siteLangId).'</small>';
$keywordFld->setWrapperAttribute('class', 'col-lg-4');
$keywordFld->developerTags['col'] = 4;
$keywordFld->developerTags['noCaptionTag'] = true;

$statusFld = $frmOrderReturnRequestsSrch->getField('orrequest_status');
$statusFld->setWrapperAttribute('class', 'col-lg-4');
$statusFld->developerTags['col'] = 4;
$statusFld->developerTags['noCaptionTag'] = true;

$orrequestDateFromFld = $frmOrderReturnRequestsSrch->getField('orrequest_date_from');
$orrequestDateFromFld->setFieldTagAttribute('class', 'field--calender');
$orrequestDateFromFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Date_From', $siteLangId));
$orrequestDateFromFld->setWrapperAttribute('class', 'col-lg-2');
$orrequestDateFromFld->developerTags['col'] = 2;
$orrequestDateFromFld->developerTags['noCaptionTag'] = true;

$orrequestDateToFld = $frmOrderReturnRequestsSrch->getField('orrequest_date_to');
$orrequestDateToFld->setFieldTagAttribute('class', 'field--calender');
$orrequestDateToFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Date_to', $siteLangId));
$orrequestDateToFld->setWrapperAttribute('class', 'col-lg-2');
$orrequestDateToFld->developerTags['col'] = 2;
$orrequestDateToFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmOrderReturnRequestsSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmOrderReturnRequestsSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
    <div class="content-header row justify-content-between mb-3">
        <div class="col-md-auto">
            <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
            <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Return_Requests', $siteLangId);?></h2>
        </div>
    </div>
    <div class="content-body">
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="cards">

                    <div class="cards-content p-4">
                        <div class="replaced">
                            <?php
                            $submitFld = $frmOrderReturnRequestsSrch->getField('btn_submit');
                            $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');

                            $fldClear= $frmOrderReturnRequestsSrch->getField('btn_clear');
                            $fldClear->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
                            echo $frmOrderReturnRequestsSrch->getFormHtml();
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="cards">

                    <div class="cards-content pt-2 pl-4 pr-4 ">
                        <div id="returnOrderRequestsListing"></div>
                        <span class="gap"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</main>
