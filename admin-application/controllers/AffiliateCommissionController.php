<?php
class AffiliateCommissionController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewAffiliateCommissionSettings($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditAffiliateCommissionSettings($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewAffiliateCommissionSettings();
        $srchFrm = $this->getSearchForm();
        $this->set("frmSearch", $srchFrm);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewAffiliateCommissionSettings();
        $srchFrm = $this->getSearchForm();

        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = AffiliateCommission::getSearchObject($this->adminLangId);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'afcs.afcommsetting_user_id = affiliate_user.user_id', 'affiliate_user');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'affiliate_cred.credential_user_id = affiliate_user.user_id', 'affiliate_cred');

        $srch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'prod_cat.prodcat_id = afcs.afcommsetting_prodcat_id', 'prod_cat');
        $srch->joinTable(ProductCategory::DB_LANG_TBL, 'LEFT OUTER JOIN', 'prod_cat.prodcat_id = prd_cat_l.prodcatlang_prodcat_id AND prd_cat_l.prodcatlang_lang_id = '.$this->adminLangId, 'prd_cat_l');

        $srch->addMultipleFields(array( 'afcs.*', 'affiliate_cred.credential_username', 'IFNULL(prd_cat_l.prodcat_name, prod_cat.prodcat_identifier) as prodcat_name' ));
        $srch->addOrder('afcommsetting_is_mandatory', 'DESC');
        $srch->addOrder('afcommsetting_fees', 'DESC');
        $srch->addOrder('afcommsetting_id', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('affiliate_cred.credential_username', 'like', '%'.$post['keyword'].'%', 'AND');
            $cond->attachCondition('prodcat_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('postedData', $post);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->_template->render(false, false);
    }

    public function form($afcommsetting_id)
    {
        $afcommsetting_id = FatUtility::int($afcommsetting_id);
        $frm = $this->getForm($afcommsetting_id);

        if ($afcommsetting_id > 0) {
            $data = AffiliateCommission::getAttributesById(
                $afcommsetting_id,
                array( 'afcommsetting_id', 'afcommsetting_prodcat_id', 'afcommsetting_user_id', 'afcommsetting_fees' )
            );
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            if ($data['afcommsetting_user_id'] > 0) {
                $userObj = new User($data['afcommsetting_user_id']);
                $userData = $userObj->getUserInfo();
                $data['affiliate_name'] = isset($userData['credential_username']) ? $userData['credential_username'] : $userData['user_name'];
            }
            $frm->fill($data);
        }

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditAffiliateCommissionSettings();
        $afcommsetting_id = FatApp::getPostedData('afcommsetting_id', FatUtility::VAR_INT, 0);
        $frm = $this->getForm($afcommsetting_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post['afcommsetting_prodcat_id'] = FatApp::getPostedData('afcommsetting_prodcat_id', FatUtility::VAR_INT, 0);

        /* $afcommsetting_user_id = FatApp::getPostedData( 'afcommsetting_user_id', FatUtility::VAR_INT, 0 );
        if( $afcommsetting_user_id <= 0 ){
        Message::addErrorMessage( Labels::getLabel("LBL_Please_select_Affiliate_from_autosuggest", $this->adminLangId) );
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        $afcommsetting_id = FatApp::getPostedData('afcommsetting_id', FatUtility::VAR_INT, 0);
        $isMandatory = false;
        if ($afcommsetting_id > 0) {
            $data = AffiliateCommission::getAttributesById($afcommsetting_id, array( 'afcommsetting_is_mandatory' ));
            if ($data['afcommsetting_is_mandatory']) {
                $isMandatory = true;
            }
        }

        if ($isMandatory) {
            $post['afcommsetting_prodcat_id'] = 0;
            $post['afcommsetting_user_id'] = 0;
        }

        if ($post['afcommsetting_id'] == 0) {
            $srch = AffiliateCommission::getSearchObject($this->adminLangId);
            $srch->addCondition('afcs.afcommsetting_user_id', '=', $post['afcommsetting_user_id']);
            $srch->addCondition('afcs.afcommsetting_prodcat_id', '=', $post['afcommsetting_prodcat_id']);
            $rs = $srch->getResultSet();
            $records = FatApp::getDb()->fetchAll($rs);
            if ($records) {
                Message::addErrorMessage(Labels::getLabel('MSG_Record_already_exists', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $affCommSetObj = new AffiliateCommission($afcommsetting_id);

        $affCommSetObj->assignValues($post);
        if (!$affCommSetObj->save()) {
            Message::addErrorMessage($affCommSetObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $recordId = $affCommSetObj->getMainTableRecordId();
        if (!$recordId) {
            $recordId = FatApp::getDb()->getInsertId();
        }

        if (!$affCommSetObj->addAffiliateCommissionHistory($recordId)) {
            Message::addErrorMessage($affCommSetObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewHistory($afcommsetting_id = 0)
    {
        $this->objPrivilege->canViewAffiliateCommissionSettings();
        $afcommsetting_id = FatUtility::int($afcommsetting_id);
        if (1 > $afcommsetting_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0)?1:$post['page'];
        $page = (empty($page) || $page <= 0)?1:FatUtility::int($page);

        $srch = AffiliateCommission::getAffiliateCommissionHistoryObj($this->adminLangId);
        $srch->addCondition('tacsh.acsh_afcommsetting_id', '=', $afcommsetting_id);
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
        $this->objPrivilege->canEditAffiliateCommissionSettings();

        $afcommsetting_id = FatApp::getPostedData('afcommsetting_id', FatUtility::VAR_INT, 0);
        if ($afcommsetting_id < 1) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($afcommsetting_id);

        $this->set('msg', Labels::getLabel("LBL_Record_deleted_successfully", $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditAffiliateCommissionSettings();
        $afcommsettingIdsArr = FatUtility::int(FatApp::getPostedData('afcommsetting_ids'));

        if (empty($afcommsettingIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($afcommsettingIdsArr as $afcommsettingId) {
            if (1 > $afcommsettingId) {
                continue;
            }
            $this->markAsDeleted($afcommsettingId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($afcommsettingId)
    {
        $afcommsettingId = FatUtility::int($afcommsettingId);
        if (1 > $afcommsettingId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $row =  AffiliateCommission::getAttributesById($afcommsettingId, array('afcommsetting_id', 'afcommsetting_is_mandatory'));
        if ($row == false) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if ($row['afcommsetting_is_mandatory']) {
            Message::addErrorMessage(Labels::getLabel("LBL_Default_record_cannot_be_deleted", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatApp::getDb()->deleteRecords(AffiliateCommission::DB_TBL, array( 'smt' => 'afcommsetting_id = ?', 'vals' => array( $afcommsettingId )));
    }

    private function getForm($afcommsetting_id = 0)
    {
        $this->objPrivilege->canViewAffiliateCommissionSettings();
        $afcommsetting_id =  FatUtility::int($afcommsetting_id);

        $frm = new Form('frmAffiliateCommission');
        $frm->addHiddenField('', 'afcommsetting_id', $afcommsetting_id);
        $isMandatory = false;
        if ($afcommsetting_id > 0) {
            $data = AffiliateCommission::getAttributesById($afcommsetting_id, array( 'afcommsetting_is_mandatory' ));
            $isMandatory = $data['afcommsetting_is_mandatory'];
        }

        if (!$isMandatory) {
            $prodCatObj = new ProductCategory();
            $arrCategories = $prodCatObj->getCategoriesForSelectBox($this->adminLangId);
            $categories = $prodCatObj->makeAssociativeArray($arrCategories);
            $frm->addSelectBox(Labels::getLabel('LBL_Category', $this->adminLangId), 'afcommsetting_prodcat_id', array( '' => 'Does not Matter' ) + $categories, '', array(), '');

            $fld = $frm->addTextBox(Labels::getLabel('LBL_Affiliate_Name', $this->adminLangId), 'affiliate_name');

            $fld = $frm->addHiddenField(Labels::getLabel('LBL_Affiliate_Name', $this->adminLangId), 'afcommsetting_user_id');
        }

        $frm->addFloatField(Labels::getLabel('LBL_Affiliate_Commission_fees', $this->adminLangId), 'afcommsetting_fees');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }


    private function getSearchForm()
    {
        $this->objPrivilege->canViewAffiliateCommissionSettings();
        $frm = new Form('frmAffiliateCommissionSearch');
        $frm->addHiddenField('', 'page');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
