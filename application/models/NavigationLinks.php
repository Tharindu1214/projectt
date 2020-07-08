<?php
class NavigationLinks extends MyAppModel
{
    const DB_TBL = 'tbl_navigation_links';
    const DB_TBL_PREFIX = 'nlink_';

    const DB_TBL_LANG = 'tbl_navigation_links_lang';
    const DB_TBL_LANG_PREFIX = 'nlinkslang_';

    const NAVLINK_TYPE_CMS = 0;
    //const NAVLINK_TYPE_CUSTOM_HTML = 1;
    const NAVLINK_TYPE_EXTERNAL_PAGE = 2;
    const NAVLINK_TYPE_CATEGORY_PAGE = 3;

    const NAVLINK_TARGET_CURRENT_WINDOW = "_self";
    const NAVLINK_TARGET_BLANK_WINDOW = "_blank";

    const NAVLINK_LOGIN_BOTH = 0;
    const NAVLINK_LOGIN_YES = 1;
    const NAVLINK_LOGIN_NO = 2;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isDeleted = false)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'link');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'link_l.'.static::DB_TBL_LANG_PREFIX.'nlink_id = link.'.static::tblFld('id').' and
			link_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = ' . $langId,
                'link_l'
            );
        }

        if ($isDeleted == false) {
            $srch->addCondition('link.'.static::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        }
        return $srch;
    }

    public static function getLinkTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::NAVLINK_TYPE_CMS =>    Labels::getLabel('LBL_CMS_Page', $langId),
        //static::NAVLINK_TYPE_CUSTOM_HTML => Labels::getLabel('LBL_Custom_HTML', $langId),
        static::NAVLINK_TYPE_EXTERNAL_PAGE    =>    Labels::getLabel('LBL_External_Page', $langId),
        static::NAVLINK_TYPE_CATEGORY_PAGE    =>    Labels::getLabel('LBL_Product_Category_Page', $langId),
        );
    }

    public static function getLinkTargetArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::NAVLINK_TARGET_CURRENT_WINDOW =>    Labels::getLabel('LBL_Current_Window', $langId),
        static::NAVLINK_TARGET_BLANK_WINDOW =>    Labels::getLabel('LBL_Blank_Window', $langId),
        );
    }

    public static function getLinkLoginTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::NAVLINK_LOGIN_BOTH =>    Labels::getLabel('LBL_Both', $langId),
        static::NAVLINK_LOGIN_YES =>    Labels::getLabel('LBL_Yes', $langId),
        static::NAVLINK_LOGIN_NO =>    Labels::getLabel('LBL_No', $langId),
        );
    }

    /* public function updateContent($data = array()){
    if (! ($this->mainTableRecordId > 0)) {
    $this->error = 'Invalid Request';
    return false;
    }

    $nav_id = FatUtility::int($data['nav_id']);
    unset($data['nav_id']);

    $assignValues = array(
    'nav_identifier'=>$data['nav_identifier'],
    'nav_active'=>$data['nav_active'],
    );

    if (!FatApp::getDb()->updateFromArray(static::DB_TBL, $assignValues,
    array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array((int)$nav_id)))){
    $this->error = $this->db->getError();
    return false;
    }
    return true;
    } */
}
