<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$defaultCurrencyId = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
foreach ($currencies as &$currency) {
    $currency['isDefault'] = ($currency['currency_id'] == $defaultCurrencyId ? 1 : 0);
}
$data = array('currencies' => $currencies);
