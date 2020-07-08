<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array('options' => (empty($options) ? array() : $options));

if (empty($options)) {
    $status = applicationConstants::OFF;
}
