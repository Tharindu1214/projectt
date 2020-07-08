<?php
class ContentPage extends MyAppModel
{
    const DB_TBL = 'tbl_content_pages';
    const DB_TBL_PREFIX = 'cpage_';

    const DB_TBL_LANG = 'tbl_content_pages_lang';
    const DB_TBL_LANG_PREFIX = 'cpagelang_';

    const DB_TBL_CONTENT_PAGES_BLOCK_LANG = 'tbl_content_pages_block_lang';
    const DB_TBL_CONTENT_PAGES_BLOCK_LANG_PREFIX = 'cpblocklang_';

    const CONTENT_PAGE_LAYOUT1_TYPE = 1;
    const CONTENT_PAGE_LAYOUT2_TYPE = 2;
    const CONTENT_PAGE_LAYOUT1_BLOCK_COUNT = 5;
    const CONTENT_PAGE_LAYOUT1_BLOCK_1 = 1 ;
    const CONTENT_PAGE_LAYOUT1_BLOCK_2 = 2 ;
    const CONTENT_PAGE_LAYOUT1_BLOCK_3 = 3 ;
    const CONTENT_PAGE_LAYOUT1_BLOCK_4 = 4 ;
    const CONTENT_PAGE_LAYOUT1_BLOCK_5 = 5 ;

    const REWRITE_URL_PREFIX = 'cms/view/';


    public function __construct($epageId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $epageId);
    }

    public static function getAllAttributesById($cPageId = 0, $langId = 0)
    {
        $cPageData = static::getAttributesById($cPageId);
        if ($cPageData == false) {
            return false ;
        }
        if ($langId > 0) {
            $cPageLangData = static::getAttributesByLangId($langId, $cPageId);
            if ($cPageLangData == false) {
                return $cPageData;
            }
            return array_merge($cPageData, $cPageLangData);
        }
        return array_merge($cPageData);
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'p');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'p_l.'.static::DB_TBL_LANG_PREFIX.'cpage_id = p.'.static::tblFld('id').' and
			p_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'p_l'
            );
        }
        $srch->addCondition('p.'.static::DB_TBL_PREFIX.'deleted', '=', 0);

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
            'IFNULL(p_l.cpage_title,p.cpage_identifier) as cpage_title'
            )
        );

        return $srch;
    }

    public static function getPagesForSelectBox($langId, $ignoreCpageId = 0)
    {
        $langId = FatUtility::int($langId);
        $ignoreCpageId = FatUtility::int($ignoreCpageId);

        $srch = static::getSearchObject($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('cpage_id', 'IFNULL(cpage_title, cpage_identifier) as cpage_title'));

        if ($ignoreCpageId > 0) {
            $srch->addCondition('cpage_id', '!=', $ignoreCpageId);
        }
        $srchRs = $srch->getResultSet();
        return FatApp::getDb()->fetchAllAssoc($srchRs);
    }

    public function canRecordMarkDelete($id)
    {
        $srch =static::getSearchObject();
        $srch->addCondition('p.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('p.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }

    public static function isNotDeleted($id)
    {
        $srch = static::getSearchObject();
        $srch->addCondition('p.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('p.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }

    public function rewriteUrl($keyword)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $originalUrl = static::REWRITE_URL_PREFIX.$this->mainTableRecordId;

        $seoUrl =  CommonHelper::seoUrl($keyword);

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl, $this->mainTableRecordId);

        return UrlRewrite::update($originalUrl, $customUrl);
    }

    public function addUpdateContentPageBlocks($langId, $cpageId, $data)
    {
        FatApp::getDb()->startTransaction();

        $assignValues = array(
        'cpblocklang_lang_id' =>$langId,
        'cpblocklang_cpage_id' =>$cpageId,
        'cpblocklang_block_id' =>$data['cpblocklang_block_id'],
        'cpblocklang_text' =>$data['cpblocklang_text'],

        );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_CONTENT_PAGES_BLOCK_LANG, $assignValues, '', array(), $assignValues)) {
            $this->error = $this->db->getError();
            FatApp::getDb()->rollbackTransaction();
            return false;
        }


        FatApp::getDb()->commitTransaction();

        return true;
    }
}
