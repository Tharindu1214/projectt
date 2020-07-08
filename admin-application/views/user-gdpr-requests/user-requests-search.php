<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'listserial'=> Labels::getLabel('LBL_S.No.',$adminLangId),
	'user'=>Labels::getLabel('LBL_User',$adminLangId),
	'ureq_type'	=> Labels::getLabel('LBL_Request_Type',$adminLangId),
	'ureq_date'=>Labels::getLabel('LBL_Request_Date',$adminLangId),
	'ureq_status'	=> Labels::getLabel('LBL_Request_Status',$adminLangId),
	'action' => Labels::getLabel('LBL_Action',$adminLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1 ? 0: $pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	
	$sr_no++;
	$tr = $tbl->appendElement('tr', array( ) );
	
	foreach ( $arr_flds as $key => $val ){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'user':
				$userDetail = '<strong>'.Labels::getLabel('LBL_N:', $adminLangId).' </strong>'.$row['user_name'].'<br/>';
				$userDetail .= '<strong>'.Labels::getLabel('LBL_UN:', $adminLangId).' </strong>'.$row['credential_username'].'<br/>';
				$userDetail .= '<strong>'.Labels::getLabel('LBL_Email:', $adminLangId).' </strong>'.$row['credential_email'].'<br/>';
				$userDetail .= '<strong>'.Labels::getLabel('LBL_User_ID:', $adminLangId).' </strong>'.$row['user_id'].'<br/>';
				$td->appendElement( 'plaintext', array(), $userDetail, true );
			break;
			case 'ureq_date':
				$td->appendElement('plaintext',array(),FatDate::format($row[$key],true,true,
				FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())));
			break;
			case 'ureq_type':
			
				$str = $userRequestTypeArr[UserGdprRequest::TYPE_DATA_REQUEST];
				if( $row['ureq_type'] == UserGdprRequest::TYPE_TRUNCATE){
					$str = $userRequestTypeArr[UserGdprRequest::TYPE_TRUNCATE];
				}
				
				$td->appendElement('plaintext', array(), $str  ,true);
				
			break;
			case 'ureq_status':
				$str = $userRequestStatusArr[UserGdprRequest::STATUS_PENDING];
				if( $row['ureq_status'] == UserGdprRequest::STATUS_COMPLETE){
					$str = $userRequestStatusArr[UserGdprRequest::STATUS_COMPLETE];
				}
				
				$td->appendElement('plaintext', array(), $str  ,true);
				
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				if($canEdit){
					if($row['ureq_status']==UserGdprRequest::STATUS_PENDING){
					$li = $ul->appendElement("li",array('class'=>'droplink'));						
    			    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
					$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));	
					$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));	
					
					if( $row['ureq_type'] == UserGdprRequest::TYPE_TRUNCATE ){
						$innerLi=$innerUl->appendElement('li');
						$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Truncate_User_Data',$adminLangId),"onclick"=>"truncateUserData(".$row['user_id'].",".$row['ureq_id'].")"),Labels::getLabel('LBL_Truncate_User_Data',$adminLangId), true);
					}
					
					if( $row['ureq_type'] == UserGdprRequest::TYPE_DATA_REQUEST ){
						$innerLi=$innerUl->appendElement('li');
						$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Change_Status_To_Complete',$adminLangId),"onclick"=>"updateRequestStatus(".$row['ureq_id'].",".UserGdprRequest::STATUS_COMPLETE.")"),Labels::getLabel('LBL_Complete',$adminLangId), true);
						
						$innerLi=$innerUl->appendElement('li');
						$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_View_Purpose',$adminLangId),"onclick"=>"viewRequestPurpose(".$row['ureq_id'].")"),Labels::getLabel('LBL_View_Purpose',$adminLangId), true);
					}
					
						/* $innerLi=$innerUl->appendElement('li');
						$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete_Request',$adminLangId),"onclick"=>"deleteUserRequest(".$row['ureq_id'].")"),Labels::getLabel('LBL_Delete_Request',$adminLangId), true); */
					}
				}
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmUserSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>