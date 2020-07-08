<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
        'listserial'=>Labels::getLabel('LBL_Sr_no.', $adminLangId),
        /*'promotion_id'=>Labels::getLabel('LBL_Id',$adminLangId),*/
        'promotion_name'=>Labels::getLabel('LBL_Name', $adminLangId),
        'user_name'=>Labels::getLabel('LBL_User', $adminLangId),
        'promotion_type'=>Labels::getLabel('LBL_Type', $adminLangId),
        'blocation_promotion_cost'=>Labels::getLabel('LBL_CPC', $adminLangId),
        'promotion_budget'=>Labels::getLabel('LBL_budget', $adminLangId),
        'impressions'=>Labels::getLabel('LBL_Impressions', $adminLangId),
        'clicks'=>Labels::getLabel('LBL_Clicks', $adminLangId),
        //'orders'=>Labels::getLabel('LBL_Orders',$adminLangId),
        'promotion_approved'=>Labels::getLabel('LBL_Approved', $adminLangId),
        'action' => Labels::getLabel('LBL_Action', $adminLangId),
    );
$tbl = new HtmlElement(
    'table',
    array('width'=>'100%', 'class'=>'table table--hovered table-responsive','id'=>'promotions')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}
$sr_no = $page==1?0:$pageSize*($page-1);

/* CommonHelper::printArray($arr_listing); die; */
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['promotion_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="promotion_ids[]" value='.$row['promotion_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'promotion_name':
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
            case 'user_name':
                $userDetail = '<strong>'.Labels::getLabel('LBL_N:', $adminLangId).' </strong>'.$row['user_name'].'<br/>';
                $userDetail .= '<strong>'.Labels::getLabel('LBL_UN:', $adminLangId).' </strong>'.$row['credential_username'].'<br/>';
                $td->appendElement('plaintext', array(), $userDetail, true);

                if (!empty($row['shop_name'])) {
                    if ($canViewShops) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Shops').'", '.$row['shop_id'].')'), '<strong>'.Labels::getLabel('LBL_Shop:', $adminLangId).' </strong>'.$row['shop_name'], true);
                    } else {
                        $td->appendElement('plaintext', array(), '<strong>'.Labels::getLabel('LBL_Shop:', $adminLangId).' </strong>'.$row['shop_name'], true);
                    }
                }
                break;
            case 'promotion_type':
                $td->appendElement('plaintext', array(), $typeArr[$row[$key]], true);
                break;
            case 'blocation_promotion_cost':
            case 'banner_promotion_cost':
                $cost = Promotion::getPromotionCostPerClick($row['promotion_type'], $row['blocation_id']);
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($cost, true, true));
                break;
            case 'promotion_budget':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true));
                break;
            case 'promotion_approved':
                $td->appendElement('plaintext', array(), $yesNoArr[$row[$key]], true);
                break;
            case 'action':
                if ($canEdit) {
                    $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                    $li = $ul->appendElement("li", array('class'=>'droplink'));
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"promotionForm(".$row['promotion_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deletepromotionRecord(".$row['promotion_id'].")"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), FatUtility::int($row[$key]));
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_records_Found', $adminLangId));
}

$frm = new Form('frmPromotionsListing', array('id'=>'frmPromotionsListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmPromotionSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
