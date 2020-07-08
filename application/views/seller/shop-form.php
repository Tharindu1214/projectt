<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$shopFrm->setFormTagAttribute('onsubmit', 'setupShop(this); return(false);');
$shopFrm->setFormTagAttribute('class', 'form form--horizontal');

$shopFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
$shopFrm->developerTags['fld_default_col'] = 4;

$countryFld = $shopFrm->getField('shop_country_id');
$countryFld->setFieldTagAttribute('id', 'shop_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,'.$stateId.',\'#shop_state\')');

$stateFld = $shopFrm->getField('shop_state');
$stateFld->setFieldTagAttribute('id', 'shop_state');

$urlFld = $shopFrm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
$urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value)");
$urlFld->htmlAfterField = "<p class='note' id='shopurl'>" . CommonHelper::generateFullUrl('Shops', 'View', array($shop_id), '/').'</p>';
$IDFld = $shopFrm->getField('shop_id');
$IDFld->setFieldTagAttribute('id', "shop_id");
$identiFierFld = $shopFrm->getField('shop_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup', "Slugify(this.value,'urlrewrite_custom','shop_id','shopurl')");
$variables= array('language'=>$language,'siteLangId'=>$siteLangId,'shop_id'=>$shop_id,'action'=>$action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="tabs__content">
            <div class="row">
                <div class="col-lg-12 col-md-12" id="shopFormBlock"> <?php echo $shopFrm->getFormHtml(); ?> </div>
            </div>
        </div>
    </div>
</div>
<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#shop_country_id").val(), <?php echo $stateId ;?>, '#shop_state');
    });
</script>
