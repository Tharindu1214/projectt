<?php
trait CustomProducts
{
    public function customProduct()
    {
        if (!$this->isShopActive(UserAuthentication::getLoggedUserId(), 0, true)) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'shop'));
        }

        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Packages'));
        }

        if (!User::canAddCustomProduct()) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'catalog'));
        }

        $frmSearchCustomProduct = $this->getCustomProductSearchForm();
        $this->set("frmSearchCustomProduct", $frmSearchCustomProduct);
        $this->_template->addJs('js/jscolor.js');
        $this->_template->render(true, true);
    }

    public function searchCustomProduct()
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $srch = Product::getSearchObject($this->siteLangId);
        $srch->addCondition('product_seller_id', '=', UserAuthentication::getLoggedUserId());

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('product_model', 'like', '%' . $keyword . '%');
        }

        $srch->addMultipleFields(
            array(    'product_id',
            'product_identifier',
            'product_active',
            'product_approved',
            'product_added_on',
            'product_name')
        );
        $srch->addOrder('product_added_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL', FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1));

        $this->_template->render(false, false);
    }

    public function customProductForm($prodId = 0, $prodCatId = 0)
    {
        if (0 == $prodId && FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && Product::getActiveCount(UserAuthentication::getLoggedUserId()) >= SellerPackages::getAllowedLimit(UserAuthentication::getLoggedUserId(), $this->siteLangId, 'spackage_products_allowed')) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Packages'));
        }

        if (!$this->isShopActive(UserAuthentication::getLoggedUserId(), 0, true)) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'shop'));
        }
        if (!User::canAddCustomProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'customProduct'));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Packages'));
        }
        $prodId = FatUtility::int($prodId);
        $prodCatId = FatUtility::int($prodCatId);

        /* if($prodCatId == 0){
        $product = new Product();
        $records = $product->getProductCategories($prodId);
        if(!empty($records)){
        $prodcatArr = array_column($records, 'prodcat_id');
        $prodCatId = reset($prodcatArr);
        }
        } */

        $this->set('prodId', $prodId);
        $this->set('prodCatId', $prodCatId);
        $this->set('includeEditor', true);
        $this->_template->addJs('js/jscolor.js');
        $this->_template->addJs('js/multi-list.js');
        $this->_template->addCss('css/multi-list.css');
        $this->_template->addJs('js/jquery.tablednd.js');
        $this->_template->addJs('js/slick.js');
        $this->_template->addCss('css/slick.css');
        $this->_template->render(true, true);
    }

    /* public function customProductFormOld( $product_id=0){
    if(!$this->isShopActive(UserAuthentication::getLoggedUserId(),0,true)){
    FatApp::redirectUser(CommonHelper::generateUrl('Seller','shop'));
    }
    if( !User::canAddCustomProduct() ){
    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
    FatApp::redirectUser(CommonHelper::generateUrl('Seller','customProduct'));

    }
    if( !UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId()) ){
    Message::addInfo( Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId) );
    FatApp::redirectUser(CommonHelper::generateUrl('Seller','Packages'));
    }

    // Validate product belongs to current logged seller[
    if($product_id){
    $productRow = Product::getAttributesById( $product_id, array('product_seller_id','product_brand_id') );
    if( $productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()){
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('Seller','customProduct'));
    }
    }
    // ]


    $this->set('customActiveTab','GENERAL');


    $this->set('languages', Language::getAllNames());
    $this->set('activeTab', 'GENERAL');
    $this->set('productId',$product_id);
    $this->set('includeEditor', true);
    $this->_template->addJs('js/jscolor.js');
    $this->_template->addJs('js/multi-list.js');
    $this->_template->addCss('css/multi-list.css');
    $this->_template->render(true,false);
    } */

    public function customProductGeneralForm($product_id = 0, $prodcat_id = 0)
    {
        $product_id = FatUtility::int($product_id);
        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */
        $optionsData = array();
        $customProductFrm = $this->getCustomProductForm('CUSTOM_PRODUCT', $prodcat_id);
        if ($product_id > 0) {
            /* $productAttrToGet =  array(
            Product::DB_TBL_PREFIX.'id',
            Product::DB_TBL_PREFIX.'identifier',
            Product::DB_TBL_PREFIX.'type',
            Product::DB_TBL_PREFIX.'model',
            Product::DB_TBL_PREFIX.'min_selling_price',
            Product::DB_TBL_PREFIX.'active',
            Product::DB_TBL_PREFIX.'approved',
            Product::DB_TBL_PREFIX.'featured',
            Product::DB_TBL_PREFIX.'length',
            Product::DB_TBL_PREFIX.'width',
            Product::DB_TBL_PREFIX.'height',
            Product::DB_TBL_PREFIX.'dimension_unit',
            Product::DB_TBL_PREFIX.'weight',
            Product::DB_TBL_PREFIX.'weight_unit',
            Product::DB_TBL_PREFIX.'ship_country',
            Product::DB_TBL_PREFIX.'ship_free',
            Product::DB_TBL_PREFIX.'cod_enabled',
            Product::DB_TBL_PREFIX.'upc',
            Product::DB_TBL_PREFIX.'brand_id',
            );
            $row_data = Product::getAttributesById($product_id, $productAttrToGet); */


            $srch = Product::getSearchObject($this->siteLangId);
            $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tp.product_brand_id = brand.brand_id', 'brand');
            $srch->joinTable(Brand::DB_LANG_TBL, 'LEFT OUTER JOIN', 'brandlang_brand_id = brand.brand_id AND brandlang_lang_id = ' . $this->siteLangId);
            $srch->addMultipleFields(array('product_id', 'product_identifier', 'product_type', 'product_model', 'product_min_selling_price', 'product_active', 'product_approved', 'product_featured', 'product_length','product_width', 'product_height', 'product_dimension_unit', 'product_weight', 'product_weight_unit', 'product_ship_country', 'product_ship_free', 'product_cod_enabled', 'product_upc', 'product_brand_id', 'IFNULL(brand_name,brand_identifier) as brand_name'));
            $srch->addCondition('product_id', '=', $product_id);
            $rs = $srch->getResultSet();
            $row_data = FatApp::getDb()->fetch($rs);

            $taxData = Tax::getTaxCatByProductId($product_id, UserAuthentication::getLoggedUserId(), $this->siteLangId, array('ptt_taxcat_id'));
            /* CommonHelper::printArray($row_data); die; */
            if (!empty($taxData)) {
                $row_data = array_merge($row_data, $taxData);
            }
            $shippingDetails = Product::getProductShippingDetails($product_id, $this->siteLangId, UserAuthentication::getLoggedUserId());

            if (isset($shippingDetails['ps_from_country_id']) && $shippingDetails['ps_from_country_id']) {
                $row_data['shipping_country'] = Countries::getCountryById($shippingDetails['ps_from_country_id'], $this->siteLangId, 'country_name');
                $row_data['ps_from_country_id'] = $shippingDetails['ps_from_country_id'];
                $row_data['ps_free'] = $shippingDetails['ps_free'];
            }
            /* CommonHelper::printArray($row_data); die; */
            $customProductFrm->fill($row_data);
        }
        $alertToShow = $this->CheckProductLinkWithCatBrand($product_id);
        $this->set('alertToShow', $alertToShow);
        $this->set('customProductFrm', $customProductFrm);
        $this->set('product_id', $product_id);
        $this->set('prodcat_id', $prodcat_id);
        $this->set('activeTab', 'GENERAL');
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setupCustomProduct()
    {
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }

        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $frm = $this->getCustomProductForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
       
        $productShiping = FatApp::getPostedData('product_shipping');
       
        $productTaxCategory  = $post['ptt_taxcat_id'];

        if (false === $post) {
            FatUtility::dieWithError(current($frm->getValidationErrors()));
        }
        $product_id = FatUtility::int($post['product_id']);
        $product_brand_id = FatUtility::int($post['product_brand_id']);

        if (1 > $product_brand_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Brand', $this->siteLangId));
        }
        $productType =  $post['product_type'];

        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        unset($post['product_id']);
        unset($post['ptt_taxcat_id']);
        unset($post['product_shipping']);
        $prodObj = new Product($product_id);
        $data_to_be_save = $post;

        if ($post['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $data_to_be_save['product_length'] = 0;
            $data_to_be_save['product_width'] = 0;
            $data_to_be_save['product_height'] = 0;
            $data_to_be_save['product_dimension_unit'] = 0;
            $data_to_be_save['product_weight'] = 0;
            $data_to_be_save['product_weight_unit'] = 0;
            $data_to_be_save['product_cod_enabled'] = applicationConstants::NO;
        }

        if (!$product_id) {
            $data_to_be_save['product_added_on'] = 'mysql_func_now()';
            $PRODUCT_REQUIRE_ADMIN_APPROVAL = FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1);
            $data_to_be_save['product_approved'] = ($PRODUCT_REQUIRE_ADMIN_APPROVAL == 1) ? 0 : 1 ;
            $data_to_be_save['product_added_by_admin_id'] = 0;
            $data_to_be_save['product_seller_id'] = UserAuthentication::getLoggedUserId();
        } else {
            $data_to_be_save['ps_free'] = (isset($post['ps_free']) && $post['ps_free'] >0)?$post['ps_free']:0;
        }

        $prodObj->assignValues($data_to_be_save, true);

        if (!$prodObj->save()) {
            FatUtility::dieWithError($prodObj->getError());
        }

        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $product_id = $prodObj->getMainTableRecordId();

        if ($product_id) {
            /*Save Prodcut tax category [*/
            $prodTaxData = array(
            'ptt_product_id'=>$product_id,
            'ptt_taxcat_id'=>$productTaxCategory,
            'ptt_seller_user_id'=>UserAuthentication::getLoggedUserId(),
            );
            $taxObj = new Tax();
            if (!$taxObj->addUpdateProductTaxCat($prodTaxData)) {
                Message::addErrorMessage($taxObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }

            /*]*/

            $data_to_be_save = $post;
            $data_to_be_save['ps_product_id'] = $product_id;


            /*Save Prodcut Shipping  [*/
            if (!$this->addUpdateProductSellerShipping($product_id, $data_to_be_save)) {
                Message::addErrorMessage(FatApp::getDb()->getError());
                FatUtility::dieWithError(Message::getHtml());
            }

            /*]*/

            /*Save Prodcut Shipping Details [*/

            if (!$this->addUpdateProductShippingRates($product_id, $productShiping)) {
                Message::addErrorMessage($taxObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }

            /*]*/
        }

        if ($data_to_be_save['preq_prodcat_id'] > 0) {
            if (!$prodObj->addUpdateProductCategory($product_id, $data_to_be_save['preq_prodcat_id'])) {
                Message::addErrorMessage(Labels::getLabel($prodObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        Product::updateMinPrices($product_id);

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->set('product_id', $product_id);
        $this->set('product_type', $productType);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customProductLangForm($product_id, $lang_id)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $product_id = FatUtility::int($product_id);
        $lang_id = FatUtility::int($lang_id);

        if ($product_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($product_id);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }

        $customProductLangFrm = $this->getCustomProductLangForm($lang_id);
        $prodObj = new Product($product_id);
        $customProductLangData = $prodObj->getAttributesByLangId($lang_id, $product_id);
        $customProductLangData['product_id'] = $product_id;
        if ($customProductLangData) {
            $customProductLangFrm->fill($customProductLangData);
        }
        $alertToShow = $this->CheckProductLinkWithCatBrand($product_id);
        $this->set('alertToShow', $alertToShow);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->set('activeTab', 'GENERAL');
        $this->set('languages', Language::getAllNames());
        $this->set('product_id', $product_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('product_lang_id', $lang_id);
        $this->set('prodcat_id', $prodCatId);
        $this->set('customProductLangFrm', $customProductLangFrm);
        $this->_template->render(false, false);
    }

    public function setupCustomProductLang()
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        $product_id = FatUtility::int($post['product_id']);

        if ($product_id == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */
        $frm = $this->getCustomProductLangForm($lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        unset($post['product_id']);
        unset($post['lang_id']);
        $data_to_update = array(
        'productlang_product_id'    =>    $product_id,
        'productlang_lang_id'        =>    $lang_id,
        'product_name'                =>    $post['product_name'],
        /* 'product_short_description' =>    $post['product_short_description'], */
        'product_description'        =>    $post['product_description'],
        'product_youtube_video'        =>    $post['product_youtube_video'],
        );

        $prodObj = new Product($product_id);
        if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row=Product::getAttributesByLangId($langId, $product_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->set('product_id', $product_id);
        $this->set('lang_id', $newTabLangId);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function customProductOptions($product_id)
    {
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($product_id);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }

        //$optionsData = Product::getProductOptions( $product_id, $this->siteLangId);

        $customProductOptionFrm = $this->getCustomProductOptionForm();
        $alertToShow = $this->CheckProductLinkWithCatBrand($product_id);
        $this->set('alertToShow', $alertToShow);
        $this->set('customProductOptionFrm', $customProductOptionFrm);
        $this->set('product_id', $product_id);
        $this->set('prodcat_id', $prodCatId);
        $this->set('activeTab', 'OPTIONS');
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function productOptions($productId = 0)
    {
        $productId = FatUtility::int($productId);
        if (!$productId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }


        /* Validate product belongs to current logged seller[ */
        if (!UserPrivilege::canSellerEditCustomProduct($productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $productOptions = Product::getProductOptions($productId, $this->siteLangId);
        $this->set('productOptions', $productOptions);
        $this->set('productId', $productId);
        $this->_template->render(false, false);
    }

    private function getCustomProductOptionForm()
    {
        $frm = new Form('frmProductOptions', array('id'=>'frmProductOptions'));
        $frm->addHtml('', 'product_name', '');
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Option_Groups', $this->siteLangId), 'option_name');
        $fld1->htmlAfterField= '<div class=""><small><a href="javascript:void(0);" onClick="optionForm(0);">'.Labels::getLabel('LBL_Add_New_Option', $this->siteLangId).'</a></small></div><div class="row"><div class="col-md-12"><ul class="list--vertical" id="product_options_list"></ul></div>';

        /* $fld1->htmlAfterField='</div><div class="col-md-4"><small> <a class="" href="javascript:void(0);" onClick="optionForm(0);">' .Labels::getLabel('LBL_Add_New_Option',$this->siteLangId).'</a></small></div>'; */
        // $fld1->attachField($fld2);
        $frm->addHiddenField('', 'product_id', '', array('id'=>'product_id'));

        return $frm;
    }

    public function updateProductOption()
    {
        $post = FatApp::getPostedData();
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);

        if (!$product_id || !$option_id) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }

        if (!UserPrivilege::canSellerEditCustomProduct($product_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodObj = new Product();
        if (!$prodObj->addUpdateProductOption($product_id, $option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        Product::updateMinPrices($product_id);
    }

    public function checkOptionLinkedToInventory()
    {
        $post = FatApp::getPostedData();
        $productId = FatUtility::int($post['product_id']);
        $optionId = FatUtility::int($post['option_id']);

        if (!$productId || !$optionId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Validate product belongs to current logged seller[ */
        if ($productId) {
            $productRow = Product::getAttributesById($productId, array('product_seller_id'));
            if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        /* Validate option is binded with seller product [ */
        $optionSrch = SellerProduct::getSearchObject();
        $optionSrch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'sp.selprod_id = spo.selprodoption_selprod_id', 'spo');
        $optionSrch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'LEFT OUTER JOIN', 'sp.selprod_product_id = po.prodoption_product_id', 'po');
        $optionSrch->addMultipleFields(array('selprodoption_option_id'));
        $optionSrch->addCondition('selprod_product_id', '=', $productId);
        $optionSrch->addCondition('prodoption_option_id', '=', $optionId);
        $optionSrch->addCondition('selprodoption_option_id', '=', $optionId);

        $rs = $optionSrch->getResultSet();
        $db = FatApp::getDb();
        $row = $db->fetch($rs);
        if (!empty($row)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_This_option_is_linked_with_the_inventory,_so_can_not_be_deleted', $this->siteLangId));
            return;
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Option_can_be_deleted", $this->siteLangId));
        /* ] */
    }

    public function removeProductOption()
    {
        $post = FatApp::getPostedData();
        $productId = FatUtility::int($post['product_id']);
        $optionId = FatUtility::int($post['option_id']);

        if (!$productId || !$optionId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        /* Validate product belongs to current logged seller[ */
        if ($productId) {
            $productRow = Product::getAttributesById($productId, array('product_seller_id'));
            if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        /* Get Linked Products [ */
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addCondition('tspo.selprodoption_option_id', '=', $optionId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addFld(array('selprod_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            // Message::addErrorMessage();
            FatUtility::dieJsonError(Labels::getLabel('LBL_Option_is_linked_with_seller_inventory', $this->siteLangId));
        }
        /* ] */

        $prodObj = new Product();
        if (!$prodObj->removeProductOption($productId, $optionId)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        Product::updateMinPrices($productId);
        FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Option_removed_successfully.', $this->siteLangId));
    }

    /* public function customProductImages( $product_id ){
    if( !User::canAddCustomProduct() ){
    FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
    }
    $product_id = FatUtility::int($product_id);
    if(!$product_id){
    FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request',$this->siteLangId));
    }

    if( !$productRow = Product::getAttributesById( $product_id, array('product_seller_id'))){
    FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request',$this->siteLangId));
    }


    if( $productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()){
    FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
    }


    $imagesFrm = $this->getCustomProductImagesForm();
    $arr = array('product_id' => $product_id);
    $imagesFrm->fill($arr);

    $product_images = AttachedFile::getMultipleAttachments( AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id );
    $this->set('product_images', $product_images );
    $this->set( 'imagesFrm', $imagesFrm );
    $this->set( 'siteLangId', $this->siteLangId );
    $this->_template->render(false,false);
    } */

    public function customProductImages($product_id)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $product_id = FatUtility::int($product_id);

        if (!$product_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (!$productRow = Product::getAttributesById($product_id, array('product_seller_id'))) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }


        if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }


        $imagesFrm = $this->getImagesFrm($product_id, $this->siteLangId);

        $imgTypesArr = $this->getSeparateImageOptions($product_id, $this->siteLangId);

        $this->set('product_id', $product_id);
        $this->set('imagesFrm', $imagesFrm);
        $this->_template->render(false, false);
    }

    public function images($product_id, $option_id=0, $lang_id=0)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $product_id = FatUtility::int($product_id);

        if (!$product_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (!$productRow = Product::getAttributesById($product_id, array('product_seller_id'))) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }


        if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $product_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $option_id, $lang_id, false, 0, 0, true);
        /* CommonHelper::printArray($product_images);die; */
        $imgTypesArr = $this->getSeparateImageOptions($product_id, $this->siteLangId);

        $this->set('images', $product_images);
        $this->set('product_id', $product_id);
        $this->set('imgTypesArr', $imgTypesArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    /*public function setupCustomProductImages(){
    if( !User::canAddCustomProduct() ){
    FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
    }
    $post = FatApp::getPostedData();
    $product_id = FatUtility::int($post['product_id']);


    $productRow = Product::getAttributesById( $product_id, array('product_seller_id'));
    if( $productRow['product_seller_id'] != UserAuthentication::getLoggedUserId() ){
    FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
    }

    if (!is_uploaded_file($_FILES['prod_image']['tmp_name'])) {
    Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file.',$this->siteLangId));
    FatUtility::dieJsonError(Message::getHtml());
    }

    $fileHandlerObj = new AttachedFile();
    if(!$res = $fileHandlerObj->saveImage($_FILES['prod_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, '',  $_FILES['prod_image']['name'], -1, $unique_record = false)
    ){
    Message::addErrorMessage($fileHandlerObj->getError());
    FatUtility::dieJsonError( Message::getHtml() );
    }

    Message::addMessage(Labels::getLabel("MSG_Product_Image_Uploaded_Successfully!",$this->siteLangId));
    FatUtility::dieJsonSuccess(Message::getHtml());
    }*/

    public function setupCustomProductImages()
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        // $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        $lang_id = FatUtility::int($post['lang_id']);


        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        /* Check allowed images [ */
        $productImagesArr = array();
        $sellerId = UserAuthentication::getLoggedUserId();

        $subscription = false;
        $allowed_images =-1;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $allowed_images = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $sellerId, array('ossubs_images_allowed'));
            $subscription = true;
        }

        /* Current Product option Values[ */
        $options = Product::getProductOptions($product_id, $this->siteLangId, true, 1);
        $productSelectedOptionValues = array();
        $productGroupImages= array();

        $productOptionId = ($option_id == 0) ? -1 : $option_id ;

        $images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $productOptionId, $this->siteLangId, true, '', $allowed_images);
        if ($images) {
            $productImagesArr += $images;
        }


        if ($productImagesArr) {
            foreach ($productImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        /* ] */

        if ($allowed_images > 0 && count($productImagesArr) >= $allowed_images) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Cant_upload_more_than_allowed_images", $this->siteLangId));
        }

        /* ] */


        if (!is_uploaded_file($_FILES['prod_image']['tmp_name'])) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Please_select_a_file", $this->siteLangId));
        }
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['prod_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $option_id, $_FILES['prod_image']['name'], -1, $unique_record = false, $lang_id)
        ) {
            FatUtility::dieJsonError($fileHandlerObj->getError());
        }
        FatApp::getDb()->updateFromArray('tbl_products', array('product_image_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?','vals' => array($product_id)));

        FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Image_Uploaded_Successfully", $this->siteLangId));
    }

    public function deleteCustomProductImage($product_id, $image_id)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $product_id = FatUtility :: int($product_id);
        $image_id = FatUtility :: int($image_id);
        if (!$image_id || !$product_id) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request!", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* Validate product belongs to current logged seller[ */
        $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
        if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        /* ] */

        $productObj = new Product();
        if (!$productObj->deleteProductImage($product_id, $image_id)) {
            FatUtility::dieJsonError($productObj->getError());
        }
        FatApp::getDb()->updateFromArray('tbl_products', array('product_image_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?','vals' => array($product_id)));

        FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Image_removed_successfully.', $this->siteLangId));
    }

    public function setCustomProductImagesOrder()
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $productObj = new Product();
        $post = FatApp::getPostedData();
        $product_id = FatUtility :: int($post['product_id']);
        /* Validate product belongs to current logged seller[ */
        $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
        if ($productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        /* ] */
        $imageIds = explode('-', $post['ids']);
        $count = 1;
        foreach ($imageIds as $row) {
            $order[$count]=$row;
            $count++;
        }

        if (!$productObj->updateProdImagesOrder($product_id, $order)) {
            Message::addErrorMessage($productObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_Ordered_Successfully!", $this->siteLangId));
    }

    /* Custom product Specifications */

    public function customProductSpecifications($product_id)
    {
        $productSpecifications = Product::getProductSpecifications($product_id, $this->siteLangId);

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($product_id);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }

        $alertToShow = $this->CheckProductLinkWithCatBrand($product_id);
        $this->set('alertToShow', $alertToShow);
        $this->set('prodSpec', $productSpecifications);
        $this->set('product_id', $product_id);
        $this->set('prodcat_id', $prodCatId);
        $languages = Language::getAllNames();
        $this->set('languages', $languages);
        $this->set('activeTab', 'SPECIFICATIONS');
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function productSpecifications($productId)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $productSpecifications = Product::getProductSpecifications($productId, $this->siteLangId);

        $languages = Language::getAllNames();
        $this->set('prodSpec', $productSpecifications);
        $this->set('productId', $productId);
        $this->set('languages', $languages);
        $this->set('siteLangId', $this->siteLangId);

        $this->_template->render(false, false);
    }

    public function prodSpecForm($productId = 0)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $prodSpecId = FatUtility :: int($post['prodSpecId']);
        $data  = array();
        $languages = Language::getAllNames();
        if ($prodSpecId>0) {
            if (!UserPrivilege::canEditSellerProductSpecification($post['prodSpecId'], $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $prodSpecObj = new ProdSpecification();
            $specResult = $prodSpecObj->getProdSpecification($prodSpecId, $productId);

            foreach ($specResult as $key=>$value) {
                foreach ($languages as $langId=>$langName) {
                    if ($value['prodspeclang_lang_id']!=$langId) {
                        continue;
                    }
                    $data['prod_spec_name['.$langId.']'] = $value['prodspec_name'];
                    $data['prod_spec_value['.$langId.']'] = $value['prodspec_value'];
                }
            }
        }

        $this->set('languages', $languages);
        $specFrm =  $this->getProductSpecForm();

        $data['product_id'] = $productId;
        $data['prodspec_id'] = $prodSpecId;
        $specFrm->fill($data);
        $this->set('data', $data);
        $this->set('prodSpecFrm', $specFrm);
        $this->_template->render(false, false, 'seller/prod-spec-form.php', false, false);
    }

    public function setupProductSpecifications()
    {
        $post = FatApp::getPostedData();
        $productId = FatUtility::int($post['product_id']);
        $prodspec_id = FatUtility::int($post['prodspec_id']);
        if (!UserPrivilege::canSellerEditCustomProduct($productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        if ($prodspec_id>0) {
            if (!UserPrivilege::canEditSellerProductSpecification($prodspec_id, $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $prodSpecObj = new ProdSpecification($prodspec_id);

        $languages = Language::getAllNames();
        foreach ($languages as $langId=>$langName) {
            $data_to_be_save['prodspec_product_id'] = $productId;
            if ($prodspec_id<1) {
                $prodSpecObj->assignValues($data_to_be_save);

                if (!$prodSpecObj->save()) {
                    Message::addErrorMessage(Labels::getLabel($prodSpecObj->getError(), $this->siteLangId));
                    FatUtility::dieWithError(Message::getHtml());
                };
                $prodSpecObj = new ProdSpecification($prodSpecObj->getMainTableRecordId());
            }

            $data_to_save_lang['prodspec_name'] = $post['prod_spec_name'][$langId];
            $data_to_save_lang['prodspec_value'] = $post['prod_spec_value'][$langId];
            $data_to_save_lang['prodspeclang_lang_id'] = $langId;
            $data['prodspeclang_prodspec_id'] = $prodspec_id;
            if (!$prodSpecObj->updateLangData($langId, $data_to_save_lang)) {
                Message::addErrorMessage(Labels::getLabel($ProdSpecObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        // Message::addMessage(Labels::getLabel('MSG_Product_Specification_Added_Successfully',$this->siteLangId));
        FatUtility::dieJsonSuccess(array("productId"=>$productId,"prodSpecId"=>$prodspec_id));
    }

    public function deleteProdSpec($productId= 0)
    {
        $post = FatApp::getPostedData();

        $prodspec_id = FatUtility::int($post['prodSpecId']);
        if (!UserPrivilege::canSellerEditCustomProduct($productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($prodspec_id>0) {
            if (!UserPrivilege::canEditSellerProductSpecification($prodspec_id, $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $prodSpecObj = new ProdSpecification($prodspec_id);
        if (!$prodSpecObj->deleteRecord(true)) {
            Message::addErrorMessage(Labels::getLabel($ProdSpecObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Specification_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getShippingTab()
    {
        $shipping_rates = array();
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $product_id =$post['product_id'];
        //$shipping_rates = Products::getProductShippingRates();
        $this->set('siteLangId', $this->siteLangId);
        $shipping_rates = array();
        $shipping_rates = Product::getProductShippingRates($product_id, $this->siteLangId, 0, $userId);

        $this->set('siteLangId', $this->siteLangId);
        $this->set('product_id', $product_id);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false);
    }

    public function countries_autocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $srch = Cities::getSearchObject(true, $this->siteLangId);
        $srch->addOrder('city_name');

        $srch->addMultipleFields(array('city_id, city_name, city_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('city_name', 'LIKE', '%' . $post['keyword']. '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $cities = $db->fetchAll($rs, 'city_id');

        /* if (isset($post['includeEverywhere']) && $post['includeEverywhere']) {
            $everyWhereArr =  array('city_name'=>Labels::getLabel('LBL_Everywhere_Else', $this->siteLangId));
            $cities[]= $everyWhereArr;
        } */

        $json = array();
        foreach ($cities as $key => $city) {
            $json[] = array(
            'id' => $city['city_id'],
            'name'      => strip_tags(html_entity_decode(isset($city['city_name'])?$city['city_name']:$city['city_identifier'], ENT_QUOTES, 'UTF-8')),
            );
        }
        die(json_encode($json));
    }

    public function shippingMethodsAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $srch = ShippingApi::getSearchObject(true, $this->siteLangId);
        $srch->addOrder('shippingapi_name');

        $srch->addMultipleFields(array('shippingapi_id, shippingapi_name'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('shippingapi_name', 'LIKE', '%' . $post['keyword']. '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $shippingMethods = $db->fetchAll($rs, 'shippingapi_id');


        $json = array();
        foreach ($shippingMethods as $key => $sMethod) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($sMethod['shippingapi_name'], ENT_QUOTES, 'UTF-8')),

            );
        }
        die(json_encode($json));
    }

    public function shippingCompanyAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $srch = ShippingCompanies::getSearchObject(true, $this->siteLangId);
        $srch->addOrder('scompany_name');

        $srch->addMultipleFields(array('scompany_id, scompany_name'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('scompany_name', 'LIKE', '%' . $post['keyword']. '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $shippingCompanies = $db->fetchAll($rs, 'scompany_id');


        $json = array();
        foreach ($shippingCompanies as $key => $sCompany) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($sCompany['scompany_name'], ENT_QUOTES, 'UTF-8')),

            );
        }
        die(json_encode($json));
    }

    public function shippingMethodDurationAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $srch = ShippingDurations::getSearchObject($this->siteLangId, true);
        $srch->addOrder('sduration_name');

        $srch->addMultipleFields(array('sduration_id, sduration_name','sduration_from','sduration_to','sduration_days_or_weeks'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('sduration_id', 'LIKE', '%' . $post['keyword']. '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $shipDurations = $db->fetchAll($rs, 'sduration_id');

        $json = array();
        foreach ($shipDurations as $key => $shipDuration) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($shipDuration['sduration_name'], ENT_QUOTES, 'UTF-8')),
            'duraion'      => ShippingDurations::getShippingDurationTitle($shipDuration, $this->siteLangId),

            );
        }
        die(json_encode($json));
    }
    /*  ---  Seller Product Links  --- - */
    public function customProductLinks($productId =0)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();


        $lang_id = $this->siteLangId;
        $frm = $this->getLinksForm($productId);

        $srch = Product::getSearchObject($lang_id);
        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tp.product_brand_id = brand.brand_id', 'brand');

        $srch->joinTable(Brand::DB_LANG_TBL, 'LEFT OUTER JOIN', 'brandlang_brand_id = brand.brand_id AND brandlang_lang_id = ' . $lang_id);

        $srch->addMultipleFields(array('product_id', 'brand_status', 'brand_deleted', 'product_brand_id', 'IFNULL(product_name,product_identifier) as product_name', 'IFNULL(brand_name,brand_identifier) as brand_name'));
        $srch->addCondition('product_id', '=', $productId);
        $srch->addCondition('brand.brand_active', '=', applicationConstants::YES);
        $srch->addCondition('brand.brand_deleted', '=', applicationConstants::NO);
        $rs = $srch->getResultSet();
        $product_row = FatApp::getDb()->fetch($rs);
        $prodObj = new Product();
        $product_tags = $prodObj->getProductTags($productId, $lang_id);

        $alertToShow = $this->CheckProductLinkWithCatBrand($productId);
        $this->set('alertToShow', $alertToShow);

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($productId);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }


        $frm->fill($product_row);

        $this->set('product_name', $product_row['product_name']);
        $this->set('product_tags', $product_tags);
        $this->set('frmLinks', $frm);
        $this->set('product_id', $productId);
        $this->set('prodcat_id', $prodCatId);
        $this->set('activeTab', 'LINKS');
        $this->_template->render(false, false);
    }

    public function setupProductLinks()
    {
        $post = FatApp::getPostedData();
        if (!UserPrivilege::canSellerEditCustomProduct($post['product_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_tags = (isset($post['product_tag']))?$post['product_tag']:array();
        $frm = $this->getLinksForm($post['product_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = $post['product_id'];
        unset($post['product_id']);

        if ($product_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        //$product_categories = $post['product_category'];
        //$product_categories = explode(',',$product_categories);

        $prodObj = new Product($product_id);

        $data_to_be_save['product_brand_id'] = FatUtility::int($post['product_brand_id']);
        $prodObj->assignValues($data_to_be_save);

        if (!$prodObj->save()) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* saving of product categories[
        if( !$prodObj->addUpdateProductCategories($product_id, $product_categories ) ){
        Message::addErrorMessage( $prodObj->getError() );
        FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */
        /* saving of product Tag[ */


        if (!$prodObj->addUpdateProductTags($product_id, $product_tags)) {
            var_dump($prodObj->getError());
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        Tag::updateProductTagString($product_id);
        /* ] */

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function tagsAutoComplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();

        $srch = Tag::getSearchObject();
        $srch->addOrder('tag_identifier');
        $srch->joinTable(
            Tag::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->siteLangId
        );
        $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('tag_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('tag_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }


        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = $db->fetchAll($rs, 'tag_id');
        $json = array();
        foreach ($options as $key => $option) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($option['tag_name'], ENT_QUOTES, 'UTF-8')),
            'tag_identifier'    => strip_tags(html_entity_decode($option['tag_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function addtagsForm($tag_id=0)
    {
        $tag_id=FatUtility::int($tag_id);
        $frm = $this->getTagsForm($tag_id);

        if (0 < $tag_id) {
            $data = Tag::getAttributesById($tag_id, array('tag_id','tag_identifier'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('tag_id', $tag_id);
        $this->set('frmTag', $frm);
        $this->set('langId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function tagsLangForm($tag_id=0, $lang_id=0)
    {
        $tag_id = FatUtility::int($tag_id);
        $lang_id = FatUtility::int($lang_id);

        if ($tag_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $tagLangFrm = $this->getTagLangForm($tag_id, $lang_id);

        $langData = Tag::getAttributesByLangId($lang_id, $tag_id);

        if ($langData) {
            $tagLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('tag_id', $tag_id);
        $this->set('tag_lang_id', $lang_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('tagLangFrm', $tagLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function setupTag()
    {
        $frm = $this->getTagsForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $tag_id = $post['tag_id'];
        if ($tag_id>0) {
            if (!UserPrivilege::canSellerUpdateTag($tag_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        unset($post['tag_id']);
        $post['tag_user_id'] =  UserAuthentication::getLoggedUserId();
        $record = new Tag($tag_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }



        $newTabLangId = 0;
        if ($tag_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Tag::getAttributesByLangId($langId, $tag_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $tag_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        /* update product tags association and tag string in products lang table[ */
        Tag::updateTagStrings($tag_id);
        /* ] */

        $this->set('msg', Labels::getLabel("MSG_Tag_Setup_Successful", $this->siteLangId));
        $this->set('tagId', $tag_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function tagLangSetup()
    {
        $post=FatApp::getPostedData();

        $tag_id = $post['tag_id'];
        $lang_id = $post['lang_id'];

        if ($tag_id == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!UserPrivilege::canSellerUpdateTag($tag_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getTagLangForm($tag_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['tag_id']);
        unset($post['lang_id']);
        $data = array(
        'taglang_lang_id'=>$lang_id,
        'taglang_tag_id'=>$tag_id,
        'tag_name'=>$post['tag_name'],
        );

        $tagObj=new Tag($tag_id);
        if (!$tagObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($tagObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row=Tag::getAttributesByLangId($langId, $tag_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        /* update product tags association and tag string in products lang table[ */
        Tag::updateTagStrings($tag_id);
        /* ] */

        $this->set('msg', Labels::getLabel("MSG_Tag_Setup_Successful", $this->siteLangId));
        $this->set('tagId', $tag_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /*...................................Product Shipping Rates..................................*/
    public function removeProductShippingRates($product_id, $userId =0)
    {
        $db = FatApp::getDb();
        $product_id = FatUtility::int($product_id);
        $userId = FatUtility::int($userId);


        if (!$db->deleteRecords(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, array('smt'=> ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX.'prod_id = ? and   '.ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX.'user_id = ?','vals' => array($product_id,$userId) ))) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }

    private function addUpdateProductShippingRates($product_id, $data)
    {
        $this->removeProductShippingRates($product_id, UserAuthentication::getLoggedUserId());
        if (!empty($data) && count($data)>0) {
            foreach ($data as $key=>$val):
                if (isset($val["city_id"]) && $val["company_id"] > 0 && $val["processing_time_id"] > 0) {
                    $prodShipData = array(
                    'pship_prod_id'=>$product_id,
                    'pship_user_id'=>UserAuthentication::getLoggedUserId(),
                    'pship_city'=>(isset($val["city_id"]) && FatUtility::int($val["city_id"]))?FatUtility::int($val["city_id"]):0,
                    'pship_company'=>(isset($val["company_id"]) && FatUtility::int($val["company_id"]))?FatUtility::int($val["company_id"]):0,
                    'pship_duration'=>(isset($val["processing_time_id"]) && FatUtility::int($val["processing_time_id"]))?FatUtility::int($val["processing_time_id"]):0,
                    'pship_charges'=> (1 > FatUtility::float($val["cost"]) ? 0 : FatUtility::float($val["cost"])),
                    'pship_additional_charges'=>FatUtility::float($val["additional_cost"]),
                    );
                    if (isset($val["pship_id"])) {
                        $prodShipData['pship_id']  = FatUtility::int($val["pship_id"]);
                    }
                    if (!FatApp::getDb()->insertFromArray(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, $prodShipData, false, array(), $prodShipData)) {
                        $this->error = FatApp::getDb()->getError();
                        return false;
                    }
                }
            endforeach;
        }
        return true;
    }

    public function addUpdateProductSellerShipping($product_id, $data_to_be_save)
    {
        $productSellerShiping = array();
        $productSellerShiping['ps_product_id']= $product_id;
        $productSellerShiping['ps_user_id']= UserAuthentication::getLoggedUserId();
        $productSellerShiping['ps_from_country_id']= $data_to_be_save['ps_from_country_id'];
        $productSellerShiping['ps_free']=  $data_to_be_save['ps_free'];
        if (!FatApp::getDb()->insertFromArray(PRODUCT::DB_TBL_PRODUCT_SHIPPING, $productSellerShiping, false, array(), $productSellerShiping)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    /* ------Brand Request ------*/

    public function addBrandReqForm($brandReqId = 0)
    {
        $frm = $this->getBrandForm();
        $this->set('languages', Language::getAllNames());
        if (0 < $brandReqId) {
            $data = Brand::getAttributesById($brandReqId, array('brand_id','brand_identifier'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }
        $this->set('frmBrandReq', $frm);
        $this->set('brandReqId', $brandReqId);
        $this->set('langId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function setupBrandReq()
    {

        /* $frm = $this->getBrandForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData()); */

        $post = FatApp::getPostedData();

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brandReqId = $post['brand_id'];
        if ($brandReqId>0) {
            if (!UserPrivilege::canSellerUpdateBrandRequest($brandReqId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        unset($post['brandReqId']);

        if (!FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) {
            $post['brand_active']  = applicationConstants::ACTIVE;
            $post['brand_status']  = applicationConstants::YES;
        }

        $post['brand_seller_id'] =  UserAuthentication::getLoggedUserId();
        $record = new Brand($brandReqId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brandReqId = $record->getMainTableRecordId();

        $notificationData = array(
        'notification_record_type' => Notification::TYPE_BRAND,
        'notification_record_id' => $brandReqId,
        'notification_user_id' => UserAuthentication::getLoggedUserId(true),
        'notification_label_key' => Notification::BRAND_REQUEST_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($brandReqId ==0) {
            $brandReqId = $record->getMainTableRecordId();
            $brandData = Brand::getAttributesById($brandReqId);
            $email = new EmailHandler();
            if (!$email->sendBrandRequestAdminNotification($this->siteLangId, $brandData)) {
            }
        }

        $newTabLangId = 0;
        if ($brandReqId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Brand::getAttributesByLangId($langId, $brandReqId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $brandReqId = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }


        $this->set('msg', Labels::getLabel("MSG_Brand_Setup_Successful", $this->siteLangId));
        $this->set('brandReqId', $brandReqId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function brandReqLangSetup()
    {
        $post=FatApp::getPostedData();

        $brandReqId = $post['brand_id'];
        $lang_id = $post['lang_id'];

        if ($brandReqId == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $brandDetails = Brand::getAttributesById($brandReqId);

        if ($brandDetails['brand_seller_id']!= UserAuthentication::getLoggedUserId()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getBrandReqLangForm($brandReqId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        unset($post['brand_id']);
        unset($post['lang_id']);
        $data = array(
        'brandlang_lang_id'=>$lang_id,
        'brandlang_brand_id'=>$brandReqId,
        'brand_name'=>$post['brand_name'],
        );

        $brandObj=new Brand($brandReqId);
        if (!$brandObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($brandObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId=0;
        $languages=Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row=Brand::getAttributesByLangId($langId, $brandReqId)) {
                $newTabLangId = $langId;
                break;
            }
        }
        if ($newTabLangId == 0 && !$this->isMediaUploaded($brandReqId)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', Labels::getLabel('MSG_Brand_Request_Sent_Successful', $this->siteLangId));
        $this->set('brandReqId', $brandReqId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function brandReqLangForm($brandReqId=0, $lang_id=0)
    {
        $brandReqId = FatUtility::int($brandReqId);
        $lang_id = FatUtility::int($lang_id);

        if ($brandReqId==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $brandDetails = Brand::getAttributesById($brandReqId);

        if ($brandDetails['brand_seller_id']!= UserAuthentication::getLoggedUserId()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        $brandReqLangFrm = $this->getBrandReqLangForm($brandReqId, $lang_id);

        $langData = Brand::getAttributesByLangId($lang_id, $brandReqId);

        if ($langData) {
            $brandReqLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('brandReqId', $brandReqId);
        $this->set('brandReqLangId', $lang_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('brandReqLangFrm', $brandReqLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function brandMediaForm($brand_id = 0)
    {
        $brandDetails = Brand::getAttributesById($brand_id);

        if ($brandDetails['brand_seller_id']!= UserAuthentication::getLoggedUserId()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brand_id = FatUtility::int($brand_id);

        $brandMediaFrm = $this->getMediaForm($brand_id);
        $brandImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BRAND_LOGO, $brand_id, 0, -1);
        $bannerTypeArr = applicationConstants::bannerTypeArr();

        $this->set('languages', Language::getAllNames());
        $this->set('brandReqId', $brand_id);
        $this->set('brandReqMediaFrm', $brandMediaFrm);
        $this->set('brandImages', $brandImages);
        $this->set('bannerTypeArr', $bannerTypeArr);
        $this->_template->render(false, false);
    }

    public function uploadLogo()
    {
        $brand_id = FatApp::getPostedData('brand_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if (!$brand_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        $fileHandlerObj->deleteFile($fileHandlerObj::FILETYPE_BRAND_LOGO, $brand_id, 0, 0, $lang_id);

        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $fileHandlerObj::FILETYPE_BRAND_LOGO,
            $brand_id,
            0,
            $_FILES['file']['name'],
            -1,
            $unique_record = false,
            $lang_id
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('brandId', $brand_id);
        $this->set('file', $_FILES['file']['name']);
        $this->set('msg', $_FILES['file']['name']. Labels::getLabel('MSG_File_Uploaded_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getMediaForm($brand_id)
    {
        $frm = new Form('frmBrandMedia');
        $languagesAssocArr = Language::getAllNames();
        $frm->addHTML('', 'brand_logo_heading', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'brand_lang_id', array( 0 => Labels::getLabel('LBL_Universal', $this->siteLangId) ) + $languagesAssocArr, '', array(), '');
        $frm->addButton(
            Labels::getLabel('Lbl_Logo', $this->siteLangId),
            'logo',
            Labels::getLabel('LBL_Upload_Logo', $this->siteLangId),
            array('class'=>'uploadFile-Js','id'=>'logo','data-file_type'=>AttachedFile::FILETYPE_BRAND_LOGO,'data-brand_id' => $brand_id )
        );

        $frm->addHtml('', 'brand_logo_display_div', '');

        return $frm;
    }

    public function removeBrandLogo($brand_id = 0, $lang_id = 0)
    {
        $brand_id = FatUtility::int($brand_id);
        $lang_id = FatUtility::int($lang_id);
        if (!$brand_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brandDetails = Brand::getAttributesById($brand_id);

        if ($brandDetails['brand_seller_id']!= UserAuthentication::getLoggedUserId()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_BRAND_LOGO, $brand_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getCustomProductSearchForm()
    {
        $frm = new Form('frmSearchCustomProduct');
        $frm->addTextBox('', 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getBrandForm()
    {
        $frm = new Form('frmBrandReq', array('id'=>'frmBrandReq'));
        $frm->addRequiredField(Labels::getlabel('LBL_Brand_Identifier', $this->siteLangId), 'brand_identifier')->setUnique(Brand::DB_TBL, Brand::DB_TBL_PREFIX.'identifier', Brand::DB_TBL_PREFIX.'id', Brand::DB_TBL_PREFIX.'id', Brand::DB_TBL_PREFIX.'identifier');
        $frm->addHiddenField('', 'brand_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    private function getBrandReqLangForm($brandReqId=0, $lang_id=0)
    {
        $frm = new Form('frmBrandReqLang', array('id'=>'frmBrandReqLang'));
        $frm->addHiddenField('', 'brand_id', $brandReqId);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Brand_Name', $this->siteLangId), 'brand_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Update", $this->siteLangId));
        return $frm;
    }

    private function getTagsForm($tag_id=0)
    {
        $tag_id=FatUtility::int($tag_id);

        $frm = new Form('frmTag', array('id'=>'frmTag'));
        $frm->addHiddenField('', 'tag_id', $tag_id);
        $frm->addRequiredField(Labels::getLabel("LBL_Tag_Identifier", $this->siteLangId), 'tag_identifier');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    private function isMediaUploaded($brandId)
    {
        if ($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_LOGO, $brandId, 0)) {
            return true;
        }
        return false;
    }

    private function getTagLangForm($tag_id=0, $lang_id=0)
    {
        $frm = new Form('frmTagLang', array('id'=>'frmTagLang'));
        $frm->addHiddenField('', 'tag_id', $tag_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Tag_Name', $this->siteLangId), 'tag_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Update", $this->siteLangId));
        return $frm;
    }

    private function getLinksForm($product_id = 0)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($product_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = new Form('frmLinks', array('id'=>'frmLinks'));
        $frm->addTextBox(Labels::getLabel('LBL_Product_Name', $this->siteLangId), 'product_name');
        /* $brndFld = $frm->addTextBox(Labels::getLabel('LBL_Brand/Manufacturer',$this->siteLangId),'brand_name');
        $brndFld->htmlAfterField= '<div class="col-md-6"><small><a href="javascript:void(0);" onClick="addBrandReqForm(0);">'.Labels::getLabel('LBL_Brand_Not_Found?_Click_here_to_',$this->siteLangId).Labels::getLabel('LBL_Request_New_Brand',$this->siteLangId).'</a></small></div>'; */

        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Category', $this->siteLangId), 'choose_links');
        $fld2 = $frm->addHtml('', 'addNewOptionLink', '</a><div id="product_links_list" class="col-xs-10" ></div>');
        $fld1->attachField($fld2);
        $frm->addHiddenField('', 'product_brand_id');

        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Tag', $this->siteLangId), 'tag_name');
        $fld1->htmlAfterField= '<div class="col-md-12"><small><a href="javascript:void(0);" onClick="addTagForm(0);">'.Labels::getLabel('LBL_Tag_Not_Found?_Click_here_to_', $this->siteLangId).' '.Labels::getLabel('LBL_Add_New_Tag', $this->siteLangId).'</a></small></div><div class="row"><div class="col-md-12"><ul class="list--vertical" id="product-tag"></ul></div>';

        //$frm->addHtml('','product-tag','');

        $frm->addHiddenField('', 'product_id', $product_id);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    private function getProductSpecForm()
    {
        $frm = new Form('frmProductSpec');
        $languages = Language::getAllNames();
        foreach ($languages as $langId=>$langName) {
            $frm->addRequiredField(Labels::getLabel('LBL_Specification_Name', $this->siteLangId), 'prod_spec_name['.$langId.']');
            $frm->addRequiredField(Labels::getLabel('LBL_Specification_Value', $this->siteLangId), 'prod_spec_value['.$langId.']');
        }
        $frm->addHiddenField('', 'product_id');
        $frm->addHiddenField('', 'prodspec_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));

        return $frm;
    }

    private function getImagesFrm($product_id = 0, $lang_id = 0)
    {
        $imgTypesArr = $this->getSeparateImageOptions($product_id, $lang_id);
        $frm = new Form('imageFrm', array('id' => 'imageFrm'));
        $frm->addSelectBox(Labels::getLabel('LBL_Image_File_Type', $this->siteLangId), 'option_id', $imgTypesArr, 0, array('class'=>'option'), '');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'lang_id', array( 0 => Labels::getLabel('LBL_All_Languages', $this->siteLangId) ) + $languagesAssocArr, '', array('class'=>'language'), '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s)', $this->siteLangId), 'prod_image', array('id' => 'prod_image', 'multiple' => 'multiple'));
        $fldImg->htmlBeforeField='<div class="filefield"><span class="filename"></span>';
        $fldImg->htmlAfterField='<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $this->siteLangId).'</label></div><small>'.Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_500_x_500._You_can_upload_multiple_photos_from_here', $this->siteLangId).'</small>';

        $frm->addHiddenField('', 'product_id', $product_id);
        return $frm;
    }

    private function getSeparateImageOptions($product_id, $lang_id)
    {
        $imgTypesArr = array( 0 => Labels::getLabel('LBL_For_All_Options', $this->siteLangId) );
        $productOptions = Product::getProductOptions($product_id, $lang_id, true, 1);

        foreach ($productOptions as $val) {
            if (!empty($val['optionValues'])) {
                foreach ($val['optionValues'] as $k => $v) {
                    $option_name = (isset($val['option_name']) && $val['option_name']) ? $val['option_name'] : $val['option_identifier'];
                    //$imgTypesArr[$k] = $v .' ( '. $option_name .' )';
                    $imgTypesArr[$k] = $v;
                }
            }
        }
        return $imgTypesArr;
    }

    private function getCustomProductImagesForm()
    {
        $frm = new Form('frmCustomProductImage');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s):', $this->siteLangId), 'prod_image', array('id' => 'prod_image', 'multiple' => 'multiple'));
        $fldImg->htmlBeforeField='<div class="filefield"><span class="filename"></span>';
        $fldImg->htmlAfterField='<label class="filelabel">' . Labels::getLabel('LBL_Browse_File', $this->siteLangId).'</label></div><br/><small class="text--small">'.Labels::getLabel('LBL_You_can_upload_multiple_photos_from_here', $this->siteLangId).'</small>';
        $frm->addHiddenField('', 'product_id');
        return $frm;
    }

    private function getCustomProductLangForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmCustomProductLang');
        $frm->addHiddenField('', 'product_id')->requirements()->setRequired();
        ;
        $frm->addHiddenField('', 'lang_id', $langId);
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $langId), 'product_name');
        /* $frm->addTextArea( Labels::getLabel('LBL_Short_Description', $langId),'product_short_description');         */
        $frm->addTextBox(Labels::getLabel('LBL_YouTube_Video', $langId), 'product_youtube_video');
        $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $langId), 'product_description');
        $fld->htmlBeforeField = '<div class="editor-bar">';
        $fld->htmlAfterField = '</div>';

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public function CheckProductLinkWithCatBrand($productId)
    {
        $alertToShow = false;
        if ($productId) {
            $productRow = Product::getAttributesById($productId, array('product_brand_id'));
            $prodObj = new Product();
            $prodCategories = $prodObj->getProductCategories($productId);
            if (!$prodCategories || $productRow['product_brand_id'] == 0) {
                $alertToShow = true;
            }
            $this->set('alertToShow', $alertToShow);
        }
        return $alertToShow;
    }
}
