<?php
class ProductCategoriesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup','updateOrder');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewProductCategories($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditProductCategories($this->admin_id, true);
        $this->rewriteUrl = ProductCategory::REWRITE_URL_PREFIX;
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($parent = 0)
    {
        $this->objPrivilege->canViewProductCategories();
        $parent = FatUtility::int($parent);
        $search = $this->getSearchForm();
        $data = array(
        'prodcat_parent'=>$parent
        );
        $search->fill($data);

        $prodCateData = ProductCategory::getAttributesById($parent);

        $prodCateObj = new ProductCategory();
        $category_structure=$prodCateObj->getCategoryStructure($parent);
        $this->set("includeEditor", true);

        $this->set("search", $search);
        $this->set("prodcat_parent", $parent);
        $this->set("prodCateData", $prodCateData);
        $this->set("category_structure", $category_structure);
        $this->_template->addJs('js/import-export.js');
        $this->_template->render();
    }

    public function search()
    {
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $page = (empty($page) || $page <= 0) ? 1: FatUtility::int($page);
        //$pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $pagesize = 500;

        $post = $searchForm->getFormDataFromArray($data);
        $parent = FatApp::getPostedData('prodcat_parent', FatUtility::VAR_INT, 0);

        $srch = ProductCategory::getSearchObject(true, $this->adminLangId, false);
        $srch->addCondition('m.prodcat_parent', '=', $parent);
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addFld('m.*');

        if (!empty($post['prodcat_identifier'])) {
            $srch->addCondition('m.prodcat_identifier', 'like', '%'.$post['prodcat_identifier'].'%');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        /* $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords(); */

        /* $srch->joinTable(ProductCategory::DB_TBL . '_lang', 'LEFT OUTER JOIN',
        'prodcatlang_prodcat_id = m.prodcat_id AND prodcatlang_lang_id = ' . $this->adminLangId); */

        $srch->addMultipleFields(array("prodcat_name"));
        $rs = $srch->getResultSet();
        $pageCount = $srch->pages();
        $records = FatApp::getDb()->fetchAll($rs);

        $parentCatData = ProductCategory::getAttributesById($parent);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $pageCount);
        $this->set('page', $page);
        $this->set('parentData', $parentCatData);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        //$this->set('category_structure', $category_structure);
        $this->_template->render(false, false);
    }

    public function form($prodcat_id = 0, $prodcat_parent = 0)
    {
        $this->objPrivilege->canEditProductCategories();
        $prodcat_id = FatUtility::int($prodcat_id);
        $prodcat_parent = FatUtility::int($prodcat_parent);
        $prodCatFrm = $this->getForm($prodcat_id);
        $parentUrl ='';
        if (0 < $prodcat_id) {
            $data = ProductCategory::getAttributesById($prodcat_id);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', $this->rewriteUrl.$prodcat_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            /* ] */
        } else {
            $data=array('prodcat_parent'=>$prodcat_parent);
            if ($prodcat_parent) {
                $parentRewrite = UrlRewrite::getDataByOriginalUrl($this->rewriteUrl.$prodcat_parent);
                $parentUrl = $parentRewrite['urlrewrite_custom'];
            }
        }


        $prodCatFrm->fill($data);
        $this->set('languages', Language::getAllNames());
        $this->set('prodcat_id', $prodcat_id);
        $this->set('parentUrl', $parentUrl);
        $this->set('prodcat_parent_id', $prodcat_parent);
        $this->set('prodCatFrm', $prodCatFrm);
        $this->_template->render(false, false);
    }

    public function langForm($catId = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditProductCategories();

        $prodcat_id = FatUtility::int($catId);
        $lang_id = FatUtility::int($lang_id);

        if ($prodcat_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $prodCatLangFrm = $this->getLangForm($prodcat_id, $lang_id);
        $langData = ProductCategory::getAttributesByLangId($lang_id, $prodcat_id);

        if ($langData) {
            $prodCatLangFrm->fill($langData);
        }

        /* $catImages = AttachedFile::getAttachment( AttachedFile::FILETYPE_CATEGORY_IMAGE, $prodcat_id, 0, $lang_id );
        $catIcons = AttachedFile::getAttachment( AttachedFile::FILETYPE_CATEGORY_ICON, $prodcat_id, 0, $lang_id );
        $catBanners = AttachedFile::getAttachment( AttachedFile::FILETYPE_CATEGORY_BANNER, $prodcat_id, 0, $lang_id );

        $this->set('catImages',$catImages);
        $this->set('catIcons',$catIcons);
        $this->set('catBanners',$catBanners); */

        $this->set('languages', Language::getAllNames());
        $this->set('prodcat_id', $prodcat_id);
        $this->set('prodcat_lang_id', $lang_id);
        $this->set('prodCatLangFrm', $prodCatLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function mediaForm($prodcat_id = 0)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        $prodCatIconFrm = $this->getCategoryIconForm($prodcat_id);
        $prodCatImageFrm = $this->getCategoryImageForm($prodcat_id);
        $prodCatBannerFrm = $this->getCategoryBannerForm($prodcat_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();

        $this->set('prodcat_id', $prodcat_id);
        $this->set('prodCatIconFrm', $prodCatIconFrm);
        $this->set('prodCatImageFrm', $prodCatImageFrm);
        $this->set('prodCatBannerFrm', $prodCatBannerFrm);
        $this->set('bannerTypeArr', $bannerTypeArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function images($prodcat_id, $imageType = '', $lang_id = 0, $slide_screen = 0)
    {
        $this->objPrivilege->canViewShops();
        $shop_id = FatUtility::int($prodcat_id);
        $lang_id = FatUtility::int($lang_id);

        if (!$prodcat_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $catDetails = ProductCategory::getAttributesById($prodcat_id);

        if (!false == $catDetails && ($catDetails['prodcat_deleted'] == 1)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $catIcons = $catBanners = array();
        if ($imageType=='icon') {
            $catIcons = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CATEGORY_ICON, $prodcat_id, 0, $lang_id, false);
            $this->set('images', $catIcons);
            $this->set('imageFunction', 'icon');
        } elseif ($imageType=='banner') {
            $catBanners = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CATEGORY_BANNER, $prodcat_id, 0, $lang_id, false, $slide_screen);
            $this->set('images', $catBanners);
            $this->set('screenTypeArr', $this->getDisplayScreenName());
            $this->set('imageFunction', 'banner');
        }
        $this->set('imageType', $imageType);
        $this->set('shop_id', $shop_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setUpCatImages()
    {
        $post = FatApp::getPostedData();
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $prodcat_id = FatApp::getPostedData('prodcat_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $slide_screen = FatApp::getPostedData('slide_screen', FatUtility::VAR_INT, 0);
        if (!$file_type || !$prodcat_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_CATEGORY_IMAGE, AttachedFile::FILETYPE_CATEGORY_ICON, AttachedFile::FILETYPE_CATEGORY_BANNER);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage(
            $_FILES['file']['tmp_name'],
            $file_type,
            $prodcat_id,
            0,
            $_FILES['file']['name'],
            -1,
            $unique_record = true,
            $lang_id,
            $_FILES['file']['type'],
            $slide_screen
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
            // FatUtility::dieJsonError($fileHandlerObj->getError());
        }
        ProductCategory::setImageUpdatedOn($prodcat_id);
        $this->set('file', $_FILES['file']['name']);
        $this->set('prodcat_id', $prodcat_id);
        $this->set('msg', $_FILES['file']['name'].' '.Labels::getLabel('LBL_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeImage($afileId, $prodCatId, $imageType = '', $langId = 0, $slide_screen = 0)
    {
        $afileId = FatUtility::int($afileId);
        $prodCatId = FatUtility::int($prodCatId);
        $langId = FatUtility::int($langId);

        if (!$afileId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if ($imageType=='icon') {
            $fileType = AttachedFile::FILETYPE_CATEGORY_ICON;
        } elseif ($imageType=='banner') {
            $fileType = AttachedFile::FILETYPE_CATEGORY_BANNER;
        }
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($fileType, $prodCatId, $afileId, 0, $langId, $slide_screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        ProductCategory::setImageUpdatedOn($prodCatId);
        $this->set('imageType', $imageType);
        $this->set('msg', Labels::getLabel('MSG_Image_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeBanner($prodcat_id = 0, $langId = 0)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        $langId = FatUtility::int($langId);
        if (!$prodcat_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CATEGORY_BANNER, $prodcat_id, 0, 0, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function contentBlock($prodcat_id = 0){
    $this->objPrivilege->canEditProductCategories();
    $prodcat_id = FatUtility::int($prodcat_id);

    if($prodcat_id == 0){
    FatUtility::dieWithError($this->str_invalid_request);
    }

    $blockCatFrm = $this->getContentBlockForm($prodcat_id);
    $data = array();

    if($data){
    $blockCatFrm->fill($data);
    }

    $this->set('languages', Language::getAllNames());
    $this->set('prodcat_id', $prodcat_id);
    $this->set('blockCatFrm', $blockCatFrm);
    $this->_template->render(false, false);
    } */

    private function getForm($prodcat_id = 0)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        $prodCatObj = new ProductCategory();
        $arrCategories = $prodCatObj->getCategoriesForSelectBox($this->adminLangId, $prodcat_id);
        $categories = $prodCatObj->makeAssociativeArray($arrCategories);

        $frm = new Form('frmProdCategory', array( 'id' => 'frmProdCategory'));
        $frm->addHiddenField('', 'prodcat_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Category_Identifier', $this->adminLangId), 'prodcat_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Category_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Category_Parent', $this->adminLangId), 'prodcat_parent', array( 0 => Labels::getLabel('LBL_Root_Category', $this->adminLangId) ) + $categories, '', array(), '');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

        $frm->addSelectBox(Labels::getLabel('LBL_Category_Status', $this->adminLangId), 'prodcat_active', $activeInactiveArr, '', array(), '');
        /* $frm->addCheckBox(Labels::getLabel('LBL_Featured',$this->adminLangId), 'prodcat_featured', 1, array(),false,0); */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));

        return $frm;
    }

    private function getLangForm($prodcat_id = 0, $lang_id = 0)
    {
        $prodcat_id = FatUtility::int($prodcat_id);

        $srch = ProductCategory::getSearchObject(true);
        $srch->addCondition('m.prodcat_id', '=', $prodcat_id);
        $srch->addCondition('m.prodcat_parent', '=', 0);

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        $frm = new Form('frmProdCatLang', array('id'=>'frmProdCatLang'));
        $frm->addHiddenField('', 'prodcat_id', $prodcat_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Category_Name', $this->adminLangId), 'prodcat_name');
        /*$fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $this->adminLangId), 'prodcat_description');
        // $fld->requirements()->setLength(0,250);
        $fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_First_100_characters_will_be_shown_in_home_page_collections.', $this->adminLangId).'</small>';*/
        /* if(isset($row['child_count']) && $row['child_count'] > 0){
        $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Content_Block',$this->adminLangId),'prodcat_content_block');
        $fld->htmlAfterField = '<br/>'.Labels::getLabel('LBL_Prefix_with_{SITEROOT}_if_u_want_to_generate_system_site_url.',$this->adminLangId).'<br/>E.g: {SITEROOT}/products, {SITEROOT}/contact_us etc.';
        } */

        /* $frm->addButton( 'Category Image', 'cat_image', 'Upload File', array('class'=>'catFile-Js', 'id' => 'cat_image', 'data-file_type' => AttachedFile::FILETYPE_CATEGORY_IMAGE, 'data-prodcat_id'=>$prodcat_id ));

        $frm->addButton('Icon','cat_icon','Upload file',array('class'=>'catFile-Js','id'=>'cat_icon','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_ICON,'data-prodcat_id'=>$prodcat_id));

        $frm->addButton('Banner','cat_banner','Upload file',array('class'=>'catFile-Js','id'=>'cat_banner','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_BANNER,'data-prodcat_id'=>$prodcat_id)); */

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getContentBlockForm($prodcat_id)
    {
        $frm = new Form('frmProdContentBlock');
        $frm->addHiddenField('', 'prodcat_id', $prodcat_id);

        $srch = ContentPage::getListingObj($this->adminLangId, array('cpage_id'));
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetchAllAssoc($rs);

        $cmsPages = $data;
        $frm->addSelectBox(Labels::getLabel('LBL_Content_Block', $this->adminLangId), 'active', $cmsPages, '', array(), '');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    /* private function getMediaForm( $prodcat_id = 0 ){
    $frm = new Form('frmCatMedia');
    $frm->addHiddenField('','prodcat_id',$prodcat_id);

    $fld = $frm->addButton( 'Category Image', 'cat_image', 'Upload File', array('class'=>'catFile-Js', 'id' => 'cat_image', 'data-file_type' => AttachedFile::FILETYPE_CATEGORY_IMAGE, 'data-prodcat_id'=>$prodcat_id ));

    $fld1 =  $frm->addButton('Icon','cat_icon','Upload file',array('class'=>'catFile-Js','id'=>'cat_icon','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_ICON,'data-prodcat_id'=>$prodcat_id));

    $fld2 =  $frm->addButton('Banner','cat_banner','Upload file',array('class'=>'catFile-Js','id'=>'cat_banner','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_BANNER,'data-prodcat_id'=>$prodcat_id));
    return $frm;
    } */

    /* private function getMediaForm( $prodcat_id = 0 ){
    $frm = new Form('frmCatMedia');
    $frm->addHTML( '', 'cat_image_heading', '' );
    $languagesAssocArr = Language::getAllNames();

    foreach( $languagesAssocArr as $lang_id => $lang_name ){
    if( $this->canEdit ){
                $frm->addButton('Image'.' ('.$lang_name.')', 'cat_image_'.$lang_id,'Upload',
                    array('class'=>'catFile-Js','id'=>'cat_image','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_IMAGE,'lang_id' =>$lang_id, 'prodcat_id' =>$prodcat_id));
    } else {
                $frm->addHtml('','cat_image_'.$lang_id, 'Category Image ('. $lang_name .')');
    }
    $frm->addHtml('','cat_image_display_div_'.$lang_id, '');
    }

    $frm->addHTML( '', 'cat_icon_heading', '' );
    foreach( $languagesAssocArr as $lang_id => $lang_name ){
    if( $this->canEdit ){
                $frm->addButton('Icon'.' ('. $lang_name .')','cat_icon_'.$lang_id,'Upload Icon',array('class'=>'catFile-Js','id'=>'cat_icon','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_ICON,'lang_id' =>$lang_id,'prodcat_id'=>$prodcat_id));
    } else {
                $frm->addHtml('','cat_icon_'.$lang_id, 'Category Icon ('. $lang_name .')');
    }
    $frm->addHtml('','cat_icon_display_div_'.$lang_id, '');
    }

    $frm->addHTML( '', 'cat_banner_heading', '' );
    foreach( $languagesAssocArr as $lang_id => $lang_name ){
    if( $this->canEdit ){
                $frm->addButton('Banner'.' ('. $lang_name .')','cat_banner_'.$lang_id,'Upload Banner',array('class'=>'catFile-Js','id'=>'cat_banner','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_BANNER,'lang_id' =>$lang_id,'prodcat_id'=>$prodcat_id));
    } else {
                $frm->addHtml('','cat_banner_'.$lang_id, 'Category Banner ('. $lang_name .')');
    }
    $frm->addHtml('','cat_banner_display_div_'.$lang_id, '');
    }
    return $frm;
    } */

    private function getCategoryImageForm($prodcat_id = 0, $lang_id = 0)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        $lang_id = FatUtility::int($lang_id);
        $frm = new Form('frmCategoryImage');
        $frm->addHTML('', Labels::getLabel('LBL_Image', $this->adminLangId), '<h3>'.Labels::getLabel('LBL_Image', $this->adminLangId).'</h3>');
        $frm->addHiddenField('', 'prodcat_id', $prodcat_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld = $frm->addButton(
            Labels::getLabel('LBL_Image', $this->adminLangId),
            'cat_image',
            Labels::getLabel('LBL_Upload', $this->adminLangId),
            array('class'=>'catFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_IMAGE,'data-frm'=>'frmCategoryImage')
        );
        return $frm;
    }

    private function getCategoryIconForm($prodcat_id = 0, $lang_id = 0)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        $lang_id = FatUtility::int($lang_id);
        $frm = new Form('frmCategoryIcon');
        $frm->addHTML('', Labels::getLabel('LBL_Icon', $this->adminLangId), '<h3>'.Labels::getLabel('LBL_Icon', $this->adminLangId).'</h3>');
        $frm->addHiddenField('', 'prodcat_id', $prodcat_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld = $frm->addButton(
            Labels::getLabel('LBL_Icon', $this->adminLangId),
            'cat_icon',
            Labels::getLabel('LBL_Upload', $this->adminLangId),
            array('class'=>'catFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_ICON,'data-frm'=>'frmCategoryIcon')
        );
        return $frm;
    }

    private function getCategoryBannerForm($prodcat_id = 0, $lang_id = 0)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        $lang_id = FatUtility::int($lang_id);
        $frm = new Form('frmCategoryBanner');
        $frm->addHTML('', Labels::getLabel('LBL_Banner', $this->adminLangId), '<h3>'.Labels::getLabel('LBL_Banner', $this->adminLangId).'</h3>');
        $frm->addHiddenField('', 'prodcat_id', $prodcat_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->adminLangId), 'slide_screen', $screenArr, '', array(), '');
        $fld = $frm->addButton(
            Labels::getLabel('LBL_Banner', $this->adminLangId),
            'cat_banner',
            Labels::getLabel('LBL_Upload', $this->adminLangId),
            array('class'=>'catFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_CATEGORY_BANNER,'data-frm'=>'frmCategoryBanner')
        );
        return $frm;
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));
        $frm->addHiddenField('', 'prodcat_parent', 0, array('id'=>'prodcat_parent'));
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Category_Identifier', $this->adminLangId), 'prodcat_identifier', '', array('class'=>'search-input'));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $frm->getField("btn_submit")->htmlAfterField='&nbsp;&nbsp;<a href="javascript:;" class="clear_btn" onClick="clearSearch()">'.Labels::getLabel('LBL_Clear_Search', $this->adminLangId).'</a>';
        return $frm;
    }

    public function setup()
    {
        $this->objPrivilege->canEditProductCategories();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $prodcat_id = FatUtility::int($post['prodcat_id']);
        $prodcat_parent = FatUtility::int($post['prodcat_parent']);
        unset($post['prodcat_id']);

        $productCategory = new ProductCategory($prodcat_id);
        $isnew = false;
        if ($prodcat_id==0) {
            $display_order=$productCategory->getMaxOrder($prodcat_parent);
            $post['prodcat_display_order']=$display_order;
            $isnew = true;
        }
        $productCategory->assignValues($post);

        //FatApp::getDb()->startTransaction();
        if (!$productCategory->save()) {
            if ($categoryId = ProductCategory::getDeletedProductCategoryByIdentifier($post['prodcat_identifier'])) {
                $record = new ProductCategory($categoryId);
                $data = $post;
                $data['prodcat_deleted'] = applicationConstants::NO;
                $record->assignValues($data);

                if (!$record->save()) {
                    Message::addErrorMessage($record->getError());
                    FatUtility::dieJsonError(Message::getHtml());

                    //$this->set('msg', $record->getError());
                    //$this->_template->render(false, false, 'json-error.php');
                }
            } else {
                Message::addErrorMessage($productCategory->getError());
                FatUtility::dieJsonError(Message::getHtml());
                //$this->set('msg', $record->getError());

                //$this->_template->render(false, false, 'json-error.php');
            }
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
            $this->set('msg', Labels::getLabel('LBL_Category_Setup_Successful', $this->adminLangId));
            $this->set('catId', $categoryId);
            $this->set('langId', $newTabLangId);
        } else {
            $productCategory->updateCatCode();

            if ($post['urlrewrite_custom'] == '') {
                FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($catOriginalUrl)));
            } else {
                $productCategory->rewriteUrl($post['urlrewrite_custom'], true, $prodcat_parent);
            }
            /* ] */

            $newTabLangId=0;
            if ($prodcat_id>0) {
                $catId=$prodcat_id;
                $languages=Language::getAllNames();
                foreach ($languages as $langId => $langName) {
                    if (!$row=ProductCategory::getAttributesByLangId($langId, $prodcat_id)) {
                        $newTabLangId = $langId;
                        break;
                    }
                }
            } else {
                $catId = $productCategory->getMainTableRecordId();
                $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
            }
            if ($newTabLangId == 0 && !$this->isMediaUploaded($prodcat_id)) {
                $this->set('openMediaForm', true);
            }
            Product::updateMinPrices();
            //$id = $record->getMainTableRecordId();
            $this->set('msg', Labels::getLabel('LBL_Category_Setup_Successful', $this->adminLangId));
            $this->set('catId', $catId);
            $this->set('langId', $newTabLangId);
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditProductCategories();
        $post=FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $prodcat_id = $post['prodcat_id'];
        $lang_id = $post['lang_id'];

        if ($prodcat_id==0 || $lang_id==0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($prodcat_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['prodcat_id']);
        unset($post['lang_id']);
        $data = array(
        'prodcatlang_lang_id'=>$lang_id,
        'prodcatlang_prodcat_id'=>$prodcat_id,
        'prodcat_name'=> $post['prodcat_name'],
        /*'prodcat_description'=>$post['prodcat_description'],*/
        );

        if (isset($post['prodcat_content_block'])) {
            $data['prodcat_content_block'] = $post['prodcat_content_block'];
        }

        $prodCatObj=new ProductCategory($prodcat_id);
        if (!$prodCatObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row=ProductCategory::getAttributesByLangId($langId, $prodcat_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        if ($newTabLangId == 0 && !$this->isMediaUploaded($prodcat_id)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', Labels::getLabel('LBL_Category_Setup_Successful', $this->adminLangId));
        $this->set('catId', $prodcat_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function isMediaUploaded($prodcat_id)
    {
        $banner = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER, $prodcat_id, 0);
        $icon = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_ICON, $prodcat_id, 0);

        if ($banner && $icon) {
            return true;
        }

        return false;
    }

    public function changeStatus()
    {
        if (!FatUtility::isAjaxCall()) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }

        if ($this->canEdit === false) {
            FatUtility::dieJsonError($this->unAuthorizeAccess);
        }

        $prodcatId = FatApp::getPostedData('prodcatId', FatUtility::VAR_INT, 0);

        if ($prodcatId < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $catData = ProductCategory::getAttributesById($prodcatId, array('prodcat_active'));
        if (!$catData) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $status = ($catData['prodcat_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateProductCategoryStatus($prodcatId, $status);
        Product::updateMinPrices();
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        if ($this->canEdit === false) {
            FatUtility::dieJsonError($this->unAuthorizeAccess);
        }
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $prodcatIdsArr = FatUtility::int(FatApp::getPostedData('prodcat_ids'));
        if (empty($prodcatIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($prodcatIdsArr as $prodcatId) {
            if (1 > $prodcatId) {
                continue;
            }
            $this->updateProductCategoryStatus($prodcatId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateProductCategoryStatus($prodcatId, $status)
    {
        $prodCatObj = new ProductCategory($prodcatId);
        $status = FatUtility::int($status);
        $prodcatId = FatUtility::int($prodcatId);

        if (1 > $prodcatId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        if (!$prodCatObj->changeStatus($status)) {
            Message::addErrorMessage($prodCatObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditProductCategories();

        $prodcat_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($prodcat_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($prodcat_id);
        Product::updateMinPrices();
        $this->set("msg", $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        if ($this->canEdit === false) {
            FatUtility::dieJsonError($this->unAuthorizeAccess);
        }
        $prodcatIdsArr = FatUtility::int(FatApp::getPostedData('prodcat_ids'));
        if (empty($prodcatIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        foreach ($prodcatIdsArr as $prodcatId) {
            if (1 > $prodcatId) {
                continue;
            }
            $this->markAsDeleted($prodcatId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($prodcat_id)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        if (1 > $prodcat_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $prodCateObj = new ProductCategory($prodcat_id);
        if (!$prodCateObj->canRecordMarkDelete($prodcat_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* Sub-Categories have products[ */
        $categoriesHaveProducts = $prodCateObj->categoriesHaveProducts($this->adminLangId);

        $srch = ProductCategory::getSearchObject(true, $this->adminLangId, false);
        $srch->addCondition('m.prodcat_parent', '=', $prodcat_id);
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addMultipleFields(array("m.prodcat_id"));
        if ($categoriesHaveProducts) {
            $srch->addCondition('m.prodcat_id', 'in', $categoriesHaveProducts);
        }
        $rs = $srch->getResultSet();
        if ($srch->recordCount() > 0) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Products_are_associated_with_its_sub-categories_so_we_are_not_able_to_delete_this_category', $this->adminLangId));
        }
        /* ] */

        $prodCateObj->assignValues(array(ProductCategory::tblFld('deleted') => 1));
        if (!$prodCateObj->save()) {
            FatUtility::dieJsonError($prodCateObj->getError());
        }
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditProductCategories();

        $post=FatApp::getPostedData();
        if (!empty($post)) {
            $prodCateObj = new ProductCategory();
            if (!$prodCateObj->updateOrder($post['prodcat'])) {
                Message::addErrorMessage($prodCateObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            ProductCategory::updateCatOrderCode();
            $this->set('msg', Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
            //FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Order_Updated_Successfully',$this->adminLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
    }

    public function autocomplete()
    {
        if (!FatUtility::isAjaxCall()) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post=FatApp::getPostedData();
        $search_keyword='';
        if (!empty($post["keyword"])) {
            $search_keyword=urldecode($post["keyword"]);
        }

        $prodCateObj = new ProductCategory();
        $categories = $prodCateObj->getProdCatAutoSuggest($search_keyword, 10, $this->adminLangId);

        $json = array();
        $matches=$categories;

        /* $prodCateObj = new ProductCategory();
        $categories = $prodCateObj->getProdCatTreeStructure(0,'',0,''); */

        /* if (!empty($search_keyword)){
        $matches = array();
        foreach($categories as $k=>$v) {
          if(!(stripos($v, $search_keyword) === false)) {
                    $matches[$k] = $v;
          }
        }
        } */

        foreach ($matches as $key => $val) {
            $json[] = array(
            'prodcat_id' => $key,
            'prodcat_identifier'      => strip_tags(html_entity_decode($val, ENT_QUOTES, 'UTF-8'))
            );
        }

        /* $sort_order = array();
        foreach ($json as $key => $value) {
        $sort_order[$key] = $value['prodcat_identifier'];
        }
        array_multisort($sort_order, SORT_ASC, $json);
        echo json_encode(array_slice($json,0,10)); */
        echo json_encode($json);
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();
        switch ($action) {
            case 'index':
                $nodes[] = array('title'=>Labels::getLabel('LBL_Root_Categories', $this->adminLangId), 'href'=>CommonHelper::generateUrl('ProductCategories'));
                if (isset($parameters[0]) && $parameters[0] > 0) {
                    $parent=FatUtility::int($parameters[0]);
                    if ($parent>0) {
                        $cntInc=1;
                        $prodCateObj = new ProductCategory();
                        $category_structure = $prodCateObj->getCategoryStructure($parent);
                        $category_structure = array_reverse($category_structure);
                        foreach ($category_structure as $catKey => $catVal) {
                            if ($cntInc<count($category_structure)) {
                                $nodes[] = array('title'=>$catVal["prodcat_identifier"], 'href'=>CommonHelper::generateUrl('ProductCategories', 'index', array($catVal['prodcat_id'])));
                            } else {
                                $nodes[] = array('title'=>$catVal["prodcat_identifier"]);
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

    public function links_autocomplete()
    {
        $prodCatObj = new ProductCategory();
        $post = FatApp::getPostedData();
        $arr_options = $prodCatObj->getProdCatTreeStructureSearch(0, $this->adminLangId, $post['keyword']);
        $json = array();
        foreach ($arr_options as $key => $product) {
            $json[] = array(
            'id'     => $key,
            'name'  => strip_tags(html_entity_decode($product, ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getDisplayScreenName()
    {
        $screenTypesArr = applicationConstants::getDisplaysArr($this->adminLangId);
        return array( 0 => '' ) + $screenTypesArr;
    }
}
