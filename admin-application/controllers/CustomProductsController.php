<?php
class CustomProductsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCustomProductRequests($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCustomProductRequests($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
        $this->set("includeEditor", true);
    }

    public function index()
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $frmSearch = $this->catalogCustomProductRequestSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srchForm = $this->catalogCustomProductRequestSearchForm();

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $post = $srchForm->getFormDataFromArray($data);

        $srch = ProductRequest::getSearchObject($this->adminLangId, false, true);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'preq_user_id = u.user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->addOrder('preq_added_on', 'desc');

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('preq.preq_content', 'like', '%'.$post['keyword'].'%');
            $cond->attachCondition('preq_l.preq_lang_data', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('u.user_name', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('uc.credential_email', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('uc.credential_username', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if (!empty($post['date_from'])) {
            $srch->addCondition('preq.preq_added_on', '>=', $post['date_from']. ' 00:00:00');
        }

        if ($post['status'] > -1) {
            $srch->addCondition('preq.preq_status', '=', $post['status']);
        }

        if (!empty($post['date_to'])) {
            $srch->addCondition('preq.preq_added_on', '<=', $post['date_to']. ' 23:59:59');
        }
        $srch->addOrder('preq.preq_added_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        while ($res = FatApp::getDb()->fetch($rs)) {
            $content =     (!empty($res['preq_content']))?json_decode($res['preq_content'], true):array();
            $langContent =     (!empty($res['preq_lang_data']))?json_decode($res['preq_lang_data'], true):array();

            $res  = array_merge($res, $content);
            if (!empty($langContent)) {
                $res  = array_merge($res, $langContent);
            }
            $arr  = array(
            'preq_id'=>$res['preq_id'],
            'preq_user_id'=>$res['preq_user_id'],
            'preq_added_on'=>$res['preq_added_on'],
            'preq_status'=>$res['preq_status'],
            'user_id'=>$res['user_id'],
            'user_name'=>$res['user_name'],
            'credential_username'=>$res['credential_username'],
            'credential_email'=>$res['credential_email'],
            'product_identifier'=>$res['product_identifier'],
            'product_name'=>(!empty($res['product_name']))?$res['product_name']:'',
            );
            $records[]  =  $arr;
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('reqStatusClassArr', User::getCatalogRequestClassArr());
        $this->set('reqStatusArr', ProductRequest::getStatusArr($this->adminLangId));
        $this->set('canViewCustomProductRequests', $this->objPrivilege->canViewCustomProductRequests($this->admin_id, true));
        $this->set('canEditCustomProductRequests', $this->objPrivilege->canEditCustomProductRequests($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function form($preqId = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility::int($preqId);
        if (!$preqId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id','preq_prodcat_id'));
        $preq_prodcat_id = $productReqRow['preq_prodcat_id'];

        $customProductFrm = $this->getForm(0);
        $productOptions = array();
        $productTags = array();
        if ($preqId > 0) {
            $row_data = ProductRequest::getAttributesById($preqId, array('preq_id', 'preq_user_id','preq_prodcat_id','preq_content','preq_status','preq_deleted','preq_added_on'));
            $productData = json_decode($row_data['preq_content'], true);
            unset($row_data['preq_content']);
            $row_data = array_merge($row_data, $productData);
            $productOptions = !empty($row_data['product_option'])?$row_data['product_option']:array();
            $productTags = !(empty($row_data['product_tags']))?$row_data['product_tags']:array();

            /*   */

            $customProductFrm->fill($row_data);
        }

        $this->set('customProductFrm', $customProductFrm);
        $this->set('preqId', $preqId);
        $this->set('preq_prodcat_id', $preq_prodcat_id);
        $this->set('productOptions', $productOptions);
        $this->set('productTags', $productTags);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $post = FatApp::getPostedData();
        $product_option = FatApp::getPostedData('product_option');
        $product_tags = FatApp::getPostedData('product_tags');
        $product_shipping = FatApp::getPostedData('product_shipping');

        $frm = $this->getForm(0);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $preq_id = FatUtility::int($post['preq_id']);
        $preq_user_id = FatUtility::int($post['product_seller_id']);
        unset($post['preq_id']);
        unset($post['preq_user_id']);
        unset($post['btn_submit']);

        $prodReqObj = new ProductRequest($preq_id);
        $data_to_be_save = $post;
        $data_to_be_save['product_option'] = $product_option;
        $data_to_be_save['product_tags'] = $product_tags;
        $data_to_be_save['product_shipping'] = $product_shipping;
        if ($post['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $data_to_be_save['product_length'] = 0;
            $data_to_be_save['product_width'] = 0;
            $data_to_be_save['product_height'] = 0;
            $data_to_be_save['product_dimension_unit'] = 0;
            $data_to_be_save['product_weight'] = 0;
            $data_to_be_save['product_weight_unit'] = 0;
        }
        $data = array(
        'preq_user_id'=>$preq_user_id,
        'preq_content'=>FatUtility::convertToJson($data_to_be_save),
        'preq_status'=>ProductRequest::STATUS_PENDING,
        );

        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            Message::addErrorMessage($prodReqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $preq_id = $prodReqObj->getMainTableRecordId();
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('preq_id', $preq_id);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function sellerProductForm($preqId = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $preqId = FatUtility::int($preqId);
        if (!$preqId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $productReqRow = ProductRequest::getAttributesById($preqId);
        if (!$productReqRow) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $productReqRow = array_merge($productReqRow, json_decode($productReqRow['preq_content'], true));

        if ($productReqRow['preq_sel_prod_data'] !='') {
            $productReqRow = array_merge($productReqRow, json_decode($productReqRow['preq_sel_prod_data'], true));
        }

        $productOptions = !empty($productReqRow['product_option']) ? $productReqRow['product_option'] : array();
        $preq_user_id = $productReqRow['preq_user_id'];

        $frmSellerProduct = $this->getSellerProductForm($preqId, 'REQUESTED_CATALOG_PRODUCT');
        $frmSellerProduct->fill($productReqRow);

        $this->set('preqId', $preqId);
        $this->set('preq_user_id', $preq_user_id);
        $this->set('productReqRow', $productReqRow);
        $this->set('productOptions', $productOptions);
        $this->set('frmSellerProduct', $frmSellerProduct);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setupSellerProduct()
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $preqId = FatApp::getPostedData('selprod_product_id', FatUtility::VAR_INT, 0);
        if (!$preqId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getSellerProductForm($preqId, 'REQUESTED_CATALOG_PRODUCT');
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        unset($post['btn_cancel']);
        unset($post['btn_submit']);

        $prodReqObj = new ProductRequest($preqId);
        $data = array(
        'preq_sel_prod_data'=>FatUtility::convertToJson($post),
        );
        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            Message::addErrorMessage($prodReqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('preq_id', $preqId);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* Specification Module [ */

    public function specificationForm($preqId = 0, $prodspecId = 0)
    {
        $preqId = FatUtility::int($preqId);

        $productOptions = array();
        $productRow = array();

        if ($preqId) {
            $productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id','preq_prodcat_id','preq_content','preq_specifications'));
            $preqCatId = $productRow['preq_prodcat_id'];
            $productReqData = json_decode($productRow['preq_content'], true);
            $productOptions = $productReqData['product_option'];
        }

        $productSpecData = json_decode($productRow['preq_specifications'], true);
        $this->set('productSpecifications', $productSpecData);
        $this->set('preqId', $preqId);
        $this->set('preqCatId', $preqCatId);
        $this->set('productOptions', $productOptions);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function getSpecificationForm($preqId, $prodspecId=0, $divCount=0)
    {
        $post = FatApp::getPostedData();
        $data = array();
        $data['product_id'] = $preqId;
        $data['prodspec_id'] = $prodspecId;
        $this->set('adminLangId', $this->adminLangId);
        $this->set('languages', Language::getAllNames());
        $this->set('preqId', $preqId);
        $this->set('divCount', $divCount);
        $this->_template->render(false, false);
    }

    public function setupSpecification($preqId, $prodSpecId=0)
    {
        $preqId = FatUtility::int($preqId);

        $post = FatApp::getPostedData();
        if (false === $post) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Please_fill_Specifications', $this->adminLangId));
        }

        $languages = Language::getAllNames();
        foreach ($post['prod_spec_name'][CommonHelper::getLangId()] as $specKey=>$specval) {
            $count = 0;
            foreach ($languages as $langId=>$langName) {
                if ($post['prod_spec_name'][$langId][$specKey] == '') {
                    $count++;
                }

                if ($count == count($languages)) {
                    foreach ($languages as $langId=>$langName) {
                        unset($post['prod_spec_name'][$langId][$specKey]);
                        unset($post['prod_spec_value'][$langId][$specKey]);
                    }
                }
            }
        }

        unset($post['btn_submit']);
        unset($post['fOutMode']);
        unset($post['fIsAjax']);
        $prodReqObj = new ProductRequest($preqId);
        $data = array(
        'preq_specifications'=> FatUtility::convertToJson($post)
        );

        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            FatUtility::dieWithError($prodReqObj->getError());
        }
        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $preqId = $prodReqObj->getMainTableRecordId();
        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('preqId', $preqId);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* ] */


    public function langForm($preq_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $preq_id = FatUtility::int($preq_id);
        $lang_id = FatUtility::int($lang_id);

        if ($preq_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $customProductLangFrm = $this->getLangForm($preq_id, $lang_id);
        $prodObj = new ProductRequest($preq_id);
        $customProductLangData = $prodObj->getAttributesByLangId($lang_id, $preq_id);
        if ($customProductLangData) {
            $customProductLangData['preq_id'] = $preq_id;
            $productData = json_decode($customProductLangData['preq_lang_data'], true);

            unset($customProductLangData['preq_lang_data']);
            if (is_array($productData)) {
                $customProductLangData = array_merge($customProductLangData, $productData);
            }
            $customProductLangFrm->fill($customProductLangData);
        }

        $row_data = ProductRequest::getAttributesById($preq_id, array('preq_content'));
        $productData = json_decode($row_data['preq_content'], true);
        $row_data = array_merge($row_data, $productData);
        $productOptions = !empty($row_data['product_option']) ? $row_data['product_option'] : array();

        $customProductLangData['preq_id'] = $preq_id;

        $this->set('languages', Language::getAllNames());
        $this->set('preqId', $preq_id);
        $this->set('productOptions', $productOptions);
        $this->set('product_lang_id', $lang_id);
        $this->set('customProductLangFrm', $customProductLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditCustomProductRequests();

        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        $preq_id = FatUtility::int($post['preq_id']);

        if ($preq_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($preq_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        unset($post['preq_id']);
        unset($post['lang_id']);
        unset($post['btn_submit']);
        $data_to_update = array(
        'preqlang_preq_id'    =>    $preq_id,
        'preqlang_lang_id'    =>    $lang_id,
        'preq_lang_data'    =>    FatUtility::convertToJson($post),
        );

        $prodObj = new ProductRequest($preq_id);
        if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = ProductRequest::getAttributesByLangId($langId, $preq_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        $data = ProductRequest::getAttributesById($preq_id, array('preq_content'));
        $productData = json_decode($data['preq_content'], true);
        $productOptions = !empty($productData['product_option']) ? $productData['product_option'] : array();

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('preq_id', $preq_id);
        $this->set('productOptions', $productOptions);
        $this->set('lang_id', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateStatusForm($preqId = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility :: int($preqId);
        if (!$preqId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = ProductRequest::getAttributesById($preqId, array('preq_id,preq_content'));

        $productData = json_decode($data['preq_content'], true);
        $productOptions = !empty($productData['product_option']) ? $productData['product_option'] : array();

        $frm = $this->getStatusForm();
        $frm->fill($data);

        $this->set('frm', $frm);
        $this->set('preqId', $preqId);
        $this->set('productOptions', $productOptions);
        $this->set('languages', Language::getAllNames());
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function updateStatus()
    {
        $this->objPrivilege->canEditCustomProductRequests();

        $frm = $this->getStatusForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $preqId =  $post['preq_id'];
        $status = $post['preq_status'];
        $update_withselprod = $post['preq_update_withselprod'];

        $srch = ProductRequest::getSearchObject($this->adminLangId);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = preq.preq_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'c.credential_user_id = u.user_id', 'c');
        $srch->addCondition('preq_id', '=', $preqId);
        $srch->addMultipleFields(array('preq.*','user_name','credential_email'));
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = $db->fetch($rs);

        if ($data == false || $data['preq_deleted'] == applicationConstants::YES || $data['preq_status'] == ProductRequest::STATUS_APPROVED) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($status != ProductRequest::STATUS_APPROVED && $status != ProductRequest::STATUS_CANCELLED) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();
        $prodReqObj = new ProductRequest($preqId);
        $updateData = array('preq_status'=>$status,'preq_comment'=>$post['preq_comment']);
        $prodReqObj->assignValues($updateData);

        if (!$prodReqObj->save()) {
            Message::addErrorMessage($prodReqObj->getError());
            $db->rollbackTransaction();
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($status == ProductRequest::STATUS_APPROVED) {
            $data = array_merge($data, json_decode($data['preq_content'], true));
            $prodObj = new Product();
            $productData = array(
            'product_identifier'=>isset($data['product_identifier'])?$data['product_identifier']:'',
            'product_type'=>isset($data['product_type'])?$data['product_type']:'',
            'product_model'=>isset($data['product_model'])?$data['product_model']:'',
            'product_brand_id'=>isset($data['product_brand_id'])?$data['product_brand_id']:0,
            'product_added_by_admin_id' => applicationConstants::YES,
            /* 'product_seller_id'=>isset($data['preq_user_id'])?$data['preq_user_id']:0, */
            'product_min_selling_price'=>isset($data['product_min_selling_price'])?$data['product_min_selling_price']:0,
            'product_length'=>isset($data['product_length'])?$data['product_length']:0,
            'product_width'=>isset($data['product_width'])?$data['product_width']:0,
            'product_height'=>isset($data['product_height'])?$data['product_height']:0,
            'product_dimension_unit'=>isset($data['product_dimension_unit'])?$data['product_dimension_unit']:0,
            'product_weight'=>isset($data['product_weight'])?$data['product_weight']:0,
            'product_weight_unit'=>isset($data['product_weight_unit'])?$data['product_weight_unit']:0,
            'product_cod_enabled'=>isset($data['product_cod_enabled'])?$data['product_cod_enabled']:0,
            'product_ship_free'=>isset($data['ps_free'])?$data['ps_free']:0,
            'product_ship_country'=>isset($data['ps_from_country_id'])?$data['ps_from_country_id']:0,
            'product_added_on'=>date('Y-m-d H:i:s'),
            'product_featured' => isset($data['product_featured'])?$data['product_featured']:applicationConstants::NO,
            'product_upc' => isset($data['product_upc'])?$data['product_upc']:applicationConstants::NO,
            'product_active' => applicationConstants::YES,
            'product_approved' => applicationConstants::YES,
            );

            $prodObj->assignValues($productData);
            if (!$prodObj->save()) {
                Message::addErrorMessage($prodObj->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }

            $product_id = $prodObj->getMainTableRecordId();

            /* saving of product categories[ */
            $product_categories = array($data['preq_prodcat_id']);
            if (!$prodObj->addUpdateProductCategories($product_id, $product_categories)) {
                Message::addErrorMessage($prodObj->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }
            /* ] */

            /*Save Prodcut tax category [*/
            $prodTaxData = array(
            'ptt_product_id'=>$product_id,
            'ptt_taxcat_id'=>$data['ptt_taxcat_id'],
            );
            $taxObj = new Tax();
            if (!$taxObj->addUpdateProductTaxCat($prodTaxData)) {
                Message::addErrorMessage($taxObj->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }
            /*]*/

            /* saving of product options[ */
            $optons = isset($data['product_option'])?$data['product_option']:array();
            if (!empty($optons)) {
                foreach ($optons as $option_id) {
                    if (!$prodObj->addUpdateProductOption($product_id, $option_id)) {
                        Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }
            /*]*/

            /* Saving of product tags[ */
            $tags = isset($data['product_tags'])?$data['product_tags']:array();
            if (!empty($tags)) {
                foreach ($tags as $tag_id) {
                    if (!$prodObj->addUpdateProductTag($product_id, $tag_id)) {
                        Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }
            /*]*/

            /* Update Product seller shipping [*/
            $prodSellerShipArr = array(
            'ps_from_country_id'=>$productData['product_ship_country'],
            'ps_free'=>$productData['product_ship_free']
            );

            if (!Product::addUpdateProductSellerShipping($product_id, $prodSellerShipArr, 0)) {
                Message::addErrorMessage(FatApp::getDb()->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }
            /* ]*/

            /* Saving product shippings [ */
            $shippingArr = isset($data['product_shipping'])?$data['product_shipping']:array();
            if (!empty($shippingArr)) {
                if (!Product::addUpdateProductShippingRates($product_id, $shippingArr, 0)) {
                    Message::addErrorMessage(FatApp::getDb()->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
            /*]*/

            /* Product Lang data insert[*/
            $languages = Language::getAllNames();
            foreach ($languages as $lang_id => $langName) {
                $reqLangData = ProductRequest::getAttributesByLangId($lang_id, $preqId);
                if ($reqLangData == false) {
                    continue;
                }

                $arr = json_decode($reqLangData['preq_lang_data'], true);
                if (!empty($arr)) {
                    $reqLangData = array_merge($reqLangData, json_decode($reqLangData['preq_lang_data'], true));
                }

                $productLangData = array(
                'productlang_product_id'=>$product_id,
                'productlang_lang_id'=>$lang_id,
                'product_name'=>isset($reqLangData['product_name']) ? $reqLangData['product_name'] : $data['product_identifier'],
                'product_description'=>isset($reqLangData['product_description'])?$reqLangData['product_description']:'',
                'product_youtube_video'=>isset($reqLangData['product_youtube_video'])?$reqLangData['product_youtube_video']:'',
                'product_tags_string'=>'',
                );
                if (!$prodObj->updateLangData($lang_id, $productLangData)) {
                    Message::addErrorMessage($prodObj->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
            /*]*/

            Tag::updateProductTagString($product_id);

            /*[ Saving product UPC/EAN/ISBN*/
            $upcCodeData = array();
            if (isset($data['preq_ean_upc_code'])) {
                $upcCodeData = json_decode($data['preq_ean_upc_code'], true);
            }
            $srch = UpcCode::getSearchObject();
            $srch->addCondition('upc_product_id', '!=', $product_id);
            $srch->doNotCalculateRecords();
            $srch->setPageSize(1);
            if (!empty($upcCodeData)) {
                foreach ($upcCodeData as $key => $code) {
                    if (trim($code) == '') {
                        continue;
                    }

                    $options = str_replace('|', ',', $key);

                    $rSrch = clone $srch;
                    $rSrch->addCondition('upc_code', '=', $code);
                    $rs = $rSrch->getResultSet();
                    $totalRecords = FatApp::getDb()->totalRecords($rs);
                    if ($totalRecords > 0) {
                        continue;
                    }

                    $optionSrch = clone $srch;
                    $optionSrch->addCondition('upc_options', '=', $options);
                    $rs = $optionSrch->getResultSet();
                    $row = FatApp::getDb()->fetch($rs);

                    $upcData = array(
                    'upc_code'=>$code,
                    'upc_product_id'=>$product_id,
                    'upc_options'=>$options,
                    );

                    if ($row && $row['upc_product_id'] == $product_id && $row['upc_options'] == $options) {
                        $upcObj = new UpcCode($row['upc_code_id']);
                    } else {
                        $upcObj = new UpcCode();
                    }

                    $upcObj->assignValues($upcData);
                    if (!$upcObj->save()) {
                        Message::addErrorMessage($upcObj->getError());
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }

            /*]*/

            /* Updating images[*/
            $where = array('smt'=>'afile_record_id = ? and afile_type = ?', 'vals'=>array($preqId,AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE));

            $db->updateFromArray(AttachedFile::DB_TBL, array('afile_record_id'=>$product_id,'afile_type'=>AttachedFile::FILETYPE_PRODUCT_IMAGE), $where);
            /*]*/

            $selProdData = isset($data['preq_sel_prod_data'])?json_decode($data['preq_sel_prod_data'], true):array();
            if ($update_withselprod && !empty($selProdData)) {
                $updateSelProdData = array(
                'selprod_user_id'=>isset($selProdData['preq_user_id'])?$selProdData['preq_user_id']:$data['preq_user_id'],
                'selprod_product_id'=>$product_id,
                'selprod_cost'=>isset($selProdData['selprod_cost'])?$selProdData['selprod_cost']:0,
                'selprod_price'=>isset($selProdData['selprod_price'])?$selProdData['selprod_price']:0,
                'selprod_stock'=>isset($selProdData['selprod_stock'])?$selProdData['selprod_stock']:0,
                'selprod_min_order_qty'=>isset($selProdData['selprod_min_order_qty'])?$selProdData['selprod_min_order_qty']:0,
                /* 'selprod_max_order_qty'=>isset($selProdData['selprod_min_order_qty'])?$selProdData['selprod_max_order_qty']:0, */
                'selprod_subtract_stock'=>isset($selProdData['selprod_subtract_stock'])?$selProdData['selprod_subtract_stock']:0,
                'selprod_track_inventory'=>isset($selProdData['selprod_track_inventory'])?$selProdData['selprod_track_inventory']:0,
                'selprod_sku'=>isset($selProdData['selprod_sku'])?$selProdData['selprod_sku']:'',
                'selprod_condition'=>isset($selProdData['selprod_condition'])?$selProdData['selprod_condition']:Product::CONDITION_NEW,
                'selprod_available_from'=>isset($selProdData['selprod_available_from'])?$selProdData['selprod_available_from']:'',
                'selprod_active'=>isset($selProdData['selprod_active'])?$selProdData['selprod_active']:'',
                'selprod_cod_enabled'=>isset($selProdData['selprod_cod_enabled'])?$selProdData['selprod_cod_enabled']:'',
                );

                $options = array();

                $optionValueIdArr = (isset($selProdData['selprodoption_optionvalue_id']) && count($selProdData['selprodoption_optionvalue_id'])> 0)?$selProdData['selprodoption_optionvalue_id']:array();
                foreach ($optionValueIdArr as $optionValueId) {
                    $row = OptionValue::getAttributesById($optionValueId, array('optionvalue_option_id'));
                    if ($row == false) {
                        continue;
                    }
                    $options[$row['optionvalue_option_id']] = $optionValueId;
                }

                asort($options);


                $selProdCode = $product_id.'_'.implode('_', $options);
                $updateSelProdData['selprod_code'] = $selProdCode;

                if (isset($data['selprod_track_inventory']) && $data['selprod_track_inventory'] == Product::INVENTORY_NOT_TRACK) {
                    $updateSelProdData['selprod_threshold_stock_level'] = 0;
                }

                $sellerProdObj = new SellerProduct();
                $sellerProdObj->assignValues($updateSelProdData);
                if (!$sellerProdObj->save()) {
                    Message::addErrorMessage($sellerProdObj->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
                $selprod_id = $sellerProdObj->getMainTableRecordId();

                /* Save url keyword [ */
                $urlKeyword = strtolower(CommonHelper::createSlug($selProdData['selprod_url_keyword']));
                $seoUrl =  CommonHelper::seoUrl($urlKeyword).'-'.$selprod_id;
                $originalUrl = Product::PRODUCT_VIEW_ORGINAL_URL.$selprod_id;
                $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl);
                $seoUrlKeyword = array(
                'urlrewrite_original'=>$originalUrl,
                'urlrewrite_custom'=>$customUrl
                );
                FatApp::getDb()->insertFromArray(UrlRewrite::DB_TBL, $seoUrlKeyword, false, array(), array('urlrewrite_custom'=>$customUrl));
                /*]*/

                /* save options data, if any [ */
                if (!$sellerProdObj->addUpdateSellerProductOptions($selprod_id, $options)) {
                    Message::addErrorMessage($sellerProdObj->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
                /*]*/

                /* Seller product lang data [*/
                $sellerProdObj = new SellerProduct($selprod_id);
                foreach ($languages as $lang_id=>$langName) {
                    $reqLangData = ProductRequest::getAttributesByLangId($lang_id, $preqId);
                    if ($reqLangData == false) {
                        continue;
                    }

                    $arr = json_decode($reqLangData['preq_lang_data'], true);
                    if (!empty($arr)) {
                        $reqLangData = array_merge($reqLangData, json_decode($reqLangData['preq_lang_data'], true));
                    }
                    $selProdLangData = array(
                    'selprodlang_selprod_id'=>$selprod_id,
                    'selprodlang_lang_id'=>$lang_id,
                    'selprod_title'=>isset($reqLangData['selprod_title']) ? $reqLangData['selprod_title'] : isset($reqLangData['product_name']) ? $reqLangData['product_name'] : '','selprod_comments'=>isset($reqLangData['selprod_comments'])?$reqLangData['selprod_comments']:'',
                    );

                    if (!$sellerProdObj->updateLangData($lang_id, $selProdLangData)) {
                        Message::addErrorMessage($prodObj->getError());
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                } /* ]*/
            }

            /*[ Saving product Specifications */
            $prodSpecData = array();
            if (isset($data['preq_specifications'])) {
                $prodSpecData = json_decode($data['preq_specifications'], true);
            }

            if (!empty($prodSpecData)) {
                foreach ($prodSpecData['prod_spec_name'][CommonHelper::getLangId()] as $specKey=>$specval) {
                    $prodSpecObj = new ProdSpecification(0);
                    $languages = Language::getAllNames();
                    foreach ($languages as $langId=>$langName) {
                        $data_to_be_save['prodspec_product_id'] = $product_id;

                        $prodSpecObj->assignValues($data_to_be_save);

                        if (!$prodSpecObj->save()) {
                            Message::addErrorMessage(Labels::getLabel($prodSpecObj->getError(), $this->adminLangId));
                            FatUtility::dieWithError(Message::getHtml());
                        };
                        $prodSpecObj = new ProdSpecification($prodSpecObj->getMainTableRecordId());

                        $data_to_save_lang['prodspec_name'] = $prodSpecData['prod_spec_name'][$langId][$specKey];
                        $data_to_save_lang['prodspec_value'] = $prodSpecData['prod_spec_value'][$langId][$specKey];
                        $data_to_save_lang['prodspeclang_lang_id'] = $langId;
                        if (!$prodSpecObj->updateLangData($langId, $data_to_save_lang)) {
                            Message::addErrorMessage(Labels::getLabel($ProdSpecObj->getError(), $this->adminLangId));
                            FatUtility::dieWithError(Message::getHtml());
                        }
                    }
                }
            }
            /*]*/
        }

        $email = new EmailHandler();
        $customCatalogReq = array();
        $customCatalogReq = $data;
        $customCatalogReq['preq_status'] = $post['preq_status'];
        $customCatalogReq['preq_comment'] = $post['preq_comment'];
        if (!$email->sendCustomCatalogRequestStatusChangeNotification($this->adminLangId, $customCatalogReq)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('MSG_Email_could_not_be_Sent', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        Product::updateMinPrices($product_id);
        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('MSG_Status_updated_successfully', $this->adminLangId));
        $this->set('preq_id', $preqId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customEanUpcForm($preqId = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility::int($preqId);
        $upcCodeData = array();

        /* Validate product request belongs to current logged seller[ */
        if ($preqId) {
            $productReqRow = ProductRequest::getAttributesById($preqId);
            if ($productReqRow == false) {
                Message::addErrorMessage($this->str_invalid_request);
                FatUtility::dieWithError(Message::getHtml());
            }
            $prodcat_id    = $productReqRow['preq_prodcat_id'];
            $upcCodeData = json_decode($productReqRow['preq_ean_upc_code'], true);
        }
        /* ] */

        $productOptions =  ProductRequest::getProductReqOptions($preqId, $this->adminLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');

        $this->set('productOptions', $productOptions);
        $this->set('optionCombinations', $optionCombinations);
        $this->set('upcCodeData', $upcCodeData);
        $this->set('preqId', $preqId);
        $this->set('preqCatId', $prodcat_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function validateUpcCode()
    {
        $post = FatApp::getPostedData();
        if (empty($post) || $post['code'] == '') {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_code', '=', $post['code']);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $totalRecords = FatApp::getDb()->totalRecords($rs);
        if ($totalRecords > 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_UPC/EAN_code_already_assigned_to_another_product', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupEanUpcCode($preqId)
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $preqId = FatUtility::int($preqId);

        /* Validate product request belongs to current logged seller[ */
        if ($preqId) {
            $productReqRow = ProductRequest::getAttributesById($preqId);
            if ($productReqRow == false) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $prodcat_id    = $productReqRow['preq_prodcat_id'];
        }
        /* ] */

        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        unset($post['btn_submit']);
        unset($post['fOutMode']);
        unset($post['fIsAjax']);
        $prodReqObj = new ProductRequest($preqId);
        $data = array(
        'preq_ean_upc_code'=> str_replace('code', '', FatUtility::convertToJson($post))
        );

        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            FatUtility::dieWithError($prodReqObj->getError());
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('preq_id', $preqId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function loadCustomProductTags()
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $post = FatApp::getPostedData();
        if (empty($post['tags'])) {
            return false;
        }

        $srch = Tag::getSearchObject();
        $srch->addOrder('tag_identifier');
        $srch->joinTable(
            Tag::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->adminLangId
        );
        $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));
        $srch->addCondition('tag_id', 'IN', $post['tags']);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $tags = $db->fetchAll($rs, 'tag_id');
        $li = '';
        foreach ($tags as $key => $tag) {
            $li .= '<li id="product-tag' . $tag['tag_id'] . '"><span class="left "><a href="javascript:void(0)" title="Remove" onClick="removeProductTag('.$tag['tag_id'].');"><i class="icon ion-close remove_tag-js" data-tag-id="' . $tag['tag_id'] . '"></i></a></span>';
            $li .= '<span class="left">' . $tag['tag_name'].' ('.$tag['tag_identifier'].')'.'<input type="hidden" value="'.$tag['tag_id'].'"  name="product_tags[]"></span></li>';
        }
        echo $li;
        exit;
    }

    public function loadCustomProductOptionss()
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $post = FatApp::getPostedData();
        if (empty($post['options'])) {
            return false;
        }

        $srch = Option::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('option_id, option_name, option_identifier'));
        $srch->addCondition('option_id', 'IN', $post['options']);
        $srch->addOrder('option_identifier');

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = $db->fetchAll($rs, 'option_id');
        $li = '';
        foreach ($options as $key => $option) {
            $li .= '<li id="product-option' . $option['option_id'] . '"><span class="left" ><a href="javascript:void(0)" title="Remove" onClick="removeProductOption('.$option['option_id'].');"><i class="icon ion-close" data-option-id="' . $option['option_id'] . '"></i></a></span>';
            $li .= '<span class="left">' . $option['option_name'].' ('.$option['option_identifier'].')'.'<input type="hidden" value="'.$option['option_id'].'"  name="product_option[]"></span></li>';
        }

        echo $li;
        exit;
    }

    public function getShippingTab()
    {
        $shipping_rates = array();
        $post = FatApp::getPostedData();
        $preq_id = $post['preq_id'];

        $shipping_rates = array();
        $productReqData = ProductRequest::getAttributesById($preq_id, array('preq_id','preq_user_id'));
        $shipping_rates = ProductRequest::getProductShippingRates($preq_id, $this->adminLangId, 0, $productReqData['preq_user_id']);
        /* $shipping_rates = array();
        $productReqData = ProductRequest::getAttributesById($preq_id);
        $productReqData = json_decode($productReqData['preq_content'],true);
        $shipping_rates = !(empty($productReqData['product_shipping']))?$productReqData['product_shipping']:array(); */
        $this->set('adminLangId', $this->adminLangId);
        $this->set('product_id', $preq_id);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false, 'products/get-shipping-tab.php');
    }

    public function imagesForm($preq_id)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preq_id = FatUtility::int($preq_id);
        if (!$preq_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        if (!$row = ProductRequest::getAttributesById($preq_id)) {
            FatUtility::dieWithError($this->str_no_record);
        }
        $imagesFrm = $this->getImagesFrm($preq_id, $this->adminLangId);
        $this->set('preq_id', $preq_id);
        $this->set('imagesFrm', $imagesFrm);
        $this->_template->render(false, false);
    }

    public function images($preq_id, $option_id=0, $lang_id=0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preq_id = FatUtility::int($preq_id);
        if (!$preq_id) {
            Message::addErrorMessage($this->str_invalid_request);
        }

        if (!$row = ProductRequest::getAttributesById($preq_id)) {
            Message::addErrorMessage($this->str_no_record);
        }
        $product_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $lang_id, false, 0, 0, true);
        $imgTypesArr = $this->getSeparateImageOptions($preq_id, $this->adminLangId);

        $this->set('images', $product_images);
        $this->set('preq_id', $preq_id);
        $this->set('imgTypesArr', $imgTypesArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setImageOrder()
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $preqObj = new ProductRequest();
        $post = FatApp::getPostedData();
        $preq_id = FatUtility::int($post['preq_id']);
        $imageIds=explode('-', $post['ids']);
        $count=1;
        foreach ($imageIds as $row) {
            $order[$count]=$row;
            $count++;
        }
        if (!$preqObj->updateProdImagesOrder($preq_id, $order)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set("msg", Labels::getLabel('LBL_Ordered_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadProductImages()
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $preq_id = FatUtility::int($post['preq_id']);
        $option_id = FatUtility::int($post['option_id']);
        $lang_id = FatUtility::int($post['lang_id']);

        if (!is_uploaded_file($_FILES['prod_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['prod_image']['tmp_name'], AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $_FILES['prod_image']['name'], -1, $unique_record = false, $lang_id)
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set("msg", Labels::getLabel('LBL_Image_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteImage($preq_id, $image_id)
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $preq_id = FatUtility :: int($preq_id);
        $image_id = FatUtility :: int($image_id);
        if (!$image_id || !$preq_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $preqObj = new ProductRequest();
        if (!$preqObj->deleteProductImage($preq_id, $image_id)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set("msg", Labels::getLabel('LBL_Image_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getImagesFrm($preq_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $imgTypesArr = $this->getSeparateImageOptions($preq_id, $lang_id);
        $frm = new Form('imageFrm', array('id' => 'imageFrm'));
        $frm->addSelectBox(Labels::getLabel('LBL_Image_File_Type', $this->adminLangId), 'option_id', $imgTypesArr, 0, array(), '');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', array( 0 => Labels::getLabel('LBL_All_Languages', $this->adminLangId) ) + $languagesAssocArr, '', array(), '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s):', $this->adminLangId), 'prod_image', array('id' => 'prod_image', 'multiple' => 'multiple'));
        $fldImg->htmlBeforeField='<div class="filefield"><span class="filename"></span>';
        $fldImg->htmlAfterField='<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $this->adminLangId).'</label></div><br/><small>'.Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_500_x_500._You_can_upload_multiple_photos_from_here.', $this->adminLangId).'</small>';
        $frm->addHiddenField('', 'preq_id', $preq_id);
        return $frm;
    }

    private function getSeparateImageOptions($preq_id, $lang_id)
    {
        $imgTypesArr = array( 0 => Labels::getLabel('LBL_For_All_Options', $this->adminLangId) );

        if ($preq_id) {
            $reqData = ProductRequest::getAttributesById($preq_id, array('preq_content'));
            if (!empty($reqData)) {
                $reqData = json_decode($reqData['preq_content'], true);
            }
            $productOptions =  isset($reqData['product_option'])?$reqData['product_option']:array();
            if (!empty($productOptions)) {
                foreach ($productOptions as $optionId) {
                    $optionData = Option::getAttributesById($optionId, array('option_is_separate_images'));

                    if (!$optionData || !$optionData['option_is_separate_images']) {
                        continue;
                    }

                    $optionValues = Product::getOptionValues($optionId, $lang_id);
                    if (!empty($optionValues)) {
                        foreach ($optionValues as $k => $v) {
                            $imgTypesArr[$k] = $v;
                        }
                    }
                }
            }
        }
        return $imgTypesArr;
    }

    private function getForm($attrgrp_id = 0)
    {
        return $this->getProductCatalogForm($attrgrp_id, 'REQUESTED_CATALOG_PRODUCT');
    }

    private function getLangForm($preqId, $langId)
    {
        $frm = new Form('frmCustomProductLang');
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addHiddenField('', 'lang_id', $langId);
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $this->adminLangId), 'product_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Seller_Product_Title', $this->adminLangId), 'selprod_title');
        $frm->addTextBox(Labels::getLabel('LBL_Any_extra_comment_for_buyer', $this->adminLangId), 'selprod_comments');
        $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $this->adminLangId), 'product_description');
        $frm->addTextBox(Labels::getLabel('LBL_YouTube_Video', $this->adminLangId), 'product_youtube_video');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getStatusForm()
    {
        $frm = new Form('frmUpdateStatus');

        $statusArr = ProductRequest::getStatusArr($this->adminLangId);
        unset($statusArr[ProductRequest::STATUS_PENDING]);
        $frm->addSelectBox(Labels::getLabel('LBL_Select_Status', $this->adminLangId), 'preq_status', $statusArr, '', array(), 'Select')->requirements()->setRequired();

        $frm->addCheckbox(Labels::getLabel('LBL_Move_seller_data_along_with_catalog_request_data', $this->adminLangId), 'preq_update_withselprod', 1, array(), true, 0);
        $frm->addHiddenField('', 'preq_id');
        $frm->addTextArea('', 'preq_comment', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function catalogCustomProductRequestSearchForm()
    {
        $frm = new Form('frmCustomProdReqSrch');
        $frm->addTextBox('Keyword', 'keyword', '');

        $statusArr = array('-1'=>Labels::getLabel('LBL_All', $this->adminLangId))+ProductRequest::getStatusArr($this->adminLangId);
        $frm->addSelectBox('Status', 'status', $statusArr, '', array(), '');
        $frm->addDateField('Date From', 'date_from', '', array( 'readonly'=>'readonly', 'class'=>'field--calender' ));
        $frm->addDateField('Date To', 'date_to', '', array( 'readonly'=>'readonly', 'class'=>'field--calender' ));
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', 'Search');
        $fld_cancel = $frm->addButton("", "btn_clear", "Clear Search", array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
