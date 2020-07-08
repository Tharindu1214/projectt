<?php
class ShopTheme extends MyAppModel
{
    const DB_TBL = 'tbl_shops_to_theme';
    const DB_TBL_PREFIX = 'stt_';
    const THEME_BACKGROUND_COLOR ='stt_bg_color';
    const THEME_HEADER_COLOR = 'stt_header_color';
    const THEME_TEXT_COLOR = 'stt_text_color';
    public function __construct($uwlistId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $uwlistId);
        $this->objMainTableRecord->setSensitiveFields(array());
    }
    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'stt');


        return $srch;
    }
    public static function getAttributesByShopId($shopId, $attr = null)
    {
        $shopId = FatUtility::int($shopId);

        $db = FatApp::getDb();
        $srch = static::getSearchObject();
        $srch->addCondition(static::tblFld('shop_id'), '=', $shopId);

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
    public static function getDefaultShopThemeColor($templateId = '')
    {
        switch ($templateId) {
            case SHOP::TEMPLATE_ONE:
                return array(

               static::THEME_BACKGROUND_COLOR=>'dadee7',
               static::THEME_HEADER_COLOR=>'FFFFFF',
               static::THEME_TEXT_COLOR=>'000000',
                );
                break;
            case SHOP::TEMPLATE_TWO:
                return array(

               static::THEME_BACKGROUND_COLOR=>'dadee7',
               static::THEME_HEADER_COLOR=>'FFFFFF',
               static::THEME_TEXT_COLOR=>'000000',
                );
                break;
            case SHOP::TEMPLATE_THREE:
                return array(

               static::THEME_BACKGROUND_COLOR=>'FFFFFF',
               static::THEME_HEADER_COLOR=>'ffff66',
               static::THEME_TEXT_COLOR=>'000000',
                );
                break;
            case SHOP::TEMPLATE_FOUR:
                return array(

               static::THEME_BACKGROUND_COLOR=>'09bfe3',
               static::THEME_HEADER_COLOR=>'09bfe3',
               static::THEME_TEXT_COLOR=>'FFFFFF',
                );
                break;
            case SHOP::TEMPLATE_FIVE:
                return array(

               static::THEME_BACKGROUND_COLOR=>'174a67',
               static::THEME_HEADER_COLOR=>'174a67',
               static::THEME_TEXT_COLOR=>'FFFFFF',
                );
                break;
            default:
                return array(
                    static::THEME_BACKGROUND_COLOR=>'dadee7',
                    static::THEME_HEADER_COLOR=>'FFFFFF',
                    static::THEME_TEXT_COLOR=>'000000',
                );
        }
    }
}
