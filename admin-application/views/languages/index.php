<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Manage_Language',$adminLangId); ?></h5>
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
							$search->setFormTagAttribute ( 'onsubmit', 'searchLanguage(this); return(false);');
							$search->setFormTagAttribute ( 'id', 'frmSearch' );
							$search->setFormTagAttribute ( 'class', 'web_form' );
							$search->developerTags['colClassPrefix'] = 'col-md-';					
							$search->developerTags['fld_default_col'] = 6;					
							
							$search->getField('keyword')->addFieldtagAttribute('class','search-input');
							$search->getField('btn_clear')->addFieldtagAttribute('onclick','clearSearch();');
							
							echo  $search->getFormHtml();
						?>    
					</div>
				</section>
				<section class="section">
					<div class="sectionhead">
						<h4><?php echo Labels::getLabel('LBL_Languages_Listing',$adminLangId); ?> </h4>
						<?php if($canEdit){
							$ul = new HtmlElement( "ul",array("class"=>"actions actions--centered") );
							$li = $ul->appendElement("li",array('class'=>'droplink'));
							$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
							$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
							$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
							$innerLiAddCat=$innerUl->appendElement('li');            
							$innerLiAddCat->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Blog_Post',$adminLangId),"onclick"=>"languageForm(0)"),Labels::getLabel('LBL_Add_Language',$adminLangId), true);
							echo $ul->getHtml();
						 } ?>
					</div>
					<div class="sectionbody">
						<div class="tablewrap" >
							<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
						</div> 
					</div>
				</section>
			</div>		
		</div>
	</div>
</div>