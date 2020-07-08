<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        'listserial'=>'',
        'message_text'=>'',
        'action' => '',
    );
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
/*$img = '<img src="'.CommonHelper::generateUrl('Image','user',array($row['message_sent_by'],'THUMB',true),CONF_WEBROOT_FRONT_URL).'" />';
                $td->appendElement('plaintext', array(), $img ,true); */

                $div_about_me = $td->appendElement('div', array('class'=>'avtar avtar--small'));
                $div_about_me->appendElement('img', array('src'=>CommonHelper::generateUrl('Image', 'user', array($row['message_sent_by'],'MINI',true), CONF_WEBROOT_FRONT_URL)));

                break;
            case 'message_text':
                    $td->appendElement('plaintext', array(), '<span>'.$row["message_date"].'</span>', true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '<p>'.$row["message_sent_by_username"].'</p>', true);
                    $td->appendElement('plaintext', array(), '<p id="'.$row["message_id"].'">'.nl2br($row["message_text"]).'</p>', true);

                break;
            case 'action':
                    $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));

                    $li = $ul->appendElement("li", array('class'=>'droplink'));
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"messageForm(".$row['message_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteRecord(".$row['message_id'].")"), Labels::getLabel('LBL_Delete', $adminLangId), true);

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
echo $tbl->getHtml();

/* echo FatUtility::createHiddenFormFromData ( $postedData, array (
        'name' => 'frmShipDurationSrchPaging'
) ); */
/* $pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false); */
