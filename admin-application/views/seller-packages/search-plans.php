<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="sectionhead">
	<h4><?php echo Labels::getLabel('LBL_Seller_Packages_Listings',$adminLangId);?></h4>
	<?php
		$ul = new HtmlElement("ul",array("class"=>"actions actions--centered"));
		$li = $ul->appendElement("li",array('class'=>'droplink'));

		$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Add_New',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
		$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
		$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));

		$innerLi=$innerUl->appendElement('li');
		$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Packages_List',$adminLangId),"onclick"=>"reloadList(0)"),Labels::getLabel('LBL_Packages_List',$adminLangId), true);

		if( $spackageData[sellerPackages::DB_TBL_PREFIX.'type'] != sellerPackages::FREE_TYPE || ($spackageData[sellerPackages::DB_TBL_PREFIX.'type'] == sellerPackages::FREE_TYPE && empty($arr_listing) ) ){

			$innerLi=$innerUl->appendElement('li');
			$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Add_New',$adminLangId),"onclick"=>"planForm(".$spackageId.")"),Labels::getLabel('LBL_Add_New',$adminLangId), true);
		}
		echo $ul->getHtml();
	?>
</div>
<div class="sectionbody">
	<div class="tablewrap" >
			<?php
				$arr_flds = array(
					'listserial'=>Labels::getLabel('LBL_Sr_no.',$adminLangId),
					SellerPackagePlans::DB_TBL_PREFIX.'price'=>Labels::getLabel('LBL_Plan_Price',$adminLangId),
					'action' => Labels::getLabel('LBL_Action',$adminLangId),
				);

				$tbl = new HtmlElement('table',
				array('width'=>'100%', 'class'=>'table table-responsive','id'=>'options'));

				$th = $tbl->appendElement('thead')->appendElement('tr');
				foreach ($arr_flds as $val) {
					$e = $th->appendElement('th', array(), $val);
				}

				$sr_no = 0;
				foreach ($arr_listing as $sn=>$row){
					$sr_no++;
					$tr = $tbl->appendElement('tr');
					$tr->setAttribute ("id",$row[SellerPackagePlans::DB_TBL_PREFIX.'id']);
					if($row['spplan_active'] != applicationConstants::ACTIVE) {
						$tr->setAttribute ("class","fat-inactive nodrag nodrop");
					}
					foreach ($arr_flds as $key=>$val){
						$td = $tr->appendElement('td');
						switch ($key){
							case 'listserial':
								$td->appendElement('plaintext', array(), $sr_no);
							break;
							case SellerPackagePlans::DB_TBL_PREFIX.'price':
								$td->appendElement('plaintext', array(), SellerPackagePlans::getPlanPriceWithPeriod($row,$row[SellerPackagePlans::DB_TBL_PREFIX.'price']), true );
							break;
							case SellerPackagePlans::DB_TBL_PREFIX.'trial_interval':
								$td->appendElement('plaintext', array(), SellerPackagePlans::getPlanTrialPeriod($row), true );
							break;

							break;

							case 'action':
								$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
								if($canEdit){
									$li = $ul->appendElement("li",array('class'=>'droplink'));
									$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
									$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
									$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));

									$innerLi=$innerUl->appendElement('li');
									$innerLi->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"planForm(".$row[SellerPackagePlans::DB_TBL_PREFIX.'spackage_id'].",".$row[SellerPackagePlans::DB_TBL_PREFIX.'id'].")"),Labels::getLabel('LBL_Edit',$adminLangId), true);
								}
							break;
							default:
								$td->appendElement('plaintext', array(), $row[$key], true);
							break;
						}
					}
				}
				if (count($arr_listing) == 0){
					$tbl->appendElement('tr')->appendElement('td', array(
					'colspan'=>count($arr_flds)),
					Labels::getLabel('LBL_No_Records_Found',$adminLangId)
					);
				}
				echo $tbl->getHtml();


				?>
	</div>
</div>
