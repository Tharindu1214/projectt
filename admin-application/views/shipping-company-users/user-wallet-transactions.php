<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="col-sm-12">
	<h1><?php echo  Labels::getLabel('LBL_Child_Order_Transactions',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<?php 
		$arr_flds = array(
			'utxn_id'=> Labels::getLabel('LBL_Transaction_Id',$adminLangId),
			'utxn_date'=>Labels::getLabel('LBL_Date',$adminLangId),
			'utxn_credit'=>Labels::getLabel('LBL_Credits',$adminLangId),						
			'utxn_debit' => Labels::getLabel('LBL_Debits',$adminLangId),
			'utxn_comments' => Labels::getLabel('LBL_Description',$adminLangId),
			'utxn_status' => Labels::getLabel('LBL_Status',$adminLangId),
			);
		$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
		$th = $tbl->appendElement('thead')->appendElement('tr');
		foreach ($arr_flds as $key=>$val) {					
			$e = $th->appendElement('th', array(), $val,true);
		}
		$sr_no = 0;
		foreach ($arr_listing as $sn=>$row){ 
			$sr_no++;
			$tr = $tbl->appendElement('tr');
			
			foreach ($arr_flds as $key=>$val){
				$td = $tr->appendElement('td');
				switch ($key){
					case 'utxn_id':
						$td->appendElement('plaintext', array(), Transactions::formatTransactionNumber($row[$key]) );
					break;
					case 'utxn_date':
						$td->appendElement('plaintext', array(),FatDate::format($row[$key]));
					break;
					case 'utxn_credit':
					case 'utxn_debit':
						$td->appendElement('plaintext', array(),CommonHelper::displayMoneyFormat($row[$key]));
					break;														
					case 'utxn_comments':								
						$td->appendElement('plaintext', array(), Transactions::formatTransactionComments($row[$key]),true);
					break;
					case 'utxn_status':								
						$td->appendElement('plaintext', array(), $statusArr[$row[$key]],true);
					break;							
					default:
						$td->appendElement('plaintext', array(), $row[$key], true);
					break;
				}
			}
		}
		if (count($arr_listing) == 0){
			$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Record_Found',$adminLangId));
		}
		echo $tbl->getHtml();
		$postedData['page'] = $page;
		echo FatUtility::createHiddenFormFromData ( $postedData, array (
				'name' => 'frmTransactionSearchPaging'
		) );
		$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'callBackJsFunc'=>'goToTransactionPage');
		$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
		?>
	</div>
</div>