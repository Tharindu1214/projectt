<?php
class DatabaseBackupRestoreController extends AdminBaseController
{
    
    public function __construct($action)
    {
        parent::__construct($action);
        $this->adminLangId = CommonHelper::getLangId();
    }
    public function index()
    {
        $this->objPrivilege->canViewDatabaseBackupView();
        $settingsObj = new Settings();
        $backup_frm = $this->getBackupForm();
        $upload_frm = $this->getUploadForm();
        $post = FatApp::getPostedData();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['submit_backup'])) {
            $this->objPrivilege->canEditDatabaseBackupView();
            $settingsObj = new Settings();
            $settingsObj->backupDatabase(trim($post["name"]));
            Message::addMessage(Labels::getLabel('LBL_Database_backup_on_Server_created_Successfully', $this->adminLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('DatabaseBackupRestore'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['submit_upload'])) {
            $this->objPrivilege->canEditDatabaseBackupView();
            $ext = strrchr($_FILES['file']['name'], '.');
            if (strtolower($ext) != '.sql') {
                Message::addErrorMessage(Labels::getLabel('LBL_File_type_unsupporte._Please_upload_Sql_file', $this->adminLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('DatabaseBackupRestore'));
            }
            if (!self::saveFile($_FILES['file']['tmp_name'], $_FILES['file']['name'], CONF_DB_BACKUP_DIRECTORY . '/')) {
                Message::addErrorMessage(Labels::getLabel('LBL_File_could_not_be_saved', $this->adminLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('DatabaseBackupRestore'));
            }
            Message::addMessage(Labels::getLabel('LBL_Database_Uploaded_Successfully', $this->adminLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('DatabaseBackupRestore'));
        }
        
        $this->set('backup_frm', $backup_frm);
        $this->set('upload_frm', $upload_frm);
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewDatabaseBackupView();
        $settingsObj = new Settings();
        $files_array = $settingsObj->getDatabaseDirectoryFiles();
        $this->set("arr_listing", $files_array);        
        $this->_template->render(false, false);    
    }
    
    public function download($file)
    {
        $this->objPrivilege->canViewDatabaseBackupView();
        $this->objPrivilege->canEditDatabaseBackupView();
        if (isset($file) and trim($file) != "") {
            $settingsObj = new Settings();
            if (!$settingsObj->download_file($file)) {
                Message::addErrorMessage($settingsObj->getError());
                FatApp::redirectUser(CommonHelper::generateUrl('DatabaseBackupRestore'));
            }
        }
    }

    public function restore($file)
    {
        $this->objPrivilege->canViewDatabaseBackupView();
        $this->objPrivilege->canEditDatabaseBackupView();
        
        if (isset($file) and trim($file) != "") {
            $settingsObj = new Settings();
            $settingsObj->restoreDatabase($file);
            Message::addMessage(Labels::getLabel('LBL_Database_restored_successfully', $this->adminLangId));
        }
        $this->set('msg', Labels::getLabel('LBL_Database_restored_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');    
    }

    public function delete($file)
    {
        $this->objPrivilege->canViewDatabaseBackupView();
        $this->objPrivilege->canEditDatabaseBackupView();
        
        if (isset($file) and trim($file) != "") {
            unlink(CONF_DB_BACKUP_DIRECTORY_FULL_PATH . $file);
        }
        $this->set('msg', Labels::getLabel('LBL_Database_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');    
    }

    protected function getBackupForm()
    {
        $frm = new Form('frmdatabaseBackup', array('id'=>'frmdatabaseBackup'));
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_File_Name', $this->adminLangId), 'name');
        $fld = $frm->addSubmitButton('', 'submit_backup', Labels::getLabel('LBL_Backup_on_Server', $this->adminLangId));
        return $frm;
    }

    protected function getUploadForm()
    {
        $frm = new Form('frmdatabaseUpload', array('id'=>'frmdatabaseUpload'));
        $fld = $frm->addFileUpload(Labels::getLabel('LBL_DB_upload', $this->adminLangId), 'file', array('autocomplete'=>'off'));
        $fld->html_before_field = '<div class="filefield"><span class="filename"></span>';
        $fld->html_after_field = '<label class="filelabel">'.Labels::getLabel('LBL_Download_File', $this->adminLangId).'</label></div>';
        $fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'submit_upload', Labels::getLabel('LBL_Upload_on_server', $this->adminLangId));
        return $frm;
    }
    
    public static function saveFile($fl, $name)
    {
        $dir = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
        if (!is_writable($dir)) {
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_Directory_%s_is_not_writable', $langId), $dir));
            return false;
        }
        $fname = preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $name);
        while (file_exists($dir.$fname)) {
            /* $fname = rand(10, 999999).'_'.$fname; */
            $fname = microtime().'_'.$fname;
        }
        if (!copy($fl, $dir.$fname)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Could_not_save_file', CommonHelper::getLangId()));
            return false;
        }
        return true;
    }
}
