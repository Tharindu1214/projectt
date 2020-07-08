<?php
class MetaTagsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewMetaTags($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditMetaTags($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewMetaTags();
        $tabsArr = MetaTag::getTabsArr();
        $this->set('tabsArr', $tabsArr);
        $this->set('activeTab', 0);
        $this->_template->render();
    }

    public function listMetaTags($metaType)
    {
        $tabsArr = MetaTag::getTabsArr();
        $metaType = FatUtility::convertToType($metaType, FatUtility::VAR_STRING);
        $metaType = ($metaType == '')?array_keys($tabsArr)[0] :$metaType ;
        $searchForm = $this->getSearchForm($metaType);
        $canAddNew = false;
        $toShowForm = true;
        if(in_array($metaType, array(MetaTag::META_GROUP_DEFAULT, MetaTag::META_GROUP_ALL_PRODUCTS, MetaTag::META_GROUP_ALL_SHOPS, MetaTag::META_GROUP_ALL_BRANDS, MetaTag::META_GROUP_BLOG_PAGE))) {
            $toShowForm = false;
        }

        if(in_array($metaType, array( MetaTag::META_GROUP_ADVANCED))) {
            $canAddNew = true;
        }
        $this->set('metaTypeDefault', MetaTag::META_GROUP_DEFAULT);
        $this->set('toShowForm', $toShowForm);
        $this->set('canAddNew', $canAddNew);
        $this->set('metaType', $metaType);
        $this->set('frmSearch', $searchForm);
        $this->_template->render(false, false);
    }

    public function search()
    {
        $this->objPrivilege->canViewMetaTags();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $metaType = FatApp::getPostedData('metaType', FatUtility::VAR_STRING);
        $this->set('metaType', $metaType);
        switch($metaType)
        {
        case MetaTag::META_GROUP_DEFAULT :
            $this->renderTemplateForDefaultMetaTag();
            break;
        case MetaTag::META_GROUP_PRODUCT_DETAIL :
            $this->renderTemplateForProductDetail();
            break;
        case MetaTag::META_GROUP_SHOP_DETAIL :
            $this->renderTemplateForShopDetail();
            break;
        case MetaTag::META_GROUP_ADVANCED :
            $this->renderTemplateForAdvanced();
            break;
        case MetaTag::META_GROUP_CMS_PAGE :
            $this->renderTemplateForCMSPage();
            break;
        case MetaTag::META_GROUP_BRAND_DETAIL :
            $this->renderTemplateForBrandDetail();
            break;
        case MetaTag::META_GROUP_CATEGORY_DETAIL :
            $this->renderTemplateForCategoryDetail();
            break;
        case MetaTag::META_GROUP_BLOG_CATEGORY :
            $this->renderTemplateForBlogCategory();
            break;
        case MetaTag::META_GROUP_BLOG_POST :
            $this->renderTemplateForBlogPost();
            break;
        default :
            $this->renderTemplateForMetaType();
            break;
        }
    }

    public function form($metaId = 0 , $metaType = 'default', $recordId = 0)
    {
        $this->objPrivilege->canViewMetaTags();
        if (0 < $metaId ) {
            $data = MetaTag::getAttributesById($metaId);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            if(empty($metaType)) {
                $tabsArr = MetaTag::getTabsArr();
                foreach($tabsArr as $key => $value)
                {
                    if($value['controller'] == $data['meta_controller'] && $value['action'] == $data['meta_action']) {
                        $metaType = $key;
                        break;
                    }
                }
            }
        }
        $frm = $this->getForm($metaId, $metaType, $recordId);

        if (0 < $metaId ) {
            $frm->fill($data);
        }

        $this->set('frm', $frm);
        $this->set('recordId', $recordId);
        $this->set('metaId', $metaId);
        $this->set('metaType', $metaType);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditMetaTags();

        $post = FatApp::getPostedData();

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $metaId = FatUtility::int($post['meta_id']);

        $tabsArr = MetaTag::getTabsArr();
        $metaType = FatUtility::convertToType($post['meta_type'], FatUtility::VAR_STRING);

        if($metaType == '' || !isset($tabsArr[$metaType]) ) {
            Message::addErrorMessage($this->str_invalid_access);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getForm($metaId, $metaType, $post['meta_record_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if($metaType ==MetaTag::META_GROUP_ADVANCED) {
            $post['meta_advanced'] = 1;
        }else if($metaType ==MetaTag::META_GROUP_DEFAULT) {
            $post['meta_default'] = 1;
        }
        else{
            $post['meta_controller'] = $tabsArr[$metaType]['controller'];
            $post['meta_action'] = $tabsArr[$metaType]['action'];
            if($metaId == 0) {
                $post['meta_subrecord_id'] = 0;
            }
        }

        $record = new MetaTag($metaId);

        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        if($metaId>0) {
            $languages = Language::getAllNames();
            foreach($languages as $langId =>$langName ){
                if(!$row = MetaTag::getAttributesByLangId($langId, $metaId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        }else{
            $metaId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('metaId', $metaId);
        $this->set('metaType', $metaType);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($metaId = 0,$lang_id = 0 , $metaType='default')
    {
        $this->objPrivilege->canViewMetaTags();

        $metaId = FatUtility::int($metaId);
        $lang_id = FatUtility::int($lang_id);

        if($metaId == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request_id);
        }

        $langFrm = $this->getLangForm($metaId, $lang_id);

        if(!$data = MetaTag::getAttributesById($metaId)) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $recordId = FatUtility::int($data['meta_record_id']);

        $langData = MetaTag::getAttributesByLangId($lang_id, $metaId);

        if($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('metaId', $metaId);
        $this->set('recordId', $recordId);
        $this->set('metaType', $metaType);
        $this->set('lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditMetaTags();
        $post = FatApp::getPostedData();

        $metaId = $post['meta_id'];
        $lang_id = $post['lang_id'];

        if($metaId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        /* echo strip_tags($post['meta_other_meta_tags']); die; */

        if(!$post['meta_other_meta_tags']=='' && $post['meta_other_meta_tags'] == strip_tags($post['meta_other_meta_tags'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Other_Meta_Tag', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($metaId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['meta_id']);
        unset($post['lang_id']);

        $data = array(
        'metalang_lang_id'=>$lang_id,
        'metalang_meta_id'=>$metaId,
        'meta_title'=>$post['meta_title'],
        'meta_keywords'=>$post['meta_keywords'],
        'meta_description'=>$post['meta_description'],
        'meta_other_meta_tags'=>$post['meta_other_meta_tags'],
        );

        $metaObj = new MetaTag($metaId);

        if(!$metaObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($metaObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach($languages as $langId =>$langName ){
            if(!$row = MetaTag::getAttributesByLangId($langId, $metaId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('metaId', $metaId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditMetaTags();

        $metaId = FatApp::getPostedData('metaId', FatUtility::VAR_INT, 0);
        if($metaId < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new MetaTag($metaId);
        if(!$obj->deleteRecord(true) ) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    private function getSearchFormForMetaType($metaType)
    {

        $frm = new Form('frmSearch');
        $frm->addHiddenField(Labels::getLabel('LBL_Type', $this->adminLangId), 'metaType', $metaType);
        return $frm;
    }

    private function getAdvancedSearchForm($metaType)
    {

        $frm = new Form('frmSearch');
        $frm->addHiddenField(Labels::getLabel('LBL_Type', $this->adminLangId), 'metaType', $metaType);
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getListingSearchForm($metaType)
    {

        $frm = new Form('frmSearch');
        $frm->addHiddenField(Labels::getLabel('LBL_Type', $this->adminLangId), 'metaType', $metaType);
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addSelectBox(Labels::getLabel('LBL_Has_Tags_Associated', $this->adminLangId), 'hasTagsAssociated', applicationConstants::getYesNoArr($this->adminLangId), false, array(), Labels::getLabel('LBL_Doesn\'t_Matter', $this->adminLangId));
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getSearchForm($metaType)
    {

        switch($metaType)
        {
        case MetaTag::META_GROUP_PRODUCT_DETAIL :
        case MetaTag::META_GROUP_SHOP_DETAIL :
        case MetaTag::META_GROUP_CMS_PAGE :
        case MetaTag::META_GROUP_BRAND_DETAIL :
        case MetaTag::META_GROUP_CATEGORY_DETAIL :
        case MetaTag::META_GROUP_BLOG_POST :
        case MetaTag::META_GROUP_BLOG_CATEGORY :
            return $this->getListingSearchForm($metaType);
         break;
        case MetaTag::META_GROUP_ADVANCED :
            return $this->getAdvancedSearchForm($metaType);
         break;
        default:
            return $this->getSearchFormForMetaType($metaType);
         break;
        }

        return false;
    }

    private function getForm($metaTagId = 0 , $metaType = 'default' , $recordId = 0)
    {
        $this->objPrivilege->canViewMetaTags();
        $metaTagId = FatUtility::int($metaTagId);
        $frm = new Form('frmMetaTag');
        $frm->addHiddenField('', 'meta_id', $metaTagId);
        $tabsArr = MetaTag::getTabsArr();
        $frm->addHiddenField('', 'meta_type', $metaType);

        if($metaTagId!= 0 && ($metaType == '' || !isset($tabsArr[$metaType])) ) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if($metaType == MetaTag::META_GROUP_ADVANCED) {
            $fld = $frm->addRequiredField(Labels::getLabel('LBL_Controller', $this->adminLangId), 'meta_controller');
            $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Ex:_If_URL_is", $this->adminLangId)." http://domain-name.com/shops/report-spam/1/10 ". Labels::getLabel("LBL_then_controller_will_be_", $this->adminLangId) ." shops</small>";
            $fld = $frm->addRequiredField(Labels::getLabel('LBL_Action', $this->adminLangId), 'meta_action');
            $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Ex:_If_URL_is", $this->adminLangId)." http://domain-name.com/shops/report-spam/1/10 ". Labels::getLabel("LBL_then_action_will_be_", $this->adminLangId) ." reportSpam</small>";
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Record_Id', $this->adminLangId), 'meta_record_id');
            $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Ex:_If_URL_is", $this->adminLangId)." http://domain-name.com/shops/report-spam/1/10 ". Labels::getLabel("LBL_then_record_id_will_be_", $this->adminLangId) ." 1</small>";
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Sub_Record_Id', $this->adminLangId), 'meta_subrecord_id');
            $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Ex:_If_URL_is", $this->adminLangId)." http://domain-name.com/shops/report-spam/1/10 ". Labels::getLabel("LBL_then_sub_record_id_will_be_", $this->adminLangId) ." 10</small>";
        }
        else{
            $frm->addHiddenField(Labels::getLabel('LBL_Entity_Id', $this->adminLangId), 'meta_record_id', $recordId);
        }
        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'meta_identifier');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($metaId = 0,$lang_id = 0)
    {
        $this->objPrivilege->canViewMetaTags();
        $frm = new Form('frmMetaTagLang');
        $frm->addHiddenField('', 'meta_id', $metaId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Meta_Title', $this->adminLangId), 'meta_title');
        $frm->addTextarea(Labels::getLabel('LBL_Meta_Keywords', $this->adminLangId), 'meta_keywords')->requirements()->setRequired(true);
        $frm->addTextarea(Labels::getLabel('LBL_Meta_Description', $this->adminLangId), 'meta_description')->requirements()->setRequired(true);
        $fld = $frm->addTextarea(Labels::getLabel('LBL_Other_Meta_Tags', $this->adminLangId), 'meta_other_meta_tags');
        $fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_For_Example:', $this->adminLangId).' '.htmlspecialchars('<meta name="copyright" content="text">').'</small>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function renderTemplateForBlogPost()
    {
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);

        $srch = BlogPost::getSearchObject($this->adminLangId, true, true);

        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', "mt.meta_record_id = bp.post_id and mt.meta_controller = '$controller' and mt.meta_action = '$action' ", 'mt');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', "mt_l.metalang_meta_id = mt.meta_id AND mt_l.metalang_lang_id = ".$this->adminLangId, 'mt_l');
        $srch->addCondition('post_deleted', '=', applicationConstants::NO);
        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('bp_l.post_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('bp.post_identifier', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if(isset($post['hasTagsAssociated']) && $post['hasTagsAssociated']!= '') {
            if($post['hasTagsAssociated'] == applicationConstants::YES) {
                $srch->addCondition('mt.meta_id', 'is not', 'mysql_func_NULL', 'AND', true);
            }
            elseif($post['hasTagsAssociated'] == applicationConstants::NO) {
                $srch->addCondition('mt.meta_id', 'is', 'mysql_func_NULL', 'AND', true);
            }
        }

        $srch->addMultipleFields(array('meta_id','meta_identifier','meta_title','post_id','IF(post_title is NULL or post_title = "" ,post_identifier, post_title) as post_title'));
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addGroupBy('post_id');
        $rs = $srch->getResultSet();
        $records = array();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $this->_template->render(false, false, 'meta-tags/blog-post.php');
    }

    private function renderTemplateForBlogCategory()
    {
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);

        $srch = BlogPostCategory::getSearchObject(false, $this->adminLangId, false);

        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', "mt.meta_record_id = bpc.bpcategory_id and mt.meta_controller = '$controller' and mt.meta_action = '$action' ", 'mt');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', "mt_l.metalang_meta_id = mt.meta_id AND mt_l.metalang_lang_id = ".$this->adminLangId, 'mt_l');

        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('bpc_l.bpcategory_name', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('bpc.bpcategory_identifier', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if(isset($post['hasTagsAssociated']) && $post['hasTagsAssociated']!= '') {
            if($post['hasTagsAssociated'] == applicationConstants::YES) {
                $srch->addCondition('mt.meta_id', 'is not', 'mysql_func_NULL', 'AND', true);
            }
            elseif($post['hasTagsAssociated'] == applicationConstants::NO) {
                $srch->addCondition('mt.meta_id', 'is', 'mysql_func_NULL', 'AND', true);
            }
        }

        $srch->addMultipleFields(array('meta_id','meta_identifier','meta_title','bpcategory_id','IF(bpcategory_name is NULL or bpcategory_name = "" ,bpcategory_identifier,bpcategory_name) as bpcategory_name'));
        $srch->addCondition('bpcategory_deleted', '=', applicationConstants::NO);
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/blog-category.php');
    }

    private function renderTemplateForProductDetail()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);
        $srch = SellerProduct::getSearchObject($this->adminLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$this->adminLangId, 'p_l');
        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', "mt.meta_record_id = sp.selprod_id and mt.meta_controller = '$controller' and mt.meta_action = '$action' ", 'mt');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', "mt_l.metalang_meta_id = mt.meta_id AND mt_l.metalang_lang_id = ".$this->adminLangId, 'mt_l');
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('p_l.product_name', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('sp_l.selprod_title', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if(isset($post['hasTagsAssociated']) && $post['hasTagsAssociated']!= '') {
            if($post['hasTagsAssociated'] == applicationConstants::YES) {
                $srch->addCondition('mt.meta_id', 'is not', 'mysql_func_NULL', 'AND', true);
            }
            elseif($post['hasTagsAssociated'] == applicationConstants::NO) {
                $srch->addCondition('mt.meta_id', 'is', 'mysql_func_NULL', 'AND', true);
            }
        }

        $srch->addMultipleFields(array('meta_id','meta_identifier','meta_title','selprod_id','IF(selprod_title is NULL or selprod_title = "" ,product_name, selprod_title) as selprod_title'));
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = array();
        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $this->_template->render(false, false, 'meta-tags/product-detail.php');
    }

    private function renderTemplateForShopDetail()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);
        $srch = Shop::getSearchObject(false, $this->adminLangId);
        $srch->joinTable('tbl_users', 'INNER JOIN', 'u.user_id = s.shop_user_id', 'u');
        $srch->joinTable('tbl_user_credentials', 'INNER JOIN', 'u.user_id = c.credential_user_id', 'c');
        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', "mt.meta_record_id = s.shop_id and mt.meta_controller = '$controller' and mt.meta_action = '$action' ", 'mt');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', "mt_l.metalang_meta_id = mt.meta_id AND mt_l.metalang_lang_id = ".$this->adminLangId, 'mt_l');

        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('s_l.shop_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if(isset($post['hasTagsAssociated']) && $post['hasTagsAssociated']!= '') {
            if($post['hasTagsAssociated'] == applicationConstants::YES) {
                $srch->addCondition('mt.meta_id', 'is not', 'mysql_func_NULL', 'AND', true);
            }
            elseif($post['hasTagsAssociated'] == applicationConstants::NO) {
                $srch->addCondition('mt.meta_id', 'is', 'mysql_func_NULL', 'AND', true);
            }
        }

        $srch->addMultipleFields(array('meta_id','meta_identifier','meta_title','shop_id','IFNULL(s_l.shop_name, s.shop_identifier) as shop_name'));
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();

        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/shop-detail.php');
    }

    private function renderTemplateForCMSPage()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);
        $srch = ContentPage::getSearchObject($this->adminLangId);

        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', "mt.meta_record_id = p.cpage_id and mt.meta_controller = '$controller' and mt.meta_action = '$action' ", 'mt');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', "mt_l.metalang_meta_id = mt.meta_id AND mt_l.metalang_lang_id = ".$this->adminLangId, 'mt_l');

        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('p_l.cpage_title', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if(isset($post['hasTagsAssociated']) && $post['hasTagsAssociated']!= '') {
            if($post['hasTagsAssociated'] == applicationConstants::YES) {
                $srch->addCondition('mt.meta_id', 'is not', 'mysql_func_NULL', 'AND', true);
            }
            elseif($post['hasTagsAssociated'] == applicationConstants::NO) {
                $srch->addCondition('mt.meta_id', 'is', 'mysql_func_NULL', 'AND', true);
            }
        }

        $srch->addMultipleFields(array('meta_id','meta_identifier','meta_title','cpage_id','IF(cpage_title is NULL or cpage_title = "" ,cpage_identifier, cpage_title) as cpage_title'));
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();

        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/cpage-detail.php');
    }

    private function renderTemplateForBrandDetail()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);
        $srch = Brand::getSearchObject($this->adminLangId);

        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', "mt.meta_record_id = b.brand_id and mt.meta_controller = '$controller' and mt.meta_action = '$action' ", 'mt');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', "mt_l.metalang_meta_id = mt.meta_id AND mt_l.metalang_lang_id = ".$this->adminLangId, 'mt_l');

        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('b_l.brand_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if(isset($post['hasTagsAssociated']) && $post['hasTagsAssociated']!= '') {
            if($post['hasTagsAssociated'] == applicationConstants::YES) {
                $srch->addCondition('mt.meta_id', 'is not', 'mysql_func_NULL', 'AND', true);
            }
            elseif($post['hasTagsAssociated'] == applicationConstants::NO) {
                $srch->addCondition('mt.meta_id', 'is', 'mysql_func_NULL', 'AND', true);
            }
        }

        $srch->addMultipleFields(array('meta_id','meta_identifier','meta_title','brand_id','IF(brand_name is NULL or brand_name = "" ,brand_identifier, brand_name) as brand_name'));
        $srch->addCondition('brand_status', '=', Brand::BRAND_REQUEST_APPROVED);
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();

        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/brand-detail.php');
    }

    private function renderTemplateForCategoryDetail()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);
        $srch = ProductCategory::getSearchObject(false, $this->adminLangId, false);

        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', "mt.meta_record_id = m.prodcat_id and mt.meta_controller = '$controller' and mt.meta_action = '$action' ", 'mt');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', "mt_l.metalang_meta_id = mt.meta_id AND mt_l.metalang_lang_id = ".$this->adminLangId, 'mt_l');

        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
            $condition->attachCondition('pc_l.prodcat_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        if(isset($post['hasTagsAssociated']) && $post['hasTagsAssociated']!= '') {
            if($post['hasTagsAssociated'] == applicationConstants::YES) {
                $srch->addCondition('mt.meta_id', 'is not', 'mysql_func_NULL', 'AND', true);
            }
            elseif($post['hasTagsAssociated'] == applicationConstants::NO) {
                $srch->addCondition('mt.meta_id', 'is', 'mysql_func_NULL', 'AND', true);
            }
        }

        $srch->addMultipleFields(array('meta_id','meta_identifier','meta_title','prodcat_id','IF(prodcat_name is NULL or prodcat_name = "" ,prodcat_identifier, prodcat_name) as prodcat_name'));
        $srch->addOrder('meta_id', 'DESC');
        $srch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();

        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/category-detail.php');
    }

    private function renderTemplateForAdvanced()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = new MetaTagSearch($this->adminLangId);
        $srch->addCondition('mt.meta_advanced', '=', 1);
        $srch->addFld('mt.* , mt_l.meta_title');
        if(!empty($post['keyword'])) {
            $condition=$srch->addCondition('mt.meta_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('mt_l.meta_title', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('meta_id', 'DESC');
        $rs = $srch->getResultSet();
        $records = array();

        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/default-template.php');
    }

    private function renderTemplateForDefaultMetaTag()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = new MetaTagSearch($this->adminLangId);
        $srch->addCondition('mt.meta_controller', '=', '');
        $srch->addCondition('mt.meta_action', '=', '');
        $srch->addCondition('mt.meta_record_id', '=', 0);
        $srch->addCondition('mt.meta_subrecord_id', '=', 0);
        $srch->addCondition('mt.meta_default', '=', 1);
        $srch->addFld('mt.* , mt_l.meta_title');
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = array();

        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/default-meta-tag.php');
    }
    private function renderTemplateForMetaType()
    {

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $searchForm = $this->getSearchForm($data['metaType']);
        $post = $searchForm->getFormDataFromArray($data);

        $metaType = FatUtility::convertToType($post['metaType'], FatUtility::VAR_STRING);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $tabsArr = MetaTag::getTabsArr();
        $controller = FatUtility::convertToType($tabsArr[$metaType]['controller'], FatUtility::VAR_STRING);
        $action = FatUtility::convertToType($tabsArr[$metaType]['action'], FatUtility::VAR_STRING);

        $srch = new MetaTagSearch($this->adminLangId);
        $srch->addFld('mt.* , mt_l.meta_title');
        $srch->addCondition('mt.meta_controller', 'like', $controller);
        $srch->addCondition('mt.meta_action', 'like', $action);

        $srch->addFld('mt.* , mt_l.meta_title');
        $srch->addOrder('meta_id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = array();
        if($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'meta-tags/default-template.php');
    }
}
