<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial'=>'Sr.',
    'product_identifier' => Labels::getLabel('LBL_Product', $siteLangId),
    'preq_added_on' => Labels::getLabel('LBL_Added_on', $siteLangId),
    'preq_status' => Labels::getLabel('LBL_Status', $siteLangId),
    'action' => Labels::getLabel('LBL_Action', $siteLangId)
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'product_identifier':
                $td->appendElement('plaintext', array(), $row['product_name'] . '<br>', true);
                $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                break;
            case 'preq_status':
                $td->appendElement('plaintext', array(), $statusArr[$row[$key]], true);
                break;
            case 'preq_added_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array('class'=>'actions'), '', true);
                $li = $ul->appendElement("li");
                if ($row['preq_status'] == ProductRequest::STATUS_PENDING) {
                    $li->appendElement(
                        'a',
                        array('href'=>CommonHelper::generateUrl('Seller', 'customCatalogProductForm', array($row['preq_id'])), 'class'=>'','title'=>Labels::getLabel('LBL_Edit', $siteLangId)),
                        '<i class="fa fa-edit"></i>',
                        true
                    );

                    $li = $ul->appendElement("li");
                    $li->appendElement("a", array('title' => Labels::getLabel('LBL_Product_Images', $siteLangId), 'onclick' => 'customCatalogProductImages('.$row['preq_id'].')', 'href'=>'javascript:void(0)'), '<i class="fa fa-picture-o"></i>', true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmCatalogProductSearchPaging'));

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'callBackJsFunc' => 'goToCustomCatalogProductSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
