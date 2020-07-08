<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<tr id="row-<?php echo $volDiscountId; ?>">
    <td>
        <label class="checkbox">
            <input class="selectItem--js" type="checkbox" name="selprod_ids[<?php echo $volDiscountId; ?>]" value="<?php echo $post['voldiscount_selprod_id']; ?>"><i class="input-helper"></i></label>
    </td>
    <td>
        <?php echo html_entity_decode($post['product_name']); ?>
    </td>
    <td>
        <div class="js--editCol edit-hover"><?php echo $post['voldiscount_min_qty']; ?></div>
        <input type="text" data-id="<?php echo $volDiscountId; ?>" value="<?php echo $post['voldiscount_min_qty']; ?>" data-selprodid="<?php echo $post['voldiscount_selprod_id']; ?>" name="voldiscount_min_qty" class="js--volDiscountCol hidden vd-input" data-oldval="<?php echo $post['voldiscount_min_qty']; ?>"/>
    </td>
    <td>
        <div class="js--editCol edit-hover"><?php echo number_format((float)$post['voldiscount_percentage'], 2, '.', ''); ?></div>
        <input type="text" data-id="<?php echo $volDiscountId; ?>" value="<?php echo $post['voldiscount_percentage']; ?>" data-selprodid="<?php echo $post['voldiscount_selprod_id']; ?>" name="voldiscount_percentage" class="js--volDiscountCol hidden vd-input" data-oldval="<?php echo $post['voldiscount_percentage']; ?>"/>
    </td>
    <td>
        <ul class="actions">
            <li><a title="Delete" href="javascript:void(0);" onclick="deleteSellerProductVolumeDiscount(<?php echo $volDiscountId; ?>)"><i class="fa fa-trash"></i></a></li>
        </ul>
    </td>
</tr>
