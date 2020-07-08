<?php
class CcavenueSettingsController extends PaymentSettingsController
{
    private $keyName = "Ccavenue";
    
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
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_ID', $this->adminLangId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Code', $this->adminLangId), 'access_code');
        $frm->addTextBox(Labels::getLabel('LBL_Working_Key', $this->adminLangId), 'working_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
