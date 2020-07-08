<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php
$arr_flds = array(
        'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
        'listserial'=>Labels::getLabel('LBL_Sr._No', $adminLangId),
        'commsetting_prodcat_id'=>Labels::getLabel('LBL_Category', $adminLangId),
        'commsetting_user_id'=>Labels::getLabel('LBL_Seller', $adminLangId),
        'commsetting_product_id'=>Labels::getLabel('LBL_Product', $adminLangId),
        'commsetting_fees'=>Labels::getLabel('LBL_Fees_[%]', $adminLangId),
        'action' => Labels::getLabel('LBL_Action', $adminLangId),
    );
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = 0;
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                if ($row['commsetting_is_mandatory'] != 1) {
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="commsetting_ids[]" value='.$row['commsetting_id'].'><i class="input-helper"></i></label>', true);
                }
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'commsetting_prodcat_id':
                $td->appendElement('plaintext', array(), CommonHelper::displayText($row['prodcat_name']), true);
                break;
            case 'commsetting_user_id':
                $td->appendElement('plaintext', array(), CommonHelper::displayText($row['vendor']), true);
                break;
            case 'commsetting_product_id':
                $td->appendElement('plaintext', array(), CommonHelper::displayText($row['product_name']), true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"editCommissionForm(".$row['commsetting_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_History', $adminLangId),"onclick"=>"viewHistory(".$row['commsetting_id'].")"), Labels::getLabel('LBL_History', $adminLangId), true);

                    if ($row['commsetting_is_mandatory'] != 1) {
                        $innerLi=$innerUl->appendElement('li');
                        $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteCommission(".$row['commsetting_id'].")"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                    }
                }
                break;
            default:
                $td->appendElement('plaintext', array(), CommonHelper::displayText($row[$key]), true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Record_Found', $adminLangId));
}

$frm = new Form('frmCommissionListing', array('id'=>'frmCommissionListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('Commission', 'deleteSelected'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
