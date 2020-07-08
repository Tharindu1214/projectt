<?php
if (isset($sponsoredShops) && count($sponsoredShops)) {
    /* category listing design [ */
    if (isset($sponsoredShops['shops']) && count($sponsoredShops['shops'])) {
        $row['shops'] = $sponsoredShops['shops'] ;
        $row['rating'] = $sponsoredShops['rating'];
        $track = true; ?>
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div class="section__heading">
                        <h2><?php echo FatApp::getConfig('CONF_PPC_SHOPS_HOME_PAGE_CAPTION_'.$siteLangId, FatUtility::VAR_STRING, Labels::getLabel('LBL_SPONSORED_SHOPS', $siteLangId)); ?></h2>
                    </div>
                </div>
                <?php include('shop-layout-1-list.php'); ?>
            </div>
        </section>
        <?php
    }
    /* ] */
} ?>
