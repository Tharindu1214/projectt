<?php
class CheckuniqueController
{
    const DB_TBL = 'tbl_unique_check_failed_attempt';
    public function check()
    {
        $db = FatApp::getDb();

        if ($this->isBruteForceAttemptToCheckUnique(CommonHelper::getClientIp())) {
            $this->logFailedAttempt(CommonHelper::getClientIp(), true);
            FatUtility::dieJsonError('Attempt to check unique exceeded!');
        }

        $post=FatApp::getPostedData();
        $expr='/^[a-zA-Z0-9_]+$/';
        if (!preg_match($expr, $post['tbl']) || !preg_match($expr, $post['tbl_fld']) || !preg_match($expr, $post['tbl_key'])) {
            $this->logFailedAttempt(CommonHelper::getClientIp());
            FatUtility::dieJsonError('Invalid Request');
        }

        $allowedFor = array(
            FatApp::getPostedData('tbl', FatUtility::VAR_STRING),
            FatApp::getPostedData('tbl_fld', FatUtility::VAR_STRING)
        );
        foreach ($allowedFor as $val) {
            switch ($val) {
                case User::DB_TBL_CRED:
                case User::DB_TBL_CRED_PREFIX.'username':
                case User::DB_TBL_CRED_PREFIX.'email':
                case ShippingDurations::DB_TBL:
                case ShippingDurations::DB_TBL_PREFIX.'identifier':
                case DiscountCoupons::DB_TBL:
                case DiscountCoupons::DB_TBL_PREFIX.'code':
                case Navigations::DB_TBL:
                case Navigations::DB_TBL_PREFIX.'identifier':
                case SocialPlatform::DB_TBL:
                case SocialPlatform::DB_TBL_PREFIX.'identifier':
                case Brand::DB_TBL:
                case Brand::DB_TBL_PREFIX.'identifier':
                case 'tbl_admin':
                case 'admin_username':
                case 'admin_email':
                case Slides::DB_TBL:
                case Slides::DB_TBL_PREFIX.'identifier':
                    break;
                default:
                    $this->logFailedAttempt(CommonHelper::getClientIp());
                    FatUtility::dieJsonError('Invalid Request! Need to define in unique check list');
                    break;
            }
        }

        $srch=new SearchBase(FatApp::getPostedData('tbl', FatUtility::VAR_STRING));
        $srch->addCondition(FatApp::getPostedData('tbl_fld', FatUtility::VAR_STRING), '=', FatApp::getPostedData('val', FatUtility::VAR_STRING));
        $srch->addCondition(FatApp::getPostedData('tbl_key', FatUtility::VAR_STRING), '!=', FatApp::getPostedData('key_val', FatUtility::VAR_STRING));

        $operators=array(
                'eq'=>'=',
                'ne'=>'!=',
                'gt'=>'>',
                'ge'=>'>=',
                'lt'=>'<',
                'le'=>'<='
        );

        if (is_array(FatApp::getPostedData('constraints'))) {
            foreach (FatApp::getPostedData('constraints') as $contraint) {
                if (!array_key_exists($contraint['op'], $operators)) {
                    continue;
                }
                $contraint['op'] = $operators[$contraint['op']];
                $srch->addCondition($contraint['fld'], $contraint['op'], $contraint['v']);
            }
        }

        $rs=$srch->getResultSet();

        if ($db->totalRecords($rs) > 0) {
            $arr = array(
                    'status' => 0,
                    'existing_value' => ''
            );
            if (FatApp::getPostedData('key_val') != '' && FatApp::getPostedData('key_val', FatUtility::VAR_STRING) != '0') {
                $srch=new SearchBase(FatApp::getPostedData('tbl'));
                $srch->addCondition(FatApp::getPostedData('tbl_key'), '=', FatApp::getPostedData('key_val'));
                $srch->addFld(FatApp::getPostedData('tbl_fld'));
                $rs=$srch->getResultSet();
                if ($row=$db->fetch($rs)) {
                    $arr['existing_value']=$row[$post['tbl_fld']];
                }
            }
            $this->logFailedAttempt(CommonHelper::getClientIp());
            die(json_encode($arr));
        }
        FatUtility::dieJsonSuccess('Available');
    }

    private function isBruteForceAttemptToCheckUnique($ip)
    {
        $db = FatApp::getDb();

        $srch = new SearchBase(self::DB_TBL);
        $srch->addCondition('ucfattempt_ip', '=', $ip);
        $srch->addCondition('ucfattempt_time', '>=', date('Y-m-d H:i:s', strtotime("-1 minutes")));
        $srch->addFld('COUNT(*) AS total');
        $rs = $srch->getResultSet();

        $row = $db->fetch($rs);

        return ($row['total'] > 10);
    }


    private function logFailedAttempt($ip, $removeOldEntries = false)
    {
        $db = FatApp::getDb();

        $db->deleteRecords(self::DB_TBL, array(
                'smt' => 'ucfattempt_time < ?',
                'vals' => array(date('Y-m-d H:i:s', strtotime("-7 Day")) ) ));

        if ($removeOldEntries) {
            $db->deleteRecords(self::DB_TBL, array(
                'smt' => 'ucfattempt_ip = ? and ucfattempt_time < ? ',
                'vals' => array($ip, date('Y-m-d H:i:s', strtotime("-2 Min")) ) ));
        }

        $db->insertFromArray(self::DB_TBL, array(
                'ucfattempt_ip'=>$ip,
                'ucfattempt_time'=>date('Y-m-d H:i:s')
        ));

        // For improvement, we can send an email about the failed attempt here.
    }
}
