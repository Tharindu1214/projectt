<?php
class CategoryRequestsController extends AdminBaseController
{
    private $canView;
    private $canEdit;    
    
    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if(!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        } 
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCategoryRequests($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCategoryRequests($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index()
    {
        $this->objPrivilege->canViewCategoryRequests();
        $frmSearch = $this->getSearchForm();                    
        $this->set("frmSearch", $frmSearch);    
        $this->_template->render();
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmCategoryReqSearch', array('id'=>'frmcategoryReqSearch'));        
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class'=>'search-input'));
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearCategoryRequestSearch();'));
        $fld_submit->attachField($fld_cancel);        
        return $frm;
    }
    
    public function search()
    {
        $this->objPrivilege->canViewCategoryRequests();
        
        $pagesize=FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);                
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        /* $categoryReqObj = new CategoryRequest(); */
        $srch = CategoryRequest::getSearchObject($this->adminLangId);
        $srch->addFld('cat.*');
        
        if(!empty($post['keyword'])) {
            $srch->addCondition('cat.scategoryreq_identifier', 'like', '%'.$post['keyword'].'%');
        }
        
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        
        $srch->addMultipleFields(array("cat_l.scategoryreq_name"));
        
        $rs = $srch->getResultSet();
        $records =array();
        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);            
        }
        $statusArr = CategoryRequest::getCategoryReqStatusArr($this->adminLangId);
        $this->set('statusArr', $statusArr);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);                        
        $this->_template->render(false, false);    
    }
    
    public function form($categoryReqId=0)
    {
        $this->objPrivilege->canEditCategoryRequests();
        $statusArr = CategoryRequest::getCategoryReqStatusArr($this->adminLangId);
        $categoryReqId=FatUtility::int($categoryReqId);
        $frm = $this->getForm($categoryReqId);
        
        if (0 < $categoryReqId ) {
            $data = CategoryRequest::getAttributesById($categoryReqId, array('scategoryreq_id','scategoryreq_identifier','scategoryreq_seller_id','scategoryreq_status'));            
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $data['status'] = $data['scategoryreq_status'];
            $frm->fill($data);
        }
    
        $this->set('languages', Language::getAllNames());
        $this->set('categoryReqId', $categoryReqId);
        $this->set('frmCategoryReq', $frm);
        $this->_template->render(false, false);
    }

    private function getForm($categoryReqId=0)
    {
        $this->objPrivilege->canEditCategoryRequests();        
        $categoryReqId=FatUtility::int($categoryReqId);
        $frm = new Form('frmcategoryReq', array('id'=>'frmCategoryReq'));        
        $frm->addHiddenField('', 'scategoryreq_id', $categoryReqId);
        $frm->addHiddenField('', 'scategoryreq_seller_id', $categoryReqId);
        $frm->addRequiredField(Labels::getLabel('LBL_Category_Request_Identifier', $this->adminLangId), 'scategoryreq_identifier');$statusArr = CategoryRequest::getCategoryReqStatusArr($this->adminLangId);
        unset($statusArr[CategoryRequest::CATEGORY_REQUEST_PENDING]);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'status', $statusArr, '')->requirements()->setRequired();
        $frm->addTextArea('', 'comments', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;        
    }

    public function setup()
    {
        $this->objPrivilege->canEditCategoryRequests();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {            
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }

        $categoryReqId = $post['scategoryreq_id'];
        unset($post['scategoryreq_id']);
        
        $creqObj = new CategoryRequest();
        $sCategoryRequest = $creqObj->getAttributesById($categoryReqId);
        
        if($sCategoryRequest==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $statusArr = array(CategoryRequest::CATEGORY_REQUEST_APPROVED,CategoryRequest::CATEGORY_REQUEST_CANCELLED); 
        if(!in_array($post['status'], $statusArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Status_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $db = FatApp::getDb();
        $db->startTransaction();
        if (in_array($post['status'], $statusArr)) {
            $post['request_id'] = $categoryReqId ;
            if(!$creqObj->updateCategoryRequest($post)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($creqObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        
        $email = new EmailHandler();        
        $sCategoryRequest['scategoryreq_status'] = $post['status'];
        $sCategoryRequest['scategoryreq_comments'] = $post['comments'];
        
        if(!$email->SendCategoryRequestStatusChangeNotification($this->adminLangId, $sCategoryRequest)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('LBL_Email_Could_Not_Be_Sent', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('LBL_Status_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function langForm($categoryReqId=0,$lang_id=0)
    {
        $this->objPrivilege->canEditCategoryRequests();
        
        $categoryReqId = FatUtility::int($categoryReqId);
        $lang_id = FatUtility::int($lang_id);
        
        if($categoryReqId==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        
        $categoryReqLangFrm = $this->getLangForm($categoryReqId, $lang_id);
            
        $langData = CategoryRequest::getAttributesByLangId($lang_id, $categoryReqId);        
        
        if($langData) {
            $categoryReqLangFrm->fill($langData);            
        }
        
        $this->set('languages', Language::getAllNames());
        $this->set('categoryReqId', $categoryReqId);
        $this->set('scategoryreq_lang_id', $lang_id);
        $this->set('categoryReqLangFrm', $categoryReqLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);    
    }
    
    /* public function langSetup(){
    $this->objPrivilege->canEditCategoryRequests();
    $post=FatApp::getPostedData();
		
    $categoryReqId = $post['scategoryreq_id'];
    $lang_id = $post['lang_id'];
		
    if($categoryReqId == 0 || $lang_id == 0){
    Message::addErrorMessage($this->str_invalid_request_id);
    FatUtility::dieWithError( Message::getHtml() );
    }
		
    $frm = $this->getLangForm($categoryReqId,$lang_id);
    $post = $frm->getFormDataFromArray(FatApp::getPostedData());
    unset($post['categoryReqId']);
    unset($post['lang_id']);
    $data = array(
    'scategoryreqlang_lang_id'=>$lang_id,
    'scategoryreqlang_scategoryreq_id'=>$categoryReqId,
    'scategoryreq_name'=>$post['scategoryreq_name'],
    );
		
    $categoryReqObj=new CategoryRequest($categoryReqId);	
    if(!$categoryReqObj->updateLangData($lang_id,$data)){
			
    Message::addErrorMessage($categoryReqObj->getError());
    FatUtility::dieJsonError( Message::getHtml() );					
    }

    $newTabLangId=0;
    $languages=Language::getAllNames();	
    foreach($languages as $langId =>$langName ){
    if(!$row=CategoryRequest::getAttributesByLangId($langId,$categoryReqId)){
				$newTabLangId = $langId;
				break;
    }
    }
		
    $this->set('msg', Labels::getLabel('LBL_Category_Request_Setup_Successful',$this->adminLangId));
    $this->set('categoryReqId', $categoryReqId);
    $this->set('langId', $newTabLangId);
    $this->_template->render(false, false, 'json-success.php');
    } */
    
    private function getLangForm($categoryReqId=0,$lang_id=0)
    {
        $frm = new Form('frmCategoryReqLang', array('id'=>'frmCategoryReqLang'));        
        $frm->addHiddenField('', 'scategoryreq_id', $categoryReqId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Category_Request_Name', $this->adminLangId), 'scategoryreq_name');        
        /* $frm->addSubmitButton('', 'btn_submit',Labels::getLabel('LBL_Update',$this->adminLangId)); */
        return $frm;
    }
    
    public function deleteRecord()
    {
        $this->objPrivilege->canEditCategoryRequests();
        
        $categoryReqId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if($categoryReqId < 1 ) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $categoryReqObj = new CategoryRequest($categoryReqId);
        if(!$categoryReqObj->canRecordDelete($categoryReqId) ) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        
        if(!$categoryReqObj->deleteRecord(true) ) {
            Message::addErrorMessage($categoryReqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        FatUtility::dieJsonSuccess($this->str_delete_record);    
    }
    
    function autoComplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $this->objPrivilege->canViewCategoryRequests();
        
        $srch = CategoryRequests::getSearchObject();
        $srch->addOrder('categoryReqIdentifier');
        $srch->joinTable(
            CategoryRequests::DB_TBL . '_lang', 'LEFT OUTER JOIN',
            'scategoryreqlang_categoryReqId = categoryReqId AND scategoryreqlang_lang_id = ' . FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)
        );
        $srch->addMultipleFields(array('categoryReqId, scategoryreq_name, categoryReqIdentifier'));        
        
        if (!empty($post['keyword']) ) {
            $cnd = $srch->addCondition('scategoryreq_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('categoryReqIdentifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }
        
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = array();
        if($rs) {
            $options = $db->fetchAll($rs, 'categoryReqId');
        }
        $json = array();
        foreach( $options as $key => $option ){
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($option['scategoryreq_name'], ENT_QUOTES, 'UTF-8')),
            'categoryReqIdentifier'    => strip_tags(html_entity_decode($option['categoryReqIdentifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

}
