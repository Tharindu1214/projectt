<?php
class OrderCancelRequest extends MyAppModel
{
    const DB_TBL = 'tbl_order_cancel_requests';
    const DB_TBL_PREFIX = 'ocrequest_';

    const CANCELLATION_REQUEST_STATUS_PENDING = 0;
    const CANCELLATION_REQUEST_STATUS_APPROVED = 1;
    const CANCELLATION_REQUEST_STATUS_DECLINED = 2;
    const CLASS_PENDING = 'warning';
    const CLASS_COMPLETED = 'success';
    const CLASS_CANCELLED = 'danger';


    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'ocr');
        return $srch;
    }

    public static function getStatusClassArr()
    {
        return array(
        static::CANCELLATION_REQUEST_STATUS_PENDING        =>    static::CLASS_PENDING,
        static::CANCELLATION_REQUEST_STATUS_APPROVED    =>    static::CLASS_COMPLETED,
        static::CANCELLATION_REQUEST_STATUS_DECLINED    =>    static::CLASS_CANCELLED,
        );
    }

    public static function getRequestStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::CANCELLATION_REQUEST_STATUS_PENDING        =>    Labels::getLabel('LBL_Cancellation_Request_Status_Pending', $langId),
        static::CANCELLATION_REQUEST_STATUS_APPROVED    =>    Labels::getLabel('LBL_Cancellation_Request_Status_Approved', $langId),
        static::CANCELLATION_REQUEST_STATUS_DECLINED    =>    Labels::getLabel('LBL_Cancellation_Request_Status_Declined', $langId),
        );
    }

    public static function getCancelRequestById($recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        if (1 > $recordId) {
            return false;
        }

        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL);
        $srch->addCondition('ocrequest_op_id', '=', $recordId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }
}
