<?php
class PaymentSettingsController extends AdminBaseController
{
    
    public function getPaymentSettings($keyName)
    {
        $pmObj = new PaymentSettings($keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        
        if (!$paymentSettings) {
            Message::addErrorMessage($pmObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        return $paymentSettings;
    }
    
    public function setUpPaymentSettings($frm, $keyName)
    {
        
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {            
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $pmObj = new PaymentSettings($keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        
        if (!$paymentSettings) {
            Message::addErrorMessage($pmObj->getError());
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        $psObj = new PaymentSettings($keyName);
        if(!$psObj->saveSettings($post)) {
            Message::addErrorMessage($psObj->getError());
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }
}
