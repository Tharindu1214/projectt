<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'listserial'=> Labels::getLabel('LBL_S.No.',$adminLangId),
	'user'=>Labels::getLabel('LBL_User',$adminLangId),
	'type'	=> Labels::getLabel('LBL_User_Type',$adminLangId),
	'user_regdate'=>Labels::getLabel('LBL_Reg._Date',$adminLangId),			
	'credential_verified'=>Labels::getLabel('LBL_verified',$adminLangId),	
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
			case 'user_regdate':
				$td->appendElement('plaintext',array(),FatDate::format($row[$key],true,true,
				FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())));
			break;
			case 'type':
				$str = '';
				$arr = User::getUserTypesArr($adminLangId);
				if( $row['user_is_buyer'] ){
					$str .= $arr[User::USER_TYPE_BUYER].'<br/>';
				}
				if( $row['user_is_supplier'] ){
					$str .= $arr[User::USER_TYPE_SELLER].'<br/>';
				}
				if( $row['user_is_advertiser'] ){
					$str .= $arr[User::USER_TYPE_ADVERTISER].'<br/>';
				}
				if( $row['user_is_affiliate'] ){
					$str .= $arr[User::USER_TYPE_AFFILIATE].'<br/>';
				}
				
				if( $str == '' && $row['user_registered_initially_for'] != 0 ){
					$str = '<span class="label label-danger">Signing Up For: '. User::getUserTypesArr($adminLangId)[$row['user_registered_initially_for']] .'</span>';
				}
				
				$td->appendElement('plaintext', array(), $str  ,true);
				
			break;
			case 'credential_verified':				
				$yesNoArr = applicationConstants::getYesNoArr($adminLangId);
				$td->appendElement('plaintext',array(),$yesNoArr[$row[$key]], true);
			break;				 
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				if($canEdit){		
					$li = $ul->appendElement("li",array('class'=>'droplink'));						
    			    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
					$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));	
					$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
              		
					$innerLi=$innerUl->appendElement('li');
					$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Restore_User',$adminLangId),"onclick"=>"restoreUser(".$row['user_id'].")"),Labels::getLabel('LBL_Restore_User',$adminLangId), true);	
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
		'name' => 'frmDeletedUserSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>