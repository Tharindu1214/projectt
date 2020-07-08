<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'token' => $token,
    'user_name' => !empty($userInfo['user_name']) ? $userInfo['user_name'] : '',
    'user_phone' => !empty($userInfo['user_phone']) ? $userInfo['user_phone'] : '',
    'credential_email' => !empty($userInfo['credential_email']) ? $userInfo['credential_email'] : '',
    'user_id' => !empty($userInfo['user_id']) ? $userInfo['user_id'] : '',
    'user_image' => !empty($userInfo['user_id']) ? CommonHelper::generateFullUrl('image', 'user', array($userInfo['user_id'],'ORIGINAL')) : ''
);

if (empty($userInfo)) {
    $status = applicationConstants::OFF;
}
