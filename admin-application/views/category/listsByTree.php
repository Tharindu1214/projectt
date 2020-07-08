<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

/*
$html = new HtmlElement('ul',array('class'=>'list-box'));
foreach($data as $cat_data){
	$li = $html->appendElement('li',array('id'=>$cat_data['category_slug'].'-wrapper'));
	$li->appendElement('img',array('src'=>CommonHelper::generateFullUrl('category','photo',array($cat_data['category_id'],50,50))));
	$li->appendElement('a',array('href'=>'Javascript:;', 'id'=>$cat_data['category_slug'],'onclick'=>"getChild(this, '".$cat_data['category_id']."')"),$cat_data['category_name']);
}
*/
echo $html->getHtml();


?>

