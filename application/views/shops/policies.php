<?php defined('SYSTEM_INIT') or die('Invalid Usage');
	$shop_city = $shop['shop_city'];
	$shop_state = ( strlen($shop['shop_city']) > 0 ) ? ', '. $shop['shop_state_name'] : $shop['shop_state_name'];
	$shop_country = ( strlen($shop_state) > 0 ) ? ', '.$shop['shop_country_name'] : $shop['shop_country_name'];
	$shopLocation = $shop_city . $shop_state. $shop_country;
?>

<div class="bg--second pt-3 pb-3 ">
  <div class="container container--fixed">
    <div class="row">
      <div class="col-md-8 col-sm-8">
        <div class="cell">
          <div class="shop-info">
            <h5><?php echo $shop['shop_name']; ?></h5>
            <p><?php echo $shopLocation; ?> <?php echo Labels::getLabel('LBL_Opened_on', $siteLangId); ?> <?php echo FatDate::format($shop['shop_created_on']); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-sm-4 align--right"><a href="<?php echo CommonHelper::generateUrl('Shops', 'View', array($shop['shop_id'])); ?>" class="btn btn--primary"><?php echo Labels::getLabel('LBL_Back_to_Shop', $siteLangId); ?></a></div>
    </div>
  </div>
</div>
<div class="container container--fixed">

    <div class="panel panel--centered clearfix">

        <div class="section section--info clearfix">
          <div class="section__head">
            <h4><?php echo Labels::getLabel('LBL_Policies', $siteLangId); ?></h4>
          </div>
          <div class="section__body">
            <div class="box box--white">
              <?php if( $shop['shop_payment_policy'] != '' ){ ?>
              <div class="table table--twocols">
                <table>
                  <tbody>
                    <tr>
                      <th><?php echo Labels::getLabel('LBL_Payment', $siteLangId)?></th>
                      <td><?php echo nl2br($shop['shop_payment_policy']); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <?php } ?>
              <?php  if ( $shop["shop_delivery_policy"] != "" ) { ?>
              <div class="table table--twocols">
                <table>
                  <tbody>
                    <tr>
                      <th><?php echo Labels::getLabel('LBL_Shipping', $siteLangId)?></th>
                      <td><?php echo nl2br($shop['shop_delivery_policy']); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <?php } ?>
              <?php  if ( $shop["shop_refund_policy"] != "" ) { ?>
              <div class="table table--twocols">
                <table>
                  <tbody>
                    <tr>
                      <th><?php echo Labels::getLabel('LBL_Refunds_Exchanges', $siteLangId)?></th>
                      <td><?php echo nl2br($shop['shop_refund_policy']); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <?php } ?>
              <?php  if ( $shop["shop_additional_info"] != "" ) { ?>
              <div class="table table--twocols">
                <table>
                  <tbody>
                    <tr>
                      <th><?php echo Labels::getLabel('LBL_Additional_Policies_FAQs', $siteLangId)?></th>
                      <td><?php echo nl2br($shop['shop_additional_info']); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <?php } ?>
              <?php  if ( $shop["shop_seller_info"] != "" ) { ?>
              <div class="table table--twocols">
                <table>
                  <tbody>
                    <tr>
                      <th><?php echo Labels::getLabel('LBL_Seller_Information', $siteLangId)?></th>
                      <td><?php echo nl2br($shop['shop_seller_info']); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>


  </div>
</div>
