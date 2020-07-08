<?php
class BlogPostCategorySearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        parent::__construct(BlogPostCategory::DB_TBL, 'bpc');
        $this->langId = FatUtility::int($langId);

        if ($this->langId > 0) {
            $this->joinTable(
                BlogPostCategory::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'bpcategorylang_bpcategory_id = bpc.bpcategory_id
			AND bpcategorylang_lang_id = ' . $langId,
                'bpc_l'
            );
        }
        $this->addCondition('bpcategory_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('bpcategory_deleted', '=', 0);
        $this->addOrder('GETBLOGCATORDERCODE(bpcategory_id)');
        $this->doNotCalculateRecords();
        $this->doNotLimitRecords();
    }

    public function setParent($parentId = 0)
    {
        $this->addCondition('bpcategory_parent', '=', $parentId);
    }
}
