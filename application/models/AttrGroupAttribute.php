<?php
class AttrGroupAttribute extends MyAppModel
{
    const DB_TBL = 'tbl_attribute_group_attributes';
    const DB_TBL_PREFIX = 'attr_';

    const ATTRTYPE_NUMBER = 1;
    const ATTRTYPE_DECIMAL = 2;
    const ATTRTYPE_SELECT_BOX = 3;
    const ATTRTYPE_TEXT = 4;

    const MAX_NUMERIC_ATTRIBUTE_ROWS = 30; /*Do not play with value of this variable, unless you have not known about this */
    const MAX_TEXTUAL_ATTRIBUTE_ROWS = 40; /*Do not play with value of this variable, unless you have not known about this */
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getNumericAttributeTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        return array(
        static::ATTRTYPE_NUMBER => Labels::getLabel('LBL_Number', $langId),
        static::ATTRTYPE_DECIMAL => Labels::getLabel('LBL_Decimal', $langId),
        static::ATTRTYPE_SELECT_BOX => Labels::getLabel('LBL_(Drop_Down)_Select_Box', $langId),
        );
    }

    public static function getTextualAttributeTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        return array(
        static::ATTRTYPE_TEXT => Labels::getLabel('LBL_Text', $langId),
        );
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'attrgrp');
        return $srch;
    }

    public function addUpdateAttributes($attrgrp_id, $data)
    {
        if (!$attrgrp_id) {
            $this->error = Labels::getLabel('MSG_Attribute_Group_not_selected!', $this->commonLangId);
            return false;
        }

        $existing_attributes_data = $this->getAttributesByGroupId($attrgrp_id, array('attr_fld_name'));
        $existed_checkboxes = array();
        $selected_posted_checboxes = array();
        foreach ($existing_attributes_data as $val) {
            $existed_checkboxes[] = $val['attr_fld_name'];
        }

        $record = new TableRecord(self::DB_TBL);
        foreach ($data as $key => $val) {
            $record->assignValues($val);
            $on_duplicate_update_vals = array(
            'attr_identifier'    => $val['attr_identifier'],
            'attr_type'            => $val['attr_type'],
            'attr_fld_name'        => $val['attr_fld_name']
            );
            if (!$record->addNew(array(), $on_duplicate_update_vals)) {
                $this->error = $record->getError();
                return false;
            }
            $selected_posted_checboxes[] = $val['attr_fld_name'];
        }

        $data_to_be_delete_arr = array_diff($existed_checkboxes, $selected_posted_checboxes);
        if (count($data_to_be_delete_arr)) {
            foreach ($data_to_be_delete_arr as $val) {
                $this->db->deleteRecords(self::DB_TBL, array( 'smt'=> self::DB_TBL_PREFIX . 'attrgrp_id=? and attr_fld_name = ?', 'vals'=>array($attrgrp_id, $val )));
            }
        }
        return true;
    }

    public function getAttributesByGroupId($attrgrp_id, $fetch_attr = null)
    {
        $attrgrp_id = FatUtility::convertToType($attrgrp_id, FatUtility::VAR_INT);
        $srch = self::getSearchObject();

        $srch->addCondition(self::DB_TBL_PREFIX.'attrgrp_id', '=', $attrgrp_id);
        $srch->addOrder(self::DB_TBL_PREFIX.'display_order');
        if (null != $fetch_attr) {
            if (is_array($fetch_attr)) {
                $srch->addMultipleFields($fetch_attr);
            } elseif (is_string($fetch_attr)) {
                $srch->addFld($fetch_attr);
            }
        }
        $rs = $srch->getResultSet();
        $records =array();
        if ($rs) {
            $records = $this->db->fetchAll($rs);
        }
        return $records;
    }
}
