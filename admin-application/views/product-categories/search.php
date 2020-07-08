<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<?php
    $arr_flds = array(
            'dragdrop'=>'',
            'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
            'listserial'=>Labels::getLabel('LBL_Sr._No', $adminLangId),
            'prodcat_identifier'=>Labels::getLabel('LBL_Category_Name', $adminLangId),
            'child_count' => Labels::getLabel('LBL_Subcategories', $adminLangId),
            'prodcat_active' => Labels::getLabel('LBL_Status', $adminLangId),
            'action' => Labels::getLabel('LBL_Action', $adminLangId),
        );
    if (!$canEdit) {
        unset($arr_flds['dragdrop']);
    }
    $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered','id'=>'prodcat'));
    $th = $tbl->appendElement('thead')->appendElement('tr');
    foreach ($arr_flds as $key => $val) {
        if ('select_all' == $key) {
            $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
        } else {
            $e = $th->appendElement('th', array(), $val);
        }
    }

    $sr_no = $page==1?0:$pageSize*($page-1);
    foreach ($arr_listing as $sn => $row) {
        $sr_no++;
        $tr = $tbl->appendElement('tr');
        if ($row['prodcat_active'] == applicationConstants::ACTIVE) {
            $tr->setAttribute("id", $row['prodcat_id']);
        }

        if ($row['prodcat_active'] != applicationConstants::ACTIVE) {
            $tr->setAttribute("class", "nodrag nodrop");
        }
        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="prodcat_ids[]" value='.$row['prodcat_id'].'><i class="input-helper"></i></label>', true);
                    break;
                case 'dragdrop':
                    if ($row['prodcat_active'] == applicationConstants::ACTIVE) {
                        $td->appendElement('i', array('class'=>'ion-arrow-move icon'));
                        $td->setAttribute("class", 'dragHandle');
                    }
                    break;
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no);
                    break;
                case 'prodcat_identifier':
                    if ($row['prodcat_name']!='') {
                        $td->appendElement('plaintext', array(), $row['prodcat_name'], true);
                        $td->appendElement('br', array());
                        $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                    } else {
                        $td->appendElement('plaintext', array(), $row[$key], true);
                    }
                    break;
                case 'prodcat_active':
                    $active = "";
                    if ($row['prodcat_active']) {
                        $active = 'checked';
                    }
                    $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' .applicationConstants::YES. ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                    $statusClass = ($canEdit === false) ? 'disabled' : '';
                    $str='<label class="statustab -txt-uppercase">
                     <input '.$active.' type="checkbox" id="switch'.$row['prodcat_id'].'" value="'.$row['prodcat_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                    <i class="switch-handles '.$statusClass.'"></i></label>';
                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'child_count':
                    if ($row[$key]==0) {
                        $td->appendElement('plaintext', array(), $row[$key], true);
                    } else {
                        $td->appendElement('a', array('href'=>CommonHelper::generateUrl('ProductCategories', 'index', array($row['prodcat_id'])),'title'=>Labels::getLabel('LBL_View_Categories', $adminLangId)), $row[$key]);
                        //$td->appendElement('a', array('href'=>'javascript:void(0)',"onclick"=>"subcat_list(".$row['prodcat_id'].")",'title'=>Labels::getLabel('LBL_View_Categories',$adminLangId)),$row[$key] );
                    }
                    break;

                case 'action':
                    $ul = $td->appendElement("ul", array("class"=>"actions actions--centered"));
                    if ($canEdit) {
                        $li = $ul->appendElement("li", array('class'=>'droplink'));


                        $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                        $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                        $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                        $innerLiEdit=$innerUl->appendElement('li');

                        $innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"addCategoryForm(".$row['prodcat_id'].")"), Labels::getLabel('LBL_Edit', $adminLangId), true);

                        if ($row['child_count'] > 0) {
                            /* $li = $ul->appendElement("li");
    						$li->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Content_Block',$adminLangId),"onclick"=>"contentBlock(".$row['prodcat_id'].")"),'<i class="ion-grid icon"></i>', true); */
                        }

                        $innerLiDelete = $innerUl->appendElement("li");
                        $innerLiDelete->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteRecord(".$row['prodcat_id'].")"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                    }
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
    }
    if (count($arr_listing) == 0) {
        $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
    }

    $frm = new Form('frmProdCatListing', array('id'=>'frmProdCatListing'));
    $frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
    $frm->setFormTagAttribute('action', CommonHelper::generateUrl('ProductCategories', 'toggleBulkStatuses'));
    $frm->addHiddenField('', 'status');

    echo $frm->getFormTag();
    echo $frm->getFieldHtml('status');
	?>
	    <?php echo $tbl->getHtml(); ?>
	</form>
	<?php $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array(
            'name' => 'frmCatSearchPaging'
    ));
    $pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
    ?>
<script>
    $(document).ready(function(){

    	var pcat_id=$('#prodcat_parent').val();
    	$('#prodcat').tableDnD({
    		onDrop: function (table, row) {
    			fcom.displayProcessing();
    			var order = $.tableDnD.serialize('id');
    			order += '&pcat_id=' + pcat_id;
    			fcom.ajax(fcom.makeUrl('productCategories', 'updateOrder'), order, function (res) {
    				var ans =$.parseJSON(res);
    				if(ans.status==1)
    				{
    					fcom.displaySuccessMessage(ans.msg);
    				}else{
    					fcom.displayErrorMessage(ans.msg);
    				}
    			});
    		},
    		dragHandle: ".dragHandle",
    	});
    });
</script>
