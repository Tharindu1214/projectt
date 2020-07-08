<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form ');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 6;
$frm->setFormTagAttribute('onSubmit', 'exportMedia(this,'.$actionType.'); return false;');
if ($actionType == Importexport::TYPE_PRODUCTS || $actionType == Importexport::TYPE_SELLER_PRODUCTS) {
    $startIdFld = $frm->getField('start_id');
    $startIdFld->setWrapperAttribute('class', 'range_fld');

    $endIdFld = $frm->getField('end_id');
    $endIdFld->setWrapperAttribute('class', 'range_fld');

    $batchCountFld = $frm->getField('batch_count');
    $batchCountFld->setWrapperAttribute('class', 'batch_fld');

    $batchNumberFld = $frm->getField('batch_number');
    $batchNumberFld->setWrapperAttribute('class', 'batch_fld');

    $rangeTypeFld = $frm->getField('export_data_range');
    $rangeTypeFld->setfieldTagAttribute('onchange', "showHideExtraFld(this.value,".Importexport::BY_ID_RANGE.",".Importexport::BY_BATCHES.");");
}
?>
     <div class="tabs tabs-sm tabs--scroll clearfix">
        <ul>
            <li ><a class="is-active" href="javascript:void(0);" onclick="exportForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Content', $siteLangId); ?></a></li>
            <li class="is-active"><a href="javascript:void(0);" onclick="exportMediaForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        </ul>
    </div>
<div class="form__subcontent">
    <?php echo $frm->getFormHtml(); ?>
</div>
