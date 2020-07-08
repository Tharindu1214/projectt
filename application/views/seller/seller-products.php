<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array();
if (count($arrListing) > 0) {
    $arr_flds['select_all'] = Labels::getLabel('LBL_Select_all', $siteLangId);
}
$arr_flds['listserial'] = Labels::getLabel('LBL_Sr', $siteLangId);
/* if( count($arrListing) && is_array($arrListing) && is_array($arrListing[0]['options']) && count($arrListing[0]['options']) ){ */
$arr_flds['name'] = Labels::getLabel('LBL_Name', $siteLangId);
/* } */
$arr_flds['selprod_price'] = Labels::getLabel('LBL_Price', $siteLangId);
$arr_flds['selprod_stock'] = Labels::getLabel('LBL_Quantity', $siteLangId);
$arr_flds['selprod_available_from'] = Labels::getLabel('LBL_Available_From', $siteLangId);
$arr_flds['selprod_active'] = Labels::getLabel('LBL_Status', $siteLangId);
$arr_flds['action'] = Labels::getLabel('LBL_Action', $siteLangId);

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        if (count($arrListing) > 0) {
            $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input type="checkbox" onclick="selectAll( $(this) )" title="'.$val.'" class="selectAll-js"><i class="input-helper"></i></label>', true);
        }
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}
if ($page ==1) {
    $sr_no = 0;
} else {
    $sr_no = ($page-1) * $pageSize;
}
/* CommonHelper::printArray($arrListing); die; */
foreach ($arrListing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ($row['selprod_active'] != applicationConstants::ACTIVE) ? '' : '' ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[]" value='.$row['selprod_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'name':
                $variantStr = '<div class="item__title">'.wordwrap($row['product_name'], 150, "<br>\n").'</div>';
                $variantStr .= ($row['selprod_title'] != '') ? '<div class="item__sub_title">' . wordwrap($row['selprod_title'], 150, "<br>\n").'</div>' : '';
                if (is_array($row['options']) && count($row['options'])) {
                    foreach ($row['options'] as $op) {
                        $variantStr .= '<div class="item__specification">'.wordwrap($op['option_name'].': '.$op['optionvalue_name'], 150, "<br>\n").'</div>';
                    }
                }
                $td->appendElement('plaintext', array(), $variantStr, true);
                break;
            case 'selprod_price':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                break;
            case 'selprod_stock':
                $td->appendElement('plaintext', array(), $row[$key], true);
                if ($row['selprod_track_inventory'] && ($row['selprod_stock']  <= $row['selprod_threshold_stock_level'])) {
                    $td->appendElement('plaintext', array(), " <i class='fa fa-question-circle-o tooltip tooltip--right spn_must_field'><span class='hovertxt'>". Labels::getLabel('MSG_Product_stock_qty_below_or_equal_to_threshold_level', $siteLangId)."</span></i>", true);
                }
                break;
            case 'selprod_available_from':
                $td->appendElement('plaintext', array(), FatDate::format($row[$key], false), true);
                break;
            case 'selprod_active':
                /* $td->appendElement( 'plaintext', array(), $activeInactiveArr[$row[$key]],true ); */
                $active = "";
                if (applicationConstants::ACTIVE == $row['selprod_active']) {
                    $active = 'checked';
                }

                $str = '<label class="toggle-switch" for="switch'.$row['selprod_id'].'"><input '.$active.' type="checkbox" value="'.$row['selprod_id'].'" id="switch'.$row['selprod_id'].'" onclick="toggleSellerProductStatus(event,this)"/><div class="slider round"></div></label>';

                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);
                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=>CommonHelper::generateUrl('seller', 'sellerProductForm', array($row['selprod_product_id'],$row['selprod_id'])), 'class'=>'', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId)),
                    '<i class="fa fa-edit"></i>',
                    true
                );

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=>'javascript:void(0)', 'class'=>'','title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"sellerProductDelete(".$row['selprod_id'].")"),
                    '<i class="fa fa-trash"></i>',
                    true
                );
                $productOptions = Product::getProductOptions($row['selprod_product_id'], $siteLangId);
                if (is_array($productOptions) && count($productOptions)) {
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'','title'=>Labels::getLabel('LBL_Clone', $siteLangId),"onclick"=>"sellerProductCloneForm(".$row['selprod_product_id'].",".$row['selprod_id'].")"),
                        '<i class="fa fa-clone"></i>',
                        true
                    );
                }

                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arrListing) == 0) {
    echo $tbl->getHtml();
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
    // $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_products_found_under_your_publication', $siteLangId));
    //$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId));
} else {
    $frm = new Form('frmSellerProductsListing', array('id'=>'frmSellerProductsListing'));
    $frm->setFormTagAttribute('class', 'form');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
    // $frm->setFormTagAttribute('action', CommonHelper::generateUrl('Seller', 'deleteBulkSellerProducts'));
    $frm->setFormTagAttribute('action', CommonHelper::generateUrl('Seller', 'toggleBulkStatuses'));
    $frm->addHiddenField('', 'status');

    echo $frm->getFormTag();
    echo $frm->getFieldHtml('status');
    echo $tbl->getHtml(); ?> </form> <?php
}

if (!$product_id) {
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSellerProductSearchPaging'));
    $pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToSellerProductSearchPage');
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
