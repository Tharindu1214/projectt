<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionhead">
		<h4>User Details</h4>
	</div>
	<div class="sectionbody space">
	<div class="add border-box border-box--space">
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
							<dd><?php echo $supplierRequest['user_name'];?></dd>
						</dl>
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Email',$adminLangId); ?></dt>
							<dd><?php echo $supplierRequest['credential_email'];?></dd>
						</dl>
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Username',$adminLangId); ?></dt>
							<dd><?php echo $supplierRequest['credential_username'];?></dd>
						</dl>			
					</div>
				</div>           
			</form>  
		</div>
		<div class="repeatedrow">
			<form class="web_form form_horizontal">
				<div class="row">
					<div class="col-md-12">
						<h3><i class="ion-person icon"></i> <?php echo Labels::getLabel('LBL_Seller_Information',$adminLangId); ?></h3>
					</div>
				</div>
				<div class="rowbody">
					<div class="listview">
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Reference_Number',$adminLangId); ?></dt>
							<dd><?php echo $supplierRequest['usuprequest_reference'];?></dd>
						</dl>
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Status',$adminLangId); ?></dt>
							<dd><?php echo $reqStatusArr[$supplierRequest['usuprequest_status']];?></dd>
						</dl>
						<?php if($supplierRequest['usuprequest_comments']!=''){?>
						<dl class="list">
							<dt><?php echo Labels::getLabel('LBL_Comments/Reason',$adminLangId); ?></dt>
							<dd><?php echo nl2br($supplierRequest['usuprequest_comments']);?></dd>
						</dl>			
						<?php }?>
					</div>
				</div>           
			</form>  
		</div>
		<div class="repeatedrow">
			<form class="web_form form_horizontal">
				<div class="row">
					<div class="col-md-12">
						<h3><i class="ion-person icon"></i> <?php echo Labels::getLabel('LBL_Additional_Information',$adminLangId); ?></h3>
					</div>
				</div>
				<div class="rowbody">
					<div class="listview">
						<?php foreach( $supplierRequest['field_values'] as $val ){ ?>
						<dl class="list">
							<dt><?php 
								if( $val['sformfield_caption'] != '' ){
									echo $val['sformfield_caption'];
								} else {
									echo $val['sformfield_identifier'];
								} ?>
							</dt>
							<dd><?php if( $val['afile_physical_path']!='' ){
								echo "<a href='". CommonHelper::generateUrl( 'Users', 'downloadAttachment', array( $supplierRequest['user_id'], $val['sfreqvalue_formfield_id']) )."'>" . $val['sfreqvalue_text'] . "</a>";
							} else {
								echo nl2br( $val['sfreqvalue_text'] );
							}
							?></dd>
						</dl>
						<?php }?>	
					</div>
				</div>           
			</form>  
		</div>
		</div>		
	</div>
</section>