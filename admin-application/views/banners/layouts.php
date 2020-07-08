<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Banner_Layouts_Instructions', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <section class="section">
                    <div class="sectionbody row">
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <div class="shop-template"> 
                                <a rel="facebox" onClick="displayImageInFacebox('<?php echo CONF_WEBROOT_URL; ?>images/banner_layouts/layout-1.jpg');" href="javascript:void(0)">
                                    <figure class="thumb--square"><img width="400px;" style="height:100%" src="<?php echo CONF_WEBROOT_URL; ?>images/banner_layouts/layout-1.jpg" /></figure>
                                    <p><?php echo Labels::getLabel('LBL_Layout_1',$adminLangId);?></p>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <div class="shop-template">
                                <a rel="facebox" onClick="displayImageInFacebox('<?php echo CONF_WEBROOT_URL; ?>images/banner_layouts/layout-2.jpg');" href="javascript:void(0)">
                                    <figure class="thumb--square"><img width="400px;" style="height:100%" src="<?php echo CONF_WEBROOT_URL; ?>images/banner_layouts/layout-2.jpg" /></figure>
                                    <p><?php echo Labels::getLabel('LBL_Layout_2',$adminLangId);?></p>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <div class="shop-template">
                                <a rel="facebox" onClick="displayImageInFacebox('<?php echo CONF_WEBROOT_URL; ?>images/banner_layouts/layout-3.jpg');" href="javascript:void(0)">
                                    <figure class="thumb--square"><img width="400px;"  style="height:100%" src="<?php echo CONF_WEBROOT_URL; ?>images/banner_layouts/layout-3.jpg" /></figure>
                                    <p><?php echo Labels::getLabel('LBL_Layout_3',$adminLangId);?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
