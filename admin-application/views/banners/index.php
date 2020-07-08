<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
/* $frmSearch->setFormTagAttribute ( 'class', 'web_form last_td_nowrap' );
$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchLocations(this); return(false);' );
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 4; */
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Banner_Locations', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <?php /* <section class="section searchform_filter">
                <div class="sectionbody space togglewrap" style="display:none;">
                    <?php echo $frmSearch->getFormHtml(); ?>
                </div>
                </section> */ ?>
                <section class="section">
                <div class="sectionhead">
                    <h4><?php echo Labels::getLabel('LBL_Banner_Locations_List', $adminLangId); ?> </h4>
                    <?php
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
                    }

                        $innerLiAddCat=$innerUl->appendElement('li');
                        $innerLiAddCat->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('Lbl_Banner_Layouts_Instructions', $adminLangId),"onclick"=>"addBannersLayouts(0)"), Labels::getLabel('Lbl_Banner_Layouts_Instructions', $adminLangId), true);
                    echo $ul->getHtml();
                    ?>
                </div>
                <div class="sectionbody">
                    <div class="tablewrap">
                        <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                    </div>
                </div>
            </section>
            </div>
        </div>
    </div>
</div>
