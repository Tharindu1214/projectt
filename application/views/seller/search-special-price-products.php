<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all'=>Labels::getLabel('LBL_Select_all', $siteLangId),
    'product_name' => Labels::getLabel('LBL_Name', $siteLangId),
    'splprice_start_date' => Labels::getLabel('LBL_Start_Date', $siteLangId),
    'splprice_end_date' => Labels::getLabel('LBL_End_Date', $siteLangId),
    'splprice_price' => Labels::getLabel('LBL_Special_Price', $siteLangId),
    'action' => Labels::getLabel('LBL_Action', $siteLangId),
);

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered splPriceList-js'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $column => $lblTitle) {
    if ('select_all' == $column) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$lblTitle.'" type="checkbox" onclick="selectAll($(this))" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $th->appendElement('th', array(), $lblTitle);
    }
}

foreach ($arrListing as $sn => $row) {
    $tr = $tbl->appendElement('tr', array());
    $splPriceId = $row['splprice_id'];
    $selProdId = $row['selprod_id'];
    $editListingFrm = new Form('editListingFrm-'.$splPriceId, array('id'=>'editListingFrm-'.$splPriceId));
    foreach ($arr_flds as $column => $lblTitle) {
        $tr->setAttribute('id', 'row-'.$splPriceId);
        $td = $tr->appendElement('td');
        switch ($column) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids['.$splPriceId.']" value='.$selProdId.'><i class="input-helper"></i></label>', true);
                break;
            case 'product_name':
                // last Param of getProductDisplayTitle function used to get title in html form.
                $productName = SellerProduct::getProductDisplayTitle($selProdId, $siteLangId, true);
                $td->appendElement('plaintext', array(), $productName, true);
                break;
            case 'splprice_start_date':
            case 'splprice_end_date':
                $date = date('Y-m-d', strtotime($row[$column]));
                $attr = array(
                    'readonly' => 'readonly',
                    'placeholder' => $lblTitle,
                    'data-selprodid' => $selProdId,
                    'data-id' => $splPriceId,
                    'data-oldval' => $date,
                    'id' => $column.'-'.$splPriceId,
                    'class' => 'date_js js--splPriceCol hidden sp-input',
                );
                $editListingFrm->addDateField($lblTitle, $column, $date, $attr);

                $td->appendElement('div', array("class" => 'js--editCol edit-hover', "title" => Labels::getLabel('LBL_Click_To_Edit', $siteLangId)), $date, true);
                $td->appendElement('plaintext', array(), $editListingFrm->getFieldHtml($column), true);
                break;
            case 'splprice_price':
                $input = '<input type="text" data-id="'.$splPriceId.'" value="'.$row[$column].'" data-selprodid="'.$selProdId.'" name="'.$column.'" data-oldval="'.$row[$column].'" data-displayoldval="'.CommonHelper::displayMoneyFormat($row[$column], true, true).'" class="js--splPriceCol hidden sp-input"/>';
                $td->appendElement('div', array("class" => 'js--editCol edit-hover', "title" => Labels::getLabel('LBL_Click_To_Edit', $siteLangId)), CommonHelper::displayMoneyFormat($row[$column], true, true), true);
                $td->appendElement('plaintext', array(), $input, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"), '', true);

                $li = $ul->appendElement('li');
                $li->appendElement(
                    'a',
                    array('href'=>'javascript:void(0)', 'class'=>'',
                    'title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"deleteSellerProductSpecialPrice(".$splPriceId.")"),
                    '<i class="fa fa-trash"></i>',
                    true
                );
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$column], true);
                break;
        }
    }
}
if (count($arrListing) == 0) {
    $tbl->appendElement('tr', array('class' => 'noResult--js'))->appendElement(
        'td',
        array('colspan'=>count($arr_flds)),
        Labels::getLabel('LBL_No_Record_Found', $siteLangId)
    );
}

$frm = new Form('frmSplPriceListing', array('id'=>'frmSplPriceListing'));
$frm->setFormTagAttribute('class', 'form');

echo $frm->getFormTag();
echo $tbl->getHtml(); ?>
</form>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchSpecialPricePaging'));

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'callBackJsFunc' => 'goToSearchPage','adminLangId'=>$siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
