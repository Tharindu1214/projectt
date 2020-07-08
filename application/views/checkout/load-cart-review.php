<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section-checkout is-completed">
    <div class="selected-panel">
        <div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Review_Order', $siteLangId); ?></div>
        <div class="selected-panel-data">
            <?php
            if (count($products)) {
                foreach ($products as $product) { ?>
            <p><?php echo $product['selprod_title']; ?><?php
                            if (isset($product['options']) && count($product['options'])) {
                                $count = 0;
                                foreach ($product['options'] as $option) { ?>
                <?php echo ($count > 0) ? ' | ' : '' ; echo $option['option_name'].':'; ?>
                <?php echo $option['optionvalue_name']; ?>
                <?php $count++;
                                }
                            }
                            echo ' | Quantity: '.$product['quantity'] ;
                        ?></p>
            <?php }
            } ?>
        </div>
        <div class="selected-panel-action"><a href="javascript:void(0);" onclick="viewOrder()" ; class="btn btn--primary btn--sm ripplelink"><?php echo Labels::getLabel('LBL_View_Order', $siteLangId); ?></a></div>
    </div>
</section>
