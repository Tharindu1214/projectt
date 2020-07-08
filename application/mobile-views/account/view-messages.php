<?php defined('SYSTEM_INIT') or die('Invalid Usage.');


$threadDetails['threadTypeTitle'] = array_key_exists('thread_type', $threadDetails) ? $threadTypeArr[$threadDetails['thread_type']] : '';

$data = array(
    'threadDetails' => $threadDetails,
    'threadTypeArr' => $threadTypeArr,
);

if (empty($threadDetails)) {
    $status = applicationConstants::OFF;
}
