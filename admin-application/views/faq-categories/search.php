<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        'dragdrop'=>'',
        'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
        'listserial'=> Labels::getLabel('LBL_Sr._No', $adminLangId) ,
        'faqcat_identifier'=>Labels::getLabel('LBL_category_Name', $adminLangId),
        'faqcat_active'    => Labels::getLabel('LBL_Status', $adminLangId),
        'action' => Labels::getLabel('LBL_Action', $adminLangId) ,
    );
if (!$canEdit) {
    unset($arr_flds['dragdrop']);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive','id'=>'faqcat'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

//$sr_no = $page==1?0:$pageSize*($page-1);
$sr_no = 0;
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    if ($row['faqcat_active'] == applicationConstants::ACTIVE) {
        $tr->setAttribute("id", $row['faqcat_id']);
    }

    if ($row['faqcat_active'] != applicationConstants::ACTIVE) {
        $tr->setAttribute("class", "nodrag nodrop");
    }
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'dragdrop':
                if ($row['faqcat_active'] == applicationConstants::ACTIVE) {
                    $td->appendElement('i', array('class'=>'ion-arrow-move icon'));
                    $td->setAttribute("class", 'dragHandle');
                }
                break;
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="faqcat_ids[]" value='.$row['faqcat_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'faqcat_identifier':
                if ($row['faqcat_name']!='') {
                    $td->appendElement('plaintext', array(), $row['faqcat_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'faqcat_active':
                $active = "";
                if ($row['faqcat_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' .applicationConstants::YES. ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str='<label class="statustab -txt-uppercase">
                     <input '.$active.' type="checkbox" id="switch'.$row['faqcat_id'].'" value="'.$row['faqcat_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                    <i class="switch-handles '.$statusClass.'"></i></label>';
                    $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                $li = $ul->appendElement("li", array('class'=>'droplink'));

                $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                      $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                      $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                      $innerLiEdit=$innerUl->appendElement('li');



                if ($canEdit) {
                    $innerLiEdit=$innerUl->appendElement('li');

                    //$li = $ul->appendElement("li");
                    $innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addFaqCatForm(".$row['faqcat_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);
                }

                if ($canViewFaq) {
                    $innerLiFaqListing=$innerUl->appendElement('li');

                    //$li = $ul->appendElement("li");

                    $url=CommonHelper::generateUrl('Faq', 'index', array($row['faqcat_id']));

                    $innerLiFaqListing->appendElement('a', array('href'=>'javascript:void(0)','onclick'=>'redirectUrl("'.$url.'")', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_FAQ_Listing', $adminLangId)), Labels::getLabel('LBL_FAQ_Listing', $adminLangId), true);
                }

                if ($canEdit) {
                    $innerLiDelete=$innerUl->appendElement('li');

                    //$li = $ul->appendElement("li");
                    $innerLiDelete->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteRecord(".$row['faqcat_id'].")"), Labels::getLabel('LBL_Delete', $adminLangId), true);
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

$frm = new Form('frmFaqCatListing', array('id'=>'frmFaqCatListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('FaqCategories', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php
//$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmFaqCatSearchPaging'
));
?>
<script>
    $(document).ready(function() {
        $('#faqcat').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                fcom.ajax(fcom.makeUrl('FaqCategories', 'updateOrder'), order, function(res) {
                    var ans = $.parseJSON(res);
                    if (ans.status == 1) {
                        fcom.displaySuccessMessage(ans.msg);
                    } else {
                        fcom.displayErrorMessage(ans.msg);
                    }
                });
            },
            dragHandle: ".dragHandle",
        });
    });
</script>
