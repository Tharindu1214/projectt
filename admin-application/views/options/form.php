<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 6;


?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Option_Setup',$adminLangId); ?></h4>
    </div>
<div class="sectionbody space">
    <div class="border-box border-box--space">
        <div id="loadForm"><?php echo Labels::getLabel('LBL_LOADING',$langId);?></div>
    </div>    
    <span class="-gap"></span><span class="-gap"></span>
<?php if($option_id > 0){ ?>
<div class="<?php echo ($hideListBox == true)?'hide':''?>" id="showHideContainer" >
	
		    <div class="sectionhead" style=" padding-bottom:20px">
		    
			<h4><?php echo Labels::getLabel('LBL_OPTION_VALUE_LISTING',$langId);?></h4>
			<!--<a href="javascript:void(0)" class="link--underlined -float-right" 
			onClick="optionValueForm(<?php echo $option_id;?>,0)";><?php echo Labels::getLabel('LBL_ADD_NEW',$langId);?></a>-->
			<?php
				$ul = new HtmlElement( "ul",array("class"=>"actions actions--centered") );
				$li = $ul->appendElement("li",array('class'=>'droplink'));
				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
				$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
				$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
				$innerLiExport=$innerUl->appendElement('li');            
				$innerLiExport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_ADD_NEW',$adminLangId),"onclick"=>"optionValueForm('".$option_id."')"),Labels::getLabel('LBL_ADD_NEW',$adminLangId), true);
				
				echo $ul->getHtml(); 
			 ?>
			
    </div>
		
			<div class="border-box " >
				<div id="optionValueListing"></div>
			</div> 
		
	
</div>
<?php } ?></div></section>