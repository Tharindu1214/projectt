<?php
class ThemeColor extends MyAppModel
{
    const DB_TBL = 'tbl_theme_colors';
    const DB_TBL_PREFIX = 'tcolor_';
    const DB_LANG_TBL = 'tbl_theme_colors_lang';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 't');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'tcolorlang_tcolor_id = tcolor_id
			AND tcolorlang_lang_id = ' . $langId,
                't_l'
            );
        }

        if ($isActive) {
            $srch->addCondition('banner_active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }
}
