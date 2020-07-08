<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$arr_flds = array(
        'listserial'=>Labels::getLabel('LBL_Sr._no.', $siteLangId),
        'option_identifier'=>Labels::getLabel('LBL_Option_Name', $siteLangId),
        'action' => Labels::getLabel('LBL_Action', $siteLangId),
    );
if (count($arr_listing) > 0) {
    $arr_flds = array_merge(array('select_all'=>Labels::getLabel('LBL_Select_all', $siteLangId)), $arr_flds);
}

$tbl = new HtmlElement(
    'table',
    array('width'=>'100%', 'class'=>'table table--orders','id'=>'options')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i>'.$val.'</label>', true);
    } else {
        $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['option_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="option_id[]" value='.$row['option_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'option_identifier':
                if ($row['option_name']!='') {
                    $td->appendElement('plaintext', array(), $row['option_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"));

                    /* if(!in_array($row['option_type'],$ignoreOptionValues)){
                        $li = $ul->appendElement("li");
                        $li->appendElement('a',array(
                        'href'=>CommonHelper::generateUrl('OptionValues',
                        'index',array($row['option_id'])),
                        'class'=>'button small green',
                        'title'=>'Option Values'
                        ),
                        '<i class="ion-navicon-round icon"></i>', true);
                    } */

                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array(
                        'href'=>'javascript:void(0)',
                        'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId),
                        "onclick"=>"optionForm(".$row['option_id'].")"),
                        '<i class="fa fa-edit"></i>',
                        true
                    );

                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array(
                        'href'=>"javascript:void(0)", 'class'=>'button small green',
                        'title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"deleteOptionRecord(".$row['option_id'].")"),
                        '<i class="fa fa-trash"></i>',
                        true
                    );

                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
$frm = new Form('frmOptionListing', array('id'=>'frmOptionListing'));
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('Seller', 'bulkOptionsDelete'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
?>
</form>
<?php

$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOptionsSearchPaging'));

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
