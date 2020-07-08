<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="col-lg-12 col-md-12">
    <div class="content-header justify-content-between row mb-4">
        <div class="content-header-left col-md-auto">
            <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5>
        </div>
        <div class="content-header-right col-auto">
            <div class="form__group">
                <a href="javascript:void(0)" onClick="toggleBulkCollectionStatues(1)" class="btn btn--primary btn--sm formActionBtn-js formActions-css"><?php echo Labels::getLabel('LBL_Activate', $siteLangId);?></a>
                <a href="javascript:void(0)" onClick="toggleBulkCollectionStatues(0)" class="btn btn--primary-border btn--sm formActionBtn-js formActions-css"><?php echo Labels::getLabel('LBL_Deactivate', $siteLangId);?></a>
                <a href="javascript:void(0)" onClick="deleteSelectedCollection()" class="btn btn--primary  btn--smformActionBtn-js formActions-css"><?php echo Labels::getLabel('LBL_Delete', $siteLangId);?></a>
                <?php if (count($arr_listing) > 0) { ?>
                <a href="javascript:void(0)" onClick="getShopCollectionGeneralForm(0)" class="btn btn--primary-border  btn--sm"><?php echo Labels::getLabel('LBL_Add_Collection', $siteLangId);?></a>
            <?php }?>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-12 col-md-12">
    <?php
    $arr_flds = array(
            'listserial'=>Labels::getLabel('LBL_Sr._no.', $siteLangId),
            'scollection_identifier'=>Labels::getLabel('LBL_Collection_Name', $siteLangId),
            'scollection_active'=>Labels::getLabel('LBL_Status', $siteLangId),
            'action' => Labels::getLabel('LBL_Action', $siteLangId),
        );
        if (count($arr_listing) > 0) {
            $arr_flds = array_merge(
                array('select_all'=>Labels::getLabel('LBL_Select_all', $siteLangId)),
                $arr_flds
                );
        }

    $tbl = new HtmlElement(
        'table',
        array('width'=>'100%', 'class'=>'table table--orders','id'=>'options')
    );

    $th = $tbl->appendElement('thead')->appendElement('tr');
    foreach ($arr_flds as $key => $val) {
        if ('select_all' == $key) {
            $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i>'.$val.'</label>', true);
        } else {
            $th->appendElement('th', array(), $val);
        }
    }
    $sr_no = 0;
    foreach ($arr_listing as $sn => $row) {
        $sr_no ++;
        $tr = $tbl->appendElement('tr');
        $tr->setAttribute("id", $row['scollection_id']);

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="scollection_ids[]" value='.$row['scollection_id'].'><i class="input-helper"></i></label>', true);
                    break;
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no);
                    break;
                case 'scollection_identifier':
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;

                case 'scollection_active':
                    /* $td->appendElement( 'plaintext', array(), $activeInactiveArr[$row[$key]],true ); */
                    $active = "";
                    if (applicationConstants::ACTIVE == $row['scollection_active']) {
                        $active = 'checked';
                    }

                    $str = '<label class="toggle-switch" for="switch'.$row['scollection_id'].'"><input '.$active.' type="checkbox" value="'.$row['scollection_id'].'" id="switch'.$row['scollection_id'].'" onclick="toggleShopCollectionStatus(event,this)"/><div class="slider round"></div></label>';

                    $td->appendElement('plaintext', array(), $str, true);
                    break;

                case 'action':
                    $ul = $td->appendElement("ul", array("class"=>"actions"));
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                            'a',
                            array(
                            'href'=>'javascript:void(0)',
                            'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId),
                            "onclick"=>"getShopCollectionGeneralForm(".$row['scollection_id'].")"),
                            '<i class="fa fa-edit"></i>',
                            true
                        );

                        $li = $ul->appendElement("li");
                        $li->appendElement(
                            'a',
                            array(
                            'href'=>"javascript:void(0)", 'class'=>'button small green',
                            'title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"deleteShopCollection(".$row['scollection_id'].")"),
                            '<i class="fa fa-trash"></i>',
                            true
                        );

                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
    }

    $frm = new Form('frmCollectionsListing', array('id'=>'frmCollectionsListing'));
    $frm->setFormTagAttribute('class', 'form');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, searchShopCollections ); return(false);');
    $frm->setFormTagAttribute('action', CommonHelper::generateUrl('Seller', 'toggleBulkCollectionStatuses'));
    $frm->addHiddenField('', 'collection_status', '');

    echo $frm->getFormTag();
    echo $frm->getFieldHtml('collection_status');
    echo $tbl->getHtml();
    if (count($arr_listing) == 0) {
        $message = Labels::getLabel('LBL_No_Collection_found', $siteLangId);
        $linkArr = array(
            0=>array(
            'href'=>'javascript:void(0);',
            'label'=>Labels::getLabel('LBL_Add_Collection', $siteLangId),
            'onClick'=>"getShopCollectionGeneralForm(0)",
            )
        );
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'linkArr'=>$linkArr,'message'=>$message));
    } ?>
    </form>
</div>
