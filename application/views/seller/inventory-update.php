<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto">
                <h2 class="content-header-title"><?php echo Labels::getLabel('Lbl_Update_Products_Inventory', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <div class="cards-content p-4">
                    <div id="productInventory"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                    <div class="cms mt-4">
                        <?php if (!empty($pageData['epage_content'])) { ?>
                            <h3 class="mb-4"><?php echo $pageData['epage_label']; ?></h3>
                            <?php echo FatUtility::decodeHtmlEntities($pageData['epage_content']);
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
