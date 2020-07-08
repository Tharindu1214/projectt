<?php
if (isset($collections) && count($collections)) {
    $counter = 1;
    foreach ($collections as $collection_id => $row) {
    /* category listing design [ */
        if (isset($row['shops']) && count($row['shops'])) { ?>
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div class="section__heading">
                        <h2><?php echo ($row['collection_name'] != '') ? $row['collection_name'] : ''; ?></h2>
                    </div>
                    <?php if ($row['totShops'] > Collections::LIMIT_SHOP_LAYOUT1) { ?>
                    <div class="section_action"> <a href="<?php echo CommonHelper::generateUrl('Collections', 'View', array($row['collection_id']));?>" class="link"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?></a> </div>
                    <?php }  ?>
                </div>
                    <?php include('shop-layout-1-list.php'); ?>
            </div>
        </section>
        <?php }
    }
}
/* ] */
