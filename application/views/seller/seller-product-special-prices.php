<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="tabs tabs--small   tabs--scroll clearfix">
    <?php require_once('sellerCatalogProductTop.php');?>
</div>
<div class="cards">
    <?php if (count($arrListing) > 0) { ?>
        <div class="cards-header p-4">
            <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Special_price', $siteLangId); ?>
            </h5>
            <div class="action">
                <a class="btn btn--primary btn--sm" href="javascript:void(0);" onClick='sellerProductSpecialPriceForm(<?php echo $selprod_id; ?>, 0);'>
                    <?php echo Labels::getLabel('LBL_Add_New_Special_Price', $siteLangId); ?>
                </a>
                <!-- <a class="btn btn--primary-border btn--sm" href="<?php echo CommonHelper::generateUrl('Seller', 'specialPrice', array($selprod_id)); ?>">
                    <?php echo Labels::getLabel('LBL_MANAGE_SPECIAL_PRICES', $siteLangId)?>
                </a> -->
            </div>
        </div>
    <?php } ?>
    <div class="cards-content pl-4 pr-4 ">
        <div class="row">
            <div
                class="<?php echo (count($arrListing) > 0) ? 'col-md-8' : 'col-md-12' ;?>">
                <div class="form__subcontent">
                    <?php
                    $arr_flds = array(
                    'listserial'=> Labels::getLabel('LBL_Sr.', $siteLangId),
                    'splprice_price' => Labels::getLabel('LBL_Special_Price', $siteLangId),
                    'splprice_start_date' => Labels::getLabel('LBL_Start_Date', $siteLangId),
                    'splprice_end_date' => Labels::getLabel('LBL_End_Date', $siteLangId),
                    'action' => Labels::getLabel('LBL_Action', $siteLangId),
                    );
                    $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
                    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
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
                                    $td->appendElement('plaintext', array(), $sr_no, true);
                                    break;
                                case 'splprice_price':
                                    $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                                    break;
                                case 'splprice_start_date':
                                    $td->appendElement('plaintext', array(), FatDate::format($row[$key]), true);
                                    break;
                                case 'splprice_end_date':
                                    $td->appendElement('plaintext', array(), FatDate::format($row[$key]), true);
                                    break;
                                case 'action':
                                    $ul = $td->appendElement("ul", array("class"=>"actions"), '', true);
                                    $li = $ul->appendElement("li");
                                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId), "onclick"=>"sellerProductSpecialPriceForm(".$selprod_id.", ".$row['splprice_id'].")"), '<i class="fa fa-edit"></i>', true);
                                    $li = $ul->appendElement("li");
                                    $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'', 'title'=>Labels::getLabel('LBL_Delete', $siteLangId), "onclick"=>"deleteSellerProductSpecialPrice(".$row['splprice_id'].")"), '<i class="fa fa-trash"></i>', true);
                                    break;
                                default:
                                    $td->appendElement('plaintext', array(), $row[$key], true);
                                    break;
                            }
                        }
                    }

                    if (count($arrListing) == 0) {
                        $message = Labels::getLabel('LBL_No_any_special_prices_on_this_product', $siteLangId);
                        $linkArr = [
                                [
                                'href'=>'javascript:void(0);',
                                'label'=>Labels::getLabel('LBL_Add_New_Special_Price', $siteLangId),
                                'onClick'=>'sellerProductSpecialPriceForm('.$selprod_id.', 0);',
                                ]
                            ];
                        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'linkArr'=>$linkArr,'message'=>$message));
                    } else {
                        echo $tbl->getHtml();
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
