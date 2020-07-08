<?php
class SelProdReviewHelpful extends MyAppModel
{
    const DB_TBL = 'tbl_seller_product_reviews_helpful';
    const DB_TBL_PREFIX = 'sprh_';

    const REVIEW_IS_HELPFUL = 1;
    const REVIEW_IS_NOT_HELPFUL = 0;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public function getdata()
    {
        $srch = new SearchBase(static::DB_TBL, 'sprh');
        $srch->addCondition(static::DB_TBL_PREFIX . 'spreview_id', '=', $this->mainTableRecordId);
        $srch->addMultipleFields(
            array('sum(if(sprh_helpful = 1 , 1 ,0)) as helpful', 'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful')
        );
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        if ($row = $db->fetch($rs)) {
            return $row;
        }
        return array();
    }
}
