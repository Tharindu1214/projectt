<?php
class EmailTemplates extends MyAppModel
{
    const DB_TBL = 'tbl_email_templates';
    const DB_TBL_PREFIX = 'etpl_';

    public function __construct($etplCode = '')
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'code', $etplCode);
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function getEtpl($etpl_code = '', $langId = 0, $fields = null)
    {
        if (empty($etpl_code)) {
            return;
        }

        $db = FatApp::getDb();

        $srch = static::getSearchObject($langId);
        $srch->addCondition(static::DB_TBL_PREFIX . 'code', 'LIKE', $etpl_code);
        if ($langId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'lang_id', '=', $langId);
        }
        $srch->addOrder(static::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->addGroupby(static::DB_TBL_PREFIX . 'code');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($data = $db->fetch($srch->getResultSet())) {
            return $data;
        }
        return false;
    }

    public static function getSearchObject($langId = 0)
    {
        $langId =  FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        $srch = new SearchBase(static::DB_TBL);
        $srch->addOrder(static::DB_TBL_PREFIX . 'name', 'ASC');
        $srch->addMultipleFields(
            array(
            static::DB_TBL_PREFIX . 'code',
            static::DB_TBL_PREFIX . 'lang_id',
            static::DB_TBL_PREFIX . 'name',
            static::DB_TBL_PREFIX . 'subject',
            static::DB_TBL_PREFIX . 'body',
            static::DB_TBL_PREFIX . 'replacements',
            static::DB_TBL_PREFIX . 'status',
            )
        );
        if ($langId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'lang_id', '=', $langId);
        }
        return $srch;
    }

    public function addUpdateData($data = array())
    {
        $assignValues = array(
         static::DB_TBL_PREFIX . 'code' => $data['etpl_code'],
         static::DB_TBL_PREFIX . 'lang_id' => $data['etpl_lang_id'],
         static::DB_TBL_PREFIX . 'name' => $data['etpl_name'],
         static::DB_TBL_PREFIX . 'subject' => $data['etpl_subject'],
         static::DB_TBL_PREFIX . 'body' => $data['etpl_body'],
                        );

        if (!FatApp::getDb()->insertFromArray(static::DB_TBL, $assignValues, false, array(), $assignValues)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function activateEmailTemplate($v = 1, $etplCode = '')
    {
        if (!$etplCode) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $db = FatApp::getDb();
        if (! $db->updateFromArray(
            static::DB_TBL,
            array(
            static::DB_TBL_PREFIX . 'status' => $v
            ),
            array(
            'smt' => static::DB_TBL_PREFIX . 'code = ?',
            'vals' => array(
                        $etplCode
            )
            )
        )) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }
}
