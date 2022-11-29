<?php
namespace WeDevs\ERP\WooCommerce;

use WeDevs\ERP\Framework\Traits\Hooker;

class Subscription {

    use Hooker;

    /**
     * Id for the subscription
     *
     * @since 1.0.0
     *
     * @var string
     */
    private $id;

    /**
     * The default label for signup option in frontend
     *
     * @since 1.0.0
     *
     * @var string
     */
    private $default_option_label;


    function __construct() {
        $this->id                   = 'erp-wc-subscription';
        $this->default_option_label = __( 'Signup for the newsletter', 'erp-woocommerce' );

        // Metabox in WC product editing page
        $this->action( 'add_meta_boxes', 'add_metabox' );

        add_action( 'save_post', [ $this, 'save_metabox' ] );

        // Render checkbox in checkout page
        $this->action( 'woocommerce_review_order_before_submit', 'checkout_fields', 100 );

        // Save data after checkout
        $this->action( 'woocommerce_thankyou', 'insert_customer_in_subscriber_groups', 99, 1 );
    }


    /**
     * Register metabox in download editor page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_metabox() {
        if ( current_user_can( 'edit_product', get_the_ID() ) ) {
            add_meta_box( $this->id, __( 'WooCommerce Subscription', 'erp-woocommerce' ), [
                $this, 'render_metabox'
            ], 'product', 'side' );
        }
    }

    /**
     * Render metabox in download editor page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function render_metabox() {
        global $post;

        $checked = (array) get_post_meta( $post->ID, $this->id, true );

        echo '<p>' . __( 'Select the contact groups you wish to subscribe your customers to, after purchasing this product.', 'erp-woocommerce' ) . '</p>';

        foreach ( self::get_contact_groups() as $group_id => $group_name ) {
            echo '<label>';
            echo '<input type="checkbox" name="' . $this->id . '[]" value="' . esc_attr( $group_id ) . '"' . checked( true, in_array( $group_id, $checked ), false ) . '>';
            echo '&nbsp;' . $group_name;
            echo '</label><br/>';
        }
    }

    /**
     * Save metabox data in download editor page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function save_metabox( $post_id ) {
        update_post_meta( $post_id, 'erp-wc-subscription', isset( $_POST['erp-wc-subscription'] ) ? $_POST['erp-wc-subscription'] : [] );
    }


    /**
     * Get contact groups
     *
     * @since 1.0.0
     *
     * @return array id, name paired associative array
     */
    public static function get_contact_groups() {
        $contact_groups = erp_crm_get_contact_groups( [ 'number' => - 1, 'order' => 'ASC' ] );

        return wp_list_pluck( $contact_groups, 'name', 'id' );
    }


    /**
     * Output the signup checkbox on the checkout screen, if enabled
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function checkout_fields() {
        $show_signup_option = erp_wc_get_option( 'show_wc_signup_option', 'yes' );
        $option_label       = erp_wc_get_option( 'wc_signup_option_label', $this->default_option_label );

        if ( ! erp_validate_boolean( $show_signup_option ) ) {
            return;
        }
        ?>
            <p>
                <label>
                    <input name="erp_wc_subscription_signup" id="erp-wc-subscription-signup" type="checkbox" checked="checked">
                    <?php echo ( ! empty( $option_label ) ) ? $option_label : $this->default_option_label; ?>
                </label>
            </p>
        <?php
    }


    /**
     * Creates subscriber and assign to contact groups after an WC purchase
     *
     * @since 1.0.0
     *
     * @param int $order_id
     *
     * @return void
     */
    public function insert_customer_in_subscriber_groups( $order_id ) {
        global $wpdb;
        global $woocommerce;

        if ( ! class_exists( 'WC_Order' ) ) {
            return;
        }

        $wc_order = new \WC_Order( $order_id );

        if ( empty( $wc_order ) ) {
            return;
        }

        $user = [];

        if ( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
            $user['email']      = $wc_order->get_billing_email();
            $user['first_name'] = $wc_order->get_billing_first_name();
            $user['last_name']  = $wc_order->get_billing_last_name();

        } else {
            $user['email']      = $wc_order->billing_email;
            $user['first_name'] = $wc_order->first_name;
            $user['last_name']  = $wc_order->last_name;
        }

        $products = $wc_order->get_items();

        $erp_groups = [];

        // use default contact group to subscribed
        $default_group_id = erp_wc_get_option( 'default_wc_signup_contact_group', '' );

        if ( ! empty( $default_group_id ) ) {
            $erp_groups[] = $default_group_id;
        }

        // per product contact groups
        foreach ( $products as $product ) {
            $per_product_erp_groups = get_post_meta( $product['product_id'], 'erp-wc-subscription', true );
            if ( ! empty( $per_product_erp_groups ) && is_array( $per_product_erp_groups ) ) {
                $erp_groups = array_merge( $erp_groups, $per_product_erp_groups );
            }
        }

        if ( empty( $erp_groups ) ) {
            return;
        }

        $erp_groups = array_unique( $erp_groups );

        if ( ! empty( $erp_groups ) && ! empty( $user['email'] ) ) {
            $args = [
                'groups'  => $erp_groups,
                'contact' => [
                    'first_name' => isset( $user['first_name'] ) ? $user['first_name'] : '',
                    'last_name'  => isset( $user['last_name'] ) ? $user['last_name'] : '',
                    'email'      => $user['email']
                ]
            ];

            \WeDevs\ERP\CRM\Subscription::instance()->create_subsciber( $args );
        }
    }

}
