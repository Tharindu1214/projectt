<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
'urp_points'    =>    Labels::getLabel('LBL_Points', $siteLangId),
'urp_comments'    =>    Labels::getLabel('LBL_Description', $siteLangId),
'urp_date_added' =>    Labels::getLabel('LBL_Added_Date', $siteLangId),
'urp_date_expiry'    =>    Labels::getLabel('LBL_Expiry_Date', $siteLangId),
);

if ($convertReward == 'coupon') {
    $arr_flds = array_merge(array('select_option'=>''), $arr_flds);
}

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' =>'' ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_option':
                $td->appendElement('plaintext', array(), '<input class="rewardOptions-Js" type="checkbox" name="rewardOptions[]" value="'.$row['urp_id'].'">', true);
                break;
            /* case 'urp_used':
                $yesNoArr = applicationConstants::getYesNoArr($siteLangId);
                if($row[$key] != applicationConstants::YES && $row['urp_date_expiry'] !='0000-00-00' && $row['urp_date_expiry'] < date('Y-m-d')){
                    $td->appendElement('plaintext', array(), Labels::getLabel('LBL_Expired',$siteLangId) , true);
                }else{
                    $td->appendElement('plaintext', array(), $yesNoArr[$row[$key]] , true);
                }
                break; */
            case 'urp_date_added':
                $td->appendElement('plaintext', array(), FatDate::format($row[$key]), true);
                break;
            case 'urp_date_expiry':
                $expiryDate = $row[$key];
                $expiryDate = ($expiryDate =='0000-00-00')?CommonHelper::displayNotApplicable($siteLangId, ''):FatDate::format($row[$key]);
                $td->appendElement('plaintext', array(), $expiryDate, true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmRewardPointSearchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
