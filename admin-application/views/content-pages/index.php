<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Content_Pages', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $frmSearch->setFormTagAttribute('onsubmit', 'searchPages(this); return(false);');
                            $frmSearch->setFormTagAttribute('class', 'web_form');
                            $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                            $frmSearch->developerTags['fld_default_col'] = 6;
                            $btn_clear = $frmSearch->getField('btn_clear');
                            $btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
                            echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Content_Pages', $adminLangId); ?></h4> <?php
                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));

                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                        if ($canEdit) {
                            $innerLiAddPage=$innerUl->appendElement('li');
                            $innerLiAddPage->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Page', $adminLangId),"onclick"=>"addFormNew(0)"), Labels::getLabel('LBL_Add_Page', $adminLangId), true);
                            
                            $innerLi=$innerUl->appendElement('li');
                            $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteSelected()"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                        }
                        $innerLiAddPage=$innerUl->appendElement('li');
                        $innerLiAddPage->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('Lbl_Layouts_Instructions', $adminLangId),"onclick"=>"pagesLayouts()"), Labels::getLabel('Lbl_Layouts_Instructions', $adminLangId), true);
                        /*            <a href="javascript:void(0)" onClick="pagesLayouts()" class="themebtn btn-default btn-sm">*/
                        echo $ul->getHtml();
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="pageListing"> <?php echo Labels::getLabel('LBL_Pages', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
