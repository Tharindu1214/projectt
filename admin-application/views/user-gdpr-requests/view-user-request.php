<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionbody space">
		<div class="add border-box border-box--space">
			<div class="repeatedrow">
				<form class="web_form form_horizontal">
					<div class="row">
						<div class="col-md-12">
							<h3><i class="ion-person icon"></i> <?php echo Labels::getLabel('LBL_User_Request',$adminLangId); ?></h3>
						</div>
					</div>
					<div class="rowbody">
						<div class="listview">
							<dl class="list">
								<dt><?php echo Labels::getLabel('LBL_Full_Name',$adminLangId); ?></dt>
								<dd><?php echo CommonHelper::displayNotApplicable( $adminLangId, $userRequest['user_name'] );?></dd>
							</dl>
							<dl class="list">
								<dt><?php echo Labels::getLabel('LBL_Email',$adminLangId); ?></dt>
								<dd><?php echo $userRequest['credential_email'];?></dd>
							</dl>
							<dl class="list">
								<dt><?php echo Labels::getLabel('LBL_Username',$adminLangId); ?></dt>
								<dd><?php echo $userRequest['credential_username'];?></dd>
							</dl>	
							<dl class="list">
								<dt><?php echo Labels::getLabel('LBL_Purpose_of_request',$adminLangId); ?></dt>
								<dd><?php echo $userRequest['ureq_purpose'];?></dd>
							</dl>
							<dl class="list">
								<dt><?php echo Labels::getLabel('LBL_Request_Date',$adminLangId); ?></dt>
								<dd><?php echo $userRequest['ureq_date'];?></dd>
							</dl>			
						</div>
					</div>           
				</form>  
			</div>
		</div>		
	</div>
</section>