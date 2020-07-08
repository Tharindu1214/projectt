<?php
class OrderStatusController extends AdminBaseController
{
    private $canView;
    private $canEdit;


    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewOrderStatus($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOrderStatus($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewOrderStatus();
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $orderStatusTypeArr = OrderStatus::getOrderStatusTypeArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_type', $this->adminLangId), 'orderstatus_type', $orderStatusTypeArr, '', array(), '');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
    public function search()
    {
        $this->objPrivilege->canViewOrderStatus();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = OrderStatus::getSearchObject(false, $this->adminLangId);

        $srch->addFld(array('ostatus.*','IFNULL(ostatus_l.orderstatus_name,ostatus.orderstatus_identifier) as orderstatus_name'));

        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('ostatus.orderstatus_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('ostatus_l.orderstatus_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        $orderStatusTypeArr = OrderStatus::getOrderStatusTypeArr($this->adminLangId);

        $orderstatus_type = FatApp::getPostedData('orderstatus_type', FatUtility::VAR_INT, -1);
        if ($orderstatus_type > 0) {
            $srch->addCondition('ostatus.orderstatus_type', '=', $orderstatus_type);
        } else {
            $srch->addCondition('ostatus.orderstatus_type', '=', Orders::ORDER_PRODUCT);
        }
        $srch->addOrder('ostatus.orderstatus_priority');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function form($orderStatusId)
    {
        $this->objPrivilege->canEditOrderStatus();

        $orderStatusId =  FatUtility::int($orderStatusId);

        $frm = $this->getForm($orderStatusId);

        if (0 < $orderStatusId) {
            $data = OrderStatus::getAttributesById($orderStatusId, array('orderstatus_id','orderstatus_identifier','orderstatus_is_active','orderstatus_is_digital','orderstatus_color_code'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('orderstatus_id', $orderStatusId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditOrderStatus();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderStatusId = $post['orderstatus_id'];
        unset($post['orderstatus_id']);

        $record = new OrderStatus($orderStatusId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record-getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($orderStatusId>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = OrderStatus::getAttributesByLangId($langId, $orderStatusId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $orderStatusId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->set('orderStatusId', $orderStatusId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($orderStatusId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewOrderStatus();

        $orderStatusId = FatUtility::int($orderStatusId);
        $lang_id = FatUtility::int($lang_id);

        if ($orderStatusId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($orderStatusId, $lang_id);

        $langData = OrderStatus::getAttributesByLangId($lang_id, $orderStatusId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('orderStatusId', $orderStatusId);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditOrderStatus();
        $post = FatApp::getPostedData();

        $orderStatusId = $post['orderstatus_id'];
        $lang_id = $post['orderstatuslang_lang_id'];

        if ($orderStatusId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($orderStatusId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['orderstatus_id']);
        unset($post['lang_id']);

        $data = array(
        'orderstatuslang_lang_id'=>$lang_id,
        'orderstatuslang_orderstatus_id'=>$orderStatusId,
        'orderstatus_name'=>$post['orderstatus_name']
        );

        $orderstatusObj = new OrderStatus($orderStatusId);

        if (!$orderstatusObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($orderstatusObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = OrderStatus::getAttributesByLangId($langId, $orderStatusId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('orderStatusId', $orderStatusId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($orderStatusId = 0)
    {
        $this->objPrivilege->canViewOrderStatus();
        $orderStatusId =  FatUtility::int($orderStatusId);

        $frm = new Form('frmorderstatus');
        $frm->addHiddenField('', 'orderstatus_id', $orderStatusId);
        $frm->addRequiredField(Labels::getLabel('LBL_Order_Status_Identifier', $this->adminLangId), 'orderstatus_identifier');
        $frm->addRequiredField(Labels::getLabel('LBL_Order_Status_Color_Code', $this->adminLangId), 'orderstatus_color_code');

        $orderStatusTypeArr = OrderStatus::getOrderStatusTypeArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_type', $this->adminLangId), 'orderstatus_type', $orderStatusTypeArr, '', array(), '');

        $yesNoArr = applicationConstants::getYesNoArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_is_Digital', $this->adminLangId), 'orderstatus_is_digital', $yesNoArr, '', array(), '');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'orderstatus_is_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($orderStatusId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewOrderStatus();
        $frm = new Form('frmorderstatuslang');
        $frm->addHiddenField('', 'orderstatus_id', $orderStatusId);
        $frm->addHiddenField('', 'orderstatuslang_lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_orderstatus_Name', $this->adminLangId), 'orderstatus_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditOrderStatus();
        $orderStatusId = FatApp::getPostedData('orderStatusId', FatUtility::VAR_INT, 0);
        if (0 >= $orderStatusId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = OrderStatus::getAttributesById($orderStatusId, array('orderstatus_is_active'));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['orderstatus_is_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateOrderStatus($orderStatusId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditOrderStatus();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $orderStatusIdsArr = FatUtility::int(FatApp::getPostedData('orderstatus_ids'));
        if (empty($orderStatusIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($orderStatusIdsArr as $orderStatusId) {
            if (1 > $orderStatusId) {
                continue;
            }

            $this->updateOrderStatus($orderStatusId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateOrderStatus($orderStatusId, $status)
    {
        $status = FatUtility::int($status);
        $orderStatusId = FatUtility::int($orderStatusId);
        if (1 > $orderStatusId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $orderstatusObj = new OrderStatus($orderStatusId);
        $data['orderstatus_is_active'] = $status;
        $orderstatusObj->assignValues($data);
        if (!$orderstatusObj->save()) {
            Message::addErrorMessage($orderstatusObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function setOrderStatusesOrder()
    {
        $this->objPrivilege->canEditOrderStatus();
        $post=FatApp::getPostedData();
        if (!empty($post)) {
            $obj = new OrderStatus();
            if (!$obj->updateOrder($post['orderStatuses'])) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            $this->set('msg', Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
    }
}
