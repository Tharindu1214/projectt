<?php
class TwocheckoutSettingsController extends PaymentSettingsController
{
    private $keyName = "Twocheckout";
    
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
        
        $paymentTypesArr = array(
        'HOSTED' => 'Hosted Checkout',
        'API' => 'Payment API'
        );
        $frm->addRadioButtons(Labels::getLabel('LBL_Payment_Type', $this->adminLangId), 'payment_type', $paymentTypesArr, 'HOSTED', array('class'=>'box--scroller'));
        $frm->addRequiredField(Labels::getLabel('LBL_Seller_ID', $this->adminLangId), 'sellerId');
        $frm->addRequiredField(Labels::getLabel('LBL_Publishable_Key', $this->adminLangId), 'publishableKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Private_Key', $this->adminLangId), 'privateKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Word', $this->adminLangId), 'hashSecretWord');
        
        $frm->addHTML(
            'Remember', '&nbsp;', 'In case of <strong>Hosted Checkout</strong>, Admin must set <strong>Direct Return (URL)</strong> to <strong>Header Redirect</strong> and 
		<strong>Approved URL</strong> to <strong>'.CommonHelper::generateFullUrl('twocheckout_pay', 'callback', array(), CONF_WEBROOT_URL).'</strong> under <strong>2Checkout Accounts</strong> Section.<br/><br/>'
        );
        
        
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
