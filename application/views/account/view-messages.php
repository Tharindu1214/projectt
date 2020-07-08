<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Messages', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <div class="cards-header p-4">
                    <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Messages', $siteLangId);?></h5>
                    <div class="btn-group"><a href="<?php echo CommonHelper::generateUrl('Account', 'messages');?>" class="btn btn--secondary btn--sm"><?php echo Labels::getLabel('LBL_Back_to_messages', $siteLangId);?></a></div>
                </div>
                <div class="cards-content pl-4 pr-4 ">
                    <table class="table table--orders">
                        <tbody>
                            <tr class="">
                                <th><?php echo Labels::getLabel('LBL_Date', $siteLangId);?></th>
                                <th><?php echo $threadTypeArr[$threadDetails['thread_type']];?></th>
                                <th><?php echo Labels::getLabel('LBL_Subject', $siteLangId);?></th>
                                <th><?php if ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_ORDER_PRODUCT) {
                                        echo Labels::getLabel('LBL_Amount', $siteLangId);
                                    } elseif ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_PRODUCT) {
                                        echo Labels::getLabel('LBL_Price', $siteLangId);
                                    }?></th>
                                <th>
                                    <?php if ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_ORDER_PRODUCT) {
                                        echo Labels::getLabel('LBL_Status', $siteLangId) ;
                                    } ?>
                                </th>
                            </tr>
                            <tr>
                                <td><?php echo FatDate::format($threadDetails["thread_start_date"], false);?> </td>
                                <td>
                                    <div class="item__description">
                                        <?php if ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_ORDER_PRODUCT) { ?>
                                            <span class="item__title"><?php echo $threadDetails["op_invoice_number"]; ?></span>
                                        <?php } elseif ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_SHOP) { ?>
                                            <span class="item__title"><?php echo $threadDetails["shop_name"]; ?></span>
                                        <?php } elseif ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_PRODUCT) { ?>
                                            <span class="item__title"><?php echo $threadDetails["selprod_title"]; ?></span>
                                        <?php }?>
                                    </div>
                                </td>
                                <td><?php echo $threadDetails["thread_subject"];?> </td>
                                <td>
                                    <span class="item__price">
                                        <?php if ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_ORDER_PRODUCT) {
                                            ?> <?php
                                        } elseif ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_SHOP) {
                                            ?> <?php
                                        } elseif ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_PRODUCT) { ?>
                                            <p><?php echo CommonHelper::displayMoneyFormat($threadDetails['selprod_price']); ?></p>
                                        <?php } ?>
                                    </span>
                                </td>
                                <td><?php if ($threadDetails["thread_type"] == THREAD::THREAD_TYPE_ORDER_PRODUCT) {
                                        echo $threadDetails["orders_status_name"];
                                    } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
            </div>
            <div class="gap"></div><div class="gap"></div>
            
            <div class="cards">
                <div class="cards-content">
                   
                    
                    <?php echo $frmSrch->getFormHtml();?> <div id="loadMoreBtnDiv"></div>
                    <div id="messageListing" class="messages-list">
                        <ul>
                        
                        </ul>
                    </div>
                    <div class="messages-list">
                        <ul>
                            <li>
                                <div class="msg_db">
                                    <img src="<?php echo CommonHelper::generateUrl('Image', 'user', array($loggedUserId,'thumb',true));?>" alt="<?php echo $loggedUserName; ?>">
                                </div>
                                <div class="msg__desc">
                                    <span class="msg__title"><?php echo $loggedUserName;?></span> <?php
                                   $frm->setFormTagAttribute('onSubmit', 'sendMessage(this); return false;');
                                   $frm->setFormTagAttribute('class', 'form');
                                   $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                                   $frm->developerTags['fld_default_col'] = 12;
                                   echo $frm->getFormHtml(); ?> </div>
                            </li>
                        </ul>
                    </div>
                    
                </div>
                
            </div>
            
            
        </div>
    </div>
</main>
