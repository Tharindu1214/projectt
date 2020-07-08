<?php
class SearchItem extends MyAppModel
{
    public function __construct()
    {
        $this->db = FatApp::getDb();
    }

    public function addSearchResult($data = array())
    {
        $keyword = str_replace('mysql_func_', 'mysql_func ', $data['keyword']);

        $assign_fields = array(
        'searchitem_keyword'=>$keyword,
        'searchitem_date'=>date('Y-m-d'),
        );
        $onDuplicateKeyUpdate = array_merge($assign_fields, array('searchitem_count'=>'mysql_func_searchitem_count+1'));
        $this->db->insertFromArray('tbl_search_items', $assign_fields, true, array(), $onDuplicateKeyUpdate);
    }

    public static function getTopSearchedKeywords()
    {
        $db = FatApp::getDb();
        $srch = new SearchBase('tbl_search_items', 'ts');
        $srch->addDirectCondition("LENGTH(searchitem_keyword) > 10 and searchitem_keyword REGEXP '^[A-Za-z0-9 ]+$'");
        $srch->addMultipleFields(array('DISTINCT searchitem_keyword'));
        $srch->addOrder('searchitem_count', 'desc');
        $srch->setPageSize(4);
        $rs = $srch->getResultSet();
        // $this->total_records = $srch->recordCount();
        // $this->total_pages = $srch->pages();
        $row = $db->fetchAll($rs);
        if ($row == false) {
            return array();
        } else {
            return $row;
        }
    }

    public static function convertUrlStringToArr($string)
    {
        return $arr = explode('/', $string);
    }


    public static function convertArrToSrchFiltersAssocArr($arr)
    {
        $arr_url_params = array();
        if (!empty($arr)) {
            foreach ($arr as $key => $val) {
                $firstDashPosition = strpos($val, '-');
                $keyString = strtolower(substr($val, 0, $firstDashPosition));
                $valueString = substr($val, $firstDashPosition+1);

                switch ($keyString) {
                    case 'price_min_range':
                    case 'price_max_range':
                        $arr_url_params[$keyString] = $valueString;
                        break;
                    case 'price':
                        $lastOccurenceDashPosition = strripos($valueString, '-');
                        $arr_url_params[$keyString.'-'.substr($valueString, 0, $lastOccurenceDashPosition)] = substr($valueString, $lastOccurenceDashPosition+1);
                        break;
                    case 'currency':
                        $arr_url_params['currency_id'] = $valueString;
                        break;
                    case 'sort':
                        $arr_url_params['sortOrder'] = $arr_url_params['sortBy'] = str_replace('-', '_', $valueString);
                        break;
                    case 'shop':
                    case 'shop_id':
                        $arr_url_params['shop_id'] = $valueString;
                        break;
                    case 'collection':
                        $arr_url_params['collection_id'] = $valueString;
                        break;
                    case 'keyword':
                        $arr_url_params[$keyString] = urldecode($valueString);
                    break;
                    case 'page':
                    case 'category':
                        $arr_url_params[$keyString] = $valueString;
                        break;
                    case 'pagesize':
                        $arr_url_params['pageSize'] = $valueString;
                        break;
                    case 'availability':
                        $dashPosition = strpos($valueString, '-');
                        $id = substr($valueString, 0, $dashPosition);
                        $arr_url_params['out_of_stock'] = $id;
                        break;
                    case 'brand':
                    case 'prodcat':
                    case 'optionvalue':
                    case 'condition':
                        $dashPosition =strpos($valueString, '-');
                        $id = substr($valueString, 0, $dashPosition);
                        $valueString = substr($valueString, $dashPosition+1);
                        if (!array_key_exists($keyString, $arr_url_params)) {
                            $arr_url_params[$keyString] = array() ;
                        }
                        if (!in_array($id, $arr_url_params[$keyString])) {
                            array_push($arr_url_params[$keyString], $id);
                        }
                        break;
                    default:
                        $arr_url_params[$keyString] = $valueString;
                        break;
                }
            }
        }

        return $arr_url_params;
    }
}
