<?php
namespace WeDevs\ERP\WooCommerce;

/**
* Manage WooCommerce Order for ERP
*
* @since 1.0.0
*
* @package WPERP|WooCommerce
*/
class Order {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'woocommerce_order_status_changed', [ $this, 'order_change_trigger' ], 10, 3 );
        add_action( 'erp_crm_load_vue_js_template', [ $this, 'load_order_actvity_js_template' ] );
        add_action( 'erp_crm_load_contact_vue_scripts', [ $this, 'load_scripts' ] );
        add_action( 'wp_ajax_erp_wc_sync_table', [ $this, 'sync_data' ], 10 );
    }

    /**
     * Sync data for WooCommerce
     *
     * @since 1.0.0
     * @since 1.0.3 Set max_execution_time = 0 during syncing
     *
     * @return void
     */
    public function sync_data() {
        global $wpdb;

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-wc-nonce' ) ) {
            wp_send_json_error();
        }

        if ( ! erp_wc_is_crm_sync_active() && ! erp_wc_is_accounting_sync_active() ) {
            wp_send_json_error( array(
                'offset'  => 0,
                'message' => sprintf( __( 'CRM and Accounting synchronization are not enable for synchronization', 'erp-woocommerce' ) )
            ) );
        }

        $limit        = $_POST['limit'];
        $offset       = $_POST['offset'];
        $total_orders = isset( $_POST['total_orders'] ) ? $_POST['total_orders'] : 0;

        if ( $offset == 0 ) {
            $total_orders = $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts
                WHERE post_type = 'shop_order' AND post_status != 'trash'"  );
        }

        if ( ! $total_orders ) {
            wp_send_json_error( array(
                'offset'  => 0,
                'message' => sprintf( __( 'No order founds', 'erp-woocommerce' ) )
            ) );
        }

        $sql = "SELECT ID FROM $wpdb->posts
                WHERE post_type = 'shop_order' AND post_status != 'trash'
                ORDER BY ID ASC
                LIMIT %d,%d";

        $orders = $wpdb->get_results( $wpdb->prepare($sql, $offset, $limit ) );

        if ( $orders ) {
            @ini_set( 'max_execution_time', '0' );

            foreach ( $orders as $key => $order_obj ) {
                $this->sync_order( $order_obj->ID );
            }

            $done = ( $offset+$limit );

            if (  $done >= $total_orders ) {
                $completed = $total_orders;
            } else {
                $completed = $done;
            }

            wp_send_json_success( array(
                'offset'       => ($offset+$limit),
                'total_orders' => $total_orders,
                'done'         => $completed,
                'message'      => sprintf( __( '%d orders sync completed out of %d', 'erp-woocommerce' ), $completed, $total_orders )
            ) );

        } else {
            wp_send_json_success( array(
                'offset'  => 0,
                'done'    => 'All',
                'message' => sprintf( __( 'All orders has been synchronized', 'erp-woocommerce' ) )
            ) );
        }

    }

    /**
     * Initializes the Order class
     *
     * @since 1.0.0
     *
     * Checks for an existing Order instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {

            $instance = new \WeDevs\ERP\WooCommerce\Order();
        }
        return $instance;
    }

    /**
     * Trigger when order placed and status changed
     *
     * @since  1.0.0
     *
     * @param  integer   $order_id
     * @param  string    $old_status
     * @param  string    $new_status
     *
     * @return void
     */
    public function order_change_trigger( $order_id, $old_status, $new_status ) {
        if ( ! $order_id ) {
            return;
        }

        if ( ! erp_wc_is_crm_sync_active() && ! erp_wc_is_accounting_sync_active() ) {
            return;
        }

        $this->sync_order( $order_id );
    }

    /**
     * Sync a single order
     *
     * @since 1.0.0
     * @since 1.0.3 Use WP User first name, last name and email if found
     *
     * @param  \WC_Order $wc_order
     *
     * @return void
     */
    public function sync_order( $order_id ) {
        $order                = wc_get_order( $order_id );
        $order_status         = $order->get_status();
        $customer_user_id     = (int) $order->customer_user;
        $payment_method_title = $order->payment_method_title;

        // If customer is a guest user, then check if this user is already contact or not,
        // using customer _billing_email or _billing_phone. If not then create e new contact and get contact id as a customer id
        $contact = \WeDevs\ERP\Framework\Models\People::where( 'email', $order->billing_email )->whereOr( 'phone', $order->billing_phone )->first();

        if ( $customer_user_id && ! empty( $contact ) && empty( $contact->user_id ) ) {
            $contact->user_id       = $customer_user_id;
            $contact->first_name    = $order->billing_first_name;
            $contact->last_name     = $order->billing_last_name;
            $contact->email         = $order->billing_email;
            $contact->phone         = $order->billing_phone;
            $contact->street_1      = $order->billing_address_1;
            $contact->street_2      = $order->billing_address_2;
            $contact->city          = $order->billing_city;
            $contact->state         = $order->billing_state;
            $contact->postal_code   = $order->billing_postcode;
            $contact->country       = $order->billing_country;

            $contact->save();
        }

        if ( ! $contact ) {
            $contact_owner = erp_get_option( 'erp_woocommerce_contact_owner', false, 0 );

            // requires ERP v1.1.17
            if ( empty( $contact_owner ) && function_exists( 'erp_crm_get_default_contact_owner' ) ) {
                $contact_owner = erp_crm_get_default_contact_owner();
            }

            // in some cases where the same user has multiple variations of emails in different
            // orders, we get some malfunction and have to exclusively add the user_id
            $user = get_user_by( 'id', $customer_user_id );

            $args = [
                'first_name'    => ! empty( $user->first_name ) ? $user->first_name : $order->billing_first_name,
                'last_name'     => ! empty( $user->last_name ) ? $user->last_name : $order->billing_last_name,
                'email'         => ! empty( $user->user_email ) ? $user->user_email : $order->billing_email,
                'phone'         => $order->billing_phone,
                'street_1'      => $order->billing_address_1,
                'street_2'      => $order->billing_address_2,
                'city'          => $order->billing_city,
                'state'         => $order->billing_state,
                'postal_code'   => $order->billing_postcode,
                'country'       => $order->billing_country,
                'contact_owner' => $contact_owner
            ];

            if ( erp_wc_is_crm_sync_active() && ! erp_wc_is_accounting_sync_active() ) {
                $args['type'] = 'contact';
            } else if ( ! erp_wc_is_crm_sync_active() && erp_wc_is_accounting_sync_active() ) {
                $args['type'] = 'customer';
            } else {
                $args['type'] = 'contact';
            }

            $contact_id = erp_insert_people( $args );

            if ( ! is_wp_error( $contact_id ) ) {
                $contact = \WeDevs\ERP\Framework\Models\People::find( $contact_id );
            } else {
                return new \WP_Error( 'contact-error', $contact_id->get_error_message() );
            }

        } else {
            $contact_id = $contact->id;
        }

        if ( empty( $contact_id ) ) {
            return new \WP_Error( 'empty-contact-id', __( 'No contact id found', 'erp-woocommerce' ) );
        }

        $order_statuses = wc_get_order_statuses();
        $order_edit_link = admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' );

        if ( erp_wc_is_crm_sync_active() ) {

            // If order is completed
            if ( 'completed' == $order_status ) {
                $life_stage = erp_get_option( 'erp_woocommerce_ls_paid_user', false, 'customer' );
                $message    = apply_filters( 'erp_wc_order_completed_feeds', sprintf( __( 'Order #<a href="%s">%d</a> %s and paid via <strong>%s</strong>', 'erp-woocommerce' ), $order_edit_link, $order_id, $order_status, $payment_method_title ), $order, $contact );

            } else {
                $life_stage = erp_get_option( 'erp_woocommerce_ls_place_order', false, 'opportunity' );
                $message    = apply_filters( 'erp_wc_order_note_feeds', sprintf( __( 'Placed an Order #<a href="%s">%d</a> of total <strong>%s</strong> status: %s', 'erp-woocommerce' ), $order_edit_link, $order_id, $order->get_formatted_order_total(), $order_statuses[ 'wc-' . $order_status ] ), $order, $contact );
            }

            $data = [
                'user_id'    => $contact_id,
                'message'    => $message,
                'type'       => 'order_note',
                'created_at' => $order->order_date
            ];

            erp_wc_cerate_actvity_order( $data );
            erp_wc_save_order_data( $contact, $order );

            $existing_life_stage = erp_people_get_meta( $contact_id, 'life_stage', true );

            if ( 'customer' != $existing_life_stage ) {
                erp_wc_change_life_status( $contact, $life_stage );
            }
        }

        if ( erp_wc_is_accounting_sync_active() ) {

            $order_data = \WeDevs\ERP\WooCommerce\Model\Product_Order::where( 'order_id', $order->id )
                        ->where( 'order_status', $order_status )
                        ->where( 'accounting', '0' )
                        ->first();

            if ( $order_data ) {
                $data = new \WeDevs\ERP\WooCommerce\Accounting( $order->id );
                $data->create_customer( $contact_id );
                $data->create_transaction();
                \WeDevs\ERP\WooCommerce\Model\Product_Order::where( [ 'order_id' => $order->id ] )
                    ->where( 'order_status', $order_status )
                    ->update( [
                        'accounting' => ( 'completed' == $order_status ) ? 1 : 0
                    ] );
            }
        }

        return true;
    }

    /**
     * Load order activity template
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_order_actvity_js_template() {
        global $current_screen;

        switch ( $current_screen->base ) {
            case 'crm_page_erp-sales-customers':
            case 'crm_page_erp-sales-companies':
                if ( isset( $_GET['action'] ) && $_GET['action'] == 'view' ) {
                    erp_get_vue_component_template( WPERP_WOOCOMMERCE_INCLUDES . '/views/timeline-order-note.php', 'erp-wc-timeline-feed-order-note' );
                }

            break;

            case 'crm_page_erp-sales-activities':
                    erp_get_vue_component_template( WPERP_WOOCOMMERCE_INCLUDES . '/views/timeline-order-note.php', 'erp-wc-timeline-feed-order-note' );
            break;
        }
    }

    /**
     * Load component scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_scripts() {
        wp_enqueue_script( 'erp-wc-component', WPERP_WOOCOMMERCE_ASSETS . '/js/erp-wc-component.js',[
            'erp-nprogress',
            'erp-script',
            'erp-vuejs',
            'underscore',
            'erp-select2',
            'erp-tiptip'
            ], date( 'Ymd' ), true );
    }
}
