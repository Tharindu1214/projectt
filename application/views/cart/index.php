<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body bg-gray-dark" role="main">
    <div class="section">
      <div class="container">
       <div class="section-head">
            <div class="section__heading"><h2><?php echo Labels::getLabel('LBL_Shopping_Cart', $siteLangId); ?></h2></div>
            <div class="section__action">
                <?php if($total > 0) { ?>
                    <a href="javascript:void(0)" onclick="cart.remove('all','cart')" class="btn btn--primary-border btn--sm emtyCartBtn-js"><?php echo Labels::getLabel('LBL_Empty_Cart', $siteLangId); ?></a>
                <?php } ?>
            </div>
        </div>
        <div id="cartList"></div>
      </div>
    </div>
</div>
