<?php
class ProductTempImagesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->objPrivilege->canViewProductTempImages();
    }

    public function index()
    {
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        if($post == false ) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $srch = new ProductTempImageSearch();
        $srch->joinProduct();
        $srch->addMultipleFields(
            array('afile_id','afile_downloaded','afile_record_id', 'afile_physical_path',
            'afile_name', 'IFNULL(tp.product_identifier,tp_l.product_name) as product_name')
        );

        $srch->addOrder('af.' . ProductTempImage::DB_TBL_PREFIX . 'id', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        if(-1 < $post['is_downloaded'] ) {
            $srch->addCondition('af.afile_downloaded', '=', $post['is_downloaded']);
        }

        $keyword = FatApp::getPostedData('keyword', null, '');
        if(!empty($keyword) ) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        // echo $srch->getQuery();die;
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $this->canView = $this->objPrivilege->canViewProductTempImages($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditProductTempImages($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);

        $this->_template->render(false, false);
    }

    private function getSearchForm()
    {
        $frm = new Form('frmProductTempImages');

        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');

        $options = applicationConstants::getYesNoArr($this->adminLangId);
        $is_downloaded = array( -1 => Labels::getLabel('LBL_Does_not_matter', $this->adminLangId)) + $options;

        $frm->addSelectBox(Labels::getLabel('LBL_Is_Downloaded', $this->adminLangId), 'is_downloaded', $is_downloaded, -1, array(), '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        return $frm;
    }

    // Edit Form
    public function form( $afile_id )
    {
        $this->objPrivilege->canEditProductTempImages();

        if (1 > $afile_id ) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $afile_id = FatUtility::int($afile_id);
        $frmImage = $this->getForm($afile_id);

        $imageObj = new ProductTempImage($afile_id);
        $srch = $imageObj->getTempImageSearchObject();
        $srch->addMultipleFields(array('afile_id,afile_physical_path,afile_name'));
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs, 'afile_id');

        if ($data === false) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $frmImage->fill($data);

        $this->set('afile_id', $afile_id);
        $this->set('frmImage', $frmImage);
        $this->_template->render(false, false);
    }

    // Edit Form Structure
    private function getForm()
    {
        $frm = new Form('frmImage');
        $frm->addRequiredField(Labels::getLabel('LBL_File_Name', $this->adminLangId), 'afile_name', '');
        $frm->addRequiredField(Labels::getLabel('LBL_File_Path', $this->adminLangId), 'afile_physical_path');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function update()
    {
        $this->objPrivilege->canEditProductTempImages();

        $updateForm = $this->getForm();
        $data = FatApp::getPostedData();
        $post = $updateForm->getFormDataFromArray($data);

        if($post == false ) {
            Message::addErrorMessage(current($updateForm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $afile_id = FatApp::getPostedData('afile_id', FatUtility::VAR_INT, 0);
        if(1 > $afile_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $imageObj = new ProductTempImage($afile_id);
        $imageObj->assignValues($post);
        if (!$imageObj->save()) {
            Message::addErrorMessage($imageObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }
}
