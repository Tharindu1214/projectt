<div class="white--bg padding20">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xm-12 clearfix">
            <div id="img-static" class="product-detail-gallery">
                <img src="<?php echo CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, 0, $siteLangId )) ?>">
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xm-12">
            <div class="product-description">
                <div class="product-description-inner">
                    <div class="products__title"><?php echo $product['product_name']; ?></div>
                    <div class="gap"></div>
                    <div class="cms">
                        <table>
                            <tbody>
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Category', $siteLangId); ?>:</th>
                                    <td><?php echo $product['prodcat_name']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>:</th>
                                    <td><?php echo ($product['brand_name']) ? $product['brand_name'] : Labels::getLabel('LBL_N/A', $siteLangId); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Product_Model', $siteLangId); ?>:</th>
                                    <td><?php echo $product['product_model']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Minimum_Selling_Price', $siteLangId); ?>:</th>
                                    <td><?php echo CommonHelper::displayMoneyFormat($product['product_min_selling_price']); ?></td>
                                </tr>
                                <?php  $saleTaxArr = Tax::getSaleTaxCatArr($siteLangId);
                                if(array_key_exists($product['ptt_taxcat_id'], $saleTaxArr)){?>
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Tax_Category', $siteLangId); ?>:</th>
                                    <td><?php echo $saleTaxArr[$product['ptt_taxcat_id']]; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($productSpecifications)>0) { ?>
                    <div class="gap"></div>
                    <div class="box box--gray box--radius box--space">
                        <div class="h6"><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?>:</div>
                        <div class="list list--specification">
                            <ul>
                                <?php $count=1;
                                foreach ($productSpecifications as $key => $specification) {
                                    if ($count>5) {
                                        continue;
                                    } ?>
                                        <li><?php echo '<span>'.$specification['prodspec_name']." :</span> ".$specification['prodspec_value']; ?></li>
                                        <?php $count++;
                                } ?>
                                <?php /*if (count($productSpecifications)>5) { ?>
                                <li class="link_li"><a href="javascript:void()"><?php echo Labels::getLabel('LBL_View_All_Details', $siteLangId); ?></a></li>
                                <?php }*/ ?>
                            </ul>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
