<?php
class AuthorizeAimSettingsController extends PaymentSettingsController
{
    private $keyName = "AuthorizeAim";

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
        $frm->addRequiredField(Labels::getLabel('LBL_Login_ID', $this->adminLangId), 'login_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Transaction_Key', $this->adminLangId), 'transaction_key');
        // $frm->addTextBox(Labels::getLabel('LBL_MD5_Hash', $this->adminLangId), 'md5_hash');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
