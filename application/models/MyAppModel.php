<?php
class MyAppModel extends FatModel
{
    /**
     *
     * @var TableRecord
     */
    protected $objMainTableRecord;

    protected $mainTableIdField;
    protected $mainTableRecordId;
    protected $mainTableName;
    protected $commonLangId;

    public function __construct($tblName, $keyFld, $id)
    {
        parent::__construct();
        $this->objMainTableRecord = new TableRecord($tblName);
        $this->mainTableIdField = $keyFld;
        $this->mainTableRecordId = FatUtility::convertToType($id, FatUtility::VAR_INT);
        $this->mainTableName = $tblName;
        $this->commonLangId = CommonHelper::getLangId();
    }

    public static function tblFld($key)
    {
        return static::DB_TBL_PREFIX . $key;
    }

    public static function getAllNames($assoc = true, $recordId = 0, $activeFld = null, $deletedFld = null)
    {
        $srch = new SearchBase(static::DB_TBL);
        $srch->addMultipleFields(array(static::tblFld('id'), static::tblFld('name')));
        $srch->addOrder(static::tblFld('name'));
        if ($activeFld != null) {
            $srch->addCondition($activeFld, '=', applicationConstants::ACTIVE);
        }
        if ($deletedFld != null) {
            $srch->addCondition($deletedFld, '=', applicationConstants::NO);
        }

        if ($recordId > 0) {
            $srch->addCondition(static::tblFld('id'), '=', FatUtility::int($recordId));
        }

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if ($assoc) {
            return FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
        } else {
            return FatApp::getDb()->fetchAll($srch->getResultSet(), static::tblFld('id'));
        }
    }

    public function updateLangData($lang_id, $data)
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', $this->commonLangId);
            return false;
        }

        $lang_id = FatUtility::int($lang_id);
        $record = new TableRecord(static::DB_TBL . '_lang');
        $record->assignValues($data);
        $prefix = substr(static::DB_TBL_PREFIX, 0, -1);
        $record->setFldValue($prefix . 'lang_' . static::DB_TBL_PREFIX . 'id', $this->mainTableRecordId);
        $record->setFldValue($prefix . 'lang_lang_id', $lang_id);

        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }

        return true;
    }

    /* public function getLangData($lang_id) {
    $srch = new SearchBase(static::DB_TBL . '_lang','ln');
    $prefix = substr(static::DB_TBL_PREFIX, 0, -1);
    $srch->addCondition('ln.'.$prefix . 'lang_' . static::DB_TBL_PREFIX . 'id', '=', $this->mainTableRecordId);
    $srch->addCondition('ln.'.$prefix . 'lang_lang_id', '=', FatUtility::int($lang_id));
    $rs=$srch->getResultSet();
    if($rs){
    return FatApp::getDb()->fetch( $rs, $prefix . 'lang_lang_id');
    }
    return false;
    } */

    public function assignValues($arr, $handleDates = false, $mysql_date_format = '', $mysql_datetime_format = '', $execute_mysql_functions = false)
    {
        $this->objMainTableRecord->assignValues($arr, $handleDates, $mysql_date_format, $mysql_datetime_format, $execute_mysql_functions);
    }

    public function deleteRecord($deleteLangData = false)
    {
        if (!FatApp::getDb()->deleteRecords($this->mainTableName, array('smt'=>$this->mainTableIdField . ' = ?', 'vals'=>array($this->mainTableRecordId)))) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }

        if ($deleteLangData == false) {
            return true;
        }

        $prefix = substr(static::DB_TBL_PREFIX, 0, -1);
        if (!FatApp::getDb()->deleteRecords($this->mainTableName.'_lang', array('smt'=>$prefix . 'lang_' . static::DB_TBL_PREFIX . 'id' . ' = ?', 'vals'=>array($this->mainTableRecordId)))) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function loadFromDb($prepare_dates_for_display = false)
    {
        $result = $this->objMainTableRecord->loadFromDb(
            array(
            'smt' => $this->mainTableIdField . " = ?",
            'vals' => array(
            $this->mainTableRecordId
            )
            ),
            $prepare_dates_for_display
        );
        if (!$result) {
            $this->error = $this->objMainTableRecord->getError();
        }

        return $result;
    }

    public static function getAttributesByIdentifier($recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_STRING);
        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(static::tblFld('identifier'), '=', $recordId);

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

    public static function getAttributesById($recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(static::tblFld('id'), '=', $recordId);

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

    public static function getAttributesByLangId($langId, $recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);

        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL . '_lang', 'ln');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $prefix = substr(static::DB_TBL_PREFIX, 0, -1);
        $srch->addCondition('ln.'.$prefix . 'lang_' . static::DB_TBL_PREFIX . 'id', '=', $recordId);
        $srch->addCondition('ln.'.$prefix . 'lang_lang_id', '=', FatUtility::int($langId));

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

    public static function getLangDataArr($recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL . '_lang', 'ln');
        $prefix = substr(static::DB_TBL_PREFIX, 0, -1);
        $srch->addCondition('ln.'.$prefix . 'lang_' . static::DB_TBL_PREFIX . 'id', '=', $recordId);
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $rs = $srch->getResultSet();
        $row = $db->fetchALL($rs, $prefix . 'lang_lang_id');
        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }

        return $row;
    }

    public function getFlds()
    {
        return $this->objMainTableRecord->getFlds();
    }

    public function getFldValue($key)
    {
        return $this->objMainTableRecord->getFldValue($key);
    }

    public function setFlds($arr)
    {
        $this->objMainTableRecord->setFlds($arr);
    }

    public function setFldValue($key, $val, $execute_mysql_function = false)
    {
        $this->objMainTableRecord->setFldValue($key, $val, $execute_mysql_function);
    }

    public function save()
    {
        if (0 < $this->mainTableRecordId) {
            $result = $this->objMainTableRecord->update(array('smt'=>$this->mainTableIdField . ' = ?', 'vals'=>array($this->mainTableRecordId)));
        } else {
            $result = $this->objMainTableRecord->addNew();
            if ($result) {
                $this->mainTableRecordId = $this->objMainTableRecord->getId();
            }
        }

        if (!$result) {
            $this->error = $this->objMainTableRecord->getError();
        }

        return $result;
    }

    public function getMainTableRecordId()
    {
        return $this->mainTableRecordId;
    }

    public function setMainTableRecordId($id)
    {
        $id =FatUtility::int($id);
        $this->mainTableRecordId = $id;
    }

    public function changeStatus($v = 1)
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = 'ERR_INVALID_REQUEST_ID';
            return false;
        }

        $db = FatApp::getDb();
        if (! $db->updateFromArray(
            static::DB_TBL,
            array(
            static::DB_TBL_PREFIX . 'active' => $v
            ),
            array(
            'smt' => static::DB_TBL_PREFIX . 'id = ?',
            'vals' => array(
                        $this->mainTableRecordId
            )
            )
        )) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }

    public function updateOrder($order)
    {
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }

                FatApp::getDb()->updateFromArray(
                    static::DB_TBL,
                    array(
                    static::DB_TBL_PREFIX . 'display_order' => $i
                    ),
                    array(
                    'smt' => static::DB_TBL_PREFIX . 'id = ?',
                    'vals' => array($id)
                    )
                );
            }
            return true;
        }
        return false;
    }

    public function addNew($insert_options = array(), $flds_update_on_duplicate = array())
    {
        if (!$this->objMainTableRecord->addNew($insert_options, $flds_update_on_duplicate)) {
            $this->error = $this->objMainTableRecord->getError();
            return false;
        }
        return true;
    }
}
