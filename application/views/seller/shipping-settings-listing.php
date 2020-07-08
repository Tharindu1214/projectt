<?php  
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'city_identifier'    =>    Labels::getLabel('LBL_CITY_NAME', $siteLangId),
    'scompany_identifier'    =>    Labels::getLabel('LBL_SHIPPING_COMPANY', $siteLangId),
    'sduration_from'        =>    Labels::getLabel('LBL_PROCESSING_TIME', $siteLangId),
    'cost_for_1st_kg'        =>    Labels::getLabel('LBL_Shipping_Charge', $siteLangId),
    'each_additional_kg'        =>    Labels::getLabel('LBL_Additional_charge', $siteLangId),
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
            case 'city_identifier':
                $td->appendElement('plaintext', array(), $row['city_identifier'], true);
                break;
            case 'scompany_identifier':
                $td->appendElement('plaintext', array(), $row['scompany_identifier'], true);
                break;
            case 'sduration_from':
                if($row['sduration_from'] == $row['sduration_to']){
                    $cond = '';
                    if($row['sduration_from'] > 1){
                       $cond = 's';
                    }
                    $td->appendElement('plaintext', array(), $row['sduration_from'].' Business Day'.$cond, true);
                }else{
                    $td->appendElement('plaintext', array(), $row['sduration_from'].' to '.$row['sduration_to'].' Business Days', true);
                }

               
                break;
            case 'cost_for_1st_kg':
                $td->appendElement('plaintext', array(), $row['cost_for_1st_kg'], true);
                break;
            case 'each_additional_kg':
                $td->appendElement('plaintext', array(), $row['each_additional_kg'], true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);

                if ($sellerPage) {
                    $url = CommonHelper::generateUrl('Seller', 'deleteShippingSetting', array($row['ship_set_id']));
                }
                $li = $ul->appendElement("li");
                $li->appendElement(
                    'span',
                    array('style'=>'cursor:pointer',
                    'onclick'=>"deleteShippingSettings(".$row['ship_set_id'].")",
                    'title'=>Labels::getLabel('LBL_Delete_Shipping_setting', $siteLangId)),
                    '<i class="fa fa-trash"></i>',
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
// echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOrderReturnRequestSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToOrderReturnRequestSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
