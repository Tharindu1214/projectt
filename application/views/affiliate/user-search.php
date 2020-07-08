<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');

$yesNoArr = applicationConstants::getYesNoArr($siteLangId);

$arr_flds = array(
	'user_name'	=>	Labels::getLabel('LBL_User', $siteLangId),
	'credential_email'	=>	Labels::getLabel('LBL_Email', $siteLangId),
	'user_regdate'	=>	Labels::getLabel('Lbl_Registered_on', $siteLangId),
	'credential_active'		=>	Labels::getLabel('LBL_Active', $siteLangId),
	'credential_verified'	=>	Labels::getLabel('LBL_Verified', $siteLangId),
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($arr_listing as $sn => $row){
	$sr_no++;

	$tr = $tbl->appendElement('tr',array('class' =>'' ));

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'user_name':
				$txt = $row['user_name'];
				$txt .= '('. $row['credential_username'].')';
				$td->appendElement('plaintext', array(), $txt , true);
			break;
			case 'user_regdate':
				$td->appendElement('plaintext', array(), FatDate::format($row['user_regdate']) , true);
			break;
			case 'credential_active':
				$txt = isset($row['credential_active']) ? $yesNoArr[$row['credential_active']] : 'N/A';
				$td->appendElement('plaintext', array(), $txt , true);
			break;
			case 'credential_verified':
				$txt = isset($row['credential_verified']) ? $yesNoArr[$row['credential_verified']] : 'N/A';
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			default:
				$td->appendElement('plaintext', array(), ''.$row[$key],true);
			break;
		}
	}
}
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmUserSrchPaging') );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToUserSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
