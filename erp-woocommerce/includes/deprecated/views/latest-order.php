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


$orders = erp_wc_get_customer_orders( $args );
?>
<div class="postbox customer-latest-order-info">
     <div class="erp-handlediv" title="Click to toggle"><br></div>
     <h3 class="erp-hndle"><span><?php _e( 'Latest order', 'erp-woocommerce' ) ?></span></h3>
     <div class="inside customer-latest-order-content">
        <?php if ( ! empty( $orders ) ): ?>
        <ul>
            <?php
            foreach ( $orders as $key => $order_id ) {
                $order = new WC_Order( $order_id );
            ?>
            <li>
                <div class="orderid-ammount">
                    <span class="erp-left"><a href="<?php echo get_edit_post_link( $order->id ); ?>"><?php echo sprintf( __( '#%d', 'erp-woocommerce' ), $order->get_order_number() ) ?></a></span>
                    <span class="erp-right"><?php echo $order->get_formatted_order_total(); ?></span>
                    <div class="clearfix"></div>
                </div>
                <div class="date-status">
                    <span class="erp-left order-date"><?php echo erp_format_date( $order->order_date ) ?></span>
                    <span class="erp-right order-status <?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name( $order->get_status() ) ?></span>
                    <div class="clearfix"></div>
                </div>
            </li>
            <?php } ?>
        </ul>
        <?php else: ?>
            <p class="not-found"><?php _e( 'No order found', 'erp-woocommerce' ); ?></p>
        <?php endif ?>
     </div>
 </div><!-- .postbox -->