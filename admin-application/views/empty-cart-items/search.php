<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_Sr._No', $adminLangId),
    'emptycartitem_identifier' => Labels::getLabel('LBL_Title', $adminLangId),
    'emptycartitem_url' => Labels::getLabel('LBL_URL', $adminLangId),
    'emptycartitem_active' => Labels::getLabel('LBL_Status', $adminLangId),
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
foreach ($arrListing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array());
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="emptycartitem_ids[]" value='.$row['emptycartitem_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'emptycartitem_url':
                $itemUrl = str_replace('{SITEROOT}', CommonHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL), $row[$key]);
                $itemUrl = str_replace('{siteroot}', CommonHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL), $itemUrl);
                $td->appendElement('plaintext', array(), $itemUrl);
                break;
            case 'emptycartitem_identifier':
                if ($row['emptycartitem_title']!='') {
                    $td->appendElement('plaintext', array(), $row['emptycartitem_title'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'emptycartitem_active':
                $active = "";
                if ($row['emptycartitem_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' .applicationConstants::YES. ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str='<label class="statustab -txt-uppercase">
                     <input '.$active.' type="checkbox" id="switch'.$row['emptycartitem_id'].'" value="'.$row['emptycartitem_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                    <i class="switch-handles '.$statusClass.'"></i></label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));


                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                    $innerLiEdit=$innerUl->appendElement('li');

                    $innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addEmptyCartItemForm(".$row['emptycartitem_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    $innerLiDelete = $innerUl->appendElement("li");
                    $innerLiDelete->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteRecord(".$row['emptycartitem_id'].")"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arrListing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
$frm = new Form('frmEmptyCartItemListing', array('id'=>'frmEmptyCartItemListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('EmptyCartItems', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmEmptyCartItemSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
