<?php
class SavedSearchProduct extends MyAppModel
{
    const DB_TBL = 'tbl_product_saved_search';
    const DB_TBL_PREFIX = 'pssearch_';

    const PAGE_CATEGORY = 1;
    const PAGE_PRODUCT = 2;
    const PAGE_BRAND = 3;
    const PAGE_SHOP = 4;
    const PAGE_FEATURED_PRODUCT = 5;
    const PAGE_PRODUCT_INDEX = 6;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getPageUrl()
    {
        return array(
        static::PAGE_CATEGORY    =>    'Category/view/',
        static::PAGE_PRODUCT    =>    'Products/search/',
        static::PAGE_PRODUCT_INDEX    =>    'Products/index/',
        static::PAGE_BRAND    =>    'Brands/view/',
        static::PAGE_SHOP    =>    'Shops/view/',
        static::PAGE_FEATURED_PRODUCT    =>    'Products/featured/'
        );
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'sps');
        return $srch;
    }

    public static function getSearchPageFullUrl($type, $recordId)
    {
        $url = '';
        switch ($type) {
            case static::PAGE_CATEGORY:
                $url = CommonHelper::generateFullUrl('Category', 'view', array($recordId));
                break;
            case static::PAGE_PRODUCT:
                $url = CommonHelper::generateFullUrl('Products', 'search');
                break;
            case static::PAGE_PRODUCT_INDEX:
                $url = CommonHelper::generateFullUrl('Products', 'index');
                break;
            case static::PAGE_BRAND:
                $url = CommonHelper::generateFullUrl('Brands', 'view', array($recordId));
                break;
            case static::PAGE_SHOP:
                $url = CommonHelper::generateFullUrl('Shops', 'view', array($recordId));
                break;
            case static::PAGE_FEATURED_PRODUCT:
                $url = CommonHelper::generateFullUrl('Products', 'featured');
                break;
        }
        return $url;
    }

    public static function getSearhResultFormat($arr, $langId = 0)
    {
        $result = [];
        $seperator = ' , ';
        $count = 1;
        foreach ($arr as $key => $row) {
            switch ($key) {
                case 'price-min-range':
                    $result[$count]['label'] = Labels::getLabel('LBL_Price_min', $langId);
                    $result[$count]['value'] = $row;
                    break;
                case 'price-max-range':
                    $result[$count]['label'] = Labels::getLabel('LBL_Price_max', $langId);
                    $result[$count]['value'] = $row;
                    break;
                case 'featured':
                    $result[$count]['label'] = Labels::getLabel('LBL_Featured', $langId);
                    $result[$count]['value'] = Labels::getLabel('LBL_Yes', $langId);
                    break;
                case 'currency_id':
                    $currency = Currency::getAttributesById($row, array('currency_code'));
                    if ($currency) {
                        $result[$count]['label'] = Labels::getLabel('LBL_Currency', $langId);
                        $result[$count]['value'] = $currency['currency_code'];
                    }
                    break;
                case 'brand':
                    $brand = Brand::getSearchObject($langId);
                    $brand->addMultipleFields(array('IFNULL(brand_name,brand_identifier) as brand_name,brand_identifier'));
                    $brand->addCondition('brand_id', 'in', $row);
                    $rs = $brand->getResultSet();
                    $brandData = FatApp::getDb()->fetchAll($rs);
                    if (!empty($brandData)) {
                        $result[$count]['label'] = Labels::getLabel('LBL_Brand', $langId);
                        $result[$count]['value'] = [];
                        foreach ($brandData as $val) {
                            $result[$count]['value'][] = ($val['brand_name']!='')?$val['brand_name']:$val['brand_identifier'];
                        }
                    }
                    break;
                case 'prodcat':
                    $productCategory = ProductCategory::getSearchObject(false, $langId);
                    $productCategory->addMultipleFields(array('IFNULL(prodcat_name,prodcat_identifier) as prodcat_name,prodcat_identifier'));
                    $productCategory->addCondition('prodcat_id', 'in', $row);
                    $rs = $productCategory->getResultSet();
                    $productCategoryData = FatApp::getDb()->fetchAll($rs);
                    if (!empty($productCategoryData)) {
                        $result[$count]['label'] = Labels::getLabel('LBL_Category', $langId);
                        $result[$count]['value'] = [];
                        foreach ($productCategoryData as $val) {
                            $result[$count]['value'][] = ($val['prodcat_name']!='')?$val['prodcat_name']:$val['prodcat_identifier'];
                        }
                    }
                    break;
                case 'condition':
                    $conditionArr = Product::getConditionArr($langId);
                    $result[$count]['label'] = Labels::getLabel('LBL_Condition', $langId);
                    $result[$count]['value'] = [];
                    foreach ($row as $val) {
                        if (!array_key_exists($val, $conditionArr)) {
                            continue;
                        }
                        $result[$count]['value'][] = $conditionArr[$val];
                    }
                    break;
                case 'optionvalue':
                    $optionValue = OptionValue::getSearchObject($langId);
                    $optionValue->addMultipleFields(array('IFNULL(optionvalue_name,optionvalue_identifier) as optionvalue_name,optionvalue_identifier'));
                    $optionValue->addCondition('optionvalue_id', 'in', $row);
                    $rs = $optionValue->getResultSet();
                    $optionValueData = FatApp::getDb()->fetchAll($rs);
                    if (!empty($optionValueData)) {
                        $result[$count]['label'] = Labels::getLabel('LBL_Options', $langId);
                        $result[$count]['value'] = [];
                        foreach ($optionValueData as $val) {
                            $result[$count]['value'][]= ($val['optionvalue_name']!='')?$val['optionvalue_name']:$val['optionvalue_identifier'];
                        }
                    }
                    break;
                case 'availability':
                    if (in_array(1, $row)) {
                        $result[$count]['label'] = Labels::getLabel('LBL_Out_of_stock', $langId);
                        $result[$count]['value'] = Labels::getLabel('LBL_Yes', $langId);
                    }
                    break;
            }
            $count++;
        }
        return $result;
    }

    /* public static function getSearhResultFormat($arr,$langId = 0){
    $string = '';
    $seperator = ' , ';
    foreach($arr as $key=>$row){
    switch($key){
                case 'price-min-range':
                    $string.= Labels::getLabel('LBL_Price',$langId).' ';
                    $string.= $row.' - ';
                break;
                case 'price-max-range':
                    $string.= $row.''.$seperator;
                break;
                case 'featured':
                    $string.= Labels::getLabel('LBL_Featured',$langId).''.$seperator;
                break;
                case 'currency_id':
                    $currency = Currency::getAttributesById($row,array('currency_code'));
                    if($currency){
                        $string.= $currency['currency_code'].''.$seperator;
                    }
                break;
                case 'brand':
                    $brand = Brand::getSearchObject($langId);
                    $brand->addMultipleFields(array('IFNULL(brand_name,brand_identifier) as brand_name,brand_identifier'));
                    $brand->addCondition('brand_id','in',$row);
                    $rs = $brand->getResultSet();
                    $brandData = FatApp::getDb()->fetchAll($rs);
                    if(!empty($brandData)){
                        $string.= Labels::getLabel('LBL_Brand',$langId).' : ';
                        foreach($brandData as $val){
                            $string.= ($val['brand_name']!='')?$val['brand_name']:$val['brand_identifier'];
                            $string.= $seperator;
                        }
                    }
                break;
                case 'prodcat':
                    $productCategory = ProductCategory::getSearchObject(false,$langId);
                    $productCategory->addMultipleFields(array('IFNULL(prodcat_name,prodcat_identifier) as prodcat_name,prodcat_identifier'));
                    $productCategory->addCondition('prodcat_id','in',$row);
                    $rs = $productCategory->getResultSet();
                    $productCategoryData = FatApp::getDb()->fetchAll($rs);
                    if(!empty($productCategoryData)){
                        $string.= Labels::getLabel('LBL_Category',$langId).' : ';
                        foreach($productCategoryData as $val){
                            $string.= ($val['prodcat_name']!='')?$val['prodcat_name']:$val['prodcat_identifier'];
                            $string.= $seperator;
                        }
                    }
                break;
                case 'condition':
                    $conditionArr = Product::getConditionArr($langId);
                    $string.= Labels::getLabel('LBL_Condition',$langId).' : ';
                    foreach($row as $val){
                        if(!array_key_exists($val,$conditionArr)){
                            continue;
                        }
                        $string.= $conditionArr[$val].''.$seperator;
                    }
                break;
                case 'optionvalue':
                    $optionValue = OptionValue::getSearchObject($langId);
                    $optionValue->addMultipleFields(array('IFNULL(optionvalue_name,optionvalue_identifier) as optionvalue_name,optionvalue_identifier'));
                    $optionValue->addCondition('optionvalue_id','in',$row);
                    $rs = $optionValue->getResultSet();
                    $optionValueData = FatApp::getDb()->fetchAll($rs);
                    if(!empty($optionValueData)){
                        $string.= Labels::getLabel('LBL_Options',$langId).' : ';
                        foreach($optionValueData as $val){
                            $string.= ($val['optionvalue_name']!='')?$val['optionvalue_name']:$val['optionvalue_identifier'];
                            $string.= $seperator;
                        }
                    }
                break;
                case 'availability':
                    if(in_array(1,$row)){
                        $string.= Labels::getLabel('LBL_Exclude_Out_of_stock',$langId).$seperator ;
                    }
                break;
    }
    }
    return rtrim($string,$seperator);
    } */
}
