<?php
class PayuMoneySettingsController extends PaymentSettingsController
{
    
    private $keyName="PayuMoney";
    
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
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Key', $this->adminLangId), 'merchant_key');
        $frm->addRequiredField(Labels::getLabel('LBL_Salt', $this->adminLangId), 'salt');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
    
}
