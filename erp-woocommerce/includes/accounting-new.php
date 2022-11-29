<?php
namespace WeDevs\ERP\WooCommerce;

/**
* Accounting data manupulation class
*
* @since 1.2.0
*/
class Accounting {

    /**
     * $order_id int
     *
     * @var object
     */
    protected $order_id;

    /**
     * $line_items int
     *
     * @var object
     */
    protected $line_items;

    /**
     * $order object
     *
     * @var object
     */
    protected $order;

    /**
     * Contact info
     */
    protected $customer;

    /**
     * Tax agency id
     */
    protected $tax_agency_id;

    /**
     * Autometically loaded when class initiate
     *
     * @since 1.2.0
     */
    public function __construct( $order_id = NULL, $contact_id = 0 ) {
        if ( $order_id ) {
            $this->order_id = $order_id;
            $this->order = new \WC_Order( $order_id );
        }

        $this->tax_agency_id = $this->get_woocommerce_agency_id();
    }

    /**
    * Create a customer
    *
    * @since 1.2.0
    *
    * @return void
    **/
    public function create_customer( $id ) {
        if ( !$id ) {
            return;
        }

        $people   = \WeDevs\ERP\Framework\Models\People::find( $id );

        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( 'customer' )->first();

        if ( ! empty( $type_obj ) && ! $people->hasType( 'customer' ) ) {
            $people->assignType( $type_obj );
        }

        $this->customer = $people;

        return $this->customer;
    }

    /**
    * Create a transaction base of order status
    *
    * @since 1.2.0
    *
    * @return void
    **/
    public function create_transaction() {
        global $wpdb;
        global $current_user;

        $order_status = $this->order->get_status();

        if ( ! $this->customer->id ) {
            return;
        }

        $transaction_id          = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );
        $already_inserted_status = get_post_meta( $this->order->get_id(), '_erp_wc_order_status', true );

        if ( $transaction_id && ( $already_inserted_status == $order_status ) ) {
            // no need for duplicate entries
            return;
        }

        $thousand_seperator = erp_ac_get_price_thousand_separator();
        $billing_address = explode( '<br/>', $this->order->get_formatted_billing_address() );

        if ( ! empty( $billing_address ) ) {
            unset( $billing_address[0] );
            unset( $billing_address[1] );
            $billing_address = implode( '<br/>', $billing_address );
        }

        $invoice_fields = [
            'customer_id'     => $this->customer->id,
            'customer_name'   => $this->customer['first_name'] . ' ' . $this->customer['last_name'],
            'trn_date'        => $this->order->get_date_created()->date( 'Y-m-d' ),
            'due_date'        => $this->order->get_date_created()->date( 'Y-m-d' ),
            'billing_address' => $billing_address,
            'amount'          => str_replace( $thousand_seperator, '', $this->order->get_total() ),
            'discount'        => $this->order->get_discount_total(),
            'discount_type'   => 'discount-value',
            'tax'             => $this->order->get_total_tax(),
            'estimate'        => 0,
            'status'          => erp_acct_trn_status_by_id('awaiting_payment'),
            'tax_rate_id'     => null,
            'attachments'     => null,
            'particulars'     => 'from woocommerce',
            'currency'        => get_woocommerce_currency(),
        ];

        $payment_fields = [
            'customer_id'      => $this->customer->id,
            'customer_name'    => $this->customer['first_name'] . ' ' . $this->customer['last_name'],
            'trn_date'         => $this->order->get_date_created()->date( 'Y-m-d' ),
            'amount'           => str_replace( $thousand_seperator, '', $this->order->get_total() + $this->order->get_total_tax() ),
            'trn_by'           => 1,
            'trn_by_ledger_id' => $this->get_ledger_id_by_slug('cash'),
            'deposit_to'       => $this->get_ledger_id_by_slug('cash'),
            'status'           => erp_acct_trn_status_by_id('closed'),
            'attachments'      => null,
            'particulars'      => 'from woocommerce',
            'currency'         => get_woocommerce_currency(),
        ];

        $current_user->add_cap( 'erp_ac_publish_sales_invoice' );

        if ( 'completed' == $order_status ) {
            $transaction_id = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );

            if ( empty( $transaction_id ) ) {
                // create new invoice with paid status
                $invoice_fields['status'] = erp_acct_trn_status_by_id('paid');

                $order_status = 'pending_payment';

                $this->prepare_line_item();

                $invoice_fields['line_items'] = $this->line_items;

                $trn = erp_acct_insert_invoice( $invoice_fields );

                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                update_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', $trn['voucher_no'] );
                update_post_meta( $this->order->get_id(), '_erp_wc_order_status', $order_status );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s created successfully and status set as  awaiting payment', 'erp-woocommerce' ),  $trn['voucher_no'] );

                $this->create_transaction();
            } else {
                $this->prepare_line_item();

                $payment_fields['line_items'] = $this->line_items;

                $trn = erp_acct_insert_payment( $payment_fields );
                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                update_post_meta( $this->order->get_id(), '_erp_ac_transaction_payment_id', $trn['voucher_no'] );
                update_post_meta( $this->order->get_id(), '_erp_wc_order_status', 'completed' );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Payment: %s created successfully', 'erp-woocommerce' ), $trn['voucher_no'] );
            }
        } else if ( 'canceled' == $order_status ||  'failed' == $order_status || 'refunded' == $order_status ) {
            $transaction_id = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );

            $this->prepare_line_item();

            if ( empty( $transaction_id ) ) {
                // create new invoice with void status
                $invoice_fields['status'] = erp_acct_trn_status_by_id('void');
                $invoice_fields['line_items'] = $this->line_items;
                $trn = erp_acct_insert_invoice( $invoice_fields );

                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                update_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', $trn['voucher_no'] );
                update_post_meta( $this->order->get_id(), '_erp_wc_order_status', $order_status );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s is void', 'erp-woocommerce' ),  $trn['voucher_no'] );
            } else {
                // update invoice status to void
                $wpdb->update(
                    $wpdb->prefix . 'erp_acct_invoices',
                    array(
                        'status' => erp_acct_trn_status_by_id('void')
                    ),
                    array( 'voucher_no' => $transaction_id )
                );

                // remove associated payment if exists
                erp_acct_delete_payment( $transaction_id );

                $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s is void', 'erp-woocommerce' ),  $transaction_id );
            }

        } else {
            // remove associated payment if exists
            erp_acct_delete_payment( $transaction_id );

            $this->prepare_line_item();

            $invoice_fields['line_items'] = $this->line_items;
            $trn = erp_acct_insert_invoice( $invoice_fields );

            $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

            update_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', $trn['voucher_no'] );
            update_post_meta( $this->order->get_id(), '_erp_wc_order_status', $order_status );

            $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s created successfully and status set as  awaiting payment', 'erp-woocommerce' ),  $trn['voucher_no'] );
        }

        if ( empty( $trn['voucher_no'] ) ) {
            $this->order->add_order_note( 'Something went wrong.' );
        } else {
            $this->order->add_order_note( $order_note_mesg );
        }
    }

    /**
    * Prepare line item
    *
    * @since 1.2.0
    *
    * @return
    **/
    public function prepare_line_item() {
        global $wpdb;

        $items = $this->order->get_items();

        if ( ! $items ) {
            return;
        }

        $transaction_id     = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );
        $thousand_seperator = erp_ac_get_price_thousand_separator();

        if ( 'completed' == $this->order->get_status() && $transaction_id ) {
            $this->line_items[] = [
                'invoice_no' => 1,
                'amount'     => $this->order->get_total(),
                'line_total' => $this->order->get_total() + $this->order->get_total_tax()
            ];
        } else {
            foreach ( $items as $key => $item ) {
                $tax_rate        = null;
                $tax_rate_agency = [];

                if ( ! empty( $item['taxes']['total'] ) ) {
                    $tax = $item['taxes']['total'];
                    $tax_rate_id = key( $tax );

                    $tax_rate = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate
                        FROM {$wpdb->prefix}woocommerce_tax_rates
                        WHERE tax_rate_id = %d", $tax_rate_id
                    ));

                    $tax_rate_agency[] = [
                        'agency_id' => $this->tax_agency_id,
                        'tax_rate'  => $tax_rate,
                    ];
                }

                $this->line_items[] = [
                    'tax_cat_id'      => null,
                    'tax_rate_agency' => $tax_rate_agency,
                    'product_id'      => isset( $item['product_id'] ) ? intval( $item['product_id'] ) : 0,
                    'qty'             => $item['qty'],
                    'unit_price'      => $item['line_subtotal'],
                    'discount'        => ( $item['line_subtotal']-$item['line_total'] ),
                    'item_total'      => $item['line_total'],
                    'tax_rate'        => $tax_rate,
                    'tax'             => isset( $item['line_tax'] ) ? str_replace( $thousand_seperator, '', $item['line_tax'] ) : 0,
                    'ecommerce_type'  => 'woocommerce'
                ];
            }
        }
    }

    /**
     * Get woocommerce agency id from `erp_acct_tax_agencies`
     *
     * @return int
     */
    public function get_woocommerce_agency_id() {
        global $wpdb;

        $agency_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}erp_acct_tax_agencies WHERE ecommerce_type = 'woocommerce'");

        if ( ! $agency_id ) {
            $wpdb->insert($wpdb->prefix . 'erp_acct_tax_agencies', [
                'name'           => 'Woocommerce Tax Agency',
                'ecommerce_type' => 'woocommerce'
            ]);

            $agency_id = $wpdb->insert_id;
        }

        return $agency_id;
    }

    /**
     * Get ledger id by slug
     *
     * @return int
     */
    public function get_ledger_id_by_slug( $slug ) {
        $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();

        return $ledger_map->get_ledger_id_by_slug( $slug );
    }

}
