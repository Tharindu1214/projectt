<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?><?php defined('SYSTEM_INIT') or die('Invalid Usage.');
/* testing cvhange*/?>

<!--main panel start here-->
<div id="userListing"></div>
<div class="page">
	<div class="container container-fluid">
		<div class="row">
		   <div class="col-lg-12 col-md-12 space">
				<div class="page__title">
					<div class="row">
						<div class="col--first col-lg-6">
							<span class="page__icon"><i class="ion-android-star"></i></span>
							<h5><?php echo Labels::getLabel('LBL_Messages',$adminLangId); ?></h5>
								<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
						</div>
						<!-- div class="col-lg-6">
							<ul class="actions right">
								<li class="droplink">
									<a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
									<div class="dropwrap">
										<ul class="linksvertical">
											<li><a href="#">Action One</a></li>
											<li><a href="#">Action Two</a></li>
											<li><a href="#">Action Three</a></li>
										</ul>
									</div>
								</li>
							</ul>
						</div -->
					</div>

				</div>


				<span class="-gap"></span><span class="-gap"></span>
				<section class="section__controls">
					<div class="row">
						<aside class="col-lg-6 col-md-6 col-sm-6">
                            <?php if($canEdit){ ?>
    							<ul class="controls">
    								<li>
    									<span>
    									<label class="checkbox">
    									<input type="checkbox" class="check-all"><i class="input-helper"></i> </label>
    									</span>
    								</li>
    							</ul>
                            <?php } ?>
							<ul class="controls">
                                <?php if($canEdit){ ?>
								        <li><a href="javascript:void(0)" onclick="deleteRecords()" title="<?php echo Labels::getLabel('LBL_DELETE',$adminLangId); ?>"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/delete-button.svg"></a></li>
                                <?php } ?>
								<li><a href="javascript:void(0)" onclick="reloadList()" title="<?php echo Labels::getLabel('LBL_REFRESH',$adminLangId); ?>"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/update-arrows.svg"></a></li>
                                <?php if($canEdit){ ?>
								            <li><a href="javascript:void(0)" onclick="changeStatus(0)" title="<?php echo Labels::getLabel('LBL_MARK_UNREAD',$adminLangId); ?>"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/envelope.svg"></a></li>
                                <?php } ?>
                                <?php if($canEdit){ ?>
								            <li><a href="javascript:void(0)" onclick="changeStatus(1)" title="<?php echo Labels::getLabel('LBL_MARK_READ',$adminLangId); ?>"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/read-icon.svg"></a></li>
                                <?php } ?>
							</ul>
						</aside>

						<!--<aside class="col-lg-6 col-md-6 col-sm-6 -align-right">
							<span class="-txt-normal">1 to 10 of 324 </span>
							<ul class="controls nomargin_right">
								<li><a href="#"><img alt="" src="images/left-arrow.svg"></a></li>
								<li><a href="#"><img alt="" src="images/right-arrow.svg"></a></li>
							</ul>
						</aside> -->

					</div>
				</section>

				<div id="notificationListing"></div>

			</div>
		</div>
	</div>
</div>

<!--main panel end here-->
