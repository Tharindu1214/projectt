<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="box__head">
   <h4><?php echo Labels::getLabel('LBL_Product_Listing',$siteLangId); ?></h4>
</div>
<div class="box__body">
<div class="tabs tabs--small   tabs--scroll clearfix">
		<?php require_once('sellerCatalogProductTop.php');?>
	</div>
	<div class="tabs__content form">

		<div class="row">
			<div class="col-md-12">
				<div class="form__subcontent">
					<?php
				$arr_flds = array(
					'listserial'=> Labels::getLabel( 'LBL_Sr.', $siteLangId ),
					'taxcat_name' => Labels::getLabel( 'LBL_Tax_Category', $siteLangId ),
					'taxval_value' => Labels::getLabel( 'LBL_Value', $siteLangId ),
					'action'	=>	Labels::getLabel('LBL_Action', $siteLangId),
				);
				$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
				$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
				foreach ($arr_flds as $val) {
					$e = $th->appendElement('th', array(), $val);
				}

				$sr_no = 0;
				
                foreach ($arrListing as $sn => $row){
                    $sr_no++;
                    $tr = $tbl->appendElement('tr',array());

                    if(is_array($row) && count($row)){
                        foreach ($arr_flds as $key=>$val){
                            $td = $tr->appendElement('td');
                            switch ($key){
                                case 'listserial':
                                    $td->appendElement('plaintext', array(), $sr_no,true);
                                break;
                                case 'taxval_value';
                                    $str = CommonHelper::displayTaxFormat($row['taxval_is_percent'],$row['taxval_value']);
                                    $td->appendElement( 'plaintext', array(), $str,true );
                                break;
                                case 'action':
                                        $ul = $td->appendElement("ul",array("class"=>"actions"),'',true);
                                        $li = $ul->appendElement("li");
                                        $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
                                        'title'=>Labels::getLabel('LBL_Edit',$siteLangId),"onclick"=>"changeTaxCategory(".$selprod_id.")"),
                                        '<i class="fa fa-edit"></i>', true);
                                    if($row['ptt_seller_user_id'] == $userId){
                                        $li = $ul->appendElement("li");
                                        $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
                                        'title'=>Labels::getLabel('LBL_Reset_to_Default',$siteLangId),"onclick"=>"resetTaxRates(".$selprod_id.")"),
                                        '<i class="fa fa-undo"></i>', true);
                                    }
                                break;
                                default:
                                    $td->appendElement('plaintext', array(), $row[$key],true);
                                break;
                            }
                        }
                    }
                }
                echo $tbl->getHtml();
                if (count($arrListing) == 0) {
                    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
                    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
                }
				?>
				</div>
			</div>
		</div>

	</div>
</div>
