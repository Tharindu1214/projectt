<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>

<section class="section">
	<h1><?php echo Labels::getLabel('LBL_User_Reward_Points',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0)" onclick="addReward(<?php echo $userId ?>);"><?php echo Labels::getLabel('LBL_Reward_Points',$adminLangId); ?></a></li>
			<li><a href="javascript:void(0)" onclick="addUserRewardPoints(<?php echo $userId ?>);"><?php echo Labels::getLabel('LBL_Add_New',$adminLangId); ?></a></li>				
		</ul>
		<div class="tabs_panel_wrap">			
			<div class="tabs_panel">
				<?php 
				$arr_flds = array(
						'urp_date_added'=>Labels::getLabel('LBL_Valid_from',$adminLangId),
						'urp_date_expiry'=>Labels::getLabel('LBL_Valid_till',$adminLangId),						
						'urp_points' => Labels::getLabel('LBL_Points',$adminLangId),
						'urp_comments' => Labels::getLabel('LBL_Comments',$adminLangId),
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
							case 'urp_date_added':
							case 'urp_date_expiry':
								$td->appendElement('plaintext', array(),FatDate::format($row[$key]));
							break;													
							case 'urp_comments':								
								$td->appendElement('plaintext', array(), nl2br($row[$key]), true);
							break;						
							default:
								$td->appendElement('plaintext', array(), $row[$key], true);
							break;
						}
					}
				}
				if (count($arr_listing) == 0){
					$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
				}
				echo $tbl->getHtml();
				$postedData['page'] = $page;
				echo FatUtility::createHiddenFormFromData ( $postedData, array (
						'name' => 'frmRewardSearchPaging'
				) );
				$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'callBackJsFunc'=>'goToRewardPage','adminLangId'=>$adminLangId);
				$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
				?>
			</div>
		</div>
	</div>
</section>