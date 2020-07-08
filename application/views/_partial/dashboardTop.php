<?php  /* if ((User::canViewSupplierTab() && User::canViewBuyerTab()) || (User::canViewSupplierTab() && User::canViewAdvertiserTab()) || (User::canViewBuyerTab() && User::canViewAdvertiserTab())) { ?>
<div class="dashboard-types no-print">
    <ul>
        <?php if (User::canViewSupplierTab()) { ?>
        <li <?php if ($activeTab == 'S') {
             echo 'class="is-active"';
            } ?>>
            <a href="<?php echo CommonHelper::generateUrl('Seller'); ?>"><?php echo Labels::getLabel('Lbl_Seller', $siteLangId);?></a></li>
        <?php }?>
        <?php if (User::canViewBuyerTab()) { ?>
        <li <?php if ($activeTab == 'B') {
            echo 'class="is-active"';
            } ?>>
            <a href="<?php echo CommonHelper::generateUrl('Buyer'); ?>"><?php echo Labels::getLabel('Lbl_Buyer', $siteLangId);?></a></li>
        <?php }?>
        <?php if (User::canViewAdvertiserTab()) { ?>
        <li <?php if ($activeTab == 'Ad') {
            echo 'class="is-active"';
            } ?>>
            <a href="<?php echo CommonHelper::generateUrl('Advertiser'); ?>"><?php echo Labels::getLabel('Lbl_Advertiser', $siteLangId);?></a></li>
        <?php }?>
    </ul>
</div>
<?php } */ ?>
