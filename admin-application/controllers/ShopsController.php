<?php
class ShopsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('form','langForm','search','setup','langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewShops($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShops($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $data = FatApp::getPostedData();
        $frmSearch = $this->getSearchForm();
        if ($data) {
            $data['shop_id'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->objPrivilege->canViewShops();

        $this->set("includeEditor", true);
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShops();

        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $post = $searchForm->getFormDataFromArray($data);

        $shopReportObj = ShopReport::getSearchObject($this->adminLangId);
        $shopReportObj->addGroupby('sreport_shop_id');
        $shopReportObj->addMultipleFields(array('sreport_shop_id','count(*) as numOfReports'));
        $shopReportObj->doNotCalculateRecords();
        $shopReportObj->doNotLimitRecords();
        $result_shop_reports = $shopReportObj->getQuery();

        $ratingSrch = new SelProdReviewSearch($this->adminLangId);
        $ratingSrch->joinUser();
        $ratingSrch->joinSeller();
        $ratingSrch->joinProducts();
        $ratingSrch->joinSelProdRatingByType(SelProdRating::TYPE_PRODUCT);
        $ratingSrch->addMultipleFields(array('spreview_seller_user_id','count(*) as numOfReviews'));
        $ratingSrch->doNotCalculateRecords();
        $ratingSrch->doNotLimitRecords();
        $ratingSrch->addGroupby('spreview_seller_user_id');
        $shopRatingQuery = $ratingSrch->getQuery();

        $prodSrch = new ProductSearch();
        $prodSrch->joinSellerProducts();
        $prodSrch->joinSellers();
        $prodSrch->addMultipleFields(array('selprod_user_id','count(*) as numOfProducts'));
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addGroupby('selprod_user_id');
        $productQuery = $prodSrch->getQuery();

        $srch = Shop::getSearchObject(false, $this->adminLangId);
        $srch->joinTable('tbl_users', 'INNER JOIN', 'u.user_id = s.shop_user_id', 'u');
        $srch->joinTable('tbl_user_credentials', 'INNER JOIN', 'u.user_id = c.credential_user_id', 'c');
        $srch->joinTable('(' . $result_shop_reports . ')', 'LEFT OUTER JOIN', 'sreport.sreport_shop_id = s.shop_id', 'sreport');
        $srch->joinTable('(' . $shopRatingQuery . ')', 'LEFT OUTER JOIN', 'srating.spreview_seller_user_id = s.shop_user_id', 'srating');
        $srch->joinTable('(' . $productQuery . ')', 'LEFT OUTER JOIN', 'sp.selprod_user_id = s.shop_user_id', 'sp');

        $srch->addMultipleFields(array('s.*','IFNULL(s_l.shop_name, s.shop_identifier) as shop_name','u.user_name','c.credential_username','ifnull(sreport.numOfReports ,0) as numOfReports','ifnull(srating.numOfReviews ,0) as numOfReviews','ifnull(sp.numOfProducts ,0) as numOfProducts'));

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cond = $srch->addCondition('s.shop_identifier', 'like', '%'.$keyword.'%', 'AND');
            $cond->attachCondition('s_l.shop_name', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('u.user_name', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('c.credential_username', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('c.credential_email', 'like', '%'.$keyword.'%', 'OR');
        }

        $shop_featured = FatApp::getPostedData('shop_featured', FatUtility::VAR_INT, -1);
        if ($shop_featured > -1) {
            $srch->addCondition('shop_featured', '=', $shop_featured);
        }

        $shop_active = FatApp::getPostedData('shop_active', FatUtility::VAR_INT, -1);
        if ($shop_active > -1) {
            $srch->addCondition('shop_active', '=', $shop_active);
        }

        $shop_supplier_display_status = FatApp::getPostedData('shop_supplier_display_status', FatUtility::VAR_INT, -1);
        if ($shop_supplier_display_status > -1) {
            $srch->addCondition('shop_supplier_display_status', '=', $shop_supplier_display_status);
        }

        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from)) {
            $srch->addCondition('shop_created_on', '>=', $date_from. ' 00:00:00');
        }

        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to)) {
            $srch->addCondition('shop_created_on', '<=', $date_to. ' 23:59:59');
        }

        $shop_id = FatApp::getPostedData('shop_id', FatUtility::VAR_INT, 0);
        if (!empty($shop_id)) {
            $srch->addCondition('shop_id', '=', $shop_id);
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        $srch->addOrder('shop_active', 'DESC');
        $srch->addOrder('shop_created_on', 'DESC');
        $rs = $srch->getResultSet();

        $records = FatApp::getDb()->fetchAll($rs);

        $this->set('canViewShopReports', $this->objPrivilege->canViewShopReports(0, true));
        $this->set('canViewSellerProducts', $this->objPrivilege->canViewSellerProducts(0, true));

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set('onOffArr', applicationConstants::getOnOffArr($this->adminLangId));

        $this->_template->render(false, false);
    }

    public function form($shop_id=0)
    {
        $this->objPrivilege->canEditShops();

        $shop_id=FatUtility::int($shop_id);
        $frm = $this->getForm($shop_id);

        $stateId = 0;
        if (0 < $shop_id) {
            $data = Shop::getAttributesById($shop_id);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', 'shops/view/'.$shop_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            /* ] */
            $frm->fill($data);
            $stateId = $data['shop_state_id'];
        }

        $this->set('languages', Language::getAllNames());
        $this->set('shop_id', $shop_id);
        $this->set('stateId', $stateId);
        $this->set('frmShop', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditShops();

        $frm = $this->getForm();

        $post = FatApp::getPostedData();
        $shop_state = FatUtility::int($post['shop_state']);
        $post = $frm->getFormDataFromArray($post);
        $post['shop_state_id'] = $shop_state;

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $post['shop_id'];
        unset($post['shop_id']);

        $shop = new Shop($shop_id);
        $shop->assignValues($post);

        if (!$shop->save()) {
            Message::addErrorMessage($shop->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* url data[ */
        $shopOriginalUrl = Shop::SHOP_TOP_PRODUCTS_ORGINAL_URL.$shop_id;

        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($shopOriginalUrl)));
        } else {
            $shop->rewriteUrlShop($post['urlrewrite_custom']);
            $shop->rewriteUrlReviews($post['urlrewrite_custom']);
            $shop->rewriteUrlTopProducts($post['urlrewrite_custom']);
            $shop->rewriteUrlContact($post['urlrewrite_custom']);
            $shop->rewriteUrlpolicy($post['urlrewrite_custom']);
        }
        /* ] */
        $newTabLangId = 0;
        if ($shop_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Shop::getAttributesByLangId($langId, $shop_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $shop_id = $shop->getMainTableRecordId();
            $newTabLangId = $this->adminLangId;
        }

        Product::updateMinPrices();
        $this->set('msg', Labels::getLabel("MSG_Setup_Successful", $this->adminLangId));
        $this->set('shopId', $shop_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($shop_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditShops();

        $shop_id = FatUtility::int($shop_id);
        $lang_id = FatUtility::int($lang_id);

        if ($shop_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $shopLangFrm = $this->getLangForm($shop_id, $lang_id);
        $langData = Shop::getAttributesByLangId($lang_id, $shop_id);

        if ($langData) {
            $shopLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('shop_id', $shop_id);
        $this->set('shop_lang_id', $lang_id);
        $this->set('shopLangFrm', $shopLangFrm);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditShops();
        $post=FatApp::getPostedData();

        $shop_id = $post['shop_id'];
        $lang_id = $post['lang_id'];

        if ($shop_id==0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($shop_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['shop_id']);
        unset($post['lang_id']);
        $data = array(
        'shoplang_lang_id'=>$lang_id,
        'shoplang_shop_id'=>$shop_id,
        'shop_name'=>$post['shop_name'],
        'shop_city'=>$post['shop_city'],
        'shop_contact_person'=>$post['shop_contact_person'],
        'shop_description'=>$post['shop_description'],
        'shop_payment_policy'=>$post['shop_payment_policy'],
        'shop_delivery_policy'=>$post['shop_delivery_policy'],
        'shop_refund_policy'=>$post['shop_refund_policy'],
        'shop_additional_info'=>$post['shop_additional_info'],
        'shop_seller_info'=>$post['shop_seller_info'],
        );

        $shopObj = new Shop($shop_id);
        if (!$shopObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Shop::getAttributesByLangId($langId, $shop_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('shopId', $shop_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadShopImages($shop_id, $lang_id)
    {
        $this->objPrivilege->canEditShops();

        $shop_id = FatUtility::int($shop_id);
        $lang_id = FatUtility::int($lang_id);

        if ($shop_id < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (!isset($post['file_type']) || FatUtility::int($post['file_type']) == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $file_type = $post['file_type'];
        $slide_screen = $post['slide_screen'];
        $allowedFileTypeArr = array( AttachedFile::FILETYPE_SHOP_LOGO, AttachedFile::FILETYPE_SHOP_BANNER, AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        $unique_record = true;
        /*if ($file_type != AttachedFile::FILETYPE_SHOP_BANNER) {
            $unique_record = true;
        }*/
        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $file_type,
            $shop_id,
            0,
            $_FILES['file']['name'],
            -1,
            $unique_record,
            $lang_id,
            $slide_screen
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('shopId', $shop_id);
        $fileName = $_FILES['file']['name'];
        $this->set('file', $fileName);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = strlen($fileName) > 10 ? substr($fileName, 0, 10).'.'.$ext : $fileName;
        $this->set('msg', $fileName.' '.Labels::getLabel('LBL_File_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function media($shop_id)
    {
        $this->objPrivilege->canViewShops();
        $shop_id = FatUtility::int($shop_id);

        $shopLogoFrm =  $this->getShopLogoForm($shop_id, $this->adminLangId);
        $shopBannerFrm =  $this->getShopBannerForm($shop_id, $this->adminLangId);
        $shopBackgroundImageFrm =  $this->getBackgroundImageForm($shop_id, $this->adminLangId);

        $this->set('languages', Language::getAllNames());
        $this->set('shop_id', $shop_id);
        $shopDetails  = Shop::getAttributesById($shop_id);
        $shopLayoutTemplateId =  $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }

        $this->set('shopDetails', $shopDetails);
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);
        $this->set('shopLogoFrm', $shopLogoFrm);
        $this->set('shopBannerFrm', $shopBannerFrm);
        $this->set('shopBackgroundImageFrm', $shopBackgroundImageFrm);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->_template->render(false, false);
    }

    public function images($shop_id, $imageType='', $lang_id = 0, $slide_screen = 0)
    {
        $this->objPrivilege->canViewShops();
        $shop_id = FatUtility::int($shop_id);

        if (!$shop_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        if ($imageType=='logo') {
            $logoAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_LOGO, $shop_id, 0, $lang_id, false);
            $this->set('images', $logoAttachments);
            $this->set('imageFunction', 'shopLogo');
        } elseif ($imageType=='banner') {
            $bannerAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BANNER, $shop_id, 0, $lang_id, false, $slide_screen);
            $this->set('images', $bannerAttachments);
            $this->set('imageFunction', 'shopBanner');
        } else {
            $backgroundAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE, $shop_id, 0, $lang_id, false);
            $this->set('images', $backgroundAttachments);
            $this->set('imageFunction', 'shopBackgroundImage');
        }
        $this->set('imageType', $imageType);
        $this->set('shop_id', $shop_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function removeShopImage($afileId, $shopId, $imageType='', $langId, $slide_screen = 0)
    {
        $afileId = FatUtility::int($afileId);
        $shopId = FatUtility::int($shopId);
        $langId = FatUtility::int($langId);

        if (!$afileId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if ($imageType=='logo') {
            $fileType = AttachedFile::FILETYPE_SHOP_LOGO;
        } elseif ($imageType=='banner') {
            $fileType = AttachedFile::FILETYPE_SHOP_BANNER;
        } else {
            $fileType = AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE;
        }
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($fileType, $shopId, $afileId, 0, $langId, $slide_screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('imageType', $imageType);
        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoComplete()
    {
        $this->objPrivilege->canViewShops();

        $srch = Shop::getSearchObject(false, $this->adminLangId);

        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $srch->addCondition('shop_name', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize(FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10));
        $srch->addMultipleFields(array('shop_id','IFNULL(shop_name,shop_identifier) as shop_name'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs, 'shop_id');
        $json = array();
        foreach ($products as $key => $product) {
            $json[] = array(
            'id' => $key,
            'name' => strip_tags(html_entity_decode($product['shop_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getSearchForm()
    {
        $frm = new Form('frmShopSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'shop_id');
        $frm->addSelectBox(Labels::getLabel('LBL_Featured', $this->adminLangId), 'shop_featured', array('-1'=>Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + applicationConstants::getYesNoArr($this->adminLangId), -1, array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'shop_active', array('-1'=>'Does not Matter')+applicationConstants::getActiveInactiveArr($this->adminLangId), -1, array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_Shop_Status_By_Seller', $this->adminLangId), 'shop_supplier_display_status', array('-1'=>Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId))+applicationConstants::getOnOffArr($this->adminLangId), -1, array(), '');
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getForm($shop_id = 0)
    {
        $shop_id = FatUtility::int($shop_id);

        $shopObj = new Tag();
        $frm = new Form('frmShop');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Shop_Identifier', $this->adminLangId), 'shop_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Shop_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $frm->addTextBox(Labels::getLabel('LBL_Postal_Code', $this->adminLangId), 'shop_postalcode');
        $phnFld = $frm->addTextBox(Labels::getLabel('LBL_Phone', $this->adminLangId), 'shop_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->adminLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'shop_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 223));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'shop_state', array())->requirement->setRequired(true);

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'shop_active', $activeInactiveArr, '', array(), '');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Free_Shipping_On', $this->adminLangId), 'shop_free_ship_upto');
        $fld->requirements()->setInt();
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Minimum_Wallet_Balance', $this->adminLangId), 'shop_cod_min_wallet_balance');
        $fld->requirements()->setFloat();
        $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Seller_needs_to_maintain_to_accept_COD_orders._Default_is_-1", $this->adminLangId)."</small>";
        $frm->addCheckBox(Labels::getLabel('LBL_Featured', $this->adminLangId), 'shop_featured', 1, array(), false, 0);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($shop_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmShopLang');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Shop_Name', $this->adminLangId), 'shop_name');
        $frm->addTextBox(Labels::getLabel('LBL_Shop_City', $this->adminLangId), 'shop_city');
        $frm->addTextBox(Labels::getLabel('LBL_Contact_person', $this->adminLangId), 'shop_contact_person');
        $frm->addTextarea(Labels::getLabel('LBL_Description', $this->adminLangId), 'shop_description');
        $frm->addTextarea(Labels::getLabel('LBL_Payment_Policy', $this->adminLangId), 'shop_payment_policy');
        $frm->addTextarea(Labels::getLabel('LBL_Delivery_Policy', $this->adminLangId), 'shop_delivery_policy');
        $frm->addTextarea(Labels::getLabel('LBL_Refund_Policy', $this->adminLangId), 'shop_refund_policy');
        $frm->addTextarea(Labels::getLabel('LBL_Additional_Information', $this->adminLangId), 'shop_additional_info');
        $frm->addTextarea(Labels::getLabel('LBL_Seller_Information', $this->adminLangId), 'shop_seller_info');

        /* $fld = $frm->addButton('Logo','shop_logo','Upload File',
        array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO));
        $fld->htmlAfterField='<span id="input-field'.AttachedFile::FILETYPE_SHOP_LOGO.'"></span>
        <div class="uploaded--image"><img src="'.CommonHelper::generateUrl('Image','shopLogo',array($shop_id,$lang_id,'THUMB'),CONF_WEBROOT_FRONT_URL).'"></div>';

        $fld1 = $frm->addButton('Banner','shop_banner','Upload File',
        array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER));
        $fld1->htmlAfterField='<span id="input-field'.AttachedFile::FILETYPE_SHOP_BANNER.'"></span>
        <span class="uploadimage--info">Preferred Dimension: Width = 1000px, Height = 250px </span>
        <div class="uploaded--image"><img src="'.CommonHelper::generateUrl('Image','shopBanner',array($shop_id,$lang_id,'THUMB'),CONF_WEBROOT_FRONT_URL).'"></div>'; */

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getShopLogoForm($shop_id, $land_id)
    {
        $land_id = FatUtility::int($land_id);
        $frm = new Form('frmShopLogo');
        $frm->addHTML('', Labels::getLabel('LBL_Logo', $this->adminLangId), '<h3>'.Labels::getLabel('LBL_Logo', $this->adminLangId).'</h3>');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld = $frm->addButton(
            Labels::getLabel('LBL_Logo', $land_id),
            'shop_logo',
            Labels::getLabel('LBL_Upload', $land_id),
            array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO,'data-frm'=>'frmShopLogo')
        );
        return $frm;
    }

    private function getShopBannerForm($shop_id, $land_id)
    {
        $land_id = FatUtility::int($land_id);
        $frm = new Form('frmShopBanner');
        $frm->addHTML('', Labels::getLabel('LBL_Banners', $this->adminLangId), '<h3>'.Labels::getLabel('LBL_Banners', $this->adminLangId).'</h3>');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->adminLangId), 'slide_screen', $screenArr, '', array(), '');
        $fld1 =  $frm->addButton(Labels::getLabel('Lbl_Banner', $land_id), 'shop_banner', Labels::getLabel('LBL_Upload', $land_id), array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER,'data-frm'=>'frmShopBanner'));
        return $frm;
    }

    private function getBackgroundImageForm($shop_id, $land_id)
    {
        $land_id = FatUtility::int($land_id);
        $frm = new Form('frmBackgroundImage');
        $frm->addHTML('', Labels::getLabel('Lbl_Background_Image', $this->adminLangId), '<h3>'.Labels::getLabel('Lbl_Background_Image', $this->adminLangId).'</h3>');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld1 =  $frm->addButton(Labels::getLabel('Lbl_Background_Image', $land_id), 'shop_background_image', Labels::getLabel('LBL_Upload', $land_id), array('class'=>'shopFile-Js','id'=>'shop_background_image','data-file_type'=>AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE,'data-frm'=>'frmBackgroundImage'));
        return $frm;
    }
    /* private function getMediaForm( $shop_id ){
    $frm = new Form('frmShopMedia');
    $frm->addHTML( '', 'shop_logo_heading', '' );
    $languagesAssocArr = Language::getAllNames();

    foreach( $languagesAssocArr as $lang_id => $lang_name ){
    if( $this->canEdit ){
                $frm->addButton('Logo'.' ('.$lang_name.')', 'shop_logo_'.$lang_id,'Upload Logo',
                    array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO,'lang_id' =>$lang_id, 'shop_id' =>$shop_id));
    } else {
                $frm->addHtml('','shop_logo_'.$lang_id, 'Logo ('. $lang_name .')');
    }
    $frm->addHtml('','shop_logo_display_div_'.$lang_id, '');
    }

    $frm->addHTML( '', 'shop_banner_heading', '' );
    foreach( $languagesAssocArr as $lang_id => $lang_name ){
    if( $this->canEdit ){
                $frm->addButton('Banner'.' ('. $lang_name .')','shop_banner_'.$lang_id,'Upload Banner',array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER,'lang_id' =>$lang_id,'shop_id'=>$shop_id));
    } else {
                $frm->addHtml('','shop_banner_'.$lang_id, 'Banner ('. $lang_name .')');
    }
    $frm->addHtml('','shop_banner_display_div_'.$lang_id, '');
    }
    return $frm;
    } */

    public function shopTemplate($shop_id)
    {
        $shopDetails = Shop::getAttributesById($shop_id, null, false);
        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }



        $shop_id =  $shopDetails['shop_id'];
        $shopLayoutTemplateId =  $shopDetails['shop_ltemplate_id'];

        $shopTemplateLayouts = LayoutTemplate::getMultipleLayouts(LayoutTemplate::LAYOUTTYPE_SHOP);

        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }
        $this->set('languages', Language::getAllNames());
        $this->set('shop_id', $shop_id);
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);
        $this->set('shopTemplateLayouts', $shopTemplateLayouts);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setTemplate($shop_id, $ltemplate_id)
    {
        $ltemplate_id = FatUtility::int($ltemplate_id);
        if (1 > $ltemplate_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data =  LayoutTemplate::getAttributesById($ltemplate_id);
        if (false == $data) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shopDetails = Shop::getAttributesById($shop_id, null, false);
        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }



        $shop_id =     $shopDetails['shop_id'];

        $shopObj = new Shop($shop_id);
        $data = array('shop_ltemplate_id'=>$ltemplate_id);
        $shopObj->assignValues($data);

        if (!$shopObj->save()) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /*  - --- Shop Collection ----- [*/
    public function commonShopCollection($shop_id)
    {
        $shopDetails = Shop::getAttributesById($shop_id, null, false);


        if (!false == $shopDetails) {
            $shop_id =  $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];
        }
        $this->set('shop_id', $shop_id);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('language', Language::getAllNames());
        return $shop_id;
    }

    public function shopCollections($shop_id)
    {
        $this->commonShopCollection($shop_id);
        $this->set('languages', Language::getAllNames());
        $this->set('shop_id', $shop_id);
        $this->_template->render(false, false);
    }

    public function searchShopCollections($shopId)
    {
        $records = ShopCollection::getCollectionGeneralDetail($shopId);
        $this->set("arr_listing", $records);
        $this->set("shopId", $shopId);
        $this->_template->render(false, false);
    }

    public function shopCollection($shop_id)
    {
        /* $shopDetails = Shop::getAttributesById($shop_id );
        if(false == $shopDetails){
        Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access',$this->adminLangId));
        FatUtility::dieWithError( Message::getHtml() );
        } */

        $this->commonShopCollection($shop_id);

        $this->_template->render(false, false);
    }

    public function shopCollectionGeneralForm($shop_id, $scollection_id)
    {
        $post = FatApp::getPostedData();
        $scollection_id = FatUtility::int($scollection_id);
        $shop_id=$this->commonShopCollection($shop_id);
        $colectionForm = $this->getCollectionGeneralForm($shop_id);
        $shopcolDetails = ShopCollection::getCollectionGeneralDetail($shop_id, $scollection_id);
        if (!empty($shopcolDetails)) {

            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');


            $urlSrch->addCondition('urlrewrite_original', '=', Shop::SHOP_COLLECTION_ORGINAL_URL.$shop_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $shopUrl = Shop::getShopUrl($shop_id, 'urlrewrite_custom');
                $shopcolDetails['urlrewrite_custom'] = str_replace('-'.$shopUrl, '', $urlRow['urlrewrite_custom']);
            }
            /* ] */
            $scollection_id = (array_key_exists('scollection_id', $shopcolDetails)) ? $shopcolDetails['scollection_id'] : 0;
            $colectionForm->fill($shopcolDetails);
            $this->set('scollection_id', $scollection_id);
        }
        $this->set('baseUrl', Shop::getShopUrl($shop_id, 'urlrewrite_custom'));
        $this->set('languages', Language::getAllNames());
        $this->set('colectionForm', $colectionForm);
        $this->_template->render(false, false);
    }

    public function deleteShopCollection($shop_id, $scollection_id)
    {
        $scollection_id = FatUtility::int($scollection_id);
        $shop_id = $this->commonShopCollection($shop_id);
        $this->markCollectionAsDeleted($shop_id, $scollection_id);
        FatUtility::dieJsonSuccess(
            Labels::getLabel('MSG_RECORD_DELETED', $this->adminLangId)
        );
    }

    public function deleteSelectedCollections()
    {
        $this->objPrivilege->canEditShops();
        $scollectionIdsArr = FatUtility::int(FatApp::getPostedData('scollection_ids'));
        $collection_shopId = FatUtility::int(FatApp::getPostedData('collection_shopId'));
        $shop_id = $this->commonShopCollection($collection_shopId);

        if (empty($scollectionIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($scollectionIdsArr as $scollection_id) {
            if (1 > $scollection_id) {
                continue;
            }
            $this->markCollectionAsDeleted($collection_shopId, $scollection_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markCollectionAsDeleted($shop_id, $scollection_id)
    {
        $shopcolDetails = ShopCollection::getCollectionGeneralDetail($shop_id, $scollection_id);
        if (empty($shopcolDetails)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID1', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $collection = new ShopCollection();
        if (!$collection->deleteCollection($scollection_id)) {
            Message::addErrorMessage($collection->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function shopCollectionMediaForm($shop_id, $scollection_id)
    {
        $shop_id = $this->commonShopCollection($shop_id);
        $collectionMediaFrm =  $this->getShopCollectionMediaForm($shop_id, $scollection_id);
        $this->set('frm', $collectionMediaFrm);
        $this->set('language', Language::getAllNames());
        $this->set('scollection_id', $scollection_id);
        $this->_template->render(false, false);
    }

    private function getShopCollectionMediaForm($shop_id, $scollection_id)
    {
        $frm = new Form('frmCollectionMedia');
        $frm->addHiddenField('', 'scollection_id', $scollection_id);
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array('class'=>'collection-language-js'), '');
        $fld1 =  $frm->addButton('', 'collection_image', Labels::getLabel('LBL_Upload_File', $this->adminLangId), array('class'=>'shopCollection-Js','id'=>'collection_image'));
        return $frm;
    }

    public function shopCollectionImages($shop_id, $scollection_id, $lang_id = 0)
    {
        $scollection_id = FatUtility::int($scollection_id);
        $lang_id = FatUtility::int($lang_id);
        $this->commonShopCollection($shop_id);
        if (1 > $scollection_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $collectionImg = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $scollection_id, 0, $lang_id, false);
        $this->set('images', $collectionImg);
        $this->set('languages', applicationConstants::bannerTypeArr());
        $this->set('scollection_id', $scollection_id);
        $this->set('lang_id', $lang_id);
        $this->_template->render(false, false);
    }

    public function uploadCollectionImage()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $scollection_id = FatApp::getPostedData('scollection_id', FatUtility::VAR_INT, 0);

        if ($scollection_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $scollection_id, 0, $_FILES['file']['name'], -1, true, $lang_id)
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('scollection_id', $scollection_id);
        $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCollectionImage($shop_id, $scollection_id, $lang_id = 0)
    {
        $shop_id = FatUtility::int($shop_id);
        $scollection_id = FatUtility::int($scollection_id);
        $lang_id = FatUtility::int($lang_id);

        $shop_id = $this->commonShopCollection($shop_id);
        if (1 > $scollection_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $scollection_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getCollectionGeneralForm($shop_id, $scollection_id = 0)
    {
        $shop_id = FatUtility::int($shop_id);
        $frm = new Form('frmShopCollection');
        $frm->addHiddenField('', 'scollection_id', $scollection_id);
        $frm->addHiddenField('', 'scollection_shop_id', $shop_id);

        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'scollection_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'scollection_active', $activeInactiveArr, applicationConstants::YES, array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function setupShopCollection()
    {
        $post = FatApp::getPostedData();
        //CommonHelper::printArray($post); die;
        $shop_id = FatUtility::int($post['scollection_shop_id']);
        $scollection_id = FatUtility::int($post['scollection_id']);
        if (!UserPrivilege::canEditSellerCollection($shop_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getCollectionGeneralForm($shop_id, $scollection_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $record = new ShopCollection($scollection_id);

        $record->assignValues($post);
        if (!$collection_id=$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* url data[ */
        $shopOriginalUrl = Shop::SHOP_COLLECTION_ORGINAL_URL.$shop_id;
        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($shopOriginalUrl)));
        } else {
            $shop = new Shop($shop_id);
            $shop->setupCollectionUrl($post['urlrewrite_custom']);
        }


        /* ] */
        $newTabLangId=0;
        if ($collection_id>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = ShopCollection::getAttributesByLangId($langId, $shop_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $collection_id = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('shop_id', $shop_id);
        $this->set('collection_id', $collection_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function shopCollectionLangForm($shop_id, $scollection_id, $langId)
    {
        $scollection_id = Fatutility::int($scollection_id);
        if (!$scollection_id) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $shopDetails = Shop::getAttributesById($shop_id, null, false);
        $shopColLangFrm = $this->getCollectionLangForm($scollection_id, $shop_id, $langId);
        if ($row = ShopCollection::getAttributesByLangId($langId, $scollection_id)) {
            $data['scollection_id']=$row['scollectionlang_scollection_id'];
            $data['lang_id']=$row['scollectionlang_lang_id'];
            $data['name']=$row['scollection_name'];

            $shopColLangFrm ->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('shopColLangFrm', $shopColLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('userId', $shopDetails['shop_user_id']);
        $this->set('scollection_id', $scollection_id);
        $this->set('langId', $langId);
        $this->commonShopCollection($shop_id);
        $this->_template->render(false, false);
    }

    private function getCollectionLangForm($scollection_id = 0, $shop_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmMetaTagLang');
        $frm->addHiddenField('', 'scollection_id', $scollection_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $frm->addRequiredField('Collection Name', 'name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function setupShopCollectionLang()
    {
        $post = FatApp::getPostedData();
        $scollection_id = FatUtility::int($post['scollection_id']);
        if (!UserPrivilege::canEditSellerCollection($scollection_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getCollectionLangForm($scollection_id, $post['shop_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $record = new ShopCollection($scollection_id);

        if (!$record->addUpdateShopCollectionLang($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        //	$this->commonShopCollection();
        $newTabLangId=0;
        if ($scollection_id>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                //	print_r(ShopCollection::getAttributesByLangId($langId,$scollection_id));
                if (!$row = ShopCollection::getAttributesByLangId($langId, $scollection_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $collection_id = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('shop_id', FatUtility::int($post['shop_id']));
        $this->set('scollection_id', $scollection_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function sellerCollectionProductLinkFrm($scollection_id, $shop_id)
    {
        $post = FatApp::getPostedData();
        $scollection_id = FatUtility::int($scollection_id);
        $shop_id=$this->commonShopCollection($shop_id);
        if (!UserPrivilege::canEditSellerCollection($scollection_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $sellProdObj  = new ShopCollection();
        //$sellerProductRow = SellerProduct::getAttributesById( $selprod_id );
        $products = $sellProdObj->getShopCollectionProducts($scollection_id, $this->adminLangId);

        $sellerCollectionproductLinkFrm =  $this->getCollectionLinksFrm();
        $data['scp_scollection_id'] = $scollection_id;
        $sellerCollectionproductLinkFrm->fill($data);
        $this->set('sellerCollectionproductLinkFrm', $sellerCollectionproductLinkFrm);
        $this->set('scollection_id', $scollection_id);
        $this->set('products', $products);
        $this->set('languages', Language::getAllNames());
        $this->set('activeTab', 'LINKS');
        $this->_template->render(false, false);
    }

    private function getCollectionLinksFrm()
    {
        $frm = new Form('frmLinks1', array('id'=>'frmLinks1'));

        $frm->addTextBox(Labels::getLabel('LBL_COLLECTION', $this->adminLangId), 'scp_selprod_id', '', array('id'=>'scp_selprod_id'));

        $frm->addHtml('', 'buy_together', '<div id="selprod-products"class="box--scroller"><ul class="links--vertical"></ul></div>');
        $frm->addHiddenField('', 'scp_scollection_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function setupSellerCollectionProductLinks()
    {
        $post = FatApp::getPostedData();
        $scollection_id = FatUtility::int($post['scp_scollection_id']);
        if (!UserPrivilege::canEditSellerCollection($scollection_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $product_ids = (isset($post['product_ids']))?$post['product_ids']:array();

        unset($post['scp_selprod_id']);

        if ($scollection_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shopColObj  = new ShopCollection();
        /* saving of product Upsell Product[ */
        if (!$shopColObj->addUpdateSellerCollectionProducts($scollection_id, $product_ids)) {
            Message::addErrorMessage($shopColObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoCompleteProducts()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $shopDetails = Shop::getAttributesById($post['shopId'], array('shop_user_id'));
        $srch = SellerProduct::getSearchObject($this->adminLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$this->adminLangId, 'p_l');
        $srch->addOrder('product_name');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd = $cnd->attachCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('product_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        $srch->addCondition('selprod_user_id', '=', $shopDetails['shop_user_id']);
        $srch->addMultipleFields(
            array(
            'selprod_id as id', 'IFNULL(selprod_title ,product_name) as product_name','product_identifier')
        );

        $srch->addOrder('selprod_active', 'DESC');
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        // echo  $srch->getQuery(); die;
        $products = array();
        if ($rs) {
            $products = $db->fetchAll($rs, 'id');
        }
        $json = array();
        foreach ($products as $key => $option) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($option['product_name'], ENT_QUOTES, 'UTF-8')),
            'product_identifier'    => strip_tags(html_entity_decode($option['product_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));

        return  $arrListing;
        ;
    }
    /*  - --- ] ----- */

    public function changeStatus()
    {
        $this->objPrivilege->canEditShops();
        $shopId = FatApp::getPostedData('shopId', FatUtility::VAR_INT, 0);
        if (1 > $shopId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $shopData = Shop::getAttributesById($shopId, array('shop_active'));

        if ($shopData == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $status = ($shopData['shop_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateShopStatus($shopId, $status);
        Product::updateMinPrices();
        //FatUtility::dieJsonSuccess($this->str_update_record);
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeCollectionStatus()
    {
        $this->objPrivilege->canEditShops();
        $scollection_id = FatApp::getPostedData('scollection_id', FatUtility::VAR_INT, 0);
        if (1 > $scollection_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $shopCollectionData = ShopCollection::getAttributesById($scollection_id, array('scollection_active'));

        if ($shopCollectionData == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $status = ($shopCollectionData['scollection_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateShopCollectionStatus($scollection_id, $status);
        //FatUtility::dieJsonSuccess($this->str_update_record);
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditShops();
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $shopIdsArr = FatUtility::int(FatApp::getPostedData('shop_ids'));

        if (empty($shopIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($shopIdsArr as $shopId) {
            if (1 > $shopId) {
                continue;
            }
            $this->updateShopStatus($shopId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateShopStatus($shopId, $status)
    {
        $shopId = FatUtility::int($shopId);
        $status = FatUtility::int($status);
        if (1 > $shopId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $shopObj = new Shop($shopId);
        $resp = $shopObj->changeStatus($status);
        if (!$resp) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function toggleBulkCollectionStatuses()
    {
        $this->objPrivilege->canEditShops();
        $status = FatApp::getPostedData('collection_status', FatUtility::VAR_INT, -1);
        $scollectionIdsArr = FatUtility::int(FatApp::getPostedData('scollection_ids'));

        if (empty($scollectionIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($scollectionIdsArr as $scollection_id) {
            if (1 > $scollection_id) {
                continue;
            }
            $this->updateShopCollectionStatus($scollection_id, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateShopCollectionStatus($scollection_id, $status)
    {
        $scollection_id = FatUtility::int($scollection_id);
        $status = FatUtility::int($status);
        if (1 > $scollection_id || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $shopCollectionObj = new ShopCollection($scollection_id);

        if (!$shopCollectionObj->changeStatus($status)) {
            Message::addErrorMessage($shopCollectionObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }
}
