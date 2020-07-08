<?php
class AmazonSettingsController extends PaymentSettingsController
{
    private $keyName = "Amazon";
    
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
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Id', $this->adminLangId), 'amazon_merchantId');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Key', $this->adminLangId), 'amazon_accessKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $this->adminLangId), 'amazon_secretKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Client_Id', $this->adminLangId), 'amazon_clientId');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
