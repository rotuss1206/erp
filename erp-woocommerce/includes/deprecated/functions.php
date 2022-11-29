<?php
/**
 * Get erp option from settings framework
 *
 * @param  string $option_name name of the option
 * @param  string $section name of the section. e.g. subscription, 'sync', 'crm', 'accounting'
 * @param  mixed $default default option
 *
 * @return mixed Value set for the option
 */
function erp_wc_get_option( $option_name, $default = false ) {
    $option = get_option($option_name);
    if ( isset( $option ) ) {
        return $option;
    }

    return $default;
}

/**
 * Get all orders for a $customer
 *
 * @since 1.0.0
 *
 * @param $args array
 *
 * @return array
 **/
function erp_wc_get_customer_orders( $args = [] ) {

    $defaults = [
        'customer'  => 0,
        'order_status' => array_keys( wc_get_order_statuses() ),
        'order_type'   => wc_get_order_types(),
        'number'       => -1,
        'orderby'      => 'date',
        'order'        => 'DESC',
        'fields'       => 'ids',
        'meta_key'     => '_customer_user'
    ];

    $args = wp_parse_args( $args, $defaults );

    $params = [
        'posts_per_page' => $args['number'],
        'post_type'      => $args['order_type'],
        'post_status'    => $args['order_status'],
        'orderby'        => $args['orderby'],
        'order'          => $args['order'],
        'fields'         => $args['fields'],
        'meta_key'       => $args['meta_key'],
        'meta_value'     => $args['customer']
    ];

    $customer_orders = get_posts( $params );

    return $customer_orders;
}


/**
 * Get all product for a customer purchased
 *
 * @since 1.0.0
 *
 * @param integer $product_id
 *
 * @return array
 **/
function erp_wc_get_customer_purchased_products( $args ) {
    $defaults = [
        'order_status' => 'wc-completed'
    ];

    $args     = wp_parse_args( $args, $defaults );
    $orders   = erp_wc_get_customer_orders( $args );
    $products = [];

    foreach ( $orders as $order_id ) {
        $order = wc_get_order( $order_id );
        $items = $order->get_items();

        foreach( $items as $item ) {
            $p_id = ( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];

            if ( array_key_exists( $p_id, $products ) ) {
                $products[$p_id]['qty'] += $item['qty'];
            } else {
                $products[$p_id] = [
                    'line_total' => $item['line_total'],
                    'qty' => $item['qty'],
                    'is_variation' => $item['variation_id'] ? 1 : 0,
                    'item' => $item
                ];
            }
        }
    }

    return $products;
}

/**
 * Create activity for order
 *
 * @since 1.0.0
 *
 * @param array $data
 *
 * @return integer
 **/
function erp_wc_cerate_actvity_order( $data ) {
    $saved_activity = \WeDevs\ERP\CRM\Models\Activity::create( $data );
    return $saved_activity->id;
}

/**
 * Change life status
 *
 * @since 1.2.7
 *
 * @param $contact_id
 * @param $life_stage
 *
 * @return bool|string|void|WP_Error
 */
function erp_wc_change_life_status( $contact_id, $life_stage ) {
    $erp_version = get_option('wp_erp_version');
    if( version_compare( $erp_version, '1.2.7', '>=' ) ){
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        $contact->update_life_stage( $life_stage );
    }else{
        erp_people_update_meta( $contact_id, 'life_stage', $life_stage );
    }
}

/**
* Get all WC meta fields
*
* @since 1.0.0
*
* @return array
**/
function erp_wc_meta_fields() {
    return apply_filters( 'erp_wc_meta_fields', [
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_email',
        'billing_phone',
        'billing_country',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_state',
        'billing_postcode',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_postcode',
        'shipping_country',
        'shipping_state'
    ] );
}

/**
 * Get order statuses for WooCommerce
 *
 * @since 1.0.0
 *
 * @param string $selected
 *
 * @return html
 **/
function erp_wc_get_order_statuses_dwopdown( $selected = '' ) {
    $statuses = wc_get_order_statuses();
    $dropdown    = '';

    if ( $statuses ) {
        foreach ( $statuses as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}

/**
 * Added meta for purchased products
 *
 * @since 1.0.0
 *
 * @return void
 **/
function erp_wc_save_order_data( $contact, $order = null ) {
    global $wpdb;
    $table = $wpdb->prefix . 'erp_wc_orders';
    $data = [];

    if ( empty( $contact->id ) ) {
        return;
    }

    if ( $order ) {
        $items = $order->get_items();

        $order_data = \WeDevs\ERP\WooCommerce\Model\Product_Order::where( 'order_id', $order->id )->first();

        if ( ! $order_data ) {
            $is_new = true;
            \WeDevs\ERP\WooCommerce\Model\Product_Order::create( [
                'people_id'    => $contact->id,
                'order_id'     => $order->id,
                'order_status' => $order->get_status(),
                'order_date'   => $order->order_date,
                'order_total'  => $order->get_total()
            ] );
        } else {
            $is_new = false;
            \WeDevs\ERP\WooCommerce\Model\Product_Order::where( [ 'order_id' => $order->id ] )->update( [
                'order_status' => $order->get_status()
            ] );
        }

        if ( $items && $is_new ) {
            foreach ( $items as $key => $item ) {
                $data[] = [
                    'order_id'     => $order->id,
                    'product_id'   => $item['product_id'],
                ];
            }
            \WeDevs\ERP\WooCommerce\Model\Order_Product::insert( $data );
        }
    }
}

/**
* Check is crm sync active or not
*
* @since 1.0.0
*
* @return void
**/
function erp_wc_is_crm_sync_active() {
    if (  wperp()->modules->is_module_active( 'crm' ) && ( 'yes' == erp_get_option( 'erp_woocommerce_is_crm_active', false, 'yes' ) ) ) {
        return true;
    }

    return false;
}

/**
* Check is accounting sync active or not
*
* @since 1.0.0
*
* @return void
**/
function erp_wc_is_accounting_sync_active() {
    if (  wperp()->modules->is_module_active( 'accounting' ) && ( 'yes' == erp_get_option( 'erp_woocommerce_is_accounting_active', false, 'yes' ) ) ) {
        return true;
    }

    return false;
}

