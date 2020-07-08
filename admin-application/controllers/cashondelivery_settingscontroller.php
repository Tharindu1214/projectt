<?php
class Cashondelivery_settingsController extends PaymentmethodsController
{
    
    private $key_name="CASH_ON_DELIVERY";
    
    
    protected function getSettingsForm() 
    {
        global $payment_status_arr;
        $frm = new Form('frmPaymentMethods', 'frmPaymentMethods');
        $frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
        $fld=$frm->addTextArea(Labels::getLabel('LBL_COD_(cash_on_delivery)_Note', $this->adminLangId), 'cod_note');
        $fld->html_after_field='<small>'.Labels::getLabel('LBL_Please_enter_details_here', $this->adminLangId).'<small>';
        $fld->requirements()->setRequired();
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
        $frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
    
    function default_action() 
    {
        $pmObj=new Paymentsettings($this->key_name);
        if (!$payment_settings=$pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $frm = $this->getSettingsForm();
        $frm->fill($payment_settings);
        $post = Syspage::getPostedVar();
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(!$frm->validate($post)) {
                Message::addErrorMessage($frm->getValidationErrors());
            }else{
                if (!$pmObj->saveSetting($post)) {
                    Message::addErrorMessage($pmObj->getError());
                    //break;
                }
                Message::addMessage(Labels::getLabel('LBL_Payment_method_details_added/updated_successfully.', $this->adminLangId));
                Utilities::reloadPage();
            }
            $frm->fill($post);
        }
           $this->set('frm', $frm);
        $this->set('payment_settings', $payment_settings);
        $this->_template->render(true, true);
    }
    
    
}