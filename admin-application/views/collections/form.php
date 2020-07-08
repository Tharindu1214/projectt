<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupCollection(this); return(false);');
$frmId = $frm->getFormTagAttribute('id');
$fld = $frm->getField('collection_criteria');
$fld->fieldWrapper = array('<div class="box--scroller">','</div>');

$fld = $frm->getField('collection_type');
$fld->fieldWrapper = array('<div class="box--scroller">','</div>');
$fld->addFieldTagAttribute('onChange', 'getCollectionTypeLayout("'.$frmId.'",this.value); ');

$fld = $frm->getField('collection_layout_type');
$fld->fieldWrapper = array('<div class="box--scroller">','</div>');

$criteria_fld = $frm->getField('collection_criteria');
$criteria_fld->setWrapperAttribute('id', 'collection_criteria_div');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

?> <section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Collection_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a class="active" href="javascript:void(0)" onclick="editCollectionForm(<?php echo $collection_id ?>);">
                            <?php echo Labels::getLabel('LBL_General', $adminLangId);?></a>
                        </li>
                        <?php
                        $inactive = ($collection_id == 0) ? 'fat-inactive' : '';
                        foreach ($languages as $langId => $langName) { ?>
                            <li class="<?php echo $inactive; ?>"><a href="javascript:void(0);"
                                <?php if ($collection_id > 0) { ?>
                                onclick="editCollectionLangForm(<?php echo $collection_id ?>, <?php echo $langId; ?>);"
                                <?php } ?>>
                            <?php echo Labels::getLabel('LBL_'.$langName, $adminLangId); ?></a>
                            </li>
                        <?php } ?>
                        <?php if (!in_array($collection_type, Collections::COLLECTION_WITHOUT_MEDIA)) { ?>
                            <li>
                                <a class="<?php  echo $inactive; ?>" href="javascript:void(0)"
                                    <?php if ($collection_id > 0) { ?>
                                        onclick="collectionMediaForm(<?php echo $collection_id ?>);"
                                    <?php } ?>>
                                        <?php echo Labels::getLabel('LBL_Media', $adminLangId);  ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel"> <?php echo $frm->getFormHtml(); ?> </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function() {
                    callCollectionTypePopulate(<?php echo $collection_type;?>);
                });
            </script>
