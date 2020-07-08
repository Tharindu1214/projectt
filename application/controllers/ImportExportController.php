<?php
class ImportExportController extends SellerBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        if (!Shop::isShopActive(UserAuthentication::getLoggedUserId(), 0, true)) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'shop'));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Packages'));
        }
    }

    public function index()
    {
        $this->_template->render(true, true);
    }

    public function exportData($actionType)
    {
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $exportDataRange = FatApp::getPostedData('export_data_range', FatUtility::VAR_INT, 0);
        $startId = FatApp::getPostedData('start_id', FatUtility::VAR_INT, 0);
        $endId = FatApp::getPostedData('end_id', FatUtility::VAR_INT, 0);
        $batchCount = FatApp::getPostedData('batch_count', FatUtility::VAR_INT, 0);
        $batchNumber = FatApp::getPostedData('batch_number', FatUtility::VAR_INT, 1);
        $sheetType = FatApp::getPostedData('sheet_type', FatUtility::VAR_INT, 0);
        $userId = UserAuthentication::getLoggedUserId();

        if (1 > $langId) {
            $langId =  CommonHelper::getLangId();
        }

        $obj = new Importexport();
        $min = null;
        $max = null;
        switch ($exportDataRange) {
            case Importexport::BY_ID_RANGE:
                if (isset($startId) && $startId >0) {
                    $min = $startId;
                }

                if (isset($endId) && $endId >1 && $endId  > $min) {
                    $max = $endId;
                }
                $obj->export($actionType, $langId, $sheetType, null, null, $min, $max, $userId);
                break;
            case Importexport::BY_BATCHES:
                if (isset($batchNumber) && $batchNumber >0) {
                    $min = $batchNumber;
                }

                $max = Importexport::MAX_LIMIT;
                if (isset($batchCount) && $batchCount >0 && $batchCount <= Importexport::MAX_LIMIT) {
                    $max = $batchCount;
                }
                $min = (!$min)?1:$min;
                $obj->export($actionType, $langId, $sheetType, $min, $max, null, null, $userId);
                break;

            default:
                $obj->export($actionType, $langId, $sheetType, null, null, null, null, $userId);
                break;
        }
    }

    public function importData($actionType)
    {
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sheetType = FatApp::getPostedData('sheet_type', FatUtility::VAR_INT, 0);
        $userId = UserAuthentication::getLoggedUserId();

        $obj->import($actionType, $langId, $sheetType, $userId);
    }

    public function exportMedia($actionType)
    {
        $post = FatApp::getPostedData();
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $exportDataRange = FatApp::getPostedData('export_data_range', FatUtility::VAR_INT, 0);
        $startId = FatApp::getPostedData('start_id', FatUtility::VAR_INT, 0);
        $endId = FatApp::getPostedData('end_id', FatUtility::VAR_INT, 0);
        $batchCount = FatApp::getPostedData('batch_count', FatUtility::VAR_INT, 0);
        $batchNumber = FatApp::getPostedData('batch_number', FatUtility::VAR_INT, 1);
        $userId = UserAuthentication::getLoggedUserId();

        $obj = new Importexport();

        $min = null;
        $max = null;

        switch ($exportDataRange) {
            case Importexport::BY_ID_RANGE:
                if (isset($startId) && $startId >0) {
                    $min = $startId;
                }

                if (isset($endId) && $endId >1 && $endId  > $min) {
                    $max = $endId;
                }

                $obj->exportMedia($actionType, $langId, null, null, $min, $max, $userId);
                break;
            case Importexport::BY_BATCHES:
                if (isset($batchNumber) && $batchNumber >0) {
                    $min = $batchNumber;
                }

                $max = Importexport::MAX_LIMIT;
                if (isset($batchCount) && $batchCount >0 && $batchCount <= Importexport::MAX_LIMIT) {
                    $max = $batchCount;
                }
                $min = (!$min)?1:$min;
                $obj->exportMedia($actionType, $langId, $min, $max, null, null, $userId);
                break;

            default:
                $obj->exportMedia($actionType, $langId, null, null, null, null, $userId);
                break;
        }
    }

    public function importMedia($actionType)
    {
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj->importMedia($actionType, $post, $langId, $userId);
    }

    public function loadForm($formType)
    {
        switch (strtoupper($formType)) {
            case 'GENERAL_INSTRUCTIONS':
                $this->generalInstructions();
                break;
            case 'IMPORT':
                $this->import();
                break;
            case 'EXPORT':
                $this->export();
                break;
            case 'SETTINGS':
                $this->settings();
                break;
            case 'BULK_MEDIA':
                $this->bulkMedia();
                break;
        }
    }

    public function exportForm($actionType)
    {
        $displayMediaTab = false;
        $options = Importexport::getImportExportTypeArr('export', $this->siteLangId, true);
        if (!isset($options[$actionType])) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $title = $options[$actionType];

        switch ($actionType) {
         /* case Importexport::TYPE_CATEGORIES:         */
            case Importexport::TYPE_BRANDS:
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $displayMediaTab = true;
                break;
        }

        $frm = $this->getImportExportForm($this->siteLangId, 'EXPORT', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function exportMediaForm($actionType)
    {
        $options = Importexport::getImportExportTypeArr('export', $this->siteLangId, true);

        if (!isset($options[$actionType])) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $title = $options[$actionType];

        $frm = $this->getImportExportForm($this->siteLangId, 'EXPORT_MEDIA', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function importForm($actionType)
    {
        $options = Importexport::getImportExportTypeArr('import', $this->siteLangId, true);
        if (!isset($options[$actionType])) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $title = $options[$actionType];

        $displayMediaTab = false;
        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
            case Importexport::TYPE_BRANDS:
            case Importexport::TYPE_PRODUCTS:
                $displayMediaTab = true;
                break;
        }

        $frm = $this->getImportExportForm($this->siteLangId, 'IMPORT', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function importInstructions($actionType)
    {
        $langId = $this->siteLangId ;
        $obj = new Extrapage();
        $pageData = '';
        $displayMediaTab = false;
        switch ($actionType) {
            case Importexport::TYPE_PRODUCTS:
                $displayMediaTab = true;
                $pageData = $obj->getContentByPageType(Extrapage::SELLER_CATALOG_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $pageData = $obj->getContentByPageType(Extrapage::SELLER_PRODUCT_INVENTORY_INSTRUCTIONS, $langId);
                break;
            default:
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $langId));
                break;
        }
        $title = Labels::getLabel('LBL_Import_Instructions', $langId);
        $this->set('pageData', $pageData);
        $this->set('title', $title);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->_template->render(false, false);
    }

    public function importMediaForm($actionType)
    {
        $options = Importexport::getImportExportTypeArr('import', $this->siteLangId, true);
        if (!isset($options[$actionType])) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $title = $options[$actionType];

        $frm = $this->getImportExportForm($this->siteLangId, 'IMPORT_MEDIA', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function import()
    {
        $frm = $this->getImportForm($this->siteLangId);

        $this->set('action', 'import');
        $this->set('frm', $frm);
        $this->set('sitelangId', $this->siteLangId);
        $this->_template->render(false, false, 'import-export/import.php');
    }

    public function export()
    {
        $frm = $this->getExportForm($this->siteLangId);

        $this->set('action', 'export');
        $this->set('frm', $frm);
        $this->_template->render(false, false, 'import-export/export.php');
    }

    public function generalInstructions()
    {
        $langId = $this->siteLangId ;
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::SELLER_GENERAL_SETTINGS_INSTRUCTIONS, $langId);
        $this->set('pageData', $pageData);
        $this->set('action', 'generalInstructions');
        $this->_template->render(false, false, 'import-export/general-instructions.php');
    }
    public function bulkMedia()
    {
        $frm = $this->getBulkMediaUploadForm($this->siteLangId);

        $this->set('action', 'bulkMedia');
        $this->set('frm', $frm);
        $this->_template->render(false, false, 'import-export/bulk-media.php');
    }

    private function getBulkMediaUploadForm($langId)
    {
        $frm = new Form('uploadBulkImages', array('id'=>'uploadBulkImages'));

        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $langId), 'bulk_images', array('id' => 'bulk_images', 'accept' => '.zip' ));
        $fldImg->requirement->setRequired(true);
        $fldImg->setFieldTagAttribute('onChange', '$("#uploadFileName").html(this.value)');
        $fldImg->htmlBeforeField='<div class="filefield">';
        $fldImg->htmlAfterField='<label class="filelabel"></label></div>';

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $langId));
        return $frm;
    }

    public function updateSettings()
    {
        $frm = $this->getSettingForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userId = UserAuthentication::getLoggedUserId();
        $obj = new Importexport();
        $settingArr = $obj->getSettingsArr();

        foreach ($settingArr as $k => $val) {
            $data = array(
            'impexp_setting_key'=>$k,
            'impexp_setting_user_id'=>$userId,
            'impexp_setting_value'=>isset($post[$k])?$post[$k]:0,
            );
            FatApp::getDb()->insertFromArray(Importexport::DB_TBL_SETTINGS, $data, false, array(), $data);
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successfull', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function settings()
    {
        $frm =  $this->getSettingForm($this->siteLangId);
        $userId = UserAuthentication::getLoggedUserId();

        $obj = new Importexport();
        $settingArr = $obj->getSettings($userId);

        $frm->fill($settingArr);

        $this->set('frm', $frm);
        $this->set('action', 'settings');
        $this->_template->render(false, false, 'import-export/settings.php');
    }

    private function getSettingForm($langId)
    {
        $frm = new Form('frmImportExportSetting', array('id'=>'frmImportExportSetting'));

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_brand_id_instead_of_brand_identifier", $langId), 'CONF_USE_BRAND_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_brand_id_instead_of_brand_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_category_id_instead_of_category_identifier", $langId), 'CONF_USE_CATEGORY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_category_id_instead_of_category_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_catalog_product_id_instead_of_catalog_product_identifier", $langId), 'CONF_USE_PRODUCT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_catalog_product_id_instead_of_catalog_product_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_id_instead_of_option_identifier", $langId), 'CONF_USE_OPTION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_option_id_instead_of_option_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_value_id_instead_of_option_identifier", $langId), 'CONF_OPTION_VALUE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_option_value_id_instead_of_option_value_identifier_in_worksheets", $langId).'</small>';

        /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_type_id_instead_of_option_type_identifier",$langId),'CONF_USE_OPTION_TYPE_ID',1,array(),false,0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_option_type_id_instead_of_option_type_identifier_in_worksheets",$langId).'</small>'; */

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_tag_id_instead_of_tag_identifier", $langId), 'CONF_USE_TAG_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_tag_id_instead_of_tag_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_tax_id_instead_of_tax_identifier", $langId), 'CONF_USE_TAX_CATEOGRY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_tax_category_id_instead_of_tax_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_product_type_id_instead_of_product_type_identifier", $langId), 'CONF_USE_PRODUCT_TYPE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_product_type_id_instead_of_product_type_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_dimension_unit_id_instead_of_dimension_unit_identifier", $langId), 'CONF_USE_DIMENSION_UNIT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_dimension_unit_id_instead_of_dimension_unit_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_weight_unit_id_instead_of_weight_unit_identifier", $langId), 'CONF_USE_WEIGHT_UNIT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_weight_unit_id_instead_of_weight_unit_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_lang_id_instead_of_lang_code", $langId), 'CONF_USE_LANG_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_language_id_instead_of_language_code_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_currency_id_instead_of_currency_code", $langId), 'CONF_USE_CURRENCY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_currency_id_instead_of_currency_code_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_Product_condition_id_instead_of_condition_identifier", $langId), 'CONF_USE_PROD_CONDITION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_Product_condition_id_instead_of_condition_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_persent_or_flat_condition_id_instead_of_identifier", $langId), 'CONF_USE_PERSENT_OR_FLAT_CONDITION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_persent_or_flat_condition_id_instead_of_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_country_id_instead_of_country_code", $langId), 'CONF_USE_COUNTRY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_country_id_instead_of_country_code_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_state_id_instead_of_state_identifier", $langId), 'CONF_USE_STATE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_state_id_instead_of_state_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_policy_point_id_instead_of_policy_point_identifier", $langId), 'CONF_USE_POLICY_POINT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_policy_point_id_instead_of_policy_point_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_company_id_instead_of_shipping_company_identifier", $langId), 'CONF_USE_SHIPPING_COMPANY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_shipping_company_id_instead_of_shipping_company_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_policy_point_type_id_instead_of_policy_point_type_identifier", $langId), 'CONF_USE_POLICY_POINT_TYPE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_policy_point_type_id_instead_of_policy_point_type_identifier_in_worksheets", $langId).'</small>';

        /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_method_id_instead_of_shipping_method_identifier",$langId),'CONF_USE_SHIPPING_METHOD_ID',1,array(),false,0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_shipping_method_id_instead_of_shipping_method_identifier_in_worksheets",$langId).'</small>'; */

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_duration_id_instead_of_shipping_duration_identifier", $langId), 'CONF_USE_SHIPPING_DURATION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_shipping_duration_id_instead_of_shipping_duration_identifier_in_worksheets", $langId).'</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_1_for_yes_0_for_no", $langId), 'CONF_USE_O_OR_1', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_1_for_yes_0_for_no_for_status_type_data", $langId).'</small>';

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $langId));
        return $frm;
    }

    private function getImportForm($langId)
    {
        $frm = new Form('frmImport', array('id'=>'frmImport'));
        $options = Importexport::getImportExportTypeArr('import', $langId, true);
        if (!FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) {
            unset($options[Importexport::TYPE_PRODUCTS]);
        }
        $fld = $frm->addRadioButtons(
            '',
            'export_option',
            $options,
            '',
            array('class'=>'list-inline'),
            array('onClick'=>'getInstructions(this.value)')
        );
        $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Select_Above_option_to_import_data.", $langId)."</small><br/><small>".Labels::getLabel('MSG_Invalid_data_will_not_be_processed', $langId)."</small>";
        return $frm;
    }

    private function getExportForm($langId)
    {
        $frm = new Form('frmExport', array('id'=>'frmExport'));
        $options = Importexport::getImportExportTypeArr('export', $langId, true);
        $fld = $frm->addRadioButtons(
            '',
            'export_option',
            $options,
            '',
            array('class'=>'list-inline'),
            array('onClick'=>'exportForm(this.value)')
        );
        $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Select_Above_option_to_export_data.", $langId)."</small>";
        return $frm;
    }

    private function getImportExportForm($langId, $type = 'EXPORT', $actionType)
    {
        $frm = new Form('frmImportExport', array('id'=>'frmImportExport'));
        $languages = Language::getAllNames();

        /* if($type != 'EXPORT_MEDIA'){ */
        if ($type == 'IMPORT_MEDIA') {
            $frm->addSelectBox(Labels::getLabel('LBL_Upload_File_Language', $langId), 'lang_id', $languages, '', array(), '')->requirements()->setRequired();
        } elseif ($type == 'EXPORT_MEDIA') {
            $frm->addSelectBox(Labels::getLabel('LBL_Export_File_Language', $langId), 'lang_id', $languages, '', array(), '')->requirements()->setRequired();
        } else {
            $frm->addSelectBox(Labels::getLabel('LBL_Language', $langId), 'lang_id', $languages, '', array(), '')->requirements()->setRequired();
        }
        /* } */

        $displayRangeFields = false;

        switch (strtoupper($type)) {
            case 'EXPORT':
                switch ($actionType) {
                    case Importexport::TYPE_PRODUCTS:
                        $displayRangeFields = true;
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getProductCatalogContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_SELLER_PRODUCTS:
                        $displayRangeFields = true;
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getSellerProductContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_USERS:
                        $displayRangeFields = true;
                        break;
                }
                break;
            case 'EXPORT_MEDIA':
                switch ($actionType) {
                    case Importexport::TYPE_PRODUCTS:
                    case Importexport::TYPE_SELLER_PRODUCTS:
                        $displayRangeFields = true;
                        break;
                }
                break;
            case 'IMPORT':
                switch ($actionType) {
                    case Importexport::TYPE_PRODUCTS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getProductCatalogContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_SELLER_PRODUCTS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getSellerProductContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                }
                $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $langId), 'import_file', array('id' => 'import_file'));
                $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
                $fldImg->htmlBeforeField='<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField = "<label class='filelabel'>".Labels::getLabel('LBL_Browse_File', $this->siteLangId)."</label></div><small>".Labels::getLabel('MSG_Invalid_data_will_not_be_processed', $langId)."</small>";
                /*$fldImg->htmlBeforeField = '<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField = '</div>'; */
                break;
            case 'IMPORT_MEDIA':
                $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $langId), 'import_file', array('id' => 'import_file'));
                $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
                $fldImg->htmlBeforeField='<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField = "<label class='filelabel'>".Labels::getLabel('LBL_Browse_File', $this->siteLangId)."</label></div><small>".Labels::getLabel('MSG_Invalid_data_will_not_be_processed', $langId)."</small>";
                /* $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField = '</div>'; */
                break;
        }

        if ($displayRangeFields) {
            $dataRangeArr = array(0=>Labels::getLabel('LBL_Does_not_matter', $langId))+Importexport::getDataRangeArr($langId);
            $rangeTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Export_data_range', $langId), 'export_data_range', $dataRangeArr, '', array(), '');

            /* Start Id[ */
            $frm->addIntegerField(Labels::getLabel('LBL_start_id', $langId), 'start_id', 1);
            $startIdUnReqObj = new FormFieldRequirement('start_id', Labels::getLabel('LBL_start_id', $langId));
            $startIdUnReqObj->setRequired(false);

            $startIdReqObj = new FormFieldRequirement('start_id', Labels::getLabel('LBL_start_id', $langId));
            $startIdReqObj->setRequired(true);
            /*]*/

            /* End Id[ */
            $frm->addIntegerField(Labels::getLabel('LBL_end_id', $langId), 'end_id', Importexport::MAX_LIMIT);
            $endIdUnReqObj = new FormFieldRequirement('end_id', Labels::getLabel('LBL_end_id', $langId));
            $endIdUnReqObj->setRequired(false);

            $endIdReqObj = new FormFieldRequirement('end_id', Labels::getLabel('LBL_end_id', $langId));
            $endIdReqObj->setRequired(true);
            //$endIdReqObj->setRange(1,Importexport::MAX_LIMIT);
            /*]*/

            /* Batch Count[ */
            $frm->addIntegerField(Labels::getLabel('LBL_counts_per_batch', $langId), 'batch_count', Importexport::MAX_LIMIT);
            $batchCountUnReqObj = new FormFieldRequirement('batch_count', Labels::getLabel('LBL_counts_per_batch', $langId));
            $batchCountUnReqObj->setRequired(false);

            $batchCountReqObj = new FormFieldRequirement('batch_count', Labels::getLabel('LBL_counts_per_batch', $langId));
            $batchCountReqObj->setRequired(true);
            $batchCountReqObj->setRange(1, Importexport::MAX_LIMIT);
            /*]*/

            /* Batch Number[ */
            $frm->addIntegerField(Labels::getLabel('LBL_batch_number', $langId), 'batch_number', 1);
            $batchNumberUnReqObj = new FormFieldRequirement('batch_number', Labels::getLabel('LBL_batch_number', $langId));
            $batchNumberUnReqObj->setRequired(false);

            $batchNumberReqObj = new FormFieldRequirement('batch_number', Labels::getLabel('LBL_batch_number', $langId));
            $batchNumberReqObj->setRequired(true);
            /*]*/

            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'batch_count', $batchCountUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'batch_number', $batchNumberUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'start_id', $startIdUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'end_id', $endIdUnReqObj);

            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'batch_count', $batchCountUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'batch_number', $batchNumberUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'start_id', $startIdReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'end_id', $endIdReqObj);

            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'start_id', $startIdUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'end_id', $endIdUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'batch_count', $batchCountReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'batch_number', $batchNumberReqObj);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $langId));
        return $frm;
    }

    public function uploadBulkMedia()
    {
        $frm = $this->getBulkMediaUploadForm($this->siteLangId);
        $post = $frm->getFormDataFromArray($_FILES);

        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Data', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileName = $_FILES['bulk_images']['name'];
        $tmpName = $_FILES['bulk_images']['tmp_name'];

        $uploadBulkImgobj = new UploadBulkImages();
        $savedFile = $uploadBulkImgobj->upload($fileName, $tmpName, UserAuthentication::getLoggedUserId());
        if (false === $savedFile) {
            Message::addErrorMessage($uploadBulkImgobj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $path = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH;

        $filePath = AttachedFile::FILETYPE_BULK_IMAGES_PATH . $savedFile;

        $msg = '<br>'.str_replace('{path}', '<br><b>'.$filePath.'</b>', Labels::getLabel('MSG_Your_uploaded_files_path_will_be:_{path}', $this->siteLangId));
        $msg = Labels::getLabel('MSG_Uploaded_Successfully.', $this->siteLangId) .' '.$msg;
        $json = [
            "msg" => $msg,
            "path" => base64_encode($path . $savedFile)
        ];
        FatUtility::dieJsonSuccess($json);
    }

    public function uploadedBulkMediaList()
    {
        $db = FatApp::getDb();
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $obj = new UploadBulkImages();
        $srch = $obj->bulkMediaFileObject(UserAuthentication::getLoggedUserId());

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
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function removeDir($directory)
    {
        $db = FatApp::getDb();
        $obj = new UploadBulkImages();
        $srch = $obj->bulkMediaFileObject(UserAuthentication::getLoggedUserId());
        $srch->addCondition('afile_physical_path', '=', base64_decode($directory));
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);

        if (0 < count($row)) {
            $directory = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH . base64_decode($directory).'/' ;
            $obj = new UploadBulkImages();
            $msg = $obj->deleteSingleBulkMediaDir($directory);
            FatUtility::dieJsonSuccess($msg);
        } else {
            $errMsg = Labels::getLabel("MSG_Directory_not_found.", $this->siteLangId);
            FatUtility::dieJsonError($errMsg);
        }
    }

    public function downloadPathsFile($path)
    {
        if (empty($path)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $filesPathArr = UploadBulkImages::getAllFilesPath(base64_decode($path));
        if (!empty($filesPathArr) && 0 < count($filesPathArr)) {
            $headers[] = ['File Path', 'File Name'];
            $filesPathArr = array_merge($headers, $filesPathArr);
            CommonHelper::convertToCsv($filesPathArr, time().'.csv');
            exit;
        }
        Message::addErrorMessage(Labels::getLabel('MSG_No_File_Found', $this->siteLangId));
        CommonHelper::redirectUserReferer();
    }
}
