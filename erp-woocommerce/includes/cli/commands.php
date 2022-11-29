<?php
namespace WeDevs\ERP\WooCommerce\CLI;

use WeDevs\ERP\WooCommerce\Order;

/**
 * Accounting CLI class
 */
class Commands extends \WP_CLI_Command {

    /**
     * Delete everything related to ERP WooCommerce plugin
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function delete() {
        global $wpdb;
        // truncate table
        $tables = [ 'erp_wc_order_product', 'erp_wc_orders' ];
        foreach ($tables as $table) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table);
        }

        $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key`='_erp_ac_transaction_payment_id'" );
        $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key`='_erp_wc_order_status'" );
        $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key`='_erp_ac_transaction_id'" );

        delete_option( 'erp_woocommerce_is_crm_active' );
        delete_option( 'erp_woocommerce_ls_register_user' );
        delete_option( 'erp_woocommerce_ls_place_order' );
        delete_option( 'erp_woocommerce_ls_paid_user' );
        delete_option( 'erp_woocommerce_contact_owner' );
        delete_option( 'erp_woocommerce_is_accounting_active' );
        delete_option( 'erp_woocommerce_payment_account_head' );
        delete_option( 'erp_woocommerce_product_account_head' );
        delete_option( 'erp_woocommerce_shipping_account_head' );

        \WP_CLI::success( "Table deleted successfully!" );
    }

    /**
     * WP CLI api for syncing WooCommerce data
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function sync() {
        global $wpdb;

        $total_orders = $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts
                WHERE post_type = 'shop_order' AND post_status != 'trash'"  );

        if ( ! $total_orders ) {
            \WP_CLI::error( __( 'No orders found!', 'erp-woocommerce' ) );
        }

        $sql = "SELECT ID FROM $wpdb->posts
                WHERE post_type = 'shop_order' AND post_status != 'trash' ORDER BY ID ASC";

        $orders = $wpdb->get_results( $sql );

        if ( $orders ) {

            $erp_order = Order::init();

            foreach ( $orders as $key => $order_obj ) {

                try {
                    \WP_CLI::debug( 'Processing order#'. $order_obj->ID );

                    $response = $erp_order->sync_order( $order_obj->ID );

                    if ( is_wp_error( $response )) {
                        \WP_CLI::error( "Order#{$order_obj->ID} error: " . $response->get_error_message(), false );
                    } elseif ( $response === true ) {
                        \WP_CLI::success( "Order#{$order_obj->ID} imported successfully!" );
                    }
                } catch (Exception $e) {
                    \WP_CLI::error( "Order#{$order_obj->ID} Exception: " . $e->getMessage(), false );
                }

            }
        }
    }

}
