<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupAbusiveWords(this); return(false);');
$frm->setFormTagAttribute('id', 'frmAbusiveWord');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
	
$abusive_lang_id_fld = $frm->getField('abusive_lang_id');
$abusive_lang_id_fld->addFieldTagAttribute( 'onChange', 'changeFormLayOut(this);' );

?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Abusive_Keyword_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="border-box border-box--space">
			<?php echo $frm->getFormHtml(); ?>
		</div>
	</div>												
</section>

<script type="text/javascript">
</script>