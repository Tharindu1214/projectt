<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                            <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Options', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search..', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $frmSearch->setFormTagAttribute('onsubmit', 'searchOptions(this); return(false);');
                            $frmSearch->setFormTagAttribute('class', 'web_form');
                            $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                            $frmSearch->developerTags['fld_default_col'] = 6;

                            $fld_keyword = $frmSearch->getField('keyword');
                            $fld_keyword->addFieldTagAttribute('class', 'search-input');

                            $btn_clear = $frmSearch->getField('btn_clear');
                            $btn_clear->addFieldTagAttribute('onclick', 'clearOptionSearch()');
                            echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Options_List', $adminLangId); ?> </h4>
                        <?php
                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                                $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                                $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                            //$innerLi=$innerUl->appendElement('li');
                        ?>
                        <?php if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0) && $canView) {
                            $innerLiExport=$innerUl->appendElement('li');
                            $innerLiExport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Export', $adminLangId),"onclick"=>"addExportForm(".Importexport::TYPE_OPTIONS.")"), Labels::getLabel('LBL_Export', $adminLangId), true); ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="exportForm(<?php echo Importexport::TYPE_OPTION_VALUES; ?>)";><?php echo Labels::getLabel('LBL_Export_Option_Value', $adminLangId); ?></a>-->
                        <?php } ?>
                        <?php if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0) && $canEdit) {
                            $innerLiImport=$innerUl->appendElement('li');
                            $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Import', $adminLangId),"onclick"=>"addImportForm(". Importexport::TYPE_OPTIONS.")"), Labels::getLabel('LBL_Import', $adminLangId), true); ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="importForm(<?php echo Importexport::TYPE_OPTION_VALUES; ?>)";><?php echo Labels::getLabel('LBL_Import_Option_Value', $adminLangId); ?></a>-->
                        <?php }
                        if ($canEdit) {
                            $innerLi=$innerUl->appendElement('li');
                            $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteSelected()"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                            
                            $innerLiAddCat=$innerUl->appendElement('li');
                            $innerLiAddCat->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_New_Option', $adminLangId),"onclick"=>"addOptionFormNew(0)"), Labels::getLabel('LBL_Add_New_Option', $adminLangId), true); ?>
                        <?php }
                        echo $ul->getHtml(); ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap" >
                            <div id="optionListing"> <?php echo Labels::getLabel('LBL_Processing', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
