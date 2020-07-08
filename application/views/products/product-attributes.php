<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class="section__head">
    <h4><?php echo Labels::getLabel('LBl_Specifications', $siteLangId); ?> </h4>
</div>
<div class="section__body">
    <div class="box box--white">
        <div class="table table--twocols">
            <table>
                <tr>
                    <th><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?></th>
                    <td>
                        <a href="<?php echo CommonHelper::generateUrl('brands', 'view', array($brandRow['brand_id'])) ;?>">
                            <?php echo $brandRow['brand_name']; ?>
                        </a>
                    </td>
                </tr>
                <?php foreach ($attributes as $attr) { ?>
                <tr>
                    <th><?php echo $attr['attr_name']; ?></th>
                    <td> <?php
                    $attrValue = $attributesValues[$attr['attr_fld_name']];
                    switch ($attr['attr_type']) {
                        case AttrGroupAttribute::ATTRTYPE_NUMBER:
                        case AttrGroupAttribute::ATTRTYPE_DECIMAL:
                            $attrValue = $attrValue*1;
                            break;
                    }
                    $attrValue = ($attr['attr_prefix'] != '') ? $attr['attr_prefix']. ' '. $attrValue : $attrValue;
                    $attrValue = ($attr['attr_postfix'] != '') ? $attrValue.' '.$attr['attr_postfix'] : $attrValue;
                    echo $attrValue; ?> </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
