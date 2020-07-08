<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="tabs_panel">
<div class="row">
	<div class="col-sm-12"> 
		<h4><?php echo Labels::getLabel('LBL_Manage_Meta_Tags',$adminLangId); ?> </h4>
		<?php if(!empty($frmSearch)){ ?>
		<?php if($toShowForm){ ?>
		<section class="section searchform_filter">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 
					$frmSearch->addFormTagAttribute('class' ,'web_form');
					$frmSearch->addFormTagAttribute('onsubmit' ,'searchMetaTag(this);return false;');
					$frmSearch->setFormTagAttribute ( 'id', 'frmSearch' );
					$frmSearch->developerTags['colClassPrefix'] = 'col-md-';					
					$frmSearch->developerTags['fld_default_col'] = 6;	

					($frmSearch->getField('keyword')) ? $frmSearch->getField('keyword')->addFieldtagAttribute('class','search-input') : NUll;
					($frmSearch->getField('hasTagsAssociated')) ? $frmSearch->getField('hasTagsAssociated')->addFieldtagAttribute('class','search-input') : NUll;
					
					($frmSearch->getField('btn_clear')) ? $frmSearch->getField('btn_clear')->addFieldtagAttribute('onclick','clearSearch();') :  NULL;
					
					echo  $frmSearch->getFormHtml();
				?>    
			</div>
		</section>
		<?php }
		else{
			echo $frmSearch->getFormHtml();
		} 
		?>
		<?php } ?>
	</div>
	<div class="col-sm-12">
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Meta_Tags_Listing',$adminLangId); ?></h4>
			<?php if(isset($canAddNew) && $canAddNew ==true){



							$ul = new HtmlElement( "ul",array("class"=>"actions actions--centered") );
							$li = $ul->appendElement("li",array('class'=>'droplink'));
							$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
							$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
							$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
							$innerLiAddCat=$innerUl->appendElement('li');            
							$innerLiAddCat->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Meta_Tag',$adminLangId),"onclick"=>"addMetaTagForm(0,'".$metaType."',0)"),Labels::getLabel('LBL_Add_Meta_Tag',$adminLangId), true);
							
							echo $ul->getHtml();
			/*<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="metaTagForm(0 , <?php echo "'$metaType'"; ?> ,0)";><?php echo Labels::getLabel('LBL_Add_Meta_Tag',$adminLangId); ?></a>*/	
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