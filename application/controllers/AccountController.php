<?php
class AccountController extends LoggedUserController
{
    public function __construct($action)
    {
        parent::__construct($action);
        if (!isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = '';
            if (User::isBuyer()  || User::isSigningUpBuyer()) {
                $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';
            } elseif (User::isSeller() || User::isSigningUpForSeller()) {
                $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';
            } elseif (User::isAdvertiser() || User::isSigningUpAdvertiser()) {
                $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';
            } elseif (User::isAffiliate()  || User::isSigningUpAffiliate()) {
                $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'AFFILIATE';
            }
        }
        $this->set('bodyClass', 'is--dashboard');
    }

    public function index()
    {
        if (UserAuthentication::isGuestUserLogged()) {
            FatApp::redirectUser(CommonHelper::generateUrl('home'));
        }
        
        switch ($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) {
            case 'B':
                FatApp::redirectUser(CommonHelper::generateUrl('buyer'));
                break;            
            case 'S':
                FatApp::redirectUser(CommonHelper::generateUrl('seller'));
                break;            
            case 'Ad':
                FatApp::redirectUser(CommonHelper::generateUrl('advertiser'));
                break;            
            case 'AFFILIATE':
                FatApp::redirectUser(CommonHelper::generateUrl('affiliate'));
                break;                        
            default:
                FatApp::redirectUser(CommonHelper::generateUrl(''));
                break;
        }

        /* $user = new User(UserAuthentication::getLoggedUserId());
        $this->set('data', $user->getProfileData());
        $this->_template->render(true,false); */
    }

    public function viewSupplierRequest($requestId)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $requestId = FatUtility::int($requestId);

        if ($userId < 1 || $requestId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Account', 'SupplierApprovalForm'));
            //FatUtility::dieJsonError( Message::getHtml() );
        }

        $userObj = new User($userId);
        $srch = $userObj->getUserSupplierRequestsObj($requestId);
        $srch->addFld('tusr.*');

        $rs = $srch->getResultSet();
        /* if(!$rs){
        Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST',$this->siteLangId));
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        $supplierRequest = FatApp::getDb()->fetch($rs);

        if (!$supplierRequest || $supplierRequest['usuprequest_id'] != $requestId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Account', 'SupplierApprovalForm'));
        }
        $maxAttempts = FatApp::getConfig('CONF_MAX_SUPPLIER_REQUEST_ATTEMPT', FatUtility::VAR_INT, 3);
        if ($supplierRequest && $supplierRequest['usuprequest_attempts'] >= $maxAttempts) {
            $this->set('maxAttemptsReached', true);
        }


        $this->set('supplierRequest', $supplierRequest);
        $this->_template->render();
    }

    public function supplierApprovalForm($p = '')
    {
        if (!User::canViewSupplierTab()) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_FOR_SUPPLIER_DASHBOARD', $this->siteLangId));
            if (User::isBuyer()) {
                FatApp::redirectUser(CommonHelper::generateUrl('buyer'));
            } elseif (User::isAdvertiser()) {
                FatApp::redirectUser(CommonHelper::generateUrl('advertiser'));
            } elseif (User::isAffiliate()) {
                FatApp::redirectUser(CommonHelper::generateUrl('affiliate'));
            } else {
                FatApp::redirectUser(CommonHelper::generateUrl('Account', 'ProfileInfo'));
            }
        }
        $userId = UserAuthentication::getLoggedUserId();

        $userObj = new User($userId);
        $srch = $userObj->getUserSupplierRequestsObj();
        $srch->addFld(array('usuprequest_attempts','usuprequest_id'));

        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $supplierRequest = FatApp::getDb()->fetch($rs);
        $maxAttempts = FatApp::getConfig('CONF_MAX_SUPPLIER_REQUEST_ATTEMPT', FatUtility::VAR_INT, 3);
        if ($supplierRequest && $supplierRequest['usuprequest_attempts'] >= $maxAttempts) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_have_already_consumed_max_attempts', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('account', 'viewSupplierRequest', array($supplierRequest["usuprequest_id"])));
        }

        if ($supplierRequest && ($p!="reopen")) {
            FatApp::redirectUser(CommonHelper::generateUrl('account', 'viewSupplierRequest', array($supplierRequest["usuprequest_id"])));
        }

        $data = array('id'=>$supplierRequest['usuprequest_id']);
        $approvalFrm = $this->getSupplierForm();
        $approvalFrm->fill($data);

        $this->set('approvalFrm', $approvalFrm);
        $this->_template->render();
    }

    public function setupSupplierApproval()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $error_messages = array();
        $fieldIdsArr = array();
        /* check if maximum attempts reached [ */
        $userObj = new User($userId);
        $srch = $userObj->getUserSupplierRequestsObj();
        $srch->addFld(array('usuprequest_attempts','usuprequest_id'));

        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $supplierRequest = FatApp::getDb()->fetch($rs);
        $maxAttempts = FatApp::getConfig('CONF_MAX_SUPPLIER_REQUEST_ATTEMPT', FatUtility::VAR_INT, 3);
        if ($supplierRequest && $supplierRequest['usuprequest_attempts'] >= $maxAttempts) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_have_already_consumed_max_attempts', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $frm = $this->getSupplierForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $supplier_form_fields = $userObj->getSupplierFormFields($this->siteLangId);

        foreach ($supplier_form_fields as $field) {
            $fieldIdsArr[] = $field['sformfield_id'];
            //$fieldCaptionsArr[] = $field['sformfield_caption'];
            if ($field['sformfield_required'] && empty($post["sformfield_".$field['sformfield_id']])) {
                $error_messages[]=sprintf(Labels::getLabel('MSG_Label_Required', $this->siteLangId), $field['sformfield_caption']);
            }
        }

        if (!empty($error_messages)) {
            Message::addErrorMessage($error_messages);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $reference_number = $userId.'-'.time();
        $data = array_merge($post, array("user_id"=>$userId,"reference"=>$reference_number,'fieldIdsArr'=>$fieldIdsArr ));

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!$supplier_request_id = $userObj->addSupplierRequestData($data, $this->siteLangId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('MSG_details_not_saved', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1)) {
            $approval_request = 1;
            $msg = Labels::getLabel('MSG_Your_seller_approval_form_request_sent', $this->siteLangId);
        } else {
            $approval_request = 0;
            $msg = Labels::getLabel('MSG_Your_application_is_approved', $this->siteLangId);
        }

        if (!$this->notifyAdminSupplierApproval($userObj, $data, $approval_request)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_SELLER_APPROVAL_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_USER,
        'notification_record_id' => $userObj->getMainTableRecordId(),
        'notification_user_id' => $userId,
        'notification_label_key' => ($approval_request)?Notification::NEW_SUPPLIER_APPROVAL_NOTIFICATION:Notification::NEW_SELLER_APPROVED_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('supplier_request_id', $supplier_request_id);
        $this->set('msg', $msg);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadSupplierFormImages()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $post = FatApp::getPostedData();

        if (empty($post)) {
            /* Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
        }
        $field_id = $post['field_id'];

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->isUploadedFile($_FILES['file']['tmp_name'])) {
            FatUtility::dieJsonError($fileHandlerObj->getError());
        }

        $fileHandlerObj->deleteFile($fileHandlerObj::FILETYPE_SELLER_APPROVAL_FILE, $userId, 0, $field_id);

        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $fileHandlerObj::FILETYPE_SELLER_APPROVAL_FILE,
            $userId,
            $field_id,
            $_FILES['file']['name'],
            -1,
            $unique_record = false
        )
        ) {
            /* Message::addErrorMessage($fileHandlerObj->getError()); */
            FatUtility::dieJsonError($fileHandlerObj->getError());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('msg', /* $_FILES['file']['name'].' '. */Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeEmailPassword()
    {
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render();
    }

    public function changePasswordForm()
    {
        $frm = $this->getChangePasswordForm();

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function updatePassword()
    {
        $pwdFrm = $this->getChangePasswordForm();
        $post = $pwdFrm->getFormDataFromArray(FatApp::getPostedData());

        if ($post === false) {
            $message = Labels::getLabel(current($pwdFrm->getValidationErrors()), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $userObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $userObj->getUserSearchObj(array('user_id','credential_password'));
        $rs = $srch->getResultSet();

        $data = FatApp::getDb()->fetch($rs, 'user_id');

        if ($data === false) {
            $message = Labels::getLabel('MSG_Invalid_User', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if ($data['credential_password'] != UserAuthentication::encryptPassword($post['current_password'])) {
            $message = Labels::getLabel('MSG_YOUR_CURRENT_PASSWORD_MIS_MATCHED', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if (!$userObj->setLoginPassword($post['new_password'])) {
            $message = Labels::getLabel('MSG_Password_could_not_be_set', $this->siteLangId). $userObj->getError();
            FatUtility::dieJsonError($message);
        }

        $this->set('msg', Labels::getLabel('MSG_Password_changed_successfully', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function setPrefferedDashboard($dasboardType)
    {
        $dasboardType  = FatUtility::int($dasboardType);

        switch ($dasboardType) {
            case User::USER_BUYER_DASHBOARD:
                if (!User::canViewBuyerTab()) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                break;
            case User::USER_SELLER_DASHBOARD:
                if (!User::canViewSupplierTab()) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                break;
            case User::USER_ADVERTISER_DASHBOARD:
                if (!User::canViewAdvertiserTab()) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                break;
            case User::USER_AFFILIATE_DASHBOARD:
                if (!User::canViewAffiliateTab()) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                break;
            default:
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
                break;
        }

        $arr = array('user_preferred_dashboard' => $dasboardType);

        $userId = UserAuthentication::getLoggedUserId();
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        $userObj->assignValues($arr);
        if (!$userObj->save()) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function credits()
    {
        $frm = $this->getCreditsSearchForm($this->siteLangId);

        $userId = UserAuthentication::getLoggedUserId();

        $canAddMoneyToWallet = true;
        if (User::isAffiliate()) {
            $canAddMoneyToWallet = false;
        }
        $codMinWalletBalance = -1;
        if (User::isSeller() && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] =='S') {
            $shop_cod_min_wallet_balance = Shop::getAttributesByUserId($userId, 'shop_cod_min_wallet_balance');
            if ($shop_cod_min_wallet_balance > -1) {
                $codMinWalletBalance = $shop_cod_min_wallet_balance;
            } elseif (FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1) > -1) {
                $codMinWalletBalance = FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1);
            }
        }
        $txnObj = new Transactions();
        $accountSummary = $txnObj->getTransactionSummary($userId);
        $this->set('codMinWalletBalance', $codMinWalletBalance);
        $this->set('frmSrch', $frm);
        $this->set('accountSummary', $accountSummary);
        $this->set('frmRechargeWallet', $this->getRechargeWalletForm($this->siteLangId));
        $this->set('canAddMoneyToWallet', $canAddMoneyToWallet);
        $this->_template->render();
    }

    public function creditsInfo()
    {
        $this->set('userWalletBalance', User::getUserBalance(UserAuthentication::getLoggedUserId()));
        $this->set('userTotalWalletBalance', User::getUserBalance(UserAuthentication::getLoggedUserId(), false, false));
        $this->set('promotionWalletToBeCharged', Promotion::getPromotionWalleToBeCharged(UserAuthentication::getLoggedUserId()));
        $this->set('withdrawlRequestAmount', User::getUserWithdrawnRequestAmount(UserAuthentication::getLoggedUserId()));

        if (false ===  MOBILE_APP_API_CALL) {
            $this->_template->render(false, false);
        }
    }

    public function setUpWalletRecharge()
    {
        $minimumRechargeAmount = 1;
        $frm = $this->getRechargeWalletForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $order_net_amount = $post['amount'];
        if ($order_net_amount < $minimumRechargeAmount) {
            $str = Labels::getLabel("LBL_Recharge_amount_must_be_greater_than_{minimumrechargeamount}", $this->siteLangId);
            $str = str_replace("{minimumrechargeamount}", CommonHelper::displayMoneyFormat($minimumRechargeAmount, true, true), $str);
            LibHelper::dieJsonError($str);
        }
        $orderData = array();
        $order_id = isset($_SESSION['wallet_recharge_cart']["order_id"]) ? $_SESSION['wallet_recharge_cart']["order_id"] : false;
        $orderData['order_type']= Orders::ORDER_WALLET_RECHARGE;

        $orderData['userAddresses'] = array(); //No Need of it
        $orderData['order_id'] = $order_id;
        $orderData['order_user_id'] = $loggedUserId;
        $orderData['order_is_paid'] = Orders::ORDER_IS_PENDING;
        $orderData['order_date_added'] = date('Y-m-d H:i:s');

        /* order extras[ */
        $orderData['extra'] = array(
        'oextra_order_id'    =>    $order_id,
        'order_ip_address'    =>    $_SERVER['REMOTE_ADDR']
        );

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $orderData['extra']['order_forwarded_ip'] = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $orderData['extra']['order_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $orderData['extra']['order_user_agent'] = '';
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $orderData['extra']['order_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $orderData['extra']['order_accept_language'] = '';
        }
        /* ] */

        $languageRow = Language::getAttributesById($this->siteLangId);
        $orderData['order_language_id'] =  $languageRow['language_id'];
        $orderData['order_language_code'] =  $languageRow['language_code'];

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);
        $orderData['order_currency_id'] =  $currencyRow['currency_id'];
        $orderData['order_currency_code'] =  $currencyRow['currency_code'];
        $orderData['order_currency_value'] =  $currencyRow['currency_value'];

        $orderData['order_user_comments'] =  '';
        $orderData['order_admin_comments'] =  '';

        $orderData['order_shippingapi_id'] = 0;
        $orderData['order_shippingapi_code'] = '';
        $orderData['order_tax_charged'] = 0;
        $orderData['order_site_commission'] = 0;
        $orderData['order_net_amount'] = $order_net_amount;
        $orderData['order_wallet_amount_charge'] = 0;

        $orderData['orderLangData'] = array();
        $orderObj = new Orders();
        if ($orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
            $order_id = $orderObj->getOrderId();
        } else {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($orderObj->getError());
            }
            Message::addErrorMessage($orderObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        if (true ===  MOBILE_APP_API_CALL) {
            /* Payment Methods[ */
            $pmSrch = PaymentMethods::getSearchObject($this->siteLangId);
            $pmSrch->doNotCalculateRecords();
            $pmSrch->doNotLimitRecords();
            $pmSrch->addMultipleFields(array('pmethod_id', 'IFNULL(pmethod_name, pmethod_identifier) as pmethod_name', 'pmethod_code', 'pmethod_description'));
            $pmSrch->addCondition('pmethod_code', '!=', 'CashOnDelivery');

            $pmRs = $pmSrch->getResultSet();
            $paymentMethods = FatApp::getDb()->fetchAll($pmRs);
            /* ] */
            $this->set('paymentMethods', $paymentMethods);
            $this->set('order_id', $order_id);
            $this->set('orderType', Orders::ORDER_WALLET_RECHARGE);
            $this->_template->render();
        }
        $this->set('redirectUrl', CommonHelper::generateUrl('WalletPay', 'Recharge', array($order_id)));
        $this->set('msg', Labels::getLabel('MSG_Redirecting', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function creditSearch()
    {
        $frm = $this->getCreditsSearchForm($this->siteLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        //$page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);


        $userId = UserAuthentication::getLoggedUserId();
        $debit_credit_type = FatApp::getPostedData('debit_credit_type', FatUtility::VAR_INT, -1);
        $dateOrder = FatApp::getPostedData('date_order', FatUtility::VAR_STRING, "DESC");

        $srch = Transactions::getUserTransactionsObj($userId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('utxn.utxn_date', $dateOrder);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cond = $srch->addCondition('utxn.utxn_order_id', 'like', '%'.$keyword.'%');
            $cond->attachCondition('utxn.utxn_op_id', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('utxn.utxn_comments', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('concat("TN-" ,lpad( utxn.`utxn_id`,7,0))', 'like', '%'.$keyword.'%', 'OR', true);
        }

        $fromDate = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($fromDate)) {
            $cond = $srch->addCondition('utxn.utxn_date', '>=', $fromDate);
        }

        $toDate = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($toDate)) {
            $cond = $srch->addCondition('cast( utxn.`utxn_date` as date)', '<=', $toDate, 'and', true);
        }
        if ($debit_credit_type > 0) {
            switch ($debit_credit_type) {
                case Transactions::CREDIT_TYPE:
                    $srch->addCondition('utxn.utxn_credit', '>', '0');
                    $srch->addCondition('utxn.utxn_debit', '=', '0');
                    break;

                case Transactions::DEBIT_TYPE:
                    $srch->addCondition('utxn.utxn_debit', '>', '0');
                    $srch->addCondition('utxn.utxn_credit', '=', '0');
                    break;
            }
        }
        $records = array();

        $rs = $srch->getResultSet();

        $records = FatApp::getDb()->fetchAll($rs, 'utxn_id');

        $this->set('arrListing', $records);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('statusArr', Transactions::getStatusArr($this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->creditsInfo();
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function requestWithdrawal()
    {
        $frm = $this->getWithdrawalForm($this->siteLangId);

        $userId = UserAuthentication::getLoggedUserId();
        $balance = User::getUserBalance($userId);
        $lastWithdrawal = User::getUserLastWithdrawalRequest($userId);

        if ($lastWithdrawal && (strtotime($lastWithdrawal["withdrawal_request_date"] . "+".FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS", FatUtility::VAR_INT, 0)." days") - time()) > 0) {
            $nextWithdrawalDate = date('d M,Y', strtotime($lastWithdrawal["withdrawal_request_date"] . "+".FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS", FatUtility::VAR_INT, 0)." days"));
            Message::addErrorMessage(sprintf(Labels::getLabel('MSG_Withdrawal_Request_Date', $this->siteLangId), FatDate::format($lastWithdrawal["withdrawal_request_date"]), FatDate::format($nextWithdrawalDate), FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")));
            FatUtility::dieWithError(Message::getHtml());
        }

        $minimumWithdrawLimit = FatApp::getConfig("CONF_MIN_WITHDRAW_LIMIT", FatUtility::VAR_INT, 0);
        if ($balance < $minimumWithdrawLimit) {
            Message::addErrorMessage(sprintf(Labels::getLabel('MSG_Withdrawal_Request_Minimum_Balance_Less', $this->siteLangId), CommonHelper::displayMoneyFormat($minimumWithdrawLimit)));
            FatUtility::dieWithError(Message::getHtml());
        }

        $userObj = new User($userId);
        $data = $userObj->getUserBankInfo();

        $data['uextra_payment_method'] = User::AFFILIATE_PAYMENT_METHOD_CHEQUE;

        if (User::isAffiliate()) {
            $userExtraData = User::getUserExtraData($userId, array('uextra_payment_method', 'uextra_cheque_payee_name', 'uextra_paypal_email_id'));
            $uextra_payment_method = isset($userExtraData['uextra_payment_method']) ? $userExtraData['uextra_payment_method'] : User::AFFILIATE_PAYMENT_METHOD_CHEQUE;
            $data = array_merge($data, $userExtraData);
            $data['uextra_payment_method'] = $uextra_payment_method;
            $this->set('uextra_payment_method', $uextra_payment_method);
        }

        $frm->fill($data);

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setupRequestWithdrawal()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $balance = User::getUserBalance($userId);
        $lastWithdrawal = User::getUserLastWithdrawalRequest($userId);

        if ($lastWithdrawal && (strtotime($lastWithdrawal["withdrawal_request_date"] . "+".FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS", FatUtility::VAR_INT, 0)." days") - time()) > 0) {
            $nextWithdrawalDate = date('d M,Y', strtotime($lastWithdrawal["withdrawal_request_date"] . "+".FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")." days"));

            $message = sprintf(Labels::getLabel('MSG_Withdrawal_Request_Date', $this->siteLangId), FatDate::format($lastWithdrawal["withdrawal_request_date"]), FatDate::format($nextWithdrawalDate), FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS"));
            FatUtility::dieJsonError($message);
        }

        $minimumWithdrawLimit = FatApp::getConfig("CONF_MIN_WITHDRAW_LIMIT", FatUtility::VAR_INT, 0);
        if ($balance < $minimumWithdrawLimit) {
            $message = sprintf(Labels::getLabel('MSG_Withdrawal_Request_Minimum_Balance_Less', $this->siteLangId), CommonHelper::displayMoneyFormat($minimumWithdrawLimit));
            FatUtility::dieJsonError($message);
        }

        $frm = $this->getWithdrawalForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }

        if (($minimumWithdrawLimit > $post["withdrawal_amount"])) {
            $message = sprintf(Labels::getLabel('MSG_Your_withdrawal_request_amount_is_less_than_the_minimum_allowed_amount_of_%s', $this->siteLangId), CommonHelper::displayMoneyFormat($minimumWithdrawLimit));
            FatUtility::dieJsonError($message);
        }

        $maximumWithdrawLimit = FatApp::getConfig("CONF_MAX_WITHDRAW_LIMIT", FatUtility::VAR_INT, 0);
        if (($maximumWithdrawLimit < $post["withdrawal_amount"])) {
            $message = sprintf(Labels::getLabel('MSG_Your_withdrawal_request_amount_is_greater_than_the_maximum_allowed_amount_of_%s', $this->siteLangId), CommonHelper::displayMoneyFormat($maximumWithdrawLimit));
            FatUtility::dieJsonError($message);
        }

        if (($post["withdrawal_amount"] > $balance)) {
            $message = Labels::getLabel('MSG_Withdrawal_Request_Greater', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $accountNumber = FatApp::getPostedData('ub_account_number', FatUtility::VAR_STRING, 0);

        if ((string)$accountNumber != $post['ub_account_number']) {
            $message = Labels::getLabel('MSG_Invalid_Account_Number', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }


        $userObj = new User($userId);
        if (!$userObj->updateBankInfo($post)) {
            $message = Labels::getLabel($userObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $withdrawal_payment_method = FatApp::getPostedData('uextra_payment_method', FatUtility::VAR_INT, 0);

        $withdrawal_payment_method = ($withdrawal_payment_method > 0 && array_key_exists($withdrawal_payment_method, User::getAffiliatePaymentMethodArr($this->siteLangId))) ? $withdrawal_payment_method  : User::AFFILIATE_PAYMENT_METHOD_BANK;
        $withdrawal_cheque_payee_name = '';
        $withdrawal_paypal_email_id = '';
        $withdrawal_bank = '';
        $withdrawal_account_holder_name = '';
        $withdrawal_account_number = '';
        $withdrawal_ifc_swift_code = '';
        $withdrawal_bank_address = '';
        $withdrawal_comments = $post['withdrawal_comments'];

        switch ($withdrawal_payment_method) {
            case User::AFFILIATE_PAYMENT_METHOD_CHEQUE:
                $withdrawal_cheque_payee_name = $post['uextra_cheque_payee_name'];
                break;
            case User::AFFILIATE_PAYMENT_METHOD_BANK:
                $withdrawal_bank = $post['ub_bank_name'];
                $withdrawal_account_holder_name = $post['ub_account_holder_name'];
                $withdrawal_account_number = $post['ub_account_number'];
                $withdrawal_ifc_swift_code = $post['ub_ifsc_swift_code'];
                $withdrawal_bank_address = $post['ub_bank_address'];
                break;
            case User::AFFILIATE_PAYMENT_METHOD_PAYPAL:
                $withdrawal_paypal_email_id = $post['uextra_paypal_email_id'];
                break;
        }


        $post['withdrawal_payment_method'] = $withdrawal_payment_method;
        $post['withdrawal_cheque_payee_name'] = $withdrawal_cheque_payee_name;
        $post['withdrawal_paypal_email_id'] = $withdrawal_paypal_email_id;

        $post['ub_bank_name'] = $withdrawal_bank;
        $post['ub_account_holder_name'] = $withdrawal_account_holder_name;
        $post['ub_account_number'] = $withdrawal_account_number;
        $post['ub_ifsc_swift_code'] = $withdrawal_ifc_swift_code;
        $post['ub_bank_address'] = $withdrawal_bank_address;

        $post['withdrawal_comments'] = $withdrawal_comments;

        if (!$withdrawRequestId = $userObj->addWithdrawalRequest(array_merge($post, array("ub_user_id"=>$userId)), $this->siteLangId)) {
            $message = Labels::getLabel($userObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendWithdrawRequestNotification($withdrawRequestId, $this->siteLangId, "A")) {
            $message = Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_WITHDRAWAL_REQUEST,
        'notification_record_id' => $withdrawRequestId,
        'notification_user_id' => UserAuthentication::getLoggedUserId(),
        'notification_label_key' => Notification::WITHDRAWL_REQUEST_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $this->set('msg', Labels::getLabel('MSG_Withdraw_request_placed_successfully', $this->siteLangId));

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProfileImage()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage();
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $userId)) {
            $message = Labels::getLabel($fileHandlerObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $userId)) {
            $message = Labels::getLabel($fileHandlerObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $this->set('msg', Labels::getLabel('MSG_Profile_Image_Removed_Successfully', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $userImgUpdatedOn = User::getAttributesById($userId, 'user_img_updated_on');
            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
            $userImage = FatCache::getCachedUrl(CommonHelper::generateFullUrl('Image', 'user', array($userId,'thumb',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');

            $data = array('userImage' => $userImage);

            $this->set('data', $data);
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function userProfileImage($userId, $sizeType = '', $cropedImage = false)
    {
        $default_image = 'user_deafult_image.jpg';
        $userId = UserAuthentication::getLoggedUserId();
        $recordId = FatUtility::int($userId);

        $file_row = false;
        if ($cropedImage == true) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $recordId);
        }

        if ($file_row == false) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $recordId);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function profileInfo()
    {
        if (true ===  MOBILE_APP_API_CALL) {
            $userId = UserAuthentication::getLoggedUserId(true);
            $userImgUpdatedOn = User::getAttributesById($userId, 'user_img_updated_on');
            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);

            $bankInfo = $this->bankInfo();
            $personalInfo = $this->personalInfo();
            $personalInfo['userImage'] = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'user', array($userId,'mini',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            $this->set('personalInfo', empty($personalInfo) ? (object)array() : $personalInfo);
            $this->set('bankInfo', empty($bankInfo) ? (object)array() : $bankInfo);
            $this->set('privacyPolicyLink', FatApp::getConfig('CONF_PRIVACY_POLICY_PAGE', FatUtility::VAR_STRING, ''));
            $this->_template->render();
        }

        $this->_template->addJs('js/jquery.form.js');
        $this->_template->addJs('js/cropper.js');
        $this->_template->addCss('css/cropper.css');
        $this->includeDateTimeFiles();

        /* $langs = Language::getAllNames();
        CommonHelper::printArray($langs); die(); */

        $userId = UserAuthentication::getLoggedUserId();

        $data = User::getAttributesById($userId, array('user_preferred_dashboard', 'user_registered_initially_for'));
        if ($data === false) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $showSellerActivateButton = false;
        if (!User::canAccessSupplierDashboard() && $data['user_registered_initially_for'] == User::USER_TYPE_SELLER) {
            $showSellerActivateButton = true;
        }

        $this->set('showSellerActivateButton', $showSellerActivateButton);
        $this->set('userPreferredDashboard', $data['user_preferred_dashboard']);
        $this->_template->render();
    }

    public function personalInfo()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $userObj = new User($userId);
        $srch = $userObj->getUserSearchObj();
        $srch->addMultipleFields(array('u.*', 'country_name', 'state_name'));
        $srch->joinTable('tbl_countries_lang', 'LEFT JOIN', 'countrylang_country_id = user_country_id and countrylang_lang_id = ' .$this->siteLangId);
        $srch->joinTable('tbl_states_lang', 'LEFT JOIN', 'statelang_state_id = user_state_id and statelang_lang_id = ' .$this->siteLangId);
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs, 'user_id');
        if (true ===  MOBILE_APP_API_CALL) {
            return $data;
        }
        $this->set('info', $data);
        $this->_template->render(false, false);
    }

    public function bankInfo()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $userObj = new User($userId);
        $data = $userObj->getUserBankInfo();
        if (true ===  MOBILE_APP_API_CALL) {
            return $data;
        }
        $this->set('info', $data);
        $this->_template->render(false, false);
    }

    public function profileInfoForm()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->getProfileInfoForm();
        $imgFrm = $this->getProfileImageForm();
        $stateId = 0;

        $userObj = new User($userId);
        $srch = $userObj->getUserSearchObj();
        $srch->addMultipleFields(array('u.*'));
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs, 'user_id');
        if (empty($data['user_phone'])) {
            $data['user_phone'] = '';
        }
        if (User::isAffiliate()) {
            $userExtraData = User::getUserExtraData($userId, array('uextra_company_name', 'uextra_website'));
            $userExtraData = ($userExtraData) ? $userExtraData : array();
            $data = array_merge($userExtraData, $data);
        }

        if ($data['user_dob'] == "0000-00-00") {
            $dobFld = $frm->getField('user_dob');
            $dobFld->requirements()->setRequired(true);
        }

        $frm->fill($data);
        $stateId = $data['user_state_id'];

        $mode = 'Add';
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $userId);
        if ($file_row != false) {
            $mode = 'Edit';
        }

        $this->set('data', $data);
        $this->set('frm', $frm);
        $this->set('imgFrm', $imgFrm);
        $this->set('mode', $mode);
        $this->set('stateId', $stateId);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function profileImageForm()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $imgFrm = $this->getProfileImageForm();
        $mode = 'Add';
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $userId);
        if ($file_row != false) {
            $mode = 'Edit';
        }
        $this->set('mode', $mode);
        $this->set('imgFrm', $imgFrm);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function uploadProfileImage()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            $message = Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $fileHandlerObj = new AttachedFile();
        $updatedAt = date('Y-m-d H:i:s');
        $uploadedTime = AttachedFile::setTimeParam($updatedAt);

        if ($post['action'] == "demo_avatar") {
            if (!$fileHandlerObj->isUploadedFile($_FILES['user_profile_image']['tmp_name'])) {
                FatUtility::dieJsonError($fileHandlerObj->getError());
            }

            if (!$res = $fileHandlerObj->saveImage($_FILES['user_profile_image']['tmp_name'], AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $userId, 0, $_FILES['user_profile_image']['name'], -1, true)
            ) {
                $message = Labels::getLabel($fileHandlerObj->getError(), $this->siteLangId);
                FatUtility::dieJsonError($message);
            }

            if (true ===  MOBILE_APP_API_CALL) {
                $profileImg = FatCache::getCachedUrl(CommonHelper::generateFullUrl('Image', 'user', array($userId,'mini',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                $this->set('file', $profileImg);
            } else {
                $profileImg = FatCache::getCachedUrl(CommonHelper::generateFullUrl('Account', 'userProfileImage', array($userId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                $this->set('file', $profileImg);
            }
        }

        if ($post['action'] == "avatar") {
            if (!$fileHandlerObj->isUploadedFile($_FILES['user_profile_image']['tmp_name'])) {
                FatUtility::dieJsonError($fileHandlerObj->getError());
            }


            if (!$res = $fileHandlerObj->saveImage($_FILES['user_profile_image']['tmp_name'], AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $userId, 0, $_FILES['user_profile_image']['name'], -1, true)
            ) {
                $message = Labels::getLabel($fileHandlerObj->getError(), $this->siteLangId);
                FatUtility::dieJsonError($message);
            }

            if (isset($post['img_data'])) {
                $data = json_decode(stripslashes($post['img_data']));
                CommonHelper::crop($data, CONF_UPLOADS_PATH .$res, $this->siteLangId);
            }

            if (false ===  MOBILE_APP_API_CALL) {
                $profileImg = FatCache::getCachedUrl(CommonHelper::generateFullUrl('Account', 'userProfileImage', array($userId,'croped',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                $this->set('file', $profileImg);
            } else {
                $profileImg = FatCache::getCachedUrl(CommonHelper::generateFullUrl('Image', 'user', array($userId,'mini',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                $this->set('file', $profileImg);
            }
            $this->set('file', $profileImg);
        }
        User::setImageUpdatedOn($userId, $updatedAt);
        $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateProfileInfo()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->getProfileInfoForm();

        $post = FatApp::getPostedData();
        $post['user_phone'] = !empty($post['user_phone']) ? ValidateElement::convertPhone($post['user_phone']) : '';
        if (1 > count($post) && true ===  MOBILE_APP_API_CALL) {
            LibHelper::dieJsonError(Labels::getLabel("MSG_INVALID_REQUEST", $this->siteLangId));
        }

        /* CommonHelper::printArray($post);  */
        $user_state_id = FatUtility::int($post['user_state_id']);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            $message = Labels::getLabel(current($frm->getValidationErrors()), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if (strtotime($post['user_dob']) > time()) {
            $message = Labels::getLabel("MSG_Invalid_date_of_birth", $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $post['user_state_id'] = $user_state_id;

        if (isset($post['user_id'])) {
            unset($post['user_id']);
        }

        if ($post['user_dob'] == "0000-00-00" || $post['user_dob'] == "" || strtotime($post['user_dob']) == 0) {
            unset($post['user_dob']);
        }
        unset($post['credential_username']);
        unset($post['credential_email']);


        /* saving user extras[ */
        if (User::isAffiliate()) {
            $dataToSave = array(
            'uextra_user_id'        =>    $userId,
            'uextra_company_name'    =>    $post['uextra_company_name'],
            'uextra_website'        =>    CommonHelper::processUrlString($post['uextra_website'])
            );
            $dataToUpdateOnDuplicate = $dataToSave;
            unset($dataToUpdateOnDuplicate['uextra_user_id']);
            if (!FatApp::getDb()->insertFromArray(User::DB_TBL_USR_EXTRAS, $dataToSave, false, array(), $dataToUpdateOnDuplicate)) {
                $message = Labels::getLabel(Labels::getLabel("LBL_Details_could_not_be_saved!", $this->siteLangId), $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }

                Message::addErrorMessage($message);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('Account', 'ProfileInfo'));
            }
        }
        /* ] */


        $userObj = new User($userId);
        $userObj->assignValues($post);
        if (!$userObj->save()) {
            $message = Labels::getLabel($userObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function bankInfoForm()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (User::isAffiliate()) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getBankInfoForm();

        $userObj = new User($userId);
        $data = $userObj->getUserBankInfo();
        if ($data != false) {
            $frm->fill($data);
        }

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function settingsInfo()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->getSettingsForm();

        $userObj = new User($userId);
        $srch = $userObj->getUserSearchObj();
        $srch->addMultipleFields(array('u.*'));
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs, 'user_id');
        if ($data != false) {
            $frm->fill($data);
        }

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function updateBankInfo()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();
        if (1 > count($post) && true ===  MOBILE_APP_API_CALL) {
            LibHelper::dieJsonError(Labels::getLabel("MSG_INVALID_REQUEST", $this->siteLangId));
        }

        $frm = $this->getBankInfoForm();
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            $message = Labels::getLabel(current($frm->getValidationErrors()), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $accountNumber = FatApp::getPostedData('ub_account_number', FatUtility::VAR_STRING, 0);

        if ((string)$accountNumber != $post['ub_account_number']) {
            $message = Labels::getLabel('MSG_Invalid_Account_Number', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }


        $userObj = new User($userId);
        if (!$userObj->updateBankInfo($post)) {
            $message = Labels::getLabel($userObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateSettingsInfo()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->getSettingsForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        if (!$userObj->updateSettingsInfo($post)) {
            Message::addErrorMessage(Labels::getLabel($userObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeEmailForm()
    {
        $frm = $this->getChangeEmailForm();

        $this->set('frm', $frm);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function updateEmail()
    {
        $emailFrm = $this->getChangeEmailForm();
        $post = $emailFrm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            $message = $emailFrm->getValidationErrors();
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($message));
            }
            Message::addErrorMessage($message);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($post['new_email'] != $post['conf_new_email']) {
            $message = Labels::getLabel('MSG_New_email_confirm_email_does_not_match', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $userObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $userObj->getUserSearchObj(array('user_id','credential_password','credential_email','user_name'));
        $rs = $srch->getResultSet();

        if (!$rs) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $data = FatApp::getDb()->fetch($rs, 'user_id');

        if ($data === false) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if ($data['credential_password'] != UserAuthentication::encryptPassword($post['current_password'])) {
            $message = Labels::getLabel('MSG_YOUR_CURRENT_PASSWORD_MIS_MATCHED', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $arr = array(
        'user_name' => $data['user_name'],
        'user_email' => $data['credential_email'],
        'user_new_email' => $post['new_email']
        );

        if (!$this->userEmailVerification($userObj, $arr)) {
            $message = Labels::getLabel('MSG_ERROR_IN_SENDING_VERFICATION_EMAIL', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }


        $this->set('msg', Labels::getLabel('MSG_CHANGE_EMAIL_REQUEST_SENT_SUCCESSFULLY', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function moveToWishList($selProdId)
    {
        $defaultWishListId = $this->getDefaultWishListId();
        $this->addRemoveWishListProduct($selProdId, $defaultWishListId);
    }

    public function getDefaultWishListId()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $srch = UserWishList::getSearchObject($loggedUserId, true);
        $srch->addCondition('uwlist_default', '=', applicationConstants::YES);
        $srch->addMultipleFields(array( 'uwlist_id'));
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!empty($row)) {
            return $row['uwlist_id'];
        }

        $wListObj = new UserWishList();
        $data_to_save_arr['uwlist_title'] = Labels::getLabel('LBL_Default_list', $this->siteLangId);
        ;
        $data_to_save_arr['uwlist_added_on'] = date('Y-m-d H:i:s');
        $data_to_save_arr['uwlist_user_id'] = $loggedUserId;
        $data_to_save_arr['uwlist_default'] = 1;
        $wListObj->assignValues($data_to_save_arr);

        /* create new List[ */
        if (!$wListObj->save()) {
            Message::addErrorMessage($wListObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $uwlist_id = $wListObj->getMainTableRecordId();
        return $uwlist_id;
    }

    /* called from products listing page */
    public function viewWishList($selprod_id, $excludeWishList = 0)
    {
        $excludeWishList = FatUtility::int($excludeWishList);
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $this->getDefaultWishListId();
        $wishLists = UserWishList::getUserWishLists($loggedUserId, true, $excludeWishList);
        $frm = $this->getCreateWishListForm();
        $frm->fill(array('selprod_id' => $selprod_id));
        $this->set('frm', $frm);
        $this->set('wishLists', $wishLists);
        $this->set('selprod_id', $selprod_id);
        $this->_template->render(false, false);
    }

    public function setupWishList()
    {
        $frm = $this->getCreateWishListForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (false === $post) {
            $message = current($frm->getValidationErrors());
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $wListObj = new UserWishList();
        $data_to_save_arr = $post;
        $data_to_save_arr['uwlist_added_on'] = date('Y-m-d H:i:s');
        $data_to_save_arr['uwlist_user_id'] = UserAuthentication::getLoggedUserId();
        $wListObj->assignValues($data_to_save_arr);

        /* create new List[ */
        if (!$wListObj->save()) {
            $message = $wListObj->getError();
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $uwlp_uwlist_id = $wListObj->getMainTableRecordId();
        /* ] */

        $successMsg = Labels::getLabel('LBL_WishList_Created_Successfully', $this->siteLangId);
        /* Assign current product to newly created list[ */
        if ($uwlp_uwlist_id && $selprod_id) {
            if (!$wListObj->addUpdateListProducts($uwlp_uwlist_id, $selprod_id)) {
                Message::addMessage($successMsg);
                $msg = Labels::getLabel('LBL_Error_while_assigning_product_under_selected_list.');

                if (true ===  MOBILE_APP_API_CALL) {
                   LibHelper::dieJsonError($msg);
                }
                Message::addErrorMessage($msg);
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */

        //UserWishList
        $srch = UserWishList::getSearchObject($loggedUserId);
        $srch->joinTable(UserWishList::DB_TBL_LIST_PRODUCTS, 'LEFT OUTER JOIN', 'uwlist_id = uwlp_uwlist_id');
        $srch->addCondition('uwlp_selprod_id', '=', $selprod_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('uwlist_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        $productIsInAnyList = false;
        if ($row) {
            $productIsInAnyList = true;
        }

        $this->set('productIsInAnyList', $productIsInAnyList);
        $this->set('wish_list_id', $uwlp_uwlist_id);
        $this->set('msg', $successMsg);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function addRemoveWishListProductArr()
    {
        $selprod_id_arr = FatApp::getPostedData('selprod_id');
        $selprod_id_arr = !empty($selprod_id_arr) ? array_filter($selprod_id_arr) : array();

        $uwlist_id = FatApp::getPostedData('uwlist_id', FatUtility::VAR_INT, 0);

        if (empty($selprod_id_arr) || empty($uwlist_id)) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        foreach ($selprod_id_arr as $selprod_id) {
            $action = $this->updateWishList($selprod_id, $uwlist_id);
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateRemoveWishListProduct($selprodId, $wishListId)
    {
        $selprodIdArr = FatApp::getPostedData('selprod_id');
        $oldWishlistId = FatApp::getPostedData('uwlist_id', FatUtility::VAR_INT, 0);

        if (empty($selprodIdArr) || empty($oldWishlistId)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        foreach ($selprodIdArr as $selprodId) {
            $this->updateWishList($selprodId, $oldWishlistId);
            $isExists = UserWishList::getListProductsByListId($wishListId, $selprodId);
            if (empty($isExists)) {
                $this->updateWishList($selprodId, $wishListId);
            }
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function addRemoveWishListProduct($selprod_id, $wish_list_id, $rowAction = '')
    {
        $selprod_id = FatUtility::int($selprod_id);
        $wish_list_id = FatUtility::int($wish_list_id);
        $rowAction = ('' == $rowAction ? -1 : $rowAction);

        if (1 > $wish_list_id) {
            $wish_list_id = $this->getDefaultWishListId();
        }
        $loggedUserId = UserAuthentication::getLoggedUserId();

        if (1 > $selprod_id) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $action = $this->updateWishList($selprod_id, $wish_list_id, $rowAction);

        //UserWishList
        $srch = UserWishList::getSearchObject($loggedUserId);
        $srch->joinTable(UserWishList::DB_TBL_LIST_PRODUCTS, 'LEFT OUTER JOIN', 'uwlist_id = uwlp_uwlist_id');
        $srch->addCondition('uwlp_selprod_id', '=', $selprod_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('uwlist_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        $productIsInAnyList = false;
        if ($row) {
            $productIsInAnyList = true;
        }

        $this->set('productIsInAnyList', $productIsInAnyList);
        $this->set('action', $action);
        $this->set('wish_list_id', $wish_list_id);
        $this->set('totalWishListItems', Common::countWishList());

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateWishList($selprod_id, $wish_list_id, $rowAction = -1)
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();

        $row = false;

        $db = FatApp::getDb();
        $wListObj = new UserWishList();
        if (0 > $rowAction) {
            $srch = UserWishList::getSearchObject($loggedUserId);
            $wListObj->joinWishListProducts($srch);
            $srch->addMultipleFields(array('uwlist_id'));
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition('uwlp_selprod_id', '=', $selprod_id);
            $srch->addCondition('uwlp_uwlist_id', '=', $wish_list_id);

            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
        }

        $action = 'N'; //nothing happened
        if (!$row && (0 < $rowAction || 0 > $rowAction)) {
            if (!$wListObj->addUpdateListProducts($wish_list_id, $selprod_id)) {
                $message = Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
            $action = 'A'; //Added to wishlist
            $this->set('msg', Labels::getLabel('LBL_Product_Added_in_list_successfully', $this->siteLangId));
        } else {
            $uwlistIds = array();
            if (true ===  MOBILE_APP_API_CALL) {
                $srch = UserWishList::getSearchObject($loggedUserId);
                $srch->addMultipleFields(array('uwlist_id'));
                $rs = $srch->getResultSet();
                $row = $db->fetchAll($rs, 'uwlist_id');
                $uwlistIds = array_keys($row);
            } else {
                $uwlistIds[] = $wish_list_id;
            }
            $err = true;
            foreach ($uwlistIds as $uwlistId) {
                $err = false;
                if (!$db->deleteRecords(UserWishList::DB_TBL_LIST_PRODUCTS, array('smt'=>'uwlp_uwlist_id = ? AND uwlp_selprod_id = ?', 'vals'=>array($uwlistId, $selprod_id)))) {
                    $err = true;
                    break;
                }
            }

            if (true == $err) {
                $message = Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }

            $action = 'R'; //Removed from wishlist
            $this->set('msg', Labels::getLabel('LBL_Product_Removed_from_list_successfully', $this->siteLangId));
        }
        return $action;
    }

    public function wishlist()
    {
        $this->_template->addCss('css/slick.css');
        $this->_template->addJs('js/slick.js');
        $this->_template->addCss('css/product-detail.css');
        $this->_template->render();
    }

    public function wishListSearch()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $defaultWishListId = $this->getDefaultWishListId();

        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $wishLists[] = Product::getUserFavouriteProducts($loggedUserId, $this->siteLangId);
        } else {
            $wishLists = UserWishList::getUserWishLists($loggedUserId, false);
            if ($wishLists && false ===  MOBILE_APP_API_CALL) {
                $srchObj = new UserWishListProductSearch($this->siteLangId);
                $db = FatApp::getDb();
                foreach ($wishLists as &$wishlist) {
                    $srch = clone $srchObj;
                    $srch->joinSellerProducts();
                    $srch->joinProducts();
                    $srch->joinBrands();
                    $srch->joinSellers();
                    $srch->joinShops();
                    $srch->joinProductToCategory();
                    $srch->joinSellerSubscription($this->siteLangId, true);
                    $srch->addSubscriptionValidCondition();
                    $srch->joinSellerProductSpecialPrice();
                    $srch->joinFavouriteProducts($loggedUserId);
                    $srch->addCondition('uwlp_uwlist_id', '=', $wishlist['uwlist_id']);
                    $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
                    $srch->addCondition('selprod_active', '=', applicationConstants::YES);
                    $srch->setPageNumber(1);
                    $srch->setPageSize(4);
                    $srch->addMultipleFields(array( 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'product_id', 'IFNULL(product_name, product_identifier) as product_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock'));
                    $srch->addOrder('uwlp_added_on');
                    $srch->addGroupBy('selprod_id');
                    $rs = $srch->getResultSet();
                    $products = $db->fetchAll($rs);
                    $wishlist['products'] = $products;
                    $wishlist['totalProducts'] = $srch->recordCount();
                }
            }
        }

        /* $wishLists = array_merge($favouriteProducts,$wishLists); */

        $this->set('wishLists', $wishLists);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $frm = $this->getCreateWishListForm();
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function viewFavouriteItems()
    {
        $db = FatApp::getDb();
        $loggedUserId = UserAuthentication::getLoggedUserId();

        $favouriteListRow = Product::getUserFavouriteProducts($loggedUserId, $this->siteLangId);

        if (!$favouriteListRow) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('wishListRow', $favouriteListRow);
        $this->_template->render(false, false, 'account/favourite-list-items.php');
    }

    public function searchWishListItems()
    {
        $post = FatApp::getPostedData();
        $db = FatApp::getDb();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $uwlist_id = empty($post['uwlist_id']) ? 0 : FatUtility::int($post['uwlist_id']);
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        if (false ===  MOBILE_APP_API_CALL) {
            $wishListRow = UserWishList::getAttributesById($uwlist_id, array('uwlist_id'));
            if (!$wishListRow) {
                $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $srch = new UserWishListProductSearch($this->siteLangId);
        $srch->joinSellerProducts();
        $srch->joinProducts();
        $srch->joinBrands();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription($this->siteLangId, true);
        $srch->addSubscriptionValidCondition();
        $srch->joinSellerProductSpecialPrice();
        $srch->joinFavouriteProducts($loggedUserId);
        if (true ===  MOBILE_APP_API_CALL && 0 >= $uwlist_id) {
            $srch->joinWishLists();
            $srch->addCondition('uwlist_user_id', '=', $loggedUserId);
        } else {
            $srch->addCondition('uwlp_uwlist_id', '=', $uwlist_id);
        }
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_active', '=', applicationConstants::YES);
        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSellerProducts();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating');

        /* $favProductObj = new UserWishListProductSearch();
        $favProductObj->joinFavouriteProducts(); */


        // echo $srch->getQuery(); die;

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        /* groupby added, beacouse if same product is linked with multiple categories, then showing in repeat for each category[ */
        $srch->addGroupBy('selprod_id');
        /* ] */

        $srch->addMultipleFields(
            array( 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'product_id', 'prodcat_id', 'ufp_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','product_image_updated_on',
            'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand.brand_id', 'product_model',
            'IFNULL(brand_name, brand_identifier) as brand_name', 'IFNULL(splprice_price, selprod_price) AS theprice','splprice_display_list_price', 'splprice_display_dis_val','splprice_display_dis_type',
            'CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found', 'selprod_price', 'selprod_user_id', 'selprod_code', 'selprod_sold_count', 'selprod_condition', 'IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist','IFNULL(uwlp.uwlp_uwlist_id, 0) as uwlp_uwlist_id','ifnull(prod_rating,0) prod_rating', 'selprod_min_order_qty'  )
        );
        $srch->addOrder('uwlp_added_on', 'DESC');
        $rs = $srch->getResultSet();
        /* echo $srch->getQuery(); die; */
        $products = $db->fetchAll($rs);
        if (count($products)) {
            foreach ($products as &$arr) {
                $arr['options'] = SellerProduct::getSellerProductOptions($arr['selprod_id'], true, $this->siteLangId);
            }
        }
        /* $prodSrchObj = new ProductSearch();
        if( $products ){
        foreach($products as &$product){
        $moreSellerSrch = clone $prodSrchObj;
        $moreSellerSrch->addMoreSellerCriteria( $product['selprod_code'], $product['selprod_user_id'] );
        $moreSellerSrch->addMultipleFields(array('count(selprod_id) as totalSellersCount','MIN(theprice) as theprice'));
        $moreSellerSrch->addGroupBy('selprod_code');
        $moreSellerRs = $moreSellerSrch->getResultSet();
        $moreSellerRow = $db->fetch($moreSellerRs);
        $product['moreSellerData'] =  ($moreSellerRow) ? $moreSellerRow : array();
        }
        }
        */
        $this->set('products', $products);
        $this->set('showProductShortDescription', false);
        $this->set('showProductReturnPolicy', false);
        $this->set('colMdVal', 6);
        $this->set('page', $page);
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page-1)*$pageSize + 1 ;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $this->set('totalRecords', $totalRecords);
        $this->set('startRecord', $startRecord);
        $this->set('endRecord', $endRecord);
        $this->set('forPage', Labels::getLabel('LBL_Wishlist', $this->siteLangId));

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        if ($totalRecords > 0) {
            $this->set('html', $this->_template->render(false, false, 'products/products-list.php', true, false));
        } else {
            $this->set('html', $this->_template->render(false, false, '_partial/no-record-found.php', true, false));
        }
        $this->set('loadMoreBtnHtml', $this->_template->render(false, false, 'products/products-list-load-more-btn.php', true, false));
        $this->_template->render(false, false, 'json-success.php', true, false);
        //$this->_template->render(false, false, 'products/products-list.php');
    }

    public function searchFavouriteListItems()
    {
        $post = FatApp::getPostedData();
        $db = FatApp::getDb();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $loggedUserId = UserAuthentication::getLoggedUserId();

        $wishListRow = Product::getUserFavouriteProducts($loggedUserId, $this->siteLangId);

        if (!$wishListRow) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = new UserFavoriteProductSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinBrands();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory();
        $srch->joinSellerProductSpecialPrice();
        $srch->joinSellerSubscription($this->siteLangId, true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotCalculateRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wishListSubQuery = $wislistPSrchObj->getQuery();
        $srch->joinTable('(' . $wishListSubQuery . ')', 'LEFT OUTER JOIN', 'uwlp.uwlp_selprod_id = selprod_id', 'uwlp');


        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSellerProducts();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating');


        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        /* groupby added, beacouse if same product is linked with multiple categories, then showing in repeat for each category[ */
        $srch->addGroupBy('selprod_id');
        /* ] */

        $srch->addMultipleFields(
            array( 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'product_id', 'prodcat_id', 'ufp_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','product_image_updated_on',
            'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand.brand_id', 'product_model',
            'IFNULL(brand_name, brand_identifier) as brand_name', 'IFNULL(splprice_price, selprod_price) AS theprice','splprice_display_list_price', 'splprice_display_dis_val','splprice_display_dis_type',
            'CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found', 'selprod_price', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist', 'ifnull(prod_rating,0) prod_rating','selprod_sold_count' )
        );

        $srch->addOrder('ufp_id', 'desc');
        $srch->addCondition('ufp_user_id', '=', $loggedUserId);
        $rs = $srch->getResultSet();

        $products = $db->fetchAll($rs);

        /* $prodSrchObj = new ProductSearch();
        if( $products ){
        foreach($products as &$product){
        $moreSellerSrch = clone $prodSrchObj;
        $moreSellerSrch->addMoreSellerCriteria( $product['selprod_code'], $product['selprod_user_id'] );
        $moreSellerSrch->addMultipleFields(array('count(selprod_id) as totalSellersCount','MIN(theprice) as theprice'));
        $moreSellerSrch->addGroupBy('selprod_code');
        $moreSellerRs = $moreSellerSrch->getResultSet();
        $moreSellerRow = $db->fetch($moreSellerRs);
        $product['moreSellerData'] =  ($moreSellerRow) ? $moreSellerRow : array();
        }
        } */

        $this->set('products', $products);
        $this->set('showProductShortDescription', false);
        $this->set('showProductReturnPolicy', false);
        $this->set('colMdVal', 2);
        $this->set('page', $page);
        $this->set('pagingFunc', 'goToFavouriteListingSearchPage');
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page-1)*$pageSize + 1 ;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }

        $this->set('totalRecords', $totalRecords);
        $this->set('startRecord', $startRecord);
        $this->set('endRecord', $endRecord);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        if ($totalRecords > 0) {
            $this->set('html', $this->_template->render(false, false, 'products/products-list.php', true, false));
        } else {
            $this->set('html', $this->_template->render(false, false, '_partial/no-record-found.php', true, false));
        }
        $this->set('loadMoreBtnHtml', $this->_template->render(false, false, 'products/products-list-load-more-btn.php', true, false));
        $this->_template->render(false, false, 'json-success.php', true, false);
        //$this->_template->render(false, false, 'products/products-list.php');
    }

    public function deleteWishList()
    {
        $uwlist_id = FatApp::getPostedData('uwlist_id', FatUtility::VAR_INT, 0);
        if (0 >= $uwlist_id) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = UserWishList::getSearchObject(UserAuthentication::getLoggedUserId());
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('uwlist_id', '=', $uwlist_id);
        $srch->addCondition('uwlist_default', '=', applicationConstants::NO);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            $message = Labels::getLabel('MSG_No_record_found', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $obj = new UserWishList();
        $obj->deleteWishList($row['uwlist_id']);
        $this->set('msg', Labels::getLabel('LBL_Record_deleted_successfully', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewWishListItems()
    {
        $post = FatApp::getPostedData();
        $uwlist_id = FatUtility::int($post['uwlist_id']);

        $db = FatApp::getDb();
        $loggedUserId = UserAuthentication::getLoggedUserId();

        $srch = UserWishList::getSearchObject($loggedUserId);
        $srch->addMultipleFields(array('uwlist_id', 'uwlist_title', 'uwlist_default'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('uwlist_id', '=', $uwlist_id);
        $rs = $srch->getResultSet();
        $wishListRow = $db->fetch($rs);
        if (!$wishListRow) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('wishListRow', $wishListRow);
        $this->_template->render(false, false, 'account/wish-list-items.php');
    }

    public function updateSearchdate()
    {
        $post = FatApp::getPostedData();
        $pssearch_id = FatUtility::int($post['pssearch_id']);

        $srch = new SearchBase(Product::DB_PRODUCT_SAVED_SEARCH);
        $srch->addCondition('pssearch_id', '=', $pssearch_id);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $updateArray = array( 'pssearch_updated_on' => date('Y-m-d H:i:s') );
        $whr = array('smt'=>'pssearch_id = ?', 'vals'=> array($pssearch_id));

        if (!FatApp::getDb()->updateFromArray(Product::DB_PRODUCT_SAVED_SEARCH, $updateArray, $whr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Record_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleShopFavorite()
    {
        $shop_id = FatApp::getPostedData('shop_id', FatUtility::VAR_INT, 0);
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $db = FatApp::getDb();

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinSellerSubscription();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city' )
        );
        $srch->addCondition('shop_id', '=', $shop_id);
        //echo $srch->getQuery();
        $shopRs = $srch->getResultSet();
        $shop = $db->fetch($shopRs);

        if (!$shop) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $action = 'N'; //nothing happened
        $srch = new UserFavoriteShopSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('ufs_user_id', '=', $loggedUserId);
        $srch->addCondition('ufs_shop_id', '=', $shop_id);
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            $shopObj = new Shop();
            if (!$shopObj->addUpdateUserFavoriteShop($loggedUserId, $shop_id)) {
                $message = Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
            $action = 'A'; //Added to favorite
            $this->set('msg', Labels::getLabel('LBL_Shop_is_marked_as_favoutite', $this->siteLangId));
        } else {
            if (!$db->deleteRecords(Shop::DB_TBL_SHOP_FAVORITE, array('smt'=>'ufs_user_id = ? AND ufs_shop_id = ?', 'vals'=>array($loggedUserId, $shop_id)))) {
                $message = Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
            $action = 'R'; //Removed from favorite
            $this->set('msg', Labels::getLabel('LBL_Shop_has_been_removed_from_your_favourite_list', $this->siteLangId));
        }

        $this->set('action', $action);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function favoriteShopSearch()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $db = FatApp::getDb();
        $srch = new UserFavoriteShopSearch($this->siteLangId);
        $srch->setDefinedCriteria();
        $srch->joinSellerOrder();
        $srch->joinSellerOrderSubscription($this->siteLangId);
        $srch->addCondition('ufs_user_id', '=', $loggedUserId);
        $srch->addMultipleFields(
            array( 's.shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as country_name', 'shop_state_l.state_name as state_name', 'shop_city',
            'IFNULL(ufs.ufs_id, 0) as is_favorite' )
        );
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $shops = $db->fetchAll($rs);

        $totalProductsToShow = 4;
        if ($shops) {
            foreach ($shops as &$shop) {
                $shop['shopRating'] = SelProdRating::getSellerRating($shop['shop_user_id']);
            }
        }
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('shops', $shops);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function toggleProductFavorite()
    {
        $post = FatApp::getPostedData();
        $selprodId = FatUtility::int($post['product_id']);
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $db = FatApp::getDb();

        $srch = new ProductSearch($this->siteLangId);
        $srch->setDefinedCriteria(0, 0, array(), false);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array( 'selprod_id'));
        $srch->addCondition('selprod_id', '=', $selprodId);
        $srch->joinProductToCategory();
        $srch->joinShops();
        $srch->joinSellerSubscription();
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);

        $productRs = $srch->getResultSet();
        $product= $db->fetch($productRs);

        if (!$product) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $action = 'N'; //nothing happened
        $srch = new UserFavoriteProductSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('ufp_user_id', '=', $loggedUserId);
        $srch->addCondition('ufp_selprod_id', '=', $selprodId);
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            $prodObj = new Product();
            if (!$prodObj->addUpdateUserFavoriteProduct($loggedUserId, $selprodId)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $action = 'A'; //Added to favorite
            $this->set('msg', Labels::getLabel('LBL_Product_has_been_marked_as_favourite_successfully', $this->siteLangId));
        } else {
            if (!$db->deleteRecords(Product::DB_TBL_PRODUCT_FAVORITE, array('smt'=>'ufp_user_id = ? AND ufp_selprod_id = ?', 'vals'=>array($loggedUserId, $selprodId)))) {
                Message::addErrorMessage(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $action = 'R'; //Removed from favorite
            $this->set('msg', Labels::getLabel('LBL_Product_has_been_removed_from_favourite_list', $this->siteLangId));
        }

        $this->set('action', $action);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function messages()
    {
        $frm = $this->getMessageSearchForm($this->siteLangId);
        $this->set('frmSrch', $frm);
        $this->_template->render();
    }

    public function messageSearch()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $userImgUpdatedOn = User::getAttributesById($userId, 'user_img_updated_on');
        $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);

        $frm = $this->getMessageSearchForm($this->siteLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = new MessageSearch();
        $srch->joinThreadLastMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinThreadStartedByUser();
        $srch->addMultipleFields(array('tth.*','ttm.message_id','ttm.message_text','ttm.message_date','ttm.message_is_unread','ttm.message_to'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $cnd = $srch->addCondition('ttm.message_from', '=', $userId);
        $cnd->attachCondition('ttm.message_to', '=', $userId, 'OR');
        $srch->addOrder('message_id', 'DESC');
        $srch->addGroupBy('ttm.message_thread_id');
        /* die($srch->getQuery()); */
        if ($post['keyword']!='') {
            $cnd = $srch->addCondition('tth.thread_subject', 'like', "%".$post['keyword']."%");
            $cnd->attachCondition('tfr.user_name', 'like', "%".$post['keyword']."%", 'OR');
            $cnd->attachCondition('tfr_c.credential_username', 'like', "%".$post['keyword']."%", 'OR');
        }
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        if (true ===  MOBILE_APP_API_CALL) {
            $message_records = array();
            foreach ($records as $mkey => $mval) {
                $profile_images_arr=  array(
                 "message_from_profile_url"=> FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'user', array($mval['message_from_user_id'],'thumb',1)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'),
                 "message_to_profile_url"=> FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'user', array($mval['message_to_user_id'],'thumb',1)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'),
                 "message_timestamp"=>strtotime($mval['message_date'])
                                            );
                $message_records[] = array_merge($mval, $profile_images_arr);
            }
            $records = $message_records;
        }

        /* CommonHelper::printArray($records); die; */
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('loggedUserId', UserAuthentication::getLoggedUserId());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function viewMessages($threadId, $messageId = 0)
    {
        $threadId = FatUtility::int($threadId);
        $messageId = FatUtility::int($messageId);
        $userId = UserAuthentication::getLoggedUserId();
        if (1 > $threadId) {
            $message = Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $threadData = Thread::getAttributesById($messageId, array('thread_id,thread_type'));
        if ($threadData == false) {
            $message = Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $srch = new MessageSearch();

        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinThreadStartedByUser();
        if ($threadData['thread_type'] == Thread::THREAD_TYPE_SHOP) {
            $srch->joinShops($this->siteLangId);
        } elseif ($threadData['thread_type'] == Thread::THREAD_TYPE_PRODUCT) {
            $srch->joinProducts($this->siteLangId);
        }

        $srch->joinOrderProducts();
        $srch->joinOrderProductStatus();
        $srch->addMultipleFields(array('tth.*','top.op_invoice_number'));
        /* die($srch->getQuery()); */
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $srch->addCondition('tth.thread_id', '=', $threadId);
        if ($messageId) {
            $srch->addCondition('ttm.message_id', '=', $messageId);
        }
        $cnd = $srch->addCondition('ttm.message_from', '=', $userId);
        $cnd->attachCondition('ttm.message_to', '=', $userId, 'OR');

        $rs = $srch->getResultSet();
        $threadDetails = FatApp::getDb()->fetch($rs);
        /* CommonHelper::printArray($threadDetails);die; */
        if ($threadDetails == false) {
            $message = Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        if (false ===  MOBILE_APP_API_CALL) {
            $frmSrch = $this->getMsgSearchForm($this->siteLangId);
            $frmSrch->fill(array('thread_id'=>$threadId));
            $frm = $this->sendMessageForm($this->siteLangId);
            $frm->fill(array('message_thread_id'=>$threadId,'message_id'=>$messageId));
        }

        $threadObj = new Thread($threadId);
        if (!$threadObj->markUserMessageRead($threadId, $userId)) {
            if (true ===  MOBILE_APP_API_CALL) {
                Message::addErrorMessage(strip_tags(current($threadObj->getError())));
            }
            Message::addErrorMessage($threadObj->getError());
            CommonHelper::redirectUserReferer();
        }

        if (false ===  MOBILE_APP_API_CALL) {
            $this->set('frmSrch', $frmSrch);
            $this->set('frm', $frm);
        }

        $this->set('threadDetails', $threadDetails);
        $this->set('threadTypeArr', Thread::getThreadTypeArr($this->siteLangId));
        $this->set('loggedUserId', $userId);
        $this->set('loggedUserName', ucfirst(UserAuthentication::getLoggedUserAttribute('user_name')));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render();
    }

    public function threadMessageSearch()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();
        $threadId = empty($post['thread_id']) ? 0 : FatUtility::int($post['thread_id']);

        if (1 > $threadId) {
            $message = Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $threadObj = new Thread($threadId);
            if (!$threadObj->markUserMessageRead($threadId, $userId)) {
                $msg = is_string($threadObj->getError()) ? $threadObj->getError() : current($threadObj->getError());
                LibHelper::dieJsonError(strip_tags($msg));
            }
        }

        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinThreadStartedByUser();
        $srch->addMultipleFields(array('tth.*','ttm.message_id','ttm.message_text','ttm.message_date','ttm.message_is_unread'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $srch->addCondition('tth.thread_id', '=', $threadId);
        $cnd = $srch->addCondition('ttm.message_from', '=', $userId);
        $cnd->attachCondition('ttm.message_to', '=', $userId, 'OR');
        $srch->addOrder('message_id', 'DESC');

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        //echo $srch->getQuery();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'message_id');
        //commonHelper::printArray($records);
        ksort($records);

        $this->set("arrListing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $startRecord = ($page-1)* $pagesize + 1 ;
        $endRecord = $pagesize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }

        $this->set('totalRecords', $totalRecords);
        $this->set('startRecord', $startRecord);
        $this->set('endRecord', $endRecord);
        $this->set('records', $records);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->set('loadMoreBtnHtml', $this->_template->render(false, false, '_partial/load-previous-btn.php', true));
        $this->set('html', $this->_template->render(false, false, 'account/thread-message-search.php', true, false));
        $this->_template->render(false, false, 'json-success.php', true, false);
    }

    public function sendMessage()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $frm = $this->sendMessageForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $threadId =  FatUtility::int($post['message_thread_id']);
        $messageId =  FatUtility::int($post['message_id']);

        if (1 > $threadId || 1 > $messageId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = new MessageSearch();

        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinThreadStartedByUser();
        //$srch->joinShops();
        //$srch->joinOrderProducts();
        //$srch->joinOrderProductStatus();
        $srch->addMultipleFields(array('tth.*'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $srch->addCondition('tth.thread_id', '=', $threadId);
        $srch->addCondition('ttm.message_id', '=', $messageId);
        $cnd = $srch->addCondition('ttm.message_from', '=', $userId);
        $cnd->attachCondition('ttm.message_to', '=', $userId, 'OR');
        $rs = $srch->getResultSet();

        $threadDetails = FatApp::getDb()->fetch($rs);
        if (empty($threadDetails)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $messageSendTo = ($threadDetails['message_from_user_id'] == $userId)?$threadDetails['message_to_user_id']:$threadDetails['message_from_user_id'];

        $data = array(
        'message_thread_id'=>$threadId,
        'message_from'=>$userId,
        'message_to'=>$messageSendTo,
        'message_text'=>$post['message_text'],
        'message_date'=>date('Y-m-d H:i:s'),
        'message_is_unread'=>1
        );

        $tObj = new Thread();

        if (!$insertId = $tObj->addThreadMessages($data)) {
            $message = Labels::getLabel($tObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($insertId) {
            $emailObj = new EmailHandler();
            $emailObj->SendMessageNotification($insertId, $this->siteLangId);
        }

        $this->set('threadId', $threadId);
        $this->set('messageId', $insertId);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('messageDetail', $data);
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getMessageSearchForm($langId)
    {
        $frm = new Form('frmMessageSrch');
        $frm->addTextBox('', 'keyword');
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getWithdrawalForm($langId)
    {
        $frm = new Form('frmWithdrawal');
        $fld  = $frm->addRequiredField(Labels::getLabel('LBL_Amount_to_be_Withdrawn', $langId).' ['.commonHelper::getDefaultCurrencySymbol().']', 'withdrawal_amount');
        $fld->requirement->setFloat(true);
        $walletBalance = User::getUserBalance(UserAuthentication::getLoggedUserId());
        $fld->htmlAfterField = Labels::getLabel("LBL_Current_Wallet_Balance", $langId) .' '.CommonHelper::displayMoneyFormat($walletBalance, true, true);

        if (User::isAffiliate()) {
            $PayMethodFld = $frm->addRadioButtons(Labels::getLabel('LBL_Payment_Method', $langId), 'uextra_payment_method', User::getAffiliatePaymentMethodArr($langId));

            /* [ */
            $frm->addTextBox(Labels::getLabel('LBL_Cheque_Payee_Name', $langId), 'uextra_cheque_payee_name');
            $chequePayeeNameUnReqFld = new FormFieldRequirement('uextra_cheque_payee_name', Labels::getLabel('LBL_Cheque_Payee_Name', $langId));
            $chequePayeeNameUnReqFld->setRequired(false);

            $chequePayeeNameReqFld = new FormFieldRequirement('uextra_cheque_payee_name', Labels::getLabel('LBL_Cheque_Payee_Name', $langId));
            $chequePayeeNameReqFld->setRequired(true);

            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_CHEQUE, 'eq', 'uextra_cheque_payee_name', $chequePayeeNameReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_BANK, 'eq', 'uextra_cheque_payee_name', $chequePayeeNameUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_PAYPAL, 'eq', 'uextra_cheque_payee_name', $chequePayeeNameUnReqFld);
            /* ] */

            /* [ */
            $frm->addTextBox(Labels::getLabel('LBL_Bank_Name', $langId), 'ub_bank_name');
            $bankNameUnReqFld = new FormFieldRequirement('ub_bank_name', Labels::getLabel('LBL_Bank_Name', $langId));
            $bankNameUnReqFld->setRequired(false);

            $bankNameReqFld = new FormFieldRequirement('ub_bank_name', Labels::getLabel('LBL_Bank_Name', $langId));
            $bankNameReqFld->setRequired(true);

            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_CHEQUE, 'eq', 'ub_bank_name', $bankNameUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_BANK, 'eq', 'ub_bank_name', $bankNameReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_PAYPAL, 'eq', 'ub_bank_name', $bankNameUnReqFld);
            /* ] */

            /* [ */
            $frm->addTextBox(Labels::getLabel('LBL_Account_Holder_Name', $langId), 'ub_account_holder_name');
            $bankAccHolderNameUnReqFld = new FormFieldRequirement('ub_account_holder_name', Labels::getLabel('LBL_Account_Holder_Name', $langId));
            $bankAccHolderNameUnReqFld->setRequired(false);

            $bankAccHolderNameReqFld = new FormFieldRequirement('ub_account_holder_name', Labels::getLabel('LBL_Account_Holder_Name', $langId));
            $bankAccHolderNameReqFld->setRequired(true);

            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_CHEQUE, 'eq', 'ub_account_holder_name', $bankAccHolderNameUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_BANK, 'eq', 'ub_account_holder_name', $bankAccHolderNameReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_PAYPAL, 'eq', 'ub_account_holder_name', $bankAccHolderNameUnReqFld);
            /* ] */

            /* [ */
            $frm->addTextBox(Labels::getLabel('LBL_Bank_Account_Number', $langId), 'ub_account_number');
            $bankAccNumberUnReqFld = new FormFieldRequirement('ub_account_number', Labels::getLabel('LBL_Bank_Account_Number', $langId));
            $bankAccNumberUnReqFld->setRequired(false);

            $bankAccNumberReqFld = new FormFieldRequirement('ub_account_number', Labels::getLabel('LBL_Bank_Account_Number', $langId));
            $bankAccNumberReqFld->setRequired(true);

            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_CHEQUE, 'eq', 'ub_account_number', $bankAccNumberUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_BANK, 'eq', 'ub_account_number', $bankAccNumberReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_PAYPAL, 'eq', 'ub_account_number', $bankAccNumberUnReqFld);
            /* ] */

            /* [ */
            $frm->addTextBox(Labels::getLabel('LBL_Swift_Code', $langId), 'ub_ifsc_swift_code');
            $bankIfscUnReqFld = new FormFieldRequirement('ub_ifsc_swift_code', Labels::getLabel('LBL_Swift_Code', $langId));
            $bankIfscUnReqFld->setRequired(false);
            $bankIfscUnReqFld->requirements()->setRegularExpressionToValidate(ValidateElement::USERNAME_REGEX);

            $bankIfscReqFld = new FormFieldRequirement('ub_ifsc_swift_code', Labels::getLabel('LBL_Swift_Code', $langId));
            $bankIfscReqFld->setRequired(true);
            $bankIfscReqFld->requirements()->setRegularExpressionToValidate(ValidateElement::USERNAME_REGEX);

            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_CHEQUE, 'eq', 'ub_ifsc_swift_code', $bankIfscUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_BANK, 'eq', 'ub_ifsc_swift_code', $bankIfscReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_PAYPAL, 'eq', 'ub_ifsc_swift_code', $bankIfscUnReqFld);
            /* ] */

            /* [ */
            $frm->addTextArea(Labels::getLabel('LBL_Bank_Address', $langId), 'ub_bank_address');
            $bankBankAddressUnReqFld = new FormFieldRequirement('ub_bank_address', Labels::getLabel('LBL_Bank_Address', $langId));
            $bankBankAddressUnReqFld->setRequired(false);

            $bankBankAddressReqFld = new FormFieldRequirement('ub_bank_address', Labels::getLabel('LBL_Bank_Address', $langId));
            $bankBankAddressReqFld->setRequired(true);

            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_CHEQUE, 'eq', 'ub_bank_address', $bankBankAddressUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_BANK, 'eq', 'ub_bank_address', $bankBankAddressReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_PAYPAL, 'eq', 'ub_bank_address', $bankBankAddressUnReqFld);
            /* ] */

            /* [ */
            $fld = $frm->addTextBox(Labels::getLabel('LBL_PayPal_Email_Account', $langId), 'uextra_paypal_email_id');
            $PPEmailIdUnReqFld = new FormFieldRequirement('uextra_paypal_email_id', Labels::getLabel('LBL_PayPal_Email_Account', $langId));
            $PPEmailIdUnReqFld->setRequired(false);

            $PPEmailIdReqFld = new FormFieldRequirement('uextra_paypal_email_id', Labels::getLabel('LBL_PayPal_Email_Account', $langId));
            $PPEmailIdReqFld->setRequired(true);
            $PPEmailIdReqFld->setEmail();

            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_CHEQUE, 'eq', 'uextra_paypal_email_id', $PPEmailIdUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_BANK, 'eq', 'uextra_paypal_email_id', $PPEmailIdUnReqFld);
            $PayMethodFld->requirements()->addOnChangerequirementUpdate(User::AFFILIATE_PAYMENT_METHOD_PAYPAL, 'eq', 'uextra_paypal_email_id', $PPEmailIdReqFld);
        /* ] */
        } else {
            $frm->addRequiredField(Labels::getLabel('LBL_Bank_Name', $langId), 'ub_bank_name');
            $frm->addRequiredField(Labels::getLabel('LBL_Account_Holder_Name', $langId), 'ub_account_holder_name');
            $frm->addRequiredField(Labels::getLabel('LBL_Account_Number', $langId), 'ub_account_number');
            $ifsc = $frm->addRequiredField(Labels::getLabel('LBL_IFSC_Swift_Code', $langId), 'ub_ifsc_swift_code');
            $ifsc->requirements()->setRegularExpressionToValidate(ValidateElement::USERNAME_REGEX);
            $frm->addTextArea(Labels::getLabel('LBL_Bank_Address', $langId), 'ub_bank_address');
        }
        $frm->addTextArea(Labels::getLabel('LBL_Other_Info_Instructions', $langId), 'withdrawal_comments');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Request', $langId));
        $frm->addButton("", "btn_cancel", Labels::getLabel("LBL_Cancel", $langId));
        return $frm;
    }

    private function getCreateWishListForm()
    {
        $frm = new Form('frmCreateWishList');
        $frm->setRequiredStarWith('NONE');
        $frm->addRequiredField('', 'uwlist_title');
        $frm->addHiddenField('', 'selprod_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Add', $this->siteLangId));
        $frm->setJsErrorDisplay('afterfield');
        return $frm;
    }

    private function userEmailVerification($userObj, $data)
    {
        return $this->userEmailVerifications($userObj, $data);
    }

    private function getProfileInfoForm()
    {
        $frm = new Form('frmProfileInfo');
        $frm->addTextBox(Labels::getLabel('LBL_Username', $this->siteLangId), 'credential_username', '');
        $frm->addTextBox(Labels::getLabel('LBL_Email', $this->siteLangId), 'credential_email', '');
        $frm->addRequiredField(Labels::getLabel('LBL_Customer_Name', $this->siteLangId), 'user_name');
        $frm->addDateField(Labels::getLabel('LBL_Date_Of_Birth', $this->siteLangId), 'user_dob', '', array('readonly'=>'readonly'));
        $phoneFld = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $this->siteLangId), 'user_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phoneFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $phoneFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));
        // $phoneFld->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_e.g.', $this->siteLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</small>';

        if (User::isAffiliate()) {
            $frm->addTextBox(Labels::getLabel('LBL_Company', $this->siteLangId), 'uextra_company_name');
            $frm->addTextBox(Labels::getLabel('LBL_Website', $this->siteLangId), 'uextra_website');
            $frm->addTextBox(Labels::getLabel('LBL_Address_Line1', $this->siteLangId), 'user_address1')->requirements()->setRequired();
            $frm->addTextBox(Labels::getLabel('LBL_Address_Line2', $this->siteLangId), 'user_address2');
        }

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->siteLangId), 'user_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0), array(), Labels::getLabel('LBL_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->siteLangId), 'user_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirement->setRequired(true);
        $frm->addTextBox(Labels::getLabel('LBL_City', $this->siteLangId), 'user_city');

        if (User::isAffiliate()) {
            $zipFld = $frm->addRequiredField(Labels::getLabel('LBL_Postalcode', $this->siteLangId), 'user_zip');
            $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
            $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId));
        }

        if (User::isAdvertiser()) {
            $fld=$frm->addTextBox(Labels::getLabel('L_Company', $this->siteLangId), 'user_company');
            $fld=$frm->addTextArea(Labels::getLabel('L_Brief_Profile', $this->siteLangId), 'user_profile_info');
            $fld->html_after_field='<small>'.Labels::getLabel('L_Please_tell_us_something_about_yourself', $this->siteLangId).'</small>';
            $frm->addTextArea(Labels::getLabel('L_What_kind_products_services_advertise', $this->siteLangId), 'user_products_services');
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    private function getProfileImageForm()
    {
        /* $frm = new Form('frmProfileImage');
        $fld1 =  $frm->addButton('','user_profile_image',Labels::getLabel('LBL_Change',$this->siteLangId),array('class'=>'userFile-Js','id'=>'user_profile_image'));
        return $frm; */
        $frm = new Form('frmProfile', array('id'=>'frmProfile'));
        $frm->addFileUpload(Labels::getLabel('LBL_Profile_Picture', $this->siteLangId), 'user_profile_image', array('id'=>'user_profile_image','onchange'=>'popupImage(this)','accept'=>'image/*'));
        $frm->addHiddenField('', 'update_profile_img', Labels::getLabel('LBL_Update', $this->siteLangId), array('id'=>'update_profile_img'));
        $frm->addHiddenField('', 'rotate_left', Labels::getLabel('LBL_Rotate_Left', $this->siteLangId), array('id'=>'rotate_left'));
        $frm->addHiddenField('', 'rotate_right', Labels::getLabel('LBL_Rotate_Right', $this->siteLangId), array('id'=>'rotate_right'));
        $frm->addHiddenField('', 'remove_profile_img', 0, array('id'=>'remove_profile_img'));
        $frm->addHiddenField('', 'action', 'avatar', array('id'=>'avatar-action'));
        $frm->addHiddenField('', 'img_data', '', array('id'=>'img_data'));
        return $frm;
    }

    private function getBankInfoForm()
    {
        $frm = new Form('frmBankInfo');
        $frm->addRequiredField(Labels::getLabel('M_Bank_Name', $this->siteLangId), 'ub_bank_name', '');
        $frm->addRequiredField(Labels::getLabel('M_Account_Holder_Name', $this->siteLangId), 'ub_account_holder_name', '');
        $fld = $frm->addRequiredField(Labels::getLabel('M_Account_Number', $this->siteLangId), 'ub_account_number', '');
        $fld->requirement->setRequired(true);

        $ifsc = $frm->addRequiredField(Labels::getLabel('M_IFSC_Swift_Code', $this->siteLangId), 'ub_ifsc_swift_code', '');
        $ifsc->requirements()->setRegularExpressionToValidate(ValidateElement::USERNAME_REGEX);

        $frm->addTextArea(Labels::getLabel('M_Bank_Address', $this->siteLangId), 'ub_bank_address', '');
        $frm->addHtml('bank_info_safety_text', 'bank_info_safety_text', '<span class="text--small">'.Labels::getLabel('Lbl_Your_Bank/Card_info_is_safe_with_us', $this->siteLangId).'</span>');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    private function getChangePasswordForm()
    {
        $frm = new Form('changePwdFrm');
        $curPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_CURRENT_PASSWORD', $this->siteLangId),
            'current_password'
        );
        $curPwd->requirements()->setRequired();

        $newPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_NEW_PASSWORD', $this->siteLangId),
            'new_password'
        );
        $newPwd->htmlAfterField='<span class="text--small">'.sprintf(Labels::getLabel('LBL_Example_password', $this->siteLangId), 'User@123').'</span>';
        $newPwd->requirements()->setRequired();
        $newPwd->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
        $newPwd->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_ATLEAST_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
        $conNewPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_CONFIRM_NEW_PASSWORD', $this->siteLangId),
            'conf_new_password'
        );
        $conNewPwdReq = $conNewPwd->requirements();
        $conNewPwdReq->setRequired();
        $conNewPwdReq->setCompareWith('new_password', 'eq');
        /* $conNewPwdReq->setCustomErrorMessage(Labels::getLabel('LBL_CONFIRM_PASSWORD_NOT_MATCHED',
        $this->siteLangId)); */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    private function notifyAdminSupplierApproval($userObj, $data, $approval_request = 1)
    {
        $attr = array('user_name','credential_username','credential_email');
        $userData = $userObj->getUserInfo($attr);

        if ($userData === false) {
            return false;
        }

        $data = array(
            'user_name' => $userData['user_name'],
            'username' => $userData['credential_username'],
        'user_email' => $userData['credential_email'],
        'reference_number' => $data['reference'],
        );

        $email = new EmailHandler();

        if (!$email->sendSupplierApprovalNotification(CommonHelper::getLangId(), $data, $approval_request)) {
            Message::addMessage(
                Labels::getLabel(
                    "MSG_ERROR_IN_SENDING_SUPPLIER_APPROVAL_EMAIL",
                    CommonHelper::getLangId()
                )
            );
            return false;
        }

        return true;
    }

    private function getSupplierForm()
    {
        $frm = new Form('frmSupplierForm');
        $frm->addHiddenField('', 'id', 0);

        $userObj = new User();
        $supplier_form_fields = $userObj->getSupplierFormFields($this->siteLangId);

        foreach ($supplier_form_fields as $field) {
            $fieldName = 'sformfield_'.$field['sformfield_id'];

            switch ($field['sformfield_type']) {
                case User::USER_FIELD_TYPE_TEXT:
                    $fld = $frm->addTextBox($field['sformfield_caption'], $fieldName);
                    break;

                case User::USER_FIELD_TYPE_TEXTAREA:
                    $fld = $frm->addTextArea($field['sformfield_caption'], $fieldName);
                    break;

                case User::USER_FIELD_TYPE_FILE:
                    $fld1 = $frm->addButton(
                        $field['sformfield_caption'],
                        'button['.$field['sformfield_id'].']',
                        Labels::getLabel('LBL_Upload_File', $this->siteLangId),
                        array('class'=>'fileType-Js','id'=>'button-upload'.$field['sformfield_id'],'data-field_id'=>$field['sformfield_id'])
                    );
                    $fld1->htmlAfterField='<span id="input-sformfield'.$field['sformfield_id'].'"></span>';
                    if ($field['sformfield_required'] == 1) {
                        $fld1->captionWrapper = array('<div class="astrick">','</div>');
                    }
                    $fld = $frm->addTextBox('', $fieldName, '', array('id'=>$fieldName , 'hidden'=>'hidden' , 'title' => $field['sformfield_caption']));
                    $fld->setRequiredStarWith(Form::FORM_REQUIRED_STAR_WITH_NONE);
                    $fld1->attachField($fld);
                    break;

                case User::USER_FIELD_TYPE_DATE:
                    $fld = $frm->addDateField($field['sformfield_caption'], $fieldName, '', array('readonly'=>'readonly'));
                    break;

                case User::USER_FIELD_TYPE_DATETIME:
                    $fld = $frm->addDateTimeField($field['sformfield_caption'], $fieldName, '', array('readonly'=>'readonly'));
                    break;

                case User::USER_FIELD_TYPE_TIME:
                    $fld = $frm->addTextBox($field['sformfield_caption'], $fieldName);
                    $fld->requirement->setRegularExpressionToValidate(ValidateElement::TIME_REGEX);
                    $fld->htmlAfterField = Labels::getLabel('LBL_HH:MM', $this->siteLangId);
                    $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_time_format.', $this->siteLangId));
                    break;

                case User::USER_FIELD_TYPE_PHONE:
                    $fld = $frm->addTextBox($field['sformfield_caption'], $fieldName, '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
                    $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
                    break;
            }

            if ($field['sformfield_required'] == 1) {
                $fld->requirements()->setRequired();
            }
            if ($field['sformfield_comment']) {
                $fld->htmlAfterField = '<p class="note">'.$field['sformfield_comment'].'</p>';
            }
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    public function updatePhoto()
    {
        if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
            $attachment = new AttachedFile();
            if ($attachment->saveImage(
                $_FILES['photo']['tmp_name'],
                AttachedFile::FILETYPE_USER_PHOTO,
                UserAuthentication::getLoggedUserId(),
                0,
                $_FILES['photo']['name'],
                0,
                false
            )) {
                Message::addMessage(Labels::getLabel('MSG_Profile_Picture_Updated', $this->siteLangId));
            } else {
                Message::addErrorMessage($attachment->getError());
            }
        } else {
            Message::addErrorMessage(Labels::getLabel('MSG_No_File_Uploaded', $this->siteLangId));
        }
        FatApp::redirectUser(CommonHelper::generateUrl('member', 'account'));
    }

    public function escalateOrderReturnRequest($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        if (!$orrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderReturnRequestSearch();
        $srch->joinOrderProducts();
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);

        /* $cnd = $srch->addCondition( 'orrequest_user_id', '=', $user_id );
        $cnd->attachCondition('op_selprod_user_id', '=', $user_id ); */
        $srch->addCondition('op_selprod_user_id', '=', $user_id);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_user_id'));
        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);

        if (!$request || $request['orrequest_id'] != $orrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        /* buyer cannot escalate request[ */
        // if( $user_id == $request['orrequest_user_id'] ){
        if (!User::isSeller()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        /* ] */


        $orrObj = new OrderReturnRequest();
        if (!$orrObj->escalateRequest($request['orrequest_id'], $user_id, $this->siteLangId)) {
            Message::addErrorMessage(Labels::getLabel($orrObj->getError(), $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($orrequest_id, $this->siteLangId)) {
            Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        /* ] */
        Message::addMessage(Labels::getLabel('MSG_Your_request_sent', $this->siteLangId));
        CommonHelper::redirectUserReferer();
    }

    public function orderReturnRequestMessageSearch()
    {
        $frm = $this->getOrderReturnRequestMessageSearchForm($this->siteLangId);
        $postedData = FatApp::getPostedData();
        $post = $frm->getFormDataFromArray($postedData);
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $user_id = UserAuthentication::getLoggedUserId();

        $orrequest_id = isset($post['orrequest_id']) ? FatUtility::int($post['orrequest_id']) : 0;
        $isSeller = isset($postedData['isSeller']) ? FatUtility::int($postedData['isSeller']) : 0;

        $srch = new OrderReturnRequestMessageSearch($this->siteLangId);
        $srch->joinOrderReturnRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->joinOrderProducts();
        $srch->addCondition('orrmsg_orrequest_id', '=', $orrequest_id);
        if (0 < $isSeller) {
            $srch->addCondition('op_selprod_user_id', '=', $user_id);
        } else {
            $srch->addCondition('orrequest_user_id', '=', $user_id);
        }
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('orrmsg_id', 'DESC');
        $srch->addMultipleFields(
            array( 'orrmsg_id', 'orrmsg_from_user_id', 'orrmsg_msg',
            'orrmsg_date', 'msg_user.user_name as msg_user_name', 'orrequest_status',
            'orrmsg_from_admin_id', 'admin_name', 'shop_identifier', 'op_selprod_user_id' )
        );

        $rs = $srch->getResultSet();
        $messagesList = FatApp::getDb()->fetchAll($rs, 'orrmsg_id');
        ksort($messagesList);

        $this->set('messagesList', (!empty($messagesList) ? $messagesList : array()));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page-1)*$pageSize + 1 ;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $this->set('totalRecords', $totalRecords);
        $this->set('startRecord', $startRecord);
        $this->set('endRecord', $endRecord);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->set('loadMoreBtnHtml', $this->_template->render(false, false, '_partial/load-previous-btn.php', true));
        $this->set('html', $this->_template->render(false, false, 'account/order-return-request-messages-list.php', true, false));
        $this->_template->render(false, false, 'json-success.php', true, false);
    }

    public function shareWithTag()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE", FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Refferal_module_no_longer_active", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        //print_r($post); exit;
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $friendlist = FatApp::getPostedData('friendlist');
        $friendlist = rtrim($friendlist, ',');

        if (1 > $selprod_id && $friendlist == '') {
            Message::addErrorMessage(Labels::getLabel("LBL_INVALID_REQUEST", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $returnDataArr = array();
        $prodSrchObj = new ProductSearch($this->siteLangId);
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinSellerSubscription();
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();
        $prodSrchObj->addCondition('selprod_id', '=', $selprod_id);
        $prodSrchObj->addMultipleFields(array('selprod_id'));
        $rs = $prodSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            Message::addErrorMessage(Labels::getLabel("LBL_Product_not_found_or_no_longer_available.", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $user_referral_code = User::getAttributesById($userId, "user_referral_code");
        if ($user_referral_code == '') {
            Message::addErrorMessage(Labels::getLabel("LBL_Your_referral_code_is_not_generated,_Please_contact_admin.", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productUrl = CommonHelper::generateUrl('products', 'view', array($selprod_id ));
        $productUrl = CommonHelper::base64encode(ltrim($productUrl, '/'));

        $productSharingUrl = CommonHelper::generateFullUrl("custom", "referral", array( $user_referral_code, $productUrl));

        $userInfo = User::getAttributesById($userId, array('user_fb_access_token'));
        if ($userInfo['user_fb_access_token']=='') {
            Message::addErrorMessage(Labels::getLabel('MSG_Authenticate_Your_Account', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        include_once CONF_INSTALLATION_PATH.'library/Fbapi.php';
        $config = array(
        'app_id' => FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING, ''),
        'app_secret' => FatApp::getConfig('CONF_FACEBOOK_APP_SECRET', FatUtility::VAR_STRING, ''),
        );
        $fb = new Fbapi($config);
        $fbObj = $fb->getInstance();

        $linkData = array(
        'link' => $productSharingUrl,
        'message' => Labels::getLabel('MSG_Share_and_Earn_Mesage', $this->siteLangId),
        );

        if ($friendlist!='') {
            $linkData['tags'] = $friendlist;
        }

        $fbAccessToken = $userInfo['user_fb_access_token'];

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fbObj->post('/me/feed', $linkData, $fbAccessToken);
        } catch (FacebookResponseException $e) {
            Message::addErrorMessage($e->getMessage());
            FatUtility::dieJsonError(Message::getHtml());
        } catch (FacebookSDKException $e) {
            Message::addErrorMessage($e->getMessage());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $graphNode = $response->getGraphNode();

        $this->set('msg', Labels::getLabel('MSG_Shared_Successfully!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function shareSocialReferEarn()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE", FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Refferal_module_no_longer_active", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $socialMediaName = FatApp::getPostedData('socialMediaName', FatUtility::VAR_STRING, 0);

        if ($selprod_id <= 0 || $socialMediaName == '') {
            Message::addErrorMessage(Labels::getLabel("LBL_INVALID_REQUEST", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user'] = CommonHelper::generateUrl('products', 'view', array($selprod_id));

        /*FB API to share [*/
        include_once CONF_INSTALLATION_PATH.'library/Fbapi.php';
        $config = array(
        'app_id' => FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING, ''),
        'app_secret' => FatApp::getConfig('CONF_FACEBOOK_APP_SECRET', FatUtility::VAR_STRING, ''),
        );
        $fb = new Fbapi($config);

        $userInfo = User::getAttributesById($userId, array('user_fb_access_token'));

        $fbLoginUrl = '';
        $friendList = array();
        if ($userInfo['user_fb_access_token']=='') {
            $redirectUrl = CommonHelper::generateFullUrl('Buyer', 'getFbToken', array(), '', false);
            $fbLoginUrl = $fb->getLoginUrl($redirectUrl);
        } else {
            $fbAccessToken = $userInfo['user_fb_access_token'];
            $fbObj = $fb->getInstance();

            try {
                $response = $fbObj->get('/me/friends?fields=id,name', $fbAccessToken);
                $graphEdge = $response->getGraphEdge();
                foreach ($graphEdge as $graphNode) {
                    $friendList[] = $graphNode->asArray();
                }
            } catch (FacebookResponseException $e) {
                Message::addErrorMessage($e->getMessage());
                FatUtility::dieWithError(Message::getHtml());
            } catch (FacebookSDKException $e) {
                Message::addErrorMessage($e->getMessage());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $this->set('fbLoginUrl', $fbLoginUrl);
        $this->set('friendList', $friendList);
        $this->set('selprod_id', $selprod_id);
        $this->_template->render(false, false);
    }

    private function getCreditsSearchForm($langId)
    {
        $frm = new Form('frmCreditSrch');
        $frm->addTextBox('', 'keyword', '');
        $frm->addSelectBox('', 'debit_credit_type', array( -1 => Labels::getLabel('LBL_Both-Debit/Credit', $langId) ) + Transactions::getCreditDebitTypeArr($langId), -1, array(), '');
        $frm->addDateField('', 'date_from', '', array('readonly'=>'readonly','class'=>'field--calender'));
        $frm->addDateField('', 'date_to', '', array('readonly'=>'readonly','class'=>'field--calender'));
        /* $frm->addSelectBox( '', 'date_order', array( 'ASC' => Labels::getLabel('LBL_Date_Order_Ascending', $langId), 'DESC' => Labels::getLabel('LBL_Date_Order_Descending', $langId) ), 'DESC', array(), '' ); */
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function sendMessageForm($langId)
    {
        $frm = new Form('frmSendMessage');
        $frm->addTextarea(Labels::getLabel('LBL_Comments', $langId), 'message_text', '')->requirements()->setRequired(true);
        $frm->addHiddenField('', 'message_thread_id');
        $frm->addHiddenField('', 'message_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $langId));
        return $frm;
    }

    private function getMsgSearchForm($langId)
    {
        $frm = new Form('frmMessageSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'thread_id');
        return $frm;
    }

    private function getSettingsForm()
    {
        $frm = new Form('frmBankInfo');
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Auto_Renew_Subscription', $this->siteLangId), 'user_autorenew_subscription', $activeInactiveArr, '', array(), Labels::getLabel('LBL_Select', $this->siteLangId));
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    private function getRechargeWalletForm($langId)
    {
        $frm = new Form('frmRechargeWallet');
        $fld = $frm->addFloatField('', 'amount');
        //$fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Add_Money_to_account', $langId));
        return $frm;
    }

    public function myAddresses()
    {
        $this->_template->render();
    }

    public function searchAddresses()
    {
        $addresses = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId);
        if ($addresses) {
            $this->set('addresses', $addresses);
        } else {
            if (true ===  MOBILE_APP_API_CALL) {
                $this->set('addresses', array());
            }
            $this->set('noRecordsHtml', $this->_template->render(false, false, '_partial/no-record-found.php', true));
        }
        if (true ===  MOBILE_APP_API_CALL) {
            $cartObj = new Cart(UserAuthentication::getLoggedUserId());
            $shipping_address_id = $cartObj->getCartShippingAddress();
            $this->set('shippingAddressId', $shipping_address_id);
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function addAddressForm($ua_id)
    {
        $ua_id =  FatUtility::int($ua_id);
        $addressFrm = $this->getUserAddressForm($this->siteLangId);

        $stateId = 0;
        $cityId = 0;

        if ($ua_id > 0) {
            $data =  UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId, 0, $ua_id);
            if ($data === false) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $stateId =  $data['ua_state_id'];
            $cityId =  $data['ua_city_id'];
            $addressFrm->fill($data);
        }

        $this->set('ua_id', $ua_id);
        $this->set('stateId', $stateId);
        $this->set('cityId', $cityId);
        $this->set('addressFrm', $addressFrm);
        $this->_template->render(false, false);
    }

    public function truncateDataRequestPopup()
    {
        $this->_template->render(false, false);
    }

    public function sendTruncateRequest()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $db = FatApp::getDb();

        $srch = new UserGdprRequestSearch();
        $srch->addCondition('ureq_user_id', '=', $userId);
        $srch->addCondition('ureq_type', '=', UserGdprRequest::TYPE_TRUNCATE);
        $srch->addCondition('ureq_status', '=', UserGdprRequest::STATUS_PENDING);
        $srch->addCondition('ureq_deleted', '=', applicationConstants::NO);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row) {
            Message::addErrorMessage(Labels::getLabel('LBL_You_have_alrady_submitted_the_request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $assignValues = array(
        'ureq_user_id'=>$userId,
        'ureq_type'=>UserGdprRequest::TYPE_TRUNCATE,
        'ureq_date'=>date('Y-m-d H:i:s'),
        );

        $userReqObj = new UserGdprRequest();
        $userReqObj->assignValues($assignValues);
        if (!$userReqObj->save()) {
            Message::addErrorMessage($userReqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        Message::addMessage(Labels::getLabel('MSG_Request_sent_successfully', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    private function getRequestDataForm()
    {
        $frm = new Form('frmRequestdata');
        $frm->addTextBox(Labels::getLabel('LBL_Email', $this->siteLangId), 'credential_email', '', array('readonly'=>'readonly'));
        $frm->addTextBox(Labels::getLabel('LBL_Name', $this->siteLangId), 'user_name', '', array('readonly'=>'readonly'));
        $purposeFld = $frm->addTextArea(Labels::getLabel('LBL_Purpose_of_Request_Data', $this->siteLangId), 'ureq_purpose');
        $purposeFld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Request', $this->siteLangId));
        return $frm;
    }

    public function requestDataForm()
    {
        $userObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $userObj->getUserSearchObj(array('credential_username','credential_email','user_name'));
        $rs = $srch->getResultSet();

        if (!$rs) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = FatApp::getDb()->fetch($rs, 'user_id');

        if ($data === false) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_GDPR_POLICY_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        $gdprPolicyLinkHref = '';
        if (!empty($cpage) && is_array($cpage)) {
            $gdprPolicyLinkHref = CommonHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
        }

        $frm = $this->getRequestDataForm();
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->set('gdprPolicyLinkHref', $gdprPolicyLinkHref);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function setupRequestData()
    {
        $frm = $this->getRequestDataForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $userId = UserAuthentication::getLoggedUserId();

        $srch = new UserGdprRequestSearch();
        $srch->addCondition('ureq_user_id', '=', $userId);
        $srch->addCondition('ureq_type', '=', UserGdprRequest::TYPE_DATA_REQUEST);
        $srch->addCondition('ureq_status', '=', UserGdprRequest::STATUS_PENDING);
        $srch->addCondition('ureq_deleted', '=', applicationConstants::NO);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row) {
            Message::addErrorMessage(Labels::getLabel('LBL_You_have_alrady_submitted_the_data_request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $assignValues = array(
        'ureq_user_id'=>$userId,
        'ureq_type'=>UserGdprRequest::TYPE_DATA_REQUEST,
        'ureq_date'=>date('Y-m-d H:i:s'),
        'ureq_purpose'=>$post['ureq_purpose'],
        );

        $userReqObj = new UserGdprRequest();
        $userReqObj->assignValues($assignValues);
        if (!$userReqObj->save()) {
            Message::addErrorMessage($userReqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post['user_id'] = $userId;
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendDataRequestNotification($post, $this->siteLangId)) {
            Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_REQUEST_SENT_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    //Valid for 10 Minutes only
    public function getTempToken()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $uObj=new User($userId);
        $tempToken = substr(md5(rand(1, 99999) . microtime()), 0, UserAuthentication::TOKEN_LENGTH);

        if (!$uObj->createUserTempToken($tempToken)) {
            FatUtility::dieJsonError($uObj->getError());
        }
        $this->set('data', array('tempToken' => $tempToken));
        $this->_template->render();
    }

    public function notifications()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $defaultPageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $pageSize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $defaultPageSize);
        $srch = Notifications::getSearchObject();
        $srch->addCondition('unt.unotification_user_id', '=', $userId);
        $srch->addOrder('unt.unotification_id', 'DESC');
        $srch->addMultipleFields(array('unt.*'));
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set('notifications', $records);
        $this->set('total_pages', $srch->pages());
        $this->set('total_records', $srch->recordCount());
        $this->_template->render();
    }

    public function markNotificationRead($notificationId)
    {
        $notificationId = FatUtility::int($notificationId);
        if (1 > $notificationId) {
            FatUtility::dieJSONError(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
        }
        $userId = UserAuthentication::getLoggedUserId();

        $srch = Notifications::getSearchObject();
        $srch->addCondition('unt.unotification_user_id', '=', $userId);
        $srch->addCondition('unt.unotification_id', '=', $notificationId);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $notification = FatApp::getDb()->fetch($rs);
        if (!($notification)) {
            FatUtility::dieJSONError(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
        }
        $nObj = new Notifications();
        if (!$nObj->readUserNotification($notificationId, $userId)) {
            FatUtility::dieJsonError($nObj->getError());
        }
        $this->set('msg', Labels::getLabel('Msg_Successfully_Updated', $this->siteLangId));
        $this->_template->render();
    }
}
