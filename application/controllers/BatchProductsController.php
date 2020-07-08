<?php
class BatchProductsController extends LoggedUserController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';
        
        if (!User::canAccessSupplierDashboard()) {
            FatApp::redirectUser(CommonHelper::generateUrl('Account', 'supplierApprovalForm'));
        }
        $this->set('bodyClass', 'is--dashboard');
    }

    public function index()
    {
        $frmSearch = $this->getBatchSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $db = FatApp::getDb();
        $srchFrm = $this->getBatchSearchForm();
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            FatUtility::dieWithError(current($srchFrm->getValidationErrors()));
        }
        $srch = new ProductGroupSearch($this->siteLangId);
        $srch->addOrder('prodgroup_name');
        $srch->addMultipleFields(array( 'prodgroup_id', 'IFNULL(prodgroup_name, prodgroup_identifier) as prodgroup_name', 'prodgroup_active' ));
        $srch->addCondition('prodgroup_user_id', '=', $userId);
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        if ($keyword = FatApp::getPostedData('keyword')) {
            $srch->addCondition('prodgroup_name', 'like', "%$keyword%");
        }

        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);
        $this->set("arrListing", $arrListing);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    public function form($prodgroup_id = 0)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $frm = $this->getBatchForm($this->siteLangId);
        if ($prodgroup_id > 0) {
            $data = ProductGroup::getAttributesById($prodgroup_id);
            if (!$data || $data['prodgroup_user_id'] != $userId) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $frm->fill($data);
        }
        $this->set('frm', $frm);
        $this->set('prodgroup_id', $prodgroup_id);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setUpBatch()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $frm = $this->getBatchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);

        /* validate batch belongs to current logged in user[ */
        if ($prodgroup_id > 0) {
            $row = ProductGroup::getAttributesById($prodgroup_id);
            if (!$row || $row['prodgroup_user_id'] != $userId) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */

        $prodGroupObj = new ProductGroup($prodgroup_id);
        $dataToSaveArr  = array(
        'prodgroup_identifier'    =>    $post['prodgroup_identifier'],
        'prodgroup_price'        =>    $post['prodgroup_price'],
        'prodgroup_active'        =>    $post['prodgroup_active'],
        'prodgroup_user_id'        =>    $userId
        );
        $prodGroupObj->assignValues($dataToSaveArr);

        if (!$prodGroupObj->save()) {
            Message::addErrorMessage(Labels::getLabel($prodGroupObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($prodgroup_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = ProductGroup::getAttributesByLangId($langId, $prodgroup_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $prodgroup_id = $prodGroupObj->getMainTableRecordId();
            $newTabLangId = $this->siteLangId;
        }

        if ($newTabLangId == 0 && !$this->isMediaUploaded($prodgroup_id)) {
            $this->set('openMediaForm', true);
        }

        $this->set('prodgroup_id', $prodgroup_id);
        $this->set('lang_id', $newTabLangId);
        $this->set('msg', Labels::getLabel('LBL_Batch_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function isMediaUploaded($prodgroup_id)
    {
        if ($attachment = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BATCH_IMAGE, $prodgroup_id, 0, -1)) {
            return true;
        }
        return false;
    }

    public function setUpLangBatch()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        $frm = $this->getBatchLangForm($prodgroup_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($lang_id <= 0 || $prodgroup_id <= 0) {
            Message::addErrorMessag(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);
        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $prodGroupObj = new ProductGroup($prodgroup_id);
        $dataToSaveArr  = array(
        'prodgrouplang_prodgroup_id'    =>    $prodgroup_id,
        'prodgrouplang_lang_id'            =>    $lang_id,
        'prodgroup_name'                =>    $post['prodgroup_name']
        );

        if (!$prodGroupObj->updateLangData($lang_id, $dataToSaveArr)) {
            Message::addErrorMessage(Labels::getLabel($prodGroupObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = ProductGroup::getAttributesByLangId($langId, $prodgroup_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        if ($newTabLangId == 0 && !$this->isMediaUploaded($prodgroup_id)) {
            $this->set('openMediaForm', true);
        }

        $this->set('prodgroup_id', $prodgroup_id);
        $this->set('lang_id', $newTabLangId);
        $this->set('msg', Labels::getLabel('LBL_Batch_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($prodgroup_id, $lang_id)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $lang_id = FatUtility::int($lang_id);
        if ($prodgroup_id <= 0 || $lang_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $lang_id));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);
        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $frm = $this->getBatchLangForm($prodgroup_id, $lang_id);
        $data = ProductGroup::getAttributesByLangId($lang_id, $prodgroup_id);
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->set('prodgroup_id', $prodgroup_id);
        $this->set('prodgroup_lang_id', $lang_id);
        $this->set('language', Language::getAllNames());
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function batchProductsForm($prodgroup_id)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $frm = $this->getBatchProductsForm($prodgroup_id, $this->siteLangId);
        /* if( $prodgroup_id > 0 ){
        $data = ProductGroup::getAttributesById( $prodgroup_id );
        $frm->fill( $data );
        } */
        $this->set('frm', $frm);
        $this->set('prodgroup_id', $prodgroup_id);
        //$this->set( 'language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function loadBatchProducts($prodgroup_id)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);

        $userId = UserAuthentication::getLoggedUserId();
        $db = FatApp::getDb();

        /* check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);

        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $sellerProductObj = new SellerProduct();
        $products = $sellerProductObj->getProductsToGroup($prodgroup_id, $this->siteLangId);
        //CommonHelper::printArray($products);
        if ($products) {
            foreach ($products as &$product) {
                $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->siteLangId);
                $product['options'] = $options;
            }
        }

        $this->set('products', $products);
        $this->set('prodgroup_id', $prodgroup_id);
        $this->_template->render(false, false);
    }

    public function updateProductToGroup()
    {
        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $userId = UserAuthentication::getLoggedUserId();
        if ($prodgroup_id <= 0 || $selprod_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);
        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $prodGroupObj = new ProductGroup();
        if (!$prodGroupObj->addUpdateProductToGroup($prodgroup_id, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel($prodGroupObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProductToGroup()
    {
        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $userId = UserAuthentication::getLoggedUserId();
        if ($prodgroup_id <= 0 || $selprod_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);
        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $productGroupObj = new ProductGroup();
        if (!$productGroupObj->removeProductToGroup($prodgroup_id, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel($productGroupObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Product_removed_successfully_from_batch', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setMainProductFromGroup()
    {
        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $userId = UserAuthentication::getLoggedUserId();
        if ($prodgroup_id <= 0 || $selprod_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);
        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $productGroupObj = new ProductGroup();
        if (!$productGroupObj->setMainProductFromGroup($prodgroup_id, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel($productGroupObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Product_marked_as_main_product', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function batchMediaForm($prodgroup_id)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $userId = UserAuthentication::getLoggedUserId();

        /* check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);

        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $mediaFrm = $this->getBatchMediaForm($prodgroup_id, $this->siteLangId);

        $batchImgArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BATCH_IMAGE, $prodgroup_id, 0, -1);
        $this->set('batchImgArr', $batchImgArr);

        $this->set('mediaFrm', $mediaFrm);
        $this->set('prodgroup_id', $prodgroup_id);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function uploadBatchImage()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);

        if ($lang_id <= 0 || $prodgroup_id <= 0) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* Check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);

        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_select_a_file', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();

        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            AttachedFile::FILETYPE_BATCH_IMAGE,
            $prodgroup_id,
            0,
            $_FILES['file']['name'],
            0,
            true,
            $lang_id
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('prodgroup_id', $prodgroup_id);
        Message::addMessage(Labels::getLabel("LBL_Batch_Image_uploaded_successfully.", $this->siteLangId));
        $this->set('msg', Message::getHtml());
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function image( $prodgroup_id, $lang_id, $sizeType = '' ){
    $prodgroup_id = FatUtility::int( $prodgroup_id );
    $lang_id = FatUtility::int( $lang_id );
    $default_image = '';

    $file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_BATCH_IMAGE, $prodgroup_id, 0, $lang_id );

    $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

    switch( strtoupper($sizeType) ){
    case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage( $image_name, $w, $h, $default_image );
    break;
    case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage( $image_name, $w, $h, $default_image );
    break;
    default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage( $image_name, $w, $h, $default_image );
    break;
    }
    } */

    public function removeBatchImage()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if ($prodgroup_id <= 0 || $lang_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Check product group belongs to current user[ */
        $row = ProductGroup::getAttributesById($prodgroup_id);

        if (!$row || $row['prodgroup_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Access!', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_BATCH_IMAGE, $prodgroup_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getBatchMediaForm($prodgroup_id, $lang_id)
    {
        $frm = new Form('frmBatchMedia');
        $frm->addHiddenField('', 'prodgroup_id', $prodgroup_id);
        $frm->addSelectBox('Language', 'lang_id', Language::getAllNames(), '', array(), '');

        $fld =  $frm->addButton('', 'prodgroup_image', Labels::getLabel('LBL_Upload_File', $lang_id), array('class'=>'prodgroup-Js btn btn--primary btn--sm', 'id' => 'prodgroup_image', 'data-prodgroup_id' => $prodgroup_id ));
        return $frm;
    }

    private function getBatchForm($lang_id)
    {
        $frm = new Form('frmBatch');
        $frm->addHiddenField('', 'prodgroup_id');
        $frm->addTextBox(Labels::getLabel('LBL_Identifier', $lang_id), 'prodgroup_identifier')->requirements()->setRequired();
        $frm->addFloatField(Labels::getLabel('LBL_Batch_Price', $lang_id).CommonHelper::concatCurrencySymbolWithAmtLbl(), 'prodgroup_price');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $lang_id), 'prodgroup_active', applicationConstants::getActiveInactiveArr($lang_id), '', array(), '')->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $lang_id));
        return $frm;
    }

    private function getBatchLangForm($prodgroup_id, $lang_id)
    {
        $frm = new Form('frmBatchLang');
        $frm->addHiddenField('', 'prodgroup_id', $prodgroup_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addTextBox(Labels::getLabel('LBL_Name', $lang_id), 'prodgroup_name')->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $lang_id));
        return $frm;
    }

    private function getBatchSearchForm()
    {
        $frm = new Form('frmBatchSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->siteLangId), 'keyword');
        $frm->addHiddenField('page', 'page', 1);
        $frm->addSubmitButton('', 'btn_submit', '');
        return $frm;
    }

    private function getBatchProductsForm($prodgroup_id, $lang_id)
    {
        $frm = new Form('frmBatchProducts');
        $frm->addTextBox(Labels::getLabel('LBL_Add_Products', $lang_id), 'product_name');
        $frm->addHiddenField('', 'selprod_id');
        $frm->addHiddenField('', 'prodgroup_id', $prodgroup_id);
        return $frm;
    }
}
