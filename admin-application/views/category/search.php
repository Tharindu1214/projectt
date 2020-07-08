<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$table = new HtmlElement('table', array('border'=>'1'));
$header = array(
				'category_image'=> Labels::getLabel('LBL_Photo',$adminLangId),
				'category_id'=> Labels::getLabel('LBL_Catagory_ID',$adminLangId),
				'category_name'=> Labels::getLabel('LBL_Name',$adminLangId),
				'category_slug'=> Labels::getLabel('LBL_Slug',$adminLangId),
				//'category_parent'=>'Parent',
				'category_status'=> Labels::getLabel('LBL_Status',$adminLangId),
				'action'=> Labels::getLabel('LBL_Action',$adminLangId)
				);
$tr = $table->appendElement('tr');
foreach($header as $key=>$field_text){
	$tr->appendElement('th','',$field_text);
}
foreach($data as $lists){
	$tr = $table->appendElement('tr');
	foreach($header as $field=>$field_text){
		switch ($field){
			case 'category_image':
				$td = $tr->appendElement('td',array('align'=>'center'));
				$td->appendElement('img',array('src'=>CommonHelper::generateFullUrl('category','photo',array($lists['category_id'],100,100))));
				break;
			case 'category_status':
				$tr->appendElement('td',array('align'=>'center'),$lists[$field]==1?'Active':'Inactive');
				break;
			/*case 'category_parent':
				$tr->appendElement('td',array('align'=>'center'),$lists[$field] != 0?$data[$lists['category_parent']]['category_name']:'--');
				break;*/
			case 'action':
				$td = $tr->appendElement('td',array('align'=>'center'));
				$td->appendElement('a',array('href'=>CommonHelper::generateFullUrl('category','edit-form',array('category_id'=>$lists['category_id']))),'Edit');
				break;
			
			default:
				$tr->appendElement('td',array('align'=>'center'),$lists[$field]);
		}
		
	}
}

echo $table->getHtml();
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmCategorySearchPaging' 
) ); 
if($totalPage>1){
	echo FatUtility::getPageString(' <a href="javascript:void(0);" onclick="showCategorySearchPage(xxpagexx);">xxpagexx</a>', 
			$totalPage, $page, $lnkcurrent = ' xxpagexx', ' ... ', 1, 
			' <a href="javascript:void(0);" onclick="showCategorySearchPage(xxpagexx);">First</a>', 
			' <a href="javascript:void(0);" onclick="showCategorySearchPage(xxpagexx);">Last</a>', 
			' <a href="javascript:void(0);" onclick="showCategorySearchPage(xxpagexx);">Pre</a>', 
			' <a href="javascript:void(0);" onclick="showCategorySearchPage(xxpagexx);">Next</a>');
}

