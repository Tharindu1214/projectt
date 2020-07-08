<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title">
                <?php echo Labels::getLabel('Lbl_Change_Email_/_password', $siteLangId);?>
                </h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-lg-6 col-md-6 mb-3">
                    <div class="cards">
                        <div class="cards-header p-4">
                            <h5 class="cards-title "><?php echo Labels::getLabel('Lbl_Change_Email', $siteLangId);?></h5>
                        </div>
                        <div class="cards-content pl-4 pr-4 ">
                            <div id="changeEmailFrmBlock"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 mb-3">
                    <div class="cards">
                        <div class="cards-header p-4">
                            <h5 class="cards-title "><?php echo Labels::getLabel('Lbl_Change_password', $siteLangId);?></h5>
                        </div>
                        <div class="cards-content pl-4 pr-4 ">
                            <div id="changePassFrmBlock"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
