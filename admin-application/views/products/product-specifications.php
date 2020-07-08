
<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
 
$arr_lang_flds = array(
	
	'prodspec_name' => $languages[$adminLangId]
);
$arr_flds['action'] = Labels::getLabel('LBL_Action', $adminLangId);

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_lang_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}


foreach ($prodSpec as $key => $specification){
	
	$tr = $tbl->appendElement('tr');
	 
		$row = $specification[$adminLangId];
		// commonHelper::printArray($row);
			$td = $tr->appendElement('td');
			
				switch ($key){			
					
					default:
						$td->appendElement('plaintext', array(), $row['prodspec_name'].': '.$row['prodspec_value'],true);
					break;
				}
	
		foreach ($arr_flds as $key=>$val){
			$td = $tr->appendElement('td');
			switch ($key){
				
				case 'action':
					$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));

					$li = $ul->appendElement("li",array('class'=>'droplink'));
					

					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
              		$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
              		$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
              		
              		$innerLiEdit=$innerUl->appendElement('li');
					
					$innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
					'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"addProdSpec(".$productId.",".$row['prodspec_id'].")"),
					Labels::getLabel('LBL_Edit',$adminLangId), true);
              		$innerLiDelete=$innerUl->appendElement('li');
					$innerLiDelete->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
					'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteProdSpec(".$productId.",".$row['prodspec_id'].")"),
					Labels::getLabel('LBL_Delete',$adminLangId), true);
				break;
				default:
					$td->appendElement('plaintext', array(), '<span class="caption--td">'.$val.'</span>'.$row[$key],true);
				break;
			}
		}
	
	
}
if (count($prodSpec) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>3), Labels::getLabel('LBL_No_Specifications_found_under_your_product', $adminLangId));
}
echo $tbl->getHtml();
	?>
