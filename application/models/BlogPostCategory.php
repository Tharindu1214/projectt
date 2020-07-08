<?php
class BlogPostCategory extends MyAppModel
{
    const DB_TBL = 'tbl_blog_post_categories';
    const DB_TBL_PREFIX = 'bpcategory_';
    const DB_TBL_LANG ='tbl_blog_post_categories_lang';
    const DB_LANG_TBL_PREFIX ='bpcategorylang_';
    const REWRITE_URL_PREFIX ='blog/category/';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($includeChildCount = false, $langId = 0, $bpcategory_active = true)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'bpc');
        $srch->addOrder('bpc.bpcategory_active', 'DESC');

        if ($includeChildCount) {
            $childSrchbase = new SearchBase(static::DB_TBL);
            $childSrchbase->addCondition('bpcategory_deleted', '=', 0);
            $childSrchbase->doNotCalculateRecords();
            $childSrchbase->doNotLimitRecords();

            $srch->joinTable('('.$childSrchbase->getQuery().')', 'LEFT OUTER JOIN', 's.bpcategory_parent = bpc.bpcategory_id', 's');
            $srch->addGroupBy('bpc.bpcategory_id');
            $srch->addFld('COUNT(s.bpcategory_id) AS child_count');
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'bpc_l.'.static::DB_LANG_TBL_PREFIX.'bpcategory_id = bpc.'.static::tblFld('id').' and
			bpc_l.'.static::DB_LANG_TBL_PREFIX.'lang_id = '.$langId,
                'bpc_l'
            );
        }

        if ($bpcategory_active) {
            $srch->addCondition('bpc.bpcategory_active', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('bpc.bpcategory_deleted', '=', applicationConstants::NO);
        return $srch;
    }

    public function getMaxOrder($parent = 0)
    {
        $srch = new SearchBase(static::DB_TBL);
        $srch->addFld("MAX(" . static::DB_TBL_PREFIX . "display_order) as max_order");
        if ($parent>0) {
            $srch->addCondition(static::DB_TBL_PREFIX.'parent', '=', $parent);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if (!empty($record)) {
            return $record['max_order']+1;
        }
        return 1;
    }

    public function getCategoryStructure($bpcategory_id, $category_tree_array = '')
    {
        if (!is_array($category_tree_array)) {
            $category_tree_array = array();
        }

        $srch = static::getSearchObject();
        $srch->addCondition('bpc.bpcategory_deleted', '=', applicationConstants::NO);
        $srch->addCondition('bpc.bpcategory_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('bpc.bpcategory_id', '=', $bpcategory_id);
        $srch->addOrder('bpc.bpcategory_display_order', 'asc');
        $srch->addOrder('bpc.bpcategory_identifier', 'asc');
        $rs=$srch->getResultSet();
        if ($rs) {
            while ($categories=FatApp::getDb()->fetch($rs)) {
                $category_tree_array[] = $categories;
                $category_tree_array = self::getCategoryStructure($categories['bpcategory_parent'], $category_tree_array);
            }
        }
        sort($category_tree_array);
        return $category_tree_array;
    }

    public function addUpdateBlogPostCatLang($data, $lang_id, $bpcategory_id)
    {
        $tbl = new TableRecord(static::DB_TBL_LANG);
        $data['bpcategorylang_bpcategory_id']=FatUtility::int($bpcategory_id);
        $tbl->assignValues($data);
        if ($this->isExistBlogPostCatLang($lang_id, $bpcategory_id)) {
            if (!$tbl->update(array('smt'=>'bpcategorylang_bpcategory_id = ? and bpcategorylang_lang_id = ? ','vals'=>array($bpcategory_id,$lang_id)))) {
                $this->error = $tbl->getError();
                return false;
            }
            return $bpcategory_id;
        }
        if (!$tbl->addNew()) {
            $this->error = $tbl->getError();
            return false;
        }
        return true;
    }

    public function isExistBlogPostCatLang($lang_id, $bpcategory_id)
    {
        $srch = new SearchBase(static::DB_TBL_LANG);
        $srch->addCondition('bpcategorylang_bpcategory_id', '=', $bpcategory_id);
        $srch->addCondition('bpcategorylang_lang_id', '=', $lang_id);
        $row = FatApp::getDb()->fetch($srch->getResultSet());
        if (!empty($row)) {
            return true;
        }
        return false;
    }

    public function getParentTreeStructure($bpCategory_id = 0, $level = 0, $name_suffix = '')
    {
        $srch = static::getSearchObject();
        $srch->addFld('bpc.bpcategory_id,bpc.bpcategory_identifier,bpc.bpcategory_parent');
        $srch->addCondition('bpc.bpcategory_deleted', '=', applicationConstants::NO);
        $srch->addCondition('bpc.bpcategory_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('bpc.bpCategory_id', '=', FatUtility::int($bpCategory_id));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);

        $name='';
        $seprator='';
        if ($level>0) {
            $seprator=' &nbsp;&nbsp;&raquo;&raquo;&nbsp;&nbsp;';
        }

        if ($records) {
            $name=$records['bpcategory_identifier'].$seprator.$name_suffix;
            if ($records['bpcategory_parent']>0) {
                $name=self::getParentTreeStructure($records['bpcategory_parent'], $level+1, $name);
            }
        }
        return $name;
    }

    public function getBlogPostCatAutoSuggest($keywords = '', $limit = 10)
    {
        $srch = static::getSearchObject();
        $srch->addFld('bpc.bpcategory_id,bpc.bpcategory_identifier,bpc.bpcategory_parent');
        $srch->addCondition('bpc.bpcategory_deleted', '=', applicationConstants::NO);
        $srch->addCondition('bpc.bpcategory_active', '=', applicationConstants::ACTIVE);
        if (!empty($keywords)) {
            $srch->addCondition('bpc.bpcategory_identifier', 'like', '%'.$keywords.'%');
        }
        $srch->addOrder('bpc.bpcategory_parent', 'asc');
        $srch->addOrder('bpc.bpcategory_display_order', 'asc');
        $srch->addOrder('bpc.bpcategory_identifier', 'asc');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $return = array();
        foreach ($records as $row) {
            if (count($return)>=$limit) {
                break;
            }
            if ($row['bpcategory_parent']>0) {
                $return[$row['bpcategory_id']]=self::getParentTreeStructure($row['bpcategory_id']);
            } else {
                $return[$row['bpcategory_id']] =$row['bpcategory_identifier'];
            }
        }
        return $return;
    }

    public function getNestedArray($langId)
    {
        $arr = $this->getCategoriesForSelectBox($langId);

        $out = array();
        foreach ($arr as $id => $cat) {
            $tree = str_split($cat['bpcategory_code'], 6);
            array_pop($tree);
            $parent = & $out;
            foreach ($tree as $parentId) {
                $parentId = intval($parentId);
                $parent = & $parent['children'][$parentId];
            }

            $parent['children'][$id]['name'] = $cat['bpcategory_name'];
        }

        return $out;
    }

    public static function isCategoryActive($categoryId)
    {
        $categoryId = FatUtility::int($categoryId);

        $srch = self::getSearchObject(false, 0, true);
        $srch->addCondition('bpcategory_id', '=', $categoryId);
        $rs = $srch->getResultSet();
        return $srch->recordCount();
    }

    public static function getActiveCategoriesFromCodes($catCodes = array())
    {
        $out = array();

        foreach ($catCodes as $key => $catCode) {
            $hierarchyArr = str_split($catCode, 6);

            $this_active = 1 ;
            foreach ($hierarchyArr as $node) {
                $node = FatUtility::int($node);
                if (!static::isCategoryActive($node)) {
                    $this_active = 0 ;
                    break;
                }
            }
            if ($this_active == applicationConstants::ACTIVE) {
                $out[] = $key;
            }
        }
        return $out;
    }

    public function makeAssociativeArray($arr, $prefix = ' » ')
    {
        $out = array();
        $tempArr = array();
        foreach ($arr as $key => $value) {
            $tempArr[] = $key;
            $name = $value['bpcategory_name'];
            $code = str_replace('_', '', $value['bpcategory_code']);
            $hierarchyArr = str_split($code, 6);
            $this_deleted = 0 ;
            foreach ($hierarchyArr as $node) {
                $node = FatUtility::int($node);
                if (!in_array($node, $tempArr)) {
                    $this_deleted = 1 ;
                    break;
                }
            }
            if ($this_deleted == 0) {
                $level = strlen($code) / 6;
                for ($i = 1; $i < $level; $i++) {
                    $name = $prefix . $name;
                }
                $out[$key] = $name;
            }
        }
        return $out;
    }

    public function getCategoriesForSelectBox($langId, $ignoreCategoryId = 0)
    {
        $srch = static::getSearchObject();
        $srch->joinTable(
            static::DB_TBL_LANG,
            'LEFT OUTER JOIN',
            'bpcategorylang_bpcategory_id = bpcategory_id
			AND bpcategorylang_lang_id = ' . $langId
        );
        $srch->addCondition(static::DB_TBL_PREFIX.'deleted', '=', 0);
        $srch->addMultipleFields(
            array('bpcategory_id',
            'IFNULL(bpcategory_name, bpcategory_identifier) AS bpcategory_name',
            'GETBLOGCATCODE(bpcategory_id) AS bpcategory_code'
            )
        );

        $srch->addOrder('GETBLOGCATORDERCODE(bpcategory_id)');

        if ($ignoreCategoryId > 0) {
            $srch->addHaving('bpcategory_code', 'NOT LIKE', '%' . str_pad($ignoreCategoryId, 6, '0', STR_PAD_LEFT) . '%');
        }
        $rs = $srch->getResultSet();

        return FatApp::getDb()->fetchAll($rs, 'bpcategory_id');
    }

    public function getFeaturedCategories($langId)
    {
        $srch = static::getSearchObject();
        $srch->joinTable(
            static::DB_TBL_LANG,
            'LEFT OUTER JOIN',
            'bpcategorylang_bpcategory_id = bpcategory_id
			AND bpcategorylang_lang_id = ' . $langId
        );
        $srch->addCondition(static::DB_TBL_PREFIX.'featured', '=', 1);
        $srch->addMultipleFields(
            array('bpcategory_id',
            'IFNULL(bpcategory_name, bpcategory_identifier) AS bpcategory_name',
            'GETBLOGCATCODE(bpcategory_id) AS bpcategory_code'
            )
        );

        $srch->addOrder('GETBLOGCATORDERCODE(bpcategory_id)');
        return FatApp::getDb()->fetchAll($srch->getResultSet(), 'bpcategory_id');
    }

    public function getBlogPostCatTreeStructure($parent_id = 0, $keywords = '', $level = 0, $name_prefix = '')
    {
        $srch = static::getSearchObject();
        $srch->addFld('bpc.bpcategory_id,bpc.bpcategory_identifier');
        $srch->addCondition('bpc.bpcategory_deleted', '=', applicationConstants::NO);
        $srch->addCondition('bpc.bpcategory_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('bpc.bpcategory_parent', '=', FatUtility::int($parent_id));

        if (!empty($keywords)) {
            $srch->addCondition('bpc.bpcategory_identifier', 'like', '%'.$keywords.'%');
        }
        $srch->addOrder('bpc.bpcategory_display_order', 'asc');
        $srch->addOrder('bpc.bpcategory_identifier', 'asc');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        $return = array();
        $seprator='';
        if ($level>0) {
            $seprator='&raquo;&raquo;&nbsp;&nbsp;';
            $seprator=CommonHelper::renderHtml($seprator);
        }
        foreach ($records as $bpcategory_id => $bpcategory_identifier) {
            $name=    $name_prefix .$seprator. $bpcategory_identifier;
            $return[$bpcategory_id] = $name;
            $return += self::getBlogPostCatTreeStructure($bpcategory_id, $keywords, $level+1, $name);
        }
        return $return;
    }

    public static function getBlogPostCatParentChildWiseArr($langId = 0, $parentId = 0, $includeChildCat = true, $forSelectBox = false)
    {
        $parentId = FatUtility::int($parentId);
        $langId = FatUtility::int($langId);
        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_not_specified', $langId), E_USER_ERROR);
        }
        $bpCatSrch = new BlogPostCategorySearch($langId);
        $bpCatSrch->doNotCalculateRecords();
        $bpCatSrch->doNotLimitRecords();
        $bpCatSrch->addMultipleFields(array( 'bpcategory_id', 'ifNull(bpcategory_name,bpcategory_identifier) as bpcategory_name'));
        $bpCatSrch->setParent($parentId);
        $bpCatSrch->addOrder('bpcategory_display_order', 'asc');

        $rs = $bpCatSrch->getResultSet();
        if ($forSelectBox) {
            $categoriesArr = FatApp::getDb()->fetchAllAssoc($rs);
        } else {
            $categoriesArr = FatApp::getDb()->fetchAll($rs);
        }
        if (!$includeChildCat) {
            return $categoriesArr;
        }
        if ($categoriesArr) {
            foreach ($categoriesArr as &$cat) {
                $cat['children'] = self::getBlogPostCatParentChildWiseArr($langId, $cat['bpcategory_id']);
                $childPosts = BlogPost::getBlogPostsUnderCategory($langId, $cat['bpcategory_id']);
                $cat['countChildBlogPosts'] = count($childPosts);
            }
        }

        return $categoriesArr;
    }

    public static function getRootBlogPostCatArr($langId)
    {
        $langId = FatUtility::int($langId);
        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_not_specified', $langId), E_USER_ERROR);
        }
        return static::getBlogPostCatParentChildWiseArr($langId, 0, false, true);
    }

    public function rewriteUrl($keyword, $suffixWithId = true, $parentId = 0)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $parentId =  FatUtility::int($parentId);
        $parentUrl = '';
        if (0 < $parentId) {
            $parentUrlRewriteData = UrlRewrite::getDataByOriginalUrl(BlogPostCategory::REWRITE_URL_PREFIX.$parentId);
            if (!empty($parentUrlRewriteData)) {
                $parentUrl = preg_replace('/-'.$parentId.'$/', '', $parentUrlRewriteData['urlrewrite_custom']);
            }
        }

        $originalUrl = BlogPostCategory::REWRITE_URL_PREFIX.$this->mainTableRecordId;

        $keyword = preg_replace('/-'.$this->mainTableRecordId.'$/', '', $keyword);
        $seoUrl =  CommonHelper::seoUrl($keyword);
        if ($suffixWithId) {
            $seoUrl =  $seoUrl.'-'.$this->mainTableRecordId;
        }

        $seoUrl = str_replace($parentUrl, '', $seoUrl);
        $seoUrl = $parentUrl.'-'.$seoUrl;

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl);

        $seoUrlKeyword = array(
        'urlrewrite_original'=>$originalUrl,
        'urlrewrite_custom'=>$customUrl
        );
        if (FatApp::getDb()->insertFromArray(UrlRewrite::DB_TBL, $seoUrlKeyword, false, array(), array('urlrewrite_custom'=>$customUrl))) {
            return true;
        }
        return false;
    }

    public function canMarkRecordDelete($bpcategory_id)
    {
        $srch = static::getSearchObject();
        $srch->addCondition('bpc.bpcategory_deleted', '=', applicationConstants::NO);
        $srch->addCondition('bpc.bpcategory_id', '=', $bpcategory_id);
        $srch->addFld('bpc.bpcategory_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['bpcategory_id']==$bpcategory_id) {
            return true;
        }
        return false;
    }

    public function canUpdateRecordStatus($bpcategory_id)
    {
        $srch = static::getSearchObject();
        $srch->addCondition('bpc.bpcategory_deleted', '=', applicationConstants::NO);
        $srch->addCondition('bpc.bpcategory_id', '=', $bpcategory_id);
        $srch->addFld('bpc.bpcategory_id,bpc.bpcategory_active');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['bpcategory_id']==$bpcategory_id) {
            return $row;
        }
        return false;
    }
}
