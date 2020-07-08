<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds1 = array(
    'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial'=>Labels::getLabel('LBL_Sr.', $adminLangId),
    'product_identifier'=>Labels::getLabel('LBL_Name', $adminLangId)
);

$arr_flds2 = array();

$arr_flds3 = array(
    'user_name'=>Labels::getLabel('LBL_User', $adminLangId),
    //'attrgrp_name'=>Labels::getLabel('LBL_Attribute_Group',$adminLangId),
    'product_added_on'=>Labels::getLabel('LBL_Date', $adminLangId),
    'product_approved' => Labels::getLabel('LBL_Approval_Status', $adminLangId),
    'product_active'=>Labels::getLabel('LBL_Status', $adminLangId),
    'action'=>Labels::getLabel('LBL_Action', $adminLangId)
);
$arr_flds = $arr_flds1 + $arr_flds2 + $arr_flds3;

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array());

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="product_ids[]" value='.$row['product_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'product_identifier':
                $td->appendElement('plaintext', array(), $row['product_name'] . '<br>', true);
                $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                break;
            case 'user_name':
                if ($canViewUsers) {
                    !empty($row[$key]) ? $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Users').'",'.$row['product_seller_id'].')'), $row[$key]) : $td->appendElement('plaintext', array(), (!empty($row[$key]) ? $row[$key] : 'Admin'), true);
                } else {
                    $td->appendElement('plaintext', array(), (!empty($row[$key]) ? $row[$key] : 'Admin'), true);
                }
                break;
            case 'attrgrp_name':
                $td->appendElement('plaintext', array(), CommonHelper::displayNotApplicable($adminLangId, $row[$key]), true);
                break;
            case 'product':
                $td->appendElement('plaintext', array(), ($row['product_seller_id']) ? 'Custom' : 'Catalog');
                break;
            case 'product_approved':
                $approveUnApproveArr = Product::getApproveUnApproveArr($adminLangId);
                $td->appendElement('plaintext', array(), $approveUnApproveArr[$row[$key]], true);
                break;
            case 'product_active':
                $active = "";
                if ($row['product_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' .applicationConstants::YES. ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                    $str='<label class="statustab -txt-uppercase">
                     <input '.$active.' type="checkbox" id="switch'.$row['product_id'].'" value="'.$row['product_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                    <i class="switch-handles '.$statusClass.'"></i></label>';
                    $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'product_added_on':
                $td->appendElement('plaintext', array(), FatDate::format($row[$key], true));
                break;
            case 'action':
                    $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));

                    $li = $ul->appendElement("li", array('class'=>'droplink'));


                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                      $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                      $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                      //$innerLi=$innerUl->appendElement('li');

                if ($canEdit) {
                    $innerLiEdit = $innerUl->appendElement("li");
                    $innerLiEdit->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addProductForm(".$row['product_id'].", ".$row['product_attrgrp_id'].")"),
                        Labels::getLabel('LBL_Edit', $adminLangId),
                        true
                    );

                    $innerLiLinks = $innerUl->appendElement("li");
                    $innerLiLinks->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Links', $adminLangId),"onclick"=>"productLinksForm(".$row['product_id'].")"),
                        Labels::getLabel('LBL_Links', $adminLangId),
                        true
                    );
                }
                $innerLiOptions = $innerUl->appendElement("li");
                $innerLiOptions->appendElement(
                    'a',
                    array('href'=>'javascript:void(0)', 'class'=>'button small green', 'innerLi'=>Labels::getLabel('LBL_Options', $adminLangId),"onclick"=>"addProductOptionsForm(".$row['product_id'].")"),
                    Labels::getLabel('LBL_Options', $adminLangId),
                    true
                );

                $innerLiProductImages = $innerUl->appendElement("li");
                $innerLiProductImages->appendElement(
                    "a",
                    array('title' =>Labels::getLabel('LBL_Product_Images', $adminLangId), 'onclick' => 'productImagesForm('.$row['product_id'].')','href'=>'javascript:void(0)'),
                    Labels::getLabel('LBL_Product_Images', $adminLangId),
                    true
                );

                $innerLiproductTags =  $innerUl->appendElement("li");
                $innerLiproductTags->appendElement(
                    "a",
                    array('title' => Labels::getLabel('LBL_Product_Tags', $adminLangId), 'onclick' => 'productTagsForm('.$row['product_id'].')','href'=>'javascript:void(0)'),
                    Labels::getLabel('LBL_Product_Tags', $adminLangId),
                    true
                );

                $innerLiSpecifications = $innerUl->appendElement("li");
                $innerLiSpecifications->appendElement(
                    "a",
                    array('title' =>Labels::getLabel('LBL_Specifications', $adminLangId), 'onclick' => 'productSpecifications('.$row['product_id'].')','href'=>'javascript:void(0)'),
                    Labels::getLabel('LBL_Specifications', $adminLangId),
                    true
                );
                if ($canEdit) {
                    $innerLiDeleteProduct = $innerUl->appendElement("li");
                    $innerLiDeleteProduct->appendElement(
                        "a",
                        array('title' =>Labels::getLabel('LBL_Delete_Product', $adminLangId), 'onclick' => 'deleteProduct('.$row['product_id'].')','href'=>'javascript:void(0)'),
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
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

$frm = new Form('frmProdListing', array('id'=>'frmProdListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('Products', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
