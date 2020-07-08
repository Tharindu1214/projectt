<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_lang_flds = array(
    'prodspec_name' => $languages[$siteLangId]
);
$arr_flds['action'] = Labels::getLabel('LBL_Action', $siteLangId);

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-w50'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_lang_flds as $val) {
    $e = $th->appendElement('th', array(), $val, true);
}
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

foreach ($prodSpec as $key => $specification) {
    $tr = $tbl->appendElement('tr');
    $row = $specification[$siteLangId];
    // commonHelper::printArray($row);
    $td = $tr->appendElement('td', array("width"=>"80%"));

    switch ($key) {
        default:
            $td->appendElement('plaintext', array(), $row['prodspec_name'].': '.$row['prodspec_value'], true);
            break;
    }

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td', array("width"=>"20%"));
        switch ($key) {
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);
                $li = $ul->appendElement("li");
                $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId),"onclick"=>"addProdSpec(".$productId.",".$row['prodspec_id'].")"), '<i class="fa fa-edit"></i>', true);
                $li = $ul->appendElement("li");
                $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Delete', $siteLangId), "onclick"=>"deleteProdSpec(".$productId.",".$row['prodspec_id'].")"), '<i class="fa fa-trash"></i>', true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}

if (count($prodSpec) == 0) {
    $message = Labels::getLabel('LBL_No_Specifications_found_under_your_product', $siteLangId);
    $linkArr = array(
        0=>array(
            'href'=>'javascript:void(0);',
            'label'=>Labels::getLabel('LBL_Add_Specification', $siteLangId),
            'onClick'=>"addProdSpec(".$productId.",0)",
            )
        );
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'linkArr'=>$linkArr,'message'=>$message));
/* $this->includeTemplate('_partial/no-record-found.php',array('siteLangId' => $siteLangId),false); */
    /* $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Specifications_found_under_your_product', $siteLangId)); */
} else {
    echo $tbl->getHtml();
}
