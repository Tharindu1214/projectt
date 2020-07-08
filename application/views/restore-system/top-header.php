<div class="demo-header no-print">
    <div class="restore-wrapper">
        <a href="javascript:void(0)" onclick="showRestorePopup()">
        
		<p class="restore__content">Database Restore in</p>
           <div class="restore__progress">
            <div class="restore__progress-bar" role="progressbar" style="width:25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="restore__counter" id="restoreCounter">00:00:00</span>                        
        </a>
    </div>
    <ul class="switch-interface">
        <?php 
            $url = CommonHelper::generateUrl('admin'); 
            $title  = 'Admin';
            if (strpos($_SERVER ['REQUEST_URI'], CONF_WEBROOT_BACKEND) !== false) {
                $url = CommonHelper::generateUrl('', '', array(), CONF_WEBROOT_FRONTEND);
                $title  = 'Marketplace';
            }  
        ?>
        <li><a title="<?php echo $title;?>" href="<?php echo $url;?>"><i class="icn icn--admin">
                    <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_FRONTEND; ?>images/retina/sprite.svg#admin" href="<?php echo CONF_WEBROOT_FRONTEND; ?>images/retina/sprite.svg#admin"></use>
                    </svg>
                </i></a></li>
       <?php /* ?> <li class="is-active"><a href="javascript:void(0)" onClick="setDemoLayout(360)"><i class="icn icn--desktop">
                    <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_FRONTEND; ?>images/retina/sprite.svg#desktop" href="<?php echo CONF_WEBROOT_FRONTEND; ?>images/retina/sprite.svg#desktop"></use>
                    </svg>
                </i></a></li>
        <li><a href="javascript:void(0);" onClick="setDemoLayout(360)"><i class="icn icn--mobile">
                    <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_FRONTEND; ?>images/retina/sprite.svg#mobile" href="<?php echo CONF_WEBROOT_FRONTEND; ?>images/retina/sprite.svg#mobile"></use>
                    </svg>
                </i></a></li> <?php  */?>
    </ul>    
    <div class="demo-cta">
        <a target="_blank" href="https://www.yo-kart.com/multivendor-ecommerce-marketplace-platform.html" class=" btn btn-primary ripplelink">Start Your Marketplace</a> &nbsp;		
		<a href="javascript:void(0);" class="request-demo btn btn--primary-border  ripplelink" id="btn-demo" >Request a Demo</a>
       <a href="javascript:void(0)" class="close-layer" id="demoBoxClose"></a>
    </div>	
</div>