<?php
class ShippingSettingsController extends AdminBaseController
{
    
    public function getShippingSettings($keyName)
    {
        $shipObj = new ShippingSettings($keyName);
        $shippingSettings = $shipObj->getShippingSettings();
        
        if (!$shippingSettings) {
            Message::addErrorMessage($shipObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        return $shippingSettings;
    }
    
    public function setUpShippingSettings($frm, $keyName)
    {
        
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {            
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $shipObj = new ShippingSettings($keyName);
        $shippingSettings = $shipObj->getShippingSettings();
        
        if (!$shippingSettings) {
            Message::addErrorMessage($shipObj->getError());
            FatUtility::dieWithError(Message::getHtml());    
        }
        //To Validate Credentails	
        
        include_once CONF_INSTALLATION_PATH . 'library/APIs/shipstatation/ship.class.php';
        $apiKey = $post['shipstation_api_key'];
        $apiSecret = $post['shipstation_api_secret_key']; 
        $Ship = new Ship();
        if (!$Ship->validateShipstationAccount($apiKey, $apiSecret)) {
            Message::addErrorMessage($Ship->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $psObj = new ShippingSettings($keyName);
        if(!$psObj->saveSettings($post)) {
            Message::addErrorMessage($psObj->getError());
            FatUtility::dieWithError(Message::getHtml());    
        }
        
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }
}
