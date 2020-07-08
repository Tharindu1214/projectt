<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php
$arr_flds = array(
        'dragdrop'=>'',
        'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
        'listserial'=> Labels::getLabel('LBL_Sr._No', $adminLangId),
        'orderstatus_name'=> Labels::getLabel('LBL_Order_Status_Name', $adminLangId),
        'orderstatus_is_active'=> Labels::getLabel('LBL_Status', $adminLangId),
        'action' =>  Labels::getLabel('LBL_Action', $adminLangId),
    );
if (!$canEdit) {
    unset($arr_flds['dragdrop']);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive', 'id'=>'orderStatuses'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page==1 ? 0: $pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( ));
    $tr->setAttribute("id", $row['orderstatus_id']);
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'dragdrop':
                if ($row['orderstatus_is_active'] == applicationConstants::ACTIVE) {
                    $td->appendElement('i', array('class'=>'ion-arrow-move icon'));
                    $td->setAttribute("class", 'dragHandle');
                }
                break;
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="orderstatus_ids[]" value='.$row['orderstatus_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'orderstatus_is_active':
                $active = "active";
                if (!$row['orderstatus_is_active']) {
                    $active = '';
                }
                $statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
                $str='<label id="'.$row['orderstatus_id'].'" class="statustab '.$active.'" onclick="'.$statucAct.'">
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

                    $innerLi = $innerUl->appendElement("li");
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"editOrderStatusForm(".$row['orderstatus_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);
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

$frm = new Form('frmOrderStatusListing', array('id'=>'frmOrderStatusListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('OrderStatus', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmOrderStatusSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
<script>
    $(document).ready(function() {
        $('#orderStatuses').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                fcom.ajax(fcom.makeUrl('OrderStatus', 'setOrderStatusesOrder'), order, function(res) {
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
