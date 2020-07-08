<?php
class UrlRewrite extends MyAppModel
{
    const DB_TBL = 'tbl_url_rewrite';
    const DB_TBL_PREFIX = 'urlrewrite_';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'ur');
        return $srch;
    }

    public static function remove($originalUrl)
    {
        if (FatApp::getDb()->deleteRecords(static::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($originalUrl)))) {
            return true;
        }
        return false;
    }

    public static function update($originalUrl, $customUrl)
    {
        $seoUrlKeyword = array(
        'urlrewrite_original'=>$originalUrl,
        'urlrewrite_custom'=>$customUrl
        );
        if (FatApp::getDb()->insertFromArray(static::DB_TBL, $seoUrlKeyword, false, array(), array('urlrewrite_custom'=>$customUrl))) {
            return true;
        }
        return false;
    }

    public static function getDataByCustomUrl($customUrl, $originalUrl = false)
    {
        $urlSrch = static::getSearchObject();
        $urlSrch->doNotCalculateRecords();
        $urlSrch->setPageSize(1);
        $urlSrch->addMultipleFields(array('urlrewrite_id','urlrewrite_original','urlrewrite_custom'));
        $urlSrch->addCondition('urlrewrite_custom', '=', $customUrl);
        if ($originalUrl) {
            $urlSrch->addCondition('urlrewrite_original', '!=', $originalUrl);
        }
        $rs = $urlSrch->getResultSet();
        $urlRow = FatApp::getDb()->fetch($rs);
        if ($urlRow == false) {
            return array();
        }

        return $urlRow;
    }
    public static function getDataByOriginalUrl($originalUrl, $excludeThisCustomUrl = false)
    {
        $urlSrch = static::getSearchObject();
        $urlSrch->doNotCalculateRecords();
        $urlSrch->setPageSize(1);
        $urlSrch->addMultipleFields(array('urlrewrite_id','urlrewrite_original','urlrewrite_custom'));
        $urlSrch->addCondition('urlrewrite_original', '=', $originalUrl);
        if ($excludeThisCustomUrl) {
            $urlSrch->addCondition('urlrewrite_custom', '!=', $excludeThisCustomUrl);
        }
        $rs = $urlSrch->getResultSet();
        $urlRow = FatApp::getDb()->fetch($rs);
        if ($urlRow == false) {
            return array();
        }

        return $urlRow;
    }

    public static function getValidSeoUrl($urlKeyword, $originalUrl, $recordId = 0)
    {
        $customUrl = CommonHelper::seoUrl($urlKeyword);

        $res = static::getDataByCustomUrl($customUrl, $originalUrl);
        if (empty($res)) {
            return $customUrl;
        }

        $i = 1;
        if ($recordId > 0) {
            $customUrl = preg_replace('/-'.$recordId.'$/', '', $customUrl).'-'.$recordId ;
        }

        $slug = $customUrl;

        while (static::getDataByCustomUrl($slug, $originalUrl)) {
            $slug = $customUrl . "-" . $i++;
        }

        return $slug;
    }
}
