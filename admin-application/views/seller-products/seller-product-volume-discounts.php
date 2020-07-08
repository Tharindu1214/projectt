<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
    <div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId); ?></h4>
        <a class=" btn-default btn-sm" href="javascript:void(0); " onClick="sellerProductVolumeDiscountForm(<?php echo $selprod_id; ?>, 0);"><?php echo Labels::getLabel( 'LBL_Add_New_Volume_Discount', $adminLangId)?></a>
        <?php /* <a class="btn-default btn-sm" target='_blank' href="<?php echo CommonHelper::generateUrl('SellerProducts', 'volumeDiscount', array($selprod_id)); ?>" style='float:right;'><?php echo Labels::getLabel('LBL_Manage_Volume_Discount', $adminLangId)?></a> */ ?>        
    </div>
    <div class="sectionbody space">
            <div class="row">
                <div class="col-sm-12">                
                    <div class="border-box border-box--space">
                    <div class="tabs_nav responsive">
                        <div class="tabs_panel_wrap">
                            <div class="tabs_panel">
                                <?php
                                $arr_flds = array(
                                    'listserial'=> Labels::getLabel('LBL_Sr.', $adminLangId),
                                    'voldiscount_min_qty' => Labels::getLabel('LBL_Minimum_Quantity', $adminLangId),
                                    'voldiscount_percentage' => Labels::getLabel('LBL_Discount', $adminLangId).' (%)',
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

                                    foreach ($arr_flds as $key=>$val) {
                                        $td = $tr->appendElement('td');
                                        switch ($key) {
                                            case 'listserial':
                                            $td->appendElement('plaintext', array(), ''.$sr_no, true);
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
                                                    array('href'=>'javascript:void(0)', 'class'=>'',
                                                    'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"sellerProductVolumeDiscountForm(".$selprod_id.", ".$row['voldiscount_id'].")"),
                                                    Labels::getLabel('LBL_Edit', $adminLangId),
                                                    true
                                                );

                                                $innerDelete = $innerUl->appendElement("li");
                                                $innerDelete->appendElement(
                                                    'a',
                                                    array('href'=>'javascript:void(0)', 'class'=>'',
                                                    'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteSellerProductVolumeDiscount(".$row['voldiscount_id'].")"),
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
                                    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId), false);
                                } else {
                                    echo $tbl->getHtml();
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
