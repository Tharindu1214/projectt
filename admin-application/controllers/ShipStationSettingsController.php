<?php
class ShipStationSettingsController extends ShippingSettingsController
{
    private $keyName = "shipstation_shipping";
    
    public function index()
    {
        $shippingSettings = $this->getShippingSettings($this->keyName);
        
        $frm = $this->getForm();
        $frm->fill($shippingSettings);
        
        $this->set('frm', $frm);
        $this->set('shippingMethod', $this->keyName);
        $this->_template->render(false, false);        
    }
    
    public function setup()
    {
        $frm = $this->getForm();
        $this->setUpShippingSettings($frm, $this->keyName);        
    }
        
    private function getForm() 
    {        
        $frm = new Form('frmShippingMethods');    
        
        $fld = $frm->addTextBox(Labels::getLabel("LBL_Shipstation_Api_key", $this->adminLangId), 'shipstation_api_key');
        $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Please_enter_your_shipstation_Api_Key_here.", $this->adminLangId)."</small>";
        
        $fld = $frm->addTextBox(Labels::getLabel("LBL_Shipstation_Api_Secret_key", $this->adminLangId), 'shipstation_api_secret_key');
        $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Please_enter_your_shipstation_api_Secret_Key_here.", $this->adminLangId)."</small>";
        
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));        
        return $frm;
        
        
        /* $paymentGatewayStatus = Orders::getPaymentGatewayStatusArr($this->adminLangId);	
        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_(Initial)',$this->adminLangId),'order_status_initial',$paymentGatewayStatus)->requirement->setRequired(true);
        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_(Pending)',$this->adminLangId),'order_status_pending',$paymentGatewayStatus)->requirement->setRequired(true);
        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_(Processed)',$this->adminLangId),'order_status_processed',$paymentGatewayStatus)->requirement->setRequired(true);
        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_(Completed)',$this->adminLangId),'order_status_completed',$paymentGatewayStatus)->requirement->setRequired(true);
        $frm->addSelectBox(Labels::getLabel('LBL_Order_Status_(Others)',$this->adminLangId),'order_status_others',$paymentGatewayStatus)->requirement->setRequired(true);
		
        $frm->addSubmitButton('&nbsp;','btn_submit',Labels::getLabel('LBL_Save_Changes',$this->adminLangId));
        return $frm; */
    }
}
