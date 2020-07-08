<?php
class Tag extends MyAppModel
{
    const DB_TBL = 'tbl_tags';
    const DB_TBL_PREFIX = 'tag_';

    const DB_LANG_TBL  = 'tbl_tags_lang';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 't');
        if ($langId) {
            $srch->joinTable(static::DB_LANG_TBL, 'LEFT OUTER JOIN', 't.tag_id = t_l.taglang_tag_id AND t_l.taglang_lang_id = '.$langId, 't_l');
        }
        return $srch;
    }

    public static function requiredTagsFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'tag_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'tag_identifier',
                'tag_name',
                'credential_username',
                'tag_user_id',
            ),
            ImportexportCommon::VALIDATE_INT => array(
                'tag_user_id',
            ),
        );
    }

    public static function validateTagsFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredTagsFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredProdTagsFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'product_id',
                'tag_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_identifier',
                'tag_identifier',
            ),
        );
    }

    public static function validateProdTagsFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredProdTagsFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public function addUpdateData($data = array(), $onDuplicateUpdateData = array())
    {
        $record = new TableRecord(static::DB_TBL);
        $record->assignValues($data);
        if (!$record->addNew($data, $onDuplicateUpdateData)) {
            $this->error = $record->getError();
            return false;
        }
        return  $record->getMainTableRecordId();
    }

    public function canRecordDelete($id)
    {
        $srch =static::getSearchObject();
        $srch->addCondition('t.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('t.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }

    public static function recordTagWeightage($tagId)
    {
        /* $tagId = FatUtility::int($tagId);
        if(1 > $tagId){ return false;}
        $obj = new SmartUserActivityBrowsing();
        return $obj->addUpdate($tagId,SmartUserActivityBrowsing::TYPE_TAG); */
    }

    /**
    * tag delete.
    * get array of all product ids having that tag
    * delete records from product_to_tag having that tag
    * updateProductTagString for each product.
    * Product category association.
    * When tag is added or removed from product. call updateProductTagString($productId)
    **/
    public static function updateProductTagString($productId = 0)
    {
        $productId = FatUtility::int($productId);
        if (!$productId) {
            return;
        }
        //$db = FatApp::getDb();
        //$productSrchObj = Product::getSearchObject();
        $languages = Language::getAllNames();

        //product_tags_string
        $productTagsStringArr = array();
        $product_tags_string = array();

        $prodObj = new Product($productId);

        if ($languages) {
            foreach ($languages as $lang_id => $lang_name) {
                $productTags = Product::getProductTags($productId, $lang_id);
                $productName = Product::getAttributesBylangId($lang_id, $productId, 'product_name');
                if (!$productName) {
                    $productData = Product::getProductDataById(FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1), $productId, array('ifNull(product_name,product_identifier) as product_name','product_identifier'));
                    $productName = $productData['product_name'];
                }



                if ($productTags) {
                    foreach ($productTags as $tag) {
                        $productTagsStringArr[$lang_id][] = ($tag['tag_name'] == '') ? $tag['tag_identifier'] : $tag['tag_name'];
                    }
                    $product_tags_string[$lang_id] = implode("| ", $productTagsStringArr[$lang_id]);

                    if (!empty($productTagsStringArr[$lang_id])) {
                        $data_to_update = array( 'product_tags_string' => $product_tags_string[$lang_id]);
                        if ($productName) {
                            $data_to_update['product_name'] = $productName;
                        }
                        if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
                        }
                    }/*  else {
                        $data_to_update = array('product_tags_string' => '');
                        if( !$prodObj->updateLangData( $lang_id, $data_to_update ) ){}
                    } */
                } else {
                    $productName = !empty($productName) ? $productName : $productData['product_identifier'];

                    $data_to_update = array( 'product_tags_string' => '', 'product_name' => $productName);
                    if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
                    }
                }
            }
        }

        /* if ( $productId ) {
            $rs = $db->query('SELECT product_id FROM tbl_products');
            while ($row = $db->fetch($rs)) {
                static::updateProductTagString($row['product_id']);
            }
        } */

        // Select all tag names for the prouct. Implode those with ', ' and update in tbl_products.
        // include category names also here.
    }

    public static function updateTagStrings($tagId)
    {
        $tagId = FatUtility::int($tagId);
        if (!$tagId) {
            return;
        }

        $rows = Product::getProductIdsByTagId($tagId);
        if (!empty($rows)) {
            foreach ($rows as $row) {
                static::updateProductTagString($row['ptt_product_id']);
            }
        }

        // get all product ids having this tag. for each updateProductTagString($productId);
    }

    public function save()
    {
        $res = parent::save();
        if (false == $res) {
            return $res;
        }
        static::updateTagStrings($this->mainTableRecordId);
        return $res;
    }
}
