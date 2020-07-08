<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=>Labels::getLabel('LBL_ID',$adminLangId),
		'user_details'=>Labels::getLabel('LBL_User_Details',$adminLangId),	
		'user_balance' => Labels::getLabel('LBL_Balance',$adminLangId),
		'withdrawal_amount' => Labels::getLabel('LBL_Amount',$adminLangId),
		'withdrawal_payment_method'	=>	Labels::getLabel( 'LBL_Withdrawal_Mode', $adminLangId ),
		'account_details' => Labels::getLabel('LBL_Account_Details',$adminLangId),
		'withdrawal_request_date' => Labels::getLabel('LBL_Date',$adminLangId),
		'withdrawal_status' => Labels::getLabel('LBL_Status',$adminLangId),
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	if( $row['withdrawal_payment_method'] == 0 ){
		$row['withdrawal_payment_method'] = User::AFFILIATE_PAYMENT_METHOD_BANK;
	}
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	
	foreach ($arr_flds as $key=>$val){
		
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), '#'.str_pad($row["withdrawal_id"],6,'0',STR_PAD_LEFT));
			break;
			case 'user_details':
				$arr = User::getUserTypesArr($adminLangId);
				$str = '<br/>';
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
				
				$txt = '<strong>'.Labels::getLabel('LBL_N',$adminLangId).': </strong>'.$row["user_name"].'<br>';
				$txt .= '<strong>'.Labels::getLabel('LBL_U',$adminLangId).': </strong>'.$row["user_username"].'<br>';
				$txt .= '<strong>'.Labels::getLabel('LBL_E',$adminLangId).': </strong>'.$row["user_email"].'<br>';
				$txt .= '<strong>'.Labels::getLabel('LBL_User_Type', $adminLangId). ': </strong>'.$str;
				$td->appendElement('plaintext', array(), $txt,true);				
			break;	
			case 'user_balance':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['user_balance'],true,true));		
			break;	
			case 'withdrawal_amount':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['withdrawal_amount'],true,true));		
			break;
			case 'withdrawal_payment_method':
				
				$td->appendElement( 'plaintext', array(), User::getAffiliatePaymentMethodArr($adminLangId)[$row[$key]] );
			break;
			case 'account_details':
				$txt = '';
				switch( $row['withdrawal_payment_method'] ){
					case User::AFFILIATE_PAYMENT_METHOD_CHEQUE:
						$txt .= '<strong>' . Labels::getLabel('LBL_Cheque_Payee_Name', $adminLangId) . ': </strong>'.$row['withdrawal_cheque_payee_name'];
					break;
					
					case User::AFFILIATE_PAYMENT_METHOD_BANK:
						$txt = '<strong>'.Labels::getLabel('LBL_Bank_Name',$adminLangId).': </strong>'.$row["withdrawal_bank"].'<br>';
						$txt .= '<strong>'.Labels::getLabel('LBL_A/C_Name',$adminLangId).': </strong>'.$row["withdrawal_account_holder_name"].'<br>';
						$txt .= '<strong>'.Labels::getLabel('LBL_A/C_Number',$adminLangId).': </strong>'.$row["withdrawal_account_number"].'<br>';
						$txt .= '<strong>'.Labels::getLabel('LBL_IFSC_Code/Swift_Code',$adminLangId).': </strong>'.$row["withdrawal_ifc_swift_code"].'<br>';
						$txt .= '<strong>'.Labels::getLabel('LBL_Bank_Address',$adminLangId).': </strong>'.$row["withdrawal_bank_address"].'<br>';
						
					break;
					
					case User::AFFILIATE_PAYMENT_METHOD_PAYPAL:
						$txt .= '<strong>'.Labels::getLabel('LBL_Paypal_Email_Account', $adminLangId).': </strong>'.$row['withdrawal_paypal_email_id'];
					break;
				}
				
				$txt .= '<br><strong>'.Labels::getLabel('LBL_Comments',$adminLangId).': </strong>'.$row["withdrawal_comments"];
				
				$td->appendElement('plaintext', array(), $txt,true);		
			break;	
			case 'withdrawal_request_date':
				$td->appendElement('plaintext', array(),FatDate::format($row[$key]),true);	
			break;
			case 'withdrawal_status':
				$td->appendElement('plaintext', array(),$statusArr[$row['withdrawal_status']],true);	
			break;
			case 'action':
			
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				
				if($canEdit && $row['withdrawal_status'] == Transactions::STATUS_PENDING){
					
					$li = $ul->appendElement("li",array('class'=>'droplink'));						
    			    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
					$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));	
					$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));		

					$innerLi=$innerUl->appendElement('li');
					$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Approve',$adminLangId),"onclick"=>"updateStatus(".$row['withdrawal_id'].",".Transactions::WITHDRAWL_STATUS_APPROVED." , 'approve' )"),Labels::getLabel('LBL_Approve',$adminLangId), true);					
					
					$innerLi=$innerUl->appendElement('li');
					$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Decline',$adminLangId),"onclick"=>"updateStatus(".$row['withdrawal_id'].",".Transactions::WITHDRAWL_STATUS_DECLINED." , 'decline' )"),Labels::getLabel('LBL_Decline',$adminLangId), true);			
					
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
		'name' => 'frmReqSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>