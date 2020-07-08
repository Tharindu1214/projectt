<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $keywordFld = $frmProductSearch->getField('keyword');
    $keywordFld->overrideFldType("hidden");
    echo $frmProductSearch->getFormTag();
?>
    <?php if ((UserAuthentication::isUserLogged() && (User::isBuyer())) || (!UserAuthentication::isUserLogged())) { ?>
    <?php } ?>
        <?php /* <li class="is--active d-none d-xl-block">
            <a href="javascript:void(0)" class="switch--grind switch--link-js grid hide--mobile"><i class="icn">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#gridview" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#gridview"></use>
                </svg>
            </i><span class="txt"><?php echo Labels::getLabel('LBL_Grid_View', $siteLangId); ?></span></a>
        </li>
        <li class="d-none d-xl-block">
            <a href="javascript:void(0)" class="switch--list switch--link-js list hide--mobile"><i class="icn">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#listview" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#listview"></use>
                </svg>
            </i><span class="txt"><?php echo Labels::getLabel('LBL_List_View', $siteLangId); ?></span></a>
        </li> */ ?>

    <?php
        echo $frmProductSearch->getFieldHTML('keyword');
        echo $frmProductSearch->getFieldHtml('category');
        echo $frmProductSearch->getFieldHtml('sortOrder');
        echo $frmProductSearch->getFieldHtml('page');
        echo $frmProductSearch->getFieldHtml('shop_id');
        echo $frmProductSearch->getFieldHtml('collection_id');
        echo $frmProductSearch->getFieldHtml('join_price');
        echo $frmProductSearch->getFieldHtml('featured');
        echo $frmProductSearch->getFieldHtml('currency_id');
        echo $frmProductSearch->getFieldHtml('brand_id');
        echo $frmProductSearch->getFieldHtml('top_products');
        echo $frmProductSearch->getExternalJS();
    ?>
    </form>
