<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLangCollection(this); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;

?> <section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Collection_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="editCollectionForm(<?php echo $collectionId ?>);"> <?php echo Labels::getLabel('LBL_General', $adminLangId);?></a></li> <?php
                            if ($collectionId > 0) {
                                foreach ($languages as $langId=>$langName) {
                                    ?> <li><a class="<?php echo ($lang_id == $langId) ? 'active' : '' ?>" href="javascript:void(0);"
                                onclick="editCollectionLangForm(<?php echo $collectionId ?>, <?php echo $langId; ?>);"><?php echo Labels::getLabel('LBL_'.$langName, $adminLangId); ?></a></li> <?php
                                }
                            }
                            ?>
                        <?php if (!in_array($collectionType, Collections::COLLECTION_WITHOUT_MEDIA)) { ?>
                            <li><a href="javascript:void(0)" onclick="collectionMediaForm(<?php  echo $collectionId ?>);"><?php echo Labels::getLabel('LBL_Media', $adminLangId);  ?></a></li>
                        <?php } ?>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel"> <?php echo $langFrm->getFormHtml(); ?> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
