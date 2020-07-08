<?php
    defined('SYSTEM_INIT') or die('Invalid Usage.');
    $blockLangFrm->setFormTagAttribute('class', 'web_form layout--'.$formLayout);
    $blockLangFrm->setFormTagAttribute('onsubmit', 'setupBlockLang(this); return(false);');

    $blockLangFrm->developerTags['colClassPrefix'] = 'col-md-';
    $blockLangFrm->developerTags['fld_default_col'] = 12;


    $edFld = $blockLangFrm->getField('epage_content');
    if ($epageData['epage_content_for'] == Extrapage::CONTENT_IMPORT_INSTRUCTION) {
        $epage_label = $blockLangFrm->getField('epage_label');
        $epage_content = $blockLangFrm->getField('epage_content');
        $epage_label->changeCaption(Labels::getLabel('LBL_Section_Title', $adminLangId));
        $epage_content->changeCaption(Labels::getLabel('LBL_Section_Content', $adminLangId));
    }
    $edFld->htmlBeforeField = '<br/><a class="themebtn btn-primary" onClick="resetToDefaultContent();" href="javascript:void(0)">Reset Editor Content to default</a>';

    if (array_key_exists($epageData['epage_id'], $contentBlockArrWithBg)) {
        $fld = $blockLangFrm->getField('cblock_bg_image');
        $fld->addFieldTagAttribute('class', 'btn btn--primary btn--sm');

        $preferredDimensionsStr = '<small class="text--small"> '.Labels::getLabel('LBL_This_will_be_displayed_on_Registration_Page', $adminLangId).'</small>';

        $htmlAfterField = $preferredDimensionsStr;
        /* CommonHelper::printArray($bgImages);die; */
        if (!empty($bgImages)) {
            $htmlAfterField .= '<ul class="image-listing grids--onethird">';
            foreach ($bgImages as $bgImage) {
                $htmlAfterField .= '<li>'.$bannerTypeArr[$bgImage['afile_lang_id']].'<div class="uploaded--image"><img src="'.CommonHelper::generateFullUrl('image', 'cblockBackgroundImage', array($epageData['epage_id'],$bgImage['afile_lang_id'],'THUMB',$bgImage['afile_type']), CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeBgImage('.$bgImage['afile_record_id'].','.$bgImage['afile_lang_id'].','.$bgImage['afile_type'].')" class="remove--img"><i class="ion-close-round"></i></a></div>';
            }
            $htmlAfterField.='</li></ul>';
        } else {
            $htmlAfterField.='<div class="temp-hide"><ul class="image-listing grids--onethird"><li><div class="uploaded--image"></div></li></ul></div>';
        }
        $fld->htmlAfterField = $htmlAfterField;
    }
    ?>
<!-- editor's default content[ -->

<div id="editor_default_content" style="display:none;">
    <?php echo (isset($epageData))?html_entity_decode($epageData['epage_default_content']):'';?>
</div>
<!-- ] -->
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Content_Block_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <?php
                            if ($epageData['epage_content_for'] != Extrapage::CONTENT_IMPORT_INSTRUCTION) { ?>
		                        <li>
									<a href="javascript:void(0);" onclick="addBlockForm(<?php echo $epage_id ?>);">
										<?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
									</a>
								</li>
                        <?php } ?>
                        <?php
                            if ($epage_id > 0) {
                                foreach ($languages as $langId => $langName) { ?>
                        			<li>
										<a class="<?php echo ($epage_lang_id == $langId)?'active':''?>" href="javascript:void(0);" onclick="addBlockLangForm(<?php echo $epage_id ?>, <?php echo $langId;?>);">
											<?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?>
										</a>
									</li>
                        <?php 	}
                            }
                            ?>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php
                                echo $blockLangFrm->getFormTag();
                                echo $blockLangFrm->getFormHtml(false);
                                echo '</form>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
