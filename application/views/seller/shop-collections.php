<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $variables= array('language'=>$language,'siteLangId'=>$siteLangId,'shop_id'=>$shop_id,'action'=>$action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="tabs__content">
            <div class="row row" id="shopFormChildBlock"></div>
        </div>
    </div>
</div>
