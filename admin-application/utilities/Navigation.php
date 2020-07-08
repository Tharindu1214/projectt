<?php
class Navigation
{
    public static function setLeftNavigationVals($template)
    {
        $db = FatApp::getDb();
        $langId = CommonHelper::getLangId();
        $userObj = new User();
        
        /* seller approval requests */
        $supReqSrchObj = $userObj->getUserSupplierRequestsObj();
        $supReqSrchObj->addCondition('usuprequest_status', '=', 0);
        $supReqSrchObj->addMultipleFields(array('count(usuprequest_id) as countOfRec'));
        $supReqResult = $db->fetch($supReqSrchObj->getResultset());
        $supReqCount = FatUtility::int($supReqResult['countOfRec']);
        
        /* product catalog requests */
        $catReqSrchObj = $userObj->getUserCatalogRequestsObj();
        $catReqSrchObj->addCondition('scatrequest_status', '=', 0);
        $catReqSrchObj->addMultipleFields(array('count(scatrequest_id) as countOfRec'));
        $catReqResult = $db->fetch($catReqSrchObj->getResultset());
        $catReqCount = FatUtility::int($catReqResult['countOfRec']);
        
        /* Custom catalog requests */
        $custReqSrchObj = ProductRequest::getSearchObject(0, false, true);
        $custReqSrchObj->addCondition('preq_status', '=', ProductRequest::STATUS_PENDING);
        $custReqSrchObj->addMultipleFields(array('count(preq_id) as countOfRec'));
        $custProdReqResult = $db->fetch($custReqSrchObj->getResultset());
        $custProdReqCount = FatUtility::int($custProdReqResult['countOfRec']);
        
        /* Custom brand requests */
        $brandReqSrchObj = Brand::getSearchObject(0, true, false);
        $brandReqSrchObj->addCondition('brand_status', '=', Brand::BRAND_REQUEST_PENDING);
        $brandReqSrchObj->addMultipleFields(array('count(brand_id) as countOfRec'));
        $brandReqResult = $db->fetch($brandReqSrchObj->getResultset());
        $brandReqCount = FatUtility::int($brandReqResult['countOfRec']);
        
        /* withdrawal requests */
        $drReqSrchObj = new WithdrawalRequestsSearch();
        $drReqSrchObj->addCondition('withdrawal_status', '=', 0);
        $drReqSrchObj->addMultipleFields(array('count(withdrawal_id) as countOfRec'));
        $drReqResult = $db->fetch($drReqSrchObj->getResultset());
        $drReqCount = FatUtility::int($drReqResult['countOfRec']);
        
        /* order cancellation requests */
        $orderCancelReqSrchObj = new OrderCancelRequestSearch($langId);
        $orderCancelReqSrchObj->addCondition('ocrequest_status', '=', 0);
        $orderCancelReqSrchObj->addMultipleFields(array('count(ocrequest_id) as countOfRec'));
        $orderCancelReqResult = $db->fetch($orderCancelReqSrchObj->getResultset());
        $orderCancelReqCount = FatUtility::int($orderCancelReqResult['countOfRec']);
        
        /* order return/refund requests */
        $orderRetReqSrchObj = new OrderReturnRequestSearch();
        $orderRetReqSrchObj->addCondition('orrequest_status', '=', 0);
        $orderRetReqSrchObj->addMultipleFields(array('count(orrequest_id) as countOfRec'));
        $orderRetReqResult = $db->fetch($orderRetReqSrchObj->getResultset());
        $orderRetReqCount = FatUtility::int($orderRetReqResult['countOfRec']);
        
        /* blog contributions */
        $blogContrSrchObj = BlogContribution::getSearchObject();
        $blogContrSrchObj->addCondition('bcontributions_status', '=', 0);
        $blogContrSrchObj->addMultipleFields(array('count(bcontributions_id) as countOfRec'));
        $blogContrResult = $db->fetch($blogContrSrchObj->getResultset());
        $blogContrCount = FatUtility::int($blogContrResult['countOfRec']);
        
        /* blog comments */
        $blogCommentsSrchObj = BlogComment::getSearchObject();
        $blogCommentsSrchObj->addCondition('bpcomment_approved', '=', 0);
        $blogCommentsSrchObj->addMultipleFields(array('count(bpcomment_id) as countOfRec'));
        $blogCommentsResult = $db->fetch($blogCommentsSrchObj->getResultset());
        $blogCommentsCount = FatUtility::int($blogCommentsResult['countOfRec']);
        
        /* threshold level products */
        $selProdSrchObj = SellerProduct::getSearchObject($langId);
        
        $selProdSrchObj->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $selProdSrchObj->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.CommonHelper::getLangId(), 'p_l');
        $selProdSrchObj->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'cred.credential_user_id = selprod_user_id', 'cred');
        $selProdSrchObj->joinTable('tbl_email_archives', 'LEFT OUTER JOIN', 'arch.emailarchive_to_email = cred.credential_email', 'arch');        
        $selProdSrchObj->addDirectCondition('selprod_stock <= selprod_threshold_stock_level');
        $selProdSrchObj->addDirectCondition('selprod_track_inventory = '.Product::INVENTORY_TRACK);
        
        $selProdSrchObj->addCondition('emailarchive_tpl_name', 'LIKE', 'threshold_notification_vendor_custom');
        $selProdSrchObj->addMultipleFields(array('count(selprod_id) as countOfRec'));
        $threshSelProdResult = $db->fetch($selProdSrchObj->getResultset());
        $threshSelProdCount = FatUtility::int($threshSelProdResult['countOfRec']);    
        
        /* seller orders */
        $sellerOrderStatus = FatApp::getConfig('CONF_BADGE_COUNT_ORDER_STATUS', FatUtility::VAR_STRING, '0');
        if($sellerOrderStatus && $sellerOrderStatusArr = (array)unserialize($sellerOrderStatus)) {
            $sellerOrderSrchObj = new OrderProductSearch($langId);
            $sellerOrderSrchObj->addStatusCondition($sellerOrderStatusArr);            
            $sellerOrderSrchObj->addMultipleFields(array('count(op_id) as countOfRec'));
            $sellerOrderResult = $db->fetch($sellerOrderSrchObj->getResultset());
            $sellerOrderCount = FatUtility::int($sellerOrderResult['countOfRec']);
            $template->set('sellerOrderCount', $sellerOrderCount);
        }
        
        /* User GDPR requests */
        $gdprSrch = new UserGdprRequestSearch();
        $gdprSrch->addCondition('ureq_status', '=', UserGdprRequest::STATUS_PENDING);
        $gdprSrch->addCondition('ureq_deleted', '=', applicationConstants::NO);
        $gdprSrch->getResultSet();
        $gdprReqCount = $gdprSrch->recordCount();
        
        /* set counter variables [ */
        $template->set('brandReqCount', $brandReqCount);
        $template->set('custProdReqCount', $custProdReqCount);
        $template->set('supReqCount', $supReqCount);
        $template->set('catReqCount', $catReqCount);
        $template->set('drReqCount', $drReqCount);
        $template->set('orderCancelReqCount', $orderCancelReqCount);
        $template->set('orderRetReqCount', $orderRetReqCount);
        $template->set('blogContrCount', $blogContrCount);
        $template->set('blogCommentsCount', $blogCommentsCount);
        $template->set('threshSelProdCount', $threshSelProdCount);
        $template->set('gdprReqCount', $gdprReqCount);
        $template->set('adminLangId', CommonHelper::getLangId());
        /* ] */
        
        $template->set('objPrivilege', AdminPrivilege::getInstance());
        $template->set('adminName', AdminAuthentication::getLoggedAdminAttribute("admin_name"));
    }
}
