<?php
class Navigations extends MyAppModel
{
    const DB_TBL = 'tbl_navigations';
    const DB_TBL_PREFIX = 'nav_';

    const DB_TBL_LANG = 'tbl_navigations_lang';
    const DB_TBL_LANG_PREFIX = 'navlang_';

    const NAVTYPE_FOOTER = 1;
    const NAVTYPE_TOP_HEADER = 2;
    const NAVTYPE_HEADER = 3;
    const NAVTYPE_SELLER_LEFT = 4;
    const NAVTYPE_SELLER_RIGHT = 5;
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true, $isDeleted = false)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'nav');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'nav_l.'.static::DB_TBL_LANG_PREFIX.'nav_id = nav.'.static::tblFld('id').' and
			nav_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'nav_l'
            );
        }

        if ($isActive == true) {
            $srch->addCondition('nav.'.static::DB_TBL_PREFIX.'active', '=', 1);
        }

        if ($isDeleted == false) {
            $srch->addCondition('nav.'.static::DB_TBL_PREFIX.'deleted', '=', 0);
        }

        return $srch;
    }

    public static function getListingObj($langId, $attr = null)
    {
        $srch = self::getSearchObject($langId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addMultipleFields(
            array(
            'IFNULL(nav_l.nav_name,nav.nav_identifier) as nav_name'
            )
        );
        return $srch;
    }

    public function updateContent($data = array())
    {
        if (! ($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', $this->commonLangId);
            return false;
        }

        $nav_id = FatUtility::int($data['nav_id']);
        unset($data['nav_id']);

        $assignValues = array(
        'nav_identifier'=>$data['nav_identifier'],
        'nav_active'=>$data['nav_active'],
        );

        if (!FatApp::getDb()->updateFromArray(
            static::DB_TBL,
            $assignValues,
            array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array((int)$nav_id))
        )) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }
}
