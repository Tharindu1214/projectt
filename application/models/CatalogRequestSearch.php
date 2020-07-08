<?php
class CatalogRequestSearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        parent::__construct(User::DB_TBL_USR_CATALOG_REQ, 'scatrequest');
    }

    public function addDateFromCondition($dateFrom)
    {
        $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
        $dateFrom = date('Y-m-d', strtotime($dateFrom));

        if ($dateFrom != '') {
            $this->addCondition('scatrequest_date', '>=', $dateFrom. ' 00:00:00');
        }
    }

    public function addDateToCondition($dateTo)
    {
        $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
        $dateTo = date('Y-m-d', strtotime($dateTo));

        if ($dateTo != '') {
            $this->addCondition('scatrequest_date', '<=', $dateTo. ' 23:59:59');
        }
    }
}
