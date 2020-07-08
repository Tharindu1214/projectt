<?php
class Notification extends MyAppModel
{
    const DB_TBL = 'tbl_notifications';
    const DB_TBL_PREFIX = 'notification_';

    const TYPE_USER = 1;
    const TYPE_CATALOG = 3;
    const TYPE_BRAND = 4;
    const TYPE_ORDER = 5;
    const TYPE_ORDER_CANCELATION = 6;
    const TYPE_ORDER_PRODUCT = 7;
    const TYPE_ORDER_RETURN_REQUEST = 8;
    const TYPE_CATALOG_REQUEST = 9;
    const TYPE_PRODUCT_REVIEW = 10;
    const TYPE_WITHDRAWAL_REQUEST = 11;
    const TYPE_WITHDRAW_RETURN_REQUEST = 12;
    const TYPE_SHOP = 13;
    const TYPE_PROMOTION = 14;
    const TYPE_ADMIN = 15;
    const TYPE_ORDER_RETURN_REQUEST_MESSAGE = 16;
    const TYPE_BLOG = 17;

    const NEW_USER_REGISTERATION_NOTIFICATION = 1;
    const NEW_SUPPLIER_REGISTERATION_NOTIFICATION = 2;
    const NEW_CATALOG_REQUEST_NOTIFICATION = 5;
    const BRAND_REQUEST_NOTIFICATION = 7;
    const NEW_ORDER_STATUS_NOTIFICATION = 8;
    const ORDER_CANCELLATION_NOTIFICATION = 9;
    const ORDER_RETURNED_NOTIFICATION = 10;
    const ORDER_RETURNED_REQUEST_NOTIFICATION = 11;
    const CATALOG_REQUEST_MESSAGE_NOTIFICATION = 12;
    const NEW_SUBSCRIPTION_PURCHASE_NOTIFICATION = 16;
    const ABUSIVE_REVIEW_POSTED_NOTIFICATION = 17;
    const ORDER_RETURNED_REQUEST_MESSAGE_NOTIFICATION = 18;
    const NEW_SUPPLIER_APPROVAL_NOTIFICATION = 19;
    const NEW_SELLER_APPROVED_NOTIFICATION = 20;
    const PROMOTION_APPROVAL_NOTIFICATION = 21;
    const WITHDRAWL_REQUEST_NOTIFICATION = 22;
    const REPORT_SHOP_NOTIFICATION = 23;
    const ORDER_PAYMENT_STATUS_CHANGE_NOTIFICATION = 24;
    const RETURN_REQUEST_MESSAGE_TO_USER_NOTIFICATION = 25;
    const ORDER_EMAIL_NOTIFICATION = 26;
    const NEW_CUSTOM_CATALOG_REQUEST_NOTIFICATION = 27;
    const PRODUCT_REVIEW_NOTIFICATION = 28;
    const BLOG_COMMENT_NOTIFICATION = 29;
    const BLOG_CONTRIBUTION_NOTIFICATION = 30;

    const GUEST_AFFILIATE_REGISTERATION = 3;
    const GUEST_ADVISER_REGISTERATION = 4;
    const SUPPLIER_APPROVAL = 6;
    const RETURN_REQUEST_STATUS_CHANGE_NOTIFICATION = 13;
    const NOTIFICATION_ABUSIVE_WORD = 15;



    public function __construct($notificationId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $notificationId);
    }

    public static function saveNotifications($notificationData)
    {
        $notificationObj = new Notification();
        $notificationObj->assignValues($notificationData);
        if (!$notificationObj->save()) {
            return false;
        }
        return true;
    }


    public static function getLabelKeyString($langId)
    {
        $brandRequestApproval = FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL');
        $brandRequestUrl = ($brandRequestApproval) ? 'brands/brand-requests' : 'brands' ;

        $labelArr = array(
        Notification::NEW_USER_REGISTERATION_NOTIFICATION=>array(Labels::getLabel('user_registration_notification', $langId),'users'),
        Notification::NEW_SUPPLIER_REGISTERATION_NOTIFICATION=>array(Labels::getLabel('supplier_registration_notification', $langId),'users'),
        Notification::GUEST_AFFILIATE_REGISTERATION=>array(Labels::getLabel('guest_adviser_registration_notification', $langId),'users'),
        Notification::GUEST_ADVISER_REGISTERATION=>array(Labels::getLabel('user_order_placed_notification', $langId),'orders'),
        Notification::NEW_CATALOG_REQUEST_NOTIFICATION=>array(Labels::getLabel('user_catalog_request_notification', $langId),'users/seller-catalog-requests'),
        Notification::SUPPLIER_APPROVAL=>array(Labels::getLabel('user_supplier_approval_notification', $langId),'users/seller-approval-requests'),
        Notification::BRAND_REQUEST_NOTIFICATION=>array(Labels::getLabel('seller_brand_request_notification', $langId),$brandRequestUrl),
        Notification::NEW_ORDER_STATUS_NOTIFICATION=>array(Labels::getLabel('user_order_status_notification', $langId),'orders'),
        Notification::ORDER_CANCELLATION_NOTIFICATION=>array(Labels::getLabel('user_order_cancellation_notification', $langId),'order-cancellation-requests'),
        Notification::ORDER_RETURNED_NOTIFICATION=>array(Labels::getLabel('user_order_return_notification', $langId),'order-return-requests'),
        Notification::ORDER_RETURNED_REQUEST_NOTIFICATION=>array(Labels::getLabel('user_order_return_request_notification', $langId),'order-return-requests'),
        Notification::CATALOG_REQUEST_MESSAGE_NOTIFICATION=>array(Labels::getLabel('user_catalog_request_message_notification', $langId),'custom-products'),
        Notification::RETURN_REQUEST_STATUS_CHANGE_NOTIFICATION=>array(Labels::getLabel('buyer_return_request_status_change_notification', $langId),'order-return-requests'),
        Notification::REPORT_SHOP_NOTIFICATION=>array(Labels::getLabel('user_report_shop_notification', $langId),'shop-report-reasons'),
        Notification::NOTIFICATION_ABUSIVE_WORD=>array(Labels::getLabel('user_abusive_word_notification', $langId),''),
        Notification::NEW_SUBSCRIPTION_PURCHASE_NOTIFICATION=>array(Labels::getLabel('user_new_subscription_purchase_notification', $langId),''),
        Notification::PROMOTION_APPROVAL_NOTIFICATION=>array(Labels::getLabel('user_promotion_approval_notification', $langId),'promotions'),
        Notification::WITHDRAWL_REQUEST_NOTIFICATION=>array(Labels::getLabel('user_withdrawl_request_notification', $langId),'withdrawal-requests'),
        Notification::NEW_SUPPLIER_APPROVAL_NOTIFICATION=>array(Labels::getLabel('user_supplier_approval_notification', $langId),'users/seller-approval-requests'),
        Notification::NEW_SELLER_APPROVED_NOTIFICATION=>array(Labels::getLabel('user_seller_approved_notification', $langId),'seller-approval-requests'),
        Notification::ABUSIVE_REVIEW_POSTED_NOTIFICATION=>array(Labels::getLabel('admin_abusive_review_posted_notification', $langId),''),
        Notification::PRODUCT_REVIEW_NOTIFICATION=>array(Labels::getLabel('admin_Product_review_notification', $langId),'product-reviews'),
        Notification::ORDER_PAYMENT_STATUS_CHANGE_NOTIFICATION=>array(Labels::getLabel('admin_order_payment_status_change_notification', $langId),'orders'),
        Notification::ORDER_RETURNED_REQUEST_MESSAGE_NOTIFICATION=>array(Labels::getLabel('admin_order_return_request_message_notification', $langId),'order-return-requests'),
        Notification::RETURN_REQUEST_MESSAGE_TO_USER_NOTIFICATION=>array(Labels::getLabel('admin_order_return_request_message_to_user_notification', $langId),'order-return-requests'),
        Notification::ORDER_EMAIL_NOTIFICATION=>array(Labels::getLabel('admin_order_email_notification', $langId),''),
        Notification::NEW_CUSTOM_CATALOG_REQUEST_NOTIFICATION=>array(Labels::getLabel('admin_custom_catalog_request_notification', $langId),'custom-products'),
        Notification::BLOG_COMMENT_NOTIFICATION=>array(Labels::getLabel('user_blog_comment_notification', $langId),'blog-comments'),
        Notification::BLOG_CONTRIBUTION_NOTIFICATION=>array(Labels::getLabel('user_blog_contibution_notification', $langId),'blog-contributions'),
        );

        return $labelArr;
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'n');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.'.User::DB_TBL_PREFIX.'id = n.notification_user_id', 'u');
        //$srch->joinTable( User::DB_TBL_CRED,'LEFT OUTER JOIN','uc.'.User::DB_TBL_CRED_PREFIX.'user_id = u.user_id', 'uc' );

        $srch->addMultipleFields(
            array(
            'n.*',
            'u.'.User::DB_TBL_PREFIX.'name',
            )
        );

        return $srch;
    }

    public function deleteNotifications($recordId)
    {
        $db = FatApp::getDb();
        if (!$db->query("UPDATE tbl_notifications SET notification_deleted = 1 WHERE notification_id in (".$recordId.")")) {
            return false;
        }
        return true;
    }

    public function changeNotifyStatus($status, $recordId)
    {
        $db = FatApp::getDb();
        if (!$db->query("UPDATE tbl_notifications SET notification_marked_read = ".$status." WHERE notification_id in (".$recordId.")")) {
            return false;
        }
        return true;
    }

    public static function getPermissionsArr()
    {
        return array(
        static::TYPE_USER => AdminPrivilege::SECTION_USERS,
        static::TYPE_BRAND => AdminPrivilege::SECTION_BRANDS,
        static::TYPE_ORDER => AdminPrivilege::SECTION_ORDERS,
        static::TYPE_ORDER_PRODUCT => AdminPrivilege::SECTION_ORDERS,
        static::TYPE_ORDER_CANCELATION => AdminPrivilege::SECTION_ORDER_CANCELLATION_REQUESTS,
        static::TYPE_ORDER_RETURN_REQUEST => AdminPrivilege::SECTION_ORDER_RETURN_REQUESTS,
        static::TYPE_CATALOG => AdminPrivilege::SECTION_CATALOG_REQUESTS,
        static::TYPE_CATALOG_REQUEST => AdminPrivilege::SECTION_CATALOG_REQUESTS,
        static::TYPE_PRODUCT_REVIEW => AdminPrivilege::SECTION_PRODUCT_REVIEWS,
        static::TYPE_WITHDRAWAL_REQUEST => AdminPrivilege::SECTION_WITHDRAW_REQUESTS,
        static::TYPE_WITHDRAW_RETURN_REQUEST => AdminPrivilege::SECTION_WITHDRAW_REQUESTS,
        static::TYPE_SHOP => AdminPrivilege::SECTION_SHOPS,
        static::TYPE_PROMOTION => AdminPrivilege::SECTION_PROMOTIONS,
        static::TYPE_ORDER_RETURN_REQUEST_MESSAGE => AdminPrivilege::SECTION_ORDER_RETURN_REQUESTS,
        static::TYPE_BLOG => AdminPrivilege::SECTION_BLOG_POSTS,
        static::TYPE_ADMIN => AdminPrivilege::SECTION_ADMIN_USERS,
        );
    }

    public static function getAllowedRecordTypeArr($adminId)
    {
        if (AdminPrivilege::isAdminSuperAdmin($adminId)) {
            return array_flip(self::getPermissionsArr());
        }

        $privilege = new AdminPrivilege();
        $userPermissions = array_filter($privilege->getAdminPermissionLevel($adminId));

        $permissionsArr = self::getPermissionsArr();
        $validType = array(-1 => -1);
        foreach ($permissionsArr as $notificationType => $permissionType) {
            if (array_key_exists($permissionType, $userPermissions)) {
                $validType[] = $notificationType;
            }
        }
        return $validType;
    }
}
