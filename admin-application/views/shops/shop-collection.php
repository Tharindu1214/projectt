<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php $variables= array( 'language'=>$language,'adminLangId'=>$adminLangId,'shop_id'=>$shop_id,'step'=>'5');
    /* $this->includeTemplate('seller/_partial/shop-navigation.php',$variables); */ ?>
<div class="tabs__content">
    <div class="form__content ">
        <div class="col-md-12" id="shopFormChildBlock">
            <?php echo Labels::getLabel('LBL_Loading..', $adminLangId); ?>
        </div>
    </div>
</div>
