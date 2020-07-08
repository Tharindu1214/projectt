<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?><?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
       
<!--main panel start here-->
<div class="page">
	<div class="container container-fluid">
		<div class="row">
		   <div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Tooltips',$adminLangId); ?></h5>
								<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
					</div>
					
				</div>
				<section class="section searchform_filter">
					<div class="sectionhead">
						<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
					</div>
					<div class="sectionbody space togglewrap" style="display:none;">
						<?php 
							$search->setFormTagAttribute ( 'onsubmit', 'listTooltips(this,1); return(false);');
							$search->setFormTagAttribute ( 'class', 'web_form' );
							$search->developerTags['colClassPrefix'] = 'col-md-';					
							$search->developerTags['fld_default_col'] = 6;					

							echo  $search->getFormHtml();
						?>
					</div>
				</section>				
				<section class="section">
					<div class="sectionhead">
						<h4><?php echo Labels::getLabel('LBL_Tooltip_List',$adminLangId); ?> </h4>
						<?php 
 							$ul = new HtmlElement("ul",array("class"=>"actions actions--centered"));
							$li = $ul->appendElement("li",array('class'=>'droplink'));						
							$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Tooltip',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
							$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));	
							$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
							$innerLi=$innerUl->appendElement('li');
							$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Tooltip',$adminLangId),"onclick"=>"addTooltip(0)"),Labels::getLabel('LBL_Add_Tooltip',$adminLangId), true);	
							echo $ul->getHtml();	 
						?>
					</div>
					<div class="sectionbody">
						<div class="tablewrap">
							<div id="tooltipListing">
								<?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?>
							</div>
						</div>
					</div>
				</section>
			</div>    
		</div>
	</div>
</div>  

<!--main panel end here-->
        
 
