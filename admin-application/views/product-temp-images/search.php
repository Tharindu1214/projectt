<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arrFlds = array(
    'listserial'=>Labels::getLabel('LBL_Sr._No', $adminLangId),
    'product_name'=>Labels::getLabel('LBL_Product_Name', $adminLangId),
    'afile_physical_path'    =>    Labels::getLabel('LBL_Path', $adminLangId),
    'afile_downloaded' => Labels::getLabel('LBL_Is_Downloaded', $adminLangId),
);
if ($canEdit) {
    $arrFlds['action'] = Labels::getLabel('LBL_Action', $adminLangId);
}
$tbl = new HtmlElement(
    'table',
    array('width'=>'100%', 'class'=>'table table-responsive table--hovered')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arrFlds as $val) {
    $e = $th->appendElement('th', array(), $val, true);
}

$sr_no = $page==1 ? 0 : $pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arrFlds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'afile_downloaded':
                $lbl = Labels::getLabel('LBL_No', $adminLangId);
                if ($row[$key]) {
                    $lbl = Labels::getLabel('LBL_Yes', $adminLangId);
                }
                $td->appendElement('plaintext', array(), $lbl);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"editProductTempImage(".$row['afile_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arrFlds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmProductsTempImagesPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
