<?php
class NotificationsController extends AdminBaseController
{
    public function __construct($action) 
    {
        parent::__construct($action);
        $this->objPrivilege->canViewNotifications();
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
    }

    public function index()
    {
        $this->canEdit = $this->objPrivilege->canEditNotifications($this->admin_id, true);
        $this->set("canEdit", $this->canEdit);
        $this->_template->render();
    }

    public function search()
    {
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2 ) {
            $page = 1;
        }

        $srch = Notification::getSearchObject();

        if(!AdminPrivilege::isAdminSuperAdmin($this->admin_id)) {
            $recordTypeArr = Notification::getAllowedRecordTypeArr($this->admin_id);
            $srch->addCondition('notification_record_type', 'IN', $recordTypeArr);
        }

        $srch->addOrder('n.notification_added_on', 'DESC');
        $srch->addCondition('n.'.Notification::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set('labelArr', Notification::getLabelKeyString($this->adminLangId));
        $this->set('arr_listing', $records);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('recordCount', $srch->recordCount());
        $this->canEdit = $this->objPrivilege->canEditNotifications($this->admin_id, true);
        $this->set("canEdit", $this->canEdit);
        $this->_template->render(false, false);
    }

    public function deleteRecords()
    {
        $this->objPrivilege->canEditNotifications();

        $notificationIds = FatApp::getPostedData('record_ids');

        $obj = new Notification();

        if(!$obj->deleteNotifications($notificationIds)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditNotifications();

        $notificationIds = FatApp::getPostedData('record_ids');
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);
        $markread = FatApp::getPostedData('markread', FatUtility::VAR_INT, 0);
        $obj = new Notification();

        if(!$obj->changeNotifyStatus($status, $notificationIds)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        if($markread!=1) {
            $this->set('msg', $this->str_update_record);
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function notificationList()
    {

        $srch = Notification::getSearchObject();

        if(!AdminPrivilege::isAdminSuperAdmin($this->admin_id)) {
            $recordTypeArr = Notification::getAllowedRecordTypeArr($this->admin_id);
            $srch->addCondition('notification_record_type', 'IN', $recordTypeArr);
        }

        $srch->addOrder('n.notification_added_on', 'DESC');
        $srch->addCondition('n.notification_deleted', '=', applicationConstants::NO);
        $srch->addCondition('n.notification_marked_read', '=', applicationConstants::NO);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set('labelArr', Notification::getLabelKeyString($this->adminLangId));
        $this->set('arr_listing', $records);
        $this->_template->render(false, false);
    }

}
?>
