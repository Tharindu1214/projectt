<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Content_Blocks', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Content_Blocks', $adminLangId); ?> </h4>
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
                            echo $ul->getHtml();
                            //echo Importexport::TYPE_PRODUCTS
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="blockListing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<?php if ($epage_id > 0) { ?>
    <script type="text/javascript">
        $(document).ready(function() {
            addBlockForm(<?php echo $epage_id; ?>);
        });
    </script>
<?php } ?>
