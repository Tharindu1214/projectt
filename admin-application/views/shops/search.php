<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial'=>Labels::getLabel('LBL_Sr._No', $adminLangId),
    'user_name'=>Labels::getLabel('LBL_Owner', $adminLangId),
    'shop_identifier'=>Labels::getLabel('LBL_Name', $adminLangId),
    'numOfProducts'=>Labels::getLabel('LBL_Products', $adminLangId),
    'numOfReports'=>Labels::getLabel('LBL_Reports', $adminLangId),
    'numOfReviews'=>Labels::getLabel('LBL_Reviews', $adminLangId),
    'shop_featured'=>Labels::getLabel('LBL_Featured', $adminLangId),
    'shop_active'=>Labels::getLabel('LBL_Status', $adminLangId),
    'shop_created_on'=>Labels::getLabel('LBL_Created_on', $adminLangId),
    'shop_supplier_display_status'=>Labels::getLabel('LBL_Status_by_seller', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('id' => $row['shop_id'], 'class' => '' ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="shop_ids[]" value='.$row['shop_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'shop_supplier_display_status':
                $td->appendElement('plaintext', array(), $onOffArr[$row[$key]], true);
                break;
            case 'shop_active':
                $active = "";
                if ($row['shop_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' .applicationConstants::YES. ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str= '<label class="statustab -txt-uppercase">
					   <input '.$active.' type="checkbox" id="switch'.$row['shop_id'].'" value="'.$row['shop_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                       <i class="switch-handles '.$statusClass.'"></i></label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'numOfProducts':
                if ($canViewSellerProducts) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('SellerProducts').'", '.$row['shop_user_id'].')'), $row[$key]);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'shop_featured':
                $td->appendElement('plaintext', array(), applicationConstants::getYesNoArr($adminLangId)[$row[$key]], true);
                break;
            case 'numOfReports':
                if ($canViewShopReports) {
                    $td->appendElement('a', array('target' => '_blank', 'href' => CommonHelper::generateUrl('ShopReports', 'index', array($row['shop_id'])) ), $row[$key]);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'numOfReviews':
                if ($canViewShopReports) {
                    $td->appendElement('a', array('target' => '_blank', 'href' => CommonHelper::generateUrl('ProductReviews', 'index', array($row['shop_user_id'])) ), $row[$key]);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'shop_identifier':
                if ($row['shop_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['shop_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                $td->appendElement('br', array());
                $shopLink = CommonHelper::generateFullUrl("Shops", 'View', array($row['shop_id']), CONF_WEBROOT_FRONT_URL);
                $td->appendElement('plaintext', array(), '<a href="'.$shopLink.'" target="_blank">'.Labels::getLabel('LBL_Visit_Shop', $adminLangId).'</a>', true);
                break;
            case 'shop_created_on':
                $td->appendElement('plaintext', array(), FatDate::format($row[$key]));
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));

                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addShopForm(".$row['shop_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);
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

$frm = new Form('frmShopListing', array('id'=>'frmShopListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('Shops', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
?>
    <?php echo $tbl->getHtml(); ?>
</form>
<?php
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmShopSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
