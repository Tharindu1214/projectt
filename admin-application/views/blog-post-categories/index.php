<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Blog_Post_Categories', $adminLangId); ?> <?php echo (isset($bpCatData['bpcategory_identifier']))?$bpCatData['bpcategory_identifier']:'';?></h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> Search...</h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $search->setFormTagAttribute('onsubmit', 'searchBlogPostCategories(this); return(false);');
                            $search->setFormTagAttribute('class', 'web_form');
                            $search->developerTags['colClassPrefix'] = 'col-md-';
                            $search->developerTags['fld_default_col'] = 6;
                            $btn_clear = $search->getField('btn_clear');
                            $btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
                            echo $search->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4>Blog Post Category List </h4>
                        <?php if ($canEdit) {
                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                            if ($canEdit) {
                                $innerLi=$innerUl->appendElement('li');
                                $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Activate', $adminLangId),"onclick"=>"toggleBulkStatues(1)"), Labels::getLabel('LBL_Activate', $adminLangId), true);

                                $innerLi=$innerUl->appendElement('li');
                                $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Deactivate', $adminLangId),"onclick"=>"toggleBulkStatues(0)"), Labels::getLabel('LBL_Deactivate', $adminLangId), true);

                                $innerLi=$innerUl->appendElement('li');
                                $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteSelected()"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                            }

                            $innerLiAddCat=$innerUl->appendElement('li');
                            $innerLiAddCat->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_Blog_Post_Category', $adminLangId),"onclick"=>"addCategoryForm(0)"), Labels::getLabel('LBL_Add_Blog_Post_Category', $adminLangId), true);

                            echo $ul->getHtml();
                            /*<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="addCategoryForm(0)";>Add Blog Post Category</a>*/
                        } ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> processing....</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
