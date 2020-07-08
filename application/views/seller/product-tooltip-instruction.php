<div class="delivery-term">
    <div id="catalogToolTip">
        <?php switch ($type) {
            case Extrapage::MARKETPLACE_PRODUCT_INSTRUCTIONS: ?>
            <div class="delivery-term-data-inner">
                <div class="heading">Products<span>All the information you need regarding this page</span></div>
                <ul class="">
                    <li>
                        This page lists all the marketplace products added by admin and seller.
                        Marketplace products are of two types:-
                        <ul>
                            <li><strong>System Products</strong>: Available to all sellers and any seller can add to their own store.</li>
                            <li><strong>My Products</strong>: Available only for you. No other seller can add to their own store.</li>
                        </ul>
                    </li>
                    <li>On clicking "<strong>Add Product</strong>" button, seller can add new product to marketplace products.</li>
                    <li>On click of "<strong>Add to Store</strong>" the seller can pick the product and add the products to his store inventory.</li>
                </ul>
            </div>
                <?php break;
            case Extrapage::SELLER_INVENTORY_INSTRUCTIONS: ?>
                <div class="delivery-term-data-inner">
                    <div class="heading">Store Inventory<span>All the information you need regarding this page</span></div>
                    <ul>
                        <li>This tab lists all the products available to your front end store.</li>
                        <li>For each product variant, separate copy need to be created by seller either from Marketplace product tab or clone product icon.</li>
                        <li>To add new product to your store inventory, seller will have to pick the products from the marketplace products tabs from "Add to Store" button</li>
                    </ul>
                </div>
                <?php break;
            case Extrapage::PRODUCT_REQUEST_INSTRUCTIONS: ?>
                <div class="delivery-term-data-inner">
                    <div class="heading">Requested Products<span>All the information you need regarding this page</span></div>
                    <ul>
                        <li>This tab lists all the products requested by seller to the admin which are not available in the marketplace products.</li>
                        <li>On admin approval, the product will be added to the marketplace products and to the seller inventory.</li>
                    </ul>
                </div>
                <?php break;
        } ?>
    </div>
</div>
