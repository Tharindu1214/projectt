<?php
class AddressesController extends LoggedUserController
{
    public function __construct($action)
    {
        parent::__construct($action);
        //$this->set('bodyClass','is--dashboard');
    }

    public function setUpAddress()
    {
        $frm = $this->getUserAddressForm($this->siteLangId);
        $post = FatApp::getPostedData();
        $post['ua_phone'] = !empty($post['ua_phone']) ? ValidateElement::convertPhone($post['ua_phone']) : '';
        $markAsDefault = (!empty($post['isDefault']) && 0 < FatUtility::int($post['isDefault']) ? true : false);

        if (empty($post)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $ua_state_id = FatUtility::int($post['ua_state_id']);
        $ua_city_id = FatUtility::int($post['ua_city']);
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        // get City name from id
        $city = Cities::getCityNameById($ua_city_id,$this->siteLangId);
        if($city){
            $cityName = $city[0]['city_name'];
        }else{
            $cityName = '';
        }
       
        $post['ua_state_id'] = $ua_state_id;
        $post['ua_city_id'] = $ua_city_id;
        $post['ua_city'] = $cityName;

        $ua_id = FatUtility::int($post['ua_id']);
        unset($post['ua_id']);

        $addressObj = new UserAddress($ua_id);

        $data_to_be_save = $post;
        $data_to_be_save['ua_user_id'] = UserAuthentication::getLoggedUserId();

        $addressObj->assignValues($data_to_be_save, true);
        if (!$addressObj->save()) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($addressObj->getError());
            }
            Message::addErrorMessage($addressObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        if (0 <= $ua_id) {
            $ua_id = $addressObj->getMainTableRecordId();
        }

        if (true === $markAsDefault) {
            $this->markAsDefault($ua_id);
        }


        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('data', array('ua_id' => $ua_id));
            $this->_template->render();
        }
        $this->set('ua_id', $ua_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setDefault()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $ua_id = FatUtility::int($post['id']);
        $this->markAsDefault($ua_id);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDefault($ua_id)
    {
        if (1 > $ua_id) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $addressDetail = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), 0, 0, $ua_id);

        if (empty($addressDetail)) {
            $message = Labels::getLabel('MSG_Invalid_request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $updateArray = array('ua_is_default'=>0);
        $whr = array('smt'=>'ua_user_id = ?', 'vals'=>array(UserAuthentication::getLoggedUserId()));

        if (!FatApp::getDb()->updateFromArray(UserAddress::DB_TBL, $updateArray, $whr)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $addressObj = new UserAddress($ua_id);
        $data = array(
        'ua_id'=>$ua_id,
        'ua_is_default'=>1,
        'ua_user_id'=>UserAuthentication::getLoggedUserId(),
        );

        $addressObj->assignValues($data, true);
        if (!$addressObj->save()) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($addressObj->getError());
            }
            Message::addErrorMessage($addressObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function deleteRecord()
    {
        $ua_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if (1 > $ua_id) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $userId = UserAuthentication::getLoggedUserId();
        $userDefaultAddress = UserAddress::getDefaultAddressId($userId);
        if ($userDefaultAddress['ua_id'] == $ua_id) {
            $message = Labels::getLabel('MSG_Select_another_address', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(UserAddress::DB_TBL, array('smt' => 'ua_user_id = ? AND ua_id = ?', 'vals' => array($userId, $ua_id)))) {
            LibHelper::dieJsonError($db->getError());
        }
        $msg = Labels::getLabel('MSG_Removed_Successfully', $this->siteLangId);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('msg', $msg);
            $this->_template->render();
        }
        FatUtility::dieJsonSuccess($msg);
    }
}
