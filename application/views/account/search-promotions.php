<?php defined('SYSTEM_INIT') or die('Invalid Usage'); $prm_budget_dur_arr = Promotion::getPromotionBudgetDurationArr($siteLangId); ?>
<?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo CommonHelper::renderHtml($error_warning); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php }
    $arr_flds = array(
        'promotion_image'=>'',
        'promotion_id' => Labels::getLabel('LBL_ID', $siteLangId),
        'promotion_identifier' => Labels::getLabel('LBL_Name', $siteLangId),
        'promotion_type' => Labels::getLabel('LBL_Type', $siteLangId),
        'promotion_cost' => Labels::getLabel('LBL_CPC', $siteLangId),
        'promotion_budget' => Labels::getLabel('LBL_Budget', $siteLangId),
        'promotion_clicks' => Labels::getLabel('LBL_Clicks', $siteLangId),
        'promotion_duration' => Labels::getLabel('LBL_Duration', $siteLangId),
        'action' => Labels::getLabel('LBL_Action', $siteLangId),
    );

    $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
    $th = $tbl->appendElement('thead')->appendElement('tr');
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }

    $sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));

    foreach ($promotions as $sn => $row) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ($row['promotion_status'] == 0) ? 'fat-inactive' : '' ));

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'promotion_image':
                    if ($row['promotion_type']==Promotions::PROMOTE_PRODUCT) {
                        $td->appendElement('plaintext', array(), '<div class="avtar"><img src="'.FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['promotion_product_id'],'MINI',0,0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg').'" alt="'.$row["prod_name"].'"></div>', true);
                    } elseif ($row['promotion_type'] == Promotions::PROMOTE_SHOP) {
                        $td->appendElement('plaintext', array(), '<div class="avtar"><img src="'.CommonHelper::generateUrl('image', 'shop', array($product['promotion_shop_id'], 'MINI', 0, 0, $siteLangId)).'" alt="'.$row["shop_identifier"].'"></div>', true);
                    } elseif ($row['promotion_type'] == Promotions::PROMOTE_BANNER) {
                        // $td->appendElement('plaintext', array(), '<div class="avtar"><img src="'.CommonHelper::generateUrl('image','promotion-banner',array($row["promotion_banner_file"],'MINI')).'" alt=""></div>' , true);
                    }
                    break;
                case 'promotion_id':
                    $td->appendElement('plaintext', array(), $row["promotion_number"] . '<br>', true);
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    break;

                case 'promotion_identifier':
                    if ($row['promotion_type']==Promotions::PROMOTE_PRODUCT) {
                        $td->appendElement('plaintext', array(), $row["prod_name"] . '<br>', true);
                        $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    } elseif ($row['promotion_type']==Promotions::PROMOTE_SHOP) {
                        $td->appendElement('plaintext', array(), $row["shop_identifier"] . '<br>', true);
                        $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    } elseif ($row['promotion_type']==Promotions::PROMOTE_BANNER) {
                        $td->appendElement('plaintext', array(), $row["promotion_banner_name"] . '<br>', true);
                        $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    }
                    if (isset($row['promotion_min_balance'])) {
                        if ($row['promotion_min_balance']==1) {
                            $td->appendElement('plaintext', array(), '<span class="text-danger">***</span><br>', true);
                        }
                    }
                    break;

                case 'promotion_type':
                    if ($row['promotion_type']==Promotions::PROMOTE_PRODUCT) {
                        $td->appendElement('plaintext', array(), Labels::getLabel('LBL_Product', $siteLangId) .'<br>', true);
                        $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    } elseif ($row['promotion_type']==Promotions::PROMOTE_SHOP) {
                        $td->appendElement('plaintext', array(), Labels::getLabel('LBL_Shop', $siteLangId) . '<br>', true);
                        $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    } elseif ($row['promotion_type']==Promotions::PROMOTE_BANNER) {
                        $td->appendElement('plaintext', array(), Labels::getLabel('LBL_Banner', $siteLangId) . '<br>', true);
                        $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    }
                    break;
                case 'promotion_cost':
                    $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row["promotion_cost"]) . '<br>', true);
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    break;
                case 'promotion_budget':
                    $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row["promotion_budget"]). '/'. $prm_budget_dur_arr[$row["promotion_budget_period"]] . '<br>', true);
                    $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    break;
                case 'promotion_clicks':
                    $td->appendElement(
                        'a',
                        array('title' => Labels::getLabel('LBL_Enable', $siteLangId), 'title'=>Labels::getLabel('LBL_Edit', $siteLangId), "href"=>CommonHelper::generateUrl('account', 'promotion_clicks', array($row['promotion_id']))),
                        $row["totClicks"],
                        true
                    );
                    break;
                case 'promotion_duration':
                    $td->appendElement('plaintext', array(), FatDate::format($row["promotion_start_date"]) .'-'. FatDate::format($row["promotion_end_date"]) .'<br/>'. Labels::getLabel('LBL_Time', $siteLangId) .':'. date(date('H:i', strtotime($row["promotion_start_time"]))) .'-'. date(date('H:i', strtotime($row["promotion_end_time"]))). '<br>', true);
                    break;
                case 'action':
                    $ul = $td->appendElement("ul", array("class"=>"actions"), ' ', true);
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array( 'class'=>'',
                    'title'=>Labels::getLabel('LBL_Edit', $siteLangId),"onclick"=>"promotionGeneralForm(".$row['promotion_id'].")"),
                        '<i class="fa fa-edit"></i>',
                        true
                    );
                    if ($row['promotion_status']==0):
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        "a",
                        array('title' => Labels::getLabel('LBL_Enable', $siteLangId),
                    'onclick' => '', 'href'=>CommonHelper::generateUrl('account', 'promotion_status', array($row['promotion_id'], 'unblock', $row['promotion_type']))),
                        '<i class="fa fa-toggle-on"></i>',
                        true
                    );
                    else :
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        "a",
                        array('title' => Labels::getLabel('LBL_Disable', $siteLangId),
                    'onclick' => '', 'href'=>CommonHelper::generateUrl('account', 'promotion_status', array($row['promotion_id'], 'block', $row['promotion_type']))),
                        '<i class="fa fa-toggle-off"></i>',
                        true
                    );
                    endif;
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        "a",
                        array('title' => Labels::getLabel('LBL_Analytics', $siteLangId),
                    'onclick' => '', 'href'=>CommonHelper::generateUrl('account', 'promotion_analytics', array($row['promotion_id']))),
                        '<i class="fa fa-list"></i>',
                        true
                    );
                    /* $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
                    'title'=>'Links',"onclick"=>"productLinksForm(".$row['product_id'].")"),
                    '<i class="ion-link icon"></i>', true);

                    $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
                    'title'=>'Options',"onclick"=>"productOptionsForm(".$row['product_id'].")"),
                    '<i class="ion-levels icon"></i>', true); */

                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
    }

    echo $tbl->getHtml();
    if (count($promotions) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
    }

    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmPromotionSearchPaging'));

    $pagingArr=array('pageCount'=>$pages,'page'=>$page,'callBackJsFunc' => 'goToPromotionSearchPage');
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
