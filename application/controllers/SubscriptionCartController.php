<?php
class SubscriptionCartController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl());
        }
    }

    public function index()
    {
        $this->_template->addCss('css/cart.css');
        $sCartObj = new SubscriptionCart();
        $subscriptionArr = $sCartObj->getSubscription($this->siteLangId);
        if (count($subscriptionArr)==0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('seller', 'packages'));
        }
        $this->_template->render();
    }
    public function listing()
    {
        $templateName = 'subscription-cart/listing.php';

        $sCartObj = new SubscriptionCart();
        $subscriptionArr = $sCartObj->getSubscription($this->siteLangId);

        if ($subscriptionArr) {
            $cartSummary = $sCartObj->getSubscriptionCartFinancialSummary($this->siteLangId);


            /* $PromoCouponsFrm = $this->getPromoCouponsForm($this->siteLangId); */
            $this->set('subscriptionArr', $subscriptionArr);

            /* $this->set('PromoCouponsFrm', $PromoCouponsFrm ); */
            $this->set('cartSummary', $cartSummary);
        }
        $this->_template->render(false, false, $templateName);
    }
    public function add()
    {
        $post = FatApp::getPostedData();
        if (false == $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl());
        }
        $json = array();
        $spplan_id = FatApp::getPostedData('spplan_id', FatUtility::VAR_INT, 0);

        if ($spplan_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Plan_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = new SellerPackagePlansSearch($this->siteLangId);
        $srch->addCondition(SellerPackagePlans::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        $srch->addCondition(SellerPackagePlans::DB_TBL_PREFIX.'id', '=', $spplan_id);
        $srch->addMultipleFields(
            array(
            'spplan_id' )
        );
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $sellerPlanRow = $db->fetch($rs);
        if (!$sellerPlanRow || $sellerPlanRow['spplan_id'] != $spplan_id) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $spplan_id = $sellerPlanRow['spplan_id'];
        /* Subscription Downgrade And Upgrade Check check[ */
        if (!UserPrivilege ::canSellerUpgradeOrDowngradePlan(UserAuthentication::getLoggedUserId(), $spplan_id, $this->siteLangId)) {
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */





        $subsObj = new SubscriptionCart();

        $subsObj->add($spplan_id);
        $subsObj->adjustPreviousPlan($this->siteLangId);


        Message::addMessage(Labels::getLabel('MSG_Success_Subscription_cart_add', $this->siteLangId));

        $this->set('msg', Labels::getLabel("MSG_Subscription_Package_Selected", $this->siteLangId));

        $this->set('success_msg', CommonHelper::renderHtml(Message::getHtml()));


        $this->_template->render(false, false, 'json-success.php', false, false);
    }
    private function getPromoCouponsForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmPromoCoupons');
        $frm->addTextBox(Labels::getLabel('LBL_Coupon_code', $langId), 'coupon_code', '', array('placeholder'=>Labels::getLabel('LBL_Enter_Your_code', $langId)));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply', $langId));
        return $frm;
    }

    public function remove()
    {
        $post = FatApp::getPostedData();
        if (false == $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl());
        }

        if (!isset($post['key'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $sCartObj = new SubscriptionCart();
        if (!$sCartObj->remove($post['key'])) {
            Message::addMessage($sCartObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel("MSG_Item_removed_successfully", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
