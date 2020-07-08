<?php
class CommissionController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCommissionSettings($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCommissionSettings($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewCommissionSettings();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewCommissionSettings();

        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = Commission::getCommissionSettingsObj($this->adminLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('prodcat_identifier', 'like', '%'.$post['keyword'].'%', 'AND');
            $cond->attachCondition('tuc.credential_username', 'like', '%'.$post['keyword'].'%', 'OR');
            $cond->attachCondition('product_identifier', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        $srch->addOrder('commsetting_id', 'DESC');
        /* echo $srch->getQuery();die; */
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    public function form($commissionId)
    {
        $this->objPrivilege->canViewCommissionSettings();

        $commissionId =  FatUtility::int($commissionId);

        $frm = $this->getForm($commissionId);

        if (0 < $commissionId) {
            $data = Commission::getAttributesById(
                $commissionId,
                array('commsetting_id','commsetting_product_id','commsetting_user_id','commsetting_prodcat_id','commsetting_fees')
            );
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            if ($data['commsetting_user_id'] > 0) {
                $userObj = new User($data['commsetting_user_id']);
                $res = $userObj->getUserInfo();
                $data['user_name'] = isset($res['credential_username'])?$res['credential_username']:'';
            }

            if ($data['commsetting_product_id'] > 0) {
                $prodObj = Product::getSearchObject($this->adminLangId);
                $prodObj->addCondition('product_id', '=', $data['commsetting_product_id']);
                $prodObj->addMultipleFields(array('IFNULL(product_name,product_identifier) as product_name'));
                $rs = $prodObj->getResultSet();
                $db = FatApp::getDb();
                $row = $db->fetch($rs);
                $data['product'] = isset($row['product_name'])?$row['product_name']:'';
            }
            $frm->fill($data);
        }

        $this->set('commsetting_id', $commissionId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditCommissionSettings();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $commissionId = $post['commsetting_id'];
        unset($post['commsetting_id']);

        $isMandatory = false;
        if ($data = Commission::getAttributesById($commissionId, array('commsetting_is_mandatory'))) {
            $isMandatory = $data['commsetting_is_mandatory'];
        }

        if (false === $isMandatory && 1 > $commissionId && (empty($post['commsetting_prodcat_id']) && empty($post['commsetting_user_id']) && empty($post['commsetting_product_id']))) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_add_commission_corresponding_to_product,_category_or_user', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($isMandatory) {
            $post['commsetting_product_id'] = 0;
            $post['commsetting_user_id'] = 0;
            $post['commsetting_prodcat_id'] = 0;
        }

        $record = new Commission($commissionId);
        if (!$record->addUpdateData($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $insertId = $record->getMainTableRecordId();
        if (!$insertId) {
            $insertId = FatApp::getDb()->getInsertId();
        }

        if (!$record->addCommissionHistory($insertId)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('commissionId', $commissionId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewHistory($commsettingId = 0)
    {
        $this->objPrivilege->canViewCommissionSettings();
        $commsettingId = FatUtility::int($commsettingId);
        if (1 > $commsettingId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0)?1:$post['page'];
        $page = (empty($page) || $page <= 0)?1:FatUtility::int($page);

        $srch = Commission::getCommissionHistorySettingsObj($this->adminLangId);
        $srch->addCondition('tcsh.csh_commsetting_id', '=', $commsettingId);
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
        $this->_template->render(false, false);
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditCommissionSettings();

        $commissionId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($commissionId < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $row =  Commission::getAttributesById($commissionId, array('commsetting_id','commsetting_is_mandatory'));
        if ($row == false || ($row != false && $row['commsetting_is_mandatory'] == 1)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($commissionId);

        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditCommissionSettings();
        $commissionIdsArr = FatUtility::int(FatApp::getPostedData('commsetting_ids'));

        if (empty($commissionIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($commissionIdsArr as $commissionId) {
            if (1 > $commissionId) {
                continue;
            }
            $this->markAsDeleted($commissionId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($commissionId)
    {
        $commissionId = FatUtility::int($commissionId);
        if (1 > $commissionId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new Commission($commissionId);
        $obj->assignValues(array('commsetting_deleted' => 1));
        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function userAutoComplete()
    {
        $this->objPrivilege->canViewCommissionSettings();
        $userObj = new User();
        $srch = $userObj->getUserSearchObj(array('u.user_name','u.user_id','credential_username'));
        $srch->addCondition('user_is_supplier', '=', 1);

        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $srch->addCondition('u.user_name', 'LIKE', '%' . $post['keyword'] . '%')
                ->attachCondition('uc.credential_username', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $users = $db->fetchAll($rs, 'user_id');
        $json = array();
        foreach ($users as $key => $user) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($user['credential_username'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function productAutoComplete()
    {
        $this->objPrivilege->canViewCommissionSettings();

        $srch = Product::getSearchObject($this->adminLangId);

        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
        }

        // $srch->setPageSize(FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10));
        $srch->setPageSize(10);
        $srch->addMultipleFields(array('product_id','IFNULL(product_name,product_identifier) as product_name'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs, 'product_id');
        $json = array();
        foreach ($products as $key => $product) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($product['product_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getForm($commissionId = 0)
    {
        $this->objPrivilege->canViewCommissionSettings();
        $commissionId =  FatUtility::int($commissionId);
        $isMandatory = false;
        if ($data = Commission::getAttributesById($commissionId, array('commsetting_is_mandatory'))) {
            $isMandatory = $data['commsetting_is_mandatory'];
        }
        $frm = new Form('frmCommission');
        $frm->addHiddenField('', 'commsetting_id', $commissionId);

        if (!$isMandatory) {
            $prodCatObj = new ProductCategory();
            $arrCategories = $prodCatObj->getCategoriesForSelectBox($this->adminLangId);
            $categories = $prodCatObj->makeAssociativeArray($arrCategories);
            $frm->addSelectBox(Labels::getLabel('LBL_Category', $this->adminLangId), 'commsetting_prodcat_id', array( '' => 'Does not Matter' ) + $categories, '', array(), '');

            $frm->addTextBox(Labels::getLabel('LBL_Seller', $this->adminLangId), 'user_name');
            $frm->addTextBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'product');

            $frm->addHiddenField('', 'commsetting_user_id', 0);
            $frm->addHiddenField('', 'commsetting_product_id', 0);
        }

        $frm->addFloatField(Labels::getLabel('LBL_Commission_fees_(%)', $this->adminLangId), 'commsetting_fees');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getSearchForm()
    {
        $this->objPrivilege->canViewCommissionSettings();
        $frm = new Form('frmCommissionSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
