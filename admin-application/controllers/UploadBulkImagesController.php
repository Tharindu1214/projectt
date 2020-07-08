<?php
class UploadBulkImagesController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->objPrivilege->canUploadBulkImages();
        $this->langId = $this->adminLangId;
    }

    public function index()
    {
        $srchFrm = $this->getSearchForm();
        $this->set("frmSearch", $srchFrm);
        $this->_template->render();
    }

    private function getUploadForm()
    {
        $frm = new Form('uploadBulkImages', array('id'=>'uploadBulkImages'));

        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $this->langId), 'bulk_images', array('id' => 'bulk_images', 'accept' => '.zip' ));
        $fldImg->requirement->setRequired(true);
        $fldImg->setFieldTagAttribute('onChange', '$("#uploadFileName").html(this.value)');
        $fldImg->htmlBeforeField='<div class="filefield"><span class="filename" id="uploadFileName"></span>';
        $fldImg->htmlAfterField='<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $this->langId).'</label></div>';

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->langId));
        return $frm;
    }

    public function uploadForm()
    {
        $uploadFrm = $this->getUploadForm();
        $this->set("frm", $uploadFrm);
        $this->_template->render(false, false);
    }

    public function upload()
    {
        $frm = $this->getUploadForm();
        $post = $frm->getFormDataFromArray($_FILES);

        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Data', $this->langId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileName = $_FILES['bulk_images']['name'];
        $tmpName = $_FILES['bulk_images']['tmp_name'];

        $uploadBulkImgobj = new UploadBulkImages();
        $savedFile = $uploadBulkImgobj->upload($fileName, $tmpName);
        if (false === $savedFile) {
            Message::addErrorMessage($uploadBulkImgobj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $path = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH;
        $filePath = AttachedFile::FILETYPE_BULK_IMAGES_PATH . $savedFile;

        $msg = '<br>'.str_replace('{path}', '<br><b>'.$filePath.'</b>', Labels::getLabel('MSG_Your_uploaded_files_path_will_be:_{path}', $this->langId));
        $msg = Labels::getLabel('MSG_Uploaded_Successfully.', $this->langId) .' '.$msg;
        $json = [
            "msg" => $msg,
            "path" => base64_encode($path . $savedFile)
        ];
        FatUtility::dieJsonSuccess($json);
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');

        $frm->addTextBox(Labels::getLabel('LBL_User', $this->adminLangId), 'user', '');

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'afile_record_id');
        return $frm;
    }

    public function search()
    {
        $db = FatApp::getDb();
        $srchFrm = $this->getSearchForm();

        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $obj = new UploadBulkImages();
        $srch = $obj->bulkMediaFileObject();

        $keyword = FatApp::getPostedData('keyword', null, '');

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('afile_physical_path', 'like', '%' . $keyword . '%');
        }

        $uploadedBy = FatApp::getPostedData('afile_record_id');
        if ('' != $uploadedBy) {
            $srch->addCondition('afile_record_id', '=', $uploadedBy);
        }

        $srch->addOrder('afile_id', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->adminLangId, true));
        $this->set('adminLangId', $this->adminLangId);
        $this->_template->render(false, false);
    }

    public function removeDir($directory)
    {
        $directory = CONF_UPLOADS_PATH . base64_decode($directory) ;
        $obj = new UploadBulkImages();
        $msg = $obj->deleteSingleBulkMediaDir($directory);
        FatUtility::dieJsonSuccess($msg);
    }

    public function deleteSelected()
    {
        $uploadDirsArr = FatApp::getPostedData('uploadDirs');

        if (empty($uploadDirsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new UploadBulkImages();
        foreach ($uploadDirsArr as $uploadDir) {
            if (empty($uploadDir)) {
                continue;
            }
            $directory = CONF_UPLOADS_PATH . base64_decode($uploadDir).'/' ;
            $msg = $obj->deleteSingleBulkMediaDir($directory);
        }
        $this->set('msg', $msg);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoCompleteSellerJson()
    {
        $pagesize = applicationConstants::PAGE_SIZE;
        $post = FatApp::getPostedData();
        $sellersObj = Product::getSellers(array( "product_seller_id", "IFNULL(credential_username,'Admin') as seller", "credential_email" ));
        $sellersObj->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'product_seller_id = afile_record_id AND afile_type = '.AttachedFile::FILETYPE_BULK_IMAGES);
        $sellersObj->addOrder('seller');
        if ('' != $post['keyword']) {
            $sellersObj->addCondition('credential_username', 'like', '%' . $post['keyword'] . '%');
            $sellersObj->addCondition('credential_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        }
        $sellersObj->setPageSize($pagesize);
        $rs = $sellersObj->getResultSet();
        $sellers = FatApp::getDb()->fetchAll($rs);
        die(json_encode($sellers));
    }

    public function downloadPathsFile($path)
    {
        if (empty($path)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
        }
        $filesPathArr = UploadBulkImages::getAllFilesPath(base64_decode($path));
        if (!empty($filesPathArr) && 0 < count($filesPathArr)) {
            $headers[] = ['File Path', 'File Name'];
            $filesPathArr = array_merge($headers, $filesPathArr);
            CommonHelper::convertToCsv($filesPathArr, time().'.csv');
            exit;
        }
        Message::addErrorMessage(Labels::getLabel('MSG_No_File_Found', $this->adminLangId));
        CommonHelper::redirectUserReferer();
    }
}
