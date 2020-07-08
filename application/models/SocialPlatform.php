<?php
class SocialPlatform extends MyAppModel
{
    const DB_TBL = 'tbl_social_platforms';
    const DB_TBL_PREFIX = 'splatform_';

    const DB_TBL_LANG = 'tbl_social_platforms_lang';
    const DB_TBL_LANG_PREFIX = 'splatformlang_';

    // const ICON_CSS_FB_CLASS = 'fa-facebook';
    // const ICON_CSS_TWITTER_CLASS = 'fa-twitter';
    // const ICON_CSS_YOUTUBE_CLASS = 'fa-youtube';
    // const ICON_CSS_INSTAGRAM_CLASS = 'fa-instagram';
    // const ICON_CSS_GOOGLE_PLUS_CLASS = 'fa-google-plus';
    // const ICON_CSS_PINTEREST_CLASS = 'fa-pinterest-p';

    const ICON_CSS_FB_CLASS = 'facebook';
    const ICON_CSS_TWITTER_CLASS = 'twitter';
    const ICON_CSS_YOUTUBE_CLASS = 'youtube';
    const ICON_CSS_INSTAGRAM_CLASS = 'instagram';
    const ICON_CSS_GOOGLE_PLUS_CLASS = 'google';
    const ICON_CSS_PINTEREST_CLASS = 'pinterest-p';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'sp');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'sp_l.'.static::DB_TBL_LANG_PREFIX.'splatform_id = sp.'.static::tblFld('id').' AND
			sp_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'sp_l'
            );
        }

        if ($isActive == true) {
            $srch->addCondition('sp.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }

        return $srch;
    }

    public static function getIconArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::ICON_CSS_FB_CLASS => Labels::getLabel('LBL_Facebook_Icon', $langId),
        static::ICON_CSS_TWITTER_CLASS => Labels::getLabel('LBL_Twitter_Icon', $langId),
        static::ICON_CSS_YOUTUBE_CLASS => Labels::getLabel('LBL_Youtube_Icon', $langId),
        static::ICON_CSS_INSTAGRAM_CLASS => Labels::getLabel('LBL_Instagram_Icon', $langId),
        static::ICON_CSS_GOOGLE_PLUS_CLASS => Labels::getLabel('LBL_Google_Icon', $langId),
        static::ICON_CSS_PINTEREST_CLASS => Labels::getLabel('LBL_Pinterest_Icon', $langId),
        );
    }
}
