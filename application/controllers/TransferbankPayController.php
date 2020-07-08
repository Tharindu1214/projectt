<?php
class TransferbankPayController extends PaymentController
{
    private $keyName="TransferBank";

    public function charge($orderId)
    {
        $pmObj = new PaymentSettings($this->keyName);
        if (!$paymentSettings = $pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        } elseif ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);
            $this->set('paymentAmount', $paymentAmount);
        } else {
            $this->set('error', Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }

    public function send($orderId)
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if ($orderInfo) {
            $cartObj=new Cart();
            $cartObj->clear();
            $cartObj->updateUserCart();
            $comment  = Labels::getLabel('MSG_PAYMENT_INSTRUCTIONS', $this->siteLangId) . "\n\n";
            $comment .= $paymentSettings["bank_details"] . "\n\n";
            $comment .= Labels::getLabel('MSG_PAYMENT_NOTE', $this->siteLangId);
            $orderPaymentObj->addOrderPaymentComments($comment);
            $json['redirect'] = CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
        } else {
            $json['error'] = 'Invalid Request.';
        }
        echo json_encode($json);
    }

    private function getPaymentForm($orderId)
    {
        $frm = new Form('frmPaymentForm', array('id'=>'frmPaymentForm','action'=> CommonHelper::generateUrl('TransferbankPay', 'send', array($orderId)), 'class' =>"form form--normal"));

        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $frm->addHtml('', 'htmlNote', Labels::getLabel('MSG_Bank_Transfer_Note', $this->siteLangId));
        $frm->addHtml('', 'htmlNote', '<div class="bank--details">'.nl2br($paymentSettings["bank_details"]).'</div>');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Confirm_Order', $this->siteLangId), array('id'=>'button-confirm'));
        return $frm;
    }

    /* public function charge_for_wallet($recharge_txn_id){
    $pmObj = new PPCPaymentsettings($this->keyName);
    if (!$paymentSettings = $pmObj->getPaymentSettings()){
    Message::addErrorMessage($pmObj->getError());
    Utilities::redirectUserReferer();
    }
    $wrObj = new WalletRecharge($recharge_txn_id);
    $paymentAmount = $wrObj->getPaymentGatewayAmount();
    $recharge_txn_info = $wrObj->getWalletRechargePrimaryinfo();
    if ($recharge_txn_info && $recharge_txn_info["payment_status"] == 0){
    $frm = $this->getWalletPaymentForm($recharge_txn_id);
    $this->set('frm', $frm);
    $this->set('paymentAmount', $paymentAmount);
    }else{
    $this->set('error', Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED'));
    }
    $this->set('recharge_txn_info', $recharge_txn_info);
    $this->_template->render(true,false);
    }
    */

    /* public function send_wallet_recharge($recharge_txn_id) {
    Message::addMessage(Utilities::getLabel('M_transfer_fund_bank'));
    Utilities::redirectUser(Utilities::generateUrl('account', 'credits'));
    } */

    /* private function getWalletPaymentForm($recharge_txn_id){
    $frm=new Form('frmPaymentForm','frmPaymentForm');
    $frm->setRequiredStarWith('x');
    $frm->setValidatorJsObjectName('system_validator');
    $frm->setExtra('class="siteForm" validator="system_validator" ');
    $frm->setAction(Utilities::generateUrl('TransferBank_pay','send_wallet_recharge',array($recharge_txn_id)));
    $frm->captionInSameCell(true);
    $frm->setFieldsPerRow(1);
    $pmObj=new PPCPaymentsettings($this->keyName);
    $paymentSettings=$pmObj->getPaymentSettings();
    $fld=$frm->addHtml('', 'htmlNote',Utilities::getLabel('M_Bank_Transfer_Note'));
    $fld->merge_caption=true;
    $fld=$frm->addHtml('', 'htmlNote','<div class="alert alert-info">'.nl2br($paymentSettings["bank_details"]).'</div>');
    $fld->merge_caption=true;
    $frm->addSubmitButton('','btn_submit',Utilities::getLabel('L_Return_Back_to_My_Account'),'button-confirm');
    $frm->setJsErrorDisplay('afterfield');
    return $frm;
    } */
}
