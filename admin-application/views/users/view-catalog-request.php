<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionhead">
		<h4>User Details</h4>
	</div>
	<div class="sectionbody space">
	<div class="border-box border-box--space">
		<div class="repeatedrow">
			<form class="web_form form_horizontal">
				<div class="row">
					<div class="col-md-12">
						<h3><i class="ion-person icon"></i> <?php echo Labels::getLabel('LBL_Profile_Information',$adminLangId); ?></h3>
					</div>
				</div>
				<div class="rowbody">
					<div class="listview">
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Full_Name',$adminLangId); ?></dt>
							<dd><?php echo $catalogRequest['user_name'];?></dd>
						</dl>
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Email',$adminLangId); ?></dt>
							<dd><?php echo $catalogRequest['credential_email'];?></dd>
						</dl>
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Username',$adminLangId); ?></dt>
							<dd><?php echo $catalogRequest['credential_username'];?></dd>
						</dl>			
					</div>
				</div>           
			</form>  
		</div>
		<div class="repeatedrow">
			<form class="web_form form_horizontal">
				<div class="row">
					<div class="col-md-12">
						<h3><i class="ion-person icon"></i> <?php echo Labels::getLabel('LBL_Catalog_Information',$adminLangId); ?></h3>
					</div>
				</div>
				<div class="rowbody">
				<div class="listview">
					<dl class="list">
						<dt><?php echo Labels::getLabel('LBL_Reference_Number',$adminLangId); ?></dt>
						<dd><?php echo $catalogRequest['scatrequest_reference'];?></dd>
					</dl>
					<dl class="list">
						<dt><?php echo Labels::getLabel('LBL_Status',$adminLangId); ?></dt>
						<dd><?php echo $reqStatusArr[$catalogRequest['scatrequest_status']];?></dd>
					</dl>
					<dl class="list">
						<dt><?php echo Labels::getLabel('LBL_Content',$adminLangId); ?></dt>
						<dd><?php echo html_entity_decode($catalogRequest['scatrequest_content'],ENT_QUOTES,'utf-8');?></dd>
					</dl>
					<?php if(!empty($attachedFile)){?>
					<dl class="list">
						<dt><?php echo Labels::getLabel('LBL_Attached_File',$adminLangId); ?></dt>
						<dd><a target="_new" href="<?php echo CommonHelper::generateUrl('Users','downloadAttachedFileForCatalogRequest',array($catalogRequest['scatrequest_id']));?>" ><?php echo $attachedFile; ?></a></dd>
					</dl>			
					<?php }?>
					<?php if($catalogRequest['scatrequest_comments']!=''){?>
					<dl class="list">
						<dt><?php echo Labels::getLabel('LBL_Comments/Reason',$adminLangId); ?></dt>
						<dd><?php echo nl2br($catalogRequest['scatrequest_comments']);?></dd>
					</dl>			
					<?php }?>
				</div>
				</div>           
			</form>  
		</div>
	 </div>
	</div>
</section>