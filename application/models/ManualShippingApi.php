<?php
class ManualShippingApi extends MyAppModel
{
    const DB_TBL = 'tbl_manual_shipping_api';
    const DB_TBL_PREFIX = 'mshipapi_';

    const DB_TBL_LANG = 'tbl_manual_shipping_api_lang';
    const DB_TBL_PREFIX_LANG = 'sdurationlang_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'msa');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'msa_l.mshipapilang_mshipapi_id = msa.mshipapi_id AND msa_l.mshipapilang_lang_id = ' . $langId,
                'msa_l'
            );
        }
        return $srch;
    }

    public function getListingObj($langId, $attr = null)
    {
        $srch = self::getSearchObject($langId);
        $srch->joinTable('tbl_shipping_durations', 'LEFT OUTER JOIN', 'sd.sduration_id = msa.mshipapi_sduration_id', 'sd');
        $srch->joinTable('tbl_shipping_durations_lang', 'LEFT OUTER JOIN', 'sd_l.sdurationlang_sduration_id = sd.sduration_id and sd_l.sdurationlang_lang_id = '.$langId, 'sd_l');
        $srch->joinTable('tbl_states', 'LEFT OUTER JOIN', 'msa.mshipapi_state_id = s.state_id and s.state_active = '.applicationConstants::ACTIVE, 's');
        $srch->joinTable('tbl_states_lang', 'LEFT OUTER JOIN', 's_l.statelang_state_id = s.state_id and s_l.statelang_lang_id = '.$langId, 's_l');
        $srch->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'msa.mshipapi_country_id = c.country_id and c.country_active = '.applicationConstants::ACTIVE, 'c');
        $srch->joinTable('tbl_countries_lang', 'LEFT OUTER JOIN', 'c_l.countrylang_country_id = c.country_id and c_l.countrylang_lang_id = '.$langId, 'c_l');

        $srch->addCondition('sd.sduration_deleted', '=', 0);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addMultipleFields(
            array(
            'IFNULL(sd_l.sduration_name,sd.sduration_identifier) as sduration_name',
            'IFNULL(s_l.state_name,s.state_identifier) as state_name',
            'IFNULL(c_l.country_name,c.country_code) as country_name',
            )
        );

        return $srch;
    }

    public function canRecordDelete($id)
    {
        $srch = self::getSearchObject();
        $srch->addCondition('msa.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('msa.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        if ($rs) {
            $row = FatApp::getDb()->fetch($rs);
            if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
                return true;
            }
        }
        return false;
    }
}
