<?php
class ProductCategory extends MyAppModel
{
    const DB_TBL = 'tbl_product_categories';
    const DB_TBL_PREFIX = 'prodcat_';
    const DB_LANG_TBL ='tbl_product_categories_lang';
    const DB_LANG_TBL_PREFIX ='prodcatlang_';
    const REWRITE_URL_PREFIX = 'category/view/';
    private $db;
    private $categoryTreeArr = array();


    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($includeChildCount = false, $langId = 0, $prodcat_active = true)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'm');
        $srch->addOrder('m.prodcat_active', 'DESC');

        if ($includeChildCount) {
            $childSrchbase = new SearchBase(static::DB_TBL);
            $childSrchbase->addCondition('prodcat_deleted', '=', 0);
            $childSrchbase->doNotCalculateRecords();
            $childSrchbase->doNotLimitRecords();
            $srch->joinTable('('.$childSrchbase->getQuery().')', 'LEFT OUTER JOIN', 's.prodcat_parent = m.prodcat_id', 's');
            $srch->addGroupBy('m.prodcat_id');
            $srch->addFld('COUNT(s.prodcat_id) AS child_count');
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'pc_l.'.static::DB_LANG_TBL_PREFIX.'prodcat_id = m.'.static::tblFld('id').' and
			pc_l.'.static::DB_LANG_TBL_PREFIX.'lang_id = '.$langId,
                'pc_l'
            );
        }

        if ($prodcat_active) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }

        return $srch;
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'prodcat_id'
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'prodcat_identifier',
                'prodcat_name',
            )
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredMediaFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'prodcat_id'
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'prodcat_identifier',
                'afile_physical_path',
                'afile_name',
                'afile_type',
            )
        );
    }

    public static function validateMediaFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredMediaFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public function updateCatCode()
    {
        $categoryId = $this->mainTableRecordId;
        if (1 > $categoryId) {
            return false;
        }

        $categoryArray = array($categoryId);
        $parentCatData = ProductCategory::getAttributesById($categoryId, array('prodcat_parent'));
        if (array_key_exists('prodcat_parent', $parentCatData) && $parentCatData['prodcat_parent'] > 0) {
            array_push($categoryArray, $parentCatData['prodcat_parent']);
        }

        foreach ($categoryArray as $categoryId) {
            $srch = ProductCategory::getSearchObject();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array('prodcat_id','GETCATCODE(`prodcat_id`) as prodcat_code','GETCATORDERCODE(`prodcat_id`) as prodcat_ordercode'));
            $srch->addCondition('GETCATCODE(`prodcat_id`)', 'LIKE', '%' . str_pad($categoryId, 6, '0', STR_PAD_LEFT) . '%', 'AND', true);
            $rs = $srch->getResultSet();
            $catCode = FatApp::getDb()->fetchAll($rs);
            foreach ($catCode as $row) {
                $record = new ProductCategory($row['prodcat_id']);
                $data = array('prodcat_code'=>$row['prodcat_code'],'prodcat_ordercode'=>$row['prodcat_ordercode']);
                $record->assignValues($data);
                if (!$record->save()) {
                    Message::addErrorMessage($record->getError());
                    return false;
                }
            }
        }
        return true;
    }

    public static function updateCatOrderCode($prodCatId = 0)
    {
        $prodCatId = FatUtility::int($prodCatId);

        $srch = ProductCategory::getSearchObject(false, 0, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('prodcat_id','GETCATORDERCODE(`prodcat_id`) as prodcat_ordercode'));
        if ($prodCatId) {
            $srch->addCondition('prodcat_id', '=', $prodCatId);
        }

        $rs = $srch->getResultSet();
        $orderCode = FatApp::getDb()->fetchAll($rs);
        foreach ($orderCode as $row) {
            $record = new ProductCategory($row['prodcat_id']);
            $data = array('prodcat_ordercode'=>$row['prodcat_ordercode']);
            $record->assignValues($data);
            if (!$record->save()) {
                Message::addErrorMessage($record->getError());
                return false;
            }
        }
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

    public static function getTreeArr($langId, $parentId = 0, $sortByName = false, $prodCatSrchObj = false, $excludeCatHavingNoProducts = false, $keywords = false)
    {
        $parentId = FatUtility::int($parentId);
        $langId = FatUtility::int($langId);
        if (!$langId) {
            trigger_error("Language not specified", E_USER_ERROR);
        }

        if (is_object($prodCatSrchObj)) {
            $prodCatSrch = clone $prodCatSrchObj;
        } else {
            $prodCatSrch = new ProductCategorySearch($langId, true, true, false);
        }

        if (!empty($keywords)) {
            $cnd = $prodCatSrch->addCondition('prodcat_identifier', 'like', '%'.$keywords.'%');
            $cnd->attachCondition('prodcat_name', 'like', '%'.$keywords.'%');
        }

        $prodCatSrch->doNotCalculateRecords();
        $prodCatSrch->doNotLimitRecords();
        $prodCatSrch->addMultipleFields(array( 'prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name', 'substr(prodcat_code,1,6) AS prodrootcat_code',  'prodcat_content_block','prodcat_active','prodcat_parent','prodcat_code','prodcat_ordercode'));

        if (0 < $parentId) {
            $catCode = static::getAttributesById($parentId, 'prodcat_code');
            $prodCatSrch->addCondition('prodcat_code', 'like', $catCode.'%');
        }

        if ($excludeCatHavingNoProducts) {
            $prodSrchObj = new ProductSearch();
            $prodSrchObj->setDefinedCriteria(0, 0, array('doNotJoinSpecialPrice'=> true));
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->doNotLimitRecords();
            $prodSrchObj->joinProductToCategory();
            $prodSrchObj->joinSellerSubscription($langId, true);
            $prodSrchObj->addSubscriptionValidCondition();

            $prodSrchObj->addGroupBy('c.prodcat_id');
            $prodSrchObj->addMultipleFields(array('count(selprod_id) as productCounts', 'c.prodcat_id as qryProducts_prodcat_id'));
            //$prodSrchObj->addMultipleFields( array('count(selprod_id) as productCounts', 'c.prodcat_code as qryProducts_prodcat_code') );
            $prodSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $prodSrchObj->addHaving('productCounts', '>', 0);
            $prodCatSrch->joinTable('('.$prodSrchObj->getQuery().')', 'INNER JOIN', 'qryProducts.qryProducts_prodcat_id = c.prodcat_id', 'qryProducts');
            //$prodCatSrch->joinTable( '('.$prodSrchObj->getQuery().')', 'LEFT OUTER JOIN', 'qryProducts.qryProducts_prodcat_code like CONCAT(c.prodcat_code, "%")', 'qryProducts' );
        }

        if ($sortByName) {
            $prodCatSrch->addOrder('prodcat_name');
            $prodCatSrch->addOrder('prodcat_identifier');
        } else {
            $prodCatSrch->addOrder('prodrootcat_code');
            $prodCatSrch->addOrder('prodcat_ordercode');
        }
        // echo $prodCatSrch->getQuery();exit;
        $rs = $prodCatSrch->getResultSet();
        $categoriesArr = FatApp::getDb()->fetchAll($rs, 'prodcat_id');
        static::addMissingParentDetails($categoriesArr, $langId);
        $categoriesArr = static::parseTree($categoriesArr, $parentId);

        return $categoriesArr;
    }

    public static function addMissingParentDetails(&$categoriesArr, $langId)
    {
        foreach ($categoriesArr as $categoryId => $category) {
            if (!$category['prodcat_parent'] || array_key_exists($category['prodcat_parent'], $categoriesArr)) {
                continue;
            }

            $catCode = explode('_', rtrim($category['prodcat_code'], '_'));
            foreach ($catCode as $code) {
                $catId = ltrim($code, 0);

                if (!$catId || array_key_exists($catId, $categoriesArr)) {
                    continue;
                }

                $srch = new ProductCategorySearch($langId, true, true, false);
                $srch->addCondition('prodcat_id', '=', $catId);
                $srch->setPageSize(1);
                $srch->addMultipleFields(array( 'prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name', 'substr(prodcat_code,1,6) AS prodrootcat_code',  'prodcat_content_block','prodcat_active','prodcat_parent','prodcat_code','prodcat_ordercode'));
                $rs = $srch->getResultSet();
                $data = FatApp::getDb()->fetch($rs);
                $categoriesArr[$catId] = $data;

                if (empty($data)) {
                    unset($categoriesArr[$catId]);
                }
            }
        }
    }


    public static function parseTree($tree, $root = 0)
    {
        $return = array();
        foreach ($tree as $categoryId => $category) {
            $parent = $category['prodcat_parent'];
            if ($parent == $root) {
                unset($tree[$categoryId]);
                $return[$categoryId] = $category;
                $child = static::parseTree($tree, $categoryId);
                $return[$categoryId]['isLastChildCategory'] = (0 < count($child)) ? 0 : 1;
                $return[$categoryId]['children'] = (true ===  MOBILE_APP_API_CALL) ? array_values($child) : $child;
            }
        }
        return empty($return) ? array() : $return;
    }

    public function getCategoryStructure($prodcat_id, $category_tree_array = '', $langId = 0)
    {
        if (!is_array($category_tree_array)) {
            $category_tree_array = array();
        }
        $langId =  FatUtility::int($langId);

        $srch = static::getSearchObject();
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('m.prodcat_id', '=', $prodcat_id);
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('m.prodcat_identifier', 'asc');

        if ($langId > 0) {
            $srch->joinTable(static::DB_LANG_TBL, 'LEFT OUTER JOIN', static::DB_LANG_TBL_PREFIX.'prodcat_id = '.static::tblFld('id').' and '.static::DB_LANG_TBL_PREFIX.'lang_id = '.$langId);
            $srch->addFld(array('COALESCE(prodcat_name,prodcat_identifier) as prodcat_name'));
        } else {
            $srch->addFld(array('prodcat_identifier as prodcat_name'));
        }

        $srch->addMultipleFields(array('prodcat_id','prodcat_identifier','prodcat_parent'));
        $rs = $srch->getResultSet();
        while ($categories = FatApp::getDb()->fetch($rs)) {
            $category_tree_array[] = $categories;
            $category_tree_array = self::getCategoryStructure($categories['prodcat_parent'], $category_tree_array, $langId);
        }

        return $category_tree_array;
    }

    /* public function getProdCat($prodcat_id,$lang_id=0){
        $srch =$this->getSearchObject();
        $srch->addCondition('m.prodcat_id','=',$prodcat_id);
        if($lang_id>0){
            $srch->joinTable(static::DB_LANG_TBL, 'LEFT JOIN', 'plang.prodcatlang_prodcat_id = m.prodcat_id', 'plang');
            $srch->addFld('plang.*');
        }
        $srch->addFld('m.*');
        $record = FatApp::getDb()->fetch($srch->getResultSet());
        //var_dump($record); exit;
        $lang_record=array();
        return  array_merge($record,$lang_record);

    } */

    public function addUpdateProdCatLang($data, $lang_id, $prodcat_id)
    {
        $tbl = new TableRecord(static::DB_LANG_TBL);
        $data['prodcatlang_prodcat_id']=FatUtility::int($prodcat_id);
        $tbl->assignValues($data);
        if ($this->isExistProdCatLang($lang_id, $prodcat_id)) {
            if (!$tbl->update(array('smt'=>'prodcatlang_prodcat_id = ? and prodcatlang_lang_id = ? ','vals'=>array($prodcat_id,$lang_id)))) {
                $this->error = $tbl->getError();
                return false;
            }
            return $prodcat_id;
        }
        if (!$tbl->addNew()) {
            $this->error = $tbl->getError();
            return false;
        }
        return true;
    }

    public function isExistProdCatLang($lang_id, $prodcat_id)
    {
        $srch = new SearchBase(static::DB_LANG_TBL);
        $srch->addCondition('prodcatlang_prodcat_id', '=', $prodcat_id);
        $srch->addCondition('prodcatlang_lang_id', '=', $lang_id);
        $row = FatApp::getDb()->fetch($srch->getResultSet());
        if (!empty($row)) {
            return true;
        }
        return false;
    }

    public function getParentTreeStructure($prodCat_id = 0, $level = 0, $name_suffix = '', $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId);
        $srch->addFld('m.prodcat_id,COALESCE(prodcat_name,m.prodcat_identifier) as prodcat_identifier,m.prodcat_parent');
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('m.prodCat_id', '=', FatUtility::int($prodCat_id));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);

        $name='';
        $seprator='';
        if ($level>0) {
            $seprator=' &nbsp;&nbsp;&raquo;&raquo;&nbsp;&nbsp;';
        }

        if ($records) {
            $name=strip_tags($records['prodcat_identifier']).$seprator.$name_suffix;
            if ($records['prodcat_parent']>0) {
                $name=self::getParentTreeStructure($records['prodcat_parent'], $level+1, $name, $langId);
            }
        }
        return $name;
    }

    public static function isLastChildCategory($prodCat_id = 0)
    {
        $srch = static::getSearchObject();
        $srch->addCondition('prodcat_parent', '=', $prodCat_id);
        $srch->addCondition('prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(array('prodcat_id'));
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);
        if (empty($records)) {
            return true;
        }
        return false;
    }

    public function getProdCatAutoSuggest($keywords = '', $limit = 10, $langId = 0)
    {
        $srch = static::getSearchObject(false, $langId);
        $srch->addFld('m.prodcat_id,m.prodcat_identifier,m.prodcat_parent');
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        if (!empty($keywords)) {
            $srch->addCondition('m.prodcat_identifier', 'like', '%'.$keywords.'%');
        }
        $srch->addOrder('m.prodcat_parent', 'asc');
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('m.prodcat_identifier', 'asc');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $return = array();
        foreach ($records as $row) {
            if (count($return)>=$limit) {
                break;
            }
            if ($row['prodcat_parent']>0) {
                $return[$row['prodcat_id']]=self::getParentTreeStructure($row['prodcat_id'], 0, '', $langId);
            } else {
                $return[$row['prodcat_id']] =$row['prodcat_identifier'];
            }
        }
        return $return;
    }

    public function getNestedArray($langId)
    {
        $arr = $this->getCategoriesForSelectBox($langId);
        $out = array();
        foreach ($arr as $id => $cat) {
            $tree = str_split($cat['prodcat_code'], 6);
            array_pop($tree);
            $parent = & $out;
            foreach ($tree as $parentId) {
                $parentId = intval($parentId);
                $parent = & $parent['children'][$parentId];
            }
            $parent['children'][$id]['name'] = $cat['prodcat_name'];
        }
        return $out;
    }

    public function makeAssociativeArray($arr, $prefix = ' » ')
    {
        $out = array();
        $tempArr = array();
        foreach ($arr as $key => $value) {
            $tempArr[] = $key;
            $name = $value['prodcat_name'];
            $code = str_replace('_', '', $value['prodcat_code']);
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

    public function getCategoriesForSelectBox($langId, $ignoreCategoryId = 0, $prefCategoryid = array())
    {
        /* $srch = new SearchBase(static::DB_TBL); */
        $srch = static::getSearchObject();
        $srch->joinTable(static::DB_LANG_TBL, 'LEFT OUTER JOIN', 'prodcatlang_prodcat_id = prodcat_id
			AND prodcatlang_lang_id = ' . $langId);
        $srch->addCondition(static::DB_TBL_PREFIX.'deleted', '=', 0);
        $srch->addMultipleFields(array('prodcat_id',
        'COALESCE(prodcat_name, prodcat_identifier) AS prodcat_name',
        'prodcat_code'
        ));

        //$srch->addOrder('GETCATORDERCODE(prodcat_id)');
        $srch->addOrder('prodcat_ordercode');

        if (count($prefCategoryid)>0) {
            foreach ($prefCategoryid as $prefCategoryids) {
                $srch->addHaving('prodcat_code', 'LIKE', '%' .$prefCategoryids. '%', 'OR');
            }
        }

        if ($ignoreCategoryId > 0) {
            $srch->addHaving('prodcat_code', 'NOT LIKE', '%' . str_pad($ignoreCategoryId, 6, '0', STR_PAD_LEFT) . '%');
        }
        /* echo $srch->getQuery(); die; */
        $rs = $srch->getResultSet();

        return FatApp::getDb()->fetchAll($rs, 'prodcat_id');
    }

    public function getProdCatTreeStructure($parent_id = 0, $langId = 0, $keywords = '', $level = 0, $name_prefix = '', $isActive = true, $isDeleted = true, $isForCsv = false)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId, $isActive);
        if ($langId) {
            $srch->addFld('m.prodcat_id, COALESCE(pc_l.prodcat_name, m.prodcat_identifier) as prodcat_name');
        } else {
            $srch->addFld('m.prodcat_id, m.prodcat_identifier as prodcat_name');
        }

        if ($isDeleted) {
            $srch->addCondition('m.prodcat_deleted', '=', 0);
        }

        if ($isActive) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('m.prodcat_parent', '=', FatUtility::int($parent_id));

        if (!empty($keywords)) {
            $srch->addCondition('prodcat_name', 'like', '%'.$keywords.'%');
        }

        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('prodcat_name', 'asc');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        $return = array();
        $seprator = '';
        if ($level > 0) {
            if ($isForCsv) {
                $seprator = '->-> ';
            } else {
                $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
            }
            $seprator = CommonHelper::renderHtml($seprator);
        }
        foreach ($records as $prodcat_id => $prodcat_identifier) {
            $name = $name_prefix .$seprator. $prodcat_identifier;
            $return[$prodcat_id] = $name;
            $return += self::getProdCatTreeStructure($prodcat_id, $langId, $keywords, $level+1, $name, $isActive, $isDeleted, $isForCsv);
        }
        return $return;
    }

    public function getProdCatTreeStructureSearch($parent_id = 0, $langId = 0, $keywords = '', $level = 0, $name_prefix = '', $isActive = true, $isDeleted = true, $isForCsv = false)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId, $isActive);
        if ($langId) {
            $srch->addFld('m.prodcat_id, COALESCE(pc_l.prodcat_name, m.prodcat_identifier) as prodcat_name');
        } else {
            $srch->addFld('m.prodcat_id, m.prodcat_identifier as prodcat_name');
        }

        if ($isDeleted) {
            $srch->addCondition('m.prodcat_deleted', '=', 0);
        }

        if ($isActive) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('m.prodcat_parent', '=', FatUtility::int($parent_id));

        if (!empty($keywords)) {
            //$srch->addCondition('prodcat_name','like','%'.$keywords.'%');
        }
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('prodcat_name', 'asc');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        $return = array();
        $seprator = '';
        if ($level > 0) {
            if ($isForCsv) {
                $seprator = '->-> ';
            } else {
                $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
            }
            $seprator = CommonHelper::renderHtml($seprator);
        }
        //print_r($records); die;
        foreach ($records as $prodcat_id => $prodcat_identifier) {
            $name = $name_prefix .$seprator. $prodcat_identifier;
            //echo $name."<br>";
            $flag=0;
            if ($keywords) {
                if (stripos($name, $keywords)!== false) {
                    $return[$prodcat_id] = $name;
                }
            } else {
                $return[$prodcat_id] = $name;
            }
            $return += self::getProdCatTreeStructureSearch($prodcat_id, $langId, $keywords, $level+1, $name, $isActive, $isDeleted, $isForCsv);
            //print_r($return); die;
        }
        return $return;
    }

    public function getAutoCompleteProdCatTreeStructure($parent_id = 0, $langId = 0, $keywords = '', $level = 0, $name_prefix = '', $isActive = true, $isDeleted = true, $isForCsv = false)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId, false);
        //$srch->addOrder('catOrder','asc');
        //$srch->addOrder('m.prodcat_display_order','asc');
        $srch->addOrder('prodcat_id', 'asc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('prodcat_id','prodcat_active','prodcat_deleted','prodcat_name','prodcat_code'));
        $rs = $srch->getResultSet();
        $catRecords = FatApp::getDb()->fetchAll($rs, 'prodcat_id');


        $srch = static::getSearchObject(false, $langId, $isActive);
        if ($langId) {
            $srch->addFld('m.prodcat_id, COALESCE(pc_l.prodcat_name, m.prodcat_identifier) as prodcat_name');
        } else {
            $srch->addFld('m.prodcat_id, m.prodcat_identifier as prodcat_name');
        }
        //$srch->addFld('GETCATORDERCODE(prodcat_id) as catOrder');
        $srch->addFld('prodcat_ordercode as catOrder');
        if ($isDeleted) {
            $srch->addCondition('m.prodcat_deleted', '=', 0);
        }

        if ($isActive) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }
        if ($parent_id>0) {
            $srch->addCondition('m.prodcat_id', '=', FatUtility::int($parent_id));
        }

        if (!empty($keywords)) {
            $srch->addCondition('prodcat_name', 'like', '%'.$keywords.'%');
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('catOrder', 'asc');
        $srch->addOrder('prodcat_name', 'asc');
        //echo $srch->getQuery();
        $rs = $srch->getResultSet();
        $return = array();

        $records = FatApp::getDb()->fetchAll($rs);
        foreach ($records as $prodCats) {
            $level = 0;
            $seprator = '';
            $name_prefix='';
            $categoryCode = substr($catRecords[$prodCats['prodcat_id']]['prodcat_code'], 0, -1);
            $prodCat = explode("_", $categoryCode);
            foreach ($prodCat as $key => $prodcatParent) {
                // var_dump($catRecords[FatUtility::int($prodcatParent)]);
                if ($catRecords[FatUtility::int($prodcatParent)]['prodcat_deleted'] !=applicationConstants::NO  || $catRecords[FatUtility::int($prodcatParent)]['prodcat_active']!=applicationConstants::ACTIVE) {
                    break;
                }
                if ($level > 0) {
                    if ($isForCsv) {
                        $seprator = '->-> ';
                    } else {
                        $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
                    }
                    $seprator = CommonHelper::renderHtml($seprator);
                }
                $productCatName = $catRecords[FatUtility::int($prodcatParent)]['prodcat_name'];

                $name_prefix = $name_prefix .$seprator. $productCatName;

                $return[$prodCats['prodcat_id']] = $name_prefix;
                $level++;
            }
        }
        return $return;
    }


    public static function getProdCatParentChildWiseArr($langId = 0, $parentId = 0, $includeChildCat = true, $forSelectBox = false, $sortByName = false, $prodCatSrchObj = false, $excludeCategoriesHavingNoProducts = false)
    {
        $parentId = FatUtility::int($parentId);
        $langId = FatUtility::int($langId);
        if (!$langId) {
            trigger_error("Language not specified", E_USER_ERROR);
        }
        if (is_object($prodCatSrchObj)) {
            $prodCatSrch = clone $prodCatSrchObj;
        } else {
            $prodCatSrch = new ProductCategorySearch($langId);
            $prodCatSrch->setParent($parentId);
        }
        $prodCatSrch->doNotCalculateRecords();
        $prodCatSrch->doNotLimitRecords();

        $prodCatSrch->addMultipleFields(array( 'prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name','substr(prodcat_code,1,6) AS prodrootcat_code', 'prodcat_content_block','prodcat_active','prodcat_parent','prodcat_code as prodcat_code'));

        if ($excludeCategoriesHavingNoProducts) {
            $prodSrchObj = new ProductSearch();
            $prodSrchObj->setDefinedCriteria();
            $prodSrchObj->joinProductToCategory();
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->doNotLimitRecords();
            $prodSrchObj->joinSellerSubscription(0, true);
            $prodSrchObj->addSubscriptionValidCondition();

            //$prodSrchObj->addGroupBy('selprod_id');
            $prodSrchObj->addGroupBy('c.prodcat_id');
            $prodSrchObj->addMultipleFields(array('count(selprod_id) as productCounts', 'c.prodcat_id as qryProducts_prodcat_id'));
            $prodSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $prodCatSrch->joinTable('('.$prodSrchObj->getQuery().')', 'LEFT OUTER JOIN', 'qryProducts.qryProducts_prodcat_id = c.prodcat_id', 'qryProducts');
            $prodCatSrch->addCondition('qryProducts.productCounts', '>', 0);
            $prodCatSrch->addFld(array('COALESCE(productCounts, 0) as productCounts'));
        }

        if ($sortByName) {
            $prodCatSrch->addOrder('prodcat_name');
            $prodCatSrch->addOrder('prodcat_identifier');
        } else {
            $prodCatSrch->addOrder('prodrootcat_code');
            $prodCatSrch->addOrder('prodcat_ordercode');
        }
        
        $rs = $prodCatSrch->getResultSet();

        if ($forSelectBox) {
            $categoriesArr = FatApp::getDb()->fetchAllAssoc($rs);
        } else {
            $categoriesArr = FatApp::getDb()->fetchAll($rs);
        }

        if (true === $includeChildCat && $categoriesArr) {
            foreach ($categoriesArr as $key => $cat) {
                $categoriesArr[$key]['icon'] = CommonHelper::generateFullUrl('Category', 'icon', array($cat['prodcat_id'], $langId, 'COLLECTION_PAGE'));
                $categoriesArr[$key]['children'] = self::getProdCatParentChildWiseArr($langId, $cat['prodcat_id']);
            }
        }
        return $categoriesArr;
    }

    public static function getRootProdCatArr($langId)
    {
        $langId = FatUtility::int($langId);
        if (!$langId) {
            trigger_error(Labels::getLabel('ERR_Language_Not_Specified', $langId), E_USER_ERROR);
        }
        return static::getProdCatParentChildWiseArr($langId, 0, false, true);
    }

    public function canRecordMarkDelete($prodcat_id)
    {
        $srch = static::getSearchObject(false, 0, false);
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addCondition('m.prodcat_id', '=', $prodcat_id);
        $srch->addFld('m.prodcat_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['prodcat_id']==$prodcat_id) {
            return true;
        }
        return false;
    }

    public function canRecordUpdateStatus($prodcat_id)
    {
        $srch = static::getSearchObject();
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addCondition('m.prodcat_id', '=', $prodcat_id);
        $srch->addFld('m.prodcat_id,m.prodcat_active');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['prodcat_id']==$prodcat_id) {
            return $row;
        }
        return false;
    }
    /* function getSubCategory(){
        $srch = new SearchBase(static::DB_TBL, 'prodSubCate');
        $srch->addCondition('prodSubCate.prodcat_deleted', '=',0);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('prodSubCate.prodcat_parent');
        $srch->addMultipleFields(array('prodSubCate.prodcat_parent',"COUNT(prodSubCate.prodcat_id) AS total_sub_cats"));
        return $srch;
    } */

    public static function recordCategoryWeightage($categoryId)
    {
        /* $categoryId =  FatUtility::int($categoryId);
        if(1 > $categoryId){ return false;}
        $obj = new SmartUserActivityBrowsing();
        return $obj->addUpdate($categoryId,SmartUserActivityBrowsing::TYPE_CATEGORY); */
    }

    public static function getDeletedProductCategoryByIdentifier($identifier = '')
    {
        $srch = static::getSearchObject(false, 0, false);
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::YES);
        $srch->addCondition('m.prodcat_identifier', '=', $identifier);

        $srch->addFld('m.prodcat_id');
        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetch($rs);
        if ($row) {
            return $row['prodcat_id'];
        } else {
            return false;
        }
    }
    /* public static function getCatName($id,$categoryArr) {
            if (!array_key_exists($id, $categoryArr)) {
                $categoryArr[$id] = productCategory::getAttributesByLangId($id, 'prodcat_name');
            }
            return $categoryArr[$id];
    } */

    public static function getProductCategoryName($id, $langId)
    {
        $srch = static::getSearchObject(false, $langId);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addCondition('m.prodcat_id', '=', $id);
        $srch->addFld('COALESCE(prodcat_name,prodcat_identifier) as prodcat_name');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row) {
            return $row['prodcat_name'];
        } else {
            return false;
        }
    }

    public function getCategoryTreeForSearch($siteLangId, $categories, &$globalCatTree = array(), $attr = array())
    {
        if ($categories) {
            $remainingCatCods =  $categories;
            $catId = $categories[0];
            unset($remainingCatCods[0]);
            $remainingCatCods = array_values($remainingCatCods);
            $catId = FatUtility::int($catId);
            if (!empty($attr) && is_array($attr)) {
                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addMultipleFields(array( 'prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name','substr(prodcat_code,1,6) AS prodrootcat_code', 'prodcat_content_block','prodcat_active','prodcat_parent','prodcat_code as prodcat_code'));
                $prodCatSrch->addCondition('prodcat_id', '=', $catId);
                $rs = $prodCatSrch->getResultSet();
                $rows = FatApp::getDb()->fetch($rs);
                foreach ($rows as $key => $val) {
                    $globalCatTree[$catId][$key] = $val;
                }
            } else {
                /* $globalCatTree[$catId]['prodcat_name'] = productCategory::getAttributesByLangId($siteLangId,$catId,'prodcat_name'); */

                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addFld('COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name');
                $prodCatSrch->addCondition('prodcat_id', '=', $catId);
                $rs = $prodCatSrch->getResultSet();
                $rows = FatApp::getDb()->fetch($rs);

                $globalCatTree[$catId]['prodcat_name'] = $rows['prodcat_name'];
                $globalCatTree[$catId]['prodcat_id'] = $catId;
            }
            //$globalCatTree[$catId]['prodcat_id']['children'] = '';
            if (count($remainingCatCods)>0) {
                self::getCategoryTreeForSearch($siteLangId, $remainingCatCods, $globalCatTree[$catId]['children'], $attr);
            }
        }
    }

    public function getCategoryTreeArr($siteLangId, $categoriesDataArr, $attr = array())
    {
        foreach ($categoriesDataArr as $categoriesData) {
            $categoryCode = substr($categoriesData['prodcat_code'], 0, -1);
            $prodCats = explode("_", $categoryCode);
            $remaingCategories = $prodCats;
            unset($remaingCategories[0]);
            $remaingCategories = array_values($remaingCategories);

            $parentId = FatUtility::int($prodCats[0]);
            if (!array_key_exists($parentId, $this->categoryTreeArr)) {
                $this->categoryTreeArr [$parentId] = array();
            }
            if (!empty($attr) && is_array($attr)) {
                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addMultipleFields($attr);
                $prodCatSrch->addCondition('prodcat_id', '=', FatUtility::int($prodCats[0]));
                $rs = $prodCatSrch->getResultSet();
                $rows = FatApp::getDb()->fetch($rs);
                foreach ($rows as $key => $val) {
                    $this->categoryTreeArr [$parentId][$key] = $val;
                }
            } else {
                /* $this->categoryTreeArr [$parentId]['prodcat_name'] = productCategory::getAttributesByLangId($siteLangId,FatUtility::int($prodCats[0]),'prodcat_name'); */
                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addFld('COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name');
                $prodCatSrch->addCondition('prodcat_id', '=', FatUtility::int($prodCats[0]));
                $rs = $prodCatSrch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);

                $this->categoryTreeArr [$parentId]['prodcat_name'] = $row['prodcat_name'];
                $this->categoryTreeArr [$parentId]['prodcat_id'] =  FatUtility::int($prodCats[0]);
            }

            if (!isset($this->categoryTreeArr [$parentId]['children'])) {
                $this->categoryTreeArr [$parentId]['children'] = array();
            }
            productCategory::getCategoryTreeForSearch($siteLangId, $remaingCategories, $this->categoryTreeArr[$parentId]['children'], $attr);
        }
        return $this->categoryTreeArr ;
    }

    public function getProdRootCategoriesWithKeyword($langId = 0, $keywords = '', $returnWithChildArr = false, $prodcatCode = false, $inludeChildCount = false)
    {
        $srch = static::getSearchObject($inludeChildCount, $langId);
        $srch->addFld('m.prodcat_id,COALESCE(pc_l.prodcat_name,m.prodcat_identifier) as prodcat_name,m.prodcat_parent,substr(m.prodcat_code,1,6) AS prodrootcat_code');
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        if (!empty($keywords)) {
            $cnd = $srch->addCondition('m.prodcat_identifier', 'like', '%'.$keywords.'%');
            $cnd->attachCondition('pc_l.prodcat_name', 'like', '%'.$keywords.'%');
        }
        $srch->addOrder('m.prodcat_parent', 'asc');
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('m.prodcat_identifier', 'asc');
        if ($returnWithChildArr == false) {
            $srch->addFld('count(m.prodcat_id) as totalRecord');
            $srch->addGroupBy('prodrootcat_code');
        }

        if ($prodcatCode) {
            $srch->addHaving('prodrootcat_code', '=', $prodcatCode);
        }

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $return = array();
        if ($returnWithChildArr) {
            foreach ($records as $row) {
                if ($row['prodcat_parent']>0) {
                    $return[$row['prodrootcat_code']][$row['prodcat_id']]['structure'] = self::getParentTreeStructure($row['prodcat_id'], 0, '', $langId);
                    $return[$row['prodrootcat_code']][$row['prodcat_id']]['prodcat_name'] = $row['prodcat_name'];
                }
            }
        } else {
            $return = $records;
        }
        return $return;
    }

    public function categoriesHaveProducts($siteLangId)
    {
        $prodSrchObj = new ProductSearch($siteLangId);
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();
        $prodSrchObj->addGroupBy('prodcat_id');
        $prodSrchObj->addMultipleFields(array('substr(prodcat_code,1,6) AS prodrootcat_code','count(selprod_id) as productCounts', 'prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_parent'));
        $rs = $prodSrchObj->getResultSet();
        $productRows = FatApp::getDb()->fetchAll($rs);
        /* die(CommonHelper::printArray($productRows)); */
        $categoriesMainRootArr = array();
        if ($productRows) {
            $categoriesMainRootArr = array_unique(array_column($productRows, 'prodcat_id'));
            array_flip($categoriesMainRootArr);
        }
        return $categoriesMainRootArr;
    }

    public function rewriteUrl($keyword, $suffixWithId = true, $parentId = 0)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $parentId =  FatUtility::int($parentId);
        $parentUrl = '';
        if (0 < $parentId) {
            $parentUrlRewriteData = UrlRewrite::getDataByOriginalUrl(ProductCategory::REWRITE_URL_PREFIX.$parentId);
            if (!empty($parentUrlRewriteData)) {
                $parentUrl = preg_replace('/-'.$parentId.'$/', '', $parentUrlRewriteData['urlrewrite_custom']);
            }
        }

        $originalUrl = ProductCategory::REWRITE_URL_PREFIX.$this->mainTableRecordId;

        $keyword = preg_replace('/-'.$this->mainTableRecordId.'$/', '', $keyword);
        $seoUrl =  CommonHelper::seoUrl($keyword);
        if ($suffixWithId) {
            $seoUrl =  $seoUrl.'-'.$this->mainTableRecordId;
        }

        $seoUrl = str_replace($parentUrl, '', $seoUrl);
        $seoUrl = $parentUrl.'-'.$seoUrl;

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl, $this->mainTableRecordId);
        return UrlRewrite::update($originalUrl, $customUrl);
    }

    public static function setImageUpdatedOn($userId, $date = '')
    {
        $date = empty($date) ? date('Y-m-d  H:i:s') : $date;
        $where = array('smt'=>'prodcat_id = ?', 'vals'=>array($userId));
        FatApp::getDb()->updateFromArray(static::DB_TBL, array('prodcat_img_updated_on'=>date('Y-m-d  H:i:s')), $where);
    }
}
