<?php
class TransferbankSettingsController extends PaymentSettingsController
{
    private $keyName = "Transferbank";
    
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
        $frm->addTextArea(Labels::getLabel('LBL_Bank_Details', $this->adminLangId), 'bank_details');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
