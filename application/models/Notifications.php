<?php
class Notifications extends MyAppModel
{
    const DB_TBL = 'tbl_user_notifications';
    const DB_TBL_PREFIX = 'unotification_';


    public function __construct($unotificationId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $unotificationId);
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'unt');
        return $srch;
    }

    public function addNotification($data, $pushNotification = true)
    {
        $userId = FatUtility::int($data['unotification_user_id']);
        if ($userId < 1) {
            trigger_error(Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId), E_USER_ERROR);
            return false;
        }
        $data['unotification_date'] = date('Y-m-d H:i:s');
        $this->assignValues($data);
        if (!$this->save()) {
            return false;
        }


        if (true === $pushNotification) {
            $google_push_notification_api_key = FatApp::getConfig("CONF_GOOGLE_PUSH_NOTIFICATION_API_KEY", FatUtility::VAR_STRING, '');
            if (trim($google_push_notification_api_key) == '') {
                return $this->getMainTableRecordId();
            }

            $uObj = new User($userId);
            $fcmDeviceIds = $uObj->getPushNotificationTokens();
            if (empty($fcmDeviceIds)) {
                return $this->getMainTableRecordId();
            }

            /* require_once(CONF_INSTALLATION_PATH . 'library/APIs/notifications/pusher.php');
            $pusher = new Pusher($google_push_notification_api_key); */
            foreach ($fcmDeviceIds as $pushNotificationApiToken) {
                $message = array( 'text' => $data['unotification_body'], 'type'=>$data['unotification_type']);
                self::sendPushNotification($google_push_notification_api_key, $pushNotificationApiToken['uauth_fcm_id'], $message);
                /* $pusher->notify($pushNotificationApiToken['uauth_fcm_id'], array('text'=>$data['unotification_body'],'type'=>$data['unotification_type'])); */
            }
        }

        return $this->getMainTableRecordId();
    }

    public static function sendPushNotification($serverKey, $deviceToken, $data = array())
    {
        $url = "https://fcm.googleapis.com/fcm/send";

        $notification = $data;
        $arrayToSend = array('to' => $deviceToken, 'notification' => $notification,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //Send the request
        $response = curl_exec($ch);
        //Close request
        $data = array();
        if ($response === false) {
            $data['status'] = false;
            $data['msg'] = curl_error($ch);
        } else {
            $data['status'] = true;
            $data['msg'] = $response;
        }
        curl_close($ch);
        return $data;
    }


    public function readUserNotification($notificationId, $userId)
    {
        $smt = array(
            'smt' => static::DB_TBL_PREFIX . 'id = ? AND '.static::DB_TBL_PREFIX . 'user_id = ?',
            'vals' => array((int)$notificationId, (int)$userId)
        );
        if (!FatApp::getDb()->updateFromArray(static::DB_TBL, array(static::DB_TBL_PREFIX.'is_read'=>1), $smt)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function getUnreadNotificationCount($userId)
    {
        $srch = new SearchBase(static::DB_TBL, 'unt');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $cnd = $srch->addCondition('unt.unotification_user_id', '=', $userId);
        $cnd = $srch->addCondition('unt.unotification_is_read', '=', 0);
        $srch->addMultipleFields(array("count(unt.unotification_id) as UnReadNotificationCount"));
        $rs = $srch->getResultSet();
        if (!$rs) {
            return 0;
        }
        $res = FatApp::getDb()->fetch($rs);
        return $res['UnReadNotificationCount'];
    }
}
