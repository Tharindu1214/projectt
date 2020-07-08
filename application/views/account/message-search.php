<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($arr_listing) && is_array($arr_listing)) { ?>
    <div class="messages-list">
        <ul>
            <?php
            foreach ($arr_listing as $sn => $row) {
                $liClass = 'is-read';
                $toName = $row['message_to_name'];

                $toUserId = $row['message_to_user_id'];
                if ($row['message_to'] == $loggedUserId) {
                    if ($row['message_is_unread'] == Thread::MESSAGE_IS_UNREAD) {
                        $liClass = '';
                    }
                    $toName = $row['message_from_name'];
                    $toUserId = $row['message_from_user_id'];
                }
                $userImgUpdatedOn = User::getAttributesById($toUserId, 'user_img_updated_on');
                $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                $toImage = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'user', array($toUserId,'thumb',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                ?>
                <li class="<?php echo $liClass; ?>">
                    <div class="msg_db"><img src="<?php echo $toImage; ?>" alt="<?php echo $toName; ?>"></div>
                    <div class="msg__desc">
                        <span class="msg__title"><?php echo htmlentities($toName); ?></span>
                        <p class="msg__detail"><?php  echo CommonHelper::truncateCharacters($row['message_text'], 85, '', '', true); ?></p>
                        <span class="msg__date"><?php echo FatDate::format($row['message_date'], true); ?></span>
                    </div>
                    <ul class="actions">
                        <li><a href="<?php echo CommonHelper::generateUrl('Account', 'viewMessages', array($row['thread_id'],$row['message_id'])); ?>"><i class="fa fa-eye"></i></a></li>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } else {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false);
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmMessageSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToMessageSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
