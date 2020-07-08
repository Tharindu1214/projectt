<?php
class ConfirmPayController extends MyAppController
{
    public function charge($orderId ='')
    {
        $isAjaxCall = FatUtility::isAjaxCall();

        if (!$orderId || ((isset($_SESSION['shopping_cart']["order_id"]) && $orderId != $_SESSION['shopping_cart']["order_id"])&& (isset($_SESSION['subscription_shopping_cart']["order_id"]))  && $orderId != $_SESSION['subscription_shopping_cart']["order_id"])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }

        if (!UserAuthentication::isUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }

        $user_id = UserAuthentication::getLoggedUserId();

        $orderObj = new Orders();
        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $orderId);
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        if ($orderId == $_SESSION['subscription_shopping_cart']["order_id"]) {
            $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        } else {
            $srch->addCondition('order_type', '=', Orders::ORDER_PRODUCT);
        }
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        if (!$orderInfo) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentFinancials = $orderObj->getOrderPaymentFinancials($orderId);

        if ($orderPaymentFinancials['order_payment_gateway_charge']>0) {
            FatApp::redirectUser(CommonHelper::generateUrl('Custom', 'paymentFailure', array($orderId)));
        }
        if ($orderPaymentFinancials["order_payment_gateway_charge"] == 0) {
            $orderPaymentObj = new OrderPayment($orderId);
            if (!$orderPaymentObj->chargeFreeOrder()) {
                Message::addErrorMessage($orderPaymentObj->getError());
                if ($isAjaxCall) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                CommonHelper::redirectUserReferer();
            }
        }
        if ($orderId == $_SESSION['subscription_shopping_cart']["order_id"]) {
            $scartObj = new SubscriptionCart();
            $scartObj->clear();
            $scartObj->updateUserSubscriptionCart();
        } else {
            $cartObj = new Cart();
            $cartObj->clear();
            $cartObj->updateUserCart();
        }



        if ($isAjaxCall) {
            $this->set('redirectUrl', CommonHelper::generateUrl('Custom', 'paymentSuccess', array($orderId)));
            $this->set('msg', Labels::getLabel("MSG_Payment_from_wallet_made_successfully", $this->siteLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
        FatApp::redirectUser(CommonHelper::generateUrl('Custom', 'paymentSuccess', array($orderId)));
    }
}
