<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$showDefalultLi = true;
if ($languages && count($languages) > 1) {
    $showDefalultLi = false;
    ?>
<li class="dropdown dropdown--arrow dropdown--lang">
    <a href="javascript:void(0)" class="dropdown__trigger dropdown__trigger-js">
        <i class="icn icn--language">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#language" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#language"></use>
            </svg>
        </i>
        <span><?php echo $languages[$siteLangId]['language_name']; ?></span> </a>
    <div class="dropdown__target dropdown__target-lang dropdown__target-js">
        <div class="dropdown__target-space">
            <span class="expand-heading"><?php echo Labels::getLabel('LBL_Select_Language', $siteLangId);?></span>
            <ul class="list-vertical list-vertical--tick">
                <?php foreach ($languages as $langId => $language) { ?>
                <li <?php echo ($siteLangId==$langId)?'class="is-active"':'';?>><a href="javascript:void(0);" onClick="setSiteDefaultLang(<?php echo $langId;?>)"> <?php echo $language['language_name']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</li>
<?php }
if ($currencies && count($currencies) > 1) {
    $showDefalultLi = false;
    ?>
<li class="dropdown dropdown--arrow  dropdown--currency">
    <a href="javascript:void(0)" class="dropdown__trigger dropdown__trigger-js">
        <i class="icn icn-currency">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#currency" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#currency"></use>
            </svg>
        </i><span> <?php echo Labels::getLabel('LBL_Currency', $siteLangId);?></span>
    </a>
    <div class="dropdown__target dropdown__target-lang dropdown__target-js">
        <div class="dropdown__target-space">
            <span class="expand-heading"><?php echo Labels::getLabel('LBL_Select_Currency', $siteLangId);?></span>
            <ul class="list-vertical list-vertical--tick" data-simplebar>
                <?php foreach ($currencies as $currencyId => $currency) { ?>
                <li <?php echo ($siteCurrencyId == $currencyId)?'class="is-active"':'';?>><a href="javascript:void(0);" onClick="setSiteDefaultCurrency(<?php echo $currencyId;?>)"> <?php echo $currency; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</li>
<?php }

if ($showDefalultLi) {            ?>
<li class="dropdown dropdown--arrow">
    <a href="javascript:void(0)" class="dropdown__trigger dropdown__trigger-js"><i class="icn-language"><img class="icon--img"> </i><span></span> </a></li>
<?php } ?>
