<?php if (count($arrListing) > 0){
	foreach($arrListing as $row){
?><li>
	   <div class="msg_db">
		   <img src="<?php echo CommonHelper::generateUrl('Image','user',array($row['message_from_user_id'],'thumb',true));?>" alt="<?php echo $row['message_from_name'];?>">
	   </div>
	   <div class="msg__desc">
            <span class="msg__title"><?php echo $row['message_from_name'];?></span>
			<div class="msg__detail"><?php echo nl2br($row['message_text']);?> </div>
            <span class="msg__date"><?php echo FatDate::format($row['message_date'],true);?></span>
       </div>

	</li>

<?php } }
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmMessageSrchPaging') );
