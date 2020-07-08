<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		
		'tcolor_name'=> Labels::getLabel('LBL_Theme_Color',$adminLangId),	
		'tcolor_first_color'=> Labels::getLabel('LBL_Primary_Color',$adminLangId),	
		'tcolor_color'=> Labels::getLabel('LBL_Color',$adminLangId),	
		'action' =>  Labels::getLabel('LBL_Action',$adminLangId),
	);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hoevered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$tr->setAttribute ("id",$row['tcolor_id']);

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			 case 'tcolor_name':
			 $activeString = (FatApp::getConfig("CONF_FRONT_THEME",FatUtility::VAR_INT,1)==$row['tcolor_id'])?' <i class="icon ion-checkmark-circled is--active"></i>':'' ;
					$td->appendElement('plaintext', array(),$row['tcolor_name'].$activeString , 
					true);				
				
			 break;	
			 case 'tcolor_color':
				$ul = $td->appendElement("ul",array("class"=>"colorpallets"));
				if($canEdit){
					$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green'
					,"style"=>"background-color:#".$row['tcolor_first_color'],"onclick"=>"ActivateTheme(".$row['tcolor_id'].")",),'' , 
					true);
				}
			 break;		
			case 'action':
				//$ul = $td->appendElement("ul",array("class"=>"actions"));
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				$li = $ul->appendElement("li",array('class'=>'droplink'));

				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
              		$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
              		$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
	
				if($canEdit){
					
						
					if($row['tcolor_added_by']>0){
						$innerLiEdit=$innerUl->appendElement('li');

						//$li = $ul->appendElement("li");
						$innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 
						'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"editThemeColorFormNew(".$row['tcolor_id'].")"),Labels::getLabel('LBL_Edit',$adminLangId), 
						true);
						$innerLiDelete=$innerUl->appendElement('li');
						//$li = $ul->appendElement("li");
						$innerLiDelete->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 
						'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteTheme(".$row['tcolor_id'].")"),Labels::getLabel('LBL_Delete',$adminLangId), 
						true);	
					}
					$innerLiClone=$innerUl->appendElement('li');
					//$li = $ul->appendElement("li");
					$innerLiClone->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 
					'title'=>Labels::getLabel('LBL_Clone',$adminLangId),"onclick"=>"cloneForm(".$row['tcolor_id'].")"),Labels::getLabel('LBL_Clone',$adminLangId), 
					true);	
					$innerLiPreview=$innerUl->appendElement('li');
					//$li = $ul->appendElement("li");
					$url=CommonHelper::generateUrl('themeColor','preview',array($row['tcolor_id']));
					$innerLiPreview->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 
					'title'=>Labels::getLabel('LBL_Preview',$adminLangId),'onclick'=>'redirectPreview("'.$url.'")'),Labels::getLabel('LBL_Preview',$adminLangId), 
					true);	
					 $activeString = (FatApp::getConfig("CONF_FRONT_THEME")==$row['tcolor_id'])?' is--active':'' ;
					 $funString = (FatApp::getConfig("CONF_FRONT_THEME")==$row['tcolor_id'])?' javascript:void(0)':"ActivateTheme(".$row['tcolor_id'].")" ;
					 $titleStr = (FatApp::getConfig("CONF_FRONT_THEME")==$row['tcolor_id'])?Labels::getLabel('LBL_Activated',$adminLangId):Labels::getLabel('LBL_Click_To_Activate',$adminLangId) ;
					//$innerLiEdit=$innerUl->appendElement('li');

					$innerLiActivate=$innerUl->appendElement('li');
					//$li = $ul->appendElement("li");
					$innerLiActivate->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>"button small $activeString", 
					'title'=>$titleStr,"onclick"=>$funString),Labels::getLabel('LBL_Click_To_Activate',$adminLangId), 
					true);			
				}
			break;
			
			default:
				$td->appendElement('plaintext', array(), $row[$key],true);
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
		'name' => 'frmThemeColorSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>