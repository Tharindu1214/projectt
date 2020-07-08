<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        'message_sent_by_username'=> Labels::getLabel('LBL_From', $adminLangId),
        'message_sent_to_name'=> Labels::getLabel('LBL_To', $adminLangId),
        'thread_subject' => Labels::getLabel('LBL_Subject', $adminLangId),
        'message_text' => Labels::getLabel('LBL_Message', $adminLangId),
        'message_date' => Labels::getLabel('LBL_Date', $adminLangId),
        'action' => Labels::getLabel('LBL_Action', $adminLangId),
    );

$tbl = new HtmlElement('table', array('class'=>'table table-responsive table--hovered','id'=>'post'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class'=>'tr--first'));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'message_sent_by_username':
                $div_about_me = $td->appendElement('div', array('class'=>'avtar avtar--small'));
                $div_about_me->appendElement('img', array('src'=>CommonHelper::generateUrl('Image', 'user', array($row['message_sent_by'],'MINI',true), CONF_WEBROOT_FRONT_URL)));
                $span = $td->appendElement('span', array('class'=>'avtar__name'), $row['message_sent_by_username']);

                break;
            case 'message_sent_to_name':
                //$td->setAttribute(array('width'=>'55%'));
                $figure = $td->appendElement('figure', array('class'=>'avtar bgm-purple'));
                $figure->appendElement('img', array('src'=>CommonHelper::generateUrl('Image', 'user', array($row['message_sent_to'],'MINI',true), CONF_WEBROOT_FRONT_URL)));
                $span = $td->appendElement('span', array('class'=>'avtar__name'), $row['message_sent_to_name']);

                break;
            case 'message_text':
                $div = $td->appendElement('div', array('class'=>'listing__desc'));
                $anchor = $div->appendElement('a', array('href'=>'#'));
                $anchor->appendElement('plaintext', array(), $row['message_text']);
                //$td->appendElement('plaintext', array(), FatDate::format($row['message_text'] , true));
                break;
            case 'message_date':
                $td->appendElement('span', array('class'=>'date'), FatDate::format($row['message_date'], true));
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));

                $li = $ul->appendElement("li", array('class'=>'droplink'));
                $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_View', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                $innerLi=$innerUl->appendElement('li');
                $innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('Messages', 'view', array($row['thread_id'])),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_View', $adminLangId)), Labels::getLabel('LBL_View', $adminLangId), true);

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

$postedData['page']=$page;

echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmSearchPaging'
));

$pagingArr = array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);

$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
