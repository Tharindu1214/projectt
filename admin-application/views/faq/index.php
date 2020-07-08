<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class='page'>
	<div class='container container-fluid'>
		<div class="row">
			<div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Manage_FAQs',$adminLangId); ?> </h5>
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
					$srchFrm->setFormTagAttribute ( 'onsubmit', 'searchFaqs(this); return(false);');
					$srchFrm->setFormTagAttribute ( 'class', 'web_form' );
					$srchFrm->developerTags['colClassPrefix'] = 'col-md-';					
					$srchFrm->developerTags['fld_default_col'] = 6;					
					echo  $srchFrm->getFormHtml();
				?>    
			</div>
		</section> 
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Faq_List',$adminLangId); ?> </h4>

			<?php	$ul = new HtmlElement( "ul",array("class"=>"actions actions--centered") );
							$li = $ul->appendElement("li",array('class'=>'droplink'));
							$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
							$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
							$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
							$innerLiAddBack=$innerUl->appendElement('li'); 

							$url=CommonHelper::generateUrl('FaqCategories');
							$innerLiAddBack->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Back',$adminLangId),"onclick"=>"redirectUrl('".$url."')"),Labels::getLabel('LBL_Back',$adminLangId), true);

		/*	<a href="<?php echo CommonHelper::generateUrl('FaqCategories');?>" class="themebtn btn-default btn-sm" ><?php echo Labels::getLabel('LBL_Back',$adminLangId); ?></a>*/
			 if($canEdit){ 
					$innerLiAddBack->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Faq',$adminLangId),"onclick"=>"addFaqForm('".$faqcat_id."',0)"),Labels::getLabel('LBL_Add_Faq',$adminLangId), true);

			/*<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="faqForm(<?php echo $faqcat_id;?>,0)";><?php echo Labels::getLabel('LBL_Add_Faq',$adminLangId); ?></a>*/
			} 
							echo $ul->getHtml();

			?>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="listing"> <?php echo Labels::getLabel('LBL_Processing',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>
</div>
</div>