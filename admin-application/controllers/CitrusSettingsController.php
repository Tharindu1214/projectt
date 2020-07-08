<?php
class CitrusSettingsController extends PaymentSettingsController
{
    private $keyName = "Citrus";
    
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
        $frm->addRequiredField(Labels::getLabel('LBL_Vanity_Url', $this->adminLangId), 'merchant_vanity_url');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Key', $this->adminLangId), 'merchant_access_key');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $this->adminLangId), 'merchant_secret_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
