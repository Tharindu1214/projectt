<?php
class ImportExportController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->langId = $this->adminLangId;
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

        if(1 > $langId) {
            $langId =  CommonHelper::getLangId();
        }

        switch($actionType){
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewSellerProducts();
                break;
            case Importexport::TYPE_OPTIONS:
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                break;
            case Importexport::TYPE_CITY:
                $this->objPrivilege->canViewCities();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                break;
            case Importexport::TYPE_USERS:
                $this->objPrivilege->canViewUsers();
                break;
            case Importexport::TYPE_TAX_CATEGORY:
                $this->objPrivilege->canViewTax();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
        }

        $obj = new Importexport();
        $min = null;
        $max = null;
        switch($exportDataRange){
            case Importexport::BY_ID_RANGE:
                if (isset($startId) && $startId >0) {
                    $min = $startId;
                }

                if (isset($endId) && $endId >1 && $endId  > $min) {
                    $max = $endId;
                }
                $obj->export($actionType, $langId, $sheetType, null, null, $min, $max);
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
                $obj->export($actionType, $langId, $sheetType, $min, $max, null, null);
                break;

            default:
                $obj->export($actionType, $langId, $sheetType, null, null, null, null);
                break;
        }
    }

    public function importData($actionType)
    {
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sheetType = FatApp::getPostedData('sheet_type', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canEditProducts();
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canEditSellerProducts();
                break;
            case Importexport::TYPE_OPTIONS:
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canEditOptions();
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canEditTags();
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canEditCountries();
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canEditStates();
                break;
            case Importexport::TYPE_CITY:
                $this->objPrivilege->canEditCities();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canEditPolicyPoints();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
        }

        $obj->import($actionType, $langId, $sheetType);
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

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewSellerProducts();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
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

                $obj->exportMedia($actionType, $langId, null, null, $min, $max);
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
                $obj->exportMedia($actionType, $langId, $min, $max, null, null);
                break;

            default:
                $obj->exportMedia($actionType, $langId, null, null, null, null);
                break;
        }
    }

    public function importMedia($actionType)
    {
        $post = FatApp::getPostedData();

        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canEditProducts();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
        }

        $obj->importMedia($actionType, $post, $langId);
    }

    public function importMediaForm($actionType)
    {
        $langId =     $this->langId;
        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                $title = Labels::getLabel('LBL_Import_Categories_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'IMPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                $title = Labels::getLabel('LBL_Import_Brands_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'IMPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canEditProducts();
                $title = Labels::getLabel('LBL_Import_Catalog_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'IMPORT_MEDIA', $actionType);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function exportMediaForm($actionType)
    {
        $langId = $this->langId;
        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                $title = Labels::getLabel('LBL_Export_Categories_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                $title = Labels::getLabel('LBL_Export_Brands_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $title = Labels::getLabel('LBL_Export_Catalogs_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewSellerProducts();
                $title = Labels::getLabel('LBL_Export_Digital_Files', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function importForm($actionType)
    {
        $langId = $this->langId ;
        $displayMediaTab = true;
        switch($actionType){
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                $title = Labels::getLabel('LBL_Import_Categories', $langId);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                $title = Labels::getLabel('LBL_Import_Brands', $langId);
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $title = Labels::getLabel('LBL_Import_Catalogs', $langId);
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewSellerProducts();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Seller_Products', $langId);
                break;
            case Importexport::TYPE_OPTIONS:
                $this->objPrivilege->canViewOptions();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Options', $langId);
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Option_Values', $langId);
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Tags', $langId);
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Countries', $langId);
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_States', $langId);
                break;
            case Importexport::TYPE_CITY:
                $this->objPrivilege->canViewCities();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Cities', $langId);
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Policy_Points', $langId);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $frm = $this->getImportExportForm($langId, 'IMPORT', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function importInstructions($actionType)
    {
        $langId = $this->langId ;
        $obj = new Extrapage();
        $pageData = '';
        $displayMediaTab = false;
        switch($actionType){
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                $displayMediaTab = true;
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_PRODUCTS_CATEGORIES_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                $displayMediaTab = true;
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_BRANDS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $displayMediaTab = true;
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_CATALOG_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewSellerProducts();
                   $pageData = $obj->getContentByPageType(Extrapage::ADMIN_PRODUCT_INVENTORY_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_OPTIONS:
                $this->objPrivilege->canViewOptions();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_OPTIONS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_OPTIONS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_TAGS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_COUNTRIES_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_STATE_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_CITY:
                $this->objPrivilege->canViewCities();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_CITY_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_TYPE_POLICY_POINTS, $langId);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }
        $title = Labels::getLabel('LBL_Import_Instructions', $langId);
        $this->set('pageData', $pageData);
        $this->set('title', $title);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->_template->render(false, false);
    }

    public function exportForm($actionType)
    {
        $langId = $this->langId;
        $displayMediaTab = false;

        $options = Importexport::getImportExportTypeArr('export', $this->adminLangId, false);
        $title = $options[$actionType];

        switch($actionType){
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewSellerProducts();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_OPTIONS:
                $this->objPrivilege->canViewOptions();
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                break;
            case Importexport::TYPE_CITY:
                $this->objPrivilege->canViewCities();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                break;
            case Importexport::TYPE_USERS:
                $this->objPrivilege->canViewUsers();
                break;
            case Importexport::TYPE_TAX_CATEGORY:
                $this->objPrivilege->canViewTax();
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $frm = $this->getImportExportForm($langId, 'EXPORT', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }


    public function getImportExportForm($langId,$type = 'EXPORT',$actionType)
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
                switch($actionType){
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
                    case Importexport::TYPE_OPTIONS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getOptionContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    }
                    break;
            case 'EXPORT_MEDIA':
                switch($actionType){
                    case Importexport::TYPE_PRODUCTS:
                    case Importexport::TYPE_SELLER_PRODUCTS:
                        $displayRangeFields = true;
                        break;
                }
                break;
            case 'IMPORT':
                switch($actionType){
                    case Importexport::TYPE_PRODUCTS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getProductCatalogContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_SELLER_PRODUCTS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getSellerProductContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_OPTIONS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getOptionContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                }
                $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $langId), 'import_file', array('id' => 'import_file'));
                $fldImg->requirement->setRequired(true);
                $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
                $fldImg->htmlBeforeField='<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField='<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $langId).'</label></div>';
                break;
            case 'IMPORT_MEDIA':
                $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $langId), 'import_file', array('id' => 'import_file'));
                $fldImg->requirement->setRequired(true);
                $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
                $fldImg->htmlBeforeField='<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField='<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $langId).'</label></div>';
                break;
        }

        if($displayRangeFields) {
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
}
