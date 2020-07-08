<?php
class BannerLocation extends MyAppModel
{
    const DB_TBL = 'tbl_banner_locations';
    const DB_TBL_PREFIX = 'blocation_';

    const DB_LANG_TBL = 'tbl_banner_locations_lang';

    const DB_DIMENSIONS_TBL = 'tbl_banner_location_dimensions';
    const DB_DIMENSIONS_TBL_PREFIX = 'bldimensions_';

    const HOME_PAGE_TOP_BANNER = 1;
    const HOME_PAGE_BOTTOM_BANNER = 2;
    const PRODUCT_DETAIL_PAGE_BANNER = 3;
    const HOME_PAGE_MIDDLE_BANNER = 4;

    const MOBILE_API_BANNER_PAGESIZE = 1;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true, $deviceType = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'bl');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'blocationlang_blocation_id = blocation_id
			AND blocationlang_lang_id = ' . $langId,
                'bl_l'
            );
        }

        if ($isActive) {
            $srch->addCondition('blocation_active', '=', applicationConstants::ACTIVE);
        }

        $deviceType = FatUtility::int($deviceType);
        if (1 > $deviceType) {
            $deviceType = applicationConstants::SCREEN_DESKTOP;
        }

        $srch->joinTable(BannerLocation::DB_DIMENSIONS_TBL, 'LEFT OUTER JOIN', 'bldim.bldimension_blocation_id = bl.blocation_id AND bldim.bldimension_device_type = ' . $deviceType, 'bldim');

        return $srch;
    }

    public static function getDimensions($bannerLocationId, $deviceType){
        $srch = new BannerSearch(0, false);
        $srch->joinLocations();
        $srch->joinLocationDimension($deviceType);
        $srch->addMultipleFields(array('blocation_banner_width','blocation_banner_height'));
        $srch->addCondition('bldimension_blocation_id', '=', $bannerLocationId);
        $srch->addCondition('bldimension_device_type', '=', $deviceType);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();       
        return $bannerDimensions = FatApp::getDb()->fetch($rs);
    }

    public static function getPromotionalBanners($blocationId, $langId, $pageSize = 0)
    {
        $blocationId = FatUtility::int($blocationId);
        $db = FatApp::getDb();

        $bannerSrch = Banner::getBannerLocationSrchObj(true);
        $bannerSrch->addCondition('blocation_id', '=', $blocationId);
        $rs = $bannerSrch->getResultSet();
        $bannerLocation = $db->fetchAll($rs, 'blocation_key');

        $banners = $bannerLocation;
        $i = 0;
        foreach ($bannerLocation as $val) {
            $bsrch = new BannerSearch($langId, true);
            $bsrch->joinPromotions($langId, true, true, true);
            $bsrch->addPromotionTypeCondition();
            $bsrch->joinActiveUser();
            $bsrch->joinUserWallet();
            $bsrch->addMinimiumWalletbalanceCondition();
            $bsrch->addSkipExpiredPromotionAndBannerCondition();
            $bsrch->joinBudget();
            $bsrch->addMultipleFields(array('banner_id','banner_blocation_id','banner_type','banner_record_id', 'banner_url','banner_target','banner_title','promotion_id','daily_cost','weekly_cost','monthly_cost','total_cost','banner_img_updated_on'));
            $bsrch->doNotCalculateRecords();
            //$bsrch->doNotLimitRecords();
            $bsrch->joinAttachedFile();
            $bsrch->addCondition('banner_blocation_id', '=', $val['blocation_id']);

            $srch = new SearchBase('('.$bsrch->getQuery().') as t');
            $srch->doNotCalculateRecords();
            $srch->addDirectCondition(
                '((CASE
					WHEN promotion_duration='.Promotion::DAILY.' THEN promotion_budget > COALESCE(daily_cost,0)
					WHEN promotion_duration='.Promotion::WEEKLY.' THEN promotion_budget > COALESCE(weekly_cost,0)
					WHEN promotion_duration='.Promotion::MONTHLY.' THEN promotion_budget > COALESCE(monthly_cost,0)
					WHEN promotion_duration='.Promotion::DURATION_NOT_AVAILABALE.' THEN promotion_budget = -1
				  END ) )'
            );
            $srch->addMultipleFields(array('banner_id','banner_blocation_id','banner_type','banner_record_id','banner_url','banner_target','banner_title','promotion_id','userBalance','daily_cost','weekly_cost','monthly_cost','total_cost','promotion_budget','promotion_duration','banner_img_updated_on'));
            if ($pageSize == 0) {
                $pageSize = $val['blocation_banner_count'];
            }
            $srch->setPageSize($pageSize);
            $srch->addOrder('', 'rand()');
            $rs = $srch->getResultSet();

            if (true ===  MOBILE_APP_API_CALL) {
                $bannerListing = $db->fetchAll($rs);
            } else {
                $bannerListing = $db->fetchAll($rs, 'banner_id');
            }
            $banners[$val['blocation_key']]['banners'] = $bannerListing;
            $i++;
        }
        return $banners;
    }
}
