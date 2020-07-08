<?php
class SystemRestoreController extends AdminBaseController
{
    
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();        
    }
    function index()
    {
        if (!AdminPrivilege::isAdminSuperAdmin($this->admin_id)) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $settingsObj = new Settings();
        
        $restore_point_frm = $this->getRestorePointForm();
        $post = FatApp::getPostedData();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['submit_restore_point'])) {
            if ($settingsObj->compress(CONF_INSTALLATION_PATH . "restore/", CONF_INSTALLATION_PATH . "restore-backups/")) {
                $settingsObj->findandDeleteOldestFile(CONF_INSTALLATION_PATH . "restore-backups/");
                $target = CONF_INSTALLATION_PATH . "restore/user-uploads";
                $source = CONF_UPLOADS_PATH;
                CommonHelper::fullCopy($source, $target);
                $settingsObj->backupDatabase("database", false, false, CONF_INSTALLATION_PATH . "restore/database");
                Message::addMessage("Restore Point Updated Successfully!!");
                /* FatApp::redirectUser(CommonHelper::generateUrl('systemRestore')); */
            }
        }
        $this->set('restore_point_frm', $restore_point_frm);
        $this->_template->render();
    }
    
    function updateSetting($val)
    {
        $record = new Configurations();    
        if (!$record->update(array("CONF_AUTO_RESTORE_ON" => $val))) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());            
        }
        $this->set('msg', 'Setting Updated Successfully!!');
        $this->_template->render(false, false, 'json-success.php');
    }

    protected function getRestorePointForm()
    {
        $frm = new Form('frmdatabaseBackup', array('id'=>'frmdatabaseBackup'));
        $frm->setJsErrorDisplay('afterfield');
        $fld = $frm->addSubmitButton('', 'submit_restore_point', 'Create Restore Point');
        $fld->htmlAfterField = '<small><strong>Notes</strong>: On clicking the above button, system restore point will change to current database & uploads folder and current restore folder will be moved to backup folder with current date attached to it.</small>';
        $status = 0;
        $active = "active";
        if(!FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) ) {
            $active = '';
            $status = 1;
        }
        $frm->addHtml(
            '', 'auto_restore', '<div class="field-set"><div class="caption-wraper"><label class="field_label"><strong>Auto Restore</strong></label></div><div class="field-wraper"><div class="field_cover"><label class="statustab '.$active.'" onclick="toggleStatus(event,this)" id="'.$status.'">
		  <span data-off="Active" data-on="Inactive" class="switch-labels"></span>
		  <span class="switch-handles"></span>
		</label></div></div></div>'
        );
        return $frm;
    }
}
