<?php
class BrandsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die(Labels::getLabel('MSG_Invalid_Action', $this->adminLangId));
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewBrands($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditBrands($this->admin_id, true);
        $this->rewriteUrl = Brand::REWRITE_URL_PREFIX;

        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewBrands();
        $search = $this->getSearchForm();
        $data = FatApp::getPostedData();
        if ($data) {
            $data['brand_id'] = $data['id'];
            unset($data['id']);
            $search->fill($data);
        }
        $this->set("search", $search);
        $this->set('includeEditor', true);
        $this->_template->addJs('js/import-export.js');
        $this->_template->render();
    }

    private function getSearchForm($request = false)
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class'=>'search-input'));
        if ($request) {
            $frm->addTextBox(Labels::getLabel('LBL_Seller_Name_Or_Email', $this->adminLangId), 'user_name', '', array('id'=>'keyword','autocomplete'=>'off'));
            $frm->addHiddenField('', 'user_id');
        }
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'brand_id');
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewBrands();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $prodBrandObj = new Brand();
        $srch = $prodBrandObj->getSearchObject($this->adminLangId);
        $srch->addFld('b.*');

        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('b.brand_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('b_l.brand_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(array("b_l.brand_name"));
        $srch->addCondition('brand_status', '=', Brand::BRAND_REQUEST_APPROVED);
        if (!empty($post['brand_id'])) {
            $srch->addCondition('b.brand_id', '=', $post['brand_id']);
        }
        $srch->addOrder('brand_id', 'DESC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditBrands();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brand_id = $post['brand_id'];


        unset($post['brand_id']);
        $data = $post;
        $data['brand_status'] = Brand::BRAND_REQUEST_APPROVED;

        //FatApp::getDb()->startTransaction();

        $brand = new Brand($brand_id);
        $brand->assignValues($data);

        if (!$brand->save()) {
            Message::addErrorMessage($brand->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brand_id = $brand->getMainTableRecordId();

        /* url data[ */
        $brandOriginalUrl = $this->rewriteUrl.$brand_id;
        if ($post['urlrewrite_custom'] == '') {
            UrlRewrite::remove($brandOriginalUrl);
        } else {
            $brand->rewriteUrl($post['urlrewrite_custom']);
        }
        /* ] */

        $newTabLangId=0;
        if ($brand_id>0) {
            $brandId=$brand_id;
            $languages=Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row=Brand::getAttributesByLangId($langId, $brand_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $brandId = $brand->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        if ($newTabLangId == 0 && !$this->isMediaUploaded($brand_id)) {
            $this->set('openMediaForm', true);
        }

        Product::updateMinPrices(0, 0, $brandId);
        $this->set('msg', Labels::getLabel('MSG_Brand_Setup_Successful', $this->adminLangId));
        $this->set('brandId', $brandId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupRequest()
    {
        $this->objPrivilege->canEditBrandRequests();

        $frm = $this->getRequestForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brand_id = $post['brand_id'];
        if ($post['brand_status']==applicationConstants::YES) {
            $post['brand_active']  = applicationConstants::ACTIVE;
        }

        unset($post['brand_id']);

        $record = new Brand($brand_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brand_id = $record->getMainTableRecordId();

        /* url data[ */
        $shopOriginalUrl = $this->rewriteUrl.$brand_id;
        $shopCustomUrl = CommonHelper::seoUrl($post['urlrewrite_custom']);
        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($shopOriginalUrl)));
        } else {
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', $shopOriginalUrl);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            $recordObj = new TableRecord(UrlRewrite::DB_TBL);
            if ($urlRow) {
                $recordObj->assignValues(array('urlrewrite_custom'    =>    $shopCustomUrl ));
                if (!$recordObj->update(array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($shopOriginalUrl)))) {
                    Message::addErrorMessage(Labels::getLabel("Please_try_different_url,_URL_already_used_for_another_record.", $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                //$shopDetails['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            } else {
                $recordObj->assignValues(array('urlrewrite_original' => $shopOriginalUrl, 'urlrewrite_custom'    =>    $shopCustomUrl ));
                if (!$recordObj->addNew()) {
                    Message::addErrorMessage(Labels::getLabel("Please_try_different_url,_URL_already_used_for_another_record.", $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }
        $brandData  = Brand::getAttributesById($brand_id);
        $brandLangData = Brand::getAttributesByLangId($this->adminLangId, $brand_id);
        $brandData['brand_name'] = $brandLangData['brand_name'];
        /* ] */
        $email = new EmailHandler();
        if ($post['brand_status']!=Brand::BRAND_REQUEST_PENDING) {
            if (!$email->sendBrandRequestStatusChangeNotification($this->adminLangId, $brandData)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Email_Could_Not_Be_Sent', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId=0;
        if ($brand_id>0) {
            $brandId=$brand_id;
            $languages=Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row=Brand::getAttributesByLangId($langId, $brand_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $brandId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        if ($newTabLangId == 0 && !$this->isMediaUploaded($brand_id)) {
            $this->set('openMediaForm', true);
        }

        Product::updateMinPrices(0, 0, $brandId);
        $this->set('msg', Labels::getLabel('MSG_Brand_Setup_Successful', $this->adminLangId));
        $this->set('brandId', $brandId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditBrands();
        $post=FatApp::getPostedData();

        $brand_id = $post['brand_id'];
        $lang_id = $post['lang_id'];

        if ($brand_id==0 || $lang_id==0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($brand_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        /* Check if same brand name already exists [ */
        $tblRecord = new TableRecord(Brand::DB_LANG_TBL);
        if ($tblRecord->loadFromDb(array('smt' => 'brand_name = ?', 'vals' => array($post['brand_name'])))) {
            $brandRow = $tblRecord->getFlds();
            if ($brandRow['brandlang_brand_id'] != $brand_id) {
                Message::addErrorMessage(Labels::getLabel('LBL_Brand_name_already_exists', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */

        unset($post['brand_id']);
        unset($post['lang_id']);
        $data=array(
            'brandlang_lang_id'=>$lang_id,
            'brandlang_brand_id'=>$brand_id,
            'brand_name'=>$post['brand_name'],
            'brand_short_description'=>$post['brand_short_description'],
        );
        $prodBrandObj=new Brand($brand_id);
        if (!$prodBrandObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($prodBrandObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row=Brand::getAttributesByLangId($langId, $brand_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        if ($newTabLangId == 0 && !$this->isMediaUploaded($brand_id)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', Labels::getLabel('MSG_Brand_Setup_Successful', $this->adminLangId));
        $this->set('brandId', $brand_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function form($brand_id=0)
    {
        $this->objPrivilege->canEditBrands();

        $brand_id=FatUtility::int($brand_id);
        $prodBrandFrm = $this->getForm($brand_id);

        if (0 < $brand_id) {
            $data = Brand::getAttributesById($brand_id, array('brand_id','brand_identifier','brand_active','brand_featured'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', $this->rewriteUrl.$brand_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            /* ] */
            $prodBrandFrm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('brand_id', $brand_id);
        $this->set('prodBrandFrm', $prodBrandFrm);
        $this->_template->render(false, false);
    }

    public function requestForm($brand_id=0)
    {
        $this->objPrivilege->canEditBrandRequests();

        $brand_id=FatUtility::int($brand_id);
        $prodBrandFrm = $this->getRequestForm($brand_id);

        if (0 < $brand_id) {
            $data = Brand::getAttributesById($brand_id, array('brand_id','brand_identifier','brand_active','brand_featured','brand_status','brand_seller_id'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', $this->rewriteUrl.$brand_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            $data['urlrewrite_custom'] ='';
            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }

            if ($data['urlrewrite_custom'] =='') {
                $data['urlrewrite_custom']= CommonHelper::seoUrl($data['brand_identifier']);
            }
            /* ] */
            $prodBrandFrm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('brand_id', $brand_id);
        $this->set('prodBrandFrm', $prodBrandFrm);
        $this->_template->render(false, false);
    }

    public function media($brand_id = 0)
    {
        $this->objPrivilege->canEditBrands();
        $brand_id = FatUtility::int($brand_id);
        $brandLogoFrm = $this->getBrandLogoForm($brand_id);
        $brandImageFrm = $this->getBrandImageForm($brand_id);
        $this->set('languages', Language::getAllNames());
        $this->set('brand_id', $brand_id);
        $this->set('brandLogoFrm', $brandLogoFrm);
        $this->set('brandImageFrm', $brandImageFrm);
        $this->_template->render(false, false);
    }

    public function images($brand_id, $file_type, $lang_id=0, $slide_screen = 0)
    {
        $brand_id = FatUtility::int($brand_id);
        if ($file_type=='logo') {
            $brandLogos = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BRAND_LOGO, $brand_id, 0, $lang_id, false);
            $this->set('images', $brandLogos);
            $this->set('imageFunction', 'brandReal');
        } else {
            $brandImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BRAND_IMAGE, $brand_id, 0, $lang_id, false, $slide_screen);
            $this->set('images', $brandImages);
            $this->set('imageFunction', 'brandImage');
        }

        $this->set('file_type', $file_type);
        $this->set('brand_id', $brand_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function requestMedia($brand_id = 0)
    {
        $this->objPrivilege->canEditBrands();
        $brand_id = FatUtility::int($brand_id);
        $brandLogoFrm = $this->getBrandLogoForm($brand_id);
        $brandImageFrm = $this->getBrandImageForm($brand_id);
        $this->set('languages', Language::getAllNames());
        $this->set('brand_id', $brand_id);
        $this->set('brandLogoFrm', $brandLogoFrm);
        $this->set('brandImageFrm', $brandImageFrm);
        $this->_template->render(false, false);
    }

    public function uploadMedia()
    {
        $this->objPrivilege->canEditBrands();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $brand_id = FatApp::getPostedData('brand_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $slide_screen = FatApp::getPostedData('slide_screen', FatUtility::VAR_INT, 0);
        if (!$brand_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        $fileHandlerObj->deleteFile($file_type, $brand_id, 0, 0, $lang_id);

        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $file_type,
            $brand_id,
            0,
            $_FILES['file']['name'],
            -1,
            $unique_record = false,
            $lang_id,
            $slide_screen
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('brandId', $brand_id);
        $this->set('file', $_FILES['file']['name']);
        $this->set('msg', $_FILES['file']['name']. Labels::getLabel('MSG_File_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function isMediaUploaded($brandId)
    {
        if ($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_LOGO, $brandId, 0)) {
            return true;
        }
        return false;
    }

    public function getBrandLogoForm($brand_id)
    {
        $frm = new Form('frmBrandLogo');
        $frm->addHTML('', Labels::getLabel('LBL_logo', $this->adminLangId), '<h3>'.Labels::getLabel('LBL_Logo', $this->adminLangId).'</h3>');
        $languagesAssocArr = Language::getAllNames();
        $frm->addHTML('', 'brand_logo_heading', '');
        $frm->addHiddenField('', 'brand_id', $brand_id);
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', array( 0 => Labels::getLabel('LBL_Universal', $this->adminLangId) ) + $languagesAssocArr, '', array(), '');
        $frm->addButton(
            Labels::getLabel('Lbl_Logo', $this->adminLangId),
            'logo',
            Labels::getLabel('LBL_Upload_Logo', $this->adminLangId),
            array('class'=>'uploadFile-Js', 'id'=>'logo','data-file_type'=>AttachedFile::FILETYPE_BRAND_LOGO,'data-brand_id' => $brand_id, 'data-image_type'=>'logo', 'data-frm'=>'frmBrandLogo' )
        );
        $frm->addHtml('', 'brand_logo_display_div', '');

        return $frm;
    }

    public function getBrandImageForm($brand_id)
    {
        $frm = new Form('frmBrandImage');
        $frm->addHTML('', Labels::getLabel('LBL_Image', $this->adminLangId), '<h3>'.Labels::getLabel('LBL_Image', $this->adminLangId).'</h3>');
        $languagesAssocArr = Language::getAllNames();
        $frm->addHTML('', 'brand_logo_heading', '');
        $frm->addHiddenField('', 'brand_id', $brand_id);
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', array( 0 => Labels::getLabel('LBL_Universal', $this->adminLangId) ) + $languagesAssocArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->adminLangId), 'slide_screen', $screenArr, '', array(), '');
        $frm->addButton(
            Labels::getLabel('Lbl_Image', $this->adminLangId),
            'image',
            Labels::getLabel('LBL_Upload_Image', $this->adminLangId),
            array('class'=>'uploadFile-Js','id'=>'image','data-file_type'=>AttachedFile::FILETYPE_BRAND_IMAGE,'data-brand_id' => $brand_id, 'data-image_type'=>'image', 'data-frm'=>'frmBrandImage' )
        );
        $frm->addHtml('', 'brand_image_display_div', '');

        return $frm;
    }

    private function getForm($brand_id=0)
    {
        $this->objPrivilege->canEditBrands();
        $brand_id = FatUtility::int($brand_id);

        $action=Labels::getLabel('LBL_Add_New', $this->adminLangId);
        if ($brand_id>0) {
            $action=Labels::getLabel('LBL_Update', $this->adminLangId);
        }

        $frm = new Form('frmProdBrand', array('id'=>'frmProdBrand'));
        $frm->addHiddenField('', 'brand_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Brand_Identifier', $this->adminLangId), 'brand_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Brand_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Brand_Status', $this->adminLangId), 'brand_active', $activeInactiveArr, '', array(), '');

        /* $frm->addCheckBox(Labels::getLabel('LBL_Featured',$this->adminLangId), 'brand_featured', 1,array(),false,0); */
        $fld = $frm->addHiddenField('', 'brand_logo', '', array('id'=>'brand_logo'));
        $frm->addSubmitButton('', 'btn_submit', $action);
        return $frm;
    }

    private function getRequestForm($brand_id=0)
    {
        $this->objPrivilege->canEditBrands();
        $brand_id = FatUtility::int($brand_id);

        $action=Labels::getLabel('LBL_Add_New', $this->adminLangId);
        if ($brand_id>0) {
            $action=Labels::getLabel('LBL_Update', $this->adminLangId);
        }

        $frm = new Form('frmProdBrand', array('id'=>'frmProdBrand'));
        $frm->addHiddenField('', 'brand_id', 0);
        $frm->addHiddenField('', 'brand_seller_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Brand_Identifier', $this->adminLangId), 'brand_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Brand_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $reqStatusArr = Brand::getBrandReqStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_brand_status', $this->adminLangId), 'brand_status', $reqStatusArr);
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addTextArea('', 'brand_comments', '');
        /* $frm->addCheckBox(Labels::getLabel('LBL_Featured',$this->adminLangId), 'brand_featured', 1,array(),false,0); */
        $fld = $frm->addHiddenField('', 'brand_logo', '', array('id'=>'brand_logo'));
        $frm->addSubmitButton('', 'btn_submit', $action);
        return $frm;
    }

    public function langForm($brand_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditBrands();

        $brand_id = FatUtility::int($brand_id);
        $lang_id = FatUtility::int($lang_id);

        if ($brand_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $prodBrandLangFrm = $this->getLangForm($brand_id, $lang_id);
        $langData = Brand::getAttributesByLangId($lang_id, $brand_id);
        /* CommonHelper::printArray($langData); die; */
        if ($langData) {
            $prodBrandLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('brand_id', $brand_id);
        $this->set('brand_lang_id', $lang_id);
        $this->set('prodBrandLangFrm', $prodBrandLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function requestLangForm($brand_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditBrands();

        $brand_id = FatUtility::int($brand_id);
        $lang_id = FatUtility::int($lang_id);

        if ($brand_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $prodBrandLangFrm = $this->getLangForm($brand_id, $lang_id);
        $langData = Brand::getAttributesByLangId($lang_id, $brand_id);
        /* CommonHelper::printArray($langData); die; */
        if ($langData) {
            $prodBrandLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('brand_id', $brand_id);
        $this->set('brand_lang_id', $lang_id);
        $this->set('prodBrandLangFrm', $prodBrandLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function removeBrandMedia($brand_id, $imageType='', $lang_id = 0, $slide_screen = 0)
    {
        $brand_id = FatUtility::int($brand_id);
        $lang_id = FatUtility::int($lang_id);
        if (!$brand_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($imageType=='logo') {
            $fileType = AttachedFile::FILETYPE_BRAND_LOGO;
        } elseif ($imageType=='image') {
            $fileType = AttachedFile::FILETYPE_BRAND_IMAGE;
        }
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($fileType, $brand_id, 0, 0, $lang_id, $slide_screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getLangForm($brand_id=0, $lang_id=0)
    {
        $frm = new Form('frmProdBrandLang', array('id'=>'frmProdBrandLang'));
        $frm->addHiddenField('', 'brand_id', $brand_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Brand_Name', $this->adminLangId), 'brand_name');
        $fld = $frm->addTextarea(Labels::getLabel('LBL_Short_Description', $this->adminLangId), 'brand_short_description');
        /* $fld->requirements()->setLength(0,250); */
        $fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_First_100_characters_will_be_shown_at_Product_detail_page', $this->adminLangId).'</small>';

        /* $fld = $frm->addButton('Logo','logo','Upload File',array('class'=>'uploadFile-Js','id'=>'','data-brand_id'=>$brand_id)
        );
        $htmlAfterField = '<span id="input-field"></span>
        <span class = "uploadimage--info" >It will be displayed in 192 × 82 pixels</span>
        <div class="uploaded--image"><img src="'.CommonHelper::generateUrl('image','brand',array($brand_id, $lang_id),CONF_WEBROOT_FRONT_URL).'">';
        if( AttachedFile::getAttachment( AttachedFile::FILETYPE_BRAND_LOGO, $brand_id, 0, $lang_id ) ){
            $htmlAfterField .= '<a href="javascript:void(0);" onclick="removeBrandLogo('.$brand_id.', '.$lang_id.')" class="remove--img"><i class="ion-close-round"></i></a>';
        }
        $htmlAfterField .= '</div>';

        $fld->htmlAfterField = $htmlAfterField;  */

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditBrands();

        $brand_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($brand_id < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $this->markAsDeleted($brand_id);
        Product::updateMinPrices(0, 0, $brand_id);
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditBrands();
        $brandIdsArr = FatUtility::int(FatApp::getPostedData('brandIds'));

        if (empty($brandIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($brandIdsArr as $brand_id) {
            if (1 > $brand_id) {
                continue;
            }
            $this->markAsDeleted($brand_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($brand_id)
    {
        $brand_id = FatUtility::int($brand_id);
        if (1 > $brand_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $prodBrandObj = new Brand($brand_id);
        if (!$prodBrandObj->canRecordMarkDelete($brand_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $prodBrandObj->assignValues(array(Brand::tblFld('deleted') => 1));
        if (!$prodBrandObj->save()) {
            Message::addErrorMessage($prodBrandObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function autoComplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $this->objPrivilege->canViewBrands();
        $brandObj = new Brand();
        $srch = $brandObj->getSearchObject();
        $srch->joinTable(
            Brand::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'brandlang_brand_id = brand_id AND brandlang_lang_id = ' . $this->adminLangId
        );
        $srch->addMultipleFields(array('brand_id, IFNULL(brand_name, brand_identifier) as brand_name'));

        if (!empty($post['keyword'])) {
            $srch->addCondition('brand_name', 'LIKE', '%' . $post['keyword'] . '%')
            ->attachCondition('brand_identifier', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->addCondition('brand_active', '=', applicationConstants::YES);
        $srch->addCondition('brand_deleted', '=', applicationConstants::NO);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $brands = $db->fetchAll($rs, 'brand_id');
        $json = array();
        foreach ($brands as $key => $brand) {
            $json[] = array(
                'id' => $key,
                'name'      => strip_tags(html_entity_decode($brand['brand_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
        /* $this->set('brands', $db->fetchAll($rs,'brand_id') );
        $this->_template->render(false,false); */
    }

    public function brandRequests()
    {
        $this->objPrivilege->canViewBrandRequests();
        $search = $this->getSearchForm(true);
        $data = FatApp::getPostedData();
        if ($data) {
            $data['brand_id'] = $data['id'];
            unset($data['id']);
            $search->fill($data);
        }
        $this->set("search", $search);
        $this->_template->render();
    }

    public function searchBrandRequests()
    {
        $this->objPrivilege->canViewBrands();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm(true);
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $prodBrandObj = new Brand();
        $srch = $prodBrandObj->getSearchObject();
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = brand_seller_id', 'u');
        $srch->addMultipleFields(array('b.*','user_name'));
        $srch->addCondition('brand_status', '=', applicationConstants::NO);
        $srch->addCondition('brand_seller_id', '>', 0);
        $srch->addOrder('b.brand_id', 'desc');
        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('b.brand_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('bl.brand_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        if (!empty($post['brand_id'])) {
            $srch->addCondition('b.brand_id', '=', $post['brand_id']);
        }
        $user_id = FatApp::getPostedData('user_id', FatUtility::VAR_INT, 0);
        if ($user_id > 0) {
            $srch->addCondition('brand_seller_id', '=', $user_id);
        }
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->joinTable(
            Brand::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'brandlang_brand_id = b.brand_id AND brandlang_lang_id = ' . $this->adminLangId,
            'bl'
        );
        $srch->addMultipleFields(array("bl.brand_name"));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditBrands();
        $brandId = FatApp::getPostedData('brandId', FatUtility::VAR_INT, 0);
        if (0 == $brandId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $brandData = Brand::getAttributesById($brandId, array('brand_active'));

        if (!$brandData) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($brandData['brand_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateBrandStatus($brandId, $status);
        Product::updateMinPrices(0, 0, $brandId);
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditBrands();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $brandIdsArr = FatUtility::int(FatApp::getPostedData('brandIds'));
        if (empty($brandIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($brandIdsArr as $brandId) {
            if (1 > $brandId) {
                continue;
            }

            $this->updateBrandStatus($brandId, $status);
        }
        Product::updateMinPrices();
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateBrandStatus($brandId, $status)
    {
        $status = FatUtility::int($status);
        $brandId = FatUtility::int($brandId);
        if (1 > $brandId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $brandObj = new Brand($brandId);
        if (!$brandObj->changeStatus($status)) {
            Message::addErrorMessage($brandObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function exportBrands()
    {
        $this->objPrivilege->canViewBrands();
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        if (1 > $langId) {
            $langId =  CommonHelper::getLangId();
        }

        $db = FatApp::getDb();

        /*Fetch all seo keyword [*/
        $keywordSrch = UrlRewrite::getSearchObject();
        $keywordSrch->doNotCalculateRecords();
        $keywordSrch->doNotLimitRecords();
        $keywordSrch->addMultipleFields(array('urlrewrite_original','urlrewrite_custom'));
        $keywordSrch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'original', 'like', $this->rewriteUrl.'%');
        $keywordRs = $keywordSrch->getResultSet();
        $urlKeywords = $db->fetchAllAssoc($keywordRs, 'brand_identifier');
        /*]*/

        $srch = Brand::getSearchObject($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('brand_status', '=', applicationConstants::ACTIVE);
        $rs = $srch->getResultSet();

        $sheetData = array();
        $obj = new Importexport();

        /* Sheet Heading Row [ */
        $arr = $obj->getBrandColoumArr($langId);
        array_push($sheetData, $arr);
        /* ] */

        while ($row = $db->fetch($rs)) {
            $sheetArr = array();

            if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                $sheetArr[] = $row['brand_id'];
            }
            $sheetArr[] = $row['brand_identifier'];
            $sheetArr[] = $row['brand_name'];
            $sheetArr[] = $row['brand_short_description'];

            if ($obj->isDefaultSheetData($langId)) {
                if (FatApp::getConfig('CONF_USE_O_OR_1', FatUtility::VAR_INT, 0)) {
                    $featured = $row['brand_featured'];
                    $active = $row['brand_active'];
                } else {
                    $featured = ($row['brand_featured'])?'YES':'NO';
                    $active = ($row['brand_active'])?'YES':'NO';
                }

                $keyword = isset($urlKeywords[$this->rewriteUrl.$row['brand_id']])?$urlKeywords[$this->rewriteUrl.$row['brand_id']]:'';

                $sheetArr[] = $keyword;
                $sheetArr[] = $featured;
                $sheetArr[] = $active;
            }

            array_push($sheetData, $sheetArr);
        }

        $langData = Language::getAttributesById($langId, array('language_code'));

        CommonHelper::convertToCsv($sheetData, 'Brands_'.$langData['language_code'].'_'.date("d-M-Y").'.csv', ',');
        exit;
    }

    public function exportMedia()
    {
        $this->objPrivilege->canViewBrands();

        $srch = Brand::getSearchObject();
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'brand_id = afile_record_id and afile_type = '.AttachedFile::FILETYPE_BRAND_LOGO);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('brand_id','brand_identifier','afile_record_id','afile_record_subid','afile_lang_id','afile_screen','afile_physical_path','afile_name','afile_display_order'));
        $srch->addCondition('brand_status', '=', applicationConstants::ACTIVE);
        $rs = $srch->getResultSet();

        $db = FatApp::getDb();
        $langId = $this->adminLangId;
        $sheetData = array();
        $obj = new Importexport();

        /* Sheet Heading Row [ */
        $arr = $obj->getBrandMediaColoumArr($langId);
        array_push($sheetData, $arr);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);

        while ($row = $db->fetch($rs)) {
            $sheetArr = array();

            if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                $sheetArr[] = $row['brand_id'];
            } else {
                $sheetArr[] = $row['brand_identifier'];
            }

            if (FatApp::getConfig('CONF_USE_LANG_ID', FatUtility::VAR_INT, 0)) {
                $sheetArr[] = $row['afile_lang_id'];
            } else {
                $sheetArr[] = $languageCodes[$row['afile_lang_id']];
            }

            $sheetArr[] = $row['afile_physical_path'];
            $sheetArr[] = $row['afile_name'];
            $sheetArr[] = $row['afile_display_order'];
            array_push($sheetData, $sheetArr);
        }

        CommonHelper::convertToCsv($sheetData, 'Brands_Media_'.$languageCodes[$langId].'_'.date("d-M-Y").'.csv', ',');
        exit;
    }

    public function importBrands()
    {
        $this->objPrivilege->canEditBrands();
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        $obj = new Importexport();

        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();

        $csvFilePointer = fopen($_FILES['import_file']['tmp_name'], 'r');
        $rowCount = 0;
        $langId = $post['lang_id'];

        while (($line = fgetcsv($csvFilePointer)) !== false) {
            if (empty($line[0])) {
                continue;
            }

            $numcols = count($line);

            $colCount = 0;
            if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                $brandId = FatUtility::int($line[$colCount++]);
            }

            $identifier = $line[$colCount++];
            $name = $line[$colCount++];
            $description = $line[$colCount++];

            if ($obj->isDefaultSheetData($langId)) {
                $seoUrl = $line[$colCount++];
                $featured = $line[$colCount++];
                $active = $line[$colCount++];
            }

            if (!$numcols || $numcols != $colCount) {
                Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            if ($rowCount == 0) {
                $coloumArr = $obj->getBrandColoumArr($langId);
                if ($line !== $coloumArr) {
                    Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }

            if ($rowCount > 0) {
                $dataToSaveArr = array(
                    'brand_status'=>applicationConstants::ACTIVE,
                    'brand_identifier'=>$identifier,
                );

                if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                    $dataToSaveArr['brand_id'] = $brandId;
                }

                if ($obj->isDefaultSheetData($langId)) {
                    if (FatApp::getConfig('CONF_USE_O_OR_1', FatUtility::VAR_INT, 0)) {
                        $dataToSaveArr['brand_featured'] = (FatUtility::int($featured) == 1)?applicationConstants::YES:applicationConstants::NO;
                        $dataToSaveArr['brand_active'] = (FatUtility::int($active) == 1)?applicationConstants::YES:applicationConstants::NO;
                    } else {
                        $dataToSaveArr['brand_featured'] = (strtoupper($featured) == 'YES')?applicationConstants::YES:applicationConstants::NO;
                        $dataToSaveArr['brand_active'] = (strtoupper($active) == 'YES')?applicationConstants::YES:applicationConstants::NO;
                    }
                }

                if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                    $brandData = Brand::getAttributesById($brandId, array('brand_id'));
                } else {
                    $brandId = 0;
                    $brandData = Brand::getAttributesByIdentifier($identifier, array('brand_id'));
                }

                if (!empty($brandData) && $brandData['brand_id']) {
                    $brandId = $brandData['brand_id'];

                    if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                        $where = array('smt' => 'brand_id = ?', 'vals' => array( $brandId ) );
                    } else {
                        $where = array('smt' => 'brand_id = ? AND brand_identifier = ?', 'vals' => array( $brandId, $identifier ) );
                    }

                    $db->updateFromArray(Brand::DB_TBL, $dataToSaveArr, $where);
                } else {
                    $brandId = $db->insertFromArray(Brand::DB_TBL, $dataToSaveArr);
                }

                if ($brandId) {
                    /* Lang Data [*/
                    $langData = array(
                        'brandlang_brand_id'=> $brandId,
                        'brandlang_lang_id'=> $langId,
                        'brand_name'=> $name,
                        'brand_short_description'=> $description,
                    );
                    $db->insertFromArray(Brand::DB_LANG_TBL, $langData, false, array(), $langData);
                    /* ]*/

                    /* Url rewriting [*/
                    if ($obj->isDefaultSheetData($langId)) {
                        if (trim($seoUrl) == '') {
                            $seoUrl = $identifier;
                        }
                        $brand = new Brand($brandId);
                        $brand->rewriteUrl($seoUrl);
                    }
                    /* ]*/
                }
            }
            $rowCount++;
        }

        Message::addMessage(Labels::getLabel('LBL_data_imported/updated_Successfully', $this->adminLangId));
        $this->set('msg', Message::getHtml());
        $this->_template->render(false, false, 'json-success.php');


        //    FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function importMedia()
    {
        $this->objPrivilege->canEditBrands();
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        $obj = new Importexport();

        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();

        $csvFilePointer = fopen($_FILES['import_file']['tmp_name'], 'r');
        $rowCount = 0;
        $langId = $this->adminLangId;

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $brandIdentifiers =  Brand::getAllIdentifierAssoc();
        $brandIds = array_flip($brandIdentifiers);

        while (($line = fgetcsv($csvFilePointer)) !== false) {
            if (empty($line[0])) {
                continue;
            }

            $numcols = count($line);
            $colCount = 0;

            if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                $brandId = FatUtility::int($line[$colCount++]);
            } else {
                $identifier = $line[$colCount++];
            }

            if (FatApp::getConfig('CONF_USE_LANG_ID', FatUtility::VAR_INT, 0)) {
                $landId = FatUtility::int($line[$colCount++]);
            } else {
                $langCode = $line[$colCount++];
            }

            $filePath = $line[$colCount++];
            $fileName = $line[$colCount++];
            $displayOrder = $line[$colCount++];

            if (!$numcols || $numcols != $colCount) {
                Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            if ($rowCount == 0) {
                $coloumArr = $obj->getBrandMediaColoumArr($langId);
                if ($line !== $coloumArr) {
                    Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }

            if ($rowCount > 0) {
                $fileType  = AttachedFile::FILETYPE_BRAND_LOGO;

                if (FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0)) {
                    $recordId = $brandId;
                } else {
                    $recordId = $brandIds[$identifier];
                }

                if (FatApp::getConfig('CONF_USE_LANG_ID', FatUtility::VAR_INT, 0)) {
                    $fileLangId = $landId;
                } else {
                    $fileLangId = $languageIds[$langCode];
                }

                $recordSubid = 0;

                $dataToSaveArr = array(
                    'afile_type'=>$fileType,
                    'afile_record_id'=>$recordId,
                    'afile_record_subid'=>$recordSubid,
                    'afile_lang_id'=>$fileLangId,
                    'afile_physical_path'=>$filePath,
                    'afile_name'=>$fileName,
                    'afile_display_order'=>$displayOrder,
                );

                $saveToTempTable = false;
                $isUrlArr = parse_url($filePath);

                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                if ($saveToTempTable) {
                    $dataToSaveArr['afile_downloaded'] = applicationConstants::NO;
                    $dataToSaveArr['afile_unique'] = applicationConstants::YES;
                    $db->deleteRecords(AttachedFile::DB_TBL_TEMP, array(
                            'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?',
                            'vals' => array($fileType, $recordId, $recordSubid, $fileLangId)
                    ));
                    $db->insertFromArray(AttachedFile::DB_TBL_TEMP, $dataToSaveArr, false, array(), $dataToSaveArr);
                } else {
                    $db->deleteRecords(AttachedFile::DB_TBL, array(
                            'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?',
                            'vals' => array($fileType, $recordId, $recordSubid, $fileLangId)
                    ));
                    $db->insertFromArray(AttachedFile::DB_TBL, $dataToSaveArr, false, array(), $dataToSaveArr);
                }
            }
            $rowCount++;
        }
        Message::addMessage(Labels::getLabel('LBL_data_imported/updated_Successfully', $this->adminLangId));
        $this->set('msg', Message::getHtml());
        $this->_template->render(false, false, 'json-success.php');

        //FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function exportBrandsForm()
    {
        $this->objPrivilege->canViewBrands();
        $frm = $this->getImportExportForm($this->adminLangId, 'EXPORT');
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function importBrandsForm()
    {
        $this->objPrivilege->canEditBrands();
        $frm = $this->getImportExportForm($this->adminLangId, 'IMPORT');
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function exportMediaForm()
    {
        $this->objPrivilege->canViewBrands();
        $frm = $this->getImportExportForm($this->adminLangId, 'EXPORT_MEDIA');
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function importMediaForm()
    {
        $this->objPrivilege->canEditBrands();
        $frm = $this->getImportExportForm($this->adminLangId, 'IMPORT_MEDIA');
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }
}
