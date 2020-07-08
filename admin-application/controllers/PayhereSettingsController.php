<?php
class PayhereSettingsController extends PaymentSettingsController
{
    private $keyName = "Payhere";
    
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
        $frm->addRequiredField(Labels::getLabel('LBL_MERCHANT_ID', $this->adminLangId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_MERCHANT_SECRET', $this->adminLangId), 'secret_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
