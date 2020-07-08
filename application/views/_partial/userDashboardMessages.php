<div class="cards-header  p-3">
	<h5 class="cards-title"><?php echo Labels::getLabel('LBL_Message',$siteLangId);?></h5>
	<?php if (count($messages) > 0){ ?>
	<div class="action"><a href="<?php echo CommonHelper::generateUrl('Account','messages');?>" class="link"><?php echo Labels::getLabel('LBL_View_All',$siteLangId);?></a></div>
<?php }?>
</div>
<?php if (count($messages) > 0){ ?>
<div class="cards-content pl-4 pr-4 ">
	<div class="messages-list">
		<ul>
			<?php foreach($messages as $row){
					$liClass = 'is-read';
					if($row['message_is_unread'] == Thread::MESSAGE_IS_UNREAD ) {
						$liClass = '';
					}
			?>
			<li>
				<div class="msg_db"><img src="<?php echo CommonHelper::generateUrl('Image','user',array($row['message_from_user_id'],'thumb',true));?>" alt="<?php echo $row['message_from_name']; ?>"></div>
				<div class="msg__desc">
					<span class="msg__title"><?php echo htmlentities($row['message_from_name']);?></span>
					<p class="msg__detail"><?php  echo CommonHelper::truncateCharacters($row['message_text'],85,'','',true);?></p>
                    <span class="msg__date"><?php echo FatDate::format($row['message_date'],true);?></span>
				</div>
			</li>
			<?php }?>
		</ul>
	</div>
</div>
<?php }else{?>
	<div class="cards-content pl-4 pr-4 ">
		<div class="messages-list">
			<?php echo Labels::getLabel('LBL_No_record_found',$siteLangId); ?>
		</div>
	</div>
<?php }?>
