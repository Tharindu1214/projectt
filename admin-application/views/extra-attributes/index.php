<?php 
$fld_keyword = $frmSearch->getField('keyword');
$fld_keyword->addFieldTagAttribute('class', 'search-input');

$btn_clear = $frmSearch->getField('btn_clear');
$btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Manage_Attributes',$adminLangId); ?> (<?php echo $extraAttrGroupdata['eattrgroup_identifier'];?>)</h1>
			<section class="section ">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 	
					$frmSearch->setFormTagAttribute ( 'class', 'web_form last_td_nowrap' );
					$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchListing(this); return(false);');
					$frmSearch->setFormTagAttribute ( 'class', 'web_form' );					
					$frmSearch->developerTags['colClassPrefix'] = 'col-md-';	
					$frmSearch->developerTags['fld_default_col'] = 6;
					echo  $frmSearch->getFormHtml();
				?>    
			</div>
		</section> 
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Attribute_List',$adminLangId); ?></h4>
			<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="addForm(<?php echo $eattrgroup_id;?>,0)";><?php echo Labels::getLabel('LBL_Add_Attributes',$adminLangId); ?></a>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>