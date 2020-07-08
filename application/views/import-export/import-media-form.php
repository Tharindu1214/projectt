<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
$frm->developerTags['fld_default_col'] = 6;
$frm->setFormTagAttribute( 'onSubmit', 'importFile("importMedia",'.$actionType.'); return false;' );
?>
<div class="tabs tabs-sm tabs--scroll clearfix">
    <ul>
        <li><a href="javascript:void(0);" onclick="getInstructions('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Instructions',$siteLangId); ?></a></li>
        <li><a href="javascript:void(0);" onclick="importForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Content',$siteLangId); ?></a></li>
        <li class="is-active"><a class="is-active" href="javascript:void(0);" onclick="importMediaForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
    </ul>
</div>
<div class="form__subcontent">
	<?php echo $frm->getFormHtml(); ?>
</div>
