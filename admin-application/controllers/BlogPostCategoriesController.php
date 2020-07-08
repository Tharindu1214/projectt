<?php
class BlogPostCategoriesController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup','updateOrder');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die(Labels::getLabel('MSG_Invalid_Action', $this->adminLangId));
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewBlogPostCategories($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditBlogPostCategories($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($parent = 0)
    {
        $this->objPrivilege->canViewBlogPostCategories();
        $parent = FatUtility::int($parent);
        $bpCatData = BlogPostCategory::getAttributesById($parent);
        $bpCatObj = new BlogPostCategory();
        $category_structure = $bpCatObj->getCategoryStructure($parent);

        $search = $this->getSearchForm();
        $data = array(
        'bpcategory_parent'=>$parent
        );
        $search->fill($data);
        $this->set("search", $search);

        $this->set("bpcategory_parent", $parent);
        $this->set("bpCatData", $bpCatData);
        $this->set("category_structure", $category_structure);
        $this->_template->render();
    }

    public function search()
    {
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $post = $searchForm->getFormDataFromArray($data);
        $parent = FatApp::getPostedData('bpcategory_parent', FatUtility::VAR_INT, 0);
        $srch = BlogPostCategory::getSearchObject(true, $this->adminLangId, false);
        $srch->addCondition('bpc.bpcategory_parent', '=', $parent);

        $srch->addOrder('bpc.bpcategory_display_order', 'asc');
        $srch->addFld('bpc.*');

        if (!empty($post['keyword'])) {
            $keywordCond =  $srch->addCondition('bpc.bpcategory_identifier', 'like', '%'.$post['keyword'].'%');
            $keywordCond->attachCondition('bpc_l.bpcategory_name', 'like', '%'.$post['keyword'].'%');
        }
        $parentCatData = BlogPostCategory::getAttributesById($parent);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $srch->addMultipleFields(array("bpcategory_name"));
        $rs = $srch->getResultSet();
        $pageCount = $srch->pages();

        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $pageCount);
        $this->set('parentData', $parentCatData);
        $this->set('postedData', $post);

        $this->_template->render(false, false);
    }

    public function form($bpcategory_id = 0, $bpcategory_parent = 0)
    {
        $this->objPrivilege->canEditBlogPostCategories();
        $bpcategory_id = FatUtility::int($bpcategory_id);
        $bpcategory_parent = FatUtility::int($bpcategory_parent);
        $frm = $this->getForm($bpcategory_id);

        if (0 < $bpcategory_id) {
            $data = BlogPostCategory::getAttributesById($bpcategory_id);
            if ($data === false) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }

            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', 'blog/category/'.$bpcategory_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            /* ] */

            $frm->fill($data);
        } else {
            $data=array('bpcategory_parent'=>$bpcategory_parent);
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('bpcategory_id', $bpcategory_id);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function langForm($catId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditBlogPostCategories();

        $bpcategory_id = FatUtility::int($catId);
        $lang_id = FatUtility::int($lang_id);

        if ($bpcategory_id==0 || $lang_id==0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $langFrm = $this->getLangForm($bpcategory_id, $lang_id);
        $langData = BlogPostCategory::getAttributesByLangId($lang_id, $bpcategory_id);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('bpcategory_id', $bpcategory_id);
        $this->set('bpcategory_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditBlogPostCategories();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $bpcategory_id = FatUtility::int($post['bpcategory_id']);
        $bpcategory_parent = FatUtility::int($post['bpcategory_parent']);
        unset($post['bpcategory_id']);
        $record = new BlogPostCategory($bpcategory_id);

        if ($bpcategory_id==0) {
            $display_order=$record->getMaxOrder($bpcategory_parent);
            $post['bpcategory_display_order']=$display_order;
        }
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $bpcategory_id = $record->getMainTableRecordId();
        /* url data[ */
        $blogOriginalUrl = BlogPostCategory::REWRITE_URL_PREFIX.$bpcategory_id;
        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($blogOriginalUrl)));
        } else {
            $record->rewriteUrl($post['urlrewrite_custom'], true, $bpcategory_parent);
        }
        /* ] */

        $newTabLangId=0;
        if ($bpcategory_id>0) {
            $catId=$bpcategory_id;
            $languages=Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row=BlogPostCategory::getAttributesByLangId($langId, $bpcategory_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $catId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', Labels::getLabel('MSG_Category_Setup_Successful', $this->adminLangId));
        $this->set('catId', $catId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditBlogPostCategories();
        $post=FatApp::getPostedData();

        $bpcategory_id = $post['bpcategory_id'];
        $lang_id = $post['lang_id'];

        if ($bpcategory_id==0 || $lang_id==0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($bpcategory_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['bpcategory_id']);
        unset($post['lang_id']);
        $data = array(
        'bpcategorylang_lang_id'=>$lang_id,
        'bpcategorylang_bpcategory_id'=>$bpcategory_id,
        'bpcategory_name'=>$post['bpcategory_name'],

        );

        $bpCatObj=new BlogPostCategory($bpcategory_id);
        if (!$bpCatObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($bpCatObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row=BlogPostCategory::getAttributesByLangId($langId, $bpcategory_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('MSG_Category_Setup_Successful', $this->adminLangId));
        $this->set('catId', $bpcategory_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function change_status()
    {
        if (!FatUtility::isAjaxCall()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        if ($this->canEdit === false) {
            FatUtility::dieJsonError($this->unAuthorizeAccess);
        }

        $bpcategory_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($bpcategory_id < 1) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request_ID', $this->adminLangId));
        }

        $bpCatObj = new BlogPostCategory($bpcategory_id);
        if (!$row = $bpCatObj->canUpdateRecordStatus($bpcategory_id)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request_ID', $this->adminLangId));
        } else {
            $bpCatObj->assignValues(array(BlogPostCategory::tblFld('active') => ($row['bpcategory_active'] != applicationConstants::ACTIVE ? applicationConstants::ACTIVE : applicationConstants::INACTIVE)));
            if ($bpCatObj->save()) {
                FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Setup_Updated_Successfully', $this->adminLangId));
            } else {
                FatUtility::dieJsonError($bpCatObj->getError());
            }
        }
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditBlogPostCategories();

        $bpcategory_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($bpcategory_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->markAsDeleted($bpcategory_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditBlogPostCategories();
        $bpcategoryIdsArr = FatUtility::int(FatApp::getPostedData('bpcategory_ids'));

        if (empty($bpcategoryIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($bpcategoryIdsArr as $bpcategoryId) {
            if (1 > $bpcategoryId) {
                continue;
            }
            $this->markAsDeleted($bpcategoryId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($bpcategoryId)
    {
        $bpcategoryId = FatUtility::int($bpcategoryId);
        if (1 > $bpcategoryId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $bpCatObj = new BlogPostCategory($bpcategoryId);
        if (!$bpCatObj->canMarkRecordDelete($bpcategoryId)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $bpCatObj->assignValues(array(BlogPostCategory::tblFld('deleted') => 1));
        if (!$bpCatObj->save()) {
            Message::addErrorMessage($bpCatObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditBlogPostCategories();

        $post=FatApp::getPostedData();
        if (!empty($post)) {
            $bpCatObj = new BlogPostCategory();
            if (!$bpCatObj->updateOrder($post['bpcategory'])) {
                Message::addErrorMessage($bpCatObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();
        switch ($action) {
            case 'index':
                $nodes[] = array('title'=>Labels::getLabel('LBL_Root_categories', $this->adminLangId), 'href'=>CommonHelper::generateUrl('BlogPostCategories'));
                if (isset($parameters[0]) && $parameters[0] > 0) {
                    $parent=FatUtility::int($parameters[0]);
                    if ($parent>0) {
                        $cntInc=1;
                        $bpCatObj =new BlogPostCategory();
                        $category_structure=$bpCatObj->getCategoryStructure($parent);
                        foreach ($category_structure as $catKey => $catVal) {
                            if ($cntInc<count($category_structure)) {
                                $nodes[] = array('title'=>$catVal["bpcategory_identifier"], 'href'=>CommonHelper::generateUrl('BlogPostCategories', 'index', array($catVal['bpcategory_id'])));
                            } else {
                                $nodes[] = array('title'=>$catVal["bpcategory_identifier"]);
                            }
                            $cntInc++;
                        }
                    }
                }
                break;

            case 'form':
                break;
        }
        return $nodes;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditBlogPostCategories();
        $bpcategoryId = FatApp::getPostedData('bpcategoryId', FatUtility::VAR_INT, 0);
        if (0 >= $bpcategoryId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = BlogPostCategory::getAttributesById($bpcategoryId, array( 'bpcategory_id', 'bpcategory_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['bpcategory_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateBlogPostCatStatus($bpcategoryId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }


    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditBlogPostCategories();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $bpcategoryIdsArr = FatUtility::int(FatApp::getPostedData('bpcategory_ids'));
        if (empty($bpcategoryIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($bpcategoryIdsArr as $bpcategoryId) {
            if (1 > $bpcategoryId) {
                continue;
            }

            $this->updateBlogPostCatStatus($bpcategoryId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateBlogPostCatStatus($bpcategoryId, $status)
    {
        $status = FatUtility::int($status);
        $bpcategoryId = FatUtility::int($bpcategoryId);
        if (1 > $bpcategoryId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new BlogPostCategory($bpcategoryId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function getForm($bpcategory_id = 0)
    {
        $bpcategory_id = FatUtility::int($bpcategory_id);
        $bpCatObj = new BlogPostCategory();
        $arrCategories = $bpCatObj->getCategoriesForSelectBox($this->adminLangId, $bpcategory_id);
        $categories = $bpCatObj->makeAssociativeArray($arrCategories);
        $frm = new Form('frmBlogPostCategory', array('id'=>'frmBlogPostCategory'));
        $frm->addHiddenField('', 'bpcategory_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Category_Identifier', $this->adminLangId), 'bpcategory_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Category_Parent', $this->adminLangId), 'bpcategory_parent', array(0=>Labels::getLabel('LBL_Root_Category', $this->adminLangId)) + $categories, '', array(), '');
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Category_Status', $this->adminLangId), 'bpcategory_active', $activeInactiveArr, '', array(), '');
        $frm->addCheckBox(Labels::getLabel('LBL_Featured', $this->adminLangId), 'bpcategory_featured', 1, array(), false, 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));

        return $frm;
    }

    private function getLangForm($bpcategory_id = 0, $lang_id = 0)
    {
        $bpcategory_id = FatUtility::int($bpcategory_id);

        $srch = BlogPostCategory::getSearchObject(true);
        $srch->addCondition('bpc.bpcategory_id', '=', $bpcategory_id);
        $srch->addCondition('bpc.bpcategory_parent', '=', 0);

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        $frm = new Form('frmBlogPostCatLang', array('id'=>'frmBlogPostCatLang'));
        $frm->addHiddenField('', 'bpcategory_id', $bpcategory_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Category_Name', $this->adminLangId), 'bpcategory_name');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));
        $frm->addHiddenField('', 'bpcategory_parent', 0, array('id'=>'bpcategory_parent'));
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class'=>'search-input'));

        $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
