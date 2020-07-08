<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSearch->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frmSearch->setFormTagAttribute('onsubmit', 'searchListing(this); return(false);');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 4;
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Banner', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div style="display:none">
                    <?php echo $frmSearch->getFormHtml(); ?>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Banner', $adminLangId);?>
                            <?php echo (isset($data['blocation_key'])) ? Labels::getLabel($data['blocation_key'], $adminLangId):''?>
                            <?php echo Labels::getLabel('LBL_Listing', $adminLangId);?> </h4>

                        <?php

                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                            $innerLiAddNew=$innerUl->appendElement('li');
                            $innerLiAddNew->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_New', $adminLangId),"onclick"=>"addBannerForm(".$data['blocation_id'].")"), Labels::getLabel('LBL_Add_New', $adminLangId), true);

                            $innerLiAddBack=$innerUl->appendElement('li');

                                $url=CommonHelper::generateUrl('banners');
                                $innerLiAddBack->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Back', $adminLangId),'onclick'=>'redirectBack("'.$url.'")'), Labels::getLabel('LBL_Back', $adminLangId), true);
                            echo $ul->getHtml();
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing', $adminLangId);?>....</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
