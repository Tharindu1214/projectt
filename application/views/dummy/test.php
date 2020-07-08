<div class="xzoom-container">
  <img class="xzoom" id="xzoom-default" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/01_b_car.jpg" xoriginal="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/01_b_car.jpg" />
  <div class="xzoom-thumbs">
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/01_b_car.jpg"><img class="xzoom-gallery" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/thumbs/01_b_car.jpg"  xpreview="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/01_b_car.jpg" title="The description goes here"></a>
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/02_o_car.jpg"><img class="xzoom-gallery" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/02_o_car.jpg" title="The description goes here"></a>
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/03_r_car.jpg"><img class="xzoom-gallery" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/03_r_car.jpg" title="The description goes here"></a>
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/04_g_car.jpg"><img class="xzoom-gallery" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/04_g_car.jpg" title="The description goes here"></a>
  </div>
</div>

<div class="xzoom-container">
  <img class="xzoom1" id="xzoom-default" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/01_b_car.jpg" xoriginal="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/01_b_car.jpg" />
  <div class="xzoom-thumbs">
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/01_b_car.jpg"><img class="xzoom-gallery1" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/thumbs/01_b_car.jpg"  xpreview="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/01_b_car.jpg" title="The description goes here"></a>
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/02_o_car.jpg"><img class="xzoom-gallery1" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/02_o_car.jpg" title="The description goes here"></a>
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/03_r_car.jpg"><img class="xzoom-gallery1" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/03_r_car.jpg" title="The description goes here"></a>
	<a href="<?php echo CONF_WEBROOT_URL;?>images/gallery/original/04_g_car.jpg"><img class="xzoom-gallery1" width="80" src="<?php echo CONF_WEBROOT_URL;?>images/gallery/preview/04_g_car.jpg" title="The description goes here"></a>
  </div>
</div>

<script>
	$(document).ready(function() {
        $('.xzoom, .xzoom-gallery').xzoom({zoomWidth: 400, title: true, tint: '#333', Xoffset: 15, position:'left'});
        $('.xzoom1, .xzoom-gallery1').xzoom({zoomWidth: 400, title: true, tint: '#333', Xoffset: 15});
	});
</script>