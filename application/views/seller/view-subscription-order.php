<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header  row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_View_Subscription_Order', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <div class="cards-header p-4">
                    <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Order_Details', $siteLangId);?></h5>
                    <div class="action">
                    <?php /* <a href="javascript:window.print();" class="btn btn--primary btn--sm no-print"><?php echo Labels::getLabel('LBL_Print',$siteLangId);?></a>  */?>
                    <a href="<?php echo CommonHelper::generateUrl('Seller', 'subscriptions');?>" class="btn btn--primary btn--sm no-print"><?php echo Labels::getLabel('LBL_Back_to_Subscription', $siteLangId);?></a></div>
                </div>
                <div class="cards-content pl-4 pr-4 ">

                                <div class="row">
                                  <div class="col-lg-6 col-md-6 col-sm-6">
                                      <div class="info--order">
                                       <p><strong><?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId);?>: </strong><?php echo $orderDetail['user_name'];?></p>
                                         <p><strong><?php echo Labels::getLabel('LBL_Status', $siteLangId);?>: </strong><?php if ($orderDetail['ossubs_status_id']==FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') && $orderDetail['ossubs_till_date']<date("Y-m-d")) {
    echo Labels::getLabel('LBL_Expired', $siteLangId);
} else {
    echo $orderStatuses[$orderDetail['ossubs_status_id']];
}
                                        ?></p>
                                        </div>
                                  </div>
                                  <div class="col-lg-6 col-md-6 col-sm-6">
                                       <div class="info--order">
                                         <p><strong><?php echo Labels::getLabel('LBL_Invoice', $siteLangId);?> #: </strong><?php echo $orderDetail['ossubs_invoice_number'];?></p>
                                         <p><strong><?php echo Labels::getLabel('LBL_Date', $siteLangId);?>: </strong><?php echo FatDate::format($orderDetail['order_date_added']);?></p>
                                         <span class="gap"></span>
                                       </div>
                                  </div>
                                </div>

					<div class="gap"></div>
                          <table class="table table--orders js-scrollable">
                              <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Date_Added', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Subscription_Name', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Subscription_Period', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Subscription_Amount', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Product_Upload_Limit', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Inventory_Upload_Limit', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Images_Limit', $siteLangId);?></th>
                                </tr>
                                <tr>
                                    <td><?php echo FatDate::format($orderDetail['order_date_added'], true);?></td>
                                    <td><?php echo OrderSubscription::getSubscriptionTitle($orderDetail, $siteLangId);?></td>
                                    <td>
                                    <?php if ($orderDetail['ossubs_from_date']==0 || $orderDetail['ossubs_till_date']==0) {
                                            echo Labels::getLabel("LBL_N/A", $siteLangId);
                                        } else {
                                            echo FatDate::format($orderDetail['ossubs_from_date'])." - " .FatDate::format($orderDetail['ossubs_till_date']);
                                        } ?>
                                    </td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($orderDetail['ossubs_price']);?></td>
                                    <td><?php echo $orderDetail['ossubs_products_allowed'];?></td>
                                    <td><?php echo $orderDetail['ossubs_inventory_allowed'];?></td>
                                    <td><?php echo $orderDetail['ossubs_images_allowed'];?></td>
                                </tr>
                              </tbody>
                          </table>

                </div>
            </div>
        </div>
    </div>
</main>
