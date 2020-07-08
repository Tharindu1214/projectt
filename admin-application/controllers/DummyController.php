<?php
class DummyController extends AdminBaseController
{



    public function index()
    {

        $srch = Product::getSearchObject($this->adminLangId);
        $cnd = $srch->addCondition('product_id', '>', '5');
        $cnd->attachCondition('mysql_func_product_brand_id', '>=', 'mysql_func_product_added_by_admin_id', 'OR', true);
        echo $srch->getQuery(); exit;
        echo time();
        $arr1 = array('a'=>'1');
        $arr2 = array('b'=>'2');
        array_push($arr1, $arr2);
        var_dump($arr1);

        exit;


        echo "<br>";
        echo $colCount++;
        echo "<br>";
        echo $colCount++;        exit;
        $arr = unserialize(FatApp::getConfig('CONF_COMPLETED_ORDER_STATUS'));
        var_dump($arr);
        exit;

        $statsObj = new Statistics();
        $data = $statsObj->getTopProducts('YEARLY');
        var_dump($data);
    }

    function test123()
    {
        $langId = 1;
        $spreviewId = 1;
        $schObj = new SelProdReviewSearch($langId);
        $schObj->joinUser();
        $schObj->joinProducts($langId);
        $schObj->joinSellerProducts($langId);
        $schObj->addCondition('spreview_id', '=', $spreviewId);
        $schObj->addCondition('spreview_status', '!=', SelProdReview::STATUS_PENDING);
        $schObj->addMultipleFields(array('spreview_selprod_id','spreview_status', 'product_name', 'selprod_title', 'user_name', 'credential_email',));
        $spreviewData = FatApp::getDb()->fetch($schObj->getResultSet());
        $productUrl = CommonHelper::generateFullUrl('Products', 'View', array($spreviewData["spreview_selprod_id"]), CONF_WEBROOT_FRONT_URL);
        echo $prodTitleAnchor = "<a href='" . $productUrl . "'>" . $spreviewData['selprod_title'] . "</a>";
        CommonHelper::printArray($prodTitleAnchor); die;
    }

}
