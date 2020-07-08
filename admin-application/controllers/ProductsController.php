<?php
class ProductsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewProducts($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditProducts($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $data = FatApp::getPostedData();
        $srchFrm = $this->getSearchForm();
        if ($data) {
            $data['product_id'] = $data['id'];
            unset($data['id']);
            $srchFrm->fill($data);
        }
        $this->objPrivilege->canViewProducts();
        $this->set("frmSearch", $srchFrm);
        $this->set("includeEditor", true);

        $this->_template->addJs('js/jscolor.js');
        $this->_template->addJs('js/import-export.js');
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewProducts();
        $db = FatApp::getDb();
        $srchFrm = $this->getSearchForm();

        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = Product::getSearchObject($this->adminLangId);
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT OUTER JOIN', 'product_attrgrp_id = attrgrp_id', 'attrgrp');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'product_seller_id = user_id', 'tu');
        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_model', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
        }

        $active = FatApp::getPostedData('active', FatUtility::VAR_INT, -1);
        if ($active > -1) {
            $srch->addCondition('product_active', '=', $active);
        }

        $product_approved = FatApp::getPostedData('product_approved', FatUtility::VAR_INT, -1);
        if ($product_approved > -1) {
            $srch->addCondition('product_approved', '=', $product_approved);
        }

        $product_seller_id = FatApp::getPostedData('product_seller_id', FatUtility::VAR_INT, 0);

        if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
            $is_custom_or_catalog = FatApp::getPostedData('is_custom_or_catalog', FatUtility::VAR_INT, -1);
            if ($is_custom_or_catalog > -1) {
                if ($is_custom_or_catalog > 0) {
                    if (0 < $product_seller_id) {
                        $srch->addCondition('product_seller_id', '=', $product_seller_id);
                    } else {
                        $srch->addCondition('product_seller_id', '>', 0);
                    }
                } else {
                    $srch->addCondition('product_seller_id', '=', 0);
                }
            } else {
                if (0 < $product_seller_id) {
                    $srch->addCondition('product_seller_id', '=', $product_seller_id);
                }
            }
        } else {
            if (0 < $product_seller_id) {
                $srch->addCondition('product_seller_id', '=', $product_seller_id);
            }
        }

        $product_attrgrp_id = FatApp::getPostedData('product_attrgrp_id', FatUtility::VAR_INT, -1);
        if ($product_attrgrp_id  > -1) {
            $srch->addCondition('product_attrgrp_id', '=', $product_attrgrp_id);
        }

        $prodcat_id = FatApp::getPostedData('prodcat_id', FatUtility::VAR_INT, -1);
        if ($prodcat_id  > -1) {
            $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'product_id = ptc_product_id', 'ptcat');
            $srch->addCondition('ptcat.ptc_prodcat_id', '=', $prodcat_id);
        }

        $product_type = FatApp::getPostedData('product_type', FatUtility::VAR_INT, 0);
        if ($product_type  > 0) {
            $srch->addCondition('product_type', '=', $product_type);
        }

        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from)) {
            $srch->addCondition('tp.product_added_on', '>=', $date_from. ' 00:00:00');
        }

        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to)) {
            $srch->addCondition('tp.product_added_on', '<=', $date_to. ' 23:59:59');
        }

        $product_id = FatApp::getPostedData('product_id', FatUtility::VAR_INT, '');
        if (!empty($product_id)) {
            $srch->addCondition('product_id', '=', $product_id);
        }

        $srch->addMultipleFields(
            array('product_id', 'product_attrgrp_id',
            'product_identifier', 'product_approved', 'product_active', 'product_seller_id', 'product_added_on',
            'product_name','attrgrp_name','user_name')
        );

        $srch->addOrder('product_added_on', 'DESC');

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();

        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));
        $this->set('canEdit', $this->objPrivilege->canEditProducts(AdminAuthentication::getLoggedAdminId(), true));
        $this->_template->render(false, false);
    }

    public function productAttributeGroupForm()
    {
        $this->set('productAttributeGroupForm', $this->getProductAttributeGroupForm());
        $this->_template->render(false, false);
    }

    private function getProductAttributeGroupForm()
    {
        $groupsArr = AttributeGroup::getAllNames();
        $frm = new Form('frmProductAttributeGroup');
        $frm->addSelectBox(Labels::getLabel('LBL_Seller_Attribute_Group', $this->adminLangId), 'attrgrp_id', $groupsArr, '', array(), '-None-');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Next', $this->adminLangId));
        return $frm;
    }

    public function form($product_id = 0, $attrgrp_id = 0)
    {
        $this->objPrivilege->canEditProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id > 0) {
            $attrgrp_id = Product::getAttributesById($product_id, 'product_attrgrp_id');
            if ($attrgrp_id === false) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request._Product_Not_Found', $this->adminLangId));

                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $product_added_by_admin = 1;
        $totalProducts= 0;
        $productFrm = $this->getForm($attrgrp_id);
        if ($product_id > 0) {
            $row_data = Product::getAttributesById($product_id);

            $taxData = array();
            $taxObj = Tax::getTaxCatObjByProductId($product_id, $this->adminLangId);
            if ($row_data['product_seller_id']>0) {
                $taxObj->addCondition('ptt_seller_user_id', '=', $row_data['product_seller_id']);
            } else {
                $taxObj->addCondition('ptt_seller_user_id', '=', 0);
            }
            //$taxObj->addCondition('ptt_seller_user_id','=',0);
            $taxObj->addMultipleFields(array('ptt_taxcat_id'));
            $taxObj->doNotCalculateRecords();
            $taxObj->setPageSize(1);
            $taxObj->addOrder('ptt_seller_user_id', 'ASC');
            $rs = $taxObj->getResultSet();
            $taxData = FatApp::getDb()->fetch($rs);

            if (!empty($taxData)) {
                $row_data = array_merge($row_data, $taxData);
            }

            if ($row_data['product_seller_id']>0) {
                $user_shop_name=User::getUserShopName($row_data['product_seller_id']);

                $row_data['selprod_user_shop_name']=$user_shop_name['user_name'].' - '.$user_shop_name['shop_identifier'];
            } else {
                $row_data['selprod_user_shop_name'] = 'Admin';
            }
            $shippingDetails = Product::getProductShippingDetails($product_id, $this->adminLangId, $row_data['product_seller_id']);

            if (isset($shippingDetails['ps_from_country_id']) && $shippingDetails['ps_from_country_id']) {
                $row_data['shipping_country'] = Countries::getCountryById($shippingDetails['ps_from_country_id'], $this->adminLangId, 'country_name');
                $row_data['ps_from_country_id'] = $shippingDetails['ps_from_country_id'];
                $row_data['ps_free'] = $shippingDetails['ps_free'];
            }
            //var_dump($row_data);
            $productFrm->fill($row_data);
            $product_added_by_admin_arr=Product::getAttributesById($product_id, array('product_added_by_admin_id'));
            $product_added_by_admin=$product_added_by_admin_arr['product_added_by_admin_id'];


            //Get productCount  in catalog

            $srch = SellerProduct::getSearchObject();
            $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
            $srch->addCondition('selprod_deleted', '=', 0);
            $srch->addCondition('selprod_product_id', '=', $product_id);
            $srch->addFld('selprod_id');
            $rs = $srch->getResultSet();
            $totalProducts = $srch->recordCount();
        }

        $this->set('product_added_by_admin', $product_added_by_admin);


        $this->set('totalProducts', $totalProducts);
        $this->set('product_id', $product_id);
        $this->set('languages', Language::getAllNames());
        $this->set('productFrm', $productFrm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        $frm = $this->getForm($post['product_attrgrp_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $productShiping = FatApp::getPostedData('product_shipping');
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $product_id = FatUtility::int($post['product_id']);
        unset($post['product_id']);

        $prodObj = new Product($product_id);
        if ($product_id) {
            unset($post['product_attrgrp_id']);
        }
        $data_to_be_save = $post;
        $userId = $post['product_seller_id'];
        if ($post['ps_free']=='') {
            $data_to_be_save['ps_free'] = 0;
        }

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
            $data_to_be_save['product_approved'] = 1;
            $data_to_be_save['product_added_by_admin_id'] = applicationConstants::YES;
        }

        $prodObj->assignValues($data_to_be_save, true);

        if (!$prodObj->save()) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $product_id = $prodObj->getMainTableRecordId();

        /* save Group attributes data[ */
        $num_data_update_arr['prodnumattr_product_id'] = $product_id;
        for ($i = 1; $i <= AttrGroupAttribute::MAX_NUMERIC_ATTRIBUTE_ROWS; $i++) {
            $num_data_update_arr['prodnumattr_num_'.$i] = isset($post['prodnumattr_num_'.$i]) ? $post['prodnumattr_num_'.$i] : '';
        }

        if (!$prodObj->addUpdateNumericAttributes($num_data_update_arr)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* ] */

        /*Save Prodcut tax category [*/

        $prodTaxData = array(
        'ptt_product_id'=>$product_id,
        'ptt_taxcat_id'=>$post['ptt_taxcat_id'],
        );
        $prodTaxData['ptt_seller_user_id']= $userId;

        $taxObj = new Tax();
        if ($userId) {
            $taxObj->removeTaxSetByAdmin($product_id);
        }

        if (!$taxObj->addUpdateProductTaxCat($prodTaxData)) {
            Message::addErrorMessage($taxObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /*]*/

        $data_to_be_save = $post;
        $data_to_be_save['ps_product_id'] = $product_id;

        /*Save Product Shipping  [*/
        if (!$this->addUpdateProductSellerShipping($product_id, $data_to_be_save, $userId)) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /*]*/

        /*Save Product Shipping Details [*/
        if (!empty($productShiping) && 0 < count($productShiping)) {
            if (!$this->addUpdateProductShippingRates($product_id, $productShiping, $userId)) {
                Message::addErrorMessage(FatApp::getDb()->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /*]*/
        Product::updateMinPrices($product_id);
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('product_id', $product_id);
        $this->set('lang_id', $this->adminLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($product_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canEditProducts();

        $product_id = FatUtility::int($product_id);
        $lang_id = FatUtility::int($lang_id);

        if ($product_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $productLangFrm = $this->getLangForm($product_id, $lang_id);

        $prodObj = new Product($product_id);
        $productLangData = $prodObj->getAttributesByLangId($lang_id, $product_id);

        if ($productLangData) {
            $productLangFrm->fill($productLangData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('product_id', $product_id);
        $this->set('product_lang_id', $lang_id);
        $this->set('productLangFrm', $productLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        $product_id = FatUtility::int($post['product_id']);
        $lang_id = $post['lang_id'];

        if ($product_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($product_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['product_id']);
        unset($post['lang_id']);
        $data_to_update = array(
        'productlang_product_id'    =>    $product_id,
        'productlang_lang_id'        =>    $lang_id,
        'product_name'                =>    $post['product_name'],
        /* 'product_short_description' =>$post['product_short_description'], */
        'product_description'        =>    $post['product_description'],
        'product_youtube_video'        =>    $post['product_youtube_video'],
        );

        $prodObj = new Product($product_id);
        if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save attributes data[ */
        /* $text_data_update['prodtxtattr_product_id'] = $product_id;
        $text_data_update['prodtxtattr_lang_id'] = $lang_id;

        for( $i = 1; $i <= AttrGroupAttribute::MAX_TEXTUAL_ATTRIBUTE_ROWS; $i++ ){
        $text_data_update['prodtxtattr_text_'.$i] = isset($post['prodtxtattr_text_'.$i]) ? $post['prodtxtattr_text_'.$i] : '';
        }
        if( !$prodObj->addUpdateTextualAttributes( $text_data_update ) ){
        Message::addErrorMessage($prodObj->getError());
        FatUtility::dieWithError(Message::getHtml());
        } */
        /* ] */

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row=Product::getAttributesByLangId($langId, $product_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('product_id', $product_id);
        $this->set('lang_id', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function imagesForm($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        if (!$row = Product::getAttributesById($product_id)) {
            FatUtility::dieWithError($this->str_no_record);
        }
        $imagesFrm = $this->getImagesFrm($product_id, $this->adminLangId);
        $this->set('product_id', $product_id);
        $this->set('imagesFrm', $imagesFrm);
        $this->_template->render(false, false);
    }

    public function images($product_id, $option_id=0, $lang_id=0)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            Message::addErrorMessage($this->str_invalid_request);
        }

        if (!$row = Product::getAttributesById($product_id)) {
            Message::addErrorMessage($this->str_no_record);
        }
        $product_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $option_id, $lang_id, false, 0, 0, true);
        $imgTypesArr = $this->getSeparateImageOptions($product_id, $this->adminLangId);

        $this->set('images', $product_images);
        $this->set('product_id', $product_id);
        $this->set('imgTypesArr', $imgTypesArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setImageOrder()
    {
        $this->objPrivilege->canEditProducts();
        $productObj = new Product();
        $post = FatApp::getPostedData();
        $product_id = FatUtility::int($post['product_id']);
        $imageIds=explode('-', $post['ids']);
        $count=1;
        foreach ($imageIds as $row) {
            $order[$count]=$row;
            $count++;
        }
        if (!$productObj->updateProdImagesOrder($product_id, $order)) {
            Message::addErrorMessage($productObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set("msg", Labels::getLabel('LBL_Ordered_Successfully', $this->adminLangId));
        //FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Ordered_Successfully',$this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadProductImages()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        $lang_id = FatUtility::int($post['lang_id']);

        if (!is_uploaded_file($_FILES['prod_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['prod_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $option_id, $_FILES['prod_image']['name'], -1, $unique_record = false, $lang_id)
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatApp::getDb()->updateFromArray('tbl_products', array('product_image_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?','vals' => array($product_id)));

        //Message::addMessage(Labels::getLabel('LBL_Image_Uploaded_Successfully',$this->adminLangId));
        $this->set("msg", Labels::getLabel('LBL_Image_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
        //FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function deleteImage($product_id, $image_id)
    {
        $this->objPrivilege->canEditProducts();
        $product_id = FatUtility :: int($product_id);
        $image_id = FatUtility :: int($image_id);
        if (!$image_id || !$product_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $productObj = new Product();
        if (!$productObj->deleteProductImage($product_id, $image_id)) {
            Message::addErrorMessage($productObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatApp::getDb()->updateFromArray('tbl_products', array('product_image_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?','vals' => array($product_id)));

        //Message::addMessage(Labels::getLabel('LBL_Image_Removed_Successfully',$this->adminLangId));
        $this->set("msg", Labels::getLabel('LBL_Image_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
        //FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function linksForm($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $frm = $this->getLinksForm($product_id);

        $srch = Product::getSearchObject($this->adminLangId);

        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tp.product_brand_id = brand.brand_id', 'brand');

        $srch->joinTable(Brand::DB_LANG_TBL, 'LEFT OUTER JOIN', 'brandlang_brand_id = brand.brand_id AND brandlang_lang_id = ' . $this->adminLangId);

        $srch->addMultipleFields(array('product_id', 'product_brand_id', 'IFNULL(product_name,product_identifier) as product_name', 'IFNULL(brand_name,brand_identifier) as brand_name','IFNULL(brand.brand_active,1) AS brand_active','IFNULL(brand.brand_deleted,0) AS brand_deleted'));
        $srch->addCondition('product_id', '=', $product_id);
        $srch->addHaving('brand_active', '=', applicationConstants::YES);
        $srch->addHaving('brand_deleted', '=', applicationConstants::NO);
        $rs = $srch->getResultSet();
        $product_row = FatApp::getDb()->fetch($rs);
        $frm->fill($product_row);
        $this->set('product_name', $product_row['product_name']);
        $this->set('productId', $product_id);
        $this->set('frmLinks', $frm);
        $this->_template->render(false, false);
    }

    public function setupProductLinks()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        $frm = $this->getLinksForm($post['product_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = $post['product_id'];
        unset($post['product_id']);

        if ($product_id <= 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        //$product_categories = $post['product_category'];
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
        Product::updateMinPrices($product_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function optionsForm($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frm = $this->getOptionsForm();

        $srch = Product::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('product_id', 'IFNULL(product_name,product_identifier) as product_name'));
        $srch->addCondition('product_id', '=', $product_id);
        $rs = $srch->getResultSet();
        $product_row = FatApp::getDb()->fetch($rs);

        $product_row['product_name'] = '<h3>'.$product_row['product_name'].'</h3>';
        $frm->fill($product_row);

        $productOptions = Product::getProductOptions($product_id, $this->adminLangId);
        $this->set('productOptions', $productOptions);
        $this->set('product_id', $product_row['product_id']);
        $this->set('product_data', $product_row);
        $this->set('frmOptions', $frm);
        $this->_template->render(false, false);
    }

    public function productOptions($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $productOptions = Product::getProductOptions($product_id, $this->adminLangId);
        $this->set('productOptions', $productOptions);
        $this->set('product_id', $product_id);
        $this->_template->render(false, false);
    }

    public function updateProductOption()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product();
        if (!$prodObj->addUpdateProductOption($product_id, $option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        Product::updateMinPrices($product_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProductOption()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Get Linked Products [ */
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
        $srch->addCondition('selprod_product_id', '=', $product_id);
        $srch->addCondition('tspo.selprodoption_option_id', '=', $option_id);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addFld(array('selprod_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Option_is_linked_with_seller_inventory', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $prodObj = new Product();
        if (!$prodObj->removeProductOption($product_id, $option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Option_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function tagsForm($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frm = $this->getTagsForm();

        $srch = Product::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('product_id', 'IFNULL(product_name,product_identifier) as product_name'));
        $srch->addCondition('product_id', '=', $product_id);
        $rs = $srch->getResultSet();
        $product_row = FatApp::getDb()->fetch($rs);

        $product_row['product_name'] = '<h3>'.$product_row['product_name'].'</h3>';
        $frm->fill($product_row);
        $this->set('product_id', $product_row['product_id']);
        $this->set('product_data', $product_row);
        $this->set('frmTags', $frm);
        $this->_template->render(false, false);
    }

    public function productTags($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $productTags = Product::getProductTags($product_id, $this->adminLangId);

        $this->set('productTags', $productTags);
        $this->set('product_id', $product_id);
        $this->_template->render(false, false);
    }

    public function updateProductTag()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $tag_id = FatUtility::int($post['tag_id']);
        if (!$product_id || !$tag_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product();
        if (!$prodObj->addUpdateProductTag($product_id, $tag_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        Tag::updateProductTagString($product_id);

        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProductTag()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $tag_id = FatUtility::int($post['tag_id']);
        if (!$product_id || !$tag_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product();
        if (!$prodObj->removeProductTag($product_id, $tag_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        Tag::updateProductTagString($product_id);

        $this->set('msg', Labels::getLabel('LBL_Tag_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoComplete()
    {
        $this->objPrivilege->canViewProducts();

        $srch = Product::getSearchObject($this->adminLangId);

        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize(FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10));
        /* $srch->addMultipleFields(array('product_id','IFNULL(product_name,product_identifier) as product_name')); */
        $srch->addMultipleFields(array('product_id', 'product_name', 'product_identifier' ));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs, 'product_id');

        $json = array();
        foreach ($products as $key => $product) {
            $product['product_name'] = empty($product['product_name']) ? $product['product_identifier'] : $product['product_name'];
            $json[] = array(
            'id'     => $key,
            'name'  => strip_tags(html_entity_decode($product['product_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getLinksForm($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $prodObj = new Product();
        $product_categories = $prodObj->getProductCategories($product_id);
        $selectedCats= array();
        if ($product_categories) {
            foreach ($product_categories as $cat) {
                $selectedCats[] = $cat['prodcat_id'];
            }
        }
        $frm = new Form('frmLinks', array('id'=>'frmLinks'));
        $frm->addTextBox(Labels::getLabel('LBL_Product_Name', $this->adminLangId), 'product_name');
        $frm->addTextBox(Labels::getLabel('LBL_Brand/Menufacturer', $this->adminLangId), 'brand_name');
        $prodCatObj = new ProductCategory();
        //$arr_options = $prodCatObj->getProdCatTreeStructure( 0, $this->adminLangId );
        //$frm->addCheckBoxes(Labels::getLabel('LBL_Category',$this->adminLangId),'product_category',$arr_options, $selectedCats);
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Choose_Category', $this->adminLangId), 'choose_links');
        //$fld2 = $frm->addHtml('','addNewOptionLink','</a><div id="product_links_list" class="col-xs-10" ></div>');
        //$fld1->attachField($fld2);
        $frm->addHiddenField('', 'product_brand_id');
        $frm->addHiddenField('', 'product_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getOptionsForm()
    {
        $this->objPrivilege->canViewProducts();
        $frm = new Form('frmOptions', array('id'=>'frmOptions'));
        $frm->addHtml('', 'product_name', '');
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Option_Groups', $this->adminLangId), 'option_name');
        $fld2 = $frm->addHtml('', 'addNewOptionLink', '<small><a href="javascript:void(0);" onClick="optionForm(0);">'.Labels::getLabel('LBL_Option_Not_Found?_Click_here_to', $this->adminLangId).' '.Labels::getLabel('LBL_Add_New_Option', $this->adminLangId).'</a></small>');
        $fld1->attachField($fld2);
        $frm->addHiddenField('', 'product_id', '', array('id'=>'product_id'));

        return $frm;
    }

    private function getTagsForm()
    {
        $this->objPrivilege->canViewProducts();
        $frm = new Form('frmTags', array('id'=>'frmTags'));
        $frm->addHtml('', 'product_name', '');
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Tag', $this->adminLangId), 'tag_name');
        $fld2 = $frm->addHtml('', 'addNewTagLink', '<small><a href="javascript:void(0);" onClick="addTagForm(0);">'.''.Labels::getLabel('LBL_Tag_Not_Found?_Click_here_to', $this->adminLangId).' '.Labels::getLabel('LBL_Add_New_Tag', $this->adminLangId).'</a></small>');
        $fld1->attachField($fld2);
        $frm->addHiddenField('', 'product_id', '', array('id'=>'product_id'));

        return $frm;
    }

    private function getSeparateImageOptions($product_id, $lang_id)
    {
        $imgTypesArr = array( 0 => Labels::getLabel('LBL_For_All_Options', $this->adminLangId) );
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

    private function getImagesFrm($product_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewProducts();
        $imgTypesArr = $this->getSeparateImageOptions($product_id, $lang_id);
        $frm = new Form('imageFrm', array('id' => 'imageFrm'));
        $frm->addSelectBox(Labels::getLabel('LBL_Image_File_Type', $this->adminLangId), 'option_id', $imgTypesArr, 0, array(), '');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', array( 0 => Labels::getLabel('LBL_All_Languages', $this->adminLangId) ) + $languagesAssocArr, '', array(), '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s):', $this->adminLangId), 'prod_image', array('id' => 'prod_image', 'multiple' => 'multiple'));
        $fldImg->htmlBeforeField='<span class="filename"></span>';
        $fldImg->htmlAfterField='<br/><small>'.Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_500_x_500._You_can_upload_multiple_photos_from_here.', $this->adminLangId).'</small>';
        $frm->addHiddenField('', 'product_id', $product_id);
        return $frm;
    }

    public function countries_autocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $srch = Countries::getSearchObject(true, $this->adminLangId);
        $srch->addOrder('country_name');

        $srch->addMultipleFields(array('country_id, country_name'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('country_name', 'LIKE', '%' . $post['keyword']. '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $countries = $db->fetchAll($rs, 'country_id');
        if (isset($post['includeEverywhere']) && $post['includeEverywhere']) {
            $everyWhereArr =  array('country_id'=>'-1','country_name'=>Labels::getLabel('LBL_Everywhere_Else', $this->adminLangId));
            $countries[]= $everyWhereArr;
        }

        $json = array();
        foreach ($countries as $key => $country) {
            $json[] = array(
            'id' => $country['country_id'],
            'name'      => strip_tags(html_entity_decode(isset($country['country_name'])?$country['country_name']:'', ENT_QUOTES, 'UTF-8')),

            );
        }
        die(json_encode($json));
    }

    public function getShippingTab()
    {
        $post = FatApp::getPostedData();
        $product_id =$post['product_id'];
        $userId = 0;
        if ($product_id) {
            $product = Product::getAttributesById($product_id);
            if ($product['product_seller_id']>0) {
                $userId  = $product['product_seller_id'];
            }
        }

        $this->set('adminLangId', $this->adminLangId);
        $shipping_rates = array();
        $shipping_rates = Product::getProductShippingRates($product_id, $this->adminLangId, 0, $userId);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('product_id', $product_id);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false);
    }

    public function shippingMethodsAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $srch = ShippingApi::getSearchObject(true, $this->adminLangId);
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

    public function shippingMethodDurationAutocomplete()
    {
        $pagesize = 10;
        $db  = FatApp::getDb();
        $post = FatApp::getPostedData();
        $srch = ShippingDurations::getSearchObject($this->adminLangId, true);
        $srch->addOrder('sduration_name');

        $srch->addMultipleFields(array('sduration_id, IFNULL(sduration_name, sduration_identifier) as sduration_name','sduration_from','sduration_to','sduration_days_or_weeks'));

        if (!empty($post['keyword'])) {
            $srch->addDirectCondition("(sduration_identifier like " . $db->quoteVariable('%' . $post['keyword'] . '%') . " OR sduration_name like " . $db->quoteVariable('%' . $post['keyword'] . '%') . ")");
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();

        $shipDurations = $db->fetchAll($rs, 'sduration_id');
        $json = array();
        foreach ($shipDurations as $key => $shipDuration) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($shipDuration['sduration_name'], ENT_QUOTES, 'UTF-8')),
            'duraion'      => ShippingDurations::getShippingDurationTitle($shipDuration, $this->adminLangId),

            );
        }
        die(json_encode($json));
    }

    private function getForm($attrgrp_id = 0)
    {
        return $this->getProductCatalogForm($attrgrp_id);
    }

    private function getLangForm($product_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewProducts();
        $lang_id = ($lang_id == 0) ? $this->adminLangId : $lang_id;
        $frm = new Form('frmProductLang', array('id'=>'frmProductLang'));
        $frm->addHiddenField('', 'product_id', $product_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $this->adminLangId), 'product_name');
        /* $frm->addTextArea(Labels::getLabel('LBL_Short_Description',$this->adminLangId),'product_short_description'); */
        $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $this->adminLangId), 'product_description');
        $frm->addTextBox(Labels::getLabel('LBL_YouTube_Video', $this->adminLangId), 'product_youtube_video');
        //$frm->addTextArea('Description','product_description');

        /* code to input values for the comparison attributes[ */
        if ($product_id) {
            $product_row = Product::getAttributesById($product_id, array('product_attrgrp_id'));
            if ($product_row['product_attrgrp_id']) {
                $db = FatApp::getDb();
                $attrGrpAttrObj = new AttrGroupAttribute();
                $srch = $attrGrpAttrObj->getSearchObject();
                $srch->joinTable(AttrGroupAttribute::DB_TBL.'_lang', 'LEFT JOIN', 'lang.attrlang_attr_id = '. AttrGroupAttribute::DB_TBL_PREFIX.'id AND attrlang_lang_id = '.$lang_id, 'lang');
                $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX.'attrgrp_id', '=', $product_row['product_attrgrp_id']);
                $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX.'type', '=', AttrGroupAttribute::ATTRTYPE_TEXT);
                $srch->addOrder(AttrGroupAttribute::DB_TBL_PREFIX.'display_order');
                $srch->addMultipleFields(array('attr_identifier', 'attr_type', 'attr_fld_name', 'attr_name','attr_options','attr_prefix','attr_postfix'));
                $rs = $srch->getResultSet();
                $attributes = $db->fetchAll($rs);
                if ($attributes) {
                    foreach ($attributes as $attr) {
                        $caption = ($attr['attr_name'] != '') ? $attr['attr_name'] : $attr['attr_identifier'];
                        $fld = $frm->addTextArea($caption, $attr['attr_fld_name']);
                        $postfix_hint = Labels::getLabel('LBL_Enter_N.A._if_value_not_required.', $this->adminLangId);
                        $fld->htmlAfterField = $postfix_hint;
                    }
                }
            }
        }
        /* ] */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');

        if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
            $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'is_custom_or_catalog', array( -1 =>Labels::getLabel('LBL_All', $this->adminLangId)) + applicationConstants::getCatalogTypeArr($this->adminLangId), -1, array(), '');
        }

        $frm->addTextBox(Labels::getLabel('LBL_User', $this->adminLangId), 'product_seller', '');

        /* $frm->addSelectBox(Labels::getLabel('LBL_Attribute_Group',$this->adminLangId), 'product_attrgrp_id', array( -1 =>Labels::getLabel('LBL_Does_not_Matter',$this->adminLangId) ) + array( 0 => 'Not in any Group') + AttributeGroup::getAllNames(), '', array(), ''); */
        $prodCatObj = new ProductCategory();
        $arrCategories = $prodCatObj->getCategoriesForSelectBox($this->adminLangId);
        $categories = $prodCatObj->makeAssociativeArray($arrCategories);

        $frm->addSelectBox(Labels::getLabel('LBL_category', $this->adminLangId), 'prodcat_id', array( -1 =>Labels::getLabel('LBL_Does_not_Matter', $this->adminLangId) ) + $categories, '', array(), '');
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Active', $this->adminLangId), 'active', array( -1 =>Labels::getLabel('LBL_Does_not_Matter', $this->adminLangId) ) + $activeInactiveArr, '', array(), '');

        $approveUnApproveArr = Product::getApproveUnApproveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Approval_Status', $this->adminLangId), 'product_approved', array( -1 =>Labels::getLabel('LBL_Does_not_Matter', $this->adminLangId) ) + $approveUnApproveArr, '', array(), '');

        $frm->addSelectBox(Labels::getLabel('LBL_Product_Type', $this->adminLangId), 'product_type', Product::getProductTypes($this->adminLangId), array());

        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'product_id');
        $frm->addHiddenField('', 'product_seller_id');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function sellerCatalog()
    {
        $this->objPrivilege->canViewProducts();
        $srchFrm = $this->getSearchForm();
        $this->set("frmSearch", $srchFrm);
        $this->_template->render();
    }

    /*...................................Product Shipping Rates..................................*/
    public function removeProductShippingRates($product_id, $userId)
    {
        return Product::removeProductShippingRates($product_id, $userId);
    }

    public function addUpdateProductShippingRates($product_id, $data, $userId = 0)
    {
        return Product::addUpdateProductShippingRates($product_id, $data, $userId);
    }

    public function shippingCompanyAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();

        $srch = ShippingCompanies::getSearchObject(true, $this->adminLangId);
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

    /*...................................Custom product Specifications..................................*/
    public function customProductSpecifications($product_id)
    {
        $this->objPrivilege->canEditProducts();
        $hideListBox = false;
        if (0 < $product_id) {
            $productObj = new Product();
            $data = $productObj->getProductSpecifications($product_id, $this->adminLangId);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            if (empty($data)) {
                $hideListBox = true;
            }

            /* CommonHelper::printArray($data); die; */
            /* if(in_array($data['prodspec_id'],Option::ignoreOptionValues())){

            } */
        }


        $this->set('product_id', $product_id);
        $this->set('hideListBox', $hideListBox);
        $languages = Language::getAllNames();
        $this->set('languages', $languages);
        $this->set('activeTab', 'SPECIFICATIONS');
        $this->set('adminLangId', $this->adminLangId);
        $this->_template->render(false, false);
    }

    public function productSpecifications($productId)
    {
        $this->objPrivilege->canEditProducts();
        $productSpecifications = Product::getProductSpecifications($productId, $this->adminLangId);

        $languages = Language::getAllNames();
        $this->set('prodSpec', $productSpecifications);
        $this->set('productId', $productId);
        $this->set('languages', $languages);
        $this->set('adminLangId', $this->adminLangId);

        $this->_template->render(false, false);
    }

    public function prodSpecForm($productId = 0)
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        $prodSpecId = FatUtility :: int($post['prodSpecId']);
        $data  = array();
        $languages = Language::getAllNames();
        if ($prodSpecId>0) {
            $prodSpecObj = new ProdSpecification();
            $specResult = $prodSpecObj->getProdSpecification($prodSpecId, $productId);

            foreach ($specResult as $key => $value) {
                foreach ($languages as $langId => $langName) {
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
        $this->_template->render(false, false, 'products/prod-spec-form.php', false, false);
    }

    private function getProductSpecForm()
    {
        $this->objPrivilege->canEditProducts();
        $frm = new Form('frmProductSpec');
        $languages = Language::getAllNames();

        foreach ($languages as $langId => $langName) {
            $frm->addRequiredField(Labels::getLabel('LBL_Specification_Name', $this->adminLangId), 'prod_spec_name['.$langId.']');
            $frm->addRequiredField(Labels::getLabel('LBL_Specification_Value', $this->adminLangId), 'prod_spec_value['.$langId.']');
        }
        $frm->addHiddenField('', 'product_id');
        $frm->addHiddenField('', 'prodspec_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));

        return $frm;
    }

    public function setupProductSpecifications()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        $productId = FatUtility::int($post['product_id']);
        $prodspec_id = FatUtility::int($post['prodspec_id']);

        $prodSpecObj = new ProdSpecification($prodspec_id);

        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            $data_to_be_save['prodspec_product_id'] = $productId;
            if ($prodspec_id<1) {
                $prodSpecObj->assignValues($data_to_be_save);

                if (!$prodSpecObj->save()) {
                    Message::addErrorMessage(Labels::getLabel($prodSpecObj->getError(), $this->adminLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
                $prodSpecObj = new ProdSpecification($prodSpecObj->getMainTableRecordId());
            }

            $data_to_save_lang['prodspec_name'] = $post['prod_spec_name'][$langId];
            $data_to_save_lang['prodspec_value'] = $post['prod_spec_value'][$langId];
            $data_to_save_lang['prodspeclang_lang_id'] = $langId;
            $data['prodspeclang_prodspec_id'] = $prodspec_id;
            if (!$prodSpecObj->updateLangData($langId, $data_to_save_lang)) {
                Message::addErrorMessage(Labels::getLabel($ProdSpecObj->getError(), $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $this->set('productId', $productId);
        $this->set('msg', Labels::getLabel('LBL_Specification_added_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteProdSpec($productId = 0)
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();

        $prodspec_id = FatUtility::int($post['prodSpecId']);

        if ($prodspec_id>0) {
            $this->objPrivilege->canEditProducts();
        }
        $prodSpecObj = new ProdSpecification($prodspec_id);
        if (!$prodSpecObj->deleteRecord(true)) {
            Message::addErrorMessage(Labels::getLabel($ProdSpecObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Specification_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function addUpdateProductSellerShipping($product_id, $data_to_be_save, $userId)
    {
        return Product::addUpdateProductSellerShipping($product_id, $data_to_be_save, $userId);
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditProducts();
        $productId = FatApp::getPostedData('productId', FatUtility::VAR_INT, 0);
        if (0 >= $productId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $productData = Product::getAttributesById($productId, array('product_active'));
        if (false == $productData) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($productData['product_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateProductStatus($productId, $status);
        Product::updateMinPrices($productId);
        $this->set("msg", $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditProducts();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $productIdsArr = FatUtility::int(FatApp::getPostedData('product_ids'));
        if (empty($productIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($productIdsArr as $productId) {
            if (1 > $productId) {
                continue;
            }

            $this->updateProductStatus($productId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateProductStatus($productId, $status)
    {
        $status = FatUtility::int($status);
        $productId = FatUtility::int($productId);
        if (1 > $productId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $productObj = new Product($productId);

        if (!$productObj->changeStatus($status)) {
            Message::addErrorMessage($productObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function deleteProduct()
    {
        $this->objPrivilege->canEditProducts();
        $productId = FatApp::getPostedData('productId', FatUtility::VAR_INT, 0);
        if (1 > $productId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->markAsDeleted($productId);
        Product::updateMinPrices($productId);
        $this->set("msg", $this->str_delete_record);
        FatUtility::dieJsonSuccess($this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditProducts();
        $productIdsArr = FatUtility::int(FatApp::getPostedData('product_ids'));

        if (empty($productIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($productIdsArr as $productId) {
            if (1 > $productId) {
                continue;
            }
            $this->markAsDeleted($productId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($productId)
    {
        $productId = FatUtility::int($productId);
        if (1 > $productId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $productObj = new Product($productId);

        if (!$productObj->deleteProduct()) {
            Message::addErrorMessage($productObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function productLinks($product_id)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $prodCatObj = new ProductCategory();
        $arr_options = $prodCatObj->getProdCatTreeStructure(0, $this->adminLangId);

        $prodObj = new Product();
        $product_categories = $prodObj->getProductCategories($product_id);

        $this->set('selectedCats', $product_categories);
        $this->set('arr_options', $arr_options);
        $this->set('product_id', $product_id);
        $this->_template->render(false, false);
    }

    public function updateProductLink()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product();
        if (!$prodObj->addUpdateProductCategory($product_id, $option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProductCategory()
    {
        $this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product();
        if (!$prodObj->removeProductCategory($product_id, $option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Category_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function upcForm($product_id = 0)
    {
        $this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_product_id', '=', $product_id);
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $upcCodeData = FatApp::getDb()->fetchAll($rs, 'upc_options');

        $productOptions = Product::getProductOptions($product_id, $this->adminLangId, true);

        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');

        $this->set('productOptions', $productOptions);
        $this->set('optionCombinations', $optionCombinations);
        $this->set('upcCodeData', $upcCodeData);
        $this->set('product_id', $product_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function updateUpc($product_id = 0)
    {
        $this->objPrivilege->canEditProducts();
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $post = FatApp::getPostedData();
        if (false === $post || $post['code'] == '') {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $options = str_replace('|', ',', $post['optionValueId']);

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_product_id', '!=', $product_id);
        $srch->addCondition('upc_code', '=', $post['code']);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row && $row['upc_product_id'] != $product_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_UPC/EAN_code_already_assigned_to_another_product', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_product_id', '=', $product_id);
        $srch->addCondition('upc_options', '=', $options);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $data = array(
        'upc_code'=>$post['code'],
        'upc_product_id'=>$product_id,
        'upc_options'=>$options,
        );

        if ($row && $row['upc_product_id'] == $product_id && $row['upc_options'] == $options) {
            $upcObj = new UpcCode($row['upc_code_id']);
        } else {
            $upcObj = new UpcCode();
        }

        $upcObj->assignValues($data);
        if (!$upcObj->save()) {
            Message::addErrorMessage($upcObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->adminLangId));
        $this->set('product_id', $product_id);
        $this->set('lang_id', FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoCompleteSellerJson()
    {
        $pagesize = applicationConstants::PAGE_SIZE;
        $post = FatApp::getPostedData();
        $srch = User::getSearchObject(true);
        $srch->addCondition('user_is_supplier', '=', applicationConstants::YES);
        $srch->addCondition('credential_active', '=', applicationConstants::ACTIVE);

        $srch->addMultipleFields(array('credential_user_id', 'credential_username', 'credential_email' ));

        if ('' != $post['keyword']) {
            $srch->addCondition('credential_username', 'like', '%' . $post['keyword'] . '%');
            $srch->addCondition('credential_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        }
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $sellers = FatApp::getDb()->fetchAll($rs, 'credential_user_id');

        die(json_encode($sellers));
    }
}
