<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<!-- <div class="tabs tabs--small tabs--scroll clearfix">
    <ul>
        <li class="is-active"><a href="javascript:void(0);" onClick="searchAddresses()"><?php echo Labels::getLabel('LBL_My_Addresses', $siteLangId);?></a></li>
        <li><a href="javascript:void(0);" onClick="addAddressForm(0)"><?php echo Labels::getLabel('LBL_Add_new_address', $siteLangId);?></a></li>
    </ul>
</div> -->

<div class="container--addresses">
    <div class="row">
<?php if (!empty($addresses)) {
    if (count($addresses) == 1 && $addresses[0]['ua_is_default'] != 1) {
        $addresses[0]['ua_is_default'] = 1;
    }
    foreach ($addresses as $address) {
        $address['ua_identifier'] = ($address['ua_identifier'] == '') ? '&nbsp;' : $address['ua_identifier']; ?> <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
            <label class="list__selection <?php echo ($address['ua_is_default']==1)?'is-active':''; ?>">
                <span class="radio">
                    <?php
                    $action = "setDefaultAddress(".$address['ua_id'].", event)";
                    if (1 == $address['ua_is_default']) {
                        $action = 'return false';
                    }
                    ?>
                    <input type="radio" <?php echo ($address['ua_is_default']==1)?'checked=""':''; ?> name="1" onClick="<?php echo $action; ?>"><i class="input-helper"></i>
                </span>
                <address>
                    <h6><?php echo $address['ua_identifier']; ?></h6>
                    <p><?php echo $address['ua_name']; ?><br> <?php echo $address['ua_address1']; ?><br> <?php echo (strlen($address['ua_address2'])>0)?$address['ua_address2'].'<br>':''; ?>
                        <?php echo (strlen($address['ua_city'])>0)?$address['ua_city'].',':''; ?> <?php echo (strlen($address['state_name'])>0)?$address['state_name'].'<br>':''; ?>
                        <?php echo (strlen($address['country_name'])>0)?$address['country_name'].'<br>':''; ?>
                         <?php /* echo (strlen($address['ua_zip'])>0) ? Labels::getLabel('LBL_Zip:', $siteLangId).$address['ua_zip'].'<br>':''; */  ?>
                        <?php echo (strlen($address['ua_phone'])>0) ? Labels::getLabel('LBL_Phone:', $siteLangId).$address['ua_phone'].'<br>':''; ?> </p>
                </address>
                <a href="javascript:void(0)" onClick="addAddressForm(<?php echo $address['ua_id']; ?>)" class="btn btn--sm btn--primary"><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></a>
                <a href="javascript:void(0)" onClick="removeAddress(<?php echo $address['ua_id']; ?>)" class="btn btn--sm btn--primary-border"><?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?></a>
            </label>
        </div> <?php
    }
} elseif (isset($noRecordsHtml)) {
    echo FatUtility::decodeHtmlEntities($noRecordsHtml);
} ?> </div>
</div>
