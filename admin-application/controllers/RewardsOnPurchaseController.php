<?php
class RewardsOnPurchaseController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die(Labels::getLabel('LBL_Invalid_Action', $this->adminLangId));
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewRewardsOnPurchase($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditRewardsOnPurchase($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewRewardsOnPurchase();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewRewardsOnPurchase();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();

        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = RewardsOnPurchase::getSearchObject();
        $srch->addOrder('rop_purchase_upto', 'asc');
        /* if(!empty($post['keyword'])){
        $cond = $srch->addCondition('sd.sduration_identifier','like','%'.$post['keyword'].'%','AND');
        $cond->attachCondition('sd_l.sduration_name','like','%'.$post['keyword'].'%','OR');
        $cond->attachCondition('msa.mshipapi_zip','like','%'.$post['keyword'].'%','OR');
        $cond->attachCondition('msa.mshipapi_cost','like','%'.$post['keyword'].'%','OR');
        $cond->attachCondition('msa.mshipapi_volume_upto','like','%'.$post['keyword'].'%','OR');
        $cond->attachCondition('msa.mshipapi_weight_upto','like','%'.$post['keyword'].'%','OR');
        }  */


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

    public function form($rop_id = 0)
    {
        $this->objPrivilege->canViewRewardsOnPurchase();

        $rop_id = FatUtility::int($rop_id);
        $frm = $this->getForm();

        if (0 < $rop_id) {
            $data = RewardsOnPurchase::getAttributesById($rop_id);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('rop_id', $rop_id);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditRewardsOnPurchase();

        $post = FatApp::getPostedData();

        $rop_id = 0;
        if (isset($post['rop_id'])) {
            $rop_id = FatUtility::int($post['rop_id']);
        }

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rop_id = $post['rop_id'];
        unset($post['rop_id']);

        $record = new RewardsOnPurchase($rop_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }


        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('ropId', $rop_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditRewardsOnPurchase();

        $rop_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($rop_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($rop_id);

        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditRewardsOnPurchase();
        $ropIdsArr = FatUtility::int(FatApp::getPostedData('rop_ids'));

        if (empty($ropIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($ropIdsArr as $ropId) {
            if (1 > $ropId) {
                continue;
            }
            $this->markAsDeleted($ropId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($ropId)
    {
        $ropId = FatUtility::int($ropId);
        if (1 > $ropId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new RewardsOnPurchase($ropId);

        $data = RewardsOnPurchase::getAttributesById($ropId, array('rop_id'));
        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$obj->deleteRecord(false)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }


    private function getSearchForm()
    {
        $frm = new Form('frmRewardsOnPurchase');

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getForm()
    {
        $this->objPrivilege->canViewRewardsOnPurchase();

        $frm = new Form('frmRewardsOnPurchase');
        $frm->addHiddenField('', 'rop_id', 0);
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Purchase_upto', $this->adminLangId), 'rop_purchase_upto');
        $fld->requirements()->setFloatPositive();
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Reward_Point', $this->adminLangId), 'rop_reward_point');
        $fld->requirements()->setFloatPositive();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
