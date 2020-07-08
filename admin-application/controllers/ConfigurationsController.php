<?php
class ConfigurationsController extends AdminBaseController
{

    /* these variables must be only those which will store array type data and will saved as serialized array [*/
    private $serializeArrayValues =  array('CONF_VENDOR_ORDER_STATUS','CONF_BUYER_ORDER_STATUS','CONF_PROCESSING_ORDER_STATUS','CONF_COMPLETED_ORDER_STATUS','CONF_REVIEW_READY_ORDER_STATUS','CONF_ALLOW_CANCELLATION_ORDER_STATUS','CONF_DIGITAL_ALLOW_CANCELLATION_ORDER_STATUS','CONF_RETURN_EXCHANGE_READY_ORDER_STATUS','CONF_DIGITAL_RETURN_READY_ORDER_STATUS','CONF_ENABLE_DIGITAL_DOWNLOADS','CONF_PURCHASE_ORDER_STATUS','CONF_BUYING_YEAR_REWARD_ORDER_STATUS','CONF_SUBSCRIPTION_ORDER_STATUS','CONF_SELLER_SUBSCRIPTION_STATUS','CONF_BADGE_COUNT_ORDER_STATUS','CONF_PRODUCT_IS_ON_ORDER_STATUSES');
    /* ] */

    public function __construct($action)
    {
        parent::__construct($action);
        $this->set("includeEditor", true);
    }

    public function index()
    {
        $this->objPrivilege->canViewGeneralSettings();
        $tabs = Configurations::getTabsArr();

        $this->set('activeTab', Configurations::FORM_GENERAL);
        $this->set('tabs', $tabs);
        $this->_template->render();
    }

    public function form($frmType)
    {
        $this->objPrivilege->canViewGeneralSettings();

        $frmType = FatUtility::int($frmType);

        $dispLangTab = false;
        if (in_array($frmType, Configurations::getLangTypeFormArr())) {
            $dispLangTab = true;
            $this->set('languages', Language::getAllNames());
        }

        $record = Configurations::getConfigurations();

        $arrayValues = array();
        foreach ($this->serializeArrayValues as $val) {
            if (array_key_exists($val, $record)) {
                $data = @unserialize($record[$val]);
                if ($data !== false) {
                    $arrayValues[$val] = $data;
                    unset($record[$val]);
                }
            } else {
                $arrayValues[$val] = array();
            }
        }

        /* switch ($frmType){
        case Configurations::FORM_GENERAL:
        $adminLogo = AttachedFile::getAttachment( AttachedFile::FILETYPE_ADMIN_LOGO, 0 );
        $this->set( 'adminLogo', $adminLogo );

        $desktopLogo = AttachedFile::getAttachment( AttachedFile::FILETYPE_FRONT_LOGO, 0 );
        $this->set( 'desktopLogo', $desktopLogo );

        $emailLogo = AttachedFile::getAttachment( AttachedFile::FILETYPE_EMAIL_LOGO, 0 );
        $this->set( 'emailLogo', $emailLogo );

        $favicon = AttachedFile::getAttachment( AttachedFile::FILETYPE_FAVICON, 0 );
        $this->set( 'favicon', $favicon );
        break;
        } */

        $frm = $this->getForm($frmType, $arrayValues);
        $frm->fill($record);

        $this->set('frm', $frm);
        $this->set('frmType', $frmType);
        $this->set('record', $record);
        $this->set('dispLangTab', $dispLangTab);
        $this->set('lang_id', 0);
        $this->set('formLayout', '');
        $this->_template->render(false, false);
    }

    public function generalInstructions($frmType)
    {
        $frmType = FatUtility::int($frmType);
        $langId = $this->adminLangId ;
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::GENERAL_SETTINGS_INSTRUCTIONS, $langId);

        $dispLangTab = false;
        if (in_array($frmType, Configurations::getLangTypeFormArr())) {
            $dispLangTab = true;
            $this->set('languages', Language::getAllNames());
        }

        $this->set('lang_id', 0);
        $this->set('frmType', 0);
        $this->set('frmType', $frmType);
        $this->set('pageData', $pageData);
        $this->set('dispLangTab', $dispLangTab);
        $this->_template->render(false, false);
    }
    public function langForm($frmType, $langId, $tabId = null)
    {
        $this->objPrivilege->canViewGeneralSettings();

        $frmType = FatUtility::int($frmType);
        $langId = FatUtility::int($langId);

        $frm = $this->getLangForm($frmType, $langId);

        $dispLangTab = false;
        if (in_array($frmType, Configurations::getLangTypeFormArr())) {
            $dispLangTab = true;
            $this->set('languages', Language::getAllNames());
        }

        $record = Configurations::getConfigurations();
        $frm->fill($record);
        if ($tabId) {
            $this->set('tabId', $tabId);
        }
        $this->set('languages', Language::getAllNames());
        $this->set('frm', $frm);
        $this->set('dispLangTab', $dispLangTab);
        $this->set('lang_id', $langId);
        $this->set('frmType', $frmType);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false, 'configurations/form.php');
    }

    public function setup()
    {
        $this->objPrivilege->canEditGeneralSettings();

        $post = FatApp::getPostedData();
        $frmType = FatUtility::int($post['form_type']);

        if (1 > $frmType) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getForm($frmType);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        unset($post['form_type']);
        unset($post['btn_submit']);

        foreach ($this->serializeArrayValues as $val) {
            if (array_key_exists($val, $post)) {
                if (is_array($post[$val])) {
                    $post[$val] = serialize($post[$val]);
                }
            } else {
                if (isset($post[$val])) {
                    $post[$val] = 0;
                }
            }
        }

        $record = new Configurations();

        if (isset($post["CONF_SEND_SMTP_EMAIL"]) && $post["CONF_SEND_EMAIL"] && $post["CONF_SEND_SMTP_EMAIL"] && (($post["CONF_SEND_SMTP_EMAIL"] != FatApp::getConfig("CONF_SEND_SMTP_EMAIL")) || ($post["CONF_SMTP_HOST"] != FatApp::getConfig("CONF_SMTP_HOST")) || ($post["CONF_SMTP_PORT"] != FatApp::getConfig("CONF_SMTP_PORT")) || ($post["CONF_SMTP_USERNAME"] != FatApp::getConfig("CONF_SMTP_USERNAME")) || ($post["CONF_SMTP_SECURE"] != FatApp::getConfig("CONF_SMTP_SECURE")) || ($post["CONF_SMTP_PASSWORD"] != FatApp::getConfig("CONF_SMTP_PASSWORD")))) {
            $smtp_arr=array("host"=>$post["CONF_SMTP_HOST"],"port"=>$post["CONF_SMTP_PORT"],"username"=>$post["CONF_SMTP_USERNAME"],"password"=>$post["CONF_SMTP_PASSWORD"],"secure"=>$post["CONF_SMTP_SECURE"]);

            if (EmailHandler::sendSmtpTestEmail($this->adminLangId, $smtp_arr)) {
                Message::addMessage(Labels::getLabel('LBL_We_have_sent_a_test_email_to_administrator_account'.FatApp::getConfig("CONF_SITE_OWNER_EMAIL"), $this->adminLangId));
            } else {
                Message::addErrorMessage(Labels::getLabel("LBL_SMTP_settings_provided_is_invalid_or_unable_to_send_email_so_we_have_not_saved_SMTP_settings", $this->adminLangId));
                unset($post["CONF_SEND_SMTP_EMAIL"]);
                foreach ($smtp_arr as $skey => $sval) {
                    unset($post['CONF_SMTP_'.strtoupper($skey)]);
                }
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        if (isset($post['CONF_USE_SSL']) && $post['CONF_USE_SSL']==1) {
            if (!$this->is_ssl_enabled()) {
                if ($post['CONF_USE_SSL']!= FatApp::getConfig('CONF_USE_SSL')) {
                    Message::addErrorMessage(Labels::getLabel('MSG_SSL_NOT_INSTALLED_FOR_WEBSITE_Try_to_Save_data_without_Enabling_ssl', $this->adminLangId));

                    FatUtility::dieJsonError(Message::getHtml());
                }

                unset($post['CONF_USE_SSL']);
            }
        }

        if (isset($post['CONF_SITE_ROBOTS_TXT'])) {
            $filePath = CONF_INSTALLATION_PATH.'public/robots.txt';
            $robotfile = fopen($filePath, "w");
            fwrite($robotfile, $post['CONF_SITE_ROBOTS_TXT']);
            fclose($robotfile);
        }

        if (!$record->update($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('frmType', $frmType);
        $this->set('langId', 0);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function is_ssl_enabled()
    {

        // url connection
        $url = "https://".$_SERVER["HTTP_HOST"];

        // Initiate connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); // set browser/user agent
        // Set cURL and other options
        curl_setopt($ch, CURLOPT_URL, $url); // set url
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // allow https verification if true
        curl_setopt($ch, CURLOPT_NOBODY, true);
        // grab URL and pass it to the browser
        $res =  curl_exec($ch);
        if (!$res) {
            return false;
        }
        return true;
    }
    public function setupLang()
    {
        $this->objPrivilege->canEditGeneralSettings();

        $post = FatApp::getPostedData();
        $frmType = FatUtility::int($post['form_type']);
        $langId = FatUtility::int($post['lang_id']);

        if (1 > $frmType || 1 > $langId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getLangForm($frmType, $langId);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        unset($post['form_type']);
        unset($post['lang_id']);
        unset($post['btn_submit']);

        $record = new Configurations();
        if (!$record->update($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('frmType', $frmType);
        $this->set('langId', $langId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadMedia()
    {
        $this->objPrivilege->canEditGeneralSettings();
        $post = FatApp::getPostedData();

        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        if (!$file_type) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(
        AttachedFile::FILETYPE_ADMIN_LOGO,
        AttachedFile::FILETYPE_FRONT_LOGO,
        AttachedFile::FILETYPE_EMAIL_LOGO,
        AttachedFile::FILETYPE_FAVICON,
        AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE,
        AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO,
        AttachedFile::FILETYPE_WATERMARK_IMAGE,
        AttachedFile::FILETYPE_APPLE_TOUCH_ICON,
        AttachedFile::FILETYPE_MOBILE_LOGO,
        AttachedFile::FILETYPE_CATEGORY_COLLECTION_BG_IMAGE,
        AttachedFile::FILETYPE_BRAND_COLLECTION_BG_IMAGE,
        AttachedFile::FILETYPE_INVOICE_LOGO,
        );

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['file']['tmp_name'], $file_type, 0, 0, $_FILES['file']['name'], -1, true, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('frmType', Configurations::FORM_GENERAL);
        $this->set('msg', $_FILES['file']['name']. Labels::getLabel('MSG_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function redirect()
    {
        include_once CONF_INSTALLATION_PATH . 'library/analytics/analyticsapi.php';
        $analyticArr = array(
        'clientId' => FatApp::getConfig("CONF_ANALYTICS_CLIENT_ID"),
        'clientSecretKey' => FatApp::getConfig("CONF_ANALYTICS_SECRET_KEY"),
        'redirectUri' => CommonHelper::generateFullUrl('configurations', 'redirect', array(), '', false),
        'googleAnalyticsID' => FatApp::getConfig("CONF_ANALYTICS_ID")
        );
        try {
            $analytics = new Ykart_analytics($analyticArr);
            $obj = FatApplication::getInstance();
            $get = $obj->getQueryStringVar();
        } catch (exception $e) {
            Message::addErrorMessage($e->getMessage());
        }

        if (isset($get['code']) && isset($get['code'])!='') {
            $code = $get['code'];
            $auth = $analytics->getAccessToken($code);
            if ($auth['refreshToken']!='') {
                $arr = array('CONF_ANALYTICS_ACCESS_TOKEN'=>$auth['refreshToken']);
                $record = new Configurations();
                if (!$record->update($arr)) {
                    Message::addErrorMessage($record->getError());
                } else {
                    Message::addMessage(Labels::getLabel('MSG_Setting_Updated_Successfully', $this->adminLangId));
                }
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access_Token', $this->adminLangId));
            }
        } else {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
        }
        FatApp::redirectUser(CommonHelper::generateUrl('configurations', 'index'));
    }

    public function removeSiteAdminLogo($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_ADMIN_LOGO, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeDesktopLogo($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeEmailLogo($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_EMAIL_LOGO, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeFavicon($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_FAVICON, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeSocialFeedImage($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removePaymentPageLogo($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeWatermarkImage($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_WATERMARK_IMAGE, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeAppleTouchIcon($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_APPLE_TOUCH_ICON, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeMobileLogo($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_MOBILE_LOGO, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeInvoiceLogo($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_INVOICE_LOGO, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCollectionBgImage($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CATEGORY_COLLECTION_BG_IMAGE, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeBrandCollectionBgImage($lang_id = 0)
    {
        $lang_id = FatUtility::int($lang_id);

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_BRAND_COLLECTION_BG_IMAGE, 0, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($type, $arrValues = array())
    {
        $frm = new Form('frmConfiguration');

        switch ($type) {
            case Configurations::FORM_GENERAL:
                $frm->addEmailField(Labels::getLabel('LBL_Store_Owner_Email', $this->adminLangId), 'CONF_SITE_OWNER_EMAIL');
                $phnFld = $frm->addTextBox(Labels::getLabel('LBL_Telephone', $this->adminLangId), 'CONF_SITE_PHONE', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
                $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
                // $phnFld->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_e.g.', $this->adminLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</small>';
                $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_format.', $this->adminLangId));

                $faxFld = $frm->addTextBox(Labels::getLabel('LBL_Fax', $this->adminLangId), 'CONF_SITE_FAX', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
                $faxFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
                // $faxFld->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_e.g.', $this->adminLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</small>';
                $faxFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_format.', $this->adminLangId));

                $cpagesArr = ContentPage::getPagesForSelectBox($this->adminLangId);

                $frm->addSelectBox(Labels::getLabel('LBL_About_Us', $this->adminLangId), 'CONF_ABOUT_US_PAGE', $cpagesArr);
                $frm->addSelectBox(Labels::getLabel('LBL_Privacy_Policy_Page', $this->adminLangId), 'CONF_PRIVACY_POLICY_PAGE', $cpagesArr);
                $frm->addSelectBox(Labels::getLabel('LBL_Terms_and_Conditions_Page', $this->adminLangId), 'CONF_TERMS_AND_CONDITIONS_PAGE', $cpagesArr);
                $frm->addSelectBox(Labels::getLabel('LBL_GDPR_policy_page', $this->adminLangId), 'CONF_GDPR_POLICY_PAGE', $cpagesArr);
                $frm->addSelectBox(Labels::getLabel('LBL_Cookies_Policies_Page', $this->adminLangId), 'CONF_COOKIES_BUTTON_LINK', $cpagesArr);
                $fld1 = $frm->addCheckBox(Labels::getLabel('LBL_Cookies_Policies', $this->adminLangId), 'CONF_ENABLE_COOKIES', 1, array(), false, 0);
                $fld1->htmlAfterField = "<br><small>".Labels::getLabel("LBL_cookies_policies_section_will_be_shown_on_frontend", $this->adminLangId)."</small>";
                $fld3 = $frm->addTextBox(Labels::getLabel("LBL_Admin_Default_Items_Per_Page", $this->adminLangId), "CONF_ADMIN_PAGESIZE");
                $fld3->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Determines_how_many_items_are_shown_per_page_(user_listing,_categories,_etc)", $this->adminLangId).".</small>";

                $iframeFld = $frm->addTextarea(Labels::getLabel('LBL_Google_Map_Iframe', $this->adminLangId), 'CONF_MAP_IFRAME_CODE');
                $iframeFld->htmlAfterField = '<small>'.Labels::getLabel("LBL_This_is_the_Gogle_Map_Iframe_Script,_used_to_display_google_map_on_contact_us_page", $this->adminLangId).'</small>';

                /*$ipFld = $frm->addTextarea(Labels::getLabel('LBL_Whitelisted_IP', $this->adminLangId), 'CONF_WHITELISTED_IP');
                $ipFld->htmlAfterField = '<small>'.Labels::getLabel("LBL_Any_IP_you_want_to_add_in_whitelist_(comma_Separated)", $this->adminLangId).'</small>';*/

                break;

            case Configurations::FORM_LOCAL:
                $frm->addSelectBox(
                    Labels::getLabel('LBL_Default_Site_Laguage', $this->adminLangId),
                    'CONF_DEFAULT_SITE_LANG',
                    Language::getAllNames(),
                    false,
                    array(),
                    ''
                );

                $fld = $frm->addSelectBox(Labels::getLabel('LBL_Timezone', $this->adminLangId), 'CONF_TIMEZONE', Configurations::dateTimeZoneArr(), false, array(), '');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("LBL_Current", $this->adminLangId).' <span id="currentDate">'. CommonHelper::currentDateTime(null, true).'</span></small>';
                $countryObj = new Countries();
                $countriesArr = $countryObj->getCountriesArr($this->adminLangId);
                $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'CONF_COUNTRY', $countriesArr);

                $frm->addSelectBox(Labels::getLabel('LBL_date_Format', $this->adminLangId), 'CONF_DATE_FORMAT', Configurations::dateFormatPhpArr(), false, array(), '');

                $currencyArr = Currency::getCurrencyNameWithCode($this->adminLangId);
                $frm->addSelectBox(Labels::getLabel('LBL_Default_Site_Currency', $this->adminLangId), 'CONF_CURRENCY', $currencyArr, false, array(), '');

                $faqCategoriesArr = FaqCategory::getFaqPageCategories();
                $sellerCategoriesArr = FaqCategory::getSellerPageCategories();

                $frm->addSelectBox(Labels::getLabel('LBL_Faq_Page_Main_Category', $this->adminLangId), 'CONF_FAQ_PAGE_MAIN_CATEGORY', $faqCategoriesArr);
                $frm->addSelectBox(Labels::getLabel('LBL_Seller_Page_Main_Faq_Category', $this->adminLangId), 'CONF_SELLER_PAGE_MAIN_CATEGORY', $sellerCategoriesArr);


                break;

            case Configurations::FORM_SEO:
                $fld = $frm->addTextBox(Labels::getLabel('LBL_Twitter_Username', $this->adminLangId), 'CONF_TWITTER_USERNAME');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("LBL_This_is_required_for_Twitter_Card_code_SEO_Update", $this->adminLangId).'</small>';

                $fld2 = $frm->addTextarea(Labels::getLabel('LBL_Site_Tracker_Code', $this->adminLangId), 'CONF_SITE_TRACKER_CODE');
                $fld2->htmlAfterField = '<small>'.Labels::getLabel("LBL_This_is_the_site_tracker_script,_used_to_track_and_analyze_data_about_how_people_are_getting_to_your_website._e.g.,_Google_Analytics.", $this->adminLangId).' http://www.google.com/analytics/</small>';

                $robotsFld = $frm->addTextarea(Labels::getLabel('LBL_Robots_Txt', $this->adminLangId), 'CONF_SITE_ROBOTS_TXT');
                $robotsFld->htmlAfterField = '<small>'.Labels::getLabel("LBL_This_will_update_your_Robots.txt_file._This_is_to_help_search_engines_index_your_site_more_appropriately.", $this->adminLangId).'</small>';
                
                $frm->addHtml('', 'Analytics', '<h3>'.Labels::getLabel("LBL_Google_Tag_Manager", $this->adminLangId).'</h3>');
                $fld = $frm->addTextarea(Labels::getLabel("LBL_Head_Script", $this->adminLangId), 'CONF_GOOGLE_TAG_MANAGER_HEAD_SCRIPT');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_code_provided_by_google_tag_manager_for_integration.", $this->adminLangId)."</small>";

                $fld = $frm->addTextarea(Labels::getLabel("LBL_Body_Script", $this->adminLangId), 'CONF_GOOGLE_TAG_MANAGER_BODY_SCRIPT');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_code_provided_by_google_tag_manager_for_integration.", $this->adminLangId)."</small>";
                break;

            case Configurations::FORM_PRODUCT:
                $frm->addHtml('', 'Product', '<h3>'.Labels::getLabel('LBL_Product', $this->adminLangId).'</h3>');

                $fld4 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Allow_Seller_to_add_products", $this->adminLangId),
                    'CONF_ENABLED_SELLER_CUSTOM_PRODUCT',
                    1,
                    array(),
                    false,
                    0
                );
                $fld4->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_On_enabling_this_feature,_Products_option_will_enabled_for_seller_dashboard", $this->adminLangId).'</small>';

                $fld3 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Administrator_Approval_on_Products", $this->adminLangId),
                    'CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL',
                    1,
                    array(),
                    false,
                    0
                );
                $fld3->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_On_enabling_this_feature,_Products_required_admin_approval_to_display", $this->adminLangId).'</small>';


                /* $fld4 = $frm->addCheckBox(Labels::getLabel("LBL_Allow_Sellers_to_request_adding_new_products_to_the_Catalog",$this->adminLangId),'CONF_SELLER_CAN_REQUEST_PRODUCT',1,
                array(),false,0);
                $fld4->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_On_enabling_this_feature,_Seller_can_request_to_add_product_on_catalog",$this->adminLangId).'</small>'; */

                $fld4 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Allow_Sellers_to_request_products_which_is_availble_to_all_sellers", $this->adminLangId),
                    'CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT',
                    1,
                    array(),
                    false,
                    0
                );
                $fld4->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_On_enabling_this_feature,_Seller_can_request_to_add_products_available_for_all_sellers", $this->adminLangId).'</small>';

                $fld1 = $frm->addCheckBox(Labels::getLabel("LBL_Product's_Model_Mandatory", $this->adminLangId), 'CONF_PRODUCT_MODEL_MANDATORY', 1, array(), false, 0);
                $fld1->htmlAfterField = "<br><small>".Labels::getLabel("LBL_This_will_make_Product's_model_mandatory", $this->adminLangId)."</small>";

                $fld2 = $frm->addCheckBox(Labels::getLabel("LBL_Product's_SKU_Mandatory", $this->adminLangId), 'CONF_PRODUCT_SKU_MANDATORY', 1, array(), false, 0);
                $fld2->htmlAfterField = "<br><small>".Labels::getLabel("LBL_This_will_make_Product's_SKU_mandatory", $this->adminLangId)."</small>";

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Product's_Dimensions", $this->adminLangId), 'CONF_PRODUCT_DIMENSIONS_ENABLE', 1, array(), false, 0);
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_dimensions_of_the_product_will_be_required_to_be_filled._Dimensions_are_required_in_case_of_Shipstation_API_(If_Enabled)_for_Live_Shipping_Charges", $this->adminLangId)."</small>";

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Brand_Request_Approval", $this->adminLangId), 'CONF_BRAND_REQUEST_APPROVAL', 1, array(), false, 0);
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_Enabling_This_Feature,_Admin_Need_To_Approve_the_brand_requests_(User_Cannot_link_the_requested_brand_with_any_product_until_it_gets_approved_by_Admin)", $this->adminLangId)."</small>";

                $fld3 = $frm->addTextBox(Labels::getLabel("LBL_Default_Items_Per_Page_(Catalog)", $this->adminLangId), "CONF_ITEMS_PER_PAGE_CATALOG");
                $fld3->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Determines_how_many_catalog_items_are_shown_per_page_(products,_categories,_etc)", $this->adminLangId).".</small>";

                break;

            case Configurations::FORM_USER_ACCOUNT:
                /* $frm->addHtml('','Account','<h3>'.Labels::getLabel("LBL_Account",$this->adminLangId).'</h3>'); */

                $fld5 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Admin_Approval_After_Registration_(Sign_Up)", $this->adminLangId),
                    'CONF_ADMIN_APPROVAL_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld5->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_On_enabling_this_feature,_admin_need_to_approve_each_user_after_registration_(User_cannot_login_until_admin_approves)", $this->adminLangId).'</small>';

                $fld7 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Email_Verification_After_Registration", $this->adminLangId),
                    'CONF_EMAIL_VERIFICATION_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld7->htmlAfterField = "<br><small>".Labels::getLabel("LBL_user_need_to_verify_their_email_address_provided_during_registration", $this->adminLangId)." </small>";

                $fld8 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Notify_Administrator_on_Each_Registration", $this->adminLangId),
                    'CONF_NOTIFY_ADMIN_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld8->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_notification_mail_will_be_sent_to_administrator_on_each_registration.", $this->adminLangId)."</small>";

                $fld9 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Auto_Login_After_Registration", $this->adminLangId),
                    'CONF_AUTO_LOGIN_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld9->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_users_will_be_automatically_logged-in_after_registration", $this->adminLangId)."</small>";

                $fld10 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Sending_Welcome_Mail_After_Registration", $this->adminLangId),
                    'CONF_WELCOME_EMAIL_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld10->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_users_will_receive_a_welcome_mail_after_registration.", $this->adminLangId)."</small>";

                $fld11 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Separate_Seller_Sign_Up_Form", $this->adminLangId),
                    'CONF_ACTIVATE_SEPARATE_SIGNUP_FORM',
                    1,
                    array(),
                    false,
                    0
                );
                $fld11->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_buyers_and_seller_will_have_a_separate_sign_up_form.", $this->adminLangId)."</small>";

                $fld6 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Administrator_Approval_On_Seller_Request", $this->adminLangId),
                    'CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld6->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_admin_need_to_approve_Seller's_request_after_registration", $this->adminLangId)."</small>";

                $fld11 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Buyers_can_see_Seller_Tab", $this->adminLangId),
                    'CONF_BUYER_CAN_SEE_SELLER_TAB',
                    1,
                    array(),
                    false,
                    0
                );
                $fld11->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_buyers_will_be_able_to_see_Seller_tab", $this->adminLangId)."</small>";

                $fld11 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Enable_facebook_Login", $this->adminLangId),
                    'CONF_ENABLE_FACEBOOK_LOGIN',
                    1,
                    array(),
                    false,
                    0
                );
                $fld11->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,users_will_be_able_to_login_using_facebook_account._Please_define_settings_for_facebook_login_if_enabled_under_\"Third_Party_APIs\"_Tab", $this->adminLangId)."</small>";
                $fld11 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Google_Plus_Login", $this->adminLangId),
                    'CONF_ENABLE_GOOGLE_LOGIN',
                    1,
                    array(),
                    false,
                    0
                );
                $fld11->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,users_will_be_able_to_login_using_google_plus_account._Please_define_settings_for_google_plus_login_if_enabled_under_\"Third_Party_APIs\"_Tab", $this->adminLangId)."</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Max_Seller_Request_Attempts", $this->adminLangId), 'CONF_MAX_SUPPLIER_REQUEST_ATTEMPT', '');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Maximum_seller_request_attempts_allowed", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Withdrawal', '<h3>'.Labels::getLabel("LBL_Withdrawal", $this->adminLangId).'</h3>');

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Withdrawal_Amount", $this->adminLangId).' ['.$this->siteDefaultCurrencyCode.']', 'CONF_MIN_WITHDRAW_LIMIT', '');
                $fld->htmlAfterField = "<small> ".Labels::getLabel("LBL_This_is_the_minimum_withdrawable_amount.", $this->adminLangId)."</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Maximum_Withdrawal_Amount", $this->adminLangId).' ['.$this->siteDefaultCurrencyCode.']', 'CONF_MAX_WITHDRAW_LIMIT', '');
                $fld->htmlAfterField = "<small> ".Labels::getLabel("LBL_This_is_the_maximum_withdrawable_amount.", $this->adminLangId)."</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Interval_[Days]", $this->adminLangId), 'CONF_MIN_INTERVAL_WITHDRAW_REQUESTS', '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_minimum_interval_in_days_between_two_withdrawal_requests.", $this->adminLangId)."</small>";

                /* $frm->addHtml('','Tax','<h3>Tax</h3>');
                $fld = $frm->addTextbox('Global Tax/VAT','CONF_SITE_TAX','');
                $fld->htmlAfterField = "<small> %Global Tax/VAT applicable on products.</small>"; */
                break;

            case Configurations::FORM_CHECKOUT_PROCESS:
                $frm->addHtml('', 'Checkout', '<h3>'.Labels::getLabel('LBL_COD_Payments', $this->adminLangId).'</h3>');
                $fld = $frm->addTextBox(Labels::getLabel('LBL_Minimum_COD_Order_Total', $this->adminLangId).' ['.$this->siteDefaultCurrencyCode.']', 'CONF_MIN_COD_ORDER_LIMIT');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_This_is_the_minimum_cash_on_delivery_order_total,_eligible_for_COD_payments.", $this->adminLangId)."</small>";
                $fld = $frm->addTextBox(Labels::getLabel('LBL_Maximum_COD_Order_Total', $this->adminLangId).' ['.$this->siteDefaultCurrencyCode.']', 'CONF_MAX_COD_ORDER_LIMIT');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_This_is_the_maximum_cash_on_delivery_order_total,_eligible_for_COD_payments._Default_is_0", $this->adminLangId)."</small>";
                $fld = $frm->addTextBox(Labels::getLabel('LBL_Minimum_Wallet_Balance', $this->adminLangId).' ['.$this->siteDefaultCurrencyCode.']', 'CONF_COD_MIN_WALLET_BALANCE');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_seller_needs_to_maintain_to_accept_COD_orders._Default_is_-1", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Checkout', '<h3>'.Labels::getLabel('LBL_Checkout_Process', $this->adminLangId).'</h3>');
                $fld1 = $frm->addCheckBox(Labels::getLabel('LBL_Activate_Live_Payment_Transaction_Mode', $this->adminLangId), 'CONF_TRANSACTION_MODE', 1, array(), false, 0);
                $fld1->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Set_Transaction_Mode_to_live_environment", $this->adminLangId)."</small>";

                /* $frm->addHtml('','Checkout','<h3>'.Labels::getLabel("LBL_Checkout",$this->adminLangId).'</h3>'); */

                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_New_Order_Alert_Email", $this->adminLangId), 'CONF_NEW_ORDER_EMAIL', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Send_an_email_to_store_owner_when_new_order_is_placed", $this->adminLangId)."</small>";

                $orderStatusArr = Orders::getOrderProductStatusArr($this->adminLangId);

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Tax_Collected_By_Seller", $this->adminLangId), 'CONF_TAX_COLLECTED_BY_SELLER', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_On_enabling_this_feature,_seller_will_be_able_to_collect_tax", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Return_Shipping_Charges_to_Customer", $this->adminLangId), 'CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_On_enabling_return_shipping_charges_to_customer,", $this->adminLangId).'</small>';


                $fld =$frm->addSelectBox(
                    Labels::getLabel("LBL_Default_Child_Order_Status", $this->adminLangId),
                    'CONF_DEFAULT_ORDER_STATUS',
                    $orderStatusArr,
                    false,
                    array(),
                    ''
                );

                $fld =$frm->addSelectBox(
                    Labels::getLabel("LBL_Default_Paid_Order_Status", $this->adminLangId),
                    'CONF_DEFAULT_PAID_ORDER_STATUS',
                    $orderStatusArr,
                    false,
                    array(),
                    ''
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_default_child_order_status_when_an_order_is_marked_Paid.", $this->adminLangId)."</small>";

                $fld =$frm->addSelectBox(
                    Labels::getLabel("LBL_Default_Shipping_Order_Status", $this->adminLangId),
                    'CONF_DEFAULT_SHIPPING_ORDER_STATUS',
                    $orderStatusArr,
                    false,
                    array(),
                    ''
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_default_child_order_status_when_an_order_is_marked_Shipped.", $this->adminLangId)."</small>";

                $fld =$frm->addSelectBox(
                    Labels::getLabel("LBL_Default_Delivered_Order_Status", $this->adminLangId),
                    'CONF_DEFAULT_DEIVERED_ORDER_STATUS',
                    $orderStatusArr,
                    false,
                    array(),
                    ''
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_default_child_order_status_when_an_order_is_marked_delivered.", $this->adminLangId)."</small>";

                $fld =$frm->addSelectBox(
                    Labels::getLabel("LBL_Default_Cancelled_Order_Status", $this->adminLangId),
                    'CONF_DEFAULT_CANCEL_ORDER_STATUS',
                    $orderStatusArr,
                    false,
                    array(),
                    ''
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_default_child_order_status_when_an_order_is_marked_Cancelled.", $this->adminLangId)."</small>";

                $fld =$frm->addSelectBox(
                    Labels::getLabel("LBL_Return_Requested_Order_Status", $this->adminLangId),
                    'CONF_RETURN_REQUEST_ORDER_STATUS',
                    $orderStatusArr,
                    false,
                    array(),
                    ''
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_default_child_order_status_when_return_request_is_opened_on_any_order.", $this->adminLangId)."</small>";

                $fld =$frm->addSelectBox(Labels::getLabel("LBL_Return_Request_Withdrawn_Order_Status", $this->adminLangId), 'CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS', $orderStatusArr, false, array(), '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_default_child_order_status_when_return_request_is_withdrawn.", $this->adminLangId)."</small>";

                $fld = $frm->addSelectBox(Labels::getLabel("LBL_Return_Request_Approved_Order_Status", $this->adminLangId), 'CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS', $orderStatusArr, false, array(), '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_default_child_order_status_when_return_request_is_accepted_by_the_Seller.", $this->adminLangId)."</small>";

                $fld = $frm->addSelectBox(Labels::getLabel("LBL_Cash_on_Delivery_Order_Status", $this->adminLangId), 'CONF_COD_ORDER_STATUS', $orderStatusArr, false, array(), '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_Cash_on_delivery_order_status.", $this->adminLangId)."</small>";

                $vendorOrderSelected = (!empty($arrValues['CONF_VENDOR_ORDER_STATUS']))?$arrValues['CONF_VENDOR_ORDER_STATUS']:0;

                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Seller_Order_Statuses", $this->adminLangId), 'CONF_VENDOR_ORDER_STATUS', $orderStatusArr, $vendorOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_the_order_starts_displaying_to_Sellers.", $this->adminLangId)."</small>";

                $buyerOrderSelected = (!empty($arrValues['CONF_BUYER_ORDER_STATUS']))?$arrValues['CONF_BUYER_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Buyer_Order_Statuses", $this->adminLangId), 'CONF_BUYER_ORDER_STATUS', $orderStatusArr, $buyerOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_the_order_starts_displaying_to_Buyers.", $this->adminLangId)."</small>";

                $processingOrderSelected = (!empty($arrValues['CONF_PROCESSING_ORDER_STATUS']))?$arrValues['CONF_PROCESSING_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Processing_Order_Status", $this->adminLangId), 'CONF_PROCESSING_ORDER_STATUS', $orderStatusArr, $processingOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_the_order_starts_stock_subtraction.", $this->adminLangId)."</small>";

                $completeOrderSelected = (!empty($arrValues['CONF_COMPLETED_ORDER_STATUS']))?$arrValues['CONF_COMPLETED_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Completed_Order_Status", $this->adminLangId), 'CONF_COMPLETED_ORDER_STATUS', $orderStatusArr, $completeOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_considered_completed_and_payment_released_to_Sellers.", $this->adminLangId)."</small>";

                $feedbackOrderSelected = (!empty($arrValues['CONF_REVIEW_READY_ORDER_STATUS']))?$arrValues['CONF_REVIEW_READY_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Feedback_ready_Order_Status", $this->adminLangId), 'CONF_REVIEW_READY_ORDER_STATUS', $orderStatusArr, $feedbackOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_allowed_to_review_the_orders.", $this->adminLangId)."</small>";

                $allowCancellationOrderSelected = (!empty($arrValues['CONF_ALLOW_CANCELLATION_ORDER_STATUS']))?$arrValues['CONF_ALLOW_CANCELLATION_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Allow_Order_Cancellation_by_Buyers", $this->adminLangId), 'CONF_ALLOW_CANCELLATION_ORDER_STATUS', $orderStatusArr, $allowCancellationOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_allowed_to_place_cancellation_request_on_orders.", $this->adminLangId)."</small>";

                $allowCancellationOrderSelected = (!empty($arrValues['CONF_DIGITAL_ALLOW_CANCELLATION_ORDER_STATUS']))?$arrValues['CONF_DIGITAL_ALLOW_CANCELLATION_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Allow_Order_Cancellation_by_Buyers_On_Digital", $this->adminLangId), 'CONF_DIGITAL_ALLOW_CANCELLATION_ORDER_STATUS', $orderStatusArr, $allowCancellationOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_allowed_to_place_cancellation_request_on_orders.", $this->adminLangId)."</small>";

                $returnExchageOrderSelected = (!empty($arrValues['CONF_RETURN_EXCHANGE_READY_ORDER_STATUS']))?$arrValues['CONF_RETURN_EXCHANGE_READY_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Allow_Return/Exchange", $this->adminLangId), 'CONF_RETURN_EXCHANGE_READY_ORDER_STATUS', $orderStatusArr, $returnExchageOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_allowed_to_place_return/exchange_request_on_orders.", $this->adminLangId)."</small>";

                /* $digitalReturnOrderSelected = (!empty($arrValues['CONF_DIGITAL_RETURN_READY_ORDER_STATUS']))?$arrValues['CONF_DIGITAL_RETURN_READY_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Allow_Return/Exchange_On_Digital",$this->adminLangId),'CONF_DIGITAL_RETURN_READY_ORDER_STATUS',$orderStatusArr,$digitalReturnOrderSelected,array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_allowed_to_place_return/exchange_request_on_orders_of_Digital_Products.",$this->adminLangId)."</small>"; */

                $enableDigitalDownloads = (!empty($arrValues['CONF_ENABLE_DIGITAL_DOWNLOADS']))?$arrValues['CONF_ENABLE_DIGITAL_DOWNLOADS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Enable_Digital_Download", $this->adminLangId), 'CONF_ENABLE_DIGITAL_DOWNLOADS', $orderStatusArr, $enableDigitalDownloads, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_allowed_to_access_their_downloadable_Products.", $this->adminLangId)."</small>";

                /* $purchaseOrderSelected = (!empty($arrValues['CONF_PURCHASE_ORDER_STATUS']))?$arrValues['CONF_PURCHASE_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Purchases_Calculation_(For_Buyers)",$this->adminLangId),'CONF_PURCHASE_ORDER_STATUS',$orderStatusArr,$purchaseOrderSelected,array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_are_considered_in_buyer's_purchase.",$this->adminLangId)."</small>"; */

                $badgeCountOrderSelected = (!empty($arrValues['CONF_BADGE_COUNT_ORDER_STATUS']))?$arrValues['CONF_BADGE_COUNT_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Order_Statuses_to_calculate_badge_count_(For_Admin)", $this->adminLangId), 'CONF_BADGE_COUNT_ORDER_STATUS', $orderStatusArr, $badgeCountOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Order_Statuses_to_calculate_badge_count_for_seller_orders_in_admin_left_navigation_panel", $this->adminLangId)."</small>";

                $productOnOrderStatusesSelected = (!empty($arrValues['CONF_PRODUCT_IS_ON_ORDER_STATUSES']))?$arrValues['CONF_PRODUCT_IS_ON_ORDER_STATUSES']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Products_On_Order_Stage(For_Seller_Inventory_Report)", $this->adminLangId), 'CONF_PRODUCT_IS_ON_ORDER_STATUSES', $orderStatusArr, $productOnOrderStatusesSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Products_are_in_On_Order_Used_on_Seller_Dashboard_Products_Inventory_Stock_Status_Report", $this->adminLangId)."</small>";
                /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Return_Shipping_Charges_to_Customer",$this->adminLangId),'CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER',1,array(),false,0);
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_the_order_starts_displaying_to_Sellers.", $this->adminLangId)."</small>"; */

                /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Tax_Collected_By_Seller",$this->adminLangId),'CONF_TAX_COLLECTED_BY_SELLER',1,array(),false,0);
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_seller_will_be_able_to_collect_tax",$this->adminLangId)."</small>"; */

                /* $frm->addHtml('','Stock','<h3>Stock</h3>');
                $fld = $frm->addRadioButtons('Subtract Stock','CONF_SUBTRACT_STOCK',applicationConstants::getYesNoArr($this->adminLangId),'',array('class'=>'list-inline'));
                $fld->htmlAfterField = "<br><small>Subtract stock when an order is placed.</small>"; */

                break;

            case Configurations::FORM_CART_WISHLIST:
                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Add_favorites_to_wishlist", $this->adminLangId), 'CONF_ADD_FAVORITES_TO_WISHLIST', applicationConstants::getYesNoArr($this->adminLangId), applicationConstants::YES, array('class'=>'list-inline'));
                $fld->htmlAfterField = '<small>'.Labels::getLabel("LBL_On_enabling_this_feature,_buyer_will_have_to_select_or_create_a_wishlist_to_group_his_favorites", $this->adminLangId).'</small>';

                $frm->addHtml('', 'Cart', '<h3>'.Labels::getLabel("LBL_Cart", $this->adminLangId).'</h3>');

                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_On_Payment_Cancel_Maintain_Cart", $this->adminLangId), 'CONF_MAINTAIN_CART_ON_PAYMENT_CANCEL', applicationConstants::getYesNoArr($this->adminLangId), applicationConstants::NO, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Cart_Items_Will_be_retained_on_Cancelling_the_payment", $this->adminLangId)."</small>";

                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_On_Payment_Failure_Maintain_Cart", $this->adminLangId), 'CONF_MAINTAIN_CART_ON_PAYMENT_FAILURE', applicationConstants::getYesNoArr($this->adminLangId), applicationConstants::NO, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Cart_Items_Will_be_retained_on_payment_failure", $this->adminLangId)."</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Reminder_Interval_For_Products_In_Cart_[Days]", $this->adminLangId), 'CONF_REMINDER_INTERVAL_PRODUCTS_IN_CART', '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_interval_in_days_to_send_auto_notification_alert_to_buyer_for_products_in_cart.", $this->adminLangId)."</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Set_Notification_Count_to_be_Sent", $this->adminLangId), 'CONF_SENT_CART_REMINDER_COUNT', '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_how_many_notifications_will_be_sent_to_buyer.", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Wishlist', '<h3>'.Labels::getLabel("LBL_Wishlist", $this->adminLangId).'</h3>');

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Reminder_Interval_For_Products_In_Wishlist_[Days]", $this->adminLangId), 'CONF_REMINDER_INTERVAL_PRODUCTS_IN_WISHLIST', '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_interval_in_days_to_send_auto_notification_alert_to_buyer_for_products_in_Wishlist.", $this->adminLangId)."</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Set_Notification_Count_to_be_Sent", $this->adminLangId), 'CONF_SENT_WISHLIST_REMINDER_COUNT', '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_how_many_notifications_will_be_sent_to_buyer.", $this->adminLangId)."</small>";

                break;

            case Configurations::FORM_COMMISSION:
                /* $frm->addHtml('','Commission','<h3>'.Labels::getLabel("LBL_Commission",$this->adminLangId).'</h3>'); */
                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Maximum_Site_Commission", $this->adminLangId).' ['.$this->siteDefaultCurrencyCode.']', 'CONF_MAX_COMMISSION', '');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_maximum_commission/Fees_that_will_be_charged_on_a_particular_product.", $this->adminLangId)."</small>";

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Commission_charged_including_shipping", $this->adminLangId), 'CONF_COMMISSION_INCLUDING_SHIPPING', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_Commission_charged_including_shipping_charges", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Commission_charged_including_tax", $this->adminLangId), 'CONF_COMMISSION_INCLUDING_TAX', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("LBL_Commission_charged_including_tax_charges", $this->adminLangId).'</small>';
                break;

            case Configurations::FORM_AFFILIATE:
                /* Affiliate Accounts[ */
                $frm->addHtml('', Labels::getLabel('LBL_Affiliate_Accounts', $this->adminLangId), '<h3>'.Labels::getLabel("LBL_Affiliate_Accounts", $this->adminLangId).'</h3>');

                $fld =$frm->addRadioButtons(
                    Labels::getLabel("LBL_Requires_Approval", $this->adminLangId),
                    'CONF_AFFILIATES_REQUIRES_APPROVAL',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Automatically_approve_any_new_affiliates_who_sign_up.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel('LBL_Sign_Up_Commission', $this->adminLangId).' ['.$this->siteDefaultCurrencyCode.']', 'CONF_AFFILIATE_SIGNUP_COMMISSION');
                $fld->requirements()->setInt();
                $fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Affiliate_will_get_commission_when_new_registration_is_received_through_affiliate.', $this->adminLangId).'</small>';

                $cpagesArr = ContentPage::getPagesForSelectBox($this->adminLangId);
                $fld = $frm->addSelectBox(Labels::getLabel('LBL_Affiliate_Terms', $this->adminLangId), 'CONF_AFFILIATE_TERMS_AND_CONDITIONS_PAGE', $cpagesArr, '', array(), '');
                $fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Forces_affiliate_to_agree_to_terms_before_an_affiliate_account_can_be_created.', $this->adminLangId).'</small>';

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referrer_Url/link_Validity_Period", $this->adminLangId), 'CONF_AFFILIATE_REFERRER_URL_VALIDITY');
                $fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Days,_After_Which_Referrer_Url_Is_Expired.(Cookie_Data_on_landed_user)', $this->adminLangId).'</small>';

                $fld =$frm->addRadioButtons(
                    Labels::getLabel("LBL_New_Affiliate_Alert_Mail", $this->adminLangId),
                    'CONF_NOTIFY_ADMIN_AFFILIATE_REGISTRATION',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Send_an_email_to_the_store_owner_when_a_new_affiliate_is_registered.", $this->adminLangId)."</small>";

                $fld = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Email_Verification_After_Registration", $this->adminLangId),
                    'CONF_EMAIL_VERIFICATION_AFFILIATE_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_affiliate_user_need_to_verify_their_email_address", $this->adminLangId)." </small>";

                $fld = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_Sending_Welcome_Mail_After_Registration", $this->adminLangId),
                    'CONF_WELCOME_EMAIL_AFFILIATE_REGISTRATION',
                    1,
                    array(),
                    false,
                    0
                );
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_On_enabling_this_feature,_affiliate_will_receive_a_welcome_e-mail_after_registration.", $this->adminLangId)."</small>";

                /* $fld = $frm->addCheckBox( Labels::getLabel("LBL_Debit_Affiliate_Commission_from_Seller_Account._(Upon_Buying_by_Affiliated_User._Triggered_When_Order_is_marked_as_completed)", $this->adminLangId), 'CONF_DEBIT_AFFILIATE_COMMISSION_FROM_SELLER', 1, array(), false, 0 );
                $fld->htmlAfterField = "<br/><small>".Labels::getLabel("LBL_If_Checked,_then_crediting_of_commission_to_affiliate_will_be_from_seller_account_otherwise_commission_will_be_beared_by_admin_whenever_any_affiliated_user_makes_any_sale_in_system.", $this->adminLangId)."</small>"; */
                /* ] */

                break;

            case Configurations::FORM_REWARD_POINTS:
                $frm->addHtml('', 'Reward', '<h3>'.Labels::getLabel("LBL_Reward_Points", $this->adminLangId).'</h3>');
                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Reward_Points_in", $this->adminLangId).'['.$this->siteDefaultCurrencyCode.']', 'CONF_REWARD_POINT');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_how_many_rewards_points_equal_to", $this->adminLangId)."[".$this->siteDefaultCurrencyCode."]</small>";
                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Reward_Point_Required_To_Use", $this->adminLangId), 'CONF_MIN_REWARD_POINT');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_minimun_reward_points_required_user_to_avail_discount_during_checkout", $this->adminLangId)." .</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Maximum_Reward_Point", $this->adminLangId), 'CONF_MAX_REWARD_POINT');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_maximum_reward_points_limit_to_avail_discount_during_checkout", $this->adminLangId)."</small>";

                $fld11 = $frm->addCheckBox(
                    Labels::getLabel("LBL_Activate_reward_point_on_every_purchase", $this->adminLangId),
                    'CONF_ENABLE_REWARDS_ON_PURCHASE',
                    1,
                    array(),
                    false,
                    0
                );
                $fld11->htmlAfterField = "<br><small>".Labels::getLabel("MSG_Buyer_will_reward_point_on_every_purchase_as_defined_settings", $this->adminLangId)."</small>";

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Reward_Point_Validity", $this->adminLangId), 'CONF_REWARDS_VALIDITY_ON_PURCHASE');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Reward_Point_Validity_in_days_from_date_of_credit", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Birthday_Rewards', '<h3>'.Labels::getLabel("LBL_Birthday_Reward_Points", $this->adminLangId).'</h3>');

                $frm->addRadioButtons(
                    Labels::getLabel("LBL_Enable_birthday_discount", $this->adminLangId),
                    'CONF_ENABLE_BIRTHDAY_DISCOUNT_REWARDS',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Birthday_Reward_Points", $this->adminLangId), 'CONF_BIRTHDAY_REWARD_POINTS');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_User_get_this_reward_points_on_his_birthday.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_reward_Points_Validity", $this->adminLangId), 'CONF_BIRTHDAY_REWARD_POINTS_VALIDITY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Reward_Points_validity_in_days_from_the_date_of_credit._Please_leave_it_blank_if_you_don't_want_reward_points_to_expire.", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Buying Year Rewards', '<h3>'.Labels::getLabel("LBL_Buying_in_an_Year_Reward_Points", $this->adminLangId).'</h3>');

                $fld = $frm->addRadioButtons(
                    Labels::getLabel("LBL_Enable_Module", $this->adminLangId),
                    'CONF_ENABLE_BUYING_IN_AN_YEAR_REWARDS',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Enable_Buying_in_an_year_reward_points_module", $this->adminLangId)."</small>";

                $orderStatusArr = Orders::getOrderProductStatusArr($this->adminLangId);
                $buyingInAnYearOrderSelected = (!empty($arrValues['CONF_BUYING_YEAR_REWARD_ORDER_STATUS']))?$arrValues['CONF_BUYING_YEAR_REWARD_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Buying_Completion_Order_Status", $this->adminLangId), 'CONF_BUYING_YEAR_REWARD_ORDER_STATUS', $orderStatusArr, $buyingInAnYearOrderSelected, array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Set_the_order_status_the_customer's_order_must_reach_before_they_are_considered_completed_and_payment_released_to_Sellers.", $this->adminLangId)."</small>";
                /* $orderStatusArr = Orders::getOrderProductStatusArr($this->adminLangId);
                $buyingInAnYearOrderSelected = (!empty($arrValues['CONF_BUYING_YEAR_REWARD_ORDER_STATUS']))?$arrValues['CONF_BUYING_YEAR_REWARD_ORDER_STATUS']:0;
                $fld = $frm->addCheckBoxes('Buying in an Year Order Status','CONF_BUYING_YEAR_REWARD_ORDER_STATUS[]',$orderStatusArr,$buyingInAnYearOrderSelected,array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>Set the order status the customer's order considered completed to earn rewards points.</small>"; */

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Minimum_buying_value", $this->adminLangId), 'CONF_BUYING_IN_AN_YEAR_MIN_VALUE');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_User_get_this_reward_points_on_min_buying_value_in_an_year.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Reward_Points", $this->adminLangId), 'CONF_BUYING_IN_AN_YEAR_REWARD_POINTS');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_User_get_this_reward_points_on_his_birthday.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Reward_Points_Validity", $this->adminLangId), 'CONF_BUYING_IN_AN_YEAR_REWARD_POINTS_VALIDITY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Reward_Points_validity_in_days_from_the_date_of_credit", $this->adminLangId)."</small>";

                break;

            case Configurations::FORM_REVIEWS:
                $frm->addHtml('', 'Reviews', '<h3>'.Labels::getLabel("LBL_Reviews", $this->adminLangId).'</h3>');

                $reviewStatusArr = SelProdReview::getReviewStatusArr($this->adminLangId);
                $fld =$frm->addSelectBox(
                    Labels::getLabel("LBL_Default_Review_Status", $this->adminLangId),
                    'CONF_DEFAULT_REVIEW_STATUS',
                    $reviewStatusArr,
                    false,
                    array(),
                    ''
                );
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Set_the_default_review_order_status_when_a_new_review_is_placed", $this->adminLangId)."</small>";

                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Allow_Reviews", $this->adminLangId), 'CONF_ALLOW_REVIEWS', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));
                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_New_Review_Alert_Email", $this->adminLangId), 'CONF_REVIEW_ALERT_EMAIL', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));

                break;

            case Configurations::FORM_EMAIL:
                $fld = $frm->addEmailField(Labels::getLabel("LBL_From_Email", $this->adminLangId), 'CONF_FROM_EMAIL');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Required_for_sending_emails", $this->adminLangId)."</small>";
                $fld = $frm->addEmailField(Labels::getLabel("LBL_Reply_to_Email_Address", $this->adminLangId), 'CONF_REPLY_TO_EMAIL');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Required_for_email_headers_-_user_can_reply_to_this_email", $this->adminLangId)."</small>";
                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Send_Email", $this->adminLangId), 'CONF_SEND_EMAIL', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));
                if (FatApp::getConfig('CONF_SEND_EMAIL', FatUtility::VAR_INT, 1)) {
                    $fld->htmlAfterField = '<a href="javascript:void(0)" id="testMail-js">'.Labels::getLabel("LBL_Click_Here", $this->adminLangId).'</a> to test email. '.Labels::getLabel("LBL_This_will_send_Test_Email_to_Site_Owner_Email", $this->adminLangId).' - '.FatApp::getConfig("CONF_SITE_OWNER_EMAIL");
                }
                $fld = $frm->addEmailField(Labels::getLabel("LBL_Contact_Email_Address", $this->adminLangId), 'CONF_CONTACT_EMAIL');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Email_id_to_contact_site_owner", $this->adminLangId)."</small>";
                $frm->addRadioButtons(Labels::getLabel("LBL_Send_SMTP_Email", $this->adminLangId), 'CONF_SEND_SMTP_EMAIL', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));
                $fld = $frm->addTextBox(Labels::getLabel("LBL_SMTP_Host", $this->adminLangId), 'CONF_SMTP_HOST');
                $fld = $frm->addTextBox(Labels::getLabel("LBL_SMTP_Port", $this->adminLangId), 'CONF_SMTP_PORT');
                $fld = $frm->addTextBox(Labels::getLabel("LBL_SMTP_Username", $this->adminLangId), 'CONF_SMTP_USERNAME');
                $fld = $frm->addPasswordField(Labels::getLabel("LBL_SMTP_Password", $this->adminLangId), 'CONF_SMTP_PASSWORD');
                $frm->addRadioButtons(Labels::getLabel("LBL_SMTP_Secure", $this->adminLangId), 'CONF_SMTP_SECURE', applicationConstants::getSmtpSecureArr($this->adminLangId), '', array('class'=>'list-inline'));
                $fld = $frm->addTextarea(Labels::getLabel("LBL_Additional_Alert_E-Mails", $this->adminLangId), 'CONF_ADDITIONAL_ALERT_EMAILS');
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Any_additional_emails_you_want_to_receive_the_alert_email", $this->adminLangId)."</small>";

                break;

            case Configurations::FORM_LIVE_CHAT:
                $fld = $frm->addRadioButtons(
                    Labels::getLabel("LBL_Activate_Live_Chat", $this->adminLangId),
                    'CONF_ENABLE_LIVECHAT',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );
                $fld->htmlAfterField = "<br><small>".Labels::getLabel("LBL_Activate_3rd_Party_Live_Chat.", $this->adminLangId)."</small>";

                $fld = $frm->addTextarea(Labels::getLabel("LBL_Live_Chat_Code", $this->adminLangId), 'CONF_LIVE_CHAT_CODE');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_live_chat_script/code_provided_by_the_3rd_party_API_for_integration.", $this->adminLangId)."</small>";

                break;

            case Configurations::FORM_THIRD_PARTY_API:
                $fld = $frm->addTextBox(Labels::getLabel("LBL_Facebook_APP_ID", $this->adminLangId), 'CONF_FACEBOOK_APP_ID');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_application_ID_used_in_login_and_post.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Facebook_App_Secret", $this->adminLangId), 'CONF_FACEBOOK_APP_SECRET');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_Facebook_secret_key_used_for_authentication_and_other_Facebook_related_plugins_support.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Twitter_APP_KEY", $this->adminLangId), 'CONF_TWITTER_API_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_application_ID_used_in_post.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Twitter_App_Secret", $this->adminLangId), 'CONF_TWITTER_API_SECRET');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_Twitter_secret_key_used_for_authentication_and_other_Twitter_related_plugins_support.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Google_Developer_Key", $this->adminLangId), 'CONF_GOOGLEPLUS_DEVELOPER_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_google_developer_key.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Google_Client_ID", $this->adminLangId), 'CONF_GOOGLEPLUS_CLIENT_ID');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_google_Client_Id_used_to_Login.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Google_Client_Secret", $this->adminLangId), 'CONF_GOOGLEPLUS_CLIENT_SECRET');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_Google_client_secret_key_used_for_authentication.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Google_Push_Notification_API_KEY", $this->adminLangId), 'CONF_GOOGLE_PUSH_NOTIFICATION_API_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_api_key_used_in_push_notifications.", $this->adminLangId)."</small>";


                $frm->addHtml('', 'Engagespot', '<h3>'.Labels::getLabel("LBL_Engagespot_Push_Notifications_(WEB)", $this->adminLangId).'</h3>');

                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Enable_Engagespot", $this->adminLangId), 'CONF_ENABLE_ENGAGESPOT_PUSH_NOTIFICATION', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));

                $fld = $frm->addTextBox(Labels::getLabel("LBL_API_Key", $this->adminLangId), 'CONF_ENGAGESPOT_API_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_API_key_provided_by_Engagespot.", $this->adminLangId)."</small>";

                $fld = $frm->addTextarea(Labels::getLabel("LBL_Engagespot_Code", $this->adminLangId), 'CONF_ENGAGESPOT_PUSH_NOTIFICATION_CODE');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_code_provided_by_the_engagespot_for_integration.", $this->adminLangId)."</small>";



                $frm->addHtml('', 'GoogleMap', '<h3>'.Labels::getLabel("LBL_Google_Map_API", $this->adminLangId).'</h3>');
                $fld = $frm->addTextBox(Labels::getLabel("LBL_Google_Map_API_Key", $this->adminLangId), 'CONF_GOOGLEMAP_API_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_Google_map_api_key_used_to_get_user_current_location.", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Newsletter', '<h3>'.Labels::getLabel("LBL_Newsletter_Subscription", $this->adminLangId).'</h3>');

                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Activate_Newsletter_Subscription", $this->adminLangId), 'CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));

                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Email_Marketing_System", $this->adminLangId), 'CONF_NEWSLETTER_SYSTEM', applicationConstants::getNewsLetterSystemArr($this->adminLangId), '', array('class'=>'list-inline'));
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Please_select_the_system_you_wish_to_use_for_email_marketing.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Mailchimp_Key", $this->adminLangId), 'CONF_MAILCHIMP_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_Mailchimp's_application_key_used_in_subscribe_and_send_newsletters.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Mailchimp_List_ID", $this->adminLangId), 'CONF_MAILCHIMP_LIST_ID');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_Mailchimp's_subscribers_List_ID.", $this->adminLangId)."</small>";

                $fld = $frm->addTextarea(Labels::getLabel("LBL_Aweber_Signup_Form_Code", $this->adminLangId), 'CONF_AWEBER_SIGNUP_CODE');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Enter_the_newsletter_signup_code_received_from_Aweber", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Analytics', '<h3>'.Labels::getLabel("LBL_Google_Analytics", $this->adminLangId).'</h3>');
                $fld = $frm->addTextBox(Labels::getLabel("LBL_Client_Id", $this->adminLangId), 'CONF_ANALYTICS_CLIENT_ID');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_application_Client_Id_used_in_Analytics_dashboard.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Secret_Key", $this->adminLangId), 'CONF_ANALYTICS_SECRET_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_application_secret_key_used_in_Analytics_dashboard.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Analytics_Id", $this->adminLangId), 'CONF_ANALYTICS_ID');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_Google_Analytics_ID._Ex._UA-xxxxxxx-xx.", $this->adminLangId)."</small>";

                $accessToken = FatApp::getConfig("CONF_ANALYTICS_ACCESS_TOKEN", FatUtility::VAR_STRING, '');
                include_once CONF_INSTALLATION_PATH . 'library/analytics/analyticsapi.php';
                $analyticArr = array(
                'clientId' => FatApp::getConfig("CONF_ANALYTICS_CLIENT_ID", FatUtility::VAR_STRING, ''),
                'clientSecretKey' => FatApp::getConfig("CONF_ANALYTICS_SECRET_KEY", FatUtility::VAR_STRING, ''),
                'redirectUri' => CommonHelper::generateFullUrl('configurations', 'redirect', array(), '', false),
                'googleAnalyticsID' => FatApp::getConfig("CONF_ANALYTICS_ID", FatUtility::VAR_STRING, '')
                );
                try {
                    $analytics = new Ykart_analytics($analyticArr);
                    $authUrl = $analytics->buildAuthUrl();
                } catch (exception $e) {
                    $authUrl = '';
                    //Message::addErrorMessage($e->getMessage());
                }

                if ($authUrl) {
                    $authenticateText = ($accessToken == '')?'Authenticate':'Re-Authenticate';
                    $fld = $frm->addHTML('', 'accessToken', 'Please save your settings & <a href="'.$authUrl.'" >click here</a> to '.$authenticateText.' settings.<div class="gap"></div>', '', 'class="medium"');
                } else {
                    $fld=$frm->addHTML('', 'accessToken', 'Please configure your settings and then authenticate them', '', 'class="medium"');
                }

                $frm->addHtml('', 'Analytics', '<h3>'.Labels::getLabel("LBL_Google_Recaptcha", $this->adminLangId).'</h3>');
                $fld = $frm->addTextBox(Labels::getLabel("LBL_Site_Key", $this->adminLangId), 'CONF_RECAPTCHA_SITEKEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_application_Site_key_used_for_Google_Recaptcha.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Secret_Key", $this->adminLangId), 'CONF_RECAPTCHA_SECRETKEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_is_the_application_Secret_key_used_for_Google_Recaptcha.", $this->adminLangId)."</small>";

                /* $frm->addHtml('','ShipStation','<h3>'.Labels::getLabel("LBL_Shipstation_shipping_Api",$this->adminLangId).'</h3>');
                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Enable_ShipStation_Api",$this->adminLangId),'CONF_SHIPSTATION_API_ENABLED',
                applicationConstants::getYesNoArr($this->adminLangId),'',array('class'=>'list-inline'));
                $fld = $frm->addTextBox(Labels::getLabel("LBL_Shipstation_Api_key",$this->adminLangId),'CONF_SHIPSTATION_API_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Please_enter_your_shipstation_api_Api_Key_here.",$this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Shipstation_Api_Secret_key",$this->adminLangId),'CONF_SHIPSTATION_API_SECRET_KEY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Please_enter_your_shipstation_api_Secret_Key_here.",$this->adminLangId)."</small>"; */

                break;
            case Configurations::FORM_REFERAL:
                $fld = $frm->addRadioButtons(
                    Labels::getLabel("LBL_Enable_Referral_Module", $this->adminLangId),
                    'CONF_ENABLE_REFERRER_MODULE',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );

                $fld = $frm->addIntegerField(Labels::getLabel("LBL_Referrer_Url/Link_Validity_Period", $this->adminLangId), 'CONF_REFERRER_URL_VALIDITY');
                $fld->requirements()->setIntPositive();
                $string = Labels::getLabel("LBL_Days,_after_which_Referrer_Url_is_Expired.", $this->adminLangId);
                $fld->htmlAfterField = "<small>". $string ."</small>";

                $frm->addHtml('', 'Rewards', '<h3>'.Labels::getLabel("LBL_Reward_Benefits_on_Registration", $this->adminLangId).'</h3>');

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referrer_Reward_Points", $this->adminLangId), 'CONF_REGISTRATION_REFERRER_REWARD_POINTS');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Referrers_get_this_reward_points_when_their_referrals_(friends)_will_register.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referrer_Reward_Points_Validity", $this->adminLangId), 'CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Rewards_points_validity_in_days_from_the_date_of_credit", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referral_Reward_Points", $this->adminLangId), 'CONF_REGISTRATION_REFERRAL_REWARD_POINTS');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Referrals_get_this_reward_points_when_they_register_through_referrer.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referral_Reward_Points_Validity", $this->adminLangId), 'CONF_REGISTRATION_REFERRAL_REWARD_POINTS_VALIDITY');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Rewards_points_validity_in_days_from_the_date_of_credit", $this->adminLangId)."</small>";

                $frm->addHtml('', 'Rewards', '<h3>'.Labels::getLabel("LBL_Reward_Benefits_on_First_Purchase", $this->adminLangId).'</h3>');

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referrer_Reward_Points", $this->adminLangId), 'CONF_SALE_REFERRER_REWARD_POINTS');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Referrers_get_this_reward_points_when_their_referrals_(friends)_will_make_first_purchase.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referrer_Reward_Points_Validity", $this->adminLangId), 'CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Rewards_points_validity_in_days_from_the_date_of_credit", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Referral_Reward_Points", $this->adminLangId), 'CONF_SALE_REFERRAL_REWARD_POINTS');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Referrals_get_this_reward_points_when_they_will_make_first_purchase_through_their_referrers.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Rewards_points_validity_in_days", $this->adminLangId), 'CONF_SALE_REFERRAL_REWARD_POINTS_VALIDITY');
                $fld->requirements()->setIntPositive();
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_NOTE:Rewards_points_validity_in_days_from_the_date_of_credit", $this->adminLangId)."</small>";

                /* $fld = $frm->addTextarea('Live Chat Code','CONF_LIVE_CHAT_CODE');
                $fld->htmlAfterField = "<small>This is the live chat script/code provided by the 3rd party API for
                integration.</small>"; */

                break;

            case Configurations::FORM_DISCOUNT:
                $frm->addHtml('', 'Birthday Discount', '<h3>'.Labels::getLabel("LBL_First_time_buyers_discount_coupon", $this->adminLangId).'</h3>');

                $frm->addRadioButtons(
                    Labels::getLabel("LBL_Enable_1st_time_buyers_discount", $this->adminLangId),
                    'CONF_ENABLE_FIRST_TIME_BUYER_DISCOUNT',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );

                $percentageFlatArr = applicationConstants::getPercentageFlatArr($this->adminLangId);
                $frm->addSelectBox(Labels::getLabel("LBL_Discount_in", $this->adminLangId), 'CONF_FIRST_TIME_BUYER_COUPON_IN_PERCENT', $percentageFlatArr, '', array(), '');
                $percentageFlatArr = applicationConstants::getPercentageFlatArr($this->adminLangId);
                $frm->addTextBox(Labels::getLabel("LBL_Discount_value", $this->adminLangId), 'CONF_FIRST_TIME_BUYER_COUPON_DISCOUNT_VALUE');

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Minimum_order_value", $this->adminLangId), 'CONF_FIRST_TIME_BUYER_COUPON_MIN_ORDER_VALUE');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Minimum_order_value_on_which_the_coupon_can_be_applied.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Max_Discount_Value", $this->adminLangId), 'CONF_FIRST_TIME_BUYER_COUPON_MAX_DISCOUNT_VALUE');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Max_discount_value_user_can_get_by_using_this_coupon.", $this->adminLangId)."</small>";

                $fld = $frm->addTextBox(Labels::getLabel("LBL_Discount_Coupon_Validity", $this->adminLangId), 'CONF_FIRST_TIME_BUYER_COUPON_VALIDITY');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Coupon_validity_in_days_from_the_date_of_credit", $this->adminLangId)."</small>";

                break;
            case Configurations::FORM_SUBSCRIPTION:
                $enable_subscption_module_fld = $frm->addRadioButtons(
                    Labels::getLabel('LBL_Enable_Subscription_Module', $this->adminLangId),
                    'CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );
                $enable_subscption_module_fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Seller_Needs_to_Purchase_the_subscrption_before_listing_products', $this->adminLangId).'</small>';
                $enable_subscption_module_fld = $frm->addRadioButtons(
                    Labels::getLabel('LBL_ENABLE_ADJUST_AMOUNT', $this->adminLangId),
                    'CONF_ENABLE_ADJUST_AMOUNT_CHANGE_PLAN',
                    applicationConstants::getYesNoArr($this->adminLangId),
                    '',
                    array('class'=>'list-inline')
                );
                $enable_subscption_module_fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Subscription_Payment_will_be_adjusted_While_Upgrading/downgrading_plan', $this->adminLangId).'</small>';

                $orderSubscriptionStatusArr = Orders::getOrderSubscriptionStatusArr($this->adminLangId);
                //$subscriptionOrderSelected = (!empty($arrValues['CONF_SUBSCRIPTION_ORDER_STATUS']))?$arrValues['CONF_SUBSCRIPTION_ORDER_STATUS']:0;
                $fld = $frm->addTextBox(Labels::getLabel("LBL_Reminder_Email_Before_Subscription_Expire_Days", $this->adminLangId), 'CONF_BEFORE_EXIPRE_SUBSCRIPTION_REMINDER_EMAIL_DAYS');
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_Before_How_many_Days_email_needs_to_be_sent_to_user_before_ending_subscription.", $this->adminLangId)."</small>";
                //$fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Subscription_Order_Statuses",$this->adminLangId),'CONF_SUBSCRIPTION_ORDER_STATUS',$orderSubscriptionStatusArr,$subscriptionOrderSelected,array('class'=>'list-inline'));

                $subscriptionSellerOrderSelected = (!empty($arrValues['CONF_SELLER_SUBSCRIPTION_STATUS']))?$arrValues['CONF_SELLER_SUBSCRIPTION_STATUS']:0;
                $fld = $frm->addCheckBoxes(Labels::getLabel("LBL_Seller_Subscription_Statuses", $this->adminLangId), 'CONF_SELLER_SUBSCRIPTION_STATUS', $orderSubscriptionStatusArr, $subscriptionSellerOrderSelected, array('class'=>'list-inline'));
                break;

            case Configurations::FORM_SYSTEM:
                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Auto_Close_System_Messages", $this->adminLangId), 'CONF_AUTO_CLOSE_SYSTEM_MESSAGES', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));
                $fld->addFieldTagAttribute("onchange", "changedMessageAutoCloseSetting(this.value);");

                $fld = $frm->addTextBox(Labels::getLabel('LBL_TIME_FOR_AUTO_CLOSE_MESSAGES', $this->adminLangId), 'CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("LBL_NOTE:_After_how_much_seconds_system_message_should_be_close", $this->adminLangId).'.</small>';
                $fld->requirements()->setInt();
                break;
            case Configurations::FORM_PPC:
                $fld = $frm->addTextBox(Labels::getLabel('LBL_Minimum_Wallet_Balance', $this->adminLangId), 'CONF_PPC_MIN_WALLET_BALANCE');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_Minimum_wallet_balance_to_start_promotion", $this->adminLangId).'</small>';

                /* $fld = $frm->addTextBox( Labels::getLabel('LBL_Wallet_Balance_Alert',$this->adminLangId), 'CONF_PPC_WALLET_BALANCE_ALERT' );
                $fld->htmlAfterField = Labels::getLabel("MSG_Send_Email_if_wallet_balance_goes_below",$this->adminLangId); */

                $fld = $frm->addTextBox(Labels::getLabel('LBL_Days_Interval_to_Charge_Wallet', $this->adminLangId), 'CONF_PPC_WALLET_CHARGE_DAYS_INTERVAL');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_Days_Interval_to_Charge_Wallet", $this->adminLangId).'</small>';

                $fld = $frm->addTextBox(Labels::getLabel('LBL_Cost_Per_Click_(product)', $this->adminLangId), 'CONF_CPC_PRODUCT');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_PPC_cost_per_click_for_Product", $this->adminLangId).'</small>';

                $fld = $frm->addTextBox(Labels::getLabel('LBL_Cost_Per_Click_(shop)', $this->adminLangId), 'CONF_CPC_SHOP');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_PPC_cost_per_click_for_shop", $this->adminLangId).'</small>';

                $fld = $frm->addTextBox(Labels::getLabel('LBL_Cost_Per_Click_(slide)', $this->adminLangId), 'CONF_CPC_SLIDES');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_PPC_cost_per_click_for_slide", $this->adminLangId).'</small>';

                /* $fld = $frm->addTextBox( Labels::getLabel('LBL_Cost_Per_Click_(banner)',$this->adminLangId), 'CONF_CPC_BANNER' );
                $fld->htmlAfterField = Labels::getLabel("MSG_PPC_cost_per_click_for_banner",$this->adminLangId); */

                $fld = $frm->addTextBox(Labels::getLabel('LBL_PPC_products_count_home_page', $this->adminLangId), 'CONF_PPC_PRODUCTS_HOME_PAGE');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_how_many_PPC_products_shown_on_home_page", $this->adminLangId).'</small>';

                $fld = $frm->addTextBox(Labels::getLabel('LBL_PPC_shops_count_home_page', $this->adminLangId), 'CONF_PPC_SHOPS_HOME_PAGE');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_how_many_PPC_shops_shown_on_home_page", $this->adminLangId).'</small>';
                $fld = $frm->addTextBox(Labels::getLabel('LBL_PPC_slides_count_home_page', $this->adminLangId), 'CONF_PPC_SLIDES_HOME_PAGE');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_how_many_PPC_slides_shown_on_home_page", $this->adminLangId).'</small>';
                $fld = $frm->addTextBox(Labels::getLabel('LBL_PPC_Clicks_Count_Time_Interval(Minutes)', $this->adminLangId), 'CONF_PPC_CLICK_COUNT_TIME_INTERVAL');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("MSG_Set_time_interval_to_calculate_no._of_click_from_one_user_for_each_promotion", $this->adminLangId).'</small>';

                break;
            case Configurations::FORM_SERVER:
                $fld = $frm->addRadioButtons(Labels::getLabel("LBL_Use_SSL", $this->adminLangId), 'CONF_USE_SSL', applicationConstants::getYesNoArr($this->adminLangId), '', array('class'=>'list-inline'));
                $fld->htmlAfterField = '<small>'.Labels::getLabel("LBL_NOTE:_To_use_SSL,_check_with_your_host_if_a_SSL_certificate_is_installed_and_enable_it_from_here.", $this->adminLangId).'.</small>';

                $fld = $frm->addSelectBox(Labels::getLabel("LBL_Enable_Maintenance_Mode", $this->adminLangId), 'CONF_MAINTENANCE', applicationConstants::getYesNoArr($this->adminLangId), '', array(), '');
                $fld->htmlAfterField = '<small>'.Labels::getLabel("LBL_NOTE:_Enable_Maintenance_Mode_Text", $this->adminLangId).'.</small>';

                break;

            case Configurations::FORM_IMPORT_EXPORT:
                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_brand_id_instead_of_brand_identifier", $this->adminLangId), 'CONF_USE_BRAND_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_brand_id_instead_of_brand_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_category_id_instead_of_category_identifier", $this->adminLangId), 'CONF_USE_CATEGORY_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_category_id_instead_of_category_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_catalog_product_id_instead_of_catalog_product_identifier", $this->adminLangId), 'CONF_USE_PRODUCT_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_catalog_product_id_instead_of_catalog_product_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_user_id_instead_of_username", $this->adminLangId), 'CONF_USE_USER_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_user_id_instead_of_username_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_id_instead_of_option_identifier", $this->adminLangId), 'CONF_USE_OPTION_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_option_id_instead_of_option_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_value_id_instead_of_option_identifier", $this->adminLangId), 'CONF_OPTION_VALUE_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_option_value_id_instead_of_option_value_identifier_in_worksheets", $this->adminLangId).'</small>';

                /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_type_id_instead_of_option_type_identifier",$this->adminLangId),'CONF_USE_OPTION_TYPE_ID',1,array(),false,0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_option_type_id_instead_of_option_type_identifier_in_worksheets",$this->adminLangId).'</small>'; */

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_tag_id_instead_of_tag_identifier", $this->adminLangId), 'CONF_USE_TAG_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_tag_id_instead_of_tag_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_tax_id_instead_of_tax_identifier", $this->adminLangId), 'CONF_USE_TAX_CATEOGRY_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_tax_category_id_instead_of_tax_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_product_type_id_instead_of_product_type_identifier", $this->adminLangId), 'CONF_USE_PRODUCT_TYPE_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_product_type_id_instead_of_product_type_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_dimension_unit_id_instead_of_dimension_unit_identifier", $this->adminLangId), 'CONF_USE_DIMENSION_UNIT_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_dimension_unit_id_instead_of_dimension_unit_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_weight_unit_id_instead_of_weight_unit_identifier", $this->adminLangId), 'CONF_USE_WEIGHT_UNIT_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_weight_unit_id_instead_of_weight_unit_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_lang_id_instead_of_lang_code", $this->adminLangId), 'CONF_USE_LANG_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_language_id_instead_of_language_code_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_currency_id_instead_of_currency_code", $this->adminLangId), 'CONF_USE_CURRENCY_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_currency_id_instead_of_currency_code_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_Product_condition_id_instead_of_condition_identifier", $this->adminLangId), 'CONF_USE_PROD_CONDITION_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_Product_condition_id_instead_of_condition_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_persent_or_flat_condition_id_instead_of_identifier", $this->adminLangId), 'CONF_USE_PERSENT_OR_FLAT_CONDITION_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_persent_or_flat_condition_id_instead_of_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_country_id_instead_of_country_code", $this->adminLangId), 'CONF_USE_COUNTRY_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_country_id_instead_of_country_code_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_state_id_instead_of_state_identifier", $this->adminLangId), 'CONF_USE_STATE_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_state_id_instead_of_state_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_policy_point_id_instead_of_policy_point_identifier", $this->adminLangId), 'CONF_USE_POLICY_POINT_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_policy_point_id_instead_of_policy_point_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_company_id_instead_of_shipping_company_identifier", $this->adminLangId), 'CONF_USE_SHIPPING_COMPANY_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_shipping_company_id_instead_of_shipping_company_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_policy_point_type_id_instead_of_policy_point_type_identifier", $this->adminLangId), 'CONF_USE_POLICY_POINT_TYPE_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_policy_point_type_id_instead_of_policy_point_type_identifier_in_worksheets", $this->adminLangId).'</small>';

                /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_method_id_instead_of_shipping_method_identifier",$this->adminLangId),'CONF_USE_SHIPPING_METHOD_ID',1,array(),false,0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_shipping_method_id_instead_of_shipping_method_identifier_in_worksheets",$this->adminLangId).'</small>'; */

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_duration_id_instead_of_shipping_duration_identifier", $this->adminLangId), 'CONF_USE_SHIPPING_DURATION_ID', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_shipping_duration_id_instead_of_shipping_duration_identifier_in_worksheets", $this->adminLangId).'</small>';

                $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_1_for_yes_0_for_no", $this->adminLangId), 'CONF_USE_O_OR_1', 1, array(), false, 0);
                $fld->htmlAfterField = '<br><small>'.Labels::getLabel("MSG_Use_1_for_yes_0_for_no_for_status_type_data", $this->adminLangId).'</small>';
                break;
        }
        $frm->addHiddenField('', 'form_type', $type);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->adminLangId));
        return $frm;
    }

    private function getLangForm($type, $langId)
    {
        $frm = new Form('frmConfiguration');

        switch ($type) {
            case Configurations::FORM_GENERAL:
                $frm->addTextBox(Labels::getLabel("LBL_Site_Name", $this->adminLangId), 'CONF_WEBSITE_NAME_'.$langId);
                $frm->addTextBox(Labels::getLabel("LBL_Site_Owner", $this->adminLangId), 'CONF_SITE_OWNER_'.$langId);
                $frm->addTextarea(Labels::getLabel("LBL_ADDRESS", $this->adminLangId), 'CONF_ADDRESS_'.$langId);
                $frm->addTextarea(Labels::getLabel('LBL_Cookies_Policies_Text', $this->adminLangId), 'CONF_COOKIES_TEXT_'.$langId);
                break;

            case Configurations::FORM_EMAIL:
                $frm->addTextBox(Labels::getLabel("LBL_From_Name", $this->adminLangId), 'CONF_FROM_NAME_'.$langId);
                break;

            case Configurations::FORM_SHARING:
                $frm->addHtml('', 'ShareAndEarn', '<h3>'.Labels::getLabel('LBL_Share_and_Earn_Settings', $this->adminLangId).'</h3>');
                $fld = $frm->addTextbox(Labels::getLabel("LBL_Facebook_Post_Title", $this->adminLangId), 'CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE_'.$langId);
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_title_shared_on_facebook", $this->adminLangId)."</small>";
                $fld = $frm->addTextbox(Labels::getLabel("LBL_Facebook_Post_Caption", $this->adminLangId), 'CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION_'.$langId);
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_caption_shared_on_facebook", $this->adminLangId)."</small>";
                $fld = $frm->addTextarea(Labels::getLabel("LBL_Facebook_Post_Description", $this->adminLangId), 'CONF_SOCIAL_FEED_FACEBOOK_POST_DESCRIPTION_'.$langId);
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_description_shared_on_facebook", $this->adminLangId)."</small>";
                $fld = $frm->addTextarea(Labels::getLabel("LBL_Twitter_Post_Description", $this->adminLangId), 'CONF_SOCIAL_FEED_TWITTER_POST_TITLE'.$langId);
                $fld->htmlAfterField = "<small>".Labels::getLabel("LBL_This_description_shared_on_twitter", $this->adminLangId)."</small>";
                break;

            case Configurations::FORM_MEDIA:
                $ul = $frm->addHtml('', 'MediaGrids', '<ul class="grids--onethird">');

                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Admin_Logo', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';

                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_ADMIN_LOGO, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'siteAdminLogo', array($langId)).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeSiteAdminLogo('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="admin_logo" class="logoFiles-Js btn-xs" id="admin_logo" data-file_type='.AttachedFile::FILETYPE_ADMIN_LOGO.' value="Upload file"><small>Dimensions 142*45</small></li>';


                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Desktop_Logo', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'siteLogo', array($langId), CONF_WEBROOT_FRONT_URL).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeDesktopLogo('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="front_logo" class="logoFiles-Js btn-xs" id="front_logo" data-file_type='.AttachedFile::FILETYPE_FRONT_LOGO.' value="Upload file"><small>Dimensions 168*37</small></li>';


                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Email_Template_Logo', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_EMAIL_LOGO, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'emailLogo', array($langId), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeEmailLogo('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="email_logo" class="logoFiles-Js btn-xs" id="email_logo" data-file_type='.AttachedFile::FILETYPE_EMAIL_LOGO.' value="Upload file"><small>Dimensions 168*37</small></li>';


                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Website_Favicon', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_FAVICON, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'favicon', array($langId), CONF_WEBROOT_FRONT_URL).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeFavicon('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="favicon" class="logoFiles-Js btn-xs" id="favicon" data-file_type='.AttachedFile::FILETYPE_FAVICON.' value="Upload file"></li>';


                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Social_Feed_Image', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'socialFeed', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeSocialFeedImage('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="social_feed_image" class="logoFiles-Js btn-xs" id="social_feed_image" data-file_type='.AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE.' value="Upload file"><small>Dimensions 160*240</small></li>';



                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Payment_Page_Logo', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'paymentPageLogo', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removePaymentPageLogo('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="payment_page_logo" class="logoFiles-Js btn-xs" id="payment_page_logo" data-file_type='.AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO.' value="Upload file"><small>Dimensions 168*37</small></li>';


                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Watermark_Image', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_WATERMARK_IMAGE, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'watermarkImage', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeWatermarkImage('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="watermark_image" class="logoFiles-Js btn-xs" id="watermark_image" data-file_type='.AttachedFile::FILETYPE_WATERMARK_IMAGE.' value="Upload file"><small>Dimensions 168*37</small></li>';


                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Apple_Touch_Icon', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_APPLE_TOUCH_ICON, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'appleTouchIcon', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeAppleTouchIcon('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="apple_touch_icon" class="logoFiles-Js btn-xs" id="apple_touch_icon" data-file_type='.AttachedFile::FILETYPE_APPLE_TOUCH_ICON.' value="Upload file"></li>';


                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Mobile_Logo', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_MOBILE_LOGO, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'mobileLogo', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeMobileLogo('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="mobile_logo" class="logoFiles-Js btn-xs" id="mobile_logo" data-file_type='.AttachedFile::FILETYPE_MOBILE_LOGO.' value="Upload file"><small>Dimensions 168*37</small></li>';
                //
                // $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Categories_Background_Image', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';
                //
                //
                // if(AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_COLLECTION_BG_IMAGE, 0, 0, $langId) ) {
                //     $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'CategoryCollectionBgImage', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeCollectionBgImage('.$langId.')" ><i class="ion-close-round"></i></a>';
                // }
                //
                // $ul->htmlAfterField .= ' </div></div><input type="button" name="category_collection" class="logoFiles-Js btn-xs" id="category_collection" data-file_type='.AttachedFile::FILETYPE_CATEGORY_COLLECTION_BG_IMAGE.' value="Upload file"><small>Dimensions 1000*1000</small></li>';
                //
                // $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Brand_Background_Image', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';
                //
                //
                // if(AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_COLLECTION_BG_IMAGE, 0, 0, $langId) ) {
                //     $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'BrandCollectionBgImage', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeBrandCollectionBgImage('.$langId.')" ><i class="ion-close-round"></i></a>';
                // }
                //
                // $ul->htmlAfterField .= ' </div></div><input type="button" name="brand_collection" class="logoFiles-Js btn-xs" id="brand_collection" data-file_type='.AttachedFile::FILETYPE_BRAND_COLLECTION_BG_IMAGE.' value="Upload file"><small>Dimensions 1000*1000</small></li>';

                $ul->htmlAfterField .= '<li>'.Labels::getLabel('LBL_Select_Invoice_Logo', $this->adminLangId).'<div class="logoWrap"><div class="uploaded--image">';


                if (AttachedFile::getAttachment(AttachedFile::FILETYPE_INVOICE_LOGO, 0, 0, $langId)) {
                    $ul->htmlAfterField .= '<img src="'.CommonHelper::generateFullUrl('Image', 'invoiceLogo', array($langId , 'THUMB'), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeInvoiceLogo('.$langId.')" ><i class="ion-close-round"></i></a>';
                }

                $ul->htmlAfterField .= ' </div></div><input type="button" name="invoice_logo" class="logoFiles-Js btn-xs" id="invoice_logo" data-file_type='.AttachedFile::FILETYPE_INVOICE_LOGO.' value="Upload file"><small>Dimensions 168*37</small></li>';

                $ul->htmlAfterField .='</ul>';
                break;

            case Configurations::FORM_PPC:
                $frm->addTextBox(Labels::getLabel('LBL_PPC_products_home_page_caption', $this->adminLangId), 'CONF_PPC_PRODUCTS_HOME_PAGE_CAPTION_'.$langId);
                $frm->addTextBox(Labels::getLabel('LBL_PPC_shops_home_page_caption', $this->adminLangId), 'CONF_PPC_SHOPS_HOME_PAGE_CAPTION_'.$langId);
                break;
            case Configurations::FORM_SERVER:
                $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Maintenance_Text', $this->adminLangId), 'CONF_MAINTENANCE_TEXT_'.$langId);
                $fld->requirements()->setRequired(true);
                break;
        }

        $frm->addHiddenField('', 'lang_id', $langId);
        $frm->addHiddenField('', 'form_type', $type);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->adminLangId));
        return $frm;
    }

    public function testEmail()
    {
        try {
            if (EmailHandler::sendMailTpl(FatApp::getConfig('CONF_SITE_OWNER_EMAIL'), 'test_email', $this->adminLangId)) {
                FatUtility::dieJsonSuccess("Mail sent to - ".FatApp::getConfig('CONF_SITE_OWNER_EMAIL'));
            }
        } catch (Exception $e) {
            FatUtility::dieJsonError($e->getMessage());
        }
    }

    public function displayDateTime()
    {
        $post = FatApp::getPostedData();
        $timeZone = $post['time_zone'];
        $dateTime = CommonHelper::currentDateTime(null, true, null, $timeZone);
        $this->set("dateTime", $dateTime);
        $this->_template->render(false, false, 'json-success.php');
    }
}
