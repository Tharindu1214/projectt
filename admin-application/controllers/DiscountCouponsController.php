<?php
class DiscountCouponsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewDiscountCoupons($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditDiscountCoupons($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewDiscountCoupons();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        /* $tagObj = new Tag(); */
        $srch = DiscountCoupons::getSearchObject($this->adminLangId, false);

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('dc.coupon_identifier', 'like', '%'.$post['keyword'].'%');
            $cnd->attachCondition('dc.coupon_code', 'like', '%'.$post['keyword'].'%');
            $cnd->attachCondition('dc_l.coupon_title', 'like', '%'.$post['keyword'].'%');
        }
        if (!empty($post['type'])) {
            $srch->addCondition('dc.coupon_type', '=', $post['type']);
        }
        $srch->addOrder('datediff(coupon_end_date,"'.date('Y-m-d').'")', 'DESC');
        /* $srch->addOrder('coupon_active','DESC'); */
        $srch->addOrder('coupon_id', 'DESC');

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        $records = FatApp::getDb()->fetchAll($rs);

        $discountTypeArr = DiscountCoupons::getTypeArr($this->adminLangId);

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('discountTypeArr', $discountTypeArr);
        $this->set('activeInactiveArr', $activeInactiveArr);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditDiscountCoupons();

        $frm = $this->getForm();

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $coupon_id = $post['coupon_id'];
        unset($post['coupon_id']);

        $record = new DiscountCoupons($coupon_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($coupon_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = DiscountCoupons::getAttributesByLangId($langId, $coupon_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $coupon_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', Labels::getLabel('MSG_Coupon_Setup_Successful.', $this->adminLangId));
        $this->set('couponId', $coupon_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();

        $coupon_id = $post['coupon_id'];
        $lang_id = $post['lang_id'];

        if ($coupon_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($coupon_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['coupon_id']);
        unset($post['lang_id']);
        $data = array(
        'couponlang_lang_id'=>$lang_id,
        'couponlang_coupon_id'=>$coupon_id,
        'coupon_title'=>$post['coupon_title'],
        'coupon_description'=>$post['coupon_description'],
        );

        $obj = new DiscountCoupons($coupon_id);
        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = DiscountCoupons::getAttributesByLangId($langId, $coupon_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        if ($newTabLangId == 0 && !$this->isMediaUploaded($coupon_id)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', Labels::getLabel('MSG_Coupon_Setup_Successful.', $this->adminLangId));
        $this->set('couponId', $coupon_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function isMediaUploaded($coupon_id)
    {
        if ($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0)) {
            return true;
        }
        return false;
    }

    public function form($coupon_id = 0)
    {
        $this->objPrivilege->canEditDiscountCoupons();

        $coupon_id = FatUtility::int($coupon_id);
        $frm = $this->getForm();

        if (0 < $coupon_id) {
            $data = DiscountCoupons::getAttributesById($coupon_id);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        } else {
            $frm->fill(array('coupon_id'=>$coupon_id));
        }

        $this->set('coupon_type', (isset($data['coupon_type'])?$data['coupon_type']:DiscountCoupons::TYPE_DISCOUNT));
        $this->set('couponDiscountIn', isset($data['coupon_discount_in_percent']) ? $data['coupon_discount_in_percent'] : applicationConstants::PERCENTAGE);
        $this->set('languages', Language::getAllNames());
        $this->set('coupon_id', $coupon_id);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function langForm($coupon_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditDiscountCoupons();

        $coupon_id = FatUtility::int($coupon_id);
        $lang_id = FatUtility::int($lang_id);

        if ($coupon_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($coupon_id, $lang_id);
        $langData = DiscountCoupons::getAttributesByLangId($lang_id, $coupon_id);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $bannerImage = AttachedFile::getAttachment(AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0, $lang_id);
        $this->set('bannerImage', $bannerImage);

        $this->set('languages', Language::getAllNames());
        $this->set('coupon_id', $coupon_id);
        $this->set('coupon_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function linkProductForm($coupon_id = 0)
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frmProduct = $this->getProductForm();

        $srch = DiscountCoupons::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('coupon_id', 'IFNULL(coupon_title,coupon_identifier) as coupon_name','coupon_code'));
        $srch->addCondition('coupon_id', '=', $coupon_id);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $row['coupon_name'] = "<h3> ".Labels::getLabel('LBL_Coupon_Name', $this->adminLangId)." : ". $row['coupon_name']." | ".Labels::getLabel('LBL_Coupon_Code', $this->adminLangId)." : ".$row['coupon_code']."</h3>";
        $frmProduct->fill($row);
        $this->set('coupon_id', $coupon_id);
        $this->set('couponData', $row);
        $this->set('frmProduct', $frmProduct);
        $this->_template->render(false, false);
    }

    public function linkCategoryForm($coupon_id = 0)
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frmCategory = $this->getCategoryForm();

        $srch = DiscountCoupons::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('coupon_id', 'IFNULL(coupon_title,coupon_identifier) as coupon_name','coupon_code'));
        $srch->addCondition('coupon_id', '=', $coupon_id);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $row['coupon_name'] = "<h3> ".Labels::getLabel('LBL_Coupon_Name', $this->adminLangId)." : ". $row['coupon_name']." | ".Labels::getLabel('LBL_Coupon_Code', $this->adminLangId)." : ".$row['coupon_code']."</h3>";
        $frmCategory->fill($row);
        $this->set('coupon_id', $coupon_id);
        $this->set('couponData', $row);
        $this->set('frmCategory', $frmCategory);
        $this->_template->render(false, false);
    }

    public function linkUserForm($coupon_id = 0)
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frmCategory = $this->getCategoryForm();
        $frmProduct = $this->getProductForm();
        $frmUser = $this->getDiscountUserForm();

        $srch = DiscountCoupons::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('coupon_id', 'IFNULL(coupon_title,coupon_identifier) as coupon_name','coupon_code'));
        $srch->addCondition('coupon_id', '=', $coupon_id);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $row['coupon_name'] = "<h3> ".Labels::getLabel('LBL_Coupon_Name', $this->adminLangId)." : ". $row['coupon_name']." | ".Labels::getLabel('LBL_Coupon_Code', $this->adminLangId)." : ".$row['coupon_code']."</h3>";
        $frmCategory->fill($row);
        $frmProduct->fill($row);
        $frmUser->fill($row);
        $this->set('coupon_id', $coupon_id);
        $this->set('couponData', $row);
        $this->set('frmCategory', $frmCategory);
        $this->set('frmProduct', $frmProduct);
        $this->set('frmUser', $frmUser);
        $this->_template->render(false, false);
    }
    public function linkPlanForm($coupon_id = 0)
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frmPlan = $this->getPlanForm();

        $srch = DiscountCoupons::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('coupon_id', 'IFNULL(coupon_title,coupon_identifier) as coupon_name','coupon_code'));
        $srch->addCondition('coupon_id', '=', $coupon_id);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $row['coupon_name'] = "<h3> ".Labels::getLabel('LBL_Coupon_Name', $this->adminLangId)." : ". $row['coupon_name']." | ".Labels::getLabel('LBL_Coupon_Code', $this->adminLangId)." : ".$row['coupon_code']."</h3>";

        $this->set('coupon_id', $coupon_id);
        $this->set('couponData', $row);
        $this->set('spPlanFrm', $frmPlan);

        $this->_template->render(false, false);
    }

    public function media($coupon_id = 0)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $couponData  = DiscountCoupons::getAttributesById($coupon_id);

        if (false == $couponData) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $couponMediaFrm = $this->getMediaForm($coupon_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();

        $this->set('coupon_id', $coupon_id);
        $this->set('couponMediaFrm', $couponMediaFrm);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function images($coupon_id = 0, $lang_id=0)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $couponData  = DiscountCoupons::getAttributesById($coupon_id);

        if (false == $couponData) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $couponImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0, $lang_id, false);
        $this->set('coupon_id', $coupon_id);
        $this->set('images', $couponImages);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function couponCategories($coupon_id = 0)
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $couponCategories = DiscountCoupons::getCouponCategories($coupon_id, $this->adminLangId);
        $this->set('couponCategories', $couponCategories);
        $this->set('coupon_id', $coupon_id);
        $this->_template->render(false, false);
    }

    public function couponProducts($coupon_id = 0)
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $couponProducts = DiscountCoupons::getCouponProducts($coupon_id, $this->adminLangId);
        $this->set('couponProducts', $couponProducts);
        $this->set('coupon_id', $coupon_id);
        $this->_template->render(false, false);
    }
    public function couponPlans($coupon_id = 0)
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $couponPlans = DiscountCoupons::getCouponPlans($coupon_id, $this->adminLangId);
        $this->set('couponPlans', $couponPlans);
        $this->set('coupon_id', $coupon_id);
        $this->_template->render(false, false);
    }

    public function couponUsers($coupon_id = 0)
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);

        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $couponUsers = DiscountCoupons::getCouponUsers($coupon_id, $this->adminLangId);
        $this->set('couponUsers', $couponUsers);
        $this->set('coupon_id', $coupon_id);
        $this->_template->render(false, false);
    }

    public function updateCouponCategory()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();

        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $prodcat_id = FatUtility::int($post['prodcat_id']);

        if (1 > $coupon_id || 1 > $prodcat_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->addUpdateCouponCategory($coupon_id, $prodcat_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateCouponProduct()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();

        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $product_id = FatUtility::int($post['product_id']);

        if (1 > $coupon_id || 1 > $product_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->addUpdateCouponProduct($coupon_id, $product_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    public function updateCouponPlan()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();

        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $spplan_id = FatUtility::int($post['spplan_id']);

        if (1 > $coupon_id || 1 > $spplan_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->addUpdateCouponPlan($coupon_id, $spplan_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    public function removeCouponPlan()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $spplan_id = FatUtility::int($post['spplan_id']);
        if (1 > $coupon_id || 1 > $spplan_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->removeCouponPlan($coupon_id, $spplan_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    public function removeCouponCategory()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $prodcat_id = FatUtility::int($post['prodcat_id']);
        if (1 > $coupon_id || 1 > $prodcat_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->removeCouponCategory($coupon_id, $prodcat_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCouponProduct()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $product_id = FatUtility::int($post['product_id']);
        if (1 > $coupon_id || 1 > $product_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->removeCouponProduct($coupon_id, $product_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateCouponUser()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();

        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $user_id = FatUtility::int($post['user_id']);

        if (1 > $coupon_id || 1 > $user_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->addUpdateCouponUser($coupon_id, $user_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCouponUser()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $user_id = FatUtility::int($post['user_id']);
        if (1 > $coupon_id || 1 > $user_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->removeCouponUser($coupon_id, $user_id)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCouponImage()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $coupon_id = FatUtility::int($post['coupon_id']);
        $lang_id = FatUtility::int($post['lang_id']);
        if (1 > $coupon_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Deleted_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadImage($coupon_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditDiscountCoupons();

        $coupon_id = FatUtility::int($coupon_id);
        $lang_id = FatUtility::int($lang_id);

        if ($coupon_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file.', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        $fileHandlerObj->deleteFile(AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0, 0, $lang_id);
        if (!$res = $fileHandlerObj->saveImage($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0, $_FILES['file']['name'], -1, true, $lang_id)
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('coupon_id', $coupon_id);
        $this->set('msg', $_FILES['file']['name'].' '.Labels::getLabel('MSG_Uploaded_Successfully.', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $coupon_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);

        if ($coupon_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = DiscountCoupons::getAttributesById($coupon_id);
        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new DiscountCoupons($coupon_id);
        $obj->assignValues(array(DiscountCoupons::tblFld('deleted') => 1));
        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function usesHistory($coupon_id)
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $coupon_id = FatUtility::int($coupon_id);
        if (1 > $coupon_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $couponData = DiscountCoupons::getAttributesById($coupon_id, array('coupon_code'));
        if ($couponData == false) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0)?1:$post['page'];
        $page = (empty($page) || $page <= 0)?1:FatUtility::int($page);

        $srch = CouponHistory::getSearchObject();
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'user_id = couponhistory_user_id');
        $srch->joinTable(Credential::DB_TBL, 'LEFT OUTER JOIN', 'credential_user_id = user_id');
        $srch->addCondition('couponhistory_coupon_id', '=', $coupon_id);
        $srch->addMultipleFields(array('couponhistory_id','couponhistory_coupon_id','couponhistory_order_id','couponhistory_user_id','couponhistory_amount','couponhistory_added_on','credential_username'));
        $srch->addOrder('couponhistory_added_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('couponId', $coupon_id);
        $this->set('couponData', $couponData);

        $this->_template->render(false, false);
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $couponId = FatApp::getPostedData('couponId', FatUtility::VAR_INT, 0);
        if (0 >= $couponId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = DiscountCoupons::getAttributesById($couponId, array('coupon_id', 'coupon_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['coupon_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $obj = new DiscountCoupons($couponId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    private function getSearchForm()
    {
        $frm = new Form('frmCouponSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Coupon_Type', $this->adminLangId), 'type', DiscountCoupons::getTypeArr($this->adminLangId, true), '', array(), '');

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('MSG_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch()'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getForm()
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $frm = new Form('frmCoupon');
        $frm->addHiddenField('', 'coupon_id');
        /* $frm->addHiddenField('', 'coupon_type',DiscountCoupons::TYPE_DISCOUNT); */
        $frm->addRequiredField(Labels::getLabel('LBL_Coupon_Identifier', $this->adminLangId), 'coupon_identifier');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Coupon_Code', $this->adminLangId), 'coupon_code');
        $fld->setUnique(DiscountCoupons::DB_TBL, 'coupon_code', 'coupon_id', 'coupon_id', 'coupon_id');
        $typeArr = DiscountCoupons::getTypeArr($this->adminLangId, true);

        $frm->addSelectBox(Labels::getLabel('LBL_Select_Discount_Type', $this->adminLangId), 'coupon_type', $typeArr, '', array(), '')->requirements()->setRequired();
        $validForArr = DiscountCoupons::getValidForArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Discount_Valid_For', $this->adminLangId), 'coupon_valid_for', $validForArr, '', array(), '');

        $percentageFlatArr = applicationConstants::getPercentageFlatArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Discount_in', $this->adminLangId), 'coupon_discount_in_percent', $percentageFlatArr, '', array(), '');

        $frm->addFloatField(Labels::getLabel('LBL_Discount_Value', $this->adminLangId), 'coupon_discount_value');
        $frm->addFloatField(Labels::getLabel('LBL_Min_Order_Value', $this->adminLangId), 'coupon_min_order_value')->requirements()->setFloatPositive();
        $frm->addFloatField(Labels::getLabel('LBL_Max_Discount_Value', $this->adminLangId), 'coupon_max_discount_value');

        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'coupon_start_date', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));

        $fld = $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'coupon_end_date', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
        $fld->requirements()->setCompareWith('coupon_start_date', 'ge', Labels::getLabel('LBL_Date_To', $this->adminLangId));

        $frm->addIntegerField(Labels::getLabel('LBL_Uses_Per_Coupon', $this->adminLangId), 'coupon_uses_count', 1);
        $frm->addIntegerField(Labels::getLabel('LBL_Uses_Per_Customer', $this->adminLangId), 'coupon_uses_coustomer', 1);

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Coupon_Status', $this->adminLangId), 'coupon_active', $activeInactiveArr, '', array(), '');

        $flatDiscountVal = new FormFieldRequirement('coupon_discount_value', Labels::getLabel('LBL_Discount_Value', $this->adminLangId));
        $flatDiscountVal->setRequired(true);
        $percentDiscountVal = new FormFieldRequirement('coupon_discount_value', Labels::getLabel('LBL_Discount_Value', $this->adminLangId));
        $percentDiscountVal->setRequired(true);
        $percentDiscountVal->setFloatPositive();

        $couponMinOrderValueReqTrue = new FormFieldRequirement('coupon_min_order_value', 'value');
        $couponMinOrderValueReqTrue->setRequired();
        $couponMinOrderValueReqTrue->setRange('0.00001', '9999999999');
        $couponMinOrderValueReqFalse = new FormFieldRequirement('coupon_min_order_value', 'value');
        $couponMinOrderValueReqFalse->setRequired(false);

        $couponMaxDiscountValueReqTrue = new FormFieldRequirement('coupon_max_discount_value', 'value');
        $couponMaxDiscountValueReqTrue->setRequired();
        $couponMaxDiscountValueReqFalse = new FormFieldRequirement('coupon_max_discount_value', 'value');
        $couponMaxDiscountValueReqFalse->setRequired(false);

        $couponMaxDiscountValueReqTrue->setFloatPositive();
        $couponMaxDiscountValueReqTrue->setRange('0.00001', '9999999999');

        $cType_fld = $frm->getField('coupon_type');
        $cType_fld->requirements()->addOnChangerequirementUpdate(DiscountCoupons::TYPE_DISCOUNT, 'eq', 'coupon_min_order_value', $couponMinOrderValueReqTrue);
        $cType_fld->requirements()->addOnChangerequirementUpdate(DiscountCoupons::TYPE_SELLER_PACKAGE, 'eq', 'coupon_min_order_value', $couponMinOrderValueReqFalse);

        $coupon_discount_in_percent_fld = $frm->getField('coupon_discount_in_percent');

        $coupon_discount_in_percent_fld->requirements()->addOnChangerequirementUpdate(applicationConstants::FLAT, 'eq', 'coupon_discount_value', $flatDiscountVal);
        $coupon_discount_in_percent_fld->requirements()->addOnChangerequirementUpdate(applicationConstants::PERCENTAGE, 'eq', 'coupon_discount_value', $percentDiscountVal);

        $coupon_discount_in_percent_fld->requirements()->addOnChangerequirementUpdate(applicationConstants::PERCENTAGE, 'eq', 'coupon_max_discount_value', $couponMaxDiscountValueReqTrue);
        $coupon_discount_in_percent_fld->requirements()->addOnChangerequirementUpdate(applicationConstants::FLAT, 'eq', 'coupon_max_discount_value', $couponMaxDiscountValueReqFalse);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($coupon_id = 0, $lang_id = 0)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $lang_id = FatUtility::int($lang_id);

        $frm = new Form('frmCouponLang');
        $frm->addHiddenField('', 'coupon_id', $coupon_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Coupon_title', $this->adminLangId), 'coupon_title');
        $frm->addTextArea(Labels::getLabel('LBL_Coupon_Description', $this->adminLangId), 'coupon_description');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getMediaForm($coupon_id = 0)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $frm = new Form('frmCouponMedia');
        $frm->addHiddenField('', 'coupon_id', $coupon_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld =  $frm->addButton('Image', 'coupon_image', Labels::getLabel('LBL_Upload_file', $this->adminLangId), array('class'=>'couponFile-Js','id'=>'coupon_image'));
        return $frm;
    }

    private function getCategoryForm()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $frm = new Form('frmCouponCategory');
        $frm->addHtml('', 'coupon_name', '');
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Category', $this->adminLangId), 'category_name');
        $fld2 = $frm->addHtml('', 'addNewCategoryLink', '<a target="_blank" href="'.CommonHelper::generateUrl('productCategories').'">'.Labels::getLabel('LBL_Category_Not_Found?_Click_here_to_add_new_category', $this->adminLangId).'</a>');
        $fld1->attachField($fld2);
        $frm->addHiddenField('', 'coupon_id');
        return $frm;
    }

    private function getProductForm()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $frm = new Form('frmCouponProduct');
        $frm->addHtml('', 'coupon_name', '');
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Product', $this->adminLangId), 'product_name');
        $fld2 = $frm->addHtml('', 'addNewProductLink', '<a target="_blank" href="'.CommonHelper::generateUrl('products').'">'.Labels::getLabel('LBL_Product_Not_Found?_Click_here_to_add_new_product', $this->adminLangId).'</a>');
        $fld1->attachField($fld2);
        $frm->addHiddenField('', 'coupon_id');
        return $frm;
    }
    private function getPlanForm()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $frm = new Form('frmCouponProduct');
        $frm->addHtml('', 'coupon_name', '');
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Plan', $this->adminLangId), 'plan_name');
        $fld2 = $frm->addHtml('', 'addNewPlanLink', '<br/><a target="_blank" href="'.CommonHelper::generateUrl('sellerPackages').'">'.Labels::getLabel('LBL_Plan_Not_Found?_Click_here_to_add_new_plan', $this->adminLangId).'</a>');
        $fld1->attachField($fld2);
        $frm->addHiddenField('', 'coupon_id');
        return $frm;
    }

    private function getDiscountUserForm()
    {
        $this->objPrivilege->canEditDiscountCoupons();
        $frm = new Form('frmCouponUser');
        $frm->addHtml('', 'coupon_name', '');
        $frm->addTextBox(Labels::getLabel('LBL_Add_User', $this->adminLangId), 'user_name');
        $frm->addHiddenField('', 'coupon_id');
        return $frm;
    }
}
