<?php
class BlogPost extends MyAppModel
{
    const DB_TBL = 'tbl_blog_post';
    const DB_TBL_PREFIX = 'post_';
    const DB_LANG_TBL ='tbl_blog_post_lang';
    const DB_LANG_TBL_PREFIX ='postlang_';
    const DB_POST_TO_CAT_TBL ='tbl_blog_post_to_category';
    const DB_POST_TO_CAT_TBL_PREFIX ='ptc_';
    const REWRITE_URL_PREFIX = 'blog/post-detail/';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $joinCategory = true, $post_published = false, $categoryActive = false)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'bp');
        $srch->addOrder('bp.post_published', 'DESC');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'bp_l.'.static::DB_LANG_TBL_PREFIX.'post_id = bp.'.static::tblFld('id').' and
			bp_l.'.static::DB_LANG_TBL_PREFIX.'lang_id = '.$langId,
                'bp_l'
            );
        }

        if ($joinCategory) {
            $srch->joinTable(
                static::DB_POST_TO_CAT_TBL,
                'LEFT OUTER JOIN',
                'bptc.'.static::DB_POST_TO_CAT_TBL_PREFIX.'post_id = bp.'.static::tblFld('id'),
                'bptc'
            );

            $srch->joinTable(
                BlogPostCategory::DB_TBL,
                'LEFT OUTER JOIN',
                'bptc.'.static::DB_POST_TO_CAT_TBL_PREFIX.'bpcategory_id = bpc.'.BlogPostCategory::tblFld('id').' and bpc.bpcategory_deleted =0',
                'bpc'
            );
            if ($langId > 0) {
                $srch->joinTable(
                    BlogPostCategory::DB_TBL_LANG,
                    'LEFT OUTER JOIN',
                    'bpc_l.'.BlogPostCategory::DB_LANG_TBL_PREFIX.'bpcategory_id = bpc.'.BlogPostCategory::tblFld('id').' and bpc_l.'.BlogPostCategory::DB_LANG_TBL_PREFIX.'lang_id = '.$langId,
                    'bpc_l'
                );
            }
        }

        if ($categoryActive) {
            $srch->addCondition('bpc.bpcategory_active', '=', applicationConstants::ACTIVE);
        }
        if ($post_published) {
            $srch->addCondition('bp.post_published', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('bp.post_deleted', '=', applicationConstants::NO);
        return $srch;
    }

    public static function getBlogPostsUnderCategory($langId, $bpcategory_id)
    {
        $srch = BlogPost::getSearchObject($langId);
        $srch->addCondition('postlang_post_id', 'is not', 'mysql_func_null', 'and', true);
        $srch->addCondition('ptc_bpcategory_id', '=', $bpcategory_id);
        $srch->addGroupby('post_id');
        return FatApp::getDb()->fetchAll($srch->getResultSet());
    }

    public function updateImagesOrder($post_id, $order)
    {
        $post_id = FatUtility :: int($post_id);
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }
                FatApp::getDb()->updateFromArray('tbl_attached_files', array('afile_display_order' => $i), array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_id = ?','vals' => array(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $post_id, $id)));
            }
            return true;
        }
        return false;
    }

    public function getPostCategories($post_id)
    {
        $srch = new SearchBase(static::DB_POST_TO_CAT_TBL, 'ptc');
        $srch->addCondition(static::DB_POST_TO_CAT_TBL_PREFIX . 'post_id', '=', $post_id);

        $srch->joinTable(BlogPostCategory::DB_TBL, 'INNER JOIN', BlogPostCategory::DB_TBL_PREFIX.'id = ptc.'.static::DB_POST_TO_CAT_TBL_PREFIX.'bpcategory_id', 'cat');
        $srch->addMultipleFields(array('bpcategory_id'));

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        if (!$records) {
            return false;
        }
        return $records;
    }

    public function addUpdateCategories($post_id, $categories = array())
    {
        if (!$post_id) {
            $this->error = Labels::getLabel('MSG_Invalid_Request!', $this->commonLangId);
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_POST_TO_CAT_TBL, array('smt'=> static::DB_POST_TO_CAT_TBL_PREFIX.'post_id = ?','vals' => array($post_id) ));
        if (empty($categories)) {
            return true;
        }

        $record = new TableRecord(static::DB_POST_TO_CAT_TBL);
        foreach ($categories as $category_id) {
            $to_save_arr = array();
            $to_save_arr['ptc_post_id'] = $post_id;
            $to_save_arr['ptc_bpcategory_id'] = $category_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function rewriteUrl($keyword, $suffixWithId = true)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $originalUrl = BlogPost::REWRITE_URL_PREFIX.$this->mainTableRecordId;

        $keyword = preg_replace('/-'.$this->mainTableRecordId.'$/', '', $keyword);
        $seoUrl =  CommonHelper::seoUrl($keyword);

        if ($suffixWithId) {
            $seoUrl =  $seoUrl.'-'.$this->mainTableRecordId;
        }

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

    public function canMarkRecordDelete()
    {
        $post_id = FatUtility::int($this->mainTableRecordId);
        if ($post_id > 0) {
            $srch = static::getSearchObject();
            $srch->addCondition('post_deleted', '=', applicationConstants::NO);
            $srch->addCondition('post_id', '=', $post_id);
            $srch->addFld('post_id');
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);
            if (!empty($row) && $row['post_id']==$post_id) {
                return true;
            }
        }
        return false;
    }

    public function deleteBlogPostImage($post_id, $image_id)
    {
        $post_id = FatUtility :: int($post_id);
        $image_id = FatUtility :: int($image_id);
        if ($post_id < 1 || $image_id < 1) {
            $this->error = Labels::getLabel('MSG_Invalid_Request!', $this->commonLangId);
            return false;
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $post_id, $image_id)) {
            $this->error = $fileHandlerObj->getError();
            return false;
        }
        return true;
    }

    public static function convertArrToSrchFiltersAssocArr($arr)
    {
        return SearchItem::convertArrToSrchFiltersAssocArr($arr);
    }

    public function setPostViewsCount($post_id = 0)
    {
        $post_id = FatUtility :: int($post_id);
        if ($post_id < 1) {
            $this->error = Labels::getLabel('MSG_Invalid_Request!', $this->commonLangId);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL, 'bp');
        $srch->addCondition('post_id', '=', $post_id);
        $srch->addFld('post_view_count');
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $result_data = $this->db->fetch($rs);
        $record = new TableRecord(static::DB_TBL);
        $assign_field['post_view_count'] = $result_data['post_view_count'] + 1;
        $record->assignValues($assign_field);
        if ($record->update(array('smt' => '`post_id`=?', 'vals' => array($post_id)))) {
            return true;
        }
        $this->error = $this->db->getError();
        return false;
    }
}
