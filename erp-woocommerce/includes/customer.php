<?php
namespace WeDevs\ERP\WooCommerce;

/**
* Manger WooCommerce Customer for ERP
*
* @since 1.0.0
*
* @package WPERP|WooCommerce
*/
class Customer {

	/**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'woocommerce_created_customer', array( $this, 'create_contact' ), 15, 3 );
        add_action( 'erp_crm_contact_left_widgets', array( $this, 'latest_order_widget' ), 10 );
        add_action( 'erp_crm_contact_left_widgets', array( $this, 'purchased_product_widget' ), 11 );
        add_filter( 'erp_crm_contact_meta_fields', array( $this, 'wc_meta_fields_sync' ), 11 );
    }

    /**
     * Initializes the Customer class
     *
     * Checks for an existing Customer instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {

            $instance = new \WeDevs\ERP\WooCommerce\Customer();
        }
        return $instance;
    }

    /**
     * Create contact
     *
     * @since 1.0.0
     *
     * @param integer $customer_id
     * @param array $new_customer_data
     * @param string $password_generated
     *
     * @return void
     */
    public function create_contact( $customer_id, $new_customer_data, $password_generated ) {

        $user = get_user_by( 'id', $customer_id );

        if ( ! in_array( 'customer', $user->roles ) ) {
            return;
        }

        if ( ! erp_wc_is_crm_sync_active() ) {
            return;
        }

        $people = \WeDevs\ERP\Framework\Models\People::whereEmail( $user->user_email )->first();

        if ( isset( $people->user_id ) && $people->user_id ) {
            return;
        }

        $life_stage    = erp_get_option( 'erp_woocommerce_ls_register_user', false, 'lead' );
        $contact_owner = erp_get_option( 'erp_woocommerce_contact_owner', false, 0 );

        $args = array(
            'user_id' => $customer_id,
            'email'   => $user->user_email,
            'type'    => 'contact'
        );

        if ( empty( $people->id ) ) {
            $args['life_stage']    = $life_stage;
            $args['contact_owner'] = $contact_owner;
        }

        if ( empty( $people->id ) ) {
            erp_insert_people( $args );
        } else {
            $callback = function( $userdata ) use( $customer_id ) {
                unset( $userdata['user_pass'], $userdata['role'] );
                $userdata['ID'] = $customer_id;
                return $userdata;
            };

            add_filter( 'erp_crm_make_wpuser_args', $callback, 10 );
            erp_crm_make_wp_user( $people->id, array(
                'email' => $people->email
            ) );
            remove_filter( 'erp_crm_make_wpuser_args', $callback, 10 );
        }
    }

    /**
    * Latest contact order widget
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function latest_order_widget( $customer ) {
        if ( ! ( $customer->email  || $customer->phone ) ) {
            return;
        }

        if ( version_compare( WC_VERSION , '3.0', '>=' ) ) {
            include WPERP_WOOCOMMERCE_INCLUDES . '/views/latest-order.php';
        } else {
            include WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/views/latest-order.php';
        }
    }

    /**
    * Purchased product widget
    *
    * @param object $customer
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function purchased_product_widget( $customer ) {
        if ( ! ( $customer->email  || $customer->phone ) ) {
            return;
        }

        if ( version_compare( WC_VERSION , '3.0', '>=' ) ) {
            include WPERP_WOOCOMMERCE_INCLUDES . '/views/purchased-products.php';
        } else {
            include WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/views/purchased-products.php';
        }
    }

    /**
    * Load user woocommerce meta fields
    *
    * Sync with people table when any woocommerce meta field updated
    *
    * @since 1.0.0
    *
    * @param array $meta_fields
    *
    * @return array
    **/
    public function wc_meta_fields_sync( $meta_fields ) {
        $wc_metas = erp_wc_meta_fields();
        $fields = array_merge( $meta_fields, $wc_metas );
        return $fields;
    }

    /**
     * Chech customer have any order
     *
     * Order status must be wc-completed | wc-processing
     *
     * @since 1.0.0
     *
     * @param integer $customer_id
     *
     * @return boolean
     */
    public function has_bought( $customer_id = null ) {
        $count = 0;
        $bought = false;

        // Get all customer orders
        $customer_orders = get_posts( array(
            'posts_per_page' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => ( $customer_id ) ? $customer_id : get_current_user_id(),
            'post_type'   => 'shop_order',
            'post_status' => array( 'wc-completed', 'wc-processing' )
        ) );

        // Going through each current customer orders
        foreach ( $customer_orders as $customer_order ) {
            $count++;
        }

        if ( $count > 0 ) {
            $bought = true;
        }

        return $bought;
    }

}
