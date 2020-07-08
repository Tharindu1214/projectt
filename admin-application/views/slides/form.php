<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$slideFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$slideFrm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
$slideFrm->developerTags['colClassPrefix'] = 'col-md-';
$slideFrm->developerTags['fld_default_col'] = 12;

$slide_identifier = $slideFrm->getField('slide_identifier');
$slide_identifier->setUnique('tbl_slides', 'slide_identifier', 'slide_id', 'slide_id', 'slide_id');

$extUrlField = $slideFrm->getField('slide_url');
$extUrlField->addFieldTagAttribute('placeholder', 'http://');
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Slide_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a class="active" href="javascript:void(0)" onclick="slideForm(<?php echo $slide_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <?php $inactive = ($slide_id == 0) ? 'fat-inactive' : '';
                        foreach ($languages as $langId => $langName) { ?>
                            <li class="<?php echo $inactive; ?>"><a href="javascript:void(0);"
                                <?php if ($slide_id > 0) {
                                    ?> onclick="slideLangForm(<?php echo $slide_id ?>, <?php echo $langId; ?>);" <?php
                                } ?>>
                                <?php echo Labels::getLabel('LBL_'.$langName, $adminLangId); ?></a></li>
                        <?php } ?>
                            <li class="<?php echo $inactive;?>">
                                <a href="javascript:void(0)"
                                    <?php if ($slide_id > 0) { ?>
                                        onclick="slideMediaForm(<?php echo $slide_id ?>);"
                                    <?php } ?> >
                                    <?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
                                </a>
                            </li>
                        </ul>
                        <div class="tabs_panel_wrap">
                            <div class="tabs_panel">
                                <?php echo $slideFrm->getFormHtml(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
