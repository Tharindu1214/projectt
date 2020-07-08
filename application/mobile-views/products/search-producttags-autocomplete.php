<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data['suggestions'] = $suggestions;


if (1 > count($suggestions)) {
    $status = applicationConstants::OFF;
}
