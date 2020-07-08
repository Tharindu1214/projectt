<?php
class CollectionsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCollections($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCollections($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewCollections();
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');

        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'collection_type', Collections::getTypeArr($this->adminLangId));
        $frm->addSelectBox(Labels::getLabel('LBL_Layout_Type', $this->adminLangId), 'collection_layout_type', array( -1 =>Labels::getLabel('LBL_Does_Not_matter', $this->adminLangId) )+Collections::getLayoutTypeArr($this->adminLangId), '', array(), '');

        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewCollections();

        //$pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();

        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);

        $post = $searchForm->getFormDataFromArray($data);
        $srch = Collections::getSearchObject(false, $this->adminLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if (!empty($post['keyword'])) {
            $condition = $srch->addCondition('c.collection_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('c_l.collection_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $collection_type = FatApp::getPostedData('collection_type', FatUtility::VAR_INT, '');
        if ($collection_type) {
            $srch->addCondition('collection_type', '=', $collection_type);
        }

        $srch->addOrder('collection_active', 'DESC');

        $collection_layout_type = FatApp::getPostedData('collection_layout_type', FatUtility::VAR_INT, '');
        if ($collection_layout_type > 0) {
            $srch->addCondition('collection_layout_type', '=', $collection_layout_type);
            $srch->addOrder('collection_display_order', 'ASC');
        } else {
            $srch->addOrder('collection_id', 'DESC');
        }

        $srch->addMultipleFields(array('c.*' , 'c_l.collection_name'));



        /* $srch->setPageNumber($page);
        $srch->setPageSize($pagesize); */

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        /* $this->set('pageCount',$srch->pages());
        $this->set('recordCount',$srch->recordCount());
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post); */
        $this->set('page', $page);
        $this->set('collection_layout_type', $collection_layout_type);
        $this->_template->render(false, false);
    }

    public function form($collectionId)
    {
        $this->objPrivilege->canViewCollections();

        $collectionId =  FatUtility::int($collectionId);

        $frm = $this->getForm($collectionId);

        if (0 < $collectionId) {
            $data = Collections::getAttributesById($collectionId);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('collection_id', $collectionId);
        $this->set('collection_type', (isset($data['collection_type']))? $data['collection_type']: Collections::COLLECTION_TYPE_PRODUCT);
        $this->set('collection_layout_type', (isset($data['collection_layout_type']))? $data['collection_layout_type']: Collections::TYPE_PRODUCT_LAYOUT1);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function selprodForm($collectionId)
    {
        $this->objPrivilege->canViewCollections();

        $collectionId =  FatUtility::int($collectionId);

        $frm = $this->getSelProdForm($collectionId);

        $this->set('collection_id', $collectionId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function collectionCategoryForm($collectionId)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);
        $frm = $this->getCollectionCategoryForm($collectionId);
        $this->set('collection_id', $collectionId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }
    public function collectionShopForm($collectionId)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);
        $frm = $this->getCollectionShopForm($collectionId);
        $this->set('collection_id', $collectionId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function collectionBrandsForm($collectionId)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);
        $frm = $this->getCollectionBrandsForm($collectionId);
        $this->set('collection_id', $collectionId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditCollections();
        $frm = $this->getForm();
        $data = FatApp::getPostedData();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collectionId = $post['collection_id'];

        $post['collection_layout_type'] = $data['collection_layout_type'];
        unset($post['btn_submit']);

        $collection = new Collections($collectionId);
        $post['collection_primary_records'] = $this->getLayoutLimit($post['collection_layout_type']);
        if (!$collection->addUpdateData($post)) {
            Message::addErrorMessage($collection->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if ($collectionId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Collections::getAttributesByLangId($langId, $collectionId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $collectionId = $collection->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        /* if( $newTabLangId == 0 && !$this->isMediaUploaded($collectionId))
        {
        $this->set('openMediaForm', true);
        } */
        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('collectionId', $collectionId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($collectionId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId = FatUtility::int($collectionId);
        $lang_id = FatUtility::int($lang_id);

        if ($collectionId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($collectionId, $lang_id);
        $langData = Collections::getAttributesByLangId($lang_id, $collectionId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $collectionType = (0 < $collectionId) ? Collections::getAttributesById($collectionId, 'collection_type') : Collections::COLLECTION_TYPE_PRODUCT;

        $this->set('collectionType', $collectionType);

        $this->set('languages', Language::getAllNames());
        $this->set('collectionId', $collectionId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();

        $collectionId = $post['collection_id'];
        $lang_id = $post['lang_id'];

        if ($collectionId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($collectionId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['collection_id']);
        unset($post['lang_id']);

        $data = array(
        'collectionlang_lang_id'=>$lang_id,
        'collectionlang_collection_id'=>$collectionId,
        'collection_name'=>$post['collection_name'],
        /* 'collection_link_caption'=>$post['collection_link_caption'], */
        /* 'collection_description'=>$post['collection_description'], */
        );

        $collectionObj = new Collections($collectionId);

        if (!$collectionObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($collectionObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Collections::getAttributesByLangId($langId, $collectionId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        /* if( $newTabLangId == 0 && !$this->isMediaUploaded($collectionId))
        {
        $this->set('openMediaForm', true);
        } */
        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('collectionId', $collectionId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* private function isMediaUploaded($collectionId){
    if($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_IMAGE , $collectionId, 0 ))
    {
    return true;
    }
    return false;
    } */

    private function getForm($collectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);
        $collectionData = Collections::getAttributesById($collectionId);
        if ($collectionId) {
            $collectionType = $collectionData['collection_type'];
        } else {
            $collectionType =  Collections::COLLECTION_TYPE_PRODUCT;
        }
        $frm = new Form('frmCollection');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'collection_identifier');
        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'collection_type', Collections::getTypeArr($this->adminLangId), Collections::COLLECTION_TYPE_PRODUCT)->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Layout_Type', $this->adminLangId), 'collection_layout_type', $this->getLayoutAvailabale($collectionType))->requirements()->setRequired();

        $fld=$frm->addRadioButtons(Labels::getLabel('LBL_Criteria', $this->adminLangId), 'collection_criteria', Collections::getCriteria(), 1);
        $fld->html_after_field = '<br/><small>This is applicable only on category collections.</small>';

        // $frm->addTextBox(Labels::getLabel('LBL_Primary_Record', $this->adminLangId), 'collection_primary_records')->requirements()->setRequired();

        /* if($collectionData['collection_type'] != Collections::COLLECTION_TYPE_SHOP){
        $frm->addTextBox( Labels::getLabel('LBL_Child_Records',$this->adminLangId), 'collection_child_records' );
        } */

        /* $frm->addTextBox( Labels::getLabel('LBL_Link_URL(If_Any)',$this->adminLangId), 'collection_link_url' ); */
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'collection_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getSelProdForm($collectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);

        $frm = new Form('frmCollectionSelProd');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addTextbox(Labels::getLabel('LBL_Products', $this->adminLangId), 'products');

        return $frm;
    }

    private function getCollectionCategoryForm($collectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);

        $frm = new Form('frmCollectionCategory');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addTextbox(Labels::getLabel('LBL_Categories', $this->adminLangId), 'categories');
        return $frm;
    }

    private function getCollectionShopForm($collectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);

        $frm = new Form('frmCollectionShop');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addTextbox(Labels::getLabel('LBL_Shops', $this->adminLangId), 'shops');
        return $frm;
    }

    private function getCollectionBrandsForm($collectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId =  FatUtility::int($collectionId);

        $frm = new Form('frmCollectionBrands');
        $fld = $frm->addHiddenField('', 'collection_id', $collectionId);

        $fld->requirements()->setInt();
        $fld->requirements()->setIntPositive();
        $frm->addTextbox(Labels::getLabel('LBL_Brands', $this->adminLangId), 'brands');
        return $frm;
    }

    private function getLangForm($collectionId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCollections();
        $frm = new Form('frmCollectionLang');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Collection_Name', $this->adminLangId), 'collection_name');
        /* $frm->addTextBox( Labels::getLabel('LBL_Link_Caption(If_Any)',$this->adminLangId), 'collection_link_caption' );
        $frm->addTextArea(Labels::getLabel('LBL_Small_Description',$this->adminLangId), 'collection_description');*/
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditCollections();
        $collectionId = FatApp::getPostedData('collectionId', FatUtility::VAR_INT, 0);
        if (0 >= $collectionId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Collections::getAttributesById($collectionId, array('collection_id','collection_active'));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['collection_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateCollectionStatus($collectionId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditCollections();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $collectionIdsArr = FatUtility::int(FatApp::getPostedData('collection_ids'));
        if (empty($collectionIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($collectionIdsArr as $collectionId) {
            if (1 > $collectionId) {
                continue;
            }

            $this->updateCollectionStatus($collectionId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateCollectionStatus($collectionId, $status)
    {
        $status = FatUtility::int($status);
        $collectionId = FatUtility::int($collectionId);
        if (1 > $collectionId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $collectionObj = new Collections($collectionId);
        if (!$collectionObj->changeStatus($status)) {
            Message::addErrorMessage($collectionObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function updateSelProd()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collection_id = FatUtility::int($post['collection_id']);
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (!$collection_id || !$selprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections($collection_id);
        if (!$collectionObj->addUpdateCollectionSelProd($collection_id, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function collectionSelprods($collection_id)
    {
        $this->objPrivilege->canViewCollections();
        $collection_id = FatUtility::int($collection_id);
        if ($collection_id == 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $productOptions = Collections::getSellProds($collection_id, $this->adminLangId);
        $this->set('collectionSelprods', $productOptions);
        $this->set('collection_id', $collection_id);
        $this->_template->render(false, false);
    }

    public function updateCollectionCategories()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collection_id = FatUtility::int($post['collection_id']);
        $prodcat_id = FatUtility::int($post['prodcat_id']);
        if (!$collection_id || !$prodcat_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections($collection_id);
        if (!$collectionObj->addUpdateCollectionCategories($collection_id, $prodcat_id)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function collectionCategories($collection_id)
    {
        $this->objPrivilege->canViewCollections();
        $collection_id = FatUtility::int($collection_id);
        if ($collection_id == 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $collectionCategories = Collections::getCategories($collection_id, $this->adminLangId);
        $this->set('collectioncategories', $collectionCategories);
        $this->set('collection_id', $collection_id);
        $this->_template->render(false, false);
    }
    public function updateCollectionShops()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collection_id = FatUtility::int($post['collection_id']);
        $shop_id = FatUtility::int($post['shop_id']);
        if (!$collection_id || !$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections($collection_id);
        if (!$collectionObj->addUpdateCollectionShops($collection_id, $shop_id)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function collectionShops($collection_id)
    {
        $this->objPrivilege->canViewCollections();
        $collection_id = FatUtility::int($collection_id);
        if ($collection_id == 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $collectionShops = Collections::getShops($collection_id, $this->adminLangId);
        $this->set('collectionshops', $collectionShops);
        $this->set('collection_id', $collection_id);
        $this->_template->render(false, false);
    }

    public function updateCollectionBrands()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collectionId = FatUtility::int($post['collection_id']);
        $brandId = FatUtility::int($post['brand_id']);
        if (!$collectionId || !$brandId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections($collectionId);
        if (!$collectionObj->addUpdateCollectionBrands($collectionId, $brandId)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCollectionBrand()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionId = FatUtility::int($post['collection_id']);
        $brandId = FatUtility::int($post['brand_id']);
        if (1 > $collectionId || 1 > $brandId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections();
        if (!$collectionObj->removeCollectionBrands($collectionId, $brandId)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Brand_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function collectionBrands($collectionId)
    {
        $this->objPrivilege->canViewCollections();

        if (1 > $collectionId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $collectionBrands = Collections::getBrands($collectionId, $this->adminLangId);
        $this->set('collectionBrands', $collectionBrands);
        $this->set('collectionId', $collectionId);
        $this->_template->render(false, false);
    }

    public function removeCollectionSelProd()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collection_id = FatUtility::int($post['collection_id']);
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (!$collection_id || !$selprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections();
        if (!$collectionObj->removeCollectionSelProd($collection_id, $selprod_id)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Product_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCollectionCategory()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collection_id = FatUtility::int($post['collection_id']);
        $prodcat_id = FatUtility::int($post['prodcat_id']);
        if (!$collection_id || !$prodcat_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections();
        if (!$collectionObj->removeCollectionCategories($collection_id, $prodcat_id)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Category_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    public function removeCollectionShop()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collection_id = FatUtility::int($post['collection_id']);
        $shop_id = FatUtility::int($post['shop_id']);
        if (!$collection_id || !$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionObj = new Collections();
        if (!$collectionObj->removeCollectionShops($collection_id, $shop_id)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Shop_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoCompleteSelprods()
    {
        $this->objPrivilege->canViewCollections();
        $post = FatApp::getPostedData();
        $db = FatApp::getDb();
        $srch = new ProductSearch($this->adminLangId);
        $srch->setDefinedCriteria(0);
        $srch->addCondition('selprod_id', '>', 0);
        if (!empty($post['keyword'])) {
            /* $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
            $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%','OR');
            $srch->addCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%','OR'); */
            $srch->addDirectCondition("(selprod_title like " . $db->quoteVariable('%'.$post['keyword'].'%') . " or product_name LIKE " . $db->quoteVariable('%'.$post['keyword'].'%') . " or product_identifier LIKE " . $db->quoteVariable('%'.$post['keyword'].'%') . " )", 'and');
        }

        $srch->setPageSize(FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10));

        $srch->addMultipleFields(array('selprod_id','IFNULL(product_name,product_identifier) as product_name, IFNULL(selprod_title,product_identifier) as selprod_title'));
        /* echo $srch->getQuery(); */
        $rs = $srch->getResultSet();

        $products = $db->fetchAll($rs, 'selprod_id');
        $json = array();
        foreach ($products as $key => $product) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode(($product['selprod_title']!='')?$product['selprod_title']:$product['product_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function mediaForm($collectionId = 0)
    {
        $collectionId = FatUtility::int($collectionId);

        $collectionDetails = Collections::getAttributesById($collectionId);
        if (!false == $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (false != $collectionDetails) {
            $collectionImages = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_IMAGE, $collectionId);
            $this->set('collectionImages', $collectionImages);
            /*$collectionBgImages = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, $collectionId);
            $this->set('collectionBgImages', $collectionBgImages);*/
        }

        $this->set('imgUpdatedOn', Collections::getAttributesById($collectionId, 'collection_img_updated_on'));
        $this->set('collection_id', $collectionId);
        $this->set('displayMediaOnly', $collectionDetails['collection_display_media_only']);
        $this->set('collectionMediaFrm', $this->getMediaForm());
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    /* private function getMediaForm( $collectionId = 0 ){
    $frm = new Form('frmCollectionMedia');
    $frm->addHiddenField('','collection_id',$collectionId);
    $fld1 =  $frm->addButton('Image','collection_image','Upload file',array('class'=>'File-Js','data-file_type'=>AttachedFile::FILETYPE_COLLECTION_IMAGE,'data-collection_id'=>$collectionId));
    $fld = $frm->addButton( 'Background Image (if any)', 'collection_bg_image', 'Upload File', array('class' => 'File-Js', 'data-file_type'=>AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, 'data-collection_id'=>$collectionId) );
    return $frm;
    } */

    private function getMediaForm()
    {
        $frm = new Form('frmCollectionMedia');
        $languagesAssocArr = Language::getAllNames();
        $frm->addHTML('', 'collection_image_heading', '');
        $frm->addCheckBox(Labels::getLabel("LBL_Display_Media_Only", $this->adminLangId), 'collection_display_media_only', 1, array(), false, 0);
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'image_lang_id', array( 0 => Labels::getLabel('LBL_All_Languages', $this->adminLangId) ) + $languagesAssocArr, '', array(), '');
        $frm->addButton(
            Labels::getLabel('LBL_Image', $this->adminLangId),
            'collection_image',
            'Upload File',
            array('class'=>'File-Js','id'=>'collection_image','data-file_type'=>AttachedFile::FILETYPE_COLLECTION_IMAGE)
        );
        $frm->addHtml('', 'collection_image_display_div', '');

        /*$frm->addHTML('', 'collection_bg_image_heading', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'bg_image_lang_id', array( 0 => Labels::getLabel('LBL_Universal', $this->adminLangId) ) + $languagesAssocArr, '', array(), '');
        $fld = $frm->addButton(Labels::getLabel('LBL_Backgroud_Image(If_Any)', $this->adminLangId), 'collection_bg_image', 'Upload File', array('class' => 'File-Js', 'data-file_type'=>AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, 'data-collection_id'=>$collectionId));
        $frm->addHtml('', 'collection_bg_image_display_div', '');*/

        return $frm;
    }

    public function uploadImage()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $collection_id = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);

        if (!$collection_id || !$file_type) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collectionType = (0 < $collection_id) ? Collections::getAttributesById($collection_id, 'collection_type') : Collections::COLLECTION_TYPE_PRODUCT;
        if (in_array($collectionType, Collections::COLLECTION_WITHOUT_MEDIA)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Not_Allowed_To_Update_Media_For_This_Collection', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_COLLECTION_IMAGE, AttachedFile::FILETYPE_COLLECTION_BG_IMAGE);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $image_info = getimagesize($_FILES["file"]["tmp_name"]);
        $image_width = $image_info[0];
        $image_height = $image_info[1];

        /*if (AttachedFile::APP_IMAGE_WIDTH < $image_width || AttachedFile::APP_IMAGE_HEIGHT < $image_height) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Dimensions', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }*/

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $file_type,
            $collection_id,
            0,
            $_FILES['file']['name'],
            -1,
            $unique_record = true,
            $lang_id
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Collections::setLastUpdatedOn($collection_id);

        $this->set('file', $_FILES['file']['name']);
        $this->set('collection_id', $collection_id);
        $this->set('msg', $_FILES['file']['name']. Labels::getLabel('MSG_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeImage($collection_id = 0, $lang_id = 0)
    {
        $collection_id = FatUtility::int($collection_id);
        $lang_id = FatUtility::int($lang_id);
        if (1 > $collection_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_COLLECTION_IMAGE, $collection_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Collections::setLastUpdatedOn($collection_id);

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeBgImage($collection_id = 0, $lang_id = 0)
    {
        $collection_id = FatUtility::int($collection_id);
        $lang_id = FatUtility::int($lang_id);
        if (1 > $collection_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, $collection_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditCollections();

        $collection_id = FatApp::getPostedData('collectionId', FatUtility::VAR_INT, 0);
        if ($collection_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->markAsDeleted($collection_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditCollections();
        $collectionIdsArr = FatUtility::int(FatApp::getPostedData('collection_ids'));

        if (empty($collectionIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($collectionIdsArr as $collection_id) {
            if (1 > $collection_id) {
                continue;
            }
            $this->markAsDeleted($collection_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($collection_id)
    {
        $collection_id = FatUtility::int($collection_id);
        if (1 > $collection_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $collectionObj = new Collections($collection_id);
        if (!$collectionObj->canRecordMarkDelete($collection_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collectionObj->assignValues(array(Collections::tblFld('deleted') => 1));
        if (!$collectionObj->save()) {
            Message::addErrorMessage($collectionObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditCollections();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $collectionObj = new Collections();
            if (!$collectionObj->updateOrder($post['collectionList'])) {
                Message::addErrorMessage($collectionObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function layouts()
    {
        $this->_template->render(false, false);
    }

    public function getCollectionTypeLayout($collectionType, $searchForm = 0)
    {
        $this->objPrivilege->canEditCollections();
        $this->set('collectionType', $collectionType);
        $availableLayouts=  $this->getLayoutAvailabale($collectionType);
        if ($searchForm>0) {
            $availableLayouts=array(-1 => Labels::getLabel('LBL_Does_Not_matter', $this->adminLangId)) + $availableLayouts;
        }
        $this->set('availableLayouts', $availableLayouts);
        $this->_template->render(false, false);
    }

    private function getLayoutAvailabale($collectionType)
    {
        if (!$collectionType) {
            return  Collections::getLayoutTypeArr($this->adminLangId);
        }
        $collectionLayouts = array(

         Collections::COLLECTION_TYPE_PRODUCT => array(
                                Collections::TYPE_PRODUCT_LAYOUT1 => Labels::getLabel('LBL_Product_Layout1', $this->adminLangId),
                                Collections::TYPE_PRODUCT_LAYOUT2 => Labels::getLabel('LBL_Product_Layout2', $this->adminLangId),
                                Collections::TYPE_PRODUCT_LAYOUT3 => Labels::getLabel('LBL_Product_Layout3', $this->adminLangId),
           ),
         Collections::COLLECTION_TYPE_CATEGORY => array(
           Collections::TYPE_CATEGORY_LAYOUT1 => Labels::getLabel('LBL_Category_Layout1', $this->adminLangId),
           Collections::TYPE_CATEGORY_LAYOUT2 => Labels::getLabel('LBL_Category_Layout2', $this->adminLangId),
           ),
         Collections::COLLECTION_TYPE_SHOP => array(
           Collections::TYPE_SHOP_LAYOUT1 => Labels::getLabel('LBL_Shop_Layout1', $this->adminLangId),

           ),
         Collections::COLLECTION_TYPE_BRAND => array(
           Collections::TYPE_BRAND_LAYOUT1 => Labels::getLabel('LBL_Brand_Layout1', $this->adminLangId),
           )
                        );

        return $collectionLayouts[$collectionType];
    }

    public function getLayoutLimit($collection_layout_type)
    {
        switch ($collection_layout_type) {
            case Collections::TYPE_PRODUCT_LAYOUT1:
                return Collections::LIMIT_PRODUCT_LAYOUT1;
            break;
            case Collections::TYPE_PRODUCT_LAYOUT2:
                return Collections::LIMIT_PRODUCT_LAYOUT2;
            break;
            case Collections::TYPE_PRODUCT_LAYOUT3:
                return Collections::LIMIT_PRODUCT_LAYOUT3;
            break;
            case Collections::TYPE_CATEGORY_LAYOUT1:
                return Collections::LIMIT_CATEGORY_LAYOUT1;
            break;
            case Collections::TYPE_CATEGORY_LAYOUT2:
                return Collections::LIMIT_CATEGORY_LAYOUT2;
            break;
            case Collections::TYPE_SHOP_LAYOUT1:
                return Collections::LIMIT_SHOP_LAYOUT1;
            break;
            case Collections::TYPE_BRAND_LAYOUT1:
                return Collections::LIMIT_BRAND_LAYOUT1;
            break;
        }
    }

    public function displayMediaOnly($collectionId, $value = 0)
    {
        $collectionId = FatUtility::int($collectionId);
        if (1 > $collectionId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $collectionType = (0 < $collectionId) ? Collections::getAttributesById($collectionId, 'collection_type') : Collections::COLLECTION_TYPE_PRODUCT;
        if (in_array($collectionType, Collections::COLLECTION_WITHOUT_MEDIA)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Not_Allowed_To_Update_Media_For_This_Collection', $this->adminLangId));
        }

        $collectionObj = new Collections($collectionId);
        $collectionObj->addUpdateData(array('collection_display_media_only' => $value));
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
