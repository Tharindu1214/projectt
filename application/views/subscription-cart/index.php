<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body bg--gray">
    <section class="top-space">
      <div class="container">
        <div class="breadcrumbs">
          <ul>
            <li><a href="<?php echo CommonHelper::generateUrl();?>"><?php echo Labels::getLabel('LBL_Home', $siteLangId); ?> </a></li>
         
            <li><?php echo Labels::getLabel('LBL_Shopping_Cart', $siteLangId); ?> </li>
          </ul>
        </div>
			<div class="white--bg" id="subsriptionCartList">
			</div>
		</div>
    </section>
	<div class="gap"></div>
</div>