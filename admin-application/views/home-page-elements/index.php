<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="row">	
	<div class="col-sm-12">  
		<h1><?php echo Labels::getLabel('LBL_Adds_Management',$adminLangId); ?></h1>
		<section class="section">
			<div class="sectionhead"><h4><?php echo Labels::getLabel('LBL_Home_Page_Section(First_Half)',$adminLangId); ?></h4></div>
			  <div class="sectionbody space">
				<div class="row clearfix">
					<div class="col-lg-10 col-md-10 col-sm-10">
						<div class="row">
							<div class="group--elements clearfix">
								<div class="col-md-4">
									<span href="#" class="preview--thumb"></span>
								</div>
								<div class="col-md-4">
									<span href="#" class="preview--thumb"></span>
								</div>
								<div class="col-md-4">
									<span href="#" class="preview--thumb"></span>
								</div>
								<div class="overlayer">
									<ul class="actions">
										<li><a title="Edit" href="javascript:void(0)" onClick="bannerLocation(3)"><i class="ion-edit icon"></i></a></li>
										<!--<li><a title="Delete" href="javascript:void(0)"><i class="ion-android-delete icon"></i></a></li>-->
										<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('banners','listing',array(3));?>"><i class="ion-eye icon"></i></a></li>
									</ul>
								</div>
							</div>
						</div>
						<span class="gap"></span>
						<div class="row clearfix">
							<div class="col-md-4">
								<div style="height:315px; text-align:left;" class="preview--thumb preview--thumb-border">
									<span class="preview--heading" style="width:60%;"></span>
									<span class="preview--heading" style="height:5px;width:100%;"></span>
									<span class="preview--heading" style="height:5px;width:80%;"></span>
									<span class="preview--heading" style="height:5px;width:100%;"></span>
									<span class="gap"></span>
									<span class="preview--heading" style="height:5px;width:100%;"></span>
									<span class="preview--heading" style="height:5px;width:100%;"></span>
									<span class="preview--heading" style="height:5px;width:100%;"></span>
									<span class="preview--heading" style="height:5px;width:80%;"></span>
									<span class="preview--heading" style="height:5px;width:100%;"></span>
								</div>
							</div>
							<div class="col-md-8">
								<div class="row">
									<div class="group--elements clearfix">
										<div class="col-md-6">
											<a href="#" class="preview--thumb" style="height:200px;"></a>
										</div>
										<div class="col-md-6">
											<a href="#" class="preview--thumb" style="height:200px;"></a>
										</div>
										
										<div class="overlayer">
											<ul class="actions">
												<li><a title="Edit" href="javascript:void(0)" onClick="bannerLocation(4)"><i class="ion-edit icon"></i></a></li>
												<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('banners','listing',array(4));?>"><i class="ion-eye icon"></i></a></li>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-8">
								<div class="row">
									<div class="group--elements clearfix">
										<span class="gap"></span>
										<div class="col-md-12">
											<a href="#" class="preview--thumb" style="height:100px;"></a>
										</div>
										<div class="overlayer">
											<ul class="actions">
												<li><a title="Edit" href="javascript:void(0)" onClick="bannerLocation(5)"><i class="ion-edit icon"></i></a></li>
												<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('banners','listing',array(5));?>"><i class="ion-eye icon"></i></a></li>
											</ul>
										</div>
									</div>
								</div>
							</div>								   
						</div>
						<span class="gap"></span>
						
						<span class="gap"></span>
						<span class="preview--heading"></span>
						<div class="row clearfix">
							<div class="group--elements clearfix">
								<div class="col-md-6">
									<a href="#" class="preview--thumb" style="height:340px;"></a>
								</div>
								<div class="col-md-6">
									<a href="#" class="preview--thumb" style="height:340px;"></a>
								</div>
								<span class="gap"></span>

								<div class="col-md-6">
									<a href="#" class="preview--thumb" style="height:340px;"></a>
								</div>
								<div class="col-md-6">
									<a href="#" class="preview--thumb" style="height:340px;"></a>
								</div>
								
								<?php
								/* <div class="overlayer">
									<ul class="actions">
										<li><a title="Edit" href="javascript:void(0)" onClick="updateStatusForm(<?php echo Extrapage::HOME_PAGE_CONTENT_BLOCK1;?>)"><i class="ion-edit icon"></i></a></li>
										<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('ContentBlock','index',array(Extrapage::HOME_PAGE_CONTENT_BLOCK1));?>"><i class="ion-eye icon"></i></a></li>
									</ul>
								</div> */
								?>
							</div>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2">
						<div class="group--elements group--elements-even clearfix">
							<a href="#" class="preview--thumb" style="height:300px;"></a>
							<span class="gap"></span>
							<a href="#" class="preview--thumb" style="height:300px;"></a>
							<span class="gap"></span>
							<a href="#" class="preview--thumb" style="height:300px;"></a>
							<span class="gap"></span>
							<a href="#" class="preview--thumb" style="height:300px;"></a>
							<div class="overlayer">
								<ul class="actions">
									<li><a title="Edit" href="javascript:void(0)" onClick="bannerLocation(1)"><i class="ion-edit icon"></i></a></li>
									<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('banners','listing',array(1));?>"><i class="ion-eye icon"></i></a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			 </div>
		</section>
		<section class="section">
			<div class="sectionhead"><h4><?php echo Labels::getLabel('LBL_Home_Page_Section(Second_Half)',$adminLangId); ?></h4></div>
			<div class="sectionbody space">
					<div class="row clearfix">
						<div class="col-lg-12 col-md-12 col-sm-12">
							
							<span class="preview--heading"></span>
							<div class="row clearfix">
								<div class="group--elements clearfix">
									<a href="#" class="col-md-2">
										<div  style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<?php 
									/* <div class="overlayer">
										<ul class="actions">
											<li><a title="Edit" href="javascript:void(0)" onClick="updateStatusForm(<?php echo Extrapage::HOME_PAGE_CONTENT_BLOCK2;?>)"><i class="ion-edit icon"></i></a></li>
											<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('ContentBlock','index',array(Extrapage::HOME_PAGE_CONTENT_BLOCK2));?>"><i class="ion-eye icon"></i></a></li>
										</ul>
									</div> */ ?>
								</div>
							</div>
						</div>						
					</div>
			 </div>
		</section>
		<section class="section">
			<div class="sectionhead"><h4><?php echo Labels::getLabel('LBL_Home_Page_Section(Third_Half)',$adminLangId); ?></h4></div>
			<div class="sectionbody space">
					<div class="row clearfix">
						<div class="col-lg-10 col-md-10 col-sm-10">
							
							<span class="preview--heading"></span>
							<div class="row clearfix">
								<div class="col-md-12">
									<div class="preview--thumb preview--thumb-border" style="height:315px;"></div>
								</div>
							</div>
							<span class="gap"></span><span class="gap"></span>
							<span class="preview--heading"></span>
							<div class="row clearfix">
								<div class="col-md-12">
									<div class="preview--thumb preview--thumb-border" style="height:315px;"></div>
								</div>
							</div>
							<span class="gap"></span><span class="gap"></span>
							<div class="row clearfix">
								<div class="group--elements clearfix">
									<div class="col-md-12">
									   <a href="#" class="preview--thumb" style="height:100px;"></a>
									</div>
									<div class="overlayer">
										<ul class="actions">
											<li><a title="Edit" href="javascript:void(0)" onClick="bannerLocation(7)"><i class="ion-edit icon"></i></a></li>
											<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('banners','listing',array(7));?>"><i class="ion-eye icon"></i></a></li>
										</ul>
									</div>
								</div>
							</div>
							<span class="gap"></span><span class="gap"></span>
							<span class="preview--heading"></span>
							<div class="row clearfix">
								<div class="col-md-12">
									<div class="preview--thumb preview--thumb-border" style="height:315px;"></div>
								</div>
							</div>
							
							
						</div>
						<div class="col-lg-2 col-md-2 col-sm-2">
							<div class="group--elements group--elements-even clearfix">
								<a href="#" class="preview--thumb" style="height:300px;"></a>
								<span class="gap"></span>
								<a href="#" class="preview--thumb" style="height:300px;"></a>
								<span class="gap"></span>
								<a href="#" class="preview--thumb" style="height:300px;"></a>
								<span class="gap"></span>
								<a href="#" class="preview--thumb" style="height:300px;"></a>
								<div class="overlayer">
									<ul class="actions">
										<li><a title="Edit" href="javascript:void(0)" onClick="bannerLocation(6)"><i class="ion-edit icon"></i></a></li>
										<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('banners','listing',array(6));?>"><i class="ion-eye icon"></i></a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
			 </div>
		</section>
		
		<section class="section">
			<div class="sectionhead"><h4><?php echo Labels::getLabel('LBL_Home_Page_Section(Fourth_Half)',$adminLangId); ?></h4></div>
			<div class="sectionbody space">
					<div class="row clearfix">
						<div class="col-lg-12 col-md-12 col-sm-12">
							
							<span class="preview--heading"></span>
							<div class="row clearfix">
								<div class="group--elements clearfix">
									<a href="#" class="col-md-4">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<a href="#" class="col-md-2">
										<div class="preview--thumb" style="height:300px;"></div>
									</a>
									<div class="overlayer">
										<ul class="actions">
											<li><a title="Edit" href="javascript:void(0)" onClick="updateStatusForm(<?php echo Extrapage::HOME_PAGE_CONTENT_BOTTOM_TOP;?>)"><i class="ion-edit icon"></i></a></li>
											<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('ContentBlock','index',array(Extrapage::HOME_PAGE_CONTENT_BOTTOM_TOP));?>"><i class="ion-eye icon"></i></a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						
					</div>
			 </div>
		</section>
		
		<section class="section">
			<div class="sectionhead"><h4><?php echo Labels::getLabel('LBL_Home_Page_Section(Fifth_Half)',$adminLangId); ?></h4></div>
			<div class="sectionbody space">
					<div class="row clearfix">
						<div class="col-lg-12 col-md-12 col-sm-12">
							<div class="row clearfix">
							   <div class="group--elements clearfix">
									<div class="col-md-3">
										<a href="#" class="preview--thumb preview--thumb-content" style="height:200px;">
											<span class="preview--icon"></span>
											<span class="preview--heading"></span>
											<span class="preview--txt"></span>
											<span class="preview--txt" style="width:60%"></span>
										</a>
									</div>
									<div class="col-md-3">
										<a href="#" class="preview--thumb preview--thumb-content" style="height:200px;">
											<span class="preview--icon"></span>
											<span class="preview--heading"></span>
											<span class="preview--txt"></span>
											<span class="preview--txt" style="width:60%"></span>
										</a>
									</div>
									<div class="col-md-3">
										<a href="#" class="preview--thumb preview--thumb-content" style="height:200px;">
											<span class="preview--icon"></span>
											<span class="preview--heading"></span>
											<span class="preview--txt"></span>
											<span class="preview--txt" style="width:60%"></span>
										</a>
									</div>
									<div class="col-md-3">
										<a href="#" class="preview--thumb preview--thumb-content" style="height:200px;">
											<span class="preview--icon"></span>
											<span class="preview--heading"></span>
											<span class="preview--txt"></span>
											<span class="preview--txt" style="width:60%"></span>
										</a>
									</div>
									<div class="overlayer">
										<ul class="actions">
											<li><a title="Edit" href="javascript:void(0)" onClick="updateStatusForm(<?php echo Extrapage::HOME_PAGE_CONTENT_BOTTOM;?>)"><i class="ion-edit icon"></i></a></li>
											<li><a title="View" target="_blank" href="<?php echo CommonHelper::generateUrl('ContentBlock','index',array(Extrapage::HOME_PAGE_CONTENT_BOTTOM));?>"><i class="ion-eye icon"></i></a></li>
										</ul>
									</div>
								</div>
								
							</div>
						</div>
						
					</div>
			 </div>
		</section>
	</div>		
</div>