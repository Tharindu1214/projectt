<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$approvalFrm->setFormTagAttribute('onsubmit', 'setupSupplierApproval(this); return(false);');
$approvalFrm->setFormTagAttribute('class', 'form');
$approvalFrm->developerTags['colClassPrefix'] = 'col-md-';
$approvalFrm->developerTags['fld_default_col'] = '4'; ?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('Lbl_Seller_Approval_Form', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <div class="cards-header p-4">
                    <h5 class="cards-title"><?php echo Labels::getLabel('Lbl_Seller_Approval_Form', $siteLangId);?></h5>
                </div>
                <div class="cards-content pl-4 pr-4 ">
                    <?php echo $approvalFrm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</main>
