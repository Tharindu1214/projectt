<?php
class PayFortStartSettingsController extends PaymentSettingsController
{
    private $keyName = "PayFortStart";
    
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
        
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Transaction_Mode', $this->adminLangId), 'transaction_mode', array(0 => "Test/Sandbox", "1" => "Live"), 'transaction_mode')->requirements()->setRequired();
        $frm->addRequiredField(Labels::getLabel('LBL_API_Secret_Key', $this->adminLangId), 'secret_key');
        $frm->addRequiredField(Labels::getLabel('LBL_API_Open_Key', $this->adminLangId), 'open_key');

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
