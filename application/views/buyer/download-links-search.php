<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmSrch->setFormTagAttribute('onSubmit', 'searchBuyerDownloadLinks(this); return false;');
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
$clearFld->setFieldTagAttribute('onclick', 'clearSearch(1)');
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
    'opddl_downloadable_link'    =>    Labels::getLabel('LBL_Link', $siteLangId),
    'downloadable_count'        =>    Labels::getLabel('LBL_Download_times', $siteLangId),
    'opddl_downloaded_times'        =>    Labels::getLabel('LBL_Downloaded_Count', $siteLangId),
    'expiry_date'    =>    Labels::getLabel('LBL_Expired_on', $siteLangId),
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
$canCancelOrder = true;
$canReturnRefund = true;
foreach ($digitalDownloadLinks as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( 'class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'opddl_downloadable_link':
                if ($row['downloadable'] != 1) {
                    $td->appendElement('plaintext', array(), Labels::getLabel('LBL_N/A', $siteLangId), true);
                } else {
                    $linkOnClick = ($row['downloadable']!=1) ? '' : 'return increaseDownloadedCount('.$row['opddl_link_id'].','.$row['op_id'].')';
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);
                    $li = $ul->appendElement("li");

                    $li->appendElement('a', array('href' => $row['opddl_downloadable_link'], 'target' => '_blank', 'onClick' => $linkOnClick, 'class'=>'', 'title'=>Labels::getLabel('LBL_Click_to_download', $siteLangId)), '<i class="fa fa-download"></i>', true);

                    // $li->appendElement('a', array('href' => $row['opddl_downloadable_link'], 'class'=>'', 'title'=>Labels::getLabel('LBL_Click_to_open', $siteLangId)), '<i class="fa fa-download"></i>', true);

                    /* $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href'=> 'javascript:void(0)', 'id'=>'dataLink', 'data-link'=>$row['opddl_downloadable_link'], 'onclick'=>'copyToClipboard(this)',
                    'title'=>Labels::getLabel('LBL_copy_to_clipboard',$siteLangId)),
                    '<i class="fa fa-copy"></i>', true); */
                }
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
                if ($row['expiry_date'] != '') {
                    $expiry = FatDate::Format($row['expiry_date']);
                }
                $td->appendElement('plaintext', array(), $expiry, true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($digitalDownloadLinks) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array ('name' => 'frmSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToLinksSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);

?>
<script>
    function increaseDownloadedCount( linkId, opId ){
        fcom.ajax(fcom.makeUrl('buyer', 'downloadDigitalProductFromLink', [linkId,opId]), '', function(t) {
            var ans = $.parseJSON(t);
            if( ans.status == 0 ){
                $.systemMessage( ans.msg, 'alert--danger');
                return false;
            }
            /* var dataLink = $(this).attr('data-link');
            window.location.href= dataLink; */
            location.reload();
            return true;
        });
    }

    function copyToClipboard(element) {
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val($('#dataLink').attr("data-link")).select();
      document.execCommand("copy");
      $temp.remove();
      alert('<?php echo Labels::getLabel('LBL_Your_link_is_copied_to_clipboard', $siteLangId); ?>');
    }
</script>
