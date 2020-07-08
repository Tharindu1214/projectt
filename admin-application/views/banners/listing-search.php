<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial'=>Labels::getLabel('LBL_Sr._No', $adminLangId),
    'banner_title'=>Labels::getLabel('LBL_Title', $adminLangId),
    'banner_type'=>Labels::getLabel('LBL_Type', $adminLangId),
    'banner_img' => Labels::getLabel('LBL_Image', $adminLangId),
    'banner_target' => Labels::getLabel('LBL_Target', $adminLangId),
    'banner_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId),
    );
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row) {
    $sr_no++;
    /* $tr = $tbl->appendElement('tr',array('class' => ($row['banner_active'] != applicationConstants::ACTIVE) ? 'fat-inactive' : '' )); */
    $tr = $tbl->appendElement('tr', array());
    foreach ($arr_flds as $key=>$val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
            break;
            case 'banner_target':
                $td->appendElement('plaintext', array(), $linkTargetsArr[$row[$key]], true);
            break;
            case 'banner_title':
                $title = ($row['banner_title'] !='')?$row['banner_title']:$row['promotion_name'];
                $td->appendElement('plaintext', array(), $title, true);
            break;
            case 'banner_type':
                $td->appendElement('plaintext', array(), $bannerTypeArr[$row[$key]], true);
            break;
            case 'banner_active':
                /* $td->appendElement('plaintext', array(), $activeInactiveArr[$row[$key]], true); */
                $active = "";
                if ($row['banner_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' .applicationConstants::YES. ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str='<label class="statustab -txt-uppercase">
                     <input '.$active.' type="checkbox" id="switch'.$row['banner_id'].'" value="'.$row['banner_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                                      	<i class="switch-handles '.$statusClass.'"></i></label>';
                    $td->appendElement('plaintext', array(), $str, true);
            break;
            case 'banner_img':
                $desktop_url = '';
                $tablet_url = '';
                $mobile_url = '';
                if (!AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $row['banner_id'], 0, $adminLangId)) {
                    continue 2;
                } else {
                    $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $row['banner_id'], 0, $adminLangId);
                    foreach ($slideArr as $slideScreen) {
                        $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
                        switch ($slideScreen['afile_screen']) {
                            case applicationConstants::SCREEN_MOBILE:
                                $mobile_url = '<480:' .CommonHelper::generateFullUrl('Banner', 'Thumb', array($row['banner_id'], $adminLangId, applicationConstants::SCREEN_MOBILE)).$uploadedTime.",";
                                break;
                            case applicationConstants::SCREEN_IPAD:
                                $tablet_url = ' <768:' .CommonHelper::generateFullUrl('Banner', 'Thumb', array($row['banner_id'], $adminLangId, applicationConstants::SCREEN_IPAD)).$uploadedTime.','.'  <1024:' .CommonHelper::generateFullUrl('Banner', 'Thumb', array($row['banner_id'], $adminLangId, applicationConstants::SCREEN_IPAD)).$uploadedTime.",";
                                break;
                            case applicationConstants::SCREEN_DESKTOP:
                                $desktop_url = ' >1024:' .CommonHelper::generateFullUrl('Banner', 'Thumb', array($row['banner_id'], $adminLangId, applicationConstants::SCREEN_DESKTOP)).$uploadedTime.",";
                                break;
                        }
                    }
                }

                $uploadedTime = AttachedFile::setTimeParam($row['banner_img_updated_on']);
                $img = '<img src="'.CommonHelper::generateFullUrl('Banner', 'Thumb', array($row['banner_id'] , $adminLangId), CONF_WEBROOT_FRONT_URL).$uploadedTime.'" />';
                $td->appendElement('plaintext', array(), $img, true);
            break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                    $innerLiEdit=$innerUl->appendElement('li');
                    $innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addBannerForm(".$row['banner_blocation_id'].",".$row['banner_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    /* $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>'Delete',"onclick"=>"deleteBanner(".$row['banner_id'].")"),'<i class="ion-android-delete icon"></i>', true); */
                }
            break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
            break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmListingSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
