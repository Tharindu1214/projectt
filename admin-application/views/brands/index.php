<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5>
                                <?php echo Labels::getLabel('LBL_Manage_Product_Brands', $adminLangId); ?>
                                <?php echo (isset($prodBrandData['brand_identifier']))?$prodBrandData['brand_identifier']:'';?>
                            </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <!---<div class="col-sm-12">-->
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?> </h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $search->setFormTagAttribute('onsubmit', 'searchProductBrands(this); return(false);');
                            $search->setFormTagAttribute('class', 'web_form');
                            $search->developerTags['colClassPrefix'] = 'col-md-';
                            $search->developerTags['fld_default_col'] = 6;
                            echo  $search->getFormHtml();
                        ?>
                    </div>
                </section>
                <!--<div class="col-sm-12"> -->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Brands_List', $adminLangId); ?> </h4>
                        <?php
                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));


                        ?>
                        <?php if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0) && $canView) {
                            $innerLiExport=$innerUl->appendElement('li');
                            $innerLiExport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Export', $adminLangId),"onclick"=>"addExportForm(".Importexport::TYPE_BRANDS.")"), Labels::getLabel('LBL_Export', $adminLangId), true); ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="exportForm(<?php echo Importexport::TYPE_BRANDS; ?>)";><?php echo Labels::getLabel('LBL_Export', $adminLangId); ?></a>-->
                        <?php }
                        if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0) && $canEdit) {
                            $innerLiImport=$innerUl->appendElement('li');
                            $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Import', $adminLangId),"onclick"=>"addImportForm(". Importexport::TYPE_BRANDS.")"), Labels::getLabel('LBL_Import', $adminLangId), true); ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="importForm(<?php echo Importexport::TYPE_BRANDS; ?>)";><?php echo Labels::getLabel('LBL_Import', $adminLangId); ?></a>-->
                        <?php } ?>
                        <?php if ($canEdit) {
                            $innerLi=$innerUl->appendElement('li');
                            $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Activate', $adminLangId),"onclick"=>"toggleBulkStatues(1)"), Labels::getLabel('LBL_Activate', $adminLangId), true);

                            $innerLi=$innerUl->appendElement('li');
                            $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Deactivate', $adminLangId),"onclick"=>"toggleBulkStatues(0)"), Labels::getLabel('LBL_Deactivate', $adminLangId), true);

                            $innerLi=$innerUl->appendElement('li');
                            $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteSelected()"), Labels::getLabel('LBL_Delete', $adminLangId), true);

                            $innerLiAddCat=$innerUl->appendElement('li');
                            $innerLiAddCat->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Brand', $adminLangId),"onclick"=>"addBrandForm(0)"), Labels::getLabel('LBL_Add_Brand', $adminLangId), true); ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="brandForm(0)";><?php echo Labels::getLabel('LBL_Add_Brand', $adminLangId); ?></a>-->
                        <?php }
                        echo $ul->getHtml();
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap" >
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
