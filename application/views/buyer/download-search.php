<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmSrch->setFormTagAttribute('onSubmit', 'searchBuyerDownloads(this); return false;');
$frmSrch->setFormTagAttribute('class', 'form');
$frmSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmSrch->developerTags['fld_default_col'] = 12;

$keyFld = $frmSrch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-6');
$keyFld->developerTags['col'] = 6;
$keyFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-3');
$submitBtnFld->developerTags['col'] = 3;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$clearFld = $frmSrch->getField('btn_clear');
$clearFld->setFieldTagAttribute('onclick', 'clearSearch(0)');
$clearFld->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
$clearFld->setWrapperAttribute('class', 'col-lg-3');
$clearFld->developerTags['col'] = 3;
$clearFld->developerTags['noCaptionTag'] = true;
?>
<div class="replaced">
    <div class="row">
        <div class="col-lg-8">
            <?php echo $frmSrch->getFormHtml(); ?>
            <?php echo $frmSrch->getExternalJS();?>
        </div>
    </div>
</div>
<span class="gap"></span>
<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'op_invoice_number'    =>    Labels::getLabel('LBL_Invoice', $siteLangId),
    'afile_name'    =>    Labels::getLabel('LBL_File', $siteLangId),
    'downloadable_count'        =>    Labels::getLabel('LBL_Download_times', $siteLangId),
    'afile_downloaded_times'        =>    Labels::getLabel('LBL_Downloaded_Count', $siteLangId),
    'expiry_date'    =>    Labels::getLabel('LBL_Expired_on', $siteLangId),
    'action'    =>    Labels::getLabel('LBL_Action', $siteLangId),
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
$canCancelOrder = true;
$canReturnRefund = true;
foreach ($digitalDownloads as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( 'class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'afile_name':
                if ($row['downloadable']) {
                    $fileName = '<a href="'.CommonHelper::generateUrl('Buyer', 'downloadDigitalFile', array($row['afile_id'], $row['afile_record_id'])).'">'.$row['afile_name'].'</a>';
                } else {
                    $fileName = $row['afile_name'];
                }
                $td->appendElement('plaintext', array(), $fileName, true);
                break;
            case 'downloadable_count':
                $downloadableCount = Labels::getLabel('LBL_N/A', $siteLangId) ;
                if ($row['downloadable_count'] != -1) {
                    $downloadableCount = $row['downloadable_count'];
                }
                $td->appendElement('plaintext', array(), $downloadableCount, true);
                break;
            case 'expiry_date':
                $expiry = Labels::getLabel('LBL_N/A', $siteLangId) ;
                if ($row['expiry_date']!='') {
                    $expiry = FatDate::Format($row['expiry_date']);
                }
                $td->appendElement('plaintext', array(), $expiry, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);
                if ($row['downloadable']) {
                    $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href'=> CommonHelper::generateUrl('Buyer', 'downloadDigitalFile', array($row['afile_id'], $row['afile_record_id'])), 'class'=>'', 'title'=>Labels::getLabel('LBL_View_Order', $siteLangId)), '<i class="fa fa-download"></i>', true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($digitalDownloads) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array ('name' => 'frmSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
