<div class="popup__body">
	<h2><?php echo $data['scatrequest_title'];?></h2>
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-12">
				<div class="field-set">
					<div class="caption-wraper">
						<label class="field_label"><h5><?php echo Labels::getLabel('LBL_Content',$adminLangId);?></h5></label>
					</div>
					<div class="field-wraper">
						<div class="field_cover">
							<p><?php echo html_entity_decode($data['scatrequest_content'],ENT_QUOTES,'utf-8');?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php if(isset($data['scatrequest_comments']) && $data['scatrequest_comments']!=''){?>
		<div class="row">
			<div class="col-md-12">
				<div class="field-set">
					<div class="caption-wraper">
						<label class="field_label"><h5><?php echo Labels::getLabel('LBL_Comment',$adminLangId);?></h5></label>
					</div>
					<div class="field-wraper">
						<div class="field_cover">
							<p><?php echo nl2br($data['scatrequest_comments']);?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php }?>
	</div>
</div>