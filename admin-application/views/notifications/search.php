<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
		'check'=> '',
		'profile'=> '',
		'notification_label_key' => '',
		'notification_added_on' => '',
	);

$tbl = new HtmlElement('table', array('class'=>'table--listing','id'=>'post'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class'=>'tr--first'));

$sr_no = $page==1?0:$pageSize*($page-1);

foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	/* CommonHelper::printArray($labelArr); die; */

	if(!$row['notification_marked_read']){
		$tr = $tbl->appendElement('tr');
	}else{
		$tr = $tbl->appendElement('tr',array('class'=>'read'));
	}
	$uname = ($row['user_name'])?$row['user_name']:'- Guest User -';
	$url = CommonHelper::generateUrl($labelArr[$row['notification_label_key']][1]);
	/* $url = 'http://'.$_SERVER['SERVER_NAME'].CONF_WEBROOT_BACKEND.$labelArr[$row['notification_label_key']][1]; */
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'check':
				if($canEdit){
					$td->appendElement('plaintext',array('class'=>'td--check'),'<label class="checkbox"><input type="checkbox" class="check-record" rel='.$row['notification_id'].'><i class="input-helper"></i></label>',true);
				}
			break;
			case 'profile':
				$div = $td->appendElement('div', array('class'=>'avtar avtar--small'));
				$div->appendElement('img', array('src'=>CommonHelper::generateUrl('Image','user',array($row['notification_user_id'],'MINI',true),CONF_WEBROOT_FRONT_URL)));
			break;
			case 'notification_label_key':
				$td->appendElement('div', array('class'=>'listing__desc'),'<a href="javascript:void(0)" onclick=redirectfunc("'.$url.'","'.$row['notification_record_id'].'","'.$row['notification_id'].'") ><strong>'.$uname.'</strong></br>'.$labelArr[$row['notification_label_key']][0].'</a>',true);
			break;
			default:
				$td->appendElement('span',array('class'=>'date'),FatDate::format($row[$key],true,true,
				FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())));
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
}
echo $tbl->getHtml();

$postedData['page']= $page;

echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSearchPaging'
) );

$pagingArr = array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);

$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
