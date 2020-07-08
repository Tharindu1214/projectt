<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_General_Settings', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div class="tabs_nav_container vertical">
                    <ul class="tabs_nav outerul"> <?php $count = 1;
                                foreach ($tabs as $formType => $tabName) {
                                    $tabsId = 'tabs_'.$count; ?> <?php if ($formType == Configurations::FORM_MEDIA || $formType == Configurations::FORM_SHARING) {
                                        ?> <li><a class="<?php echo ($activeTab == $formType) ? 'active' : ''?>" rel=<?php echo $tabsId; ?> href="javascript:void(0)"
                                onClick="getLangForm(<?php echo $formType; ?>, <?php echo $adminLangId; ?>, '<?php echo $tabsId; ?>')"><?php echo $tabName; ?></a></li> <?php
                                    } elseif ($formType == Configurations::FORM_IMPORT_EXPORT) {
                                        ?> <li><a class="<?php echo ($activeTab == $formType) ? 'active' : ''?>" rel=<?php echo $tabsId; ?> href="javascript:void(0)" onClick="generalInstructions(<?php echo $formType; ?>)"><?php echo $tabName; ?></a>
                        </li> <?php
                                    } else {
                                        ?> <li><a class="<?php echo ($activeTab==$formType) ? 'active' : ''?>" rel=<?php echo $tabsId; ?> href="javascript:void(0)"
                                onClick="getForm(<?php echo $formType; ?>,'<?php echo $tabsId; ?>')"><?php echo $tabName; ?></a></li> <?php
                                    }
                                    $count++;
                                } ?> </ul>
                    <div id="frmBlock" class="tabs_panel_wrap">
                        <div class="tabs_panel"></div>
                    </div>
                </div>
                <!-- <section class="section">
                        <div class="sectionbody">
                            <div class="tablewrap">
                                <div id="frmBlock">
                                    <?php //echo Labels::getLabel('LBL_Processing...',$adminLangId);?>
                                </div>
                            </div>
                        </div>
                    </section> -->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var YES = <?php echo applicationConstants::YES; ?>;
    var NO = <?php echo applicationConstants::NO; ?>;
    var FORM_MEDIA = <?php echo Configurations::FORM_MEDIA; ?>;
</script>
