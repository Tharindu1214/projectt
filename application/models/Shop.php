<?php
class Shop extends MyAppModel
{
    const DB_TBL = 'tbl_shops';
    const DB_TBL_PREFIX = 'shop_';

    const DB_TBL_LANG = 'tbl_shops_lang';
    const DB_TBL_LANG_PREFIX = 'shoplang_';

    const DB_TBL_SHOP_FAVORITE = 'tbl_user_favourite_shops';
    const DB_TBL_SHOP_THEME_COLOR = 'tbl_shops_to_theme';

    const FILETYPE_SHOP_LOGO = 1;
    const FILETYPE_SHOP_BANNER = 2;
    const TEMPLATE_ONE = 10001;
    const TEMPLATE_TWO = 10002;
    const TEMPLATE_THREE = 10003;
    const TEMPLATE_FOUR = 10004;
    const TEMPLATE_FIVE = 10005;

    const SHOP_VIEW_ORGINAL_URL ='shops/view/';
    const SHOP_REVIEWS_ORGINAL_URL ='reviews/shop/';
    const SHOP_POLICY_ORGINAL_URL ='shops/policy/';
    const SHOP_SEND_MESSAGE_ORGINAL_URL ='shops/send-message/';
    const SHOP_TOP_PRODUCTS_ORGINAL_URL ='shops/top-products/';
    const SHOP_COLLECTION_ORGINAL_URL ='shops/collection/';

    const SHOP_PRODUCTS_COUNT_AT_HOMEPAGE = 2;

    public function __construct($shopId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $shopId);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);

        $srch = new SearchBase(static::DB_TBL, 's');

        if ($isActive == true) {
            $srch->addCondition(static::tblFld('active'), '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                's_l.'.static::DB_TBL_LANG_PREFIX.'shop_id = s.'.static::tblFld('id').' and
			s_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                's_l'
            );
        }
        return $srch;
    }

    public static function getAttributesByUserId($userId, $attr = null, $isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $userId = FatUtility::int($userId);

        $db = FatApp::getDb();
        $srch = static::getSearchObject($isActive, $langId);
        $srch->addCondition(static::tblFld('user_id'), '=', $userId);

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

    public static function isShopActive($userId, $shopId = 0, $returnResult = false)
    {
        $shopId = FatUtility::int($shopId);
        $userId = FatUtility::int($userId);

        $shopDetails = self::getAttributesByUserId($userId, array('shop_active','shop_id'), false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            return false;
        }

        if ($shopId > 0) {
            if (!$shopDetails['shop_id'] == $shopId) {
                return false;
            }
        }

        if ($returnResult === true) {
            if (false == $shopDetails) {
                return false;
            }
            return $shopDetails;
        }

        return true;
    }

    public static function getUserShopProdCategoriesObj($userId, $siteLangId, $shopId = 0, $prodcat_id = 0)
    {
        $userId = FatUtility::int($userId);
        $prodcat_id = FatUtility::int($prodcat_id);
        $shopId = FatUtility::int($shopId);

        $srch = new ProductSearch();
        $srch->joinSellerProducts();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory($siteLangId);

        $srch->addCondition('selprod_user_id', '=', $userId);
        if ($shopId >0) {
            $srch->addCondition('shop_id', '=', $shopId);
        }

        if ($prodcat_id > 0) {
            $srch->addCondition('prodcat_id', '=', $prodcat_id);
        }
        $srch->addGroupBy('prodcat_id');
        $srch->addMultipleFields(array('prodcat_id','ifnull(prodcat_name,prodcat_identifier) as prodcat_name','shop_id'));
        return $srch;
    }

    public function addUpdateUserFavoriteShop($user_id, $shop_id)
    {
        $user_id = FatUtility::int($user_id);
        $shop_id = FatUtility::int($shop_id);

        $data_to_save = array( 'ufs_user_id' => $user_id, 'ufs_shop_id' => $shop_id );
        $data_to_save_on_duplicate = array( 'ufs_shop_id' => $shop_id );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_SHOP_FAVORITE, $data_to_save, false, array(), $data_to_save_on_duplicate)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }
    public static function getShopAddress($shop_id, $isActive = true, $langId = 0, $attr = array())
    {
        if ($langId) {
            $shop_id = FatUtility::int($shop_id);
        }
        $db = FatApp::getDb();
        $srch = static::getSearchObject($isActive, $langId);
        $srch->addCondition(static::tblFld('id'), '=', $shop_id);
        $srch->joinTable(States::DB_TBL, 'LEFT JOIN', 's.shop_state_id=ss.state_id and ss.state_active='.applicationConstants::ACTIVE, 'ss');
        $srch->joinTable(Countries::DB_TBL, 'LEFT JOIN', 's.shop_country_id=sc.country_id and sc.country_active='.applicationConstants::ACTIVE, 'sc');
        if ($isActive) {
            $srch->addCondition('s.shop_active', '=', $isActive);
        }
        $srch->addCondition('s.shop_id', '=', $shop_id);
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

    public static function getShopUrl($shopId = 0, $attr = array())
    {
        $db = FatApp::getDb();
        $shopOriginalUrl ='shops/view/'.$shopId;
        $urlSrch = UrlRewrite::getSearchObject();
        $urlSrch->doNotCalculateRecords();
        $urlSrch->doNotLimitRecords();
        $urlSrch->addFld('urlrewrite_custom');
        $urlSrch->addCondition('urlrewrite_original', '=', $shopOriginalUrl);
        $rs = $urlSrch->getResultSet();
        if (null != $attr) {
            if (is_array($attr)) {
                $urlSrch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $urlSrch->addFld($attr);
            }
        }

        $rs = $urlSrch->getResultSet();
        $row = $db->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }
    }

    public static function getFilterSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox('', 'keyword');
        $frm->addHiddenField('', 'shop_id');
        $frm->addHiddenField('', 'join_price');
        $frm->addSubmitButton('', 'btnProductSrchSubmit', '');
        return $frm;
    }

    private function _rewriteUrl($keyword, $type = 'shop')
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $seoUrl = CommonHelper::seoUrl($keyword);

        switch (strtolower($type)) {
            case 'top-products':
                $originalUrl = Shop::SHOP_TOP_PRODUCTS_ORGINAL_URL.$this->mainTableRecordId;
                $seoUrl = preg_replace('/-top-products$/', '', $seoUrl);
                $seoUrl.=  '-top-products';
                break;
            case 'reviews':
                $originalUrl = Shop::SHOP_REVIEWS_ORGINAL_URL.$this->mainTableRecordId;
                $seoUrl = preg_replace('/-reviews$/', '', $seoUrl);
                $seoUrl.= '-reviews';
                break;
            case 'contact':
                $originalUrl = Shop::SHOP_SEND_MESSAGE_ORGINAL_URL.$this->mainTableRecordId;
                $seoUrl = preg_replace('/-contact$/', '', $seoUrl);
                $seoUrl.=  '-contact';
                break;
            case 'policy':
                $originalUrl = Shop::SHOP_POLICY_ORGINAL_URL.$this->mainTableRecordId;
                $seoUrl = preg_replace('/-policy$/', '', $seoUrl);
                $seoUrl.=  '-policy';
                break;
            case 'collection':
                $originalUrl = Shop::SHOP_COLLECTION_ORGINAL_URL.$this->mainTableRecordId;
                $shopUrl = static::getShopUrl($this->mainTableRecordId, 'urlrewrite_custom');
                $seoUrl = preg_replace('/-'.$shopUrl.'$/', '', $seoUrl);
                $seoUrl.=  '-'.$shopUrl;
                break;
            default:
                $originalUrl = Shop::SHOP_VIEW_ORGINAL_URL.$this->mainTableRecordId;
                break;
        }

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl, $this->mainTableRecordId);

        return UrlRewrite::update($originalUrl, $customUrl);
    }

    public function setupCollectionUrl($keyword)
    {
        return $this->_rewriteUrl($keyword, 'collection');
    }

    public function rewriteUrlShop($keyword)
    {
        return $this->_rewriteUrl($keyword);
    }

    public function rewriteUrlReviews($keyword)
    {
        return $this->_rewriteUrl($keyword, 'reviews');
    }

    public function rewriteUrlTopProducts($keyword)
    {
        return $this->_rewriteUrl($keyword, 'top-products');
    }

    public function rewriteUrlContact($keyword)
    {
        return $this->_rewriteUrl($keyword, 'contact');
    }

    public function rewriteUrlpolicy($keyword)
    {
        return $this->_rewriteUrl($keyword, 'policy');
    }

    /* public function getShopAttachments(){
    $srch = static::getSearchObject();
    } */
    /* public function getUserShopData( $userId , $langId ){
    $userId = FatUtility::int($userId);
    $langId = FatUtility::int($langId);

    $srch = static::getSearchObject($isActive , $langId);

    $srch->addCondition( static::tblFld('user_id') , '=', $userId);
    } */

    public static function getShopName($shopId, $langId = 0, $isActive = true)
    {
        $shopId = FatUtility::int($shopId);
        if (1 > $shopId) {
            return false;
        }

        $srch = static::getSearchObject($isActive, $langId);
        $srch->addMultipleFields(array('IFNULL(shop_name, shop_identifier) as shop_name'));
        $srch->addCondition('shop_id', '=', $shopId);
        $srch->setPageSize(1);
        $shopRs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($shopRs);
        if ($row) {
            return $row['shop_name'];
        } else {
            return false;
        }
    }
}
