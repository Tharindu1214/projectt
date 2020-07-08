<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

?>
<div class="box" style="text-align: center;">
<img src="<?php echo CommonHelper::generateUrl('user', 'photo', array($data['user_id'], 100, 100)); ?>">
<form method="post" enctype="multipart/form-data" action="<?php echo CommonHelper::generateUrl('profile', 'updatePhoto') ?>">
	Change Photo <input type="file" name="photo" onchange="this.form.submit();">
</form>
<br>
<b><?php echo $data['user_name'] ?></b><br>
DOB: <?php echo FatDate::format($data['user_dob']); ?><br>
Member Since: <?php echo FatDate::format($data['user_regdate']); ?><br>
Ph: <?php echo $data['user_phone']; ?><br>
<p><?php echo nl2br($data['user_profile_info']); ?>
</p>
<a href="<?php echo CommonHelper::generateUrl('profile','edit-form');?>">Edit</a>
</div>
