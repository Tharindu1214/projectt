<div class="bg--second pt-3 pb-3">
       <div class="container">
           <div class="row">
               <div class="col-md-8 col-sm-7">
                   <div class="cell">
                       <div class="cell__left">
                           <div class="avtar avtar--rounded"><img alt="<?php echo $userInfo['user_name'];?>" src="<?php echo CommonHelper::generateUrl('Image','user',array($userInfo['user_id'],'thumb','1'));?>"></div>
                       </div>
                       <div class="cell__right">
                           <div class="avtar__info">
                               <h5><?php echo $userInfo['user_name'];?></h5>
                               <p><?php echo $userInfo['user_city'];?></p>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="col-md-4 col-sm-5 align--right" style="padding-top:20px;">
                  <ul class="links--inline">
                        <li class="is--active"><strong id="favShopCount">XX</strong> <?php echo Labels::getLabel('LBL_Favorite_Shops',$siteLangId);?></li>
                   </ul>
               </div>
           </div>
       </div>
   </div>
   <div class="container container--fixed">

            <div class="panel panel--centered clearfix">
					<div class="section section--info clearfix">
                        <div class="section__body">
							<div class="box box--white" id="listing">
                            </div>
							<div id="loadMoreBtnDiv"></div>
                        </div>
					</div>

			</div>

    </div>
<?php echo $searchForm->getFormHtml();?>
