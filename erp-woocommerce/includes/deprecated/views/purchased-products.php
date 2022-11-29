<?php
if ( $customer->email ) {

    $args = [
        'meta_key' => '_billing_email',
        'customer' => $customer->email
    ];

} else if ( $customer->phone ) {

    $args = [
        'meta_key' => '_billing_phone',
        'customer' => $customer->phone
    ];

} else if ( $customer->user_id ) {

    $args = [
        'meta_key' => '_customer_user',
        'customer' => $customer->user_id
    ];

} else {
    return;
}

$products = erp_wc_get_customer_purchased_products( $args );
?>
<div class="postbox customer-latest-purchased-product">
    <div class="erp-handlediv" title="Click to toggle"><br></div>
    <h3 class="erp-hndle"><span><?php _e( 'Purchased Products', 'erp-woocommerce' ) ?></span></h3>
    <div class="inside customer-latest-purchased-product-content">
        <?php if ( ! empty( $products ) ): ?>
            <ul>
                <?php
                foreach ( $products as $product_id => $value ) {
                    $product       = wc_get_product( $product_id );
                    $product_title = $product->get_title();
                    $product_id    = ( $value['is_variation'] ) ? $product->parent->id : $product->get_id();
                    $item_meta     = new WC_Order_Item_Meta( $value['item'], $product );
                    $variations    = explode( ',', $item_meta->display( true, true ) );
                ?>

                <li>
                    <div class="erp-left">
                        <?php echo sprintf( '<a href="%s">%s</a> &times; %d', get_edit_post_link( $product_id ), $product_title, $value['qty'] ) ?>
                        <?php
                        echo '<div class="item-variation">' . implode( '</div><div class="item-variation">', $variations ) . '</div>';
                        ?>
                    </div>
                    <div class="erp-right"><?php echo wc_price( $value['qty']*$value['line_total'] ); ?></div>
                    <div class="clearfix"></div>
                </li>

                <?php } ?>
            </ul>
        <?php else: ?>
            <p class="not-found"><?php _e( 'No products found', 'erp-woocommerce' ); ?></p>
        <?php endif ?>
    </div>
 </div><!-- .postbox -->