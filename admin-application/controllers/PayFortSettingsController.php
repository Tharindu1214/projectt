<?php
class PayFortSettingsController extends PaymentSettingsController
{
    private $keyName = "PayFort";
    
    public function index()
    {        
        $paymentSettings = $this->getPaymentSettings($this->keyName);
        
        $frm = $this->getForm();
        $frm->fill($paymentSettings);
        
        $this->set('frm', $frm);
        $this->set('paymentMethod', $this->keyName);
        $this->_template->render(false, false);        
    }
    
    
    public function setup()
    {
        $frm = $this->getForm();
        $this->setUpPaymentSettings($frm, $this->keyName);        
    }
        
    private function getForm() 
    {        
        $frm = new Form('frmPaymentMethods');    
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Identifier', $this->adminLangId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Code', $this->adminLangId), 'access_code');
        $frm->addSelectBox(Labels::getLabel('LBL_SHA_Type', $this->adminLangId), 'sha_type', array( 'sha128' => 'SHA-128', 'sha256' => 'SHA-256', 'sha512' => 'SHA-512' ), 'sha512')->requirements()->setRequired();
        $frm->addRequiredField(Labels::getLabel('LBL_SHA_Request_Phrase', $this->adminLangId), 'sha_request_phrase');
        $frm->addRequiredField(Labels::getLabel('LBL_SHA_Response_Phrase', $this->adminLangId), 'sha_response_phrase');        
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
