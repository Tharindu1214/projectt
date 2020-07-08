<?php
class PaytmSettingsController extends PaymentSettingsController
{
    
    private $keyName="paytm";
    
    public function index() 
    {
        $paymentSettings = $this->getPaymentSettings($this->keyName);
        $frm = $this->getSettingsForm();
        $frm->fill($paymentSettings);
        $this->set('frm', $frm);
        $this->set('paymentMethod', $this->keyName);
        $this->_template->render(false, false);
    }
    
    public function setup()
    {
        $frm = $this->getSettingsForm();
        $this->setUpPaymentSettings($frm, $this->keyName);        
    }
    
    private function getSettingsForm() 
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_ID', $this->adminLangId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Key', $this->adminLangId), 'merchant_key');
        $frm->addRequiredField(Labels::getLabel('LBL_Website', $this->adminLangId), 'merchant_website');
        $frm->addRequiredField(Labels::getLabel('LBL_Channel_ID', $this->adminLangId), 'merchant_channel_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Industry_Type_ID', $this->adminLangId), 'merchant_industry_type');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
    
}
