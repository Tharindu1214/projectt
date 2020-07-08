<?php
class BlogPostsController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die(Labels::getLabel('MSG_Invalid_Action', $this->adminLangId));
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewBlogPosts($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditBlogPosts($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewBlogPosts();

        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->set('includeEditor', true);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewBlogPosts();

        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $post = $searchForm->getFormDataFromArray($data);
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srch = BlogPost::getSearchObject($this->adminLangId);

        if (!empty($post['keyword'])) {
            $keywordCond =  $srch->addCondition('bp.post_identifier', 'like', '%'.$post['keyword'].'%');
            $keywordCond->attachCondition('bp_l.post_title', 'like', '%'.$post['keyword'].'%');
        }

        if (isset($post['post_published']) && $post['post_published']!='') {
            $srch->addCondition('bp.post_published', '=', $post['post_published']);
        }
        $srch->addMultipleFields(array('*,ifnull(post_title,post_identifier) post_title , group_concat(ifnull(bpcategory_name ,bpcategory_identifier)) categories'));
        $srch->addGroupby('post_id');
        $srch->addOrder('post_id', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $pageCount = $srch->pages();

        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $this->_template->render(false, false);
    }

    public function form($post_id=0)
    {
        $this->objPrivilege->canEditBlogPosts();
        $post_id = FatUtility::int($post_id);

        $frm = $this->getForm($post_id);
        if (0 < $post_id) {
            $data = BlogPost::getAttributesById($post_id);
            if ($data === false) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }
            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', 'blog/post-detail/'.$post_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            /* ] */
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('post_id', $post_id);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function linksForm($post_id)
    {
        $this->objPrivilege->canViewBlogPosts();
        $lang_id = $this->adminLangId;
        $frm = $this->getLinksForm($post_id);
        $this->set('frmLinks', $frm);
        $this->set('post_id', $post_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function langForm($postId=0, $lang_id=0)
    {
        $this->objPrivilege->canEditBlogPosts();

        $postId = FatUtility::int($postId);
        $lang_id = FatUtility::int($lang_id);
        if ($postId==0 || $lang_id==0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $langFrm = $this->getLangForm($postId, $lang_id);
        $langData = BlogPost::getAttributesByLangId($lang_id, $postId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('post_id', $postId);
        $this->set('post_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditBlogPosts();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post_id = FatUtility::int($post['post_id']);
        unset($post['post_id']);

        if ($post_id == 0) {
            $post['post_added_on'] = date('Y-m-d H:i:s');
        }
        if ($post['post_published']) {
            $post['post_published_on'] = date('Y-m-d H:i:s');
        } else {
            $post['post_published_on'] = '';
        }
        $post['post_updated_on'] = date('Y-m-d H:i:s');

        $record = new BlogPost($post_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post_id = $record->getMainTableRecordId();
        /* url data[ */
        $blogOriginalUrl = BlogPost::REWRITE_URL_PREFIX.$post_id;
        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($blogOriginalUrl)));
        } else {
            $record->rewriteUrl($post['urlrewrite_custom']);
        }
        /* ] */

        $newTabLangId=0;
        if ($post_id>0) {
            $postId=$post_id;
            $languages=Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row=BlogPost::getAttributesByLangId($langId, $post_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $postId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $postObj = new BlogPost();
        $post_categories = $postObj->getPostCategories($post_id);
        $selectedCats= array();
        if (!$post_categories) {
            $this->set('openLinksForm', true);
        }
        $this->set('msg', Labels::getLabel('MSG_Blog_Post_Setup_Successful', $this->adminLangId));
        $this->set('postId', $postId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditBlogPosts();
        $post=FatApp::getPostedData();
        $post_id = $post['post_id'];
        $lang_id = $post['lang_id'];

        if ($post_id==0 || $lang_id==0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($post_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['post_id']);
        unset($post['lang_id']);
        $data = array(
        'postlang_lang_id'=>$lang_id,
        'postlang_post_id'=>$post_id,
        'post_title'=>$post['post_title'],
        'post_author_name'=>$post['post_author_name'],
        'post_short_description'=>$post['post_short_description'],
        'post_description'=>$post['post_description'],
        );

        $bpCatObj=new BlogPost($post_id);
        if (!$bpCatObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($bpCatObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row=BlogPost::getAttributesByLangId($langId, $post_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        if (!$newTabLangId) {
            if (!$post_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $post_id, 0, -1)) {
                $this->set('openImagesTab', true);
            }
        }
        $this->set('msg', Labels::getLabel('MSG_Blog_Post_Setup_Successful', $this->adminLangId));
        $this->set('postId', $post_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupCategories()
    {
        $this->objPrivilege->canEditBlogPosts();
        $post = FatApp::getPostedData();
        $frm = $this->getLinksForm($post['post_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post_id = $post['post_id'];
        unset($post['post_id']);

        if ($post_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $categories = $post['categories'];
        $prodObj = new BlogPost($post_id);

        /* link blog post to blog post categories[ */
        if (!$prodObj->addUpdateCategories($post_id, $categories)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */
        $newTabLangId=0;
        if ($post_id>0) {
            $postId=$post_id;
            $languages=Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row=BlogPost::getAttributesByLangId($langId, $post_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $postId = $record->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('postId', $post_id);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditBlogPosts();

        $post_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($post_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->markAsDeleted($post_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditBlogPosts();
        $postIdsArr = FatUtility::int(FatApp::getPostedData('post_ids'));

        if (empty($postIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($postIdsArr as $postId) {
            if (1 > $postId) {
                continue;
            }
            $this->markAsDeleted($postId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($postId)
    {
        $postId = FatUtility::int($postId);
        if (1 > $postId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new BlogPost($postId);
        if (!$obj->canMarkRecordDelete()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Unauthorized_Access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $obj->assignValues(array(BlogPost::tblFld('deleted') => 1));

        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }


    public function imagesForm($post_id)
    {
        $this->objPrivilege->canViewBlogPosts();
        $post_id = FatUtility::int($post_id);
        if (!$post_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        if (!$row = BlogPost::getAttributesById($post_id)) {
            FatUtility::dieWithError($this->str_no_record);
        }
        $imagesFrm = $this->getImagesFrm($post_id);
        $this->set('languages', Language::getAllNames());
        $this->set('post_id', $post_id);
        $this->set('imagesFrm', $imagesFrm);
        $this->_template->render(false, false);
    }

    public function images($post_id, $lang_id=0)
    {
        $this->objPrivilege->canViewBlogPosts();
        $post_id = FatUtility::int($post_id);
        if (!$post_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        if (!$row = BlogPost::getAttributesById($post_id)) {
            FatUtility::dieWithError($this->str_no_record);
        }
        $post_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $post_id, 0, $lang_id, false);
        $this->set('languages', Language::getAllNames());
        $this->set('images', $post_images);
        $this->set('post_id', $post_id);
        $this->_template->render(false, false);
    }

    public function setImageOrder()
    {
        $this->objPrivilege->canEditBlogPosts();
        $postObj = new BlogPost();
        $post = FatApp::getPostedData();
        $post_id = FatUtility::int($post['post_id']);
        $imageIds=explode('-', $post['ids']);
        $count=1;
        foreach ($imageIds as $row) {
            $order[$count]=$row;
            $count++;
        }
        if (!$postObj->updateImagesOrder($post_id, $order)) {
            Message::addErrorMessage($postObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Ordered_Successfully', $this->adminLangId));
    }

    public function uploadBlogPostImages($post_id, $lang_id=0)
    {
        $this->objPrivilege->canEditBlogPosts();
        $post_id = FatUtility::int($post_id);
        $lang_id = FatUtility::int($lang_id);
        if ($post_id < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $file_type = $post['file_type'];
        $allowedFileTypeArr = array( AttachedFile::FILETYPE_BLOG_POST_IMAGE );

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();

        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $file_type,
            $post_id,
            0,
            $_FILES['file']['name'],
            -1,
            false,
            $lang_id
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Image_Uploaded_Successfully', $this->adminLangId));
    }

    public function deleteImage($post_id = 0, $afile_id=0, $lang_id = 0)
    {
        $post_id = FatUtility::int($post_id);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);
        if (!$post_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $post_id, $afile_id, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getImagesFrm($post_id = 0)
    {
        $this->objPrivilege->canViewBlogPosts();
        $bannerTypeArr = applicationConstants::bannerTypeArr();

        $frm = new Form('frmBlogPostImage', array('id' => 'imageFrm'));
        $frm->addHiddenField('', 'post_id', $post_id);
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld = $frm->addButton(Labels::getLabel('LBL_Photo(s)', $this->adminLangId), 'post_image', Labels::getLabel('LBL_Upload_Image', $this->adminLangId), array('class'=>'blogFile-Js','id'=>'post_image','data-file_type'=>AttachedFile::FILETYPE_BLOG_POST_IMAGE,'data-frm'=>'frmBlogPostImage'));
        return $frm;
    }

    private function getForm($post_id = 0)
    {
        $post_id = FatUtility::int($post_id);
        $frm = new Form('frmBlogPost', array('id'=>'frmBlogPost'));
        $frm->addHiddenField('', 'post_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Post_Identifier', $this->adminLangId), 'post_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $postStatusArr = applicationConstants::getBlogPostStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Post_Status', $this->adminLangId), 'post_published', $postStatusArr, '', array(), '');
        $frm->addCheckBox(Labels::getLabel('LBL_Comment_Open', $this->adminLangId), 'post_comment_opened', 1, array(), false, 0);
        $frm->addCheckBox(Labels::getLabel('LBL_Featured', $this->adminLangId), 'post_featured', 1, array(), false, 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($postId = 0, $lang_id = 0)
    {
        $postId = FatUtility::int($postId);

        $srch = BlogPost::getSearchObject(true);
        $srch->addCondition('bp.post_id', '=', $postId);

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        $frm = new Form('frmBlogPostCatLang', array('id'=>'frmBlogPostCatLang'));
        $frm->addHiddenField('', 'post_id', $postId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->adminLangId), 'post_title');
        $frm->addRequiredField(Labels::getLabel('LBL_Post_Author_Name', $this->adminLangId), 'post_author_name');
        $fld = $frm->addTextarea(Labels::getLabel('LBL_Short_Description', $this->adminLangId), 'post_short_description');
        $fld->requirements()->setRequired(true);
        $fld->htmlAfterField = '<small>'.Labels::getLabel("LBL_only_250_characters_will_be_shown_on_frontend", $this->adminLangId).'</small>';
        $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $this->adminLangId), 'post_description')->requirements()->setRequired(true);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));

        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class'=>'search-input'));
        $postStatusArr = applicationConstants::getBlogPostStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Post_Status', $this->adminLangId), 'post_published', $postStatusArr, '', array(), 'Select');
        $frm->addHiddenField('', 'page');
        $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getLinksForm($post_id)
    {
        $this->objPrivilege->canViewBlogPosts();
        $postObj = new BlogPost();
        $post_categories = $postObj->getPostCategories($post_id);
        $selectedCats= array();
        if ($post_categories) {
            foreach ($post_categories as $cat) {
                $selectedCats[] = $cat['bpcategory_id'];
            }
        }
        $frm = new Form('frmLinks', array('id'=>'frmLinks'));

        $prodCatObj = new BlogPostCategory();
        $arr_options = $prodCatObj->getBlogPostCatTreeStructure();
        $frm->addCheckBoxes(Labels::getLabel('LBL_Category', $this->adminLangId), 'categories', $arr_options, $selectedCats);

        $frm->addHiddenField('', 'post_id', $post_id);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
