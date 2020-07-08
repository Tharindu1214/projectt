<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSearch->setFormTagAttribute('onSubmit', 'searchUsers(this); return false;');
$frmSearch->setFormTagAttribute('class', 'form ');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 12;

$keywordFld = $frmSearch->getField('keyword');
$keywordFld->setWrapperAttribute('class', 'col-lg-4');
$keywordFld->developerTags['col'] = 4;

$keywordFld = $frmSearch->getField('user_active');
$keywordFld->setWrapperAttribute('class', 'col-lg-2');
$keywordFld->developerTags['col'] = 2;

$keywordFld = $frmSearch->getField('user_verified');
$keywordFld->setWrapperAttribute('class', 'col-lg-2');
$keywordFld->developerTags['col'] = 2;

$submitBtnFld = $frmSearch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;

$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block btn--primary-border');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
?>
<?php $this->includeTemplate('_partial/affiliate/affiliateDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header  row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Referrals', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-content pt-4 pl-4 pr-4 ">
                            <div class="replaced">
                                <?php echo $frmSearch->getFormHtml(); ?>
                            </div>
                            <span class="gap"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-content pt-2 pl-4 pr-4 ">
                            <div id="usersListing"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
