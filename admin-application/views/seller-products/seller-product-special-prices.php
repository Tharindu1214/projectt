<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?> <section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Special_Price', $adminLangId); ?></h4>
        <a class="btn-default btn-sm" href="javascript::void(0)" onClick="sellerProductSpecialPriceForm(<?php echo $selprod_id;?>);"><?php echo Labels::getLabel('LBL_ADD_SPECIAL_PRICE', $adminLangId)?></a> 
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
            <?php /*<div class="tabs_nav_container responsive flat"> <?php require_once('sellerCatalogProductTop.php');?> </div>*/?>
                <div class="border-box border-box--space">
                    <div class="tabs_nav_container responsive">
                        <div class="tabs_panel_wrap">
                            <div class="tabs_panel"> <?php
                                $arr_flds = array(
                                    'listserial'=> Labels::getLabel('LBL_Sr.', $adminLangId),
                                    'splprice_price' => Labels::getLabel('LBL_Special_Price', $adminLangId),
                                    'splprice_start_date' => Labels::getLabel('LBL_Start_Date', $adminLangId),
                                    'splprice_end_date' => Labels::getLabel('LBL_End_Date', $adminLangId),
                                    'action'    =>    Labels::getLabel('LBL_Action', $adminLangId),
                                );
                                $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
                                $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => 'hide--mobile'));
                                foreach ($arr_flds as $val) {
                                    $e = $th->appendElement('th', array(), $val);
                                }

                                $sr_no = 0;
                                foreach ($arrListing as $sn => $row) {
                                    $sr_no++;
                                    $tr = $tbl->appendElement('tr', array());

                                    foreach ($arr_flds as $key => $val) {
                                        $td = $tr->appendElement('td');
                                        switch ($key) {
                                            case 'listserial':
                                                $td->appendElement('plaintext', array(), ''.$sr_no, true);
                                                break;
                                            case 'splprice_price':
                                                $td->appendElement('plaintext', array(), ''.CommonHelper::displayMoneyFormat($row[$key]), true);
                                                break;
                                            case 'splprice_start_date':
                                                $td->appendElement('plaintext', array(), ''.FatDate::format($row[$key]), true);
                                                break;
                                            case 'splprice_end_date':
                                                $td->appendElement('plaintext', array(), ''.FatDate::format($row[$key]), true);
                                                break;
                                            case 'action':
                                                $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"), '', true);
                                                $li = $ul->appendElement("li", array('class'=>'droplink'));
                                                $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                                                $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                                                $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));

                                                $innerLiEdit=$innerUl->appendElement('li');

                                                $innerLiEdit->appendElement(
                                                    'a',
                                                    array(
                                                        'href'=>'javascript:void(0)',
                                                        'class'=>'',
                                                        'title'=>Labels::getLabel('LBL_Edit', $adminLangId), "onclick"=>"sellerProductSpecialPriceForm(".$selprod_id.", ".$row['splprice_id'].")"
                                                    ),
                                                    Labels::getLabel('LBL_Edit', $adminLangId),
                                                    true
                                                );

                                                $innerLiDelete=$innerUl->appendElement('li');
                                                $innerLiDelete->appendElement(
                                                    'a',
                                                    array('href'=>'javascript:void(0)', 'class'=>'',
                                                    'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteSellerProductSpecialPrice(".$row['splprice_id'].")"),
                                                    Labels::getLabel('LBL_Delete', $adminLangId),
                                                    true
                                                );
                                                break;
                                            default:
                                                $td->appendElement('plaintext', array(), ''.$row[$key], true);
                                                break;
                                        }
                                    }
                                }
                                if (count($arrListing) == 0) {
                                    // $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Special_Price_added_to_this_product', $adminLangId));
                                    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId), false);
                                } else {
                                    echo $tbl->getHtml();
                                }
                                ?> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
