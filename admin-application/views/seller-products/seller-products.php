<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_Sr.', $adminLangId),
);
/* if( count($arrListing) && is_array($arrListing) && is_array($arrListing[0]['options']) && count($arrListing[0]['options']) ){ */
    $arr_flds['name'] = Labels::getLabel('LBL_Name', $adminLangId);
/* } */
$arr_flds['user'] = Labels::getLabel('LBL_Seller', $adminLangId);
$arr_flds['selprod_price'] = Labels::getLabel('LBL_Price', $adminLangId);
$arr_flds['selprod_stock'] = Labels::getLabel('LBL_Quantity', $adminLangId);
$arr_flds['selprod_available_from'] = Labels::getLabel('LBL_Available_From', $adminLangId);
$arr_flds['selprod_active'] = Labels::getLabel('LBL_Status', $adminLangId);
$arr_flds['action'] = Labels::getLabel('LBL_Action', $adminLangId);

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => 'hide--mobile'));
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arrListing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array());

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[]" value='.$row['selprod_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'name':
                $variantStr = ($row['selprod_title'] != '') ? $row['selprod_title'].'<br/>' : '';
                if (is_array($row['options']) && count($row['options'])) {
                    foreach ($row['options'] as $op) {
                        $variantStr .= $op['option_name'].': '.$op['optionvalue_name'].'<br/>';
                    }
                }
                $td->appendElement('plaintext', array(), $variantStr, true);
                if ($canViewProducts) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Products').'", '.$row['selprod_product_id'].')'), $row['product_name'], true);
                } else {
                    $td->appendElement('plaintext', array(), $row['product_name'], true);
                }
                break;
            case 'user':
                if ($canViewUsers) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Users').'", '.$row['selprod_user_id'].')'), '<strong>'.Labels::getLabel('LBL_N:', $adminLangId).' </strong>'.$row['user_name'], true);
                } else {
                    $td->appendElement('plaintext', array(), '<strong>'.Labels::getLabel('LBL_N:', $adminLangId).' </strong>'.$row['user_name'], true);
                }
                $userDetail = '<br/><strong>'.Labels::getLabel('LBL_Email:', $adminLangId).' </strong>'.$row['credential_email'].'<br/>';
                $td->appendElement('plaintext', array(), $userDetail, true);
                break;
            case 'selprod_price':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                break;
            case 'selprod_available_from':
                $td->appendElement('plaintext', array(), FatDate::format($row[$key], false), true);
                break;
            case 'selprod_active':
                $active = "";
                if ($row['selprod_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' .applicationConstants::YES. ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str= '<label class="statustab -txt-uppercase">
					   <input '.$active.' type="checkbox" id="switch'.$row['selprod_id'].'" value="'.$row['selprod_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                       <i class="switch-handles '.$statusClass.'"></i></label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"), '', true);
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));

                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);

                    $innerDiv = $li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl = $innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                    $innerLiEdit = $innerUl->appendElement('li');

                    $innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
                    'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addSellerProductForm(" . $row['selprod_product_id'] . ",".$row['selprod_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    $innerLiSeo = $innerUl->appendElement("li");
                    $innerLiSeo->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_SEO_CONTENT', $adminLangId),"onclick"=>"getProductSeoGeneralForm(".$row['selprod_id'].")"),
                        Labels::getLabel('LBL_SEO_CONTENT', $adminLangId),
                        true
                    );

                    $innerLiSpecialPrice = $innerUl->appendElement("li");
                    $innerLiSpecialPrice->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Special_Price', $adminLangId),"onclick"=>"addSellerProductSpecialPrices(".$row['selprod_id'].")"),
                        Labels::getLabel('LBL_Special_Price', $adminLangId),
                        true
                    );

                    $innerLiVolumeDis = $innerUl->appendElement("li");
                    $innerLiVolumeDis->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Volume_Discount', $adminLangId),"onclick"=>"sellerProductVolumeDiscounts(".$row['selprod_id'].")"),
                        Labels::getLabel('LBL_Volume_Discount', $adminLangId),
                        true
                    );

                    $innerLiLinks = $innerUl->appendElement("li");
                    $innerLiLinks->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Links', $adminLangId),"onclick"=>"sellerProductLinkFrm(".$row['selprod_id'].")"),
                        Labels::getLabel('LBL_Links', $adminLangId),
                        true
                    );
                    
                    if ($row['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                        $innerLiDownload = $innerUl->appendElement("li");
                        $innerLiDownload->appendElement(
                            'a',
                            array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Downloads', $adminLangId),"onclick"=>"sellerProductDownloadFrm(".$row['selprod_id'].")"),
                            Labels::getLabel('LBL_Downloads', $adminLangId),
                            true
                        );
                    }

                    $innerLiDelete = $innerUl->appendElement("li");
                    $innerLiDelete->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Delete_Product', $adminLangId),"onclick"=>"sellerProductDelete(".$row['selprod_id'].")"),
                        Labels::getLabel('LBL_Delete_Product', $adminLangId),
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
    $tbl->appendElement('tr')->appendElement(
        'td',
        array('colspan'=>count($arr_flds)),
        Labels::getLabel('LBL_No_Record_Found', $adminLangId)
    );
}

$frm = new Form('frmSelProdListing', array('id'=>'frmSelProdListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('SellerProducts', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
?>
<?php echo $tbl->getHtml(); ?>
</form>
<?php if (!$product_id) {
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging'));
    $pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToSearchPage','adminLangId'=>$adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
