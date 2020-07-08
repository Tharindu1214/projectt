<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Labels', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;"> <?php
                        $frmSearch->setFormTagAttribute('onsubmit', 'searchLabels(this); return(false);');
                        $frmSearch->setFormTagAttribute('id', 'frmLabelsSearch');
                        $frmSearch->setFormTagAttribute('class', 'web_form');
                        $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                        $frmSearch->developerTags['fld_default_col'] = 4;

                        $btn = $frmSearch->getField('btn_clear');
                        $btn->setFieldTagAttribute('onClick', 'clearSearch()');
                        echo  $frmSearch->getFormHtml();
                        ?> </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Language_labels_List', $adminLangId); ?> </h4> <?php

                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                        if ($canEdit) {
                             $innerLiImport=$innerUl->appendElement('li');
                             $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Import', $adminLangId),"onclick"=>"importLabels(0)"), Labels::getLabel('LBL_Import', $adminLangId), true);

                             /*<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="importLabels()";><?php echo Labels::getLabel('LBL_Import',$adminLangId); ?></a>*/
                        }
                        if ($canView) {
                            $innerLiImport=$innerUl->appendElement('li');
                            $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Export', $adminLangId),"onclick"=>"exportLabels(0)"), Labels::getLabel('LBL_Export', $adminLangId), true); /*<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="exportLabels()" ;><?php echo Labels::getLabel('LBL_Export',$adminLangId); ?></a>*/
                        }

                        $innerLiImport=$innerUl->appendElement('li');
                        $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_UPDATE_WEB_LABEL_FILE', $adminLangId),"onclick"=>"updateFile()"), Labels::getLabel('LBL_UPDATE_WEB_LABEL_FILE', $adminLangId), true);
                        $innerLiImport=$innerUl->appendElement('li');
                        $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_UPDATE_APP_LABEL_FILE', $adminLangId),"onclick"=>"updateFile(".Labels::TYPE_APP.")"), Labels::getLabel('LBL_UPDATE_APP_LABEL_FILE', $adminLangId), true);

                        echo $ul->getHtml(); ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
