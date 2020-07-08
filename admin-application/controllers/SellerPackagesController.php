<?php
class SellerPackagesController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewSellerPackages($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSellerPackages($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewSellerPackages($this->admin_id);
        $srch = SellerPackages::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array( "sp.*", "IFNULL( spl.".SellerPackages::DB_TBL_PREFIX."name, sp.".SellerPackages::DB_TBL_PREFIX."identifier ) as ".SellerPackages::DB_TBL_PREFIX."name"));
        $srch->addOrder(SellerPackages::DB_TBL_PREFIX.'active', 'DESC');
        $srch->addOrder(SellerPackages::DB_TBL_PREFIX.'id', 'DESC');
        $srch->addOrder(SellerPackages::DB_TBL_PREFIX."display_order");

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    public function form($spackageId = 0)
    {
        $this->objPrivilege->canEditSellerPackages();
        $spackageId = FatUtility::int($spackageId);
        /* if ($spackageId <1) {
        Message::addErrorMessage($this->str_invalid_request);
        FatUtility::dieWithError(Message::getHtml());
        } */
        $frm = $this->getForm($spackageId);
        if (0 < $spackageId) {
            $sPackageObj = new SellerPackages();
            $data = $sPackageObj->getAttributesById($spackageId);
            if ($data === false) {
                Message::addErrorMessage($this->str_invalid_request);
                FatUtility::dieWithError(Message::getHtml());
            }
            $frm->fill($data);
        }
        $this->set('languages', Language::getAllNames());
        $this->set('spackageId', $spackageId);
        $this->set('spackageFrm', $frm);
        $this->_template->render(false, false);
    }

    private function getForm($spackageId)
    {
        $arr_package_options = SellerPackages::getPackageTypes();
        $frm = new Form('frmSellerPackage', array('id'=>'frmSellerPackage'));
        $frm->addHiddenField('', 'spackage_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Package_Identifier', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'identifier');
        $disbaleText= array();
        if ($spackageId > 0) {
            $disbaleText=  array('disabled'=>'disabled');
        }
        $packageTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Package_Type', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'type', $arr_package_options, '', $disbaleText, '');
        if (0 == $spackageId) {
            $packageTypeFld->requirements()->setRequired();
        }
        $commissionRate = $frm->addFloatField(Labels::getLabel('LBL_Package_Commision_Rate_in_Percentage', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'commission_rate');
        $commissionRate->requirements()->setRange(0, 100);

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Package_Products_Allowed', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'products_allowed');
        $fld->requirements()->setIntPositive();

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Package_Inventory_Allowed', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'inventory_allowed');
        $fld->requirements()->setIntPositive();

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Package_Images_Per_Catalog', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'images_per_product');
        $fld->requirements()->setIntPositive();

        $frm->addSelectBox(Labels::getLabel('LBL_Package_Status', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'active', applicationConstants::getActiveInactiveArr($this->adminLangId), applicationConstants::ACTIVE, array(), '');

        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Package_Display_Order', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'display_order');
        $fld->requirements()->setIntPositive();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SAVE_CHANGES', $this->adminLangId));
        return $frm;
    }
    public function setup()
    {
        $this->objPrivilege->canEditSellerPackages();
        $post= FatApp::getPostedData();
        $spackageId = $post['spackage_id'];
        $frm = $this->getForm($spackageId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }


        unset($post['spackage_id']);
        $record = new SellerPackages($spackageId);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($spackageId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = SellerPackages::getAttributesByLangId($langId, $spackageId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $spackageId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('msg', $this->str_setup_successful);
        $this->set('spackageId', $spackageId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }
    public function langForm($spackageId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditSellerPackages();
        $spackageId = FatUtility::int($spackageId);
        $lang_id = FatUtility::int($lang_id);

        if ($spackageId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($spackageId, $lang_id);
        $langData = SellerPackages::getAttributesByLangId($lang_id, $spackageId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('spackageId', $spackageId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }
    public function langSetup()
    {
        $this->objPrivilege->canEditSellerPackages();
        $post = FatApp::getPostedData();

        $spackageId = $post[SellerPackages::DB_TBL_PREFIX.'id'];
        $lang_id = $post['lang_id'];

        if ($spackageId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($spackageId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post[SellerPackages::DB_TBL_PREFIX.'id']);
        unset($post['lang_id']);

        $data = array(
        'spackagelang_lang_id'=>$lang_id,
        'spackagelang_spackage_id'=>$spackageId,
        SellerPackages::DB_TBL_PREFIX.'name'=>$post[SellerPackages::DB_TBL_PREFIX.'name'],
        SellerPackages::DB_TBL_PREFIX.'text'=>$post[SellerPackages::DB_TBL_PREFIX.'text']
        );

        $obj = new SellerPackages($spackageId);

        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = SellerPackages::getAttributesByLangId($langId, $spackageId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successfull', $this->adminLangId));
        $this->set('spackageId', $spackageId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getLangForm($spackageId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditSellerPackages();
        $frm = new Form('frmSellerPackageLang');
        $frm->addHiddenField('', SellerPackages::DB_TBL_PREFIX.'id', $spackageId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Package_Name', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'name');
        $frm->addTextarea(Labels::getLabel('LBL_Package_Description', $this->adminLangId), SellerPackages::DB_TBL_PREFIX.'text');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SAVE_CHANGES', $this->adminLangId));
        return $frm;
    }
    public function searchPlans()
    {
        $spackageId  = FatApp::getPostedData('spackageId');
        $spackageId = FatUtility::convertToType($spackageId, FatUtility::VAR_INT);
        $sPackageObj = new SellerPackages();
        $data = $sPackageObj->getAttributesById($spackageId);

        if ($data === false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->objPrivilege->canViewSellerPackages($this->admin_id);
        $records = SellerPackagePlans::getPlanByPackageId($spackageId);

        $this->set('spackageId', $spackageId);
        $this->set("arr_listing", $records);
        $this->set("spackageData", $data);
        $this->_template->render(false, false);
    }

    public function planForm($spackageId =0, $spPlanId =0)
    {
        $this->objPrivilege->canEditSellerPackages();
        $spackageId = FatUtility::int($spackageId);
        $spPlanId = FatUtility::int($spPlanId);
        $sPackageObj = new SellerPackages();
        $spdata = $sPackageObj->getAttributesById($spackageId);

        if ($spackageId <1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->getPlanForm($spackageId);
        if (0 < $spPlanId) {
            $sPackageObj = new SellerPackagePlans();
            $data = $sPackageObj->getAttributesById($spPlanId);

            if ($data === false) {
                Message::addErrorMessage($this->str_invalid_request);
                FatUtility::dieWithError(Message::getHtml());
            }
        } else {
            $data[SellerPackagePlans::DB_TBL_PREFIX.'spackage_id']= $spackageId;
        }
        $frm->fill($data);
        $this->set('languages', Language::getAllNames());
        $this->set('spackageId', $spackageId);
        $this->set('spackageType', $spdata['spackage_type']);
        $this->set('spPlanId', $spPlanId);
        $this->set('spPlanFrm', $frm);

        $this->_template->render(false, false);
    }

    private function getPlanForm($spackageId)
    {
        $sPackageObj = new SellerPackages($this->adminLangId);
        $sPackageData = $sPackageObj->getAttributesById($spackageId);

        $frm = new Form('frmSellerPackagePlan', array('id'=>'frmSellerPackagePlan'));
        $frm->addHiddenField('', SellerPackagePlans::DB_TBL_PREFIX.'id');
        $frm->addHiddenField('', SellerPackagePlans::DB_TBL_PREFIX.'spackage_id');
        $arr_options_packages =  SellerPackages::getSellerPackages($this->adminLangId);
        $frm->addHTML(Labels::getLabel('LBL_Package', $this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'spackage_name', '<div class="field-set"><div class="caption-wraper"><label class="field_label">'.Labels::getLabel('LBL_Package', $this->adminLangId).'<span class="spn_must_field">*</span></label></div><div class="field-wraper"><div class="field_cover"><p class="text-ptop10">'.$sPackageData['spackage_identifier'].'</p></div></div></div>');

        /* $subsPeriodOption = SellerPackagePlans::getSubscriptionPeriods($this->adminLangId);
        $fldTFreq= $frm->addSelectBox(Labels::getLabel('LBL_Trial_Frequency',$this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'trial_frequency', $subsPeriodOption, '', array(),'');
        $fldTFreqText  = $frm->addHTML('',SellerPackagePlans::DB_TBL_PREFIX.'trial_frequency_text','');
        $fldTFreq->attachField($fldTFreqText);

        $frm->addIntegerField(Labels::getLabel('LBL_Trial_Interval',$this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'trial_interval');
        */
        $subsPeriodOption = SellerPackagePlans::getSubscriptionPeriods($this->adminLangId);
        $fldFreq = $frm->addSelectBox(Labels::getLabel('LBL_PERIOD', $this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'frequency', $subsPeriodOption, '', array(), '');
        $fldFreqText  = $frm->addHTML('', SellerPackagePlans::DB_TBL_PREFIX.'frequency_text', '');
        $fldFreq->attachField($fldFreqText);


        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Time_Interval_(FREQUENCY)', $this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'interval');
        $fld->requirements()->setIntPositive();

        if ($sPackageData[SellerPackages::DB_TBL_PREFIX.'type'] !=SellerPackages::FREE_TYPE) {
            $priceFld = $frm->addFloatField(Labels::getLabel('LBL_Price', $this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'price')->requirements()->setRange('0.01', '9999999999');
            $fldPckPrice=$frm->getField(SellerPackagePlans::DB_TBL_PREFIX.'price');
            $fldPckPrice->setWrapperAttribute('class', 'package_price');
        }

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Plan_Display_Order', $this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'display_order');
        $fld->requirements()->setIntPositive();
        $arr_options = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), SellerPackagePlans::DB_TBL_PREFIX.'active', $arr_options, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SAVE_CHANGES', $this->adminLangId));
        return $frm;
    }
    public function setupPlan()
    {
        $this->objPrivilege->canEditSellerPackages();
        $postData =  FatApp::getPostedData();

        $spackageId = $postData[SellerPackagePlans::DB_TBL_PREFIX.'spackage_id'];
        $frm = $this->getPlanForm($spackageId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $spPlanId = $post[SellerPackagePlans::DB_TBL_PREFIX.'id'];


        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }


        $packageRow  = SellerPackages::getAttributesById($spackageId);

        $data= $post;

        if ($packageRow[SellerPackages::DB_TBL_PREFIX.'type'] == SellerPackages::FREE_TYPE) {
            $data[SellerPackagePlans::DB_TBL_PREFIX.'trial_frequency'] = '';
            $data[SellerPackagePlans::DB_TBL_PREFIX.'trial_interval'] = 0;

            /* $data[SellerPackagePlans::DB_TBL_PREFIX.'frequency'] = SellerPackagePlans::SUBSCRIPTION_PERIOD_UNLIMITED; */
            $data[SellerPackagePlans::DB_TBL_PREFIX.'price'] = 0;
        }

        $record = new SellerPackagePlans($spPlanId);
        $record->assignValues($data);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', $this->str_setup_successful);
        $this->set('spackageId', $spackageId);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoComplete()
    {
        $pagesize=10;
        $post = FatApp::getPostedData();
        $srch = SellerPackagePlans::getSearchObject();

        $srch->joinTable(
            SellerPackages::DB_TBL,
            'LEFT OUTER JOIN',
            'sp.spackage_id = spp.spplan_spackage_id ',
            'sp'
        );
        $srch->joinTable(
            SellerPackages::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'spl.spackagelang_spackage_id = sp.spackage_id AND spl.spackagelang_lang_id = ' . $this->adminLangId,
            'spl'
        );

        $srch->addOrder('spackage_name');

        $srch->addMultipleFields(array('spplan_id', "IFNULL( spl.spackage_name, sp.spackage_identifier ) as spackage_name","spplan_interval","spplan_frequency"));
        $srch->addCondition('spackage_active', '=', applicationConstants::YES);
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('spackage_name', 'LIKE', '%' . $post['keyword']. '%');
            $cnd->attachCondition('spackage_identifier', 'LIKE', '%' . $post['keyword']. '%', 'OR');
        }
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $plans = $db->fetchAll($rs, 'spplan_id');
        $json = array();
        foreach ($plans as $key => $plan) {
            $json[] = array(
            'id' => $plan['spplan_id'],
            'name'      => DiscountCoupons::getPlanTitle($plan, $this->adminLangId),

            );
        }
        die(json_encode($json));
    }


    public function changeStatus()
    {
        $this->objPrivilege->canEditSellerPackages();
        $spackageId = FatApp::getPostedData('spackageId', FatUtility::VAR_INT, 0);
        if (0 >= $spackageId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = SellerPackages::getAttributesById($spackageId, array( 'spackage_id', 'spackage_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['spackage_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateSellerPkgStatus($spackageId, $status);

        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditSellerPackages();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $spackageIdsArr = FatUtility::int(FatApp::getPostedData('spackage_ids'));
        if (empty($spackageIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($spackageIdsArr as $spackageId) {
            if (1 > $spackageId) {
                continue;
            }

            $this->updateSellerPkgStatus($spackageId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSellerPkgStatus($spackageId, $status)
    {
        $status = FatUtility::int($status);
        $spackageId = FatUtility::int($spackageId);
        if (1 > $spackageId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new SellerPackages($spackageId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }
}
