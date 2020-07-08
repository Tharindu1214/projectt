<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial'=> Labels::getLabel('LBL_S.No.', $adminLangId),
    'user'=>Labels::getLabel('LBL_User', $adminLangId),
    'shop_name'=>Labels::getLabel('LBL_Shop', $adminLangId),
    'type'    => Labels::getLabel('LBL_User_Type', $adminLangId),
    'user_regdate'=>Labels::getLabel('LBL_Reg._Date', $adminLangId),
    'credential_active'=>Labels::getLabel('LBL_Status', $adminLangId),
    'credential_verified'=>Labels::getLabel('LBL_verified', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');

foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page==1 ? 0: $pageSize*($page-1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="user_ids[]" value='.$row['user_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'user':
                $userDetail = '<strong>'.Labels::getLabel('LBL_N:', $adminLangId).' </strong>'.$row['user_name'].'<br/>';
                $userDetail .= '<strong>'.Labels::getLabel('LBL_UN:', $adminLangId).' </strong>'.$row['credential_username'].'<br/>';
                $userDetail .= '<strong>'.Labels::getLabel('LBL_Email:', $adminLangId).' </strong>'.$row['credential_email'].'<br/>';
                $userDetail .= '<strong>'.Labels::getLabel('LBL_User_ID:', $adminLangId).' </strong>'.$row['user_id'].'<br/>';
                $td->appendElement('plaintext', array(), $userDetail, true);
                break;
            case 'shop_name':
                if ($row[$key]!='') {
                    if ($canViewShops) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Shops').'", '.$row['shop_id'].')'), $row[$key], true);
                    } else {
                        $td->appendElement('plaintext', array(), $row[$key], true);
                    }
                } else {
                    $td->appendElement('plaintext', array(), Labels::getLabel('LBL_N/A', $adminLangId), true);
                }
                break;
            case 'credential_active':
                $active = "active";
                if (!$row['credential_active']) {
                    $active = '';
                }
                $statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
                $str='<label id="'.$row['user_id'].'" class="statustab '.$active.'" onclick="'.$statucAct.'">
                  <span data-off="'. Labels::getLabel('LBL_Active', $adminLangId) .'" data-on="'. Labels::getLabel('LBL_Inactive', $adminLangId) .'" class="switch-labels"></span>
                  <span class="switch-handles"></span>
                </label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'user_regdate':
                $td->appendElement('plaintext', array(), FatDate::format(
                    $row[$key],
                    true,
                    true,
                    FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())
                ));
                break;
            case 'type':
                $str = '';
                $arr = User::getUserTypesArr($adminLangId);
                if ($row['user_is_buyer']) {
                    $str .= $arr[User::USER_TYPE_BUYER].'<br/>';
                }
                if ($row['user_is_supplier']) {
                    $str .= $arr[User::USER_TYPE_SELLER].'<br/>';
                }
                if ($row['user_is_advertiser']) {
                    $str .= $arr[User::USER_TYPE_ADVERTISER].'<br/>';
                }
                if ($row['user_is_affiliate']) {
                    $str .= $arr[User::USER_TYPE_AFFILIATE].'<br/>';
                }

                if ($str == '' && $row['user_registered_initially_for'] != 0) {
                    $str = '<span class="label label-danger">Signing Up For: '. User::getUserTypesArr($adminLangId)[$row['user_registered_initially_for']] .'</span>';
                }

                $td->appendElement('plaintext', array(), $str, true);

                break;
            case 'credential_verified':
                $yesNoArr = applicationConstants::getYesNoArr($adminLangId);
                $str = isset($row[$key])?$yesNoArr[$row[$key]]:'';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class'=>'droplink'));
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                    $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addUserForm(".$row['user_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Rewards', $adminLangId),"onclick"=>"rewards(".$row['user_id'].")"), Labels::getLabel('LBL_Rewards', $adminLangId), true);

                    $innerLi=$innerUl->appendElement("li");
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green',
                    'title'=>Labels::getLabel('LBL_Transactions', $adminLangId),"onclick"=>"transactions(".$row['user_id'].")"), Labels::getLabel('LBL_Transactions', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Change_Password', $adminLangId),"onclick"=>"changePasswordForm(".$row['user_id'].")"), Labels::getLabel('LBL_Change_Password', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('Users', 'login', array($row['user_id'])),'target'=>'_blank','class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_Login_to_user_profile', $adminLangId)), Labels::getLabel('LBL_Login_to_user_profile', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Email_User', $adminLangId),"onclick"=>"sendMailForm(".$row['user_id'].")"), Labels::getLabel('LBL_Email_User', $adminLangId), true);

                    $innerLi=$innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Delete_User', $adminLangId),"onclick"=>"deleteUser(".$row['user_id'].")"), Labels::getLabel('LBL_Delete_User', $adminLangId), true);
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
$frm = new Form('frmUsersListing', array('id'=>'frmUsersListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadUserList ); return(false);');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl('Users', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page']=$page;
echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmUserSearchPaging'
));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
