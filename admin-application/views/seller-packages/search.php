<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
        'listserial'=>Labels::getLabel('LBL_Sr_no.', $adminLangId),
        'spackage_identifier'=>Labels::getLabel('LBL_Package_Name', $adminLangId),
        'spackage_active'    =>    Labels::getLabel('LBL_Status', $adminLangId),
        'action' => Labels::getLabel('LBL_Action', $adminLangId),
    );
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive','id'=>'options'));

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
    $tr->setAttribute("id", $row[SellerPackages::DB_TBL_PREFIX.'id']);
    if ($row['spackage_active'] != applicationConstants::ACTIVE) {
        $tr->setAttribute("class", " nodrag nodrop");
    }
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="'.SellerPackages::DB_TBL_PREFIX.'ids[]" value='.$row[SellerPackages::DB_TBL_PREFIX.'id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'spackage_identifier':
                if ($row[SellerPackages::DB_TBL_PREFIX.'name']!='') {
                    $td->appendElement('plaintext', array(), $row[SellerPackages::DB_TBL_PREFIX.'name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'spackage_active':
                $active = "active";
                if (!$row['spackage_active']) {
                    $active = '';
                }
                $statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
                $str = '<label id="'.$row[SellerPackages::DB_TBL_PREFIX.'id'].'" class="statustab '.$active.'" onclick="'.$statucAct.'">
                <span data-off="'. Labels::getLabel('LBL_Active', $adminLangId) .'" data-on="'. Labels::getLabel('LBL_Inactive', $adminLangId) .'" class="switch-labels"></span>
                <span class="switch-handles"></span>
                </label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"PackageForm(".$row[SellerPackages::DB_TBL_PREFIX.'id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Settings', $adminLangId),"onclick"=>"searchPlans(".$row[SellerPackages::DB_TBL_PREFIX.'id'].")"), Labels::getLabel('LBL_Settings', $adminLangId), true);
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

$frm = new Form('frmSellerPkgListing', array('id'=>'frmSellerPkgListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('SellerPackages', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
