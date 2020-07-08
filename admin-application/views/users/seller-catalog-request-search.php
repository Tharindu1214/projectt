<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=>Labels::getLabel('LBL_Sr._No',$adminLangId),
		'scatrequest_reference'=>Labels::getLabel('LBL_Reference_Number',$adminLangId),			
		'user_name'=>Labels::getLabel('LBL_Name',$adminLangId),			
		'user_details'=>Labels::getLabel('LBL_Username/Email',$adminLangId),			
		'scatrequest_date'=>Labels::getLabel('LBL_Requested_On',$adminLangId),			
		'status'=>Labels::getLabel('LBL_Status',$adminLangId),			
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
$tbl = new HtmlElement('table', 
array('width'=>'100%', 'class'=>'table table-responsive'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){ 
	$sr_no++;
	$tr = $tbl->appendElement('tr');

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'user_details':
				$td->appendElement('plaintext', array(), '<strong>'.Labels::getLabel('LBL_U',$adminLangId).': </strong> '.$row['credential_username'],true);
				$td->appendElement('br', array());
				$td->appendElement('plaintext', array(), '<strong>'.Labels::getLabel('LBL_E',$adminLangId).': </strong> '.$row['credential_email'],true);
			break;
			case 'status':
				$td->appendElement('label', array('class'=>'label label-'.$reqStatusClassArr[$row['scatrequest_status']].''), $reqStatusArr[$row['scatrequest_status']],true);
				
			break;	
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				
				$li = $ul->appendElement("li",array('class'=>'droplink'));						
				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
				$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));	
				$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));				
				
				if($canViewSellerCatalogRequests){					
					$innerLi=$innerUl->appendElement('li');
					$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_View',$adminLangId),"onclick"=>"viewCatalogRequest(".$row['scatrequest_id'].")"),Labels::getLabel('LBL_View',$adminLangId), true);						
				}
				if($canEditSellerCatalogRequests && $row['scatrequest_status'] == User::CATALOG_REQUEST_PENDING){						
					$innerLi=$innerUl->appendElement('li');
					$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Change_Status',$adminLangId),"onclick"=>"updateCatalogRequestForm(".$row['scatrequest_id'].")"),Labels::getLabel('LBL_Change_Status',$adminLangId), true);							
				}
				if($canEditSellerCatalogRequests){
					$innerLi=$innerUl->appendElement('li');
					$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Message_Seller',$adminLangId),"onclick"=>"sellerCatalogRequestMsgForm(".$row['scatrequest_id'].")"),Labels::getLabel('LBL_Message_Seller',$adminLangId), true);						
				}				
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array(
	'colspan'=>count($arr_flds)), 
	Labels::getLabel('LBL_No_Records_Found',$adminLangId)
	);
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>