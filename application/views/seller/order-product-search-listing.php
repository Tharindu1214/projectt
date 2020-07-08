<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'order_id'  =>    Labels::getLabel('LBL_Order_Id_Date', $siteLangId),
    'product'   =>    Labels::getLabel('LBL_Ordered_Product', $siteLangId),
    'op_qty'    =>    Labels::getLabel('LBL_Qty', $siteLangId),
    'total'     =>    Labels::getLabel('LBL_Total', $siteLangId),
    'status'    =>    Labels::getLabel('LBL_Status', $siteLangId),
    'action'    =>    Labels::getLabel('LBL_Action', $siteLangId),
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}
$sr_no = 0;
$orderObj = new Orders();
$processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

foreach ($orders as $sn => $order) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' =>'' ));
    $orderDetailUrl = CommonHelper::generateUrl('seller', 'viewOrder', array($order['op_id']));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'order_id':
                $txt = '<a title="'.Labels::getLabel('LBL_View_Order_Detail', $siteLangId).'" href="'.$orderDetailUrl.'">';
                $txt .= $order['op_invoice_number'];
                $txt .= '</a><br/>'. FatDate::format($order['order_date_added']);
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'product':
                $txt = '<div class="item__description">';
                if ($order['op_selprod_title'] != '') {
                    $txt .= '<div class="item__title">'.$order['op_selprod_title'].'</div>';
                }
                $txt .= '<div class="item__sub_title">'.$order['op_product_name'].'</div>';

                $txt .= '<div class="item__brand">'.Labels::getLabel('LBL_Brand', $siteLangId).': '.$order['op_brand_name'];
                if ($order['op_selprod_options'] != '') {
                    $txt .= ' | ' . $order['op_selprod_options'];
                }
                $txt .= '</div>';
                 $txt .= '</div>';
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'total':
                $txt = '';
                // $txt .= CommonHelper::displayMoneyFormat($order['order_net_amount']);
                $txt .= CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount', false, USER::USER_TYPE_SELLER));
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'status':
                $txt = $order['orderstatus_name'];
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=> $orderDetailUrl, 'class'=>'',
                    'title'=>Labels::getLabel('LBL_View_Order', $siteLangId)),
                    '<i class="fa fa-eye"></i>',
                    true
                );

                if (in_array($order['orderstatus_id'], $processingStatuses)) {
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array('href'=> CommonHelper::generateUrl('seller', 'cancelOrder', array($order['op_id'])), 'class'=>'',
                        'title'=>Labels::getLabel('LBL_Cancel_Order', $siteLangId)),
                        '<i class="fa fa-close"></i>',
                        true
                    );
                }

                break;
            default:
                $td->appendElement('plaintext', array(), ''.$order[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($orders) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOrderSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToOrderSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
