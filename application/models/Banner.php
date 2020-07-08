<?php
class Banner extends MyAppModel
{
    const DB_TBL = 'tbl_banners';
    const DB_TBL_PREFIX = 'banner_';
    const DB_LANG_TBL = 'tbl_banners_lang';

    const DB_TBL_CLICKS = 'tbl_banners_clicks';
    const DB_TBL_CLICKS_PREFIX = 'bclick_';

    const DB_TBL_LOGS = 'tbl_banners_logs';
    const DB_TBL_LOGS_PREFIX = 'lbanner_';

    const DB_TBL_LOCATIONS = 'tbl_banner_locations';
    const DB_LANG_TBL_LOCATIONS = 'tbl_banner_locations_lang';
    const DB_TBL_LOCATIONS_PREFIX = 'blocation_';

    const TYPE_BANNER = 1;
    const TYPE_PPC = 2;
    const BANNER_HOME_PAGE_LAYOUT_1 = 1;
    const BANNER_HOME_PAGE_LAYOUT_2 = 2;
    const BANNER_PRODUCT_PAGE_LAYOUT_1 = 3;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getBannerTypesArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::TYPE_BANNER    =>    Labels::getLabel('LBL_Banner', $langId),
        static::TYPE_PPC    =>    Labels::getLabel('LBL_Promotion', $langId),
        );
    }
    
    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'b');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'bannerlang_banner_id = banner_id
			AND bannerlang_lang_id = ' . $langId
            );
        }

        if ($isActive) {
            $srch->addCondition('banner_active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function getBannerLocationSrchObj($isActive = true, $deviceType = 0)
    {
        $srch = new SearchBase(static::DB_TBL_LOCATIONS, 'bl');
        if ($isActive) {
            $srch->addCondition('blocation_active', '=', applicationConstants::ACTIVE);
        }

        $deviceType = FatUtility::int($deviceType);
        if (1 > $deviceType) {
            $deviceType = applicationConstants::SCREEN_DESKTOP;
        }

        $srch->joinTable(BannerLocation::DB_DIMENSIONS_TBL, 'LEFT OUTER JOIN', 'bldim.bldimension_blocation_id = bl.blocation_id AND bldim.bldimension_device_type = ' . $deviceType, 'bldim');

        $srch->addOrder('blocation_key', 'ASC');
        return $srch;
    }

    public function updateLocationData($data = array())
    {
        $db = FatApp::getDb();

        $blocationId = $data['blocation_id'];
        unset($data['blocation_id']);

        $assignValues = array(
        /* 'blocation_banner_width'=>$data['blocation_banner_width'],
        'blocation_banner_height'=>$data['blocation_banner_height'], */
        'blocation_active'=>$data['blocation_active'],
        'blocation_promotion_cost'=>$data['blocation_promotion_cost'],
        'blocation_identifier'=>$data['blocation_identifier'],
        );
        $where = array('smt'=>'blocation_id = ?', 'vals'=>array($blocationId));
        if (!$db->updateFromArray(static::DB_TBL_LOCATIONS, $assignValues, $where)) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public static function updateImpressionData($bannerId = 0)
    {
        if (1 > $bannerId) {
            return ;
        }

        $bannerLogData = array(
        'lbanner_banner_id' => $bannerId,
        'lbanner_date' =>  date('Y-m-d'),
        'lbanner_impressions' =>  1,
        );

        $onDuplicateBannerLogData = array_merge($bannerLogData, array('lbanner_impressions'=>'mysql_func_lbanner_impressions+1'));
        FatApp::getDb()->insertFromArray(static::DB_TBL_LOGS, $bannerLogData, true, array(), $onDuplicateBannerLogData);
    }

    public static function setLastModified($banner_id){
        $where = array('smt'=>'banner_id = ?', 'vals'=>array($banner_id));
        FatApp::getDb()->updateFromArray(static::DB_TBL, array('banner_img_updated_on'=>date('Y-m-d  H:i:s')), $where);
    }
}
