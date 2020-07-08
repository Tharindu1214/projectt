<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <?php echo $search->getFormHtml(); ?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Messages_List', $adminLangId); ?> </h4>
                        <?php
                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Back_to_Messages', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                            $innerLi=$innerUl->appendElement('li');
                            $innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('Messages'),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_Back_to_Messages', $adminLangId)), Labels::getLabel('LBL_Back_to_Messages', $adminLangId), true);
                            echo $ul->getHtml();
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
