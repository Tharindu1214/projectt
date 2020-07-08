<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="generalForm"></div>
<?php
$frmShop->setFormTagAttribute('class', 'web_form form_horizontal');
$frmShop->setFormTagAttribute('onsubmit', 'setupShop(this); return(false);');
$frmShop->developerTags['colClassPrefix'] = 'col-md-';
$frmShop->developerTags['fld_default_col'] = 12;
$countryFld = $frmShop->getField('shop_country_id');
$countryFld->setFieldTagAttribute('id', 'shop_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,'.$stateId.',\'#shop_state\')');

$stateFld = $frmShop->getField('shop_state');
$stateFld->setFieldTagAttribute('id', 'shop_state');

$fld = $frmShop->getField('shop_featured');
$fld->htmlAfterField = '<small><br>'.Labels::getLabel('LBL_Featured_Shops_will_be_listed_on_Featured_Shops_Page._Featured_Shops_will_get_priority,', $adminLangId).'</small>';
$urlFld = $frmShop->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
$urlFld->htmlAfterField = "<br><small class='text--small'>" . CommonHelper::generateFullUrl('shops', 'View', array($shop_id), CONF_WEBROOT_FRONT_URL).'</small>';
$urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value,'')");
?>
    <section class="section">
        <div class="sectionhead">
            <h4><?php echo Labels::getLabel('LBL_Shop_Setup', $adminLangId); ?></h4>
        </div>
        <div class="sectionbody space">
            <div class="tabs_nav_container responsive flat">
                <ul class="tabs_nav">
                    <li>
                        <a class="active" href="javascript:void(0)" onclick="shopForm(<?php echo $shop_id ?>);">
                            <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                        </a>
                    </li>
                    <?php $inactive= ($shop_id==0) ? 'fat-inactive':'';
                    foreach ($languages as $langId => $langName) {?>
                    <li class="<?php echo $inactive;?>"><a href="javascript:void(0);"
                        <?php if ($shop_id > 0) { ?>
                            onclick="addShopLangForm(<?php echo $shop_id ?>, <?php echo $langId;?>);"
                        <?php } ?>><?php echo Labels::getLabel("LBL_".$langName, $adminLangId);?></a></li>
                    <?php } ?>
                        <?php /* <li><a href="javascript:void(0);"
                        <?php if ($shop_id > 0) { ?>
                            onclick="shopTemplates(<?php echo $shop_id ?>);"
                        <?php } ?>><?php echo Labels::getLabel('LBL_Templates', $adminLangId); ?></a></li> */ ?>
                    <li><a href="javascript:void(0);"
                        <?php if ($shop_id > 0) {?>
                            onclick="shopMediaForm(<?php echo $shop_id ?>);"
                        <?php } ?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    <li><a href="javascript:void(0);"
                        <?php if ($shop_id > 0) { ?>
                            onclick="shopCollections(<?php echo $shop_id ?>);"
                        <?php } ?>><?php echo Labels::getLabel('LBL_Collections', $adminLangId); ?></a></li>
                </ul>
                <div class="tabs_panel_wrap">
                    <div class="tabs_panel">

                        <?php echo $frmShop->getFormHtml(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script language="javascript">
        $(document).ready(function() {
            getCountryStates($("#shop_country_id").val(), <?php echo $stateId ;?>, '#shop_state');
        });
    </script>
