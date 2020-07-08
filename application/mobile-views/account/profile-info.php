<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'personalInfo' => (object)$personalInfo,
    'bankInfo' => (object)$bankInfo,
    'privacyPolicyLink' => CommonHelper::generateFullUrl('cms', 'view', array($privacyPolicyLink)),
    'faqLink' => CommonHelper::generateFullUrl('custom', 'faq'),
);

if (empty((array)$personalInfo) && empty((array)$bankInfo)) {
    $status = applicationConstants::OFF;
}
