<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'order_id'    =>    Labels::getLabel('LBL_Order_ID_Date', $siteLangId),
    'product'    =>    Labels::getLabel('LBL_Details', $siteLangId),
    'total'        =>    Labels::getLabel('LBL_Total', $siteLangId),
    'status'    =>    Labels::getLabel('LBL_Status', $siteLangId),
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
foreach ($orders as $sn => $order) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( 'class' => '' ));
    $orderDetailUrl = CommonHelper::generateUrl('Buyer', 'viewOrder', array($order['order_id'],$order['op_id']));

    if ($order['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
        $canCancelOrder = (in_array($order["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses(true)));
        $canReturnRefund = (in_array($order["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses(true)));
    } else {
        $canCancelOrder = (in_array($order["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses()));
        $canReturnRefund = (in_array($order["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses()));
    }
    $isValidForReview = false;
    if (in_array($order["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
        $isValidForReview = true;
    }
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'order_id':
                $txt = '<a title="'.Labels::getLabel('LBL_View_Order_Detail', $siteLangId).'" href="'.$orderDetailUrl.'">';
                if ($order['totOrders'] > 1) {
                    $txt .= $order['op_invoice_number'];
                } else {
                    $txt .= $order['order_id'];
                }
                $txt .= '</a><br/>'. FatDate::format($order['order_date_added']);
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'product':
                $txt = '<div class="item__description">';
                if ($order['op_selprod_title'] != '') {
                    $txt .= '<div class="item__title">'.$order['op_selprod_title'].'</div>';
                }
                $txt .= '<div class="item__sub_title">'.$order['op_product_name'].' ('.Labels::getLabel('LBL_Qty', $siteLangId).': '.$order['op_qty'].')</div>';
                $txt .= '<div class="item__brand">'.Labels::getLabel('LBL_Brand', $siteLangId).': '.$order['op_brand_name'];
                if ($order['op_selprod_options'] != '') {
                    $txt .= ' | ' . $order['op_selprod_options'];
                }
                $txt .='</div>';
                if ($order['totOrders'] > 1) {
                    $txt .= '<div class="item__specification">'.Labels::getLabel('LBL_Part_combined_order', $siteLangId).' <a title="'.Labels::getLabel('LBL_View_Order_Detail', $siteLangId).'" href="'.CommonHelper::generateUrl('Buyer', 'viewOrder', array($order['order_id'])).'">'.$order['order_id'].'</div>';
                }
                $txt .= '</div>';
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'total':
                $txt = '';
                /* if( $order['totOrders'] == 1 ){
                    $txt .= CommonHelper::displayMoneyFormat($order['order_net_amount'], true, true);
                } else {
                    $txt .= '-';
                } */
                // var_dump($order['totOrders']);
                // CommonHelper::displayMoneyFormat($order['order_net_amount'], true, true);
                $txt .= CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order));
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'status':
                $pMethod ='';
                if ($order['order_pmethod_id']==PaymentSettings::CASH_ON_DELIVERY && $order['order_status']==FatApp::getConfig('CONF_DEFAULT_ORDER_STATUS')) {
                    $pMethod = " - ".$order['pmethod_name'] ;
                }
                $txt = $order['orderstatus_name'].$pMethod;
                $td->appendElement('plaintext', array(), $txt, true);
                break;


            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);

                $opCancelUrl = CommonHelper::generateUrl('Buyer', 'orderCancellationRequest', array($order['op_id']));

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=> $orderDetailUrl, 'class'=>'',
                    'title'=>Labels::getLabel('LBL_View_Order', $siteLangId)),
                    '<i class="fa fa-eye"></i>',
                    true
                );

                if ($canCancelOrder && false === OrderCancelRequest::getCancelRequestById($order['op_id'])) {
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array('href'=> $opCancelUrl, 'class'=>'',
                        'title'=>Labels::getLabel('LBL_Cancel_Order', $siteLangId)),
                        '<i class="fa fa-close"></i>',
                        true
                    );
                }
                $canSubmitFeedback = Orders::canSubmitFeedback($order['order_user_id'], $order['order_id'], $order['op_selprod_id']);
                if ($canSubmitFeedback && $isValidForReview) {
                    $opFeedBackUrl = CommonHelper::generateUrl('Buyer', 'orderFeedback', array($order['op_id']));
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array('href'=> $opFeedBackUrl, 'class'=>'',
                        'title'=>Labels::getLabel('LBL_Feedback', $siteLangId)),
                        '<i class="fa fa-star"></i>',
                        true
                    );
                }

                if ($canReturnRefund && ($order['return_request'] == 0 && $order['cancel_request'] == 0)) {
                    $opRefundRequestUrl = CommonHelper::generateUrl('Buyer', 'orderReturnRequest', array($order['op_id']));
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array('href'=> $opRefundRequestUrl, 'class'=>'',
                        'title'=>Labels::getLabel('LBL_Refund', $siteLangId)),
                        '<i class="fa fa-dollar"></i>',
                        true
                    );
                }

                $cartUrl = CommonHelper::generateUrl('cart');
                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=>'javascript:void(0)' , 'onClick'=>'return addItemsToCart("'.$order['order_id'].'");',
                    'title'=>Labels::getLabel('LBL_Re-Order', $siteLangId)),
                    '<i class="fa fa-cart-plus"></i>',
                    true
                );
                break;
            default:
                $td->appendElement('plaintext', array(), $order[$key], true);
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
