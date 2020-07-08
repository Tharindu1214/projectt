<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$ul = new HtmlElement("ul",array("class"=>"columlist"));
if(count($arr_listing) > 0){
	foreach($arr_listing as $key=>$row){
		$li = $ul->appendElement("li");
		
		$img = '<img src="'.CommonHelper::generateFullUrl('Image','user',array($row['notification_user_id'],'MINI',true),CONF_WEBROOT_FRONT_URL).'" />';
		$uname = ($row['user_name'])?$row['user_name']:'- Guest User -';
		
		$url = CommonHelper::generateUrl($labelArr[$row['notification_label_key']][1]);
		/* $url = 'http://'.$_SERVER['SERVER_NAME'].CONF_WEBROOT_BACKEND.$labelArr[$row['notification_label_key']][1]; */
		$li->appendElement('a', array('href'=>'javascript:void(0)','onclick'=>'redirectfunc("'.$url.'","'.$row['notification_record_id'].'","'.$row['notification_id'].'")'),'<span class="grid first"><figure class="avtar">'.$img.'</figure></span><span class="grid"><span class="name">'.$uname.'</span><span class="desc">'.$labelArr[$row['notification_label_key']][0].'</span></span>', true);	
	}
}else{
	$li = $ul->appendElement("li",array('class'=>'padd15'));
	$li->appendElement('plaintext',array(),Labels::getLabel('MSG_currently_no_new_notification',$adminLangId),true);
}

echo $ul->getHtml();

/* testing cvhange*/
?>