<?php
class BraintreeSettingsController extends PaymentSettingsController
{

    private $keyName="Braintree";

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
        $frm->addRequiredField(Labels::getLabel('LBL_MerchantId', $this->adminLangId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Public_Key', $this->adminLangId), 'public_key');
        $frm->addRequiredField(Labels::getLabel('LBL_Private_Key', $this->adminLangId), 'private_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

}
