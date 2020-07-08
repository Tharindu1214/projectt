<?php 
if($product['product_weight_unit'] == 1){
    $kg = $product['product_weight']/1000;
}else if($product['product_weight_unit'] == 3){
    $kg = $product['product_weight']/2.20;
}else{
    $kg = $product['product_weight'];
}
$kg = number_format((float)$kg, 2, '.', '');

if (!empty($product['selprod_warranty_policies']) || !empty($product['selprod_return_policies']) || (isset($shippingDetails['ps_free']) && $shippingDetails['ps_free']==applicationConstants::YES) || (count($shippingRates)>0) || ($codEnabled)) { ?>
<section class="section certified-bar">

        <div class="row justify-content-around">
            <?php if (!empty($product['selprod_warranty_policies'])) { ?>
                <div class="col-auto">
                    <div class="certified-box">
                        <i class="icn">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#yearswarranty" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#yearswarranty"></use>
                            </svg>
                        </i>
                        <p><?php echo $product['selprod_warranty_policies']['ppoint_title']; ?></p>
                    </div>
                </div>
            <?php } ?>
            <?php if (!empty($product['selprod_return_policies'])) { ?>
                <div class="col-auto">
                    <div class="certified-box">
                        <i class="icn">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns"></use>
                            </svg>
                        </i>
                        <p><?php echo $product['selprod_return_policies']['ppoint_title']; ?></p>
                    </div>
                </div>
            <?php } ?>
            <?php if (isset($shippingDetails['ps_free']) && $shippingDetails['ps_free']==applicationConstants::YES) { ?>
                <div class="col-auto">
                    <div class="certified-box">
                        <i class="icn">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#freeshipping" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#freeshipping"></use>
                            </svg>
                        </i>
                        <p><?php echo Labels::getLabel('LBL_Free_Shipping_on_this_Order', $siteLangId); ?></p>
                    </div>
                </div>
            <?php } elseif (count($shippingRates)>0) { ?>
                <div class="col-auto">
                    <div class="certified-box">
                        <i class="icn">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-policies" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-policies"></use>
                            </svg>
                        </i>
                        <p><?php echo Labels::getLabel('LBL_Shipping_Rates', $siteLangId);?>
                        <a href="#shipRates" rel="facebox"><i class="fa fa-question-circle"></i></a></p>
                        <div id="shipRates" style="display:none">
                            <div class="delivery-term-data-inner">
                                <?php
                               if($typeMode){
                                    $arr_flds = array(
                                        'country_name'=> Labels::getLabel('LBL_Ship_to', $siteLangId),
                                        'pship_charges'=> 'Shiping Cost',
                                        'pship_additional_charges'=> 'Each Additional Item Cost',
                                    );
                                }else{
                                    $arr_flds = array(
                                        'country_name'=> Labels::getLabel('LBL_Ship_to', $siteLangId),
                                        'pship_charges'=> Labels::getLabel('LBL_COST', $siteLangId),
                                        // 'pship_additional_charges'=> Labels::getLabel('LBL_Each_Additional_Kg', $siteLangId),
                                    );
                                }
                                $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
                                $th = $tbl->appendElement('thead')->appendElement('tr');
                                foreach ($arr_flds as $val) {
                                    $e = $th->appendElement('th', array(), $val);
                                }
                                $kk = 0;
                                foreach ($shippingRates as $sn => $row) {
                                    if($kk == 1){
                                     break;
                                    }
                                    $kk++;
                                    $tr = $tbl->appendElement('tr');

                                    foreach ($arr_flds as $key => $val) {
                                        $td = $tr->appendElement('td');
                                        switch ($key) {
                                            case 'pship_additional_charges':
                                                if($row['pship_charges'] > 0){
                                                        $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key]));
                                                }else{
                                                    $td->appendElement('plaintext', array(),'Free Delivery' , true);
                                                }
                                                break;
                                            case 'pship_charges':
                                                if($row['pship_charges'] > 0){
                                                    if($typeMode == 0){
                                                        $total_charge = $row[$key];
                                                        $extr_weight = $kg > 0 ? ceil($kg - 1) : 0; 
                                                        if($extr_weight != 0){
                                                            $total_charge = $total_charge + ($extr_weight * $row['pship_additional_charges']);
                                                        }
                                                        $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($total_charge));
                                                    }else{
                                                        $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key]));
                                                    }
                                                }else{
                                                    $td->appendElement('plaintext', array(),'Free Delivery' , true);
                                                }
                                                break;
                                            case 'country_name':                                           
                                                $td->appendElement('plaintext', array(), Product::getProductShippingTitle($siteLangId, $row), true);
                                                break;
                                            default:
                                                $td->appendElement('plaintext', array(), $row[$key], true);
                                                break;
                                        }
                                    }
                                }
                                echo $tbl->getHtml(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }elseif (count($shippingRates) == 0) { ?>
                <div class="col-auto">
                    <div class="certified-box">
                        <i class="icn">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-policies" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-policies"></use>
                            </svg>
                        </i>
                        <p><?php echo Labels::getLabel('LBL_Shipping_Rates', $siteLangId);?>
                        <a href="#shipRates" rel="facebox"><i class="fa fa-question-circle"></i></a></p>
                        <p><?php echo 'Delivery to '.$cityName.'<br/><span style="color:#ff3a59;"> Unavailable </span>'; ?></p>
                        <div id="shipRates" style="display:none">
                            <div class="delivery-term-data-inner">
                                <?php
                                if($typeMode){
                                    $arr_flds = array(
                                        'country_name'=> Labels::getLabel('LBL_Ship_to', $siteLangId),
                                        'pship_charges'=> 'Shipping Cost',
                                        'pship_additional_charges'=> 'Each Additional Item Cost',
                                    );
                                }else{
                                    $arr_flds = array(
                                        'country_name'=> Labels::getLabel('LBL_Ship_to', $siteLangId),
                                        'pship_charges'=> Labels::getLabel('LBL_COST', $siteLangId),
                                        //'pship_additional_charges'=> Labels::getLabel('LBL_Each_Additional_Kg', $siteLangId),
                                    );
                                }
                               
                                $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
                                $th = $tbl->appendElement('thead')->appendElement('tr');
                                foreach ($arr_flds as $val) {
                                    $e = $th->appendElement('th', array(), $val);
                                }                                
                                $tr = $tbl->appendElement('tr');
                                $td = $tr->appendElement('td');
                                // $td->appendElement('plaintext', array(),Labels::getLabel('LBL_No_Shipping_Available', $siteLangId));
                                $td->appendElement('plaintext', array(),'Delivery unavailable to '.$cityName.' city ');
                                
                                $td->setAttribute('colspan','3');
                                $td->setAttribute('align','center');
                                echo $tbl->getHtml(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } if ($codEnabled) { ?>
                <div class="col-auto">
                    <div class="certified-box">
                        <i class="icn">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#safepayments" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#safepayments"></use>
                            </svg>
                        </i>
                        <p><?php echo Labels::getLabel('LBL_Cash_on_delivery_is_available', $siteLangId);?> <i class="fa fa-question-circle-o tooltip tooltip--right"><span class="hovertxt"><?php echo Labels::getLabel('MSG_Cash_on_delivery_available._Choose_from_payment_options', $siteLangId);?> </span></i></p>
                    </div>
                </div>
            <?php } ?>
        </div>

</section>
<?php }?>
