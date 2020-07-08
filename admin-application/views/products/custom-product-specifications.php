<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>

<section class="section">
<div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_Product_Specifications',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
 <div class="row">
<div class="col-sm-12">
	<div class="tabs_nav_container responsive ">
	    <div class="border-box border-box--space">
		
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<div id="loadForm"><?php echo Labels::getLabel('LBL_LOADING',$adminLangId);?></div>
			</div>
		</div>
	</div>
</div>
</div>
<?php if($product_id > 0){


            $ul = new HtmlElement( "ul",array("class"=>"actions actions--centered") );
            $li = $ul->appendElement("li",array('class'=>'droplink'));
            		    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
                     		$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
                     		$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
							$innerLiAddCat=$innerUl->appendElement('li');            
              				
              				 $innerLiAddCat->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_ADD_NEW',$adminLangId),"onclick"=>"addProdSpec(".$product_id.")"),Labels::getLabel('LBL_ADD_NEW',$adminLangId), true);
 ?>
<div class="col-sm-12 <?php echo ($hideListBox == true)?'hide':'' ?>" id="showHideContainer" >
	<section class="section">
		<div class="sectionhead" style="margin-bottom: 10px">
			<h4><?php echo Labels::getLabel('LBL_Specifications_Listing',$adminLangId);?></h4>
			<!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" 
			onClick="addProdSpec(<?php echo $product_id;?>)"><?php echo Labels::getLabel('LBL_ADD_NEW',$adminLangId);?></a>-->
			<?php echo $ul->getHtml(); ?>
		</div>
		<div class="gap clearfix"></div>
		<div class="sectionbody space">
		<div class="border-box border-box--space">

			<div class="tablewrap" >
				<div id="product_specifications_list"></div>
			</div> 
		</div>
		</div>
	</section>
</div>
<?php } ?>
</div>
</div>
</section>
