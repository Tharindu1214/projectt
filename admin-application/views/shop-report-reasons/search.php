<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial'=>Labels::getLabel('LBL_Sr._No', $adminLangId),
    'reportreason_identifier'=>Labels::getLabel('LBL_Reason_Identifier', $adminLangId),
    'reportreason_title'=>Labels::getLabel('LBL_Reason_Title', $adminLangId),
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


$sr_no = 0;
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['reportreason_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="reportreason_ids[]" value='.$row['reportreason_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));

                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                    $innerLiEdit=$innerUl->appendElement('li');

                    $innerLiEdit->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'button small green',
                        'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"editReasonFormNew(".$row['reportreason_id'].")"),
                        Labels::getLabel('LBL_Edit', $adminLangId),
                        true
                    );
                    $innerLiDelete=$innerUl->appendElement('li');
                    $innerLiDelete->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'button small green',
                        'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteRecord(".$row['reportreason_id'].")"),
                        Labels::getLabel('LBL_Delete', $adminLangId),
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

$frm = new Form('frmShpRptRsnListing', array('id'=>'frmShpRptRsnListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('ShopReportReasons', 'deleteSelected'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
