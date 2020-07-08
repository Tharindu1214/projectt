<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'recordCount' => !empty($recordCount) ? $recordCount : 0,
    'collection' => !empty($collection) ? $collection : (object)array(),
    'collectionItems' => !empty($collections) ? $collections : array(),
);


if (empty((array)$collection)) {
    $status = applicationConstants::OFF;
}
