<?php class BlogController extends MyAppController
{
    public function __construct($action = '')
    {
        parent::__construct($action);
        $this->set('blogPage', true);
        $this->set('bodyClass', 'is--blog');
        $this->_template->addCss('css/blog.css');
        if ('rtl' == CommonHelper::getLayoutDirection()) {
            $this->_template->addCss('css/blog1-arabic.css');
        }
        $this->_template->addJs('js/blog.js');
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $className = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($className));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $className = ucwords(implode(' ', $arr));

        if ($action == 'index') {
            $nodes[] = array('title'=>$className);
        } else {
            $nodes[] = array('title'=>$className, 'href'=>CommonHelper::generateUrl($urlController));
        }
        $parameters = FatApp::getParameters();

        if (!empty($parameters)) {
            if ($action == 'category') {
                $id = reset($parameters);
                $id = FatUtility::int($id);
                $data = BlogPostCategory::getAttributesByLangId($this->siteLangId, $id);
                $title = $data['bpcategory_name'];
                $nodes[] = array('title'=>$title);
            } elseif ($action == 'postDetail') {
                $id = reset($parameters);
                $id = FatUtility::int($id);
                $data = BlogPost::getAttributesByLangId($this->siteLangId, $id);
                $title = CommonHelper::truncateCharacters($data['post_title'], 40);
                $nodes[] = array('title'=>$title);
            }
        } elseif ($action == 'contributionForm' || $action == 'setupContribution') {
            $nodes[] = array('title'=>Labels::getLabel('Lbl_Contribution', $this->siteLangId));
        }

        return $nodes;
    }

    public function index()
    {
        $srch = $this->getBlogSearchObject();
        $srch->addOrder('post_added_on', 'desc');
        $srch->setPageSize(7);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $featuredSrch = $this->getBlogSearchObject();
        $featuredSrch->addCondition('post_featured', '=', applicationConstants::YES);
        $featuredSrch->addOrder('post_added_on', 'desc');
        $featuredRs = $featuredSrch->getResultSet();
        $featuredRecords = FatApp::getDb()->fetchAll($featuredRs);

        $popularSrch = $this->getBlogSearchObject();
        $popularSrch->addOrder('post_view_count', 'DESC');
        $popularSrch->setPageSize(7);
        $popularRs = $popularSrch->getResultSet();
        $popularRecords = FatApp::getDb()->fetchAll($popularRs);

        $this->set('postList', $records);
        $this->set('featuredPostList', $featuredRecords);
        $this->set('popularPostList', $popularRecords);
        $this->_template->addJs('js/slick.min.js');
        $this->_template->addCss('css/slick.css');
        $this->_template->render();
    }

    private function getBlogSearchObject()
    {
        $srch = BlogPost::getSearchObject($this->siteLangId, true, false, true);
        $srch->addMultipleFields(array('bp.*' , 'IFNULL(bp_l.post_title,post_identifier) as post_title' , 'bp_l.post_author_name', 'bp_l.post_short_description', 'group_concat(bpcategory_id) categoryIds', 'group_concat(IFNULL(bpcategory_name, bpcategory_identifier) SEPARATOR "~") categoryNames', 'group_concat(GETBLOGCATCODE(bpcategory_id)) AS categoryCodes'));
        $srch->addCondition('postlang_post_id', 'is not', 'mysql_func_null', 'and', true);
        $srch->addCondition('post_published', '=', applicationConstants::YES);
        $srch->addGroupby('post_id');
        return $srch;
    }

    public function category($categoryId)
    {
        $categoryId = FatUtility::int($categoryId);
        if ($categoryId < 1) {
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_Request', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $srch = BlogPost::getSearchObject($this->siteLangId, true, false, true);
        $srch->addMultipleFields(array('bp.*' , 'IFNULL(bp_l.post_title,post_identifier) as post_title' , 'bp_l.post_author_name', 'bp_l.post_short_description', 'group_concat(bpcategory_id) categoryIds', 'group_concat(IFNULL(bpcategory_name, bpcategory_identifier) SEPARATOR "~") categoryNames', 'group_concat(GETBLOGCATCODE(bpcategory_id)) AS categoryCodes'));
        $srch->addCondition('postlang_post_id', 'is not', 'mysql_func_null', 'and', true);
        $srch->addCondition('ptc_bpcategory_id', '=', $categoryId);
        $srch->addCondition('post_published', '=', applicationConstants::YES);
        $srch->addOrder('post_added_on', 'desc');
        $srch->setPageSize(5);
        $srch->addGroupby('post_id');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set('postList', $records);
        $this->_template->addJs('js/slick.min.js');
        $this->_template->addCss('css/slick.css');
        $this->set('bpCategoryId', $categoryId);
        $this->_template->render(true, true, 'blog/index.php');
    }

    public function search()
    {
        $headerFormParamsArr = FatApp::getParameters();
        $headerFormParamsAssocArr = BlogPost::convertArrToSrchFiltersAssocArr($headerFormParamsArr);
        $frm = $this->getBlogSearchForm();
        $frm->fill($headerFormParamsAssocArr);
        if (isset($headerFormParamsAssocArr['keyword'])) {
            $keyword = $headerFormParamsAssocArr['keyword'];
            $this->set('keyword', $keyword);
            $this->set('srchFrm', $frm);
        }

        $featuredSrch = $this->getBlogSearchObject();
        $featuredSrch->addCondition('post_featured', '=', applicationConstants::YES);
        $featuredSrch->addOrder('post_added_on', 'desc');
        $featuredSrch->setPageSize(4);
        $featuredRs = $featuredSrch->getResultSet();
        $featuredRecords = FatApp::getDb()->fetchAll($featuredRs);

        $popularSrch = $this->getBlogSearchObject();
        $popularSrch->addOrder('post_view_count', 'DESC');
        $popularSrch->setPageSize(4);
        $popularRs = $popularSrch->getResultSet();
        $popularRecords = FatApp::getDb()->fetchAll($popularRs);

        $this->set('featuredPostList', $featuredRecords);
        $this->set('popularPostList', $popularRecords);

        $this->_template->addJs('js/slick.min.js');
        $this->_template->addCss('css/slick.css');
        $this->_template->render(true, true);
    }

    public function blogList()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $srch = BlogPost::getSearchObject($this->siteLangId, true, false, true);
        $srch->addMultipleFields(array('bp.*' , 'IFNULL(bp_l.post_title,post_identifier) as post_title' , 'bp_l.post_author_name', 'bp_l.post_short_description', 'group_concat(bpcategory_id) categoryIds', 'group_concat(IFNULL(bpcategory_name, bpcategory_identifier) SEPARATOR "~") categoryNames', 'group_concat(GETBLOGCATCODE(bpcategory_id)) AS categoryCodes'));
        $srch->addCondition('postlang_post_id', 'is not', 'mysql_func_null', 'and', true);

        if ($categoryId = FatApp::getPostedData('categoryId', FatUtility::VAR_INT, 0)) {
            $srch->addCondition('ptc_bpcategory_id', '=', $categoryId);
            $this->set('bpCategoryId', $categoryId);
        } elseif ($keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '')) {
            $keywordCond= $srch->addCondition('post_title', 'like', "%$keyword%");
            $keywordCond->attachCondition('post_short_description', 'like', "%$keyword%");
            $keywordCond->attachCondition('post_description', 'like', "%$keyword%");
            $this->set('keyword', $keyword);
        }

        $srch->addCondition('post_published', '=', applicationConstants::YES);
        $srch->addOrder('post_added_on', 'desc');
        $srch->setPageSize($pageSize);
        $srch->setPageNumber($page);
        $srch->addGroupby('post_id');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $startRecord = ($page - 1) * $pageSize + 1 ;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postList', $records);
        $this->set('recordCount', $totalRecords);
        $this->set('postedData', $post);

        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = ($totalRecords > 0) ? 1 : 0 ;
        $json['endRecord'] = $endRecord;
        $json['html'] = $this->_template->render(false, false, 'blog/blog-listing.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'blog/load-more-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function postDetail($blogPostId)
    {
        $blogPostId = FatUtility::int($blogPostId);
        if ($blogPostId <= 0) {
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Blog'));
        }

        $post_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $blogPostId, 0, $this->siteLangId);
        $this->set('post_images', $post_images);

        $srch = BlogPost::getSearchObject($this->siteLangId, true, true);
        $srch->addCondition('post_id', '=', $blogPostId);
        $srch->addMultipleFields(array('bp.*' , 'IFNULL(bp_l.post_title,post_identifier) as post_title' , 'bp_l.post_author_name', 'bp_l.post_description' , 'group_concat(bpcategory_id) categoryIds', 'group_concat(IFNULL(bpcategory_name, bpcategory_identifier) SEPARATOR "~") categoryNames'));
        $srchComment = clone $srch;
        $srch->addGroupby('post_id');
        if (!$blogPostData = FatApp::getDb()->fetch($srch->getResultSet())) {
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Blog'));
        }
        $this->set('blogPostData', $blogPostData);

        $srchComment->addGroupby('bpcomment_id');
        $srchComment->joinTable(BlogComment::DB_TBL, 'inner join', 'bpcomment.bpcomment_post_id = post_id and bpcomment.bpcomment_deleted=0', 'bpcomment');
        $srchComment->addMultipleFields(array('bpcomment.*'));
        $srchComment->addCondition('bpcomment_approved', '=', BlogComment::COMMENT_STATUS_APPROVED);

        $commentsResultSet = $srchComment->getResultSet();
        $this->set('commentsCount', $srchComment->recordCount());
        $this->set('blogPostComments', FatApp::getDb()->fetchAll($commentsResultSet));

        if ($blogPostData['post_comment_opened'] && UserAuthentication::isUserLogged()) {
            $frm = $this->getPostCommentForm($blogPostId);
            if (UserAuthentication::isUserLogged()) {
                $loggedUserId = UserAuthentication::getLoggedUserId(true);
                $userObj = new User($loggedUserId);
                $userInfo = $userObj->getUserInfo();
                $frm->getField('bpcomment_author_name')->value = $userInfo['user_name'];
                $frm->getField('bpcomment_author_email')->value = $userInfo['credential_email'];
            }
            $this->set('postCommentFrm', $frm);
        }
        $title  = $blogPostData['post_title'];
        $post_description = trim(CommonHelper::subStringByWords(strip_tags(CommonHelper::renderHtml($blogPostData["post_description"], true)), 500));
        $post_description .= ' - '.Labels::getLabel('LBL_See_more_at', $this->siteLangId).": ".CommonHelper::getCurrUrl();
        $postImageUrl = CommonHelper::generateFullUrl('Image', 'blogPostFront', array($blogPostData['post_id'],$this->siteLangId, ''));
        $socialShareContent = array(
        'type'=>'Blog Post',
        'title'=>$title,
        'description'=>$post_description,
        'image'=>$postImageUrl,
        );

        /* View Count functionality [ */
        if (empty($_SESSION['postid'])) {
            $_SESSION['postid'] = $blogPostId;
            $flag = 1;
        } else {
            $finalarray = explode(',', $_SESSION['postid']);
            if (in_array($blogPostId, $finalarray)) {
                $flag = 0;
            } else {
                $_SESSION['postid'] .= ',' . $blogPostId;
                $flag = 1;
            }
        }
        if ($flag == 1) {
            $blog = new BlogPost();
            $blog->setPostViewsCount($blogPostId);
        }
        /* ] */

        $featuredSrch = $this->getBlogSearchObject();
        $featuredSrch->addCondition('post_featured', '=', applicationConstants::YES);
        $featuredSrch->addOrder('post_added_on', 'desc');
        $featuredSrch->setPageSize(4);
        $featuredRs = $featuredSrch->getResultSet();
        $featuredRecords = FatApp::getDb()->fetchAll($featuredRs);

        $popularSrch = $this->getBlogSearchObject();
        $popularSrch->addOrder('post_view_count', 'DESC');
        $popularSrch->setPageSize(4);
        $popularRs = $popularSrch->getResultSet();
        $popularRecords = FatApp::getDb()->fetchAll($popularRs);

        $this->set('featuredPostList', $featuredRecords);
        $this->set('popularPostList', $popularRecords);

        $this->set('socialShareContent', $socialShareContent);

        $srchCommentsFrm = $this->getCommentSearchForm($blogPostId);
        $this->set('srchCommentsFrm', $srchCommentsFrm);

        $this->_template->addJs(array('js/masonry.pkgd.js'));
        $this->_template->addJs(array('js/slick.js'));
        $this->_template->addCss(array('css/slick.css'));

        $this->_template->render();
    }

    public function setupPostComment()
    {
        $userId = UserAuthentication::getLoggedUserId(true);
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_Not_Logged', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $blogPostId = FatApp::getPostedData('bpcomment_post_id', FatUtility::VAR_INT, 0);
        if ($blogPostId <=0) {
            Message::addErrorMessage(Labels('Lbl_Invalid_Request'));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->getPostCommentForm($blogPostId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage($frm->getValidationErrors());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* checking Abusive Words[ */
        $enteredAbusiveWordsArr = array();
        if (!Abusive::validateContent($post['bpcomment_content'], $enteredAbusiveWordsArr)) {
            if (!empty($enteredAbusiveWordsArr)) {
                $errStr =  Labels::getLabel("LBL_Word_{abusiveword}_is/are_not_allowed_to_post", $this->siteLangId);
                $errStr = str_replace("{abusiveword}", '"'.implode(", ", $enteredAbusiveWordsArr).'"', $errStr);
                Message::addErrorMessage($errStr);
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */

        $post['bpcomment_user_id'] = $userId;
        $post['bpcomment_added_on'] = date('Y-m-d H:i:s');
        $post['bpcomment_user_ip'] = $_SERVER['REMOTE_ADDR'];
        $post['bpcomment_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        $blogComment = new BlogComment();
        $blogComment->assignValues($post);
        if (!$blogComment->save()) {
            Message::addErrorMessage($blogComment->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $blogCommentId = $blogComment->getMainTableRecordId();

        $notificationData = array(
        'notification_record_type' => Notification::TYPE_BLOG,
        'notification_record_id' => $blogCommentId,
        'notification_user_id' => UserAuthentication::getLoggedUserId(true),
        'notification_label_key' => Notification::BLOG_COMMENT_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel('Msg_Blog_Comment_Saved_and_awaiting_admin_approval.', $this->siteLangId));
    }

    public function searchComments()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $blogPostId = FatApp::getPostedData('post_id', FatUtility::VAR_INT, 0);
        $srch = BlogPost::getSearchObject($this->siteLangId, true, true);
        $srch->joinTable(BlogComment::DB_TBL, 'inner join', 'bpcomment.bpcomment_post_id = post_id and bpcomment.bpcomment_deleted=0', 'bpcomment');
        $srch->addMultipleFields(array('bpcomment.*'));
        $srch->addCondition('bpcomment_approved', '=', BlogComment::COMMENT_STATUS_APPROVED);
        $srch->addCondition('post_id', '=', $blogPostId);

        $srch->setPageSize($pageSize);
        $srch->setPageNumber($page);

        $srch->addGroupby('bpcomment_id');
        $srch->addOrder('bpcomment_added_on', 'desc');
        $this->set('blogPostComments', FatApp::getDb()->fetchAll($srch->getResultSet()));
        $this->set('commentsCount', $srch->recordCount());

        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);
        $json['html'] = $this->_template->render(false, false, 'blog/search-comments.php', true, true);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'blog/load-more-comments-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function contributionForm()
    {
        $frm = $this->getContributionForm();
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId(true);
            $userObj = new User($loggedUserId);
            $userInfo = $userObj->getUserInfo();
            $nameArr = explode(' ', $userInfo['user_name']);
            $wordCount = count($nameArr);

            $firstName = ($wordCount>0)?$nameArr[0]:$userInfo['user_name'];
            $lastName = ($wordCount>1)?$nameArr[$wordCount-1]:'';

            $frm->getField('bcontributions_author_first_name')->value = $firstName;
            $frm->getField('bcontributions_author_last_name')->value = $lastName;
            $frm->getField('bcontributions_author_email')->value = $userInfo['credential_email'];
            $frm->getField('bcontributions_author_phone')->value = $userInfo['user_phone'];
        }
        if ($post = FatApp::getPostedData()) {
            $frm->fill($post);
        }
        $this->set('frm', $frm);
        $this->_template->render(true, true, 'blog/contribution-form.php');
    }

    public function setupContribution()
    {
        $frm = $this->getContributionForm();
        $post = FatApp::getPostedData();
        $post['file'] = 'file';
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage($frm->getValidationErrors());
            $this->contributionForm();
            return false;
        }

        if (FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '')!= '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '')!= '') {
            if (!CommonHelper::verifyCaptcha()) {
                Message::addErrorMessage(Labels::getLabel('MSG_That_captcha_was_incorrect', $this->siteLangId));
                $this->contributionForm();
                return false;
            }
        }
        $post['bcontributions_added_on'] = date('Y-m-d H:i:s');
        $post['bcontributions_user_id'] = UserAuthentication::getLoggedUserId(true);
        if ($loggedUserId = UserAuthentication::getLoggedUserId(true)) {
            $userObj = new User($loggedUserId);
            $userInfo = $userObj->getUserInfo();
            $nameArr = explode(' ', $userInfo['user_name']);
            $wordCount = count($nameArr);
            $firstName = ($wordCount>0)?$nameArr[0]:$userInfo['user_name'];
            $post['bcontributions_author_first_name'] = $firstName;
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            $this->contributionForm();
            return false;
        } else {
            $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileExt = strtolower($fileExt);
            if (!in_array($fileExt, applicationConstants::allowedFileExtensions())) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_FILE_EXTENSION', $this->siteLangId));
                $this->contributionForm();
                return false;
            }

            $fileMimeType = mime_content_type($_FILES['file']['tmp_name']);
            if (!in_array($fileMimeType, applicationConstants::allowedMimeTypes())) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_FILE_MIME_TYPE', $this->siteLangId));
                $this->contributionForm();
                return false;
            }
        }

        $uploadedFile = $_FILES['file']['tmp_name'];

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->isUploadedFile($uploadedFile)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            $this->contributionForm();
            return false;
        }

        $contribution = new BlogContribution();
        $contribution->assignValues($post);
        if (!$contribution->save()) {
            Message::addErrorMessage($contribution->getError());
            $this->contributionForm();
            return false;
        }
        $contributionId = $contribution->getMainTableRecordId();

        $notificationData = array(
        'notification_record_type' => Notification::TYPE_BLOG,
        'notification_record_id' => $contributionId,
        'notification_user_id' => UserAuthentication::getLoggedUserId(true),
        'notification_label_key' => Notification::BLOG_CONTRIBUTION_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_BLOG_CONTRIBUTION, $contributionId, 0, $_FILES['file']['name'], -1, true)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            $this->contributionForm();
            return false;
        }

        Message::addMessage(Labels::getLabel('Lbl_Contributed_Successfully', $this->siteLangId));
        FatApp::redirectUser(CommonHelper::generateUrl('Blog', 'contributionForm'));
    }

    private function getContributionForm()
    {
        $frm = new Form('frmBlogContribution');
        $frm->addRequiredField(Labels::getLabel('LBL_First_Name', $this->siteLangId), 'bcontributions_author_first_name', '');
        $frm->addRequiredField(Labels::getLabel('LBL_Last_Name', $this->siteLangId), 'bcontributions_author_last_name', '');
        $frm->addEmailField(Labels::getLabel('LBL_Email_Address', $this->siteLangId), 'bcontributions_author_email', '');
        $fld_phn = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $this->siteLangId), 'bcontributions_author_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $fld_phn->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        // $fld_phn->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_e.g.', $this->siteLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</small>';
        $fld_phn->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $frm->addFileUpload(Labels::getLabel('LBL_Upload_File', $this->siteLangId), 'file')->requirements()->setRequired(true);
        if (FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '')!= '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '')!= '') {
            $frm->addHtml('', 'htmlNote', '<div class="g-recaptcha" data-sitekey="'.FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '').'"></div>');
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SUBMIT', $this->siteLangId));
        return $frm;
    }

    private function getPostCommentForm($postId)
    {
        $frm = new Form('frmBlogPostComment');
        $frm->addRequiredField(Labels::getLabel('', $this->siteLangId), 'bpcomment_author_name');
        $frm->addEmailField(Labels::getLabel('', $this->siteLangId), 'bpcomment_author_email', '');
        $frm->addTextarea(Labels::getLabel('', $this->siteLangId), 'bpcomment_content')->requirements()->setRequired(true);
        $frm->addHiddenField('', 'bpcomment_post_id', $postId);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('Btn_Post_Comment', $this->siteLangId));
        return $frm;
    }

    private function getCommentSearchForm($postId)
    {
        $frm = new Form('frmSearchComments');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'post_id', $postId);
        return $frm;
    }

    public function getBlogSearchForm()
    {
        $frm = new Form('frmBlogSearch');
        $frm->addTextBox('', 'keyword', '', array('id'=>'keyword'));
        $frm->addSubmitButton('', 'btnProductSrchSubmit', Labels::getLabel('btn_search', $this->siteLangId));
        return $frm;
    }
}
