<?php
class ThemeColorController extends MyAppController
{
    public function shop($shopId)
    {
        $shopId = FatUtility::int($shopId);
        if (1 > $shopId) {
            die();
        }

        $shopDetails = Shop::getAttributesById($shopId, array('shop_ltemplate_id','shop_custom_color_status','shop_id'));

        $replaceArr = array(
        '{layoutId}'=>$shopDetails['shop_ltemplate_id']
        );
        $themeCssBody = '';

        //$themeCssBody.= file_get_contents(CONF_THEME_PATH.'templates/css/'.$shopDetails['shop_ltemplate_id'].'-theme.css');
        /* echo $shopDetails['shop_theme_button_text_color']; die; */
        if ($shopDetails['shop_custom_color_status'] == applicationConstants::ACTIVE) {
            $themeDetails = ShopTheme::getAttributesByShopId($shopDetails['shop_id'], array('stt_bg_color','stt_header_color','stt_text_color'));
            $templateId = $shopDetails['shop_ltemplate_id'];
            if (!$themeDetails) {
                $themeDetails = ShopTheme::getDefaultShopThemeColor($templateId);
            }
            $arr = $this->getThemeReplacedArr($templateId, $themeDetails);

            $themeCssBody.= file_get_contents(CONF_THEME_PATH.'templates/css/'.$templateId.'-theme.css');
            $replaceArr = array_merge($replaceArr, $arr);
        }

        foreach ($replaceArr as $key => $val) {
            $themeCssBody = str_replace($key, $val, $themeCssBody);
        }

        header("Content-type: text/css");
        die($themeCssBody);
    }

    private function getThemeReplacedArr($templateId, $themeDetails)
    {
        switch ($templateId) {
        case  SHOP::TEMPLATE_ONE:
            return array(

            'var(--theme-background-color--)'=>'#'.$themeDetails['stt_bg_color'],

            'var(--theme-text-color--)'=>'#'.$themeDetails['stt_text_color'],
            );
         break;
        case  SHOP::TEMPLATE_TWO:
            return array(
            'var(--theme-background-color--)'=>'#'.$themeDetails['stt_bg_color'],
            'var(--theme-header-color--)'=>'#'.$themeDetails['stt_header_color'],
            'var(--theme-text-color--)'=>'#'.$themeDetails['stt_text_color'],
            );
         break;
        case  SHOP::TEMPLATE_THREE:
            return array(

            'var(--theme-header-color--)'=>'#'.$themeDetails['stt_header_color'],
            'var(--theme-text-color--)'=>'#'.$themeDetails['stt_text_color'],
            );
         break;
        case  SHOP::TEMPLATE_FOUR:
            return array(
          'var(--theme-background-color--)'=>'#'.$themeDetails['stt_bg_color'],
           'var(--theme-header-color--)'=>'#'.$themeDetails['stt_header_color'],
          'var(--theme-text-color--)'=>'#'.$themeDetails['stt_text_color'],
            );
         break;
        case  SHOP::TEMPLATE_FIVE:
            return array(
            'var(--theme-background-color--)'=>'#'.$themeDetails['stt_bg_color'],
            'var(--theme-header-color--)'=>'#'.$themeDetails['stt_header_color'],
            'var(--theme-text-color--)'=>'#'.$themeDetails['stt_text_color'],
            );
         break;
        }
        return array();
    }
}
