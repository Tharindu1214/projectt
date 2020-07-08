<?php
class Importexport extends ImportexportCommon
{
    const DB_TBL_SETTINGS = 'tbl_import_export_settings';
    const DB_TBL_TEMP_SELPROD_IDS = 'tbl_seller_products_temp_ids';
    const DB_TBL_TEMP_PRODUCT_IDS = 'tbl_products_temp_ids';

    const TYPE_CATEGORIES = 1;
    const TYPE_BRANDS = 2;
    const TYPE_PRODUCTS = 3;
    const TYPE_SELLER_PRODUCTS = 4;
    const TYPE_OPTIONS = 5;
    const TYPE_OPTION_VALUES = 6;
    const TYPE_TAG = 7;
    const TYPE_COUNTRY = 8;
    const TYPE_STATE = 9;
    const TYPE_POLICY_POINTS = 10;
    const TYPE_USERS = 11;
    const TYPE_TAX_CATEGORY = 12;
    const TYPE_CITY = 13;
    const TYPE_SHIPPING_SETTING = 21;
    const TYPE_PRODUCT_SHIPPING_RATE = 22;

    const MAX_LIMIT = 1000;

    const PRODUCT_CATALOG = 1;
    const PRODUCT_OPTION = 2;
    const PRODUCT_TAG = 3;
    const PRODUCT_SPECIFICATION = 4;
    const PRODUCT_SHIPPING = 5;

    const LABEL_OPTIONS = 1;
    const LABEL_OPTIONS_VALUES = 2;

    const SELLER_PROD_GENERAL_DATA = 6;
    const SELLER_PROD_OPTION = 7;
    const SELLER_PROD_SEO = 8;
    const SELLER_PROD_SPECIAL_PRICE = 9;
    const SELLER_PROD_VOLUME_DISCOUNT = 10;
    const SELLER_PROD_BUY_TOGTHER = 11;
    const SELLER_PROD_RELATED_PRODUCT = 12;
    const SELLER_PROD_POLICY = 13;

    const BY_ID_RANGE = 1;
    const BY_BATCHES = 2;

    private $headingIndexArr = array();
    private $CSVfileObj;

    public static function getImportExportTypeArr($type, $langId, $sellerDashboard = false)
    {
        switch (strtoupper($type)) {
            case 'EXPORT':
                $arr[static::TYPE_CATEGORIES] = Labels::getLabel('LBL_Export_Categories', $langId);
                $arr[static::TYPE_PRODUCTS] = Labels::getLabel('LBL_Export_Catalogs', $langId);
                $arr[static::TYPE_SELLER_PRODUCTS] = Labels::getLabel('LBL_Export_Seller_Products', $langId);
                $arr[static::TYPE_BRANDS] = Labels::getLabel('LBL_Export_Brands', $langId);
                $arr[static::TYPE_OPTIONS] = Labels::getLabel('LBL_Export_Options', $langId);
                $arr[static::TYPE_OPTION_VALUES] = Labels::getLabel('LBL_Export_Option_Values', $langId);
                $arr[static::TYPE_TAG] = Labels::getLabel('LBL_Export_Tags', $langId);
                $arr[static::TYPE_COUNTRY] = Labels::getLabel('LBL_Export_Countries', $langId);
                $arr[static::TYPE_STATE] = Labels::getLabel('LBL_Export_States', $langId);
                $arr[static::TYPE_CITY] = Labels::getLabel('LBL_Export_Cities', $langId);
                $arr[static::TYPE_POLICY_POINTS] = Labels::getLabel('LBL_Export_Policy_Points', $langId);
                if (!$sellerDashboard) {
                    $arr[static::TYPE_USERS] = Labels::getLabel('LBL_Export_users', $langId);
                }
                $arr[static::TYPE_TAX_CATEGORY] = Labels::getLabel('LBL_Export_Tax_Category', $langId);
                break;
            case 'IMPORT':
                if (!$sellerDashboard) {
                    $arr[static::TYPE_CATEGORIES] = Labels::getLabel('LBL_Import_Categories', $langId);
                    $arr[static::TYPE_BRANDS] = Labels::getLabel('LBL_Import_Brands', $langId);
                }

                $arr[static::TYPE_PRODUCTS] = Labels::getLabel('LBL_Import_Catalogs', $langId);

                if (!$sellerDashboard) {
                    $arr[static::TYPE_OPTIONS] = Labels::getLabel('LBL_Import_Options', $langId);
                    $arr[static::TYPE_OPTION_VALUES] = Labels::getLabel('LBL_Import_Option_Values', $langId);
                    $arr[static::TYPE_TAG] = Labels::getLabel('LBL_Import_Tags', $langId);
                    $arr[static::TYPE_COUNTRY] = Labels::getLabel('LBL_Import_Countries', $langId);
                    $arr[static::TYPE_STATE] = Labels::getLabel('LBL_Import_States', $langId);
                    $arr[static::TYPE_CITY] = Labels::getLabel('LBL_Import_Cities', $langId);
                    $arr[static::TYPE_POLICY_POINTS] = Labels::getLabel('LBL_Import_Policy_Points', $langId);
                }
                $arr[static::TYPE_SELLER_PRODUCTS] = Labels::getLabel('LBL_Import_Seller_Products', $langId);
                if (!$sellerDashboard) {
                    $arr[static::TYPE_USERS] = Labels::getLabel('LBL_Import_users', $langId);
                    $arr[static::TYPE_TAX_CATEGORY] = Labels::getLabel('LBL_Import_Tax_Category', $langId);
                }
                break;
        }

        return $arr;
    }

    public static function getOptionContentTypeArr($langId)
    {
        $arr = array(
        static::LABEL_OPTIONS=>Labels::getLabel('LBL_Options', $langId),
        static::LABEL_OPTIONS_VALUES=>Labels::getLabel('LBL_Option_Values', $langId),
        );
        return $arr;
    }

    public static function getProductCatalogContentTypeArr($langId)
    {
        $arr = array(
        static::PRODUCT_CATALOG=>Labels::getLabel('LBL_Product_Catalog', $langId),
        static::PRODUCT_OPTION=>Labels::getLabel('LBL_Product_Options', $langId),
        static::PRODUCT_TAG=>Labels::getLabel('LBL_Product_Tags', $langId),
        static::PRODUCT_SPECIFICATION=>Labels::getLabel('LBL_Product_Specifications', $langId),
        static::PRODUCT_SHIPPING=>Labels::getLabel('LBL_Product_Shipping', $langId),
        );
        return $arr;
    }

    public static function getSellerProductContentTypeArr($langId)
    {
        $arr = array(
        static::SELLER_PROD_GENERAL_DATA=>Labels::getLabel('LBL_General_Data', $langId),
        static::SELLER_PROD_OPTION=>Labels::getLabel('LBL_Product_Options', $langId),
        static::SELLER_PROD_SEO=>Labels::getLabel('LBL_SEO_Data', $langId),
        static::SELLER_PROD_SPECIAL_PRICE=>Labels::getLabel('LBL_Special_Price', $langId),
        static::SELLER_PROD_VOLUME_DISCOUNT=>Labels::getLabel('LBL_Volume_Discount', $langId),
        static::SELLER_PROD_BUY_TOGTHER=>Labels::getLabel('LBL_Buy_togther', $langId),
        static::SELLER_PROD_RELATED_PRODUCT=>Labels::getLabel('LBL_Related_products', $langId),
        static::SELLER_PROD_POLICY=>Labels::getLabel('LBL_Seller_Product_Policy', $langId),
        );
        return $arr;
    }

    public static function getDataRangeArr($langId)
    {
        $arr = array(
        static::BY_ID_RANGE=>Labels::getLabel('LBL_By_id_range', $langId),
        static::BY_BATCHES=>Labels::getLabel('LBL_By_batches', $langId),
        );
        return $arr;
    }

    public function getCsvFilePointer($fileTempName)
    {
        return fopen($fileTempName, 'r');
    }

    public function getFileRow($csvFilePointer)
    {
        return fgetcsv($csvFilePointer);
    }

    public function getCell($arr = array(), $index, $defaultValue = '')
    {
        if (array_key_exists($index, $arr) && trim($arr[$index]) != '') {
            $str = str_replace("\xc2\xa0", '', trim($arr[$index]));
            return str_replace("\xa0", '', $str);
        }
        return $defaultValue;
    }

    private function validateCSVHeaders($csvFilePointer, $coloumArr, $langId)
    {
        $headingRow = $this->getFileRow($csvFilePointer);
        if (!$this->isValidColumns($headingRow, $coloumArr)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $langId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->headingIndexArr = array_flip($headingRow);
    }

    public function export($type, $langId, $sheetType, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = 0)
    {
        $all = !isset($offset) && !isset($noOfRows) && !isset($minId) && !isset($maxId);
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        $sheetData = array();
        $sheetName = '';

        if (isset($offset) && isset($noOfRows)) {
            $sheetName .='_'.$offset;
        }

        if (isset($minId) && isset($maxId)) {
            $sheetName .='_'.$minId.'-'.$maxId;
        }

        $default = false;
        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportBrands($langId, $userId);
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Category', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCategories($langId, $userId);
                break;
            case Importexport::TYPE_PRODUCTS:
                switch ($sheetType) {
                    case Importexport::PRODUCT_CATALOG:
                        $sheetName = Labels::getLabel('LBL_Product_Catalogs', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductsCatalog($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_OPTION:
                        $sheetName = Labels::getLabel('LBL_Catalog_Options', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductOptions($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_TAG:
                        $sheetName = Labels::getLabel('LBL_Catalog_Tags', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductTags($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_SPECIFICATION:
                        $sheetName = Labels::getLabel('LBL_Catalog_Specification', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductSpecification($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_SHIPPING:
                        $sheetName = Labels::getLabel('LBL_Catalog_Shipping', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductShipping($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                switch ($sheetType) {
                    case Importexport::SELLER_PROD_GENERAL_DATA:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_General', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdGeneralData($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_OPTION:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Option', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdOptionData($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_SEO:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Seo', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdSeoData($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_SPECIAL_PRICE:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Special_price', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdSpecialPrice($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_VOLUME_DISCOUNT:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Volume_Discount', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdVolumeDiscount($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_BUY_TOGTHER:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Buy_Together', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdBuyTogther($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_RELATED_PRODUCT:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Related_Prod', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdRelatedProd($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_POLICY:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Policy', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdPolicy($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_OPTIONS:
                switch ($sheetType) {
                    case Importexport::LABEL_OPTIONS:
                        $sheetName = Labels::getLabel('LBL_Options', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportOptions($langId, $userId);
                        break;
                    case Importexport::LABEL_OPTIONS_VALUES:
                        $sheetName = Labels::getLabel('LBL_Option_Values', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportOptionValues($langId, $userId);
                        break;
                    default:
                        $sheetName = Labels::getLabel('LBL_Options', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportOptions($langId, $userId);
                        break;
                }
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $sheetName = Labels::getLabel('LBL_Option_Values', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportOptionValues($langId, $userId);
                break;
            case Importexport::TYPE_TAG:
                $sheetName = Labels::getLabel('LBL_Tags', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportTags($langId, $userId);
                break;
            case Importexport::TYPE_COUNTRY:
                $sheetName = Labels::getLabel('LBL_Country', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCountries($langId, $userId);
                break;
            case Importexport::TYPE_STATE:
                $sheetName = Labels::getLabel('LBL_State', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportStates($langId, $userId);
                break;
            case Importexport::TYPE_CITY:
                $sheetName = Labels::getLabel('LBL_City', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCities($langId, $userId);
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $sheetName = Labels::getLabel('LBL_Policy_points', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportPolicyPoints($langId, $userId);
                break;
            case Importexport::TYPE_USERS:
                $sheetName = Labels::getLabel('LBL_Users', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportUsers($langId);
                break;
            case Importexport::TYPE_TAX_CATEGORY:
                $sheetName = Labels::getLabel('LBL_Tax_Category', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportTaxCategory($langId, $userId);
                break;
            default:
                $default = true;
                break;
        }

        if ($default) {
            Message::addMessage(Labels::getLabel('MSG_Invalid_Access', $langId));
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function exportMedia($type, $langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = 0)
    {
        $all = !isset($offset) && !isset($noOfRows) && !isset($minId) && !isset($maxId);
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        $sheetData = array();
        $sheetName = '';
        if (isset($offset) && isset($noOfRows)) {
            $sheetName .='_'.$offset;
        }

        if (isset($minId) && isset($maxId)) {
            $sheetName .='_'.$minId.'-'.$maxId;
        }
        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands_Media', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportBrandMedia($langId);
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Category_Media', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCategoryMedia($langId);
                break;
            case Importexport::TYPE_PRODUCTS:
                $sheetName = Labels::getLabel('LBL_Product_Media', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportProductMedia($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $sheetName = Labels::getLabel('LBL_Seller_Product_Digital_File', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportSellerProductMedia($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                break;
        }
    }

    public function import($type, $langId, $sheetType = '', $userId = 0)
    {
        $post = FatApp::getPostedData();
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        $csvFilePointer = $this->getCsvFilePointer($_FILES['import_file']['tmp_name']);
        $default = false;
        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importBrands($csvFilePointer, $post, $langId, $userId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Categories_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCategories($csvFilePointer, $post, $langId, $userId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_PRODUCTS:
                switch ($sheetType) {
                    case Importexport::PRODUCT_CATALOG:
                        $sheetName = Labels::getLabel('LBL_Products_catalog_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductsCatalog($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::PRODUCT_OPTION:
                        $sheetName = Labels::getLabel('LBL_Product_Options_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductOptions($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::PRODUCT_TAG:
                        $sheetName = Labels::getLabel('LBL_Product_Tags_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductTags($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::PRODUCT_SPECIFICATION:
                        $sheetName = Labels::getLabel('LBL_Product_Specifications_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductSpecifications($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::PRODUCT_SHIPPING:
                        $sheetName = Labels::getLabel('LBL_Product_Shipping_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductShipping($csvFilePointer, $post, $langId, $userId);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                switch ($sheetType) {
                    case Importexport::SELLER_PROD_GENERAL_DATA:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_General_Data_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdGeneralData($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::SELLER_PROD_OPTION:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Option_Data_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdOptionData($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::SELLER_PROD_SEO:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Seo_Data_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdSeoData($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_SPECIAL_PRICE:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Special_Price_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdSpecialPrice($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::SELLER_PROD_VOLUME_DISCOUNT:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Volume_Discount_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdVolumeDiscount($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_BUY_TOGTHER:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Buy_Togther_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdBuyTogther($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_RELATED_PRODUCT:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Related_Product_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdRelatedProd($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_POLICY:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Policy_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdPolicy($csvFilePointer, $post, $langId, $userId);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_OPTIONS:
                switch ($sheetType) {
                    case Importexport::PRODUCT_CATALOG:
                        $sheetName = Labels::getLabel('LBL_Options_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importOptions($csvFilePointer, $post, $langId);
                        break;
                    case Importexport::PRODUCT_OPTION:
                        $sheetName = Labels::getLabel('LBL_Option_Values_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importOptionValues($csvFilePointer, $post, $langId);
                        break;
                }
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $sheetName = Labels::getLabel('LBL_Option_Values_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importOptionValues($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_TAG:
                $sheetName = Labels::getLabel('LBL_Tags_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importTags($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_COUNTRY:
                $sheetName = Labels::getLabel('LBL_Countries_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCountries($csvFilePointer, $post, $langId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_STATE:
                $sheetName = Labels::getLabel('LBL_States_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importStates($csvFilePointer, $post, $langId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_CITY:
                $sheetName = Labels::getLabel('LBL_City_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCities($csvFilePointer, $post, $langId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_SHIPPING_SETTING:
                $sheetName = Labels::getLabel('LBL_Shipping_Setting_Error', $langId);				
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importShippingSettings($csvFilePointer, $post, $langId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_PRODUCT_SHIPPING_RATE:
                $sheetName = Labels::getLabel('LBL_Product_Shipping_RATE_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importProductShippingRate($csvFilePointer, $post, $langId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $sheetName = Labels::getLabel('LBL_Policy_Points_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importPolicyPoints($csvFilePointer, $post, $langId);
                break;
            default:
                $default = true;
                break;
        }

        if ($default) {
            Message::addMessage(Labels::getLabel('MSG_Invalid_Access', $langId));
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function importMedia($type, $post, $langId, $userId = 0)
    {
        $csvFilePointer = $this->getCsvFilePointer($_FILES['import_file']['tmp_name']);
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands_Media_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importBrandsMedia($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Category_Media_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCategoryMedia($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_PRODUCTS:
                $sheetName = Labels::getLabel('LBL_Product_Catalog_Media_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importProductCatalogMedia($csvFilePointer, $post, $langId, $userId);
                break;
        }
    }

    public function exportCategories($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);

        if (!$userId) {
            $urlKeywords = $this->getAllRewriteUrls(ProductCategory::REWRITE_URL_PREFIX);
        }

        $useCategoryId = false;
        if ($this->settings['CONF_USE_CATEGORY_ID']) {
            $useCategoryId = true;
        } else {
            $categoriesIdentifiers = $this->getAllCategoryIdentifiers();
        }

        $srch = ProductCategory::getSearchObject(false, $langId, false);
        $srch->addMultipleFields(array('prodcat_id','prodcat_identifier','prodcat_parent','IFNULL(prodcat_name,prodcat_identifier) as prodcat_name','prodcat_description','prodcat_featured','prodcat_active','prodcat_deleted','prodcat_display_order'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('prodcat_id', 'asc');
        if ($userId) {
            $srch->addCondition('prodcat_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        }

        $rs = $srch->getResultSet();

        /* Sheet Heading Row [ */
        $headingsArr = $this->getCategoryColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array( 'prodcat_featured', 'prodcat_active', 'prodcat_deleted' )) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if ('urlrewrite_custom' == $columnKey) {
                    $colValue = isset($urlKeywords[ProductCategory::REWRITE_URL_PREFIX.$row['prodcat_id']]) ? $urlKeywords[ProductCategory::REWRITE_URL_PREFIX.$row['prodcat_id']] : '';
                }

                if ('prodcat_parent_identifier' == $columnKey) {
                    $colValue = array_key_exists($row['prodcat_parent'], $categoriesIdentifiers) ? $categoriesIdentifiers[$row['prodcat_parent']] : '';
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function exportCategoryMedia($langId)
    {
        $srch = ProductCategory::getSearchObject(false, false, false);
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'prodcat_id = afile_record_id and ( afile_type = '.AttachedFile::FILETYPE_CATEGORY_ICON.' or afile_type = '.AttachedFile::FILETYPE_CATEGORY_BANNER.')');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('prodcat_id','prodcat_identifier','afile_record_id','afile_record_subid','afile_type','afile_lang_id','afile_screen','afile_physical_path','afile_name','afile_display_order'));
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getCategoryMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);
        $fileTypeArr = AttachedFile::getFileTypeArray($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('afile_lang_code' == $columnKey) {
                    $colValue = $languageCodes[$row['afile_lang_id']];
                }

                if ('afile_type' == $columnKey) {
                    $colValue =  array_key_exists($row['afile_type'], $fileTypeArr) ? $fileTypeArr[ $row['afile_type'] ] : '';
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importCategories($csvFilePointer, $post, $langId, $userId = null)
    {
        $coloumArr = $this->getCategoryColoumArr($langId, $userId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);
        $rowIndex = 1;
        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodCatDataArr = $prodCatlangDataArr = array();
            $errorInRow = $seoUrl = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = ProductCategory::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex,($colIndex + 1),$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array( 'prodcat_featured', 'prodcat_active', 'prodcat_deleted', 'prodcat_display_order' ))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                        }
                    }

                    if ('prodcat_parent_identifier' == $columnKey) {
                        $columnKey = 'prodcat_parent';
                    }

                    if (in_array($columnKey, array( 'prodcat_name', 'prodcat_description' ))) {
                        $prodCatlangDataArr[$columnKey] = $colValue;
                    } elseif ('urlrewrite_custom' == $columnKey) {
                        $seoUrl = $colValue;
                    } else {
                        $prodCatDataArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($prodCatDataArr)) {
                if ($this->isDefaultSheetData($langId)) {
                    if ($this->settings['CONF_USE_CATEGORY_ID']) {
                        $parentId = $prodCatDataArr['prodcat_parent'];
                    } else {
                        $identifier = $prodCatDataArr['prodcat_parent'];

                        $categoriesIdentifiers = $this->getAllCategoryIdentifiers(false);
                        $parentId = isset($categoriesIdentifiers[$identifier]) ? $categoriesIdentifiers[$identifier] : 0;
                    }
                    if ($parentId) {
                        $parentCategoryData = ProductCategory::getAttributesById($parentId, 'prodcat_id');
                        if (empty($parentCategoryData) || $parentCategoryData == false) {
                            $parentId = 0;
                        }
                        $prodCatDataArr['prodcat_parent'] = $parentId;
                    }
                }

                if ($this->settings['CONF_USE_CATEGORY_ID']) {
                    $categoryId = $prodCatDataArr['prodcat_id'];
                    $categoryData = ProductCategory::getAttributesById($categoryId, array('prodcat_id'));
                } else {
                    $identifier = $prodCatDataArr['prodcat_identifier'];
                    $categoryData = ProductCategory::getAttributesByIdentifier($identifier, array('prodcat_id'));
                    $categoryId = $categoryData['prodcat_id'];
                }

                if (!$this->isDefaultSheetData($langId)) {
                    unset($prodCatDataArr['prodcat_parent']);
                    unset($prodCatDataArr['prodcat_identifier']);
                    unset($prodCatDataArr['prodcat_display_order']);
                }

                if (!empty($categoryData) && $categoryData['prodcat_id']) {
                    $where = array('smt' => 'prodcat_id = ?', 'vals' => array( $categoryId ) );
                    $this->db->updateFromArray(ProductCategory::DB_TBL, $prodCatDataArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(ProductCategory::DB_TBL, $prodCatDataArr);
                        $categoryId = $this->db->getInsertId();
                    }
                }

                if ($categoryId) {
                    /* Lang Data [*/
                    $langData = array(
                    'prodcatlang_prodcat_id'=> $categoryId,
                    'prodcatlang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $prodCatlangDataArr);

                    $this->db->insertFromArray(ProductCategory::DB_LANG_TBL, $langData, false, array(), $langData);

                    /* ]*/

                    /* Update cat code[*/
                    $category = new ProductCategory($categoryId);
                    $category->updateCatCode();
                    /*]*/

                    /* Url rewriting [*/
                    if ($this->isDefaultSheetData($langId)) {
                        if (!$seoUrl) {
                            $seoUrl = $identifier;
                        }
                        $prodcatData = ProductCategory::getAttributesById($categoryId, array('prodcat_parent'));
                        $category->rewriteUrl($seoUrl, true, $prodcatData['prodcat_parent']);
                    }
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        $ProductCategory = new ProductCategory();
        $ProductCategory->updateCatCode();
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function importCategoryMedia($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        $fileTypeArr = AttachedFile::getFileTypeArray($langId);
        $fileTypeIdArr = array_flip($fileTypeArr);

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $useCategoryId = false;
        if ($this->settings['CONF_USE_CATEGORY_ID']) {
            $useCategoryId = true;
        } else {
            $categoriesIdentifiers = $this->getAllCategoryIdentifiers();
            $categoriesIds = array_flip($categoriesIdentifiers);
        }

        $coloumArr = $this->getCategoryMediaColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $categoryMediaArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = ProductCategory::validateMediaFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('afile_type' == $columnKey) {
                        $colValue = array_key_exists($colValue, $fileTypeIdArr) ? $fileTypeIdArr[$colValue] : 0;
                    }

                    if ('prodcat_id' == $columnKey) {
                        $columnKey = 'afile_record_id';
                    }
                    if ('prodcat_identifier' == $columnKey) {
                        $columnKey = 'afile_record_id';
                        $colValue = array_key_exists($colValue, $categoriesIds) ? $categoriesIds[$colValue] : 0;
                    }

                    if ('afile_lang_code' == $columnKey) {
                        $columnKey = 'afile_lang_id';
                        $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                    }

                    $categoryMediaArr[$columnKey] = $colValue;
                }
            }
            if (false === $errorInRow && count($categoryMediaArr)) {
                $categoryMediaArr['afile_record_subid'] = 0;

                $saveToTempTable = false;
                $isUrlArr = parse_url($categoryMediaArr['afile_physical_path']);

                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                if ($saveToTempTable) {
                    $categoryMediaArr['afile_downloaded'] = applicationConstants::NO;
                    $categoryMediaArr['afile_unique'] = applicationConstants::YES;
                    $this->db->deleteRecords(
                        AttachedFile::DB_TBL_TEMP,
                        array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?',
                        'vals' => array($categoryMediaArr['afile_type'], $categoryMediaArr['afile_record_id'], 0, $categoryMediaArr['afile_lang_id'])
                        )
                    );
                    $this->db->insertFromArray(AttachedFile::DB_TBL_TEMP, $categoryMediaArr, false, array(), $categoryMediaArr);
                } else {
                    $this->db->deleteRecords(
                        AttachedFile::DB_TBL,
                        array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?',
                        'vals' => array($categoryMediaArr['afile_type'], $categoryMediaArr['afile_record_id'], 0, $categoryMediaArr['afile_lang_id'])
                        )
                    );

                    $physical_path = explode('/', $categoryMediaArr['afile_physical_path']);
                    if (AttachedFile::FILETYPE_BULK_IMAGES_PATH ==  $physical_path[0].'/') {
                        $afileObj = new AttachedFile();

                        $moved = $afileObj->moveAttachment($categoryMediaArr['afile_physical_path'], $categoryMediaArr['afile_type'], $categoryMediaArr['afile_record_id'], 0, $categoryMediaArr['afile_name'], $categoryMediaArr['afile_display_order'], true, $categoryMediaArr['afile_lang_id']);

                        if (false === $moved) {
                            $errMsg = Labels::getLabel("MSG_Invalid_File.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, 'N/A', $errMsg ));
                        }
                    } else {
                        $this->db->insertFromArray(AttachedFile::DB_TBL, $categoryMediaArr, false, array(), $categoryMediaArr);
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        $success['status'] = 1;


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportBrands($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        if (!$userId) {
            /*Fetch all seo keyword [*/
            $urlKeywords = $this->getAllRewriteUrls(Brand::REWRITE_URL_PREFIX);
            /*]*/
        }

        $srch = Brand::getSearchObject($langId, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('brand_id','brand_identifier','iFNULL(brand_name,brand_identifier) as brand_name','brand_short_description','brand_featured','brand_active','brand_deleted'));
        $srch->addCondition('brand_status', '=', applicationConstants::ACTIVE);
        if ($userId) {
            $srch->addCondition('brand_active', '=', applicationConstants::ACTIVE);
            $srch->addOrder('brand_id');
        }
        $rs = $srch->getResultSet();

        $sheetData = array();

        /* Sheet Heading Row [ */
        $headingsArr = $this->getBrandColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */
        // $data = $this->db->fetchAll($rs);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                switch ($columnKey){
                    case 'brand_featured':
                    case 'brand_active':
                    case 'brand_deleted':
                        if (!$this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                        }
                        break;
                    case 'urlrewrite_custom':
                        $colValue = isset($urlKeywords[Brand::REWRITE_URL_PREFIX.$row['brand_id']]) ? $urlKeywords[Brand::REWRITE_URL_PREFIX.$row['brand_id']] : '';
                        break;
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importBrands($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;

        $coloumArr = $this->getBrandColoumArr($langId, $userId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $brandDataArr = $brandlangDataArr = array();
            $errorInRow = $seoUrl = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = Brand::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex,($colIndex + 1),$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array('brand_featured', 'brand_active','brand_deleted'))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1)?applicationConstants::YES:applicationConstants::NO;
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES')?applicationConstants::YES:applicationConstants::NO;
                        }
                    }

                    if (in_array($columnKey, array( 'brand_name', 'brand_short_description' ))) {
                        $brandlangDataArr[$columnKey] = $colValue;
                    } elseif ('urlrewrite_custom' == $columnKey) {
                        $seoUrl = $colValue;
                    } else {
                        $brandDataArr['brand_status'] = applicationConstants::ACTIVE;
                        $brandDataArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($brandDataArr)) {
                if ($this->settings['CONF_USE_BRAND_ID']) {
                    $brandId = $brandDataArr['brand_id'];
                    $brandData = Brand::getAttributesById($brandId, array('brand_id'));
                } else {
                    $identifier = $brandDataArr['brand_identifier'];
                    $brandData = Brand::getAttributesByIdentifier($identifier, array('brand_id'));
                    $brandId = $brandData['brand_id'];
                }

                if (!empty($brandData) && $brandData['brand_id']) {
                    $where = array('smt' => 'brand_id = ?', 'vals' => array( $brandId ) );
                    $this->db->updateFromArray(Brand::DB_TBL, $brandDataArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(Brand::DB_TBL, $brandDataArr);
                        $brandId = $this->db->getInsertId();
                    }
                }

                if ($brandId) {
                    /* Lang Data [*/
                    $langData = array(
                    'brandlang_brand_id'=> $brandId,
                    'brandlang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $brandlangDataArr);

                    $this->db->insertFromArray(Brand::DB_LANG_TBL, $langData, false, array(), $langData);
                    /* ]*/

                    /* Url rewriting [*/
                    if ($this->isDefaultSheetData($langId)) {
                        if (!$seoUrl) {
                            $seoUrl = $brandDataArr['brand_identifier'];
                        }
                        $brand = new Brand($brandId);
                        $brand->rewriteUrl($seoUrl);
                    }
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }

        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);



        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportBrandMedia($langId)
    {
        $srch = Brand::getSearchObject();
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'brand_id = afile_record_id');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('brand_id','brand_identifier','afile_record_id','afile_record_subid','afile_lang_id','afile_screen','afile_physical_path','afile_name','afile_display_order','afile_type'));
        $srch->addCondition('brand_status', '=', applicationConstants::ACTIVE);
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getBrandMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                switch ($columnKey) {
                    case 'afile_lang_code':
                        $colValue = $languageCodes[$row['afile_lang_id']];
                        break;

                    case 'afile_lang_code':
                        $colValue = $languageCodes[$row['afile_lang_id']];
                        break;

                    case 'afile_type':
                        $colValue = 'logo';
                        if ($row['afile_type'] == AttachedFile::FILETYPE_BRAND_IMAGE){
                            $colValue = 'image';
                        }
                        break;
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importBrandsMedia($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $brandIdentifiers =  Brand::getAllIdentifierAssoc();
        $brandIds = array_flip($brandIdentifiers);

        $coloumArr = $this->getBrandMediaColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $brandsMediaArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = Brand::validateMediaFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'brand_id':
                            $columnKey = 'afile_record_id';
                            break;

                        case 'brand_identifier':
                            $columnKey = 'afile_record_id';
                            $colValue = $brandIds[$colValue];
                            break;
                        case 'afile_lang_code':
                            $columnKey = 'afile_lang_id';
                            $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                            break;

                        case 'afile_type':
                            $fileType  = AttachedFile::FILETYPE_BRAND_LOGO;
                            if ('image' == mb_strtolower($colValue)) {
                                $fileType  = AttachedFile::FILETYPE_BRAND_IMAGE;
                            }
                            $colValue = $fileType;
                            break;
                    }

                    $brandsMediaArr[$columnKey] = $colValue;
                }
            }

            if (false === $errorInRow && count($brandsMediaArr)) {
                $dataToSaveArr = array(
                'afile_record_subid'=> 0,
                );
                $dataToSaveArr = array_merge($dataToSaveArr, $brandsMediaArr);

                $saveToTempTable = false;
                $isUrlArr = parse_url($brandsMediaArr['afile_physical_path']);

                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                if ($saveToTempTable) {
                    $dataToSaveArr['afile_downloaded'] = applicationConstants::NO;
                    $dataToSaveArr['afile_unique'] = applicationConstants::YES;
                    $this->db->deleteRecords(
                        AttachedFile::DB_TBL_TEMP,
                        array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?',
                        'vals' => array( $fileType, $dataToSaveArr['afile_record_id'], 0, $dataToSaveArr['afile_lang_id'] )
                        )
                    );
                    $this->db->insertFromArray(AttachedFile::DB_TBL_TEMP, $dataToSaveArr, false, array(), $dataToSaveArr);
                } else {
                    $this->db->deleteRecords(
                        AttachedFile::DB_TBL,
                        array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?',
                        'vals' => array( $fileType, $dataToSaveArr['afile_record_id'], 0, $dataToSaveArr['afile_lang_id'] )
                        )
                    );

                    $physical_path = explode('/', $brandsMediaArr['afile_physical_path']);
                    if (AttachedFile::FILETYPE_BULK_IMAGES_PATH ==  $physical_path[0].'/') {
                        $afileObj = new AttachedFile();

                        $moved = $afileObj->moveAttachment($brandsMediaArr['afile_physical_path'], $fileType, $dataToSaveArr['afile_record_id'], 0, $brandsMediaArr['afile_name'], $brandsMediaArr['afile_display_order'], true, $brandsMediaArr['afile_lang_id']);

                        if (false === $moved) {
                            $errMsg = Labels::getLabel("MSG_Invalid_File.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, 'N/A', $errMsg ));
                        }
                    } else {
                        $this->db->insertFromArray(AttachedFile::DB_TBL, $dataToSaveArr, false, array(), $dataToSaveArr);
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductsCatalog($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $useProductId = false;

        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $useProductId = true;
        }

        if (!$this->settings['CONF_USE_PRODUCT_TYPE_ID']) {
            $ProdTypeIdentifierById = Product::getProductTypes($langId);
        }

        if (!$this->settings['CONF_USE_TAX_CATEOGRY_ID']) {
            $taxCategoryIdentifierById = $this->getTaxCategoriesArr();
        }

        if (!$this->settings['CONF_USE_DIMENSION_UNIT_ID']) {
            $lengthUnitsArr = applicationConstants::getLengthUnitsArr($langId);
        }

        if (!$this->settings['CONF_USE_WEIGHT_UNIT_ID']) {
            $weightUnitsArr = applicationConstants::getWeightUnitsArr($langId);
        }

        $srch = Product::getSearchObject($langId, false);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = tp.product_seller_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = tp.product_seller_id', 'uc');
        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'b.brand_id = tp.product_brand_id', 'b');
        if ($userId) {
            $srch->joinTable(Product::DB_TBL_PRODUCT_SHIPPING, 'LEFT OUTER JOIN', 'ps.ps_product_id = tp.product_id and ps.ps_user_id = '.$userId, 'ps');
        } else {
            $srch->joinTable(Product::DB_TBL_PRODUCT_SHIPPING, 'LEFT OUTER JOIN', 'ps.ps_product_id = tp.product_id and ps.ps_user_id = tp.product_seller_id', 'ps');
        }
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'c.country_id = ps.ps_from_country_id', 'c');
        //$srch->joinTable(Countries::DB_TBL,'LEFT OUTER JOIN','c.country_id = tp.product_ship_country','c');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('tp.*','tp_l.*','ps.ps_from_country_id','ps.ps_free','user_id','credential_username','brand_id','brand_identifier','country_id','country_code'));
        if ($userId) {
            $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
            $cnd->attachCondition('tp.product_seller_id', '=', 0);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductsCatalogColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $taxData = $this->getTaxCategoryByProductId($row['product_id']);

            if (!empty($taxData)) {
                $row = array_merge($row, $taxData);
            }
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array( 'brand_featured', 'brand_active' )) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if (in_array($columnKey, array( 'category_Id', 'category_indentifier' ))) {
                    if ('category_Id' == $columnKey) {
                        $productCategories = $this->getProductCategoriesByProductId($row['product_id'], false);
                    } else {
                        $productCategories = $this->getProductCategoriesByProductId($row['product_id']);
                    }

                    $colValue =  ($productCategories) ? implode(',', $productCategories) : '';
                }

                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($row[$columnKey]) ? $row['credential_username'] :  Labels::getLabel('LBL_Admin', $langId));
                }

                if ('product_type_identifier' == $columnKey) {
                    $colValue = (!empty($row['product_type']) && array_key_exists($row['product_type'], $ProdTypeIdentifierById) ? $ProdTypeIdentifierById[$row['product_type']] : 0);
                }

                if ('tax_category_id' == $columnKey) {
                    $colValue = (array_key_exists('ptt_taxcat_id', $row) ? $row['ptt_taxcat_id'] : 0);
                }
                if ('tax_category_identifier' == $columnKey) {
                    $colValue = (!empty($row['ptt_taxcat_id']) && array_key_exists($row['ptt_taxcat_id'], $taxCategoryIdentifierById) ? $taxCategoryIdentifierById[ $row['ptt_taxcat_id'] ] : 0);
                }

                if ('product_dimension_unit_identifier' == $columnKey) {
                    $colValue = (!empty($row['product_dimension_unit']) && array_key_exists($row['product_dimension_unit'], $lengthUnitsArr) ? $lengthUnitsArr[$row['product_dimension_unit']] : '');
                }

                if ('product_weight_unit_identifier' == $columnKey) {
                    $colValue = (!empty($row['product_weight_unit']) && array_key_exists($row['product_weight_unit'], $weightUnitsArr) ? $weightUnitsArr[$row['product_weight_unit']] : '');
                }

                if (in_array($columnKey, array( 'ps_free', 'product_cod_enabled', 'product_featured', 'product_approved', 'product_active', 'product_deleted' )) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductsCatalog($csvFilePointer, $post, $langId, $sellerId = null)
    {
        $sellerId = FatUtility::int($sellerId);

        $rowIndex = 1;
        $usernameArr = array();
        $categoryIdentifierArr = array();
        $brandIdentifierArr = array();
        $taxCategoryArr = array();
        $countryArr = array();
        $userProdUploadLimit = $usersCrossedUploadLimit = array();

        if (!$this->settings['CONF_USE_PRODUCT_TYPE_ID']) {
            $prodTypeIdentifierArr = Product::getProductTypes($langId);
            $prodTypeIdentifierArr = array_flip($prodTypeIdentifierArr);
        }

        if (!$this->settings['CONF_USE_DIMENSION_UNIT_ID']) {
            $lengthUnitsArr = applicationConstants::getLengthUnitsArr($langId);
            $lengthUnitsArr = array_flip($lengthUnitsArr);
        }

        if (!$this->settings['CONF_USE_WEIGHT_UNIT_ID']) {
            $weightUnitsArr = applicationConstants::getWeightUnitsArr($langId);
            $weightUnitsArr = array_flip($weightUnitsArr);
        }

        $coloumArr = $this->getProductsCatalogColoumArr($langId, $sellerId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        $prodType = PRODUCT::PRODUCT_TYPE_PHYSICAL;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $prodDataArr = $prodlangDataArr = $categoryIds = $prodShippingArr = array();
            $errorInRow = $taxCatId = false;

            $breakForeach = false;
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = $errMsg = false;

                if ($this->isDefaultSheetData($langId) && in_array($columnKey, array('product_seller_id', 'credential_username', 'product_id', 'product_identifier'))) {
                    if ($this->settings['CONF_USE_USER_ID']) {
                        $colTitle = ('product_seller_id' != $columnKey) ? $coloumArr['product_seller_id'] :$columnTitle;
                        $colInd = $this->headingIndexArr[$colTitle];
                        $userId = $this->getCell($row, $colInd, '');
                    } else {
                        $colTitle = ('credential_username' != $columnKey)? $coloumArr['credential_username']:$columnTitle;

                        if ('credential_username' == $columnKey) {
                            $columnKey = 'product_seller_id';
                        }

                        $colInd = $this->headingIndexArr[$colTitle];
                        $userName = $this->getCell($row, $colInd, '');

                        if (0 < $sellerId && empty($userName)) {
                            $userObj = new User($sellerId);
                            $userInfo = $userObj->getUserInfo(array('credential_username'));
                            $userName = $userInfo['credential_username'];
                        } else {
                            $userName = ($userName == Labels::getLabel('LBL_Admin', $langId) ? '' : $userName);
                        }

                        if (!empty($userName) && !array_key_exists($userName, $usernameArr)) {
                            $res = $this->getAllUserArr(false, $userName);
                            if (!$res) {
                                $colIndex = $colInd;
                                $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                            } else {
                                $usernameArr = array_merge($usernameArr, $res);
                            }
                        }
                        $userId = array_key_exists($userName, $usernameArr) ? FatUtility::int($usernameArr[$userName]) : 0;
                        if ('credential_username' == $columnKey) {
                            $colValue = $userId;
                        }
                    }

                    if (0 < $sellerId && ($sellerId != $userId || 1 > $userId)) {
                        $colIndex = $colInd;
                        $errMsg = Labels::getLabel("MSG_Sorry_you_are_not_authorized_to_update_this_product.", $langId);
                        $breakForeach = true;
                    }

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && in_array($columnKey, array('credential_username','product_seller_id')) && 0 < $userId) {
                        if (!array_key_exists($userId, $userProdUploadLimit)) {
                            $userProdUploadLimit[$userId] = SellerPackages::getAllowedLimit($userId, $langId, 'spackage_products_allowed');
                        }
                    }
                }

                if (false === $errMsg) {
                    $errMsg = Product::validateFields($columnKey, $columnTitle, $colValue, $langId, $prodType);
                }

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex,($colIndex + 1),$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    if ($breakForeach) {
                        break;
                    }
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                            if ($this->settings['CONF_USE_PRODUCT_ID']) {
                                if (0 < $sellerId) {
                                    $userTempIdData = $this->getProductIdByTempId($colValue, $userId);
                                    if (!empty($userTempIdData) && $userTempIdData['pti_product_temp_id'] == $colValue) {
                                        $colValue = $userTempIdData['pti_product_id'];
                                    }
                                }

                                $prodDataArr['product_id'] = $colValue;

                                $prodData = Product::getAttributesById($colValue, array( 'product_id', 'product_seller_id', 'product_featured', 'product_approved' ));
                            }
                            break;
                        case 'product_identifier':
                            $prodData = Product::getAttributesByIdentifier($colValue, array( 'product_id', 'product_seller_id', 'product_featured', 'product_approved' ));
                            if ($sellerId && !empty($prodData) && $prodData['product_seller_id'] != $sellerId) {
                                $invalid = true;
                            }
                            break;
                        case 'product_seller_id':
                            $colValue = 0;
                            if ($userId > 0) {
                                $colValue = $userId;
                            }
                            break;
                        case 'product_cod_enabled':
                        case 'product_featured':
                        case 'product_approved':
                        case 'product_active':
                        case 'product_deleted':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'product_added_on':
                            if ($sellerId) {
                                $colValue = date('Y-m-d H:i:s');
                            }
                            break;
                        case 'category_Id':
                            $categoryIds = $colValue;
                            break;
                        case 'category_indentifier':
                            $catArr = array();
                            $catIdentifiers = explode(',', $colValue);
                            if (!empty($catIdentifiers)) {
                                foreach ($catIdentifiers as $val) {
                                    if (!array_key_exists($val, $categoryIdentifierArr)) {
                                        $res = $this->getAllCategoryIdentifiers(false, $val);
                                        if (!$res) {
                                            continue;
                                        } else {
                                            $categoryIdentifierArr = array_merge($categoryIdentifierArr, $res);
                                        }
                                    }
                                    if (isset($categoryIdentifierArr[$val])) {
                                        $catArr[] = $categoryIdentifierArr[$val];
                                    }
                                }
                            }
                            $categoryIds = implode(',', $catArr);
                            break;
                        case 'brand_identifier':
                            $columnKey = 'product_brand_id';
                            if (!array_key_exists($colValue, $brandIdentifierArr)) {
                                $res = $this->getAllBrandsArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $brandIdentifierArr = array_merge($brandIdentifierArr, $res);
                                }
                            }
                            $colValue = isset($brandIdentifierArr[$colValue]) ? $brandIdentifierArr[$colValue] : 0;
                            break;
                        case 'product_type_identifier':
                            $columnKey = 'product_type';
                            if (!array_key_exists($colValue, $prodTypeIdentifierArr)) {
                                $invalid = true;
                            } else {
                                $colValue = $prodTypeIdentifierArr[$colValue];
                            }
                            $prodType =  $colValue;
                            break;
                        case 'tax_category_id':
                            $taxCatId = $colValue;
                            break;
                        case 'tax_category_identifier':
                            if (!array_key_exists($colValue, $taxCategoryArr)) {
                                $res = $this->getTaxCategoriesArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $taxCategoryArr = array_merge($taxCategoryArr, $res);
                                }
                            }
                            $taxCatId = isset($taxCategoryArr[$colValue]) ? $taxCategoryArr[$colValue] : 0;
                            break;
                        case 'product_dimension_unit_identifier':
                            $columnKey = 'product_dimension_unit';
                            if (FatApp::getConfig('CONF_PRODUCT_DIMENSIONS_ENABLE', FatUtility::VAR_INT, 0) && $prodType == PRODUCT::PRODUCT_TYPE_PHYSICAL) {
                                if (!array_key_exists($colValue, $lengthUnitsArr)) {
                                    $invalid = true;
                                } else {
                                    $colValue = $lengthUnitsArr[$colValue];
                                }
                            } else {
                                $colValue = '';
                            }

                            break;
                        case 'product_weight_unit_identifier':
                            $columnKey = 'product_weight_unit';
                            if (FatApp::getConfig('CONF_PRODUCT_DIMENSIONS_ENABLE', FatUtility::VAR_INT, 0) && $prodType == PRODUCT::PRODUCT_TYPE_PHYSICAL) {
                                if (!array_key_exists($colValue, $weightUnitsArr)) {
                                    $invalid = true;
                                } else {
                                    $colValue = $weightUnitsArr[$colValue];
                                }
                            } else {
                                $colValue = '';
                            }
                            break;
                        case 'country_code':
                            $columnKey = 'ps_from_country_id';
                            if (!array_key_exists($colValue, $countryArr)) {
                                $res = $this->getCountriesArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $countryArr = array_merge($countryArr, $res);
                                }
                            }
                            $colValue = isset($countryArr[$colValue]) ? $countryArr[$colValue] : 0;
                            break;
                    }


                    if (true == $invalid) {
                        $errorInRow = true;
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        if (in_array($columnKey, array( 'product_name', 'product_description', 'product_youtube_video' ))) {
                            $prodlangDataArr[$columnKey] = $colValue;
                        } elseif (in_array($columnKey, array( 'ps_from_country_id', 'ps_free' ))) {
                            $prodShippingArr[$columnKey] = $colValue;
                        } else {
                            if (in_array($columnKey, array( 'tax_category_id', 'tax_category_identifier' ))) {
                                continue;
                            }
                            if (in_array($columnKey, array( 'category_Id', 'category_indentifier' ))) {
                                continue;
                            }

                            $prodDataArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($prodDataArr)) {
                $prodDataArr['product_added_on'] = date('Y-m-d H:i:s');
                ;
                $prodDataArr['product_added_by_admin_id'] = (1 > $userId) ? applicationConstants::YES : applicationConstants::NO;

                if (!empty($prodData) && $prodData['product_id'] && (!$sellerId || ($sellerId && $prodData['product_seller_id'] == $sellerId))) {
                    unset($prodData['product_seller_id']);
                    $productId = $prodData['product_id'];

                    if ($sellerId) {
                        $prodDataArr['product_featured'] = $prodData['product_featured'] ;
                        $prodDataArr['product_approved'] = $prodData['product_approved'] ;
                        unset($prodDataArr['product_added_on']);
                    }

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && Product::getActiveCount($userId, $productId) >= $userProdUploadLimit[$userId]) {
                        $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                        continue;
                    }

                    $where = array('smt' => 'product_id = ?', 'vals' => array( $productId ) );
                    $this->db->updateFromArray(Product::DB_TBL, $prodDataArr, $where);

                    if ($sellerId && $this->isDefaultSheetData($langId)) {
                        $tempData = array(
                        'pti_product_id' =>$productId,
                        'pti_product_temp_id' =>$productId,
                        'pti_user_id' =>$userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_PRODUCT_IDS, array('smt'=> 'pti_product_id = ? and pti_user_id = ?','vals' => array($productId,$userId) ));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_PRODUCT_IDS, $tempData, false, array(), $tempData);
                    }
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        if ($sellerId) {
                            unset($prodDataArr['product_id']);
                            unset($prodDataArr['product_featured']);
                            if (FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1)) {
                                $prodDataArr['product_approved'] = applicationConstants::NO;
                            }
                        }

                        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && Product::getActiveCount($userId) >= $userProdUploadLimit[$userId]) {
                            $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                            continue;
                        }

                        if (!$this->db->insertFromArray(Product::DB_TBL, $prodDataArr, false, array(), $prodDataArr)) {
                            FatUtility::dieJsonError($this->db->getError());
                        }
                        // echo $this->db->getError();
                        $productId = $this->db->getInsertId();

                        if ($sellerId) {
                            $tempData = array(
                            'pti_product_id' =>$productId,
                            'pti_product_temp_id' =>$productId,
                            'pti_user_id' =>$userId,
                            );
                            $this->db->deleteRecords(Importexport::DB_TBL_TEMP_PRODUCT_IDS, array('smt'=> 'pti_product_id = ? and pti_user_id = ?','vals' => array($productId,$userId) ));
                            $this->db->insertFromArray(Importexport::DB_TBL_TEMP_PRODUCT_IDS, $tempData, false, array(), $tempData);
                        }
                    }
                }

                if (!empty($productId)) {
                    if ($this->isDefaultSheetData($langId)) {
                        $productSellerShiping = array(
                        'ps_product_id'=>$productId,
                        'ps_user_id'=>$userId,
                        );
                        $productSellerShiping = array_merge($productSellerShiping, $prodShippingArr);

                        FatApp::getDb()->insertFromArray(PRODUCT::DB_TBL_PRODUCT_SHIPPING, $productSellerShiping, false, array(), $productSellerShiping);
                    }

                    /* Lang Data [*/
                    $langData = array(
                    'productlang_product_id'=> $productId,
                    'productlang_lang_id'=> $langId,
                    );

                    $langData = array_merge($langData, $prodlangDataArr);

                    $this->db->insertFromArray(Product::DB_LANG_TBL, $langData, false, array(), $langData);
                    /* ]*/

                    if ($this->isDefaultSheetData($langId)) {
                        /* Product Categories [*/
                        $this->db->deleteRecords(Product::DB_TBL_PRODUCT_TO_CATEGORY, array('smt'=> Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX.'product_id = ?','vals' => array($productId) ));

                        $categoryIdsArr = explode(',', $categoryIds);
                        if (!empty($categoryIdsArr)) {
                            foreach ($categoryIdsArr as $catId) {
                                $catData = array(
                                'ptc_product_id'=>$productId,
                                'ptc_prodcat_id'=>$catId
                                );
                                $this->db->insertFromArray(Product::DB_TBL_PRODUCT_TO_CATEGORY, $catData);
                            }
                        }
                        /*]*/

                        /* Tax Category [*/
                        $this->db->deleteRecords(Tax::DB_TBL_PRODUCT_TO_TAX, array('smt'=> 'ptt_product_id = ? and ptt_seller_user_id = ?','vals' => array( $productId, $userId ) ));
                        if ($taxCatId) {
                            $this->db->insertFromArray(Tax::DB_TBL_PRODUCT_TO_TAX, array('ptt_product_id'=>$productId,'ptt_taxcat_id'=>$taxCatId,'ptt_seller_user_id'=>$userId));
                        }
                        /*]*/
                    }
                }
            } else {
                $errInSheet = true;
            }
        }

        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }

        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }

        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductOptions($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'INNER JOIN', Product::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_TO_OPTION_PREFIX.'product_id');
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', Option::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_TO_OPTION_PREFIX.'option_id');
        $srch->addMultipleFields(array('option_id','option_identifier','product_id','product_identifier'));
        $srch->doNotCalculateRecords();
        if ($userId) {
            $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
            $cnd->attachCondition('tp.product_seller_id', '=', 0);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addOrder('product_id');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductOptionColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductOptions($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $optionIdentifierArr = array();
        $prodArr = array();

        $coloumArr = $this->getProductOptionColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $optionsArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Option::validateProdOptionFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex,($colIndex + 1),$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array( 'product_identifier', 'option_identifier' ))) {
                        if ('product_identifier' == $columnKey) {
                            if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                $res = $this->getAllProductsIdentifiers(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $prodIndetifierArr = array_merge($prodIndetifierArr, $res);
                                }
                            }
                            $colValue =   array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                        } else {
                            if (!array_key_exists($colValue, $optionIdentifierArr)) {
                                $res = $this->getAllOptions(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $optionIdentifierArr = array_merge($optionIdentifierArr, $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                        }
                    }

                    if (in_array($columnKey, array( 'product_id', 'product_identifier' ))) {
                        $columnKey = 'prodoption_product_id';

                        if ($userId) {
                            $colValue = $this->getCheckAndSetProductIdByTempId($colValue, $userId);
                        }

                        $productId = $colValue;
                    }

                    if (in_array($columnKey, array( 'option_id', 'option_identifier' ))) {
                        $columnKey = 'prodoption_option_id';
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $optionsArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($optionsArr)) {
                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;
                    $this->db->deleteRecords(Product::DB_PRODUCT_TO_OPTION, array('smt'=> 'prodoption_product_id = ? ','vals' => array( $productId ) ));
                }

                $this->db->insertFromArray(Product::DB_PRODUCT_TO_OPTION, $optionsArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }

        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductTags($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_TO_TAG, 'INNER JOIN', Product::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_TO_TAG_PREFIX.'product_id');
        $srch->joinTable(Tag::DB_TBL, 'INNER JOIN', Tag::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_TO_TAG_PREFIX.'tag_id');
        $srch->addMultipleFields(array('tag_id','tag_identifier','product_id','product_identifier'));
        $srch->doNotCalculateRecords();
        if ($userId) {
            $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
            $cnd->attachCondition('tp.product_seller_id', '=', 0);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addOrder('product_id');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductTagColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductTags($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);

        $rowIndex = 1;
        $prodIndetifierArr = array();
        $tagIndetifierArr = array();
        $prodArr = array();

        $coloumArr = $this->getProductTagColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $tagsArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Tag::validateProdTagsFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex,($colIndex + 1),$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array( 'product_identifier', 'tag_identifier' ))) {
                        if ('product_identifier' == $columnKey) {
                            if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                $res = $this->getAllProductsIdentifiers(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $prodIndetifierArr = array_merge($prodIndetifierArr, $res);
                                }
                            }
                            $colValue =   array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                        } else {
                            if (!array_key_exists($colValue, $tagIndetifierArr)) {
                                $res = $this->getAllTags(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $tagIndetifierArr = array_merge($tagIndetifierArr, $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $tagIndetifierArr) ? $tagIndetifierArr[$colValue] : 0;
                        }
                    }

                    if (in_array($columnKey, array( 'product_id', 'product_identifier' ))) {
                        $columnKey = 'ptt_product_id';

                        if ($userId) {
                            $colValue = $this->getCheckAndSetProductIdByTempId($colValue, $userId);
                        }

                        $productId = $colValue;
                    }

                    if (in_array($columnKey, array( 'tag_id', 'tag_identifier' ))) {
                        $columnKey = 'ptt_tag_id';
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $tagsArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($tagsArr)) {
                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;
                    $this->db->deleteRecords(Product::DB_PRODUCT_TO_TAG, array('smt'=> 'ptt_product_id = ? ','vals' => array($productId) ));
                }

                $this->db->insertFromArray(Product::DB_PRODUCT_TO_TAG, $tagsArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductSpecification($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'INNER JOIN', Product::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_SPECIFICATION_PREFIX.'product_id');
        $srch->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'LEFT OUTER JOIN', Product::DB_PRODUCT_SPECIFICATION_PREFIX.'id = '.Product::DB_PRODUCT_LANG_SPECIFICATION_PREFIX.'prodspec_id');
        $srch->addMultipleFields(array('prodspec_id','prodspeclang_lang_id','prodspec_name','prodspec_value','product_id','product_identifier'));
        $srch->joinTable(Language::DB_TBL, 'INNER JOIN', 'language_id = prodspeclang_lang_id');
        $srch->doNotCalculateRecords();
        if ($userId) {
            $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
            $cnd->attachCondition('tp.product_seller_id', '=', 0);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addCondition('language_active', '=', applicationConstants::ACTIVE);

        $srch->addOrder('product_id');
        $srch->addOrder('prodspec_id');

        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductSpecificationColoumArr($langId);

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */
        $languageCodes = Language::getAllCodesAssoc();

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if ('prodspeclang_lang_code' == $columnKey) {
                    $colValue = $languageCodes[ $row['prodspeclang_lang_id'] ];
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductSpecifications($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $prodArr = array();
        $langArr = array();
        $languageCodes = Language::getAllCodesAssoc();
        $languageCodes = array_flip($languageCodes);

        $prodspec_id = 0;

        $coloumArr = $this->getProductSpecificationColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodSpecArr = $prodSpecLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = ProdSpecification::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex,($colIndex + 1),$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                        case 'product_identifier':
                            if ('product_identifier' == $columnKey) {
                                if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                    $res = $this->getAllProductsIdentifiers(false, $colValue);

                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $prodIndetifierArr = array_merge($prodIndetifierArr, $res);
                                    }
                                }
                                $productId = $colValue = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            } else {
                                $productId = $colValue;
                            }

                            if ($userId) {
                                $productId = $colValue = $this->getCheckAndSetProductIdByTempId($colValue, $userId);
                            }
                            break;
                        case 'prodspeclang_lang_id':
                            $languageId = $colValue;
                            break;
                        case 'prodspeclang_lang_code':
                            $columnKey = 'prodspeclang_lang_id';
                            $colValue =  array_key_exists($colValue, $languageCodes) ? $languageCodes[$colValue] : 0;
                            if (0 >= $colValue) {
                                $invalid = true;
                            }
                            $languageId = $colValue;
                            break;
                    }


                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        if (in_array($columnKey, array( 'prodspeclang_lang_id', 'prodspec_name', 'prodspec_value' ))) {
                            $prodSpecLangArr[$columnKey] = $colValue;
                        } else {
                            $prodSpecArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($prodSpecArr)) {
                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;

                    $srch = new SearchBase(Product::DB_PRODUCT_SPECIFICATION);
                    $srch->addCondition(Product::DB_PRODUCT_SPECIFICATION_PREFIX . 'product_id', '=', $productId);
                    $rs = $srch->getResultSet();
                    $res = FatApp::getDb()->fetchAll($rs);
                    foreach ($res as $val) {
                        $this->db->deleteRecords(Product::DB_PRODUCT_LANG_SPECIFICATION, array('smt'=> 'prodspeclang_prodspec_id = ? ','vals' => array($val['prodspec_id']) ));
                    }
                    $this->db->deleteRecords(Product::DB_PRODUCT_SPECIFICATION, array('smt'=> 'prodspec_product_id = ? ','vals' => array( $productId ) ));
                }

                if (!in_array($languageId, $langArr)) {
                    $langArr[] = $languageId;
                    if (!$prodspec_id) {
                        $this->db->insertFromArray(Product::DB_PRODUCT_SPECIFICATION, array('prodspec_product_id' => $productId));
                        $prodspec_id = $this->db->getInsertId();
                    }
                } else {
                    // continue lang loop
                    $langArr = array();
                    $langArr[] = $languageId;
                    $this->db->insertFromArray(Product::DB_PRODUCT_SPECIFICATION, array('prodspec_product_id' => $productId));
                    $prodspec_id = $this->db->getInsertId();
                }

                $langData = array(
                'prodspeclang_prodspec_id'=>$prodspec_id
                );
                $langData = array_merge($langData, $prodSpecLangArr);

                $this->db->insertFromArray(Product::DB_PRODUCT_LANG_SPECIFICATION, $langData);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductShipping($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_TO_SHIP, 'INNER JOIN', Product::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_TO_SHIP_PREFIX.'prod_id', 'tpsr');
        $srch->joinTable(ShippingCompanies::DB_TBL, 'LEFT OUTER JOIN', ShippingCompanies::DB_TBL_PREFIX.'id = tpsr.pship_company', 'tsc');
        $srch->joinTable(ShippingDurations::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_duration=tsd.sduration_id', 'tsd');
        $srch->joinTable(ShippingMethods::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_method = tsm.shippingapi_id', 'tsm');
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_country = c.country_id', 'c');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_user_id = u.user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tpsr.pship_user_id = uc.credential_user_id', 'uc');
        $srch->addMultipleFields(array('product_id','product_identifier','scompany_id','scompany_identifier','shippingapi_id','shippingapi_identifier','sduration_id','sduration_identifier','user_id','credential_username','country_id','country_code','pship_charges','pship_additional_charges'));
        $srch->doNotCalculateRecords();
        if ($userId) {
            $srch->addDirectCondition("( ( tp.product_seller_id = '".$userId."' and (tpsr.pship_user_id = '".$userId."' or tpsr.pship_user_id = 0)) or (tp.product_seller_id = 0 and (tpsr.pship_user_id = '".$userId."' or tpsr.pship_user_id = 0)))");
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addOrder('product_id');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductShippingColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('user_id' == $columnKey) {
                    $colValue = $colValue == '' ? 0 : $colValue;
                }
                if ('credential_username' == $columnKey) {
                    $colValue = !empty($row['credential_username']) ? $row['credential_username'] : Labels::getLabel('LBL_Admin', $langId);
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductShipping($csvFilePointer, $post, $langId, $userId = null)
    {
        $sellerId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $prodArr = array();
        $usernameArr = array();
        $scompanyIdentifierArr = array();
        $durationIdentifierArr = array();
        $countryCodeArr = array();

        $coloumArr = $this->getProductShippingColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodShipArr = array();
            $errorInRow = false;
            $breakForeach = false;
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Product::validateShippingFields($columnKey, $columnTitle, $colValue, $langId);

                if ($errMsg) {
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                        case 'product_identifier':
                            if ('product_identifier' == $columnKey) {
                                if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                    $res = $this->getAllProductsIdentifiers(false, $colValue);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $prodIndetifierArr = array_merge($prodIndetifierArr, $res);
                                    }
                                }
                                $colValue =   array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            }
                            $productId = $colValue;

                            /* Product Ship By Seller [ */
                            $srch = new ProductSearch($langId);
                            $srch->joinProductShippedBySeller($sellerId);
                            $srch->addCondition('psbs_user_id', '=', $sellerId);
                            $srch->addCondition('product_id', '=', $productId);
                            $srch->addFld('psbs_user_id');
                            $rs = $srch->getResultSet();
                            $shipBySeller = FatApp::getDb()->fetch($rs);
                            /* ] */

                            if (empty($shipBySeller) && 0 < $sellerId) {
                                $colValue = $productId = $this->getCheckAndSetProductIdByTempId($productId, $sellerId);
                            }

                            if (1 > $productId) {
                                $invalid = true;
                            }
                            $columnKey = 'pship_prod_id';
                            break;
                        case 'user_id':
                        case 'credential_username':
                            if ($this->settings['CONF_USE_USER_ID']) {
                                $userId = $colValue;
                            } else {
                                $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);

                                if (!empty($colValue) && !array_key_exists($colValue, $usernameArr)) {
                                    $res = $this->getAllUserArr(false, $colValue);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $usernameArr = array_merge($usernameArr, $res);
                                    }
                                }
                                $userId = $colValue = array_key_exists($colValue, $usernameArr) ? FatUtility::int($usernameArr[$colValue]) : 0;
                            }

                            if (0 < $sellerId && ($sellerId != $userId || 1 > $userId)) {
                                $errMsg = Labels::getLabel("MSG_Sorry_you_are_not_authorized_to_update_this_product.", $langId);
                                $breakForeach = true;
                            }

                            $columnKey = 'pship_user_id';
                            break;
                        case 'country_code':
                        case 'country_id':
                            if ('country_code' == $columnKey && !array_key_exists($colValue, $countryCodeArr)) {
                                $res = $this->getCountriesArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $countryCodeArr = array_merge($countryCodeArr, $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $countryCodeArr) ? $countryCodeArr[$colValue] : -1;
                            $columnKey = 'pship_country';
                            break;
                        case 'scompany_id':
                            $columnKey = 'pship_company';
                            break;
                        case 'scompany_identifier':
                            $columnKey = 'pship_company';
                            if (!array_key_exists($colValue, $scompanyIdentifierArr)) {
                                $res = $this->getAllShippingCompany(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $scompanyIdentifierArr = array_merge($scompanyIdentifierArr, $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $scompanyIdentifierArr) ? $scompanyIdentifierArr[$colValue] : 0;
                            break;
                        case 'sduration_id':
                            $columnKey = 'pship_duration';
                            break;
                        case 'sduration_identifier':
                            $columnKey = 'pship_duration';
                            if (!array_key_exists($colValue, $durationIdentifierArr)) {
                                $res = $this->getAllShippingDurations(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $durationIdentifierArr = array_merge($durationIdentifierArr, $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $durationIdentifierArr) ? $durationIdentifierArr[$colValue] : 0;
                            if (0 >= $colValue) {
                                $invalid = true;
                            }
                            break;
                    }

                    if (true === $invalid) {
                        if ('' == $errMsg) {
                            $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_{column-name}_is_invalid", $langId));
                        }
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $prodShipArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($prodShipArr)) {
                $data = array(
                'pship_method'=>ShippingCompanies::MANUAL_SHIPPING,
                );
                $data = array_merge($prodShipArr, $data);

                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;
                    $where =  array('smt'=> 'pship_prod_id = ? ','vals' => array( $productId ) );
                    if ($sellerId) {
                        $where =  array('smt'=> 'pship_prod_id = ? and pship_user_id = ?','vals' => array( $productId, $sellerId ) );
                    }
                    $this->db->deleteRecords(Product::DB_PRODUCT_TO_SHIP, $where);
                }
                $this->db->insertFromArray(Product::DB_PRODUCT_TO_SHIP, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductMedia($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'product_id = afile_record_id and ( afile_type = '.AttachedFile::FILETYPE_PRODUCT_IMAGE.')');
        $srch->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'ov.optionvalue_id = afile_record_subid', 'ov');
        $srch->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');
        $srch->doNotCalculateRecords();
        if ($userId) {
            $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'AND');
            $cnd->attachCondition('tp.product_seller_id', '=', 0);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }

        $srch->addMultipleFields(array('product_id','product_identifier','afile_record_id','afile_record_subid','afile_type','afile_lang_id','afile_screen','afile_physical_path','afile_name','afile_display_order','optionvalue_identifier','option_identifier','optionvalue_id','option_id'));
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('afile_lang_code' == $columnKey) {
                    $colValue = $languageCodes[ $row['afile_lang_id'] ];
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductCatalogMedia($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $optionValueIndetifierArr = array();
        $optionIdentifierArr = array();
        $prodTempArr = array();
        $prodArr = array();
        $selProdValidOptionArr = array();

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $coloumArr = $this->getProductMediaColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        $breakForeach = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodCatalogMediaArr = array();
            $errorInRow = false;
            $productId = $optionId = 0;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Product::validateMediaFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                        case 'product_identifier':
                            if ('product_id' == $columnKey) {
                                $productId = $colValue;
                            }
                            if ('product_identifier' == $columnKey) {
                                if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                    $res = $this->getAllProductsIdentifiers(false, $colValue);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $prodIndetifierArr = array_merge($prodIndetifierArr, $res);
                                    }
                                }
                                $colValue = $productId = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            }
                            $columnKey = 'afile_record_id';

                            if (!empty($userId)) {
                                $colValue = $productId = $this->getCheckAndSetProductIdByTempId($productId, $userId);
                            }

                            if (1 > $colValue) {
                                $errMsg = Labels::getLabel("MSG_Sorry_you_are_not_authorized_to_update_this_product.", $langId);
                                $invalid = true;
                                $breakForeach = true;
                            }

                            break;
                        case 'option_id':
                        case 'option_identifier':
                            if ('option_id' == $columnKey) {
                                $optionId = $colValue;
                            }
                            if ('option_identifier' == $columnKey) {
                                $optionId = 0;
                                if (!empty($colValue) && !array_key_exists($colValue, $optionIdentifierArr)) {
                                    $res = $this->getAllOptions(false, $colValue);
                                    if (!$res) {
                                        $invalid = true;
                                    }
                                    $optionIdentifierArr = array_merge($optionIdentifierArr, $res);
                                }
                                $colValue = $optionId = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                            }

                            if (!array_key_exists($productId, $selProdValidOptionArr)) {
                                $selProdValidOptionArr[$productId] = array();
                                $optionSrch = Product::getSearchObject();
                                $optionSrch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'INNER JOIN', 'tp.product_id = po.prodoption_product_id', 'po');
                                $optionSrch->addCondition('product_id', '=', $productId);
                                $optionSrch->addMultipleFields(array('prodoption_option_id'));
                                $optionSrch->doNotCalculateRecords();
                                $optionSrch->doNotLimitRecords();
                                $rs = $optionSrch->getResultSet();
                                $db = FatApp::getDb();
                                while ($rowOptions = $db->fetch($rs)) {
                                    $selProdValidOptionArr[$productId][] = $rowOptions['prodoption_option_id'];
                                }
                                if ($optionId && !in_array($optionId, $selProdValidOptionArr[$productId])) {
                                    $invalid = true;
                                }
                            }
                            break;
                        case 'optionvalue_id':
                        case 'optionvalue_identifier':
                            if ('optionvalue_id' == $columnKey) {
                                $columnKey = 'afile_record_subid';
                                $optionValueId = $colValue;
                            }
                            if ('optionvalue_identifier' == $columnKey) {
                                $columnKey = 'afile_record_subid';
                                $optionValueId = 0;
                                $optionValueIndetifierArr[$optionId] = array_key_exists($optionId, $optionValueIndetifierArr)  ? $optionValueIndetifierArr[$optionId] : array();

                                if (!empty($colValue) && !array_key_exists($colValue, $optionValueIndetifierArr[$optionId])) {
                                    $res = $this->getAllOptionValues($optionId, false, $colValue);
                                    if (!$res) {
                                        $invalid = true;
                                    }
                                    $optionValueIndetifierArr[$optionId] = array_merge($optionValueIndetifierArr[$optionId], $res);
                                }
                                $colValue = $optionValueId = isset($optionValueIndetifierArr[$optionId][$colValue]) ? $optionValueIndetifierArr[$optionId][$colValue] : 0;
                            }
                            break;
                        case 'afile_lang_code':
                            $columnKey = 'afile_lang_id';
                            $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                            break;
                    }

                    if (true === $invalid) {
                        $errMsg = !empty($errMsg) ? $errMsg : str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg ));
                        if ($breakForeach) {
                            break;
                        }
                    } else {
                        $prodCatalogMediaArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($prodCatalogMediaArr)) {
                unset($prodCatalogMediaArr['option_identifier']);
                unset($prodCatalogMediaArr['option_id']);
                $fileType = AttachedFile::FILETYPE_PRODUCT_IMAGE;

                $prodCatalogMediaArr['afile_type'] = $fileType;

                $saveToTempTable = false;
                $isUrlArr = parse_url($prodCatalogMediaArr['afile_physical_path']);
                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                if ($saveToTempTable) {
                    $prodCatalogMediaArr['afile_downloaded'] = applicationConstants::NO;
                    $prodCatalogMediaArr['afile_unique'] = applicationConstants::NO;
                    if (!in_array($productId, $prodTempArr)) {
                        $prodTempArr[] = $productId;
                        $this->db->deleteRecords(
                            AttachedFile::DB_TBL_TEMP,
                            array(
                            'smt' => 'afile_type = ? AND afile_record_id = ?',
                            'vals' => array($fileType, $productId)
                            )
                        );
                    }
                    $this->db->insertFromArray(AttachedFile::DB_TBL_TEMP, $prodCatalogMediaArr, false, array(), $prodCatalogMediaArr);
                } else {
                    if (!in_array($productId, $prodArr)) {
                        $prodArr[] = $productId;
                        $this->db->deleteRecords(
                            AttachedFile::DB_TBL,
                            array(
                            'smt' => 'afile_type = ? AND afile_record_id = ?',
                            'vals' => array( $fileType, $productId )
                            )
                        );
                    }

                    $physical_path = explode('/', $prodCatalogMediaArr['afile_physical_path']);

                    if (AttachedFile::FILETYPE_BULK_IMAGES_PATH ==  $physical_path[0].'/') {
                        $afileObj = new AttachedFile();

                        $moved = $afileObj->moveAttachment($prodCatalogMediaArr['afile_physical_path'], $fileType, $productId, $prodCatalogMediaArr['afile_record_subid'], $prodCatalogMediaArr['afile_name'], $prodCatalogMediaArr['afile_display_order'], false, $prodCatalogMediaArr['afile_lang_id']);

                        if (false === $moved) {
                            $errMsg = str_replace('{filepath}', $prodCatalogMediaArr['afile_physical_path'], Labels::getLabel("MSG_Invalid_File_{filepath}.", $langId));
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, 'N/A', $errMsg ));
                        }
                    } else {
                        $this->db->insertFromArray(AttachedFile::DB_TBL, $prodCatalogMediaArr, false, array(), $prodCatalogMediaArr);
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProductMedia($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = sp.selprod_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'pa.afile_record_id = sp.selprod_id and afile_type = '.AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD, 'pa');
        if ($userId) {
            $srch->addCondition('u.user_id', '=', $userId);
            $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('sp.*','sp_l.*','pa.*','user_id','credential_username','product_id','product_identifier'));

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();

        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('afile_lang_code' == $columnKey) {
                    $colValue = $languageCodes[ $row['afile_lang_id'] ];
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function exportSellerProdGeneralData($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);

        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = sp.selprod_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        if ($userId) {
            $srch->addCondition('u.user_id', '=', $userId);
            /*$srch->addCondition('selprod_deleted', '=', applicationConstants::NO);*/
        }
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('sp.*','sp_l.*','user_id','credential_username','product_id','product_identifier'));
        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdGeneralColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        $conditionArr = Product::getConditionArr($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($colValue) ? $colValue : Labels::getLabel('LBL_Admin', $langId));
                }
                if ('selprod_condition_identifier' == $columnKey) {
                    $colValue = array_key_exists($row['selprod_condition'], $conditionArr) ? $conditionArr[$row['selprod_condition']] : '';
                }

                if (in_array($columnKey, array( 'selprod_added_on', 'selprod_available_from' ))) {
                    $colValue = $this->displayDateTime($colValue);
                }
                if (in_array($columnKey, array( 'selprod_subtract_stock', 'selprod_track_inventory', 'selprod_active', 'selprod_cod_enabled', 'selprod_deleted' )) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdGeneralData($csvFilePointer, $post, $langId, $sellerId = null)
    {
        $sellerId = FatUtility::int($sellerId);

        $rowIndex = 1;
        $usernameArr = array();
        $prodIndetifierArr = array();
        $prodTypeArr = array();
        $userProdUploadLimit = array();

        $prodConditionArr = Product::getConditionArr($langId);
        $prodConditionArr = array_flip($prodConditionArr);
        $userId = $sellerId;

        $coloumArr = $this->getSelProdGeneralColoumArr($langId, $sellerId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $selProdGenArr = $selProdGenLangArr = array();
            $errorInRow = false;

            //if(array_key_exists($row['selprod_product_id'], $prodTypeArr))

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateGenDataFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'selprod_id':
                            $selprodId = $sellerTempId = $colValue;
                            if ($sellerId) {
                                $userId = $sellerId;
                                $userTempIdData = $this->getTempSelProdIdByTempId($sellerTempId, $sellerId);
                                if (!empty($userTempIdData) && $userTempIdData['spti_selprod_temp_id'] == $sellerTempId) {
                                    $selprodId = $colValue = $userTempIdData['spti_selprod_id'];
                                }
                            }
                            break;
                        case 'selprod_product_id':
                            $productId = $colValue;
                            break;
                        case 'product_identifier':
                            $columnKey = 'selprod_product_id';
                            if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                $res = $this->getAllProductsIdentifiers(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $prodIndetifierArr = array_merge($prodIndetifierArr, $res);
                                }
                            }
                            $productId = $colValue = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            break;
                        case 'selprod_user_id':
                            $userId = $colValue;
                            break;
                        case 'credential_username':
                            $columnKey = 'selprod_user_id';
                            $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);
                            if (!empty($colValue) && !array_key_exists($colValue, $usernameArr)) {
                                $res = $this->getAllUserArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $usernameArr = array_merge($usernameArr, $res);
                                }
                            }
                            $userId = $colValue = array_key_exists($colValue, $usernameArr) ? $usernameArr[$colValue] : 0;
                            break;
                        case 'selprod_condition_identifier':
                            $colValue = array_key_exists($colValue, $prodConditionArr) ? $prodConditionArr[$colValue] : 0;
                            $columnKey = 'selprod_condition';
                            break;
                        case 'selprod_available_from':
                            $colValue = $this->getDateTime($colValue);
                            break;
                        case 'selprod_url_keyword':
                            $urlKeyword = $colValue;
                            break;
                        case 'selprod_active':
                        case 'selprod_cod_enabled':
                        case 'selprod_deleted':
                            if (!$this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                    }

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId) {
                        if (!array_key_exists($userId, $userProdUploadLimit)) {
                            $userProdUploadLimit[$userId] = SellerPackages::getAllowedLimit($userId, $langId, 'spackage_inventory_allowed');
                        }
                    }

                    if (true === $invalid) {
                        $errorInRow = true;
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        $err = array($rowIndex, ($colIndex + 1), $errMsg);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    } else {
                        if (in_array($columnKey, array( 'selprod_title', 'selprod_comments' ))) {
                            $selProdGenLangArr[$columnKey] = $colValue;
                        } else {
                            $selProdGenArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            $userId = (!$sellerId) ? $userId : $sellerId;
            $selProdGenArr['selprod_user_id'] = $userId;

            if (false === $errorInRow && count($selProdGenArr)) {
                $prodData = Product::getAttributesById($productId, array('product_min_selling_price'));

                if (array_key_exists('selprod_price', $selProdGenArr) && $selProdGenArr['selprod_price'] < $prodData['product_min_selling_price']) {
                    $selProdGenArr['selprod_price'] = $prodData['product_min_selling_price'];
                }
                $selProdGenArr['selprod_added_on'] = date('Y-m-d H:i:s');

                $selProdData = SellerProduct::getAttributesById($selprodId, array('selprod_id', 'selprod_sold_count', 'selprod_user_id'));

                if (!empty($selProdData) && $selProdData['selprod_id'] && (!$sellerId || ($sellerId && $selProdData['selprod_user_id'] == $sellerId))) {
                    $where = array('smt' => 'selprod_id = ?', 'vals' => array( $selprodId ) );
                    $selProdGenArr['selprod_sold_count'] = $selProdData['selprod_sold_count'];

                    if ($sellerId) {
                        unset($selProdGenArr['selprod_added_on']);
                    }

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && SellerProduct::getActiveCount($userId, $selprodId) >= $userProdUploadLimit[$userId]) {
                        $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                        continue;
                    }

                    $this->db->updateFromArray(SellerProduct::DB_TBL, $selProdGenArr, $where);

                    if ($sellerId && $this->isDefaultSheetData($langId)) {
                        $tempData = array(
                        'spti_selprod_id' => $selprodId,
                        'spti_selprod_temp_id' => $sellerTempId,
                        'spti_user_id' => $userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_SELPROD_IDS, array('smt'=> 'spti_selprod_id = ? and spti_user_id = ?','vals' => array( $selprodId, $userId ) ));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_SELPROD_IDS, $tempData, false, array(), $tempData);
                    }
                } else {
                    $selProdGenArr['selprod_code'] = $productId.'_';
                    if ($sellerId) {
                        unset($selProdGenArr['selprod_id']);
                        unset($selProdGenArr['selprod_sold_count']);
                    }

                    if ($this->isDefaultSheetData($langId)) {
                        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && SellerProduct::getActiveCount($userId) >= $userProdUploadLimit[$userId]) {
                            $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                            continue;
                        }
                        $this->db->insertFromArray(SellerProduct::DB_TBL, $selProdGenArr);
                        $selprodId = $this->db->getInsertId();

                        $tempData = array(
                        'spti_selprod_id' =>$selprodId,
                        'spti_selprod_temp_id' =>$sellerTempId,
                        'spti_user_id' =>$userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_SELPROD_IDS, array('smt'=> 'spti_selprod_id = ? and spti_user_id = ?','vals' => array( $selprodId, $userId ) ));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_SELPROD_IDS, $tempData, false, array(), $tempData);
                    }
                }

                if ($selprodId) {
                    /* Lang Data [ */
                    $langData = array(
                    'selprodlang_selprod_id'=> $selprodId,
                    'selprodlang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $selProdGenLangArr);
                    $this->db->insertFromArray(SellerProduct::DB_LANG_TBL, $langData, false, array(), $langData);
                    /*]*/

                    /* Url rewriting [*/
                    if ($this->isDefaultSheetData($langId)) {
                        if (trim($urlKeyword) != '') {
                            $sellerProdObj = new SellerProduct($selprodId);
                            $sellerProdObj->rewriteUrlProduct($urlKeyword);
                            $sellerProdObj->rewriteUrlReviews($urlKeyword);
                            $sellerProdObj->rewriteUrlMoreSellers($urlKeyword);
                        }
                    }
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdOptionData($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = new SearchBase(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'spo');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_id = spo.selprodoption_selprod_id', 'sp');
        $srch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'spo.selprodoption_optionvalue_id = ov.optionvalue_id', 'ov');
        $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = '.$langId, 'ov_lang');
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');
        $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'o.option_id = o_lang.optionlang_option_id AND o_lang.optionlang_lang_id = '.$langId, 'o_lang');
        $srch->addMultipleFields(array('selprodoption_selprod_id','o.option_id', 'ov.optionvalue_id', 'option_identifier', 'optionvalue_identifier'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprodoption_selprod_id', '>=', $minId);
            $srch->addCondition('selprodoption_selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprodoption_selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdOptionsColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdOptionData($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $optionIdentifierArr = array();
        $optionValueIndetifierArr = array();
        $selProdArr = array();
        $selProdOptionsArr = array();
        $selProdValidOptionArr = array();

        $coloumArr = $this->getSelProdOptionsColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $errorInRow = false;
            $selprodId = $optionId = 0;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateOptionDataFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprodoption_selprod_id' == $columnKey) {
                        $selprodId = $colValue;
                        if ($userId) {
                            $selprodId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                    }

                    if (in_array($columnKey, array('option_id', 'option_identifier'))) {
                        $optionId = $colValue;
                        if ('option_identifier' == $columnKey) {
                            if (!array_key_exists($colValue, $optionIdentifierArr)) {
                                $res = $this->getAllOptions(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $optionIdentifierArr = array_merge($optionIdentifierArr, $res);
                                }
                            }
                            $colValue = $optionId = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                            if (1 > $optionId) {
                                $invalid = true;
                            }
                        }

                        if (!array_key_exists($selprodId, $selProdValidOptionArr)) {
                            $selProdValidOptionArr[$selprodId] = array();
                            $optionSrch = SellerProduct::getSearchObject();
                            $optionSrch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'INNER JOIN', 'sp.selprod_product_id = po.prodoption_product_id', 'po');
                            $optionSrch->addCondition('selprod_id', '=', $selprodId);
                            $optionSrch->addMultipleFields(array('prodoption_option_id'));
                            $optionSrch->doNotCalculateRecords();
                            $optionSrch->doNotLimitRecords();

                            $rs = $optionSrch->getResultSet();
                            $db = FatApp::getDb();
                            while ($spRow = $db->fetch($rs)) {
                                $selProdValidOptionArr[$selprodId][] = $spRow['prodoption_option_id'];
                            }

                            if (!in_array($optionId, $selProdValidOptionArr[$selprodId])) {
                                $invalid = true;
                            }
                        }
                    }

                    if (in_array($columnKey, array('optionvalue_id', 'optionvalue_identifier'))) {
                        $optionValueId = $colValue;
                        if ($optionId) {
                            if ('optionvalue_identifier' == $columnKey) {
                                $optionValueId = 0;
                                $optionValueIndetifierArr[$optionId] = array_key_exists($optionId, $optionValueIndetifierArr) ? $optionValueIndetifierArr[$optionId] : array();

                                if (!array_key_exists($colValue, $optionValueIndetifierArr[$optionId])) {
                                    $res = $this->getAllOptionValues($optionId, false, $colValue);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $optionValueIndetifierArr[$optionId] = array_merge($optionValueIndetifierArr[$optionId], $res);
                                    }
                                }
                                $optionValueId = array_key_exists($colValue, $optionValueIndetifierArr[$optionId]) ? $optionValueIndetifierArr[$optionId][$colValue] : 0;
                            }
                        }
                        $colValue = $optionValueId;
                        if (1 > $colValue) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    }
                }
            }

            if (false === $errorInRow) {
                if (!in_array($selprodId, $selProdArr)) {
                    $selProdArr[] = $selprodId;
                    $where = array('smt' => 'selprodoption_selprod_id = ?','vals' => array($selprodId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, $where);
                }
                $selProdOptionsArr[$selprodId]['optionValueIds'][] = $optionValueId;
                $selProdOptionsArr[$selprodId]['row'] = $rowIndex;

                $data = array(
                'selprodoption_selprod_id' => $selprodId,
                'selprodoption_option_id' => $optionId,
                'selprodoption_optionvalue_id' => $optionValueId,
                );

                $this->db->insertFromArray(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, $data, false, array(), $data);
            } else {
                $errInSheet = true;
            }
        }

        if ($selProdOptionsArr) {
            $options = array();
            foreach ($selProdOptionsArr as $k => $v) {
                $productRow = SellerProduct::getAttributesById($k, array('selprod_product_id'));
                if (!$productRow) {
                    $errMsg = Labels::getLabel("MSG_Product_not_found.", $langId);
                    $err = array($v['row'], 'N/A', $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    continue;
                }
                $options['selprod_code'] = $productRow['selprod_product_id'].'_'.implode('_', $v['optionValueIds']);
                $sellerProdObj = new SellerProduct($k);
                $sellerProdObj->assignValues($options);
                if (!$sellerProdObj->save()) {
                    $errMsg = Labels::getLabel("MSG_Product_not_saved.", $langId);
                    $err = array($v['row'], 'N/A',$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    continue;
                }
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdSeoData($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $metaTabArr = MetaTag::getTabsArr($langId);

        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = m.meta_record_id', 'm');
        $srch->joinTable(MetaTag::DB_LANG_TBL, 'LEFT OUTER JOIN', 'm_l.metalang_meta_id = m.meta_id and m_l.metalang_lang_id = '.$langId, 'm_l');
        $srch->addCondition('meta_identifier', '!=', '');
        $srch->addCondition('meta_controller', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller']);
        $srch->addCondition('meta_action', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action']);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('sp.selprod_id','m.*','m_l.*'));

        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdSeoColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdSeoData($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $metaTabArr = MetaTag::getTabsArr($langId);
        $metaSrch = MetaTag::getSearchObject();

        $coloumArr = $this->getSelProdSeoColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $selProdSeoArr = $selProdSeoLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = SellerProduct::validateSEODataFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                    }

                    if (in_array($columnKey, array( 'meta_title', 'meta_keywords', 'meta_description', 'meta_other_meta_tags' ))) {
                        $selProdSeoLangArr[$columnKey] = $colValue;
                    } else {
                        $selProdSeoArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($selProdSeoArr)) {
                $data = array(
                'meta_controller' => $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller'],
                'meta_action' => $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action'],
                'meta_record_id' => $selProdId,
                );
                $data = array_merge($data, $selProdSeoArr);

                $srch = clone $metaSrch;
                $srch->addCondition('meta_controller', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller']);
                $srch->addCondition('meta_action', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action']);
                $srch->addCondition('meta_record_id', '=', $selProdId);
                $srch->addMultipleFields(array('meta_id','meta_record_id'));
                $srch->doNotCalculateRecords();
                $srch->setPageSize(1);
                $rs = $srch->getResultSet();
                $row = $this->db->fetch($rs);
                if ($row && $row['meta_record_id'] === $selProdId) {
                    $metaId = $row['meta_id'];
                    $where = array('smt' => 'meta_controller = ? AND meta_action = ? AND meta_record_id = ?', 'vals' => array( $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller'], $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action'],$selProdId ) );
                    $this->db->updateFromArray(MetaTag::DB_TBL, $data, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        unset($data['selprod_id']);
                        $resp = $this->db->insertFromArray(MetaTag::DB_TBL, $data);
                        $metaId = $this->db->getInsertId();
                    }
                }

                if (isset($metaId)) {
                    /* Lang Data [*/
                    $langData = array(
                    'metalang_meta_id'=> $metaId,
                    'metalang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $selProdSeoLangArr);
                    $this->db->insertFromArray(MetaTag::DB_LANG_TBL, $langData, false, array(), $langData);
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdSpecialPrice($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'INNER JOIN', 'sp.selprod_id = spsp.splprice_selprod_id', 'spsp');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spsp.*','sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdSpecialPriceColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */


        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if (in_array($columnKey, array( 'splprice_start_date', 'splprice_end_date' ))) {
                    $colValue = $this->displayDate($colValue);
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdSpecialPrice($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $persentOrFlatTypeArr = applicationConstants::getPercentageFlatArr($langId);
        $persentOrFlatTypeArr = array_flip($persentOrFlatTypeArr);
        $selProdArr = array();

        $coloumArr = $this->getSelProdSpecialPriceColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $sellerProdSplPriceArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = $errMsg = false;

                $errMsg = SellerProduct::validateSplPriceFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    }
                    if (in_array($columnKey, array( 'splprice_start_date', 'splprice_end_date'))) {
                        $colValue = $this->getDateTime($colValue, false);
                    }
                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $sellerProdSplPriceArr[$columnKey] = $colValue;
                    }
                }
            }

            unset($sellerProdSplPriceArr['selprod_id']);
            if (false === $errorInRow && count($sellerProdSplPriceArr)) {
                $data = array(
                'splprice_selprod_id'=>$selProdId,
                );
                $data = array_merge($data, $sellerProdSplPriceArr);

                $res = SellerProduct::getSellerProductSpecialPrices($selProdId);
                if (!empty($res)) {
                    if (!in_array($selProdId, $selProdArr)) {
                        $selProdArr[] = $selProdId;
                        $where = array('smt' => 'splprice_selprod_id = ?','vals' => array($selProdId));
                        $this->db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, $where);
                    }
                }
                $this->db->insertFromArray(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdVolumeDiscount($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProductVolumeDiscount::DB_TBL, 'INNER JOIN', 'sp.selprod_id = spvd.voldiscount_selprod_id', 'spvd');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spvd.voldiscount_min_qty','spvd.voldiscount_percentage','sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdVolumeDiscountColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdVolumeDiscount($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();

        $coloumArr = $this->getSelProdVolumeDiscountColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $selProdVolDisArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateVolDiscountFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array( $rowIndex, ($colIndex + 1), $errMsg );
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $selProdVolDisArr[$columnKey] = $colValue;
                    }
                }
            }
            unset($selProdVolDisArr['selprod_id']);
            if (false === $errorInRow && count($selProdVolDisArr)) {
                $data = array(
                'voldiscount_selprod_id'=>$selProdId,
                );
                $data = array_merge($data, $selProdVolDisArr);

                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array( 'smt' => 'voldiscount_selprod_id = ?','vals' => array( $selProdId ) );
                    $this->db->deleteRecords(SellerProductVolumeDiscount::DB_TBL, $where);
                }
                $this->db->insertFromArray(SellerProductVolumeDiscount::DB_TBL, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdBuyTogther($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_UPSELL_PRODUCTS, 'INNER JOIN', 'sp.selprod_id = spu.upsell_sellerproduct_id', 'spu');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spu.upsell_sellerproduct_id','spu.upsell_recommend_sellerproduct_id','sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdBuyTogetherColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdBuyTogther($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();
        $selProdUserArr = array();

        $coloumArr = $this->getSelProdBuyTogetherColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $errorInRow = false;
            $selProdId = 0;
            $selProdBuyTogetherArr = array();

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateBuyTogetherFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex,($colIndex + 1),$errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $columnKey = 'upsell_sellerproduct_id';
                        $selProdId = $colValue;
                        if (0 < $userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }

                        if (!array_key_exists($selProdId, $selProdUserArr)) {
                            $res = SellerProduct::getAttributesById($selProdId, array( 'selprod_id', 'selprod_user_id' ));
                            if (empty($res)) {
                                $invalid = true;
                            } else {
                                $selProdUserArr[ $res['selprod_id'] ] = $res['selprod_user_id'];
                            }
                        }
                    }

                    if ('upsell_recommend_sellerproduct_id' == $columnKey) {
                        $upselProdId = $colValue;
                        if (0 < $userId) {
                            $upselProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($upselProdId, $userId);
                        }

                        if (1 > $upselProdId) {
                            $invalid = true;
                        }

                        if (!array_key_exists($upselProdId, $selProdUserArr)) {
                            $res = SellerProduct::getAttributesById($upselProdId, array( 'selprod_id', 'selprod_user_id' ));
                            if (empty($res)) {
                                $invalid = true;
                            } else {
                                $selProdUserArr[ $res['selprod_id'] ] = $res['selprod_user_id'];
                            }
                        }

                        if ((array_key_exists($selProdId, $selProdUserArr) && array_key_exists($upselProdId, $selProdUserArr) && $selProdUserArr[$selProdId] != $selProdUserArr[$upselProdId]) || !array_key_exists($selProdId, $selProdUserArr) || !array_key_exists($upselProdId, $selProdUserArr)) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $selProdBuyTogetherArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($selProdBuyTogetherArr)) {
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'upsell_sellerproduct_id = ?','vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_UPSELL_PRODUCTS, $where);
                }

                $this->db->insertFromArray(SellerProduct::DB_TBL_UPSELL_PRODUCTS, $selProdBuyTogetherArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdRelatedProd($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_RELATED_PRODUCTS, 'INNER JOIN', 'sp.selprod_id = spr.related_sellerproduct_id', 'spr');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spr.related_sellerproduct_id','spr.related_recommend_sellerproduct_id','sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdRelatedProductColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdRelatedProd($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();

        $coloumArr = $this->getSelProdRelatedProductColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $sellerProdSplPriceArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = $errMsg = false;

                $errMsg = SellerProduct::validateRelatedProdFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $columnKey = 'related_sellerproduct_id';
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    }
                    $sellerProdSplPriceArr[$columnKey] = $colValue;
                }
            }

            if (false === $errorInRow && count($sellerProdSplPriceArr)) {
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'related_sellerproduct_id = ?','vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_RELATED_PRODUCTS, $where);
                }
                $this->db->insertFromArray(SellerProduct::DB_TBL_RELATED_PRODUCTS, $sellerProdSplPriceArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdPolicy($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_POLICY, 'INNER JOIN', 'sp.selprod_id = spp.sppolicy_selprod_id', 'spp');
        $srch->joinTable(PolicyPoint::DB_TBL, 'INNER JOIN', 'spp.sppolicy_ppoint_id = pp.ppoint_id', 'pp');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('pp.ppoint_identifier','sp.selprod_id','spp.sppolicy_ppoint_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdPolicyColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdPolicy($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $policyPonitIdentifierArr = array();
        $policyPonitIdArr = array();
        $selProdArr = array();

        $coloumArr = $this->getSelProdPolicyColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $sellerProdPolicyArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateProdPolicyFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $columnKey = 'sppolicy_selprod_id';
                        $selProdId = $colValue;
                        if (0 < $userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (1 > $selProdId) {
                            $invalid = true;
                        }
                    }

                    if (in_array($columnKey, array( 'sppolicy_ppoint_id', 'ppoint_identifier' ))) {
                        if ('sppolicy_ppoint_id' == $columnKey) {
                            $colValue = $policyPointId = FatUtility::int($colValue);

                            if (!array_key_exists($policyPointId, $policyPonitIdArr)) {
                                $res = $this->getAllPrivacyPoints(true, $policyPointId);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $policyPonitIdArr = array_merge($policyPonitIdArr, $res);
                                }
                            }
                        }

                        if ('ppoint_identifier' == $columnKey) {
                            $columnKey = 'sppolicy_ppoint_id';

                            if (!array_key_exists($colValue, $policyPonitIdentifierArr)) {
                                $res = $this->getAllPrivacyPoints(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $policyPonitIdentifierArr = array_merge($policyPonitIdentifierArr, $res);
                                }
                            }
                            $colValue = $policyPointId = $policyPonitIdentifierArr[$colValue];
                        }

                        if (1 > $policyPointId) {
                            $invalid = true;
                        }
                    }
                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $sellerProdPolicyArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($sellerProdPolicyArr)) {
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'sppolicy_selprod_id = ?','vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_POLICY, $where);
                }
                $this->db->insertFromArray(SellerProduct::DB_TBL_SELLER_PROD_POLICY, $sellerProdPolicyArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportOptions($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = Option::getSearchObject($langId, false);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = o.option_seller_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = o.option_seller_id', 'uc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('option_id','option_identifier','option_seller_id','option_type','option_deleted','option_is_separate_images','option_is_color','option_display_in_filter','IFNULL(option_name,option_identifier)option_name','credential_username'));
        $srch->addOrder('option_id', 'ASC');
        if ($userId) {
            $srch->addCondition('option_deleted', '=', applicationConstants::NO);
        }
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getOptionsColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        /* $optionTypeArr = Option::getOptionTypes($langId); */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($colValue) ? $colValue : Labels::getLabel('LBL_Admin', $langId));
                }

                if (in_array($columnKey, array( 'option_is_separate_images', 'option_is_color', 'option_display_in_filter', 'option_deleted' )) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importOptions($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $optionIdentifierArr = array();
        $optionIdArr = array();
        $userArr = array();

        $coloumArr = $this->getOptionsColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $optionsArr = $optionsLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Option::validateOptionFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('credential_username' ==  $columnKey) {
                        $columnKey = 'option_seller_id';
                        $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);

                        if (!empty($colValue)) {
                            if (!array_key_exists($colValue, $userArr)) {
                                $res = $this->getAllUserArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $userArr = array_merge($userArr, $res);
                                }
                            }
                            $colValue = $userId = array_key_exists($colValue, $userArr) ? $userArr[$colValue] : 0;
                        }
                    }

                    if (in_array($columnKey, array( 'option_is_separate_images', 'option_is_color', 'option_display_in_filter', 'option_deleted' ))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = FatUtility::int($colValue);
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                        }
                    }
                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        if ('option_name' == $columnKey) {
                            $optionsLangArr[$columnKey] = $colValue;
                        } else {
                            $optionsArr[$columnKey] = $colValue;
                        }
                    }
                }
            }
            if (false === $errorInRow && count($optionsArr)) {
                $data = array( 'option_type' => Option::OPTION_TYPE_SELECT );

                $data = array_merge($data, $optionsArr);

                if ($this->settings['CONF_USE_OPTION_ID']) {
                    $optionData =  Option::getAttributesById($data['option_id'], array('option_id'));
                } else {
                    $brandId = 0;
                    $optionData = Option::getAttributesByIdentifier($data['option_identifier'], array('option_id'));
                }


                if (!empty($optionData) && $optionData['option_id']) {
                    $optionId = $optionData['option_id'];
                    $where = array('smt' => 'option_id = ?', 'vals' => array( $optionId ) );
                    $this->db->updateFromArray(Option::DB_TBL, $data, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(Option::DB_TBL, $data);
                        $optionId = $this->db->getInsertId();
                    }
                }

                if ($optionId) {
                    /* Lang Data [*/
                    $langData = array(
                    'optionlang_option_id'=> $optionId,
                    'optionlang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $optionsLangArr);
                    $this->db->insertFromArray(Option::DB_LANG_TBL, $langData, false, array(), $langData);
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportOptionValues($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = OptionValue::getSearchObject();
        $srch->joinTable(
            OptionValue::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'ovl.optionvaluelang_optionvalue_id = ov.optionvalue_id
		AND ovl.optionvaluelang_lang_id = ' . $langId,
            'ovl'
        );
        $srch->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'ov.optionvalue_option_id = o.option_id', 'o');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('optionvalue_id','optionvalue_option_id','optionvalue_identifier','optionvalue_color_code','optionvalue_display_order','IFNULL(optionvalue_name,optionvalue_identifier) as optionvalue_name','option_identifier'));
        $srch->addOrder('optionvalue_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getOptionsValueColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importOptionValues($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $optionIdentifierArr = array();
        $optionIdArr = array();

        $optionValueObj= new OptionValue();
        $srchObj = OptionValue::getSearchObject();

        $coloumArr = $this->getOptionsValueColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $sellerProdPolicyArr = $sellerProdPolicyLangArr = array();
            $errorInRow = false;
            $optionvalue_identifier = '';

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Option::validateOptionValFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('optionvalue_display_order' == $columnKey) {
                        $colValue = FatUtility::int($colValue);
                    }
                    if (in_array($columnKey, array( 'optionvalue_id', 'optionvalue_identifier' ))) {
                        if ('optionvalue_id' == $columnKey) {
                            $optionValueData = OptionValue::getAttributesById($colValue, array( 'optionvalue_id' ));
                        } else {
                            $optionvalue_identifier = $colValue;
                        }
                    }

                    if (in_array($columnKey, array( 'optionvalue_option_id', 'option_identifier' ))) {
                        $optionId = 0;
                        if ('optionvalue_option_id' == $columnKey && !array_key_exists($colValue, $optionIdArr)) {
                            $optionId = $colValue;
                            $res = $this->getAllOptions(true, $optionId);
                            if (!$res) {
                                $invalid = true;
                            } else {
                                $optionIdArr = array_merge($optionIdArr, $res);
                            }
                        }

                        if ('option_identifier' == $columnKey) {
                            $columnKey = 'optionvalue_option_id';
                            if (!array_key_exists($colValue, $optionIdentifierArr)) {
                                $res = $this->getAllOptions(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $optionIdentifierArr = array_merge($optionIdentifierArr, $res);
                                }
                            }

                            $optionId = $colValue = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                        }

                        if (1 > $optionId) {
                            $invalid = true;
                        } else {
                            $optionValueData = $optionValueObj->getAtttibutesByIdentifierAndOptionId($optionId, $optionvalue_identifier, array( 'optionvalue_id' ));
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        if ('optionvalue_name' == $columnKey) {
                            $sellerProdPolicyLangArr[$columnKey] = $colValue;
                        } else {
                            $sellerProdPolicyArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($sellerProdPolicyArr)) {
                if (!empty($optionValueData) && $optionValueData['optionvalue_id']) {
                    $optionValueId = $optionValueData['optionvalue_id'];
                    $where = array('smt' => 'optionvalue_id = ?', 'vals' => array( $optionValueId ) );
                    $this->db->updateFromArray(OptionValue::DB_TBL, $sellerProdPolicyArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(OptionValue::DB_TBL, $sellerProdPolicyArr);
                        $optionValueId = $this->db->getInsertId();
                    }
                }

                if ($optionValueId) {
                    /* Lang Data [*/
                    $langData = array(
                    'optionvaluelang_optionvalue_id'=> $optionValueId,
                    'optionvaluelang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $sellerProdPolicyLangArr);

                    $this->db->insertFromArray(OptionValue::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportTags($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = Tag::getSearchObject($langId);
        $srch->addMultipleFields(array('tag_id','tag_identifier','tag_user_id','tag_admin_id','tag_name','credential_username'));
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = t.tag_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getTagColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($colValue) ? $colValue : Labels::getLabel('LBL_Admin', $langId));
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importTags($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $usernameArr = array();
        $useTagId  = false;
        if ($this->settings['CONF_USE_TAG_ID']) {
            $useTagId = true;
        }

        $coloumArr = $this->getTagColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $tagsArr = $tagsLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Tag::validateTagsFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('tag_user_id' == $columnKey) {
                        $userId = $colValue;
                    }
                    if ('credential_username' == $columnKey) {
                        $columnKey = 'tag_user_id';
                        $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);

                        if (!empty($colValue) && !array_key_exists($colValue, $usernameArr)) {
                            $res = $this->getAllUserArr(false, $colValue);
                            if (!$res) {
                                $invalid = true;
                            } else {
                                $usernameArr = array_merge($usernameArr, $res);
                            }
                        }
                        $userId = $colValue = array_key_exists($colValue, $usernameArr) ? $usernameArr[$colValue] : 0;
                    }

                    if (in_array($columnKey, array( 'tag_identifier', 'tag_name' )) && empty($colValue)) {
                        if ('tag_id' == $columnKey) {
                            $tagData = Tag::getAttributesById($colValue, array('tag_id'));
                        }

                        if ('tag_identifier' == $columnKey) {
                            $tagData = Tag::getAttributesByIdentifier($colValue, array('tag_id'));
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        if ('tag_name' == $columnKey) {
                            $tagsLangArr[$columnKey] = $colValue;
                        } else {
                            if (isset($userId)) {
                                $tagsArr['tag_admin_id']    = 0;
                            }

                            $tagsArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($tagsArr)) {
                if (!empty($tagData) && $tagData['tag_id']) {
                    $tagId = $tagData['tag_id'];
                    $where = array('smt' => 'tag_id = ?', 'vals' => array( $tagId ) );
                    $this->db->updateFromArray(Tag::DB_TBL, $tagsArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(Tag::DB_TBL, $tagsArr);
                        $tagId = $this->db->getInsertId();
                    }
                }

                if ($tagId) {
                    /* Lang Data [*/
                    $langData = array(
                    'taglang_tag_id'=> $tagId,
                    'taglang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $tagsLangArr);

                    $this->db->insertFromArray(Tag::DB_LANG_TBL, $langData, false, array(), $langData);
                    /* ]*/

                    /* update product tags association and tag string in products lang table[ */
                    Tag::updateTagStrings($tagId);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportCountries($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);

        $srch = Countries::getSearchObject(false, $langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('country_active', '=', applicationConstants::ACTIVE);
        }
        $rs = $srch->getResultSet();

        $languageCodes = Language::getAllCodesAssoc(true);
        $currencyCodes = Currency::getCurrencyAssoc(true);

        $useCountryId = false;
        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $useCountryId = true;
        }

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getCountryColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : 'a';

                if ('country_currency_code' == $columnKey) {
                    $colValue =  array_key_exists($row['country_currency_id'], $currencyCodes) ? $currencyCodes[$row['country_currency_id']] : 0;
                }

                if ('country_language_code' == $columnKey) {
                    $colValue =  array_key_exists($row['country_language_id'], $languageCodes) ? $languageCodes[$row['country_language_id']] : 0;
                }

                if ('country_active' == $columnKey) {
                    if (!$this->settings['CONF_USE_O_OR_1']) {
                        $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                    }
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importCountries($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        $useCountryId  = false;
        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $useCountryId = true;
        }

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $currencyCodes = Currency::getCurrencyAssoc(true);
        $currencyIds = array_flip($currencyCodes);

        $coloumArr = $this->getCountryColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $countryArr = $countryLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = Countries::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'country_currency_id':
                            $currencyId = FatUtility::int($colValue);
                            $colValue =  array_key_exists($currencyId, $currencyCodes) ? $currencyId : 0;
                            break;
                        case 'country_currency_code':
                            $columnKey = 'country_currency_id';
                            $colValue = array_key_exists($colValue, $currencyIds) ? $currencyIds[$colValue] : 0;
                            break;
                        case 'country_language_id':
                            $currencyLangId = FatUtility::int($colValue);
                            $colValue = array_key_exists($currencyLangId, $languageCodes) ? $currencyLangId : 0;
                            break;
                        case 'country_language_code':
                            $columnKey = 'country_language_id';
                            $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                            break;
                        case 'country_active':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = FatUtility::int($colValue);
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'country_id':
                            $countryData = Countries::getAttributesById($colValue, array('country_id'));
                            break;
                        case 'country_code':
                            $countryData = Countries::getCountryByCode($colValue, array('country_id'));
                            break;
                        case 'country_name':
                            $countryLangArr[$columnKey] = $colValue;
                            break;
                    }

                    $countryArr[$columnKey] = $colValue;
                    unset($countryArr['country_name']);
                }
            }

            if (false === $errorInRow && count($countryArr)) {
                if (!empty($countryData) && $countryData['country_id']) {
                    $countryId = $countryData['country_id'];
                    $where = array('smt' => 'country_id = ?', 'vals' => array( $countryId ) );
                    $this->db->updateFromArray(Countries::DB_TBL, $countryArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        unset($countryArr['country_language_code']);
                        $this->db->insertFromArray(Countries::DB_TBL, $countryArr);
                        $countryId = $this->db->getInsertId();
                    }
                }

                if ($countryId) {
                    /* Lang Data [*/
                    $langData = array(
                    'countrylang_country_id'=> $countryId,
                    'countrylang_lang_id'=> $langId,
                    );
                    $langData = array_merge($langData, $countryLangArr);
                    $this->db->insertFromArray(Countries::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportStates($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $useStateId = false;
        if ($this->settings['CONF_USE_STATE_ID']) {
            $useStateId = true;
        }

        $srch = States::getSearchObject(false, $langId);
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'st.state_country_id = c.country_id', 'c');
        $srch->addMultipleFields(array('state_id','state_code','state_country_id','state_identifier','state_active','country_id','country_code','state_name'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('state_active', '=', applicationConstants::ACTIVE);
        }

        if ($useStateId) {
            $srch->addOrder('state_country_id', 'ASC');
            $srch->addOrder('state_id', 'ASC');
        } else {
            $srch->addOrder('country_code', 'ASC');
            $srch->addOrder('state_identifier', 'ASC');
        }

        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getStatesColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('state_active' == $columnKey && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function exportCities($langId, $userId = 0)
    { 
        $userId = FatUtility::int($userId);        

        $srch = Cities::getSearchObject(false, $langId);
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'ct.city_country_id = c.country_id', 'c');
        $srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'ct.city_state_id = st.state_id', 'st');
        $srch->addMultipleFields(array('city_id','city_code','city_country_id','city_state_id','city_identifier','city_active','country_code','state_identifier','city_name'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('city_active', '=', applicationConstants::ACTIVE);
        }
       
        $srch->addOrder('city_name', 'ASC');
       // $srch->addOrder('state_identifier', 'ASC');    
        $rs = $srch->getResultSet();
        $sheetData = array();
        // * Sheet Heading Row [
        $headingsArr = $this->getCitiesColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        // ] 

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('city_active' == $columnKey && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importStates($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $countryCodes = $this->getCountriesArr(true);
        } else {
            $countryIds = $this->getCountriesArr(false);
        }

        $coloumArr = $this->getStatesColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $statesArr = $statesLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = States::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'state_country_id':
                            $countryId = FatUtility::int($colValue);
                            $colValue = array_key_exists($countryId, $countryCodes) ? $countryId : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'country_code':
                            $columnKey = 'state_country_id';
                            $colValue = array_key_exists($colValue, $countryIds) ? $countryIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'state_active':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'state_name':
                            if (false === $invalid) {
                                $statesLangArr[$columnKey] = $colValue;
                            }
                            break;
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $statesArr[$columnKey] = $colValue;
                        unset($statesArr['state_name']);
                    }
                }
            }

            if (false === $errorInRow && count($statesArr)) {
                if ($this->settings['CONF_USE_STATE_ID']) {
                    $stateData = States::getAttributesById($statesArr['state_id'], array('state_id'));
                } else {
                    $stateData = States::getAttributesByIdentifierAndCountry($statesArr['state_identifier'], $statesArr['state_country_id'], array('state_id'));
                }

                if (!empty($stateData) && $stateData['state_id']) {
                    $stateId = $stateData['state_id'];
                    $where = array('smt' => 'state_id = ?', 'vals' => array( $stateId ) );
                    $this->db->updateFromArray(States::DB_TBL, $statesArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(States::DB_TBL, $statesArr);
                        $stateId = $this->db->getInsertId();
                    }
                }
                if ($stateId) {
                    /* Lang Data [*/
                    $langData = array(
                    'statelang_state_id'=> $stateId,
                    'statelang_lang_id'=> $langId,
                    );

                    $langData = array_merge($langData, $statesLangArr);

                    $this->db->insertFromArray(States::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function importCities($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $countryCodes = $this->getCountriesArr(true);
        } else {
            $countryIds = $this->getCountriesArr(false);
        }
        
        if ($this->settings['CONF_USE_STATE_ID']) {
            $stateCodes = $this->getStatesArr(true);
        } else {
            $stateIds = $this->getStatesArr(false);
        }

        $coloumArr = $this->getCitiesColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $citiesArr = $citiesLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $invalid = false;

               $errMsg = Cities::validateFields($columnKey, $columnTitle, $colValue, $langId);
               
                if (false !== $errMsg) {
                    
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                   
                    switch ($columnKey) {
                        case 'city_country_id':
                            $colValue = array_key_exists($colValue, $countryIds) ? $countryIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'city_state_id':
                            $colValue = array_key_exists($colValue, $stateIds) ? $stateIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'city_identifier':
                            $colValue =  $colValue;
                            break;
                        case 'city_code':
                            $colValue =  $colValue;
                            break;
                        case 'city_active':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'city_name':
                            if (false === $invalid) {
                                $citiesLangArr[$columnKey] = $colValue;
                            }
                            break;
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $citiesArr[$columnKey] = $colValue;
                        unset($citiesArr['city_name']);
                    }
                }
            }
           
            if (false === $errorInRow && count($citiesArr)) {
                if ($this->settings['CONF_USE_CITY_ID']) {
                    $cityData = Cities::getAttributesById($citiesArr['city_id'], array('city_id'));
                } else {
                    $cityData = Cities::getAttributesByIdentifierAndCountry($citiesArr['city_identifier'], $citiesArr['city_country_id'],$citiesArr['city_state_id'], array('city_id'));
                }

                if (!empty($cityData) && $cityData['city_id']) {
                    $cityId = $cityData['city_id'];
                    $where = array('smt' => 'city_id = ?', 'vals' => array( $cityId ) );
                    $this->db->updateFromArray(Cities::DB_TBL, $citiesArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(Cities::DB_TBL, $citiesArr);
                        $cityId = $this->db->getInsertId();
                    }
                }
                if ($cityId) {
                    /* Lang Data [*/
                    $langData = array(
                    'citylang_city_id'=> $cityId,
                    'citylang_lang_id'=> $langId,
                    );

                    $langData = array_merge($langData, $citiesLangArr);
                    $this->db->insertFromArray(Cities::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }


    public function importShippingSettings($csvFilePointer, $post, $langId)
    {
        $srch = Cities::getSearchObject(false, false);
        $rs = $srch->getResultSet();
        $cityCollection = $this->db->fetchAll($rs);

        $cityIds = array();
        foreach($cityCollection as $city){
            $cityIds[$city['city_identifier']] = $city['city_id'];
        }


        $srch = ShippingCompanies::getSearchObject(false, false); 
        $rs = $srch->getResultSet();
        $shCompColl = $this->db->fetchAll($rs);
       
        $shCompanyIds = array();
        foreach($shCompColl as $company){
            $shCompanyIds[$company['scompany_identifier']] = $company['scompany_id'];
        }
       
        $rowIndex = 1;
        $langId = 1;
        $coloumArr = $this->getShipSetColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;

        //delete Record from Shipping Setting Table
        FatApp::getDb()->deleteRecords('tbl_shipping_settings', array( 'smt' => 'ship_set_user_id = ?', 'vals' => array(UserAuthentication::getLoggedUserId())));

        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $shipSetArr = array();
            $errorInRow = false;
           
            // echo '<pre>';
            // print_r($coloumArr);
            // exit();

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

               $errMsg = ShippingSettings::validateFields($columnKey, $columnTitle, $colValue, $langId);
               
                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'ship_set_city':
                            $colValue = array_key_exists($colValue, $cityIds) ? $cityIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'ship_set_company':
                            $colValue = array_key_exists($colValue, $shCompanyIds) ? $shCompanyIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'ship_set_duration_from':
                            $from = $colValue;
                            $to  = $this->getCell($row, $colIndex+1, '');
                           
                            $srch = new SearchBase('tbl_shipping_durations');
                            $srch->addCondition('sduration_from', '=', $from);
                            $srch->addCondition('sduration_to', '=', $to);
                            $rs = $srch->getResultSet();
                            $result = FatApp::getDb()->fetchAll($rs);
                            if($result){
                                $colValue =  $result[0]['sduration_id'];
                            }else{
                                if($from == $to){
                                    $cond = '';
                                    if($from > 1){
                                        $cond = 's';
                                    }
                                    $identifier = $from.' Business Day'.$cond;
                                }else{
                                    $identifier = $from.' to '.$to.' Business Days';
                                }
                                $data = array(
                                        'sduration_identifier' => $identifier,
                                        'sduration_from' => $from,
                                        'sduration_to' => $to,
                                        'sduration_days_or_weeks' => 1,
                                        'sduration_deleted' => 0
                                        );
                                $cc = $this->db->insertFromArray('tbl_shipping_durations', $data);
                                $shippingId = $this->db->getInsertId();
                                $colValue = $shippingId;
                                if (!$shippingId) {
                                    Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
                                    FatUtility::dieWithError(Message::getHtml());
                                }else{
                                    $data = array(
                                        'sdurationlang_sduration_id'=>$shippingId,
                                        'sdurationlang_lang_id' => 1,
                                        'sduration_name' => $identifier
                                        );
                                    $this->db->insertFromArray('tbl_shipping_durations_lang', $data);
                                }
                            }
                           break;
                        case 'cost_for_1st_kg':
                            $colValue =  $colValue;
                            break;
                        case 'each_additional_kg':
                            $colValue =  $colValue;
                            break;
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $shipSetArr[$columnKey] = $colValue;
                        if($columnKey == 'ship_set_duration_from'){
                            $shipSetArr['ship_set_duration'] = $colValue;
                        }
                        unset($shipSetArr['ship_set_duration_from']);
                        unset($shipSetArr['ship_set_duration_to']);
                    }
                }
            }
            
            if (false === $errorInRow && count($shipSetArr)) {
               if ($this->isDefaultSheetData($langId)) {
                    $shipSetArr['ship_set_user_id'] = UserAuthentication::getLoggedUserId();
                    $shipSetArr['ship_set_method'] = 1;

                    //check record exists or not
                    $srch = new SearchBase('tbl_shipping_settings');
                    $srch->addCondition('ship_set_user_id', '=', UserAuthentication::getLoggedUserId());
                    $srch->addCondition('ship_set_city', '=', $shipSetArr['ship_set_city']);
                    $srch->addCondition('ship_set_method', '=', 1);
                    $srch->addCondition('ship_set_company', '=', $shipSetArr['ship_set_company']);
                    $srch->addCondition('ship_set_duration', '=', $shipSetArr['ship_set_duration']);
                    $srch->addCondition('cost_for_1st_kg', '=', $shipSetArr['cost_for_1st_kg']);
                    $srch->addCondition('each_additional_kg', '=', $shipSetArr['each_additional_kg']);
                    $rs = $srch->getResultSet();
                    $result = FatApp::getDb()->fetchAll($rs);
                    if(empty($result)){
                        $this->db->insertFromArray('tbl_shipping_settings', $shipSetArr);
                        $shippingSettingId = $this->db->getInsertId();
                    }
                    
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }


    public function importProductShippingRate($csvFilePointer, $post, $langId)
    {
        
        $productId = $post['product_id'];
        
        $srch = Cities::getSearchObject(false, false);
        $rs = $srch->getResultSet();
        $cityCollection = $this->db->fetchAll($rs);

        $cityIds = array();
        foreach($cityCollection as $city){
            $cityIds[$city['city_identifier']] = $city['city_id'];
        }


        $srch = ShippingCompanies::getSearchObject(false, false); 
        $rs = $srch->getResultSet();
        $shCompColl = $this->db->fetchAll($rs);
       
        $shCompanyIds = array();
        foreach($shCompColl as $company){
            $shCompanyIds[$company['scompany_identifier']] = $company['scompany_id'];
        }
       
        $rowIndex = 1;
        $langId = 1;
        $coloumArr = $this->getProductShipRateColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;

         //delete Record from Shipping Setting Table
         $whr = array('smt'=>'pship_prod_id = ? and pship_user_id = ?', 'vals'=>array($productId, UserAuthentication::getLoggedUserId()));
         FatApp::getDb()->deleteRecords('tbl_product_shipping_rates', $whr);
        
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $shipSetArr = array();
            $errorInRow = false;
           
            // echo '<pre>';
            // print_r($coloumArr);
            // exit();

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

               $errMsg = Product::validateFieldsCheck($columnKey, $columnTitle, $colValue, $langId);
               
                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'pship_city':
                            $colValue = array_key_exists($colValue, $cityIds) ? $cityIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'pship_company':
                            $colValue = array_key_exists($colValue, $shCompanyIds) ? $shCompanyIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'pship_duration_from':
                            $from = $colValue;
                            $to  = $this->getCell($row, $colIndex+1, '');
                           
                            $srch = new SearchBase('tbl_shipping_durations');
                            $srch->addCondition('sduration_from', '=', $from);
                            $srch->addCondition('sduration_to', '=', $to);
                            $rs = $srch->getResultSet();
                            $result = FatApp::getDb()->fetchAll($rs);
                            if($result){
                                $colValue =  $result[0]['sduration_id'];
                            }else{
                                if($from == $to){
                                    $cond = '';
                                    if($from > 1){
                                        $cond = 's';
                                    }
                                    $identifier = $from.' Business Day'.$cond;
                                }else{
                                    $identifier = $from.' to '.$to.' Business Days';
                                }
                                $data = array(
                                        'sduration_identifier'=>$identifier,
                                        'sduration_from' => $from,
                                        'sduration_to' => $to,
                                        'sduration_days_or_weeks' => 1,
                                        'sduration_deleted'=> 0
                                        );
                                $this->db->insertFromArray('tbl_shipping_durations', $data);
                                $shippingId = $this->db->getInsertId();
                                $colValue = $shippingId;
                                if (!$shippingId) {
                                    Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
                                    FatUtility::dieWithError(Message::getHtml());
                                }else{
                                    $data = array(
                                        'sdurationlang_sduration_id'=>$shippingId,
                                        'sdurationlang_lang_id' => 1,
                                        'sduration_name' => $identifier
                                        );
                                    $this->db->insertFromArray('tbl_shipping_durations_lang', $data);
                                }
                            }
                           break;
                        case 'pship_charges':
                            $colValue =  $colValue;
                            break;
                        case 'pship_additional_charges':
                            $colValue =  $colValue;
                            break;
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        $shipSetArr[$columnKey] = $colValue;
                        if($columnKey == 'pship_duration_from'){
                            $shipSetArr['pship_duration'] = $colValue;
                        }
                        unset($shipSetArr['pship_duration_from']);
                        unset($shipSetArr['pship_duration_to']);
                    }
                }
            }
            
            if (false === $errorInRow && count($shipSetArr)) {
               if ($this->isDefaultSheetData($langId)) {
                    $shipSetArr['pship_prod_id'] = $productId;
                    $shipSetArr['pship_user_id'] = UserAuthentication::getLoggedUserId();
                    $shipSetArr['pship_method'] = 1;
                   
                    //check record exists or not
                    $srch = new SearchBase('tbl_product_shipping_rates');
                    $srch->addCondition('pship_prod_id', '=', $productId);
                    $srch->addCondition('pship_user_id', '=', UserAuthentication::getLoggedUserId());
                    $srch->addCondition('pship_city', '=', $shipSetArr['pship_city']);
                    $srch->addCondition('pship_method', '=', 1);
                    $srch->addCondition('pship_company', '=', $shipSetArr['pship_company']);
                    $srch->addCondition('pship_duration', '=', $shipSetArr['pship_duration']);
                    $srch->addCondition('pship_charges', '=', $shipSetArr['pship_charges']);
                    $srch->addCondition('pship_additional_charges', '=', $shipSetArr['pship_additional_charges']);
                   
                    $rs = $srch->getResultSet();
                    $result = FatApp::getDb()->fetchAll($rs);
                    if(empty($result)){
                        $this->db->insertFromArray('tbl_product_shipping_rates', $shipSetArr);
                        $ProductshippingRateId = $this->db->getInsertId();
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }


    public function exportPolicyPoints($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = PolicyPoint::getSearchObject($langId, false, false);
        $srch->addMultipleFields(array('ppoint_id','ppoint_identifier','ppoint_type','ppoint_display_order','ppoint_active','ppoint_deleted','ppoint_title'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('ppoint_active', '=', applicationConstants::ACTIVE);
        }
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getPolicyPointsColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        $usePolicyPointId = false;
        if ($this->settings['CONF_USE_POLICY_POINT_ID']) {
            $usePolicyPointId = true;
        }

        $policyPointTypeArr = PolicyPoint::getPolicyPointTypesArr($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                switch ($columnKey) {
                    case 'ppoint_active':
                    case 'ppoint_deleted':
                        if (!$this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                        }
                        break;
                    case 'ppoint_type_identifier':
                        $colValue = isset($policyPointTypeArr[$row['ppoint_type']]) ? $policyPointTypeArr[$row['ppoint_type']] : '';
                        break;
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importPolicyPoints($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        $policyPointTypeArr = PolicyPoint::getPolicyPointTypesArr($langId);
        $policyPointTypeKeys = array_flip($policyPointTypeArr);

        $coloumArr = $this->getPolicyPointsColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $policyPointsArr = $policyPointsLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;
                $errMsg = PolicyPoint::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('ppoint_type' == $columnKey) {
                        $policyPointTypeId = FatUtility::int($colValue);
                        $colValue = $policyPointTypeId = array_key_exists($policyPointTypeId, $policyPointTypeArr) ? $policyPointTypeId : 0;
                    } elseif ('ppoint_type_identifier' == $columnKey) {
                        $columnKey = 'ppoint_type';
                        $colValue = $policyPointTypeId = array_key_exists($colValue, $policyPointTypeKeys) ? $policyPointTypeKeys[$colValue] : 0;
                        if (1 > $colValue) {
                            $errInSheet = $invalid = true;
                        }
                    }

                    if (in_array($columnKey, array( 'ppoint_active', 'ppoint_deleted' ))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = FatUtility::int($colValue);
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array( $rowIndex, ($colIndex + 1), $errMsg ));
                    } else {
                        if ('ppoint_title' == $columnKey) {
                            $policyPointsLangArr[$columnKey] = $colValue;
                        } else {
                            $policyPointsArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($policyPointsArr)) {
                if ($this->settings['CONF_USE_POLICY_POINT_ID']) {
                    $policyData = PolicyPoint::getAttributesById($policyPointsArr['ppoint_id'], array('ppoint_id'));
                } else {
                    $policyData = PolicyPoint::getAttributesByIdentifier($policyPointsArr['ppoint_identifier'], array('ppoint_id'));
                }

                if (!empty($policyData) && $policyData['ppoint_id']) {
                    $policyPointId = $policyData['ppoint_id'];
                    $where = array('smt' => 'ppoint_id = ?', 'vals' => array( $policyPointId ) );
                    $this->db->updateFromArray(PolicyPoint::DB_TBL, $policyPointsArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(PolicyPoint::DB_TBL, $policyPointsArr);
                        $policyPointId = $this->db->getInsertId();
                    }
                }

                if ($policyPointId) {
                    /* Lang Data [*/
                    $langData = array(
                    'ppointlang_ppoint_id'=> $policyPointId,
                    'ppointlang_lang_id'=> $langId,
                    );

                    $langData = array_merge($langData, $policyPointsLangArr);

                    $this->db->insertFromArray(PolicyPoint::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ]*/
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = FatUtility::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportUsers($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null)
    {
        $userObj = new User();
        $srch = $userObj->getUserSearchObj();
        $srch->addOrder('u.user_id', 'DESC');
        $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::NO);
        $srch->doNotCalculateRecords();
        $srch->addFld(array('user_is_buyer', 'user_is_supplier','user_is_advertiser','user_is_affiliate', 'user_registered_initially_for'));
        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('user_id', '>=', $minId);
            $srch->addCondition('user_id', '<=', $maxId);
        }

        $srch->addOrder('user_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getUsersColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        $userTypeArr = User::getUserTypesArr($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array( 'user_is_buyer', 'user_is_supplier', 'user_is_advertiser', 'user_is_affiliate' )) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if ('urlrewrite_custom' == $columnKey) {
                    $colValue = isset($urlKeywords[ProductCategory::REWRITE_URL_PREFIX.$row['prodcat_id']]) ? $urlKeywords[ProductCategory::REWRITE_URL_PREFIX.$row['prodcat_id']] : '';
                }

                if ('prodcat_parent_identifier' == $columnKey) {
                    $colValue = array_key_exists($row['prodcat_parent'], $categoriesIdentifiers) ? $categoriesIdentifiers[$row['prodcat_parent']] : '';
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function exportTaxCategory($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $taxObj = new Tax();
        $srch = $taxObj->getSearchObject($langId, false);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('taxcat_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('taxcat_deleted', '=', applicationConstants::NO);
        }
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSalesTaxColumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array( 'taxcat_active', 'taxcat_deleted' )) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if ('taxcat_last_updated' == $columnKey) {
                    $colValue = $this->displayDateTime($colValue);
                }

                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }
}
