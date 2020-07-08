<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'orrequest_id'    =>    Labels::getLabel('LBL_ID', $siteLangId),
    'orrequest_date'    =>    Labels::getLabel('LBL_Date', $siteLangId),
    'op_invoice_number'        =>    Labels::getLabel('LBL_Order_Id/Invoice_Number', $siteLangId),
    'products'            => Labels::getLabel('LBL_Products', $siteLangId),
    /* 'orrequest_type'        =>    Labels::getLabel( 'LBL_Request_Type', $siteLangId ), */
    'orrequest_qty'        =>    Labels::getLabel('LBL_Return_Qty', $siteLangId),
    'orrequest_status'    =>    Labels::getLabel('LBL_Status', $siteLangId),
    'action'            =>    Labels::getLabel('LBL_Action', $siteLangId),
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($requests as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' =>'' ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'orrequest_id':
                /* $requestId = CommonHelper::formatOrderReturnRequestNumber($row[$key]); */
                $td->appendElement('plaintext', array(), $row['orrequest_reference'], true);
                break;
            case 'orrequest_date':
                $td->appendElement('plaintext', array(), FatDate::format($row[$key]), true);
                break;
            case 'orrequest_type':
                $td->appendElement('plaintext', array(), $returnRequestTypeArr[$row[$key]], true);
                break;
            case 'products':
                $txt = '<div class="item__description">';
                if ($row['op_selprod_title'] != '') {
                    $txt .= '<div class="item__title">'.$row['op_selprod_title'].'</div>';
                }
                $txt .= '<div class="item__sub_title">'.$row['op_product_name'].'</div>';
                $txt .= '<div class="item__brand">'.Labels::getLabel('LBL_Brand', $siteLangId).': '.$row['op_brand_name'];
                if ($row['op_selprod_options'] != '') {
                    $txt .= ' | ' . $row['op_selprod_options'];
                }
                $txt .='</div>';
                if ($row['op_selprod_sku'] != '') {
                    $txt .= '<div class="item__sku">'.Labels::getLabel('LBL_SKU', $siteLangId).':  ' . $row['op_selprod_sku'].'</div>';
                }
                if ($row['op_product_model'] != '') {
                    $txt .= '<div class="item__model">'.Labels::getLabel('LBL_Model', $siteLangId).':  ' . $row['op_product_model'].'</div>';
                }
                $txt .= '</div>';
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'orrequest_status':
                $td->appendElement('plaintext', array(), $OrderReturnRequestStatusArr[$row[$key]], true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);

                if ($buyerPage) {
                    $url = CommonHelper::generateUrl('Buyer', 'ViewOrderReturnRequest', array($row['orrequest_id']));
                }
                if ($sellerPage) {
                    $url = CommonHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($row['orrequest_id']));
                }
                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=> $url, 'class'=>'',
                'title'=>Labels::getLabel('LBL_View_Return_Order_Request', $siteLangId)),
                    '<i class="fa fa-eye"></i>',
                    true
                );
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($requests) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOrderReturnRequestSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToOrderReturnRequestSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
