<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
    <section class="section">
        <div class="sectionhead">
            <h4><?php echo Labels::getLabel('LBL_Shop_Setup', $adminLangId); ?></h4>
        </div>
        <div class="sectionbody space">
            <div class="row">
                <div class="col-sm-12">
                    <div class="tabs_nav_container responsive flat">
                        <ul class="tabs_nav">
                            <li>
                                <a href="javascript:void(0);" onclick="shopForm(<?php echo $shop_id ?>);">
                                    <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                                </a>
                            </li>
                            <?php
                            $inactive = ($shop_id == 0) ? 'fat-inactive' : '';
                            foreach ($languages as $langId => $langName) { ?>
                            <li class="<?php echo $inactive;?>"><a href="javascript:void(0);"
                                <?php if ($shop_id > 0) {?>
                                    onclick="addShopLangForm(<?php echo $shop_id ?>, <?php echo $langId;?>);"
                                <?php }?>><?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?></a></li>
                            <?php } ?>
                                <?php /* <li><a class="active" href="javascript:void(0);"
                                <?php if ($shop_id > 0) {?>
                                        onclick="shopTemplates(<?php echo $shop_id ?>);"
                                <?php }?>><?php echo Labels::getLabel('LBL_Templates', $adminLangId); ?></a></li> */ ?>
                                <li><a href="javascript:void(0);"
                                <?php if ($shop_id > 0) {?>
                                    onclick="shopMediaForm(<?php echo $shop_id ?>);"
                                <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                                <li><a href="javascript:void(0);"
                                <?php if ($shop_id > 0) {?>
                                    onclick="shopCollections(<?php echo $shop_id ?>);"
                                <?php }?>><?php echo Labels::getLabel('LBL_Collections', $adminLangId); ?></a></li>
                        </ul>
                            <div class="tabs_panel_wrap">
                                <div class="row"  id="shopFormBlock">
                                    <?php foreach ($shopTemplateLayouts as $k => $layout) { ?>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12  ">
                                            <div class="shop-template <?php echo ($shopLayoutTemplateId == $layout['ltemplate_id'])?'is--active':'';?>">
                                                <a href="javascript:void(0)" onClick="setTemplate(<?php echo $shop_id; ?>,<?php echo $layout['ltemplate_id']; ?>)">
                                                    <figure class="thumb--square"><img src="<?php echo CommonHelper::generateFullUrl('Image', 'shopLayout', array($layout['ltemplate_id'], 'THUMB'), CONF_WEBROOT_FRONT_URL) ?>" alt=""></figure>
                                                    <p style="text-align: center">
                                                        <?php echo Labels::getLabel('LBL_Layout', $adminLangId);?> <strong><?php echo $layout['ltemplate_id'];?></strong> </p>
                                                </a>
                                            </div>
                                        </div>
                                    <?php }?>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
