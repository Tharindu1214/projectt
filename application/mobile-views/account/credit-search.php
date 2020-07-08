<?php defined('SYSTEM_INIT') or die('Invalid Usage.');


$txnStatusArr = $statusArr;


foreach ($arrListing as $key => $value) {
    $arrListing[$key]['utxn_statusLabel'] = $txnStatusArr[$value['utxn_status']];
    $arrListing[$key]['utxn_id'] = Transactions::formatTransactionNumber($value['utxn_id']);
    $arrListing[$key]['balance'] = CommonHelper::displayMoneyFormat($value['balance'], false, false, false);
    $arrListing[$key]['utxn_credit'] = CommonHelper::displayMoneyFormat($value['utxn_credit'], false, false, false);
    $arrListing[$key]['utxn_debit'] = CommonHelper::displayMoneyFormat($value['utxn_debit'], false, false, false);
}

$data = array(
    'creditsListing' => array_values($arrListing),
    'page' => $page,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'userWalletBalance' => CommonHelper::displayMoneyFormat($userWalletBalance, false, false, false),
    'userTotalWalletBalance' => CommonHelper::displayMoneyFormat($userTotalWalletBalance, false, false, false),
    'promotionWalletToBeCharged' => $promotionWalletToBeCharged,
    'withdrawlRequestAmount' => CommonHelper::displayMoneyFormat($withdrawlRequestAmount, false, false, false),
    'txnStatusArr' => $txnStatusArr,
);

if (1 > $recordCount) {
    $status = applicationConstants::OFF;
}
