<?php
class ProductRequest extends MyAppModel
{
    const DB_TBL = 'tbl_product_requests';
    const DB_TBL_LANG ='tbl_product_requests_lang';

    const DB_TBL_PREFIX = 'preq_';
    const DB_TBL_LANG_PREFIX = 'preqlang_';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_CANCELLED = 2;

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::STATUS_PENDING        =>    Labels::getLabel('LBL_Pending', $langId),
        static::STATUS_APPROVED    =>    Labels::getLabel('LBL_Approved', $langId),
        static::STATUS_CANCELLED    =>    Labels::getLabel('LBL_Cancelled', $langId),
        );
    }

    public static function getSearchObject($langId = 0, $deleted = false, $submittedForApproval = false)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'preq');
        if ($langId) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'preq_l.'.static::DB_TBL_LANG_PREFIX.'preq_id = preq.'.static::tblFld('id').' and
			preq_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'preq_l'
            );
        }

        if ($deleted == false) {
            $srch->addCondition(static::tblFld('deleted'), '=', applicationConstants::NO);
        }

        if ($submittedForApproval == true) {
            $srch->addCondition(static::tblFld('submitted_for_approval'), '=', applicationConstants::YES);
        }

        return $srch;
    }

    public static function getDataArr($preqId, $attr)
    {
        $row_data = static::getAttributesById($preqId, $attr);
        $productData = json_decode($row_data['preq_content'], true);
        unset($row_data['preq_content']);
        $row_data = array_merge($row_data, $productData);
        return     $row_data;
    }


    public function deleteProductImage($preq_id, $image_id)
    {
        $preq_id = FatUtility :: int($preq_id);
        $image_id = FatUtility :: int($image_id);
        if ($preq_id < 1 || $image_id < 1) {
            $this->error = 'Invalid Request!';
            return false;
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $image_id)) {
            $this->error = $fileHandlerObj->getError();
            return false;
        }
        return true;
    }

    public function updateProdImagesOrder($preq_id, $order)
    {
        $preq_id = FatUtility :: int($preq_id);
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }
                FatApp::getDb()->updateFromArray('tbl_attached_files', array('afile_display_order' => $i), array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_id = ?','vals' => array(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $id)));
            }
            return true;
        }
        return false;
    }

    public static function getProductReqOptions($preq_id, $lang_id, $includeOptionValues = false, $option_is_separate_images = 0)
    {
        $preq_id = FatUtility::convertToType($preq_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$preq_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }

        $data = array();
        $productReqRow = static::getAttributesById($preq_id);
        $productData = json_decode($productReqRow['preq_content'], true);
        if (!empty($productData['product_option'])) {
            $optionSrch = Option::getSearchObject($lang_id);
            $optionSrch->addMultipleFields(array('IFNULL(option_name,option_identifier) as option_name','option_id'));
            $optionSrch->doNotCalculateRecords();
            $optionSrch->doNotLimitRecords();
            $optionSrch->addCondition('option_id', 'in', $productData['product_option']);
            $rs = $optionSrch->getResultSet();
            $db = FatApp::getDb();
            //$option = FatApp::getDb()->fetchAll($rs);
            while ($row = $db->fetch($rs)) {
                if ($includeOptionValues) {
                    $row['optionValues'] = Product::getOptionValues($row['option_id'], $lang_id);
                }
                $data[] = $row;
            }
            return $data;
        }
    }

    public static function getProductShippingRates($preq_id, $lang_id, $country_id = 0, $sellerId = 0, $limit = 0)
    {
        $preq_id = FatUtility::convertToType($preq_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        $sellerId = FatUtility::convertToType($sellerId, FatUtility::VAR_INT);
        if (!$preq_id || !$lang_id) {
            return false;
        }

        $productReqRow = ProductRequest::getAttributesById($preq_id, array('preq_content','preq_user_id'));
        $productData = json_decode($productReqRow['preq_content'], true);
        $shippingRates = array();


        if ($sellerId > 0 && array_key_exists('product_seller_shipping', $productData)) {
            $shippingArr = $productData['product_seller_shipping'];
        } else {
            $shippingArr = array_key_exists('product_shipping', $productData)?$productData['product_shipping']:array();
        }
        /* CommonHelper::printArray($shippingArr); die; */
        if (!empty($shippingArr)) {
            $count = 0;

            foreach ($shippingArr as $val) {
                if (!array_key_exists('country_id', $val)) {
                    continue;
                }
                $shippingRates[$count] = $val;
                $shippingRates[$count]['pship_id'] = $count;
                $shippingRates[$count]['pship_country'] = $val['country_id'];
                $shippingRates[$count]['pship_user_id'] = $productReqRow['preq_user_id'];
                $shippingRates[$count]['pship_company'] = $val['company_id'];
                $shippingRates[$count]['pship_duration'] = $val['processing_time_id'];
                $shippingRates[$count]['pship_charges'] = $val['cost'];
                $shippingRates[$count]['pship_additional_charges'] = $val['additional_cost'];
                $shippingRates[$count]['country_name'] = $val['country_name'];

                $shipCompSrch = ShippingCompanies::getListingObj(
                    $lang_id,
                    array('ifNull('.ShippingCompanies::DB_TBL_PREFIX.'name',ShippingCompanies::DB_TBL_PREFIX.'identifier) as '.ShippingCompanies::DB_TBL_PREFIX.'name',
                                        ShippingCompanies::DB_TBL_PREFIX.'id',
                                        ShippingCompanies::DB_LANG_TBL_PREFIX.'scompany_id')
                );
                $shipCompSrch->addCondition(ShippingCompanies::DB_TBL_PREFIX.'id', '=', $val['company_id']);
                $rs = $shipCompSrch->getResultSet();
                $shippingCompData = FatApp::getDb()->fetch($rs);
                if (is_array($shippingCompData)) {
                    $shippingRates[$count] = array_merge($shippingRates[$count], $shippingCompData);
                }

                $shipDurationSrch = ShippingDurations::getListingObj(
                    $lang_id,
                    array(ShippingDurations::DB_TBL_PREFIX.'name',
                                        ShippingDurations::DB_TBL_PREFIX.'id',
                                        ShippingDurations::DB_TBL_PREFIX.'from',
                                        ShippingDurations::DB_TBL_PREFIX.'identifier ',
                                        ShippingDurations::DB_TBL_PREFIX.'to',
                                        ShippingDurations::DB_TBL_PREFIX.'days_or_weeks')
                );
                $shipDurationSrch->addCondition(ShippingDurations::DB_TBL_PREFIX.'id', '=', $val['processing_time_id']);
                $rs = $shipDurationSrch->getResultSet();
                $durationArr = FatApp::getDb()->fetch($rs);
                if (is_array($durationArr)) {
                    $shippingRates[$count] = array_merge($shippingRates[$count], $durationArr);
                }
                $count++;
            }
        }

        return $shippingRates;
    }
}
