<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if (($languages && count($languages) > 1) || ($currencies && count($currencies) > 1)) { ?>
    <li class="divider"></li>
    <li class="menu__item">
        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Language_&_Currency", $siteLangId); ?></span></div>
    </li>
    <?php if ($languages && count($languages) > 1) { ?>
    <li class="menu__item">
        <div class="menu__item__inner">
        <a href="" class="accordianheader">
            <i class="icn "><svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#language" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#language"></use>
                </svg>
            </i><span class="menu-item__title"><?php echo $languages[$siteLangId]['language_name']; ?></span></a>
            <ul class="accordianbody">
                <?php foreach ($languages as $langId => $language) { ?>
                <li <?php echo ($siteLangId==$langId)?'class="is-active"':'';?>><a href="javascript:void(0);" onClick="setSiteDefaultLang(<?php echo $langId;?>)"> <?php echo $language['language_name']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </li>
    <?php }
    if ($currencies && count($currencies) > 1) { ?>
    <li class="menu__item">
        <div class="menu__item__inner"><a title="" href=""  class="accordianheader">
            <i class="icn "><svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#currency" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#currency"></use>
                </svg>
            </i><span class="menu-item__title"> <?php echo Labels::getLabel('LBL_Currency', $siteLangId);?></span></a>
             <ul class="accordianbody">
                <?php foreach ($currencies as $currencyId => $currency) { ?>
                 <li <?php echo ($siteCurrencyId == $currencyId)?'class="is-active"':'';?>><a href="javascript:void(0);" onClick="setSiteDefaultCurrency(<?php echo $currencyId;?>)"> <?php echo $currency; ?></a></li>
                <?php } ?>
                </ul>
                </div>
    </li>
    <?php } ?>
<?php } ?>
