<?php
namespace WeDevs\ERP\WooCommerce;

/**
* Accounting data manupulation class
*
* @since 1.0.0
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
     * Autometically loaded when class initiate
     *
     * @since 1.0.0
     */
    public function __construct( $order_id = NULL, $contact_id = 0 ) {
        if ( $order_id ) {
            $this->order_id = $order_id;
            $this->order = new \WC_Order( $order_id );
        }
    }

    /**
    * Create a customer
    *
    * @since 0.0.1
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
    * @since 1.0.0
    *
    * @return void
    **/
    public function create_transaction() {
        global $current_user;

        $order_status = $this->order->get_status();

        $this->prepare_line_item();

        if ( ! $this->customer->id ) {
            return;
        }

        $line_total = wp_list_pluck( $this->line_items, 'line_total' );
        $thousand_seperator = erp_ac_get_price_thousand_separator();
        $billing_address = explode( '<br/>', $this->order->get_formatted_billing_address() );

        if ( ! empty( $billing_address ) ) {
            unset( $billing_address[0] );
            unset( $billing_address[1] );
            $billing_address = implode( '<br/>', $billing_address );
        }

        $fields = [
            'partial_id'      => [],
            'items_id'        => [],
            'journals_id'     => [],
            'type'            => 'sales',
            'user_id'         => $this->customer->id,
            'billing_address' => $billing_address,
            'ref'             => '',
            'issue_date'      => $this->order->get_date_created()->date( 'Y-m-d H:i:s' ),
            'due_date'        => $this->order->get_date_created()->date( 'Y-m-d H:i:s' ),
            'summary'         => '',
            'total'           => str_replace( $thousand_seperator, '', $this->order->get_total() ),
            'sub_total'       => str_replace( $thousand_seperator, '', $this->order->get_subtotal() ),
            'trans_total'     => str_replace( $thousand_seperator, '', $this->order->get_total() ),
            'files'           => '',
            'currency'        => get_woocommerce_currency(),
            'line_total'      => $line_total
        ];

        if ( 'completed' == $order_status ) {
            $transaction_id = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );
            $account_id     =  erp_get_option( 'erp_woocommerce_payment_account_head', false, 7 );

            $fields['id']             = '';
            $fields['form_type']      = 'payment';
            $fields['account_id']     = $account_id;
            $fields['status']         = 'closed';
            $fields['partial_id']     = $transaction_id ? [ $transaction_id ] : [];
            $fields['line_total']     = $transaction_id ? [ $this->order->get_total() ] : $line_total;
            $fields['invoice_number'] = erp_ac_get_auto_generated_invoice( 'payment' );

            $order_note_mesg = sprintf( __( 'ERP Accounting: Payment: %s created successfully', 'erp-woocommerce' ), $fields['invoice_number'] );

        } else {
            $transaction_id    = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );
            $already_completed = get_post_meta( $this->order->get_id(), '_erp_wc_order_status', true );

            if ( is_wp_error( $transaction_id ) ) {
                return;
            }

            if ( $transaction_id ) {
                $transaction    = erp_ac_get_transaction( $transaction_id );
                $invoice_number = erp_ac_get_invoice_number( $transaction['invoice_number'], $transaction['invoice_format'] );
            } else {
                $invoice_number = erp_ac_get_auto_generated_invoice( 'invoice' );
            }

            $fields['id']             = ( $transaction_id ) ? $transaction_id : '';
            $fields['due']            = str_replace( $thousand_seperator, '', $this->order->get_total() );
            $fields['form_type']      = 'invoice';
            $fields['account_id']     = 1;
            $fields['invoice_number'] = $invoice_number;

            if ( $already_completed == 'completed' ) {
                $payment_id = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_payment_id', true );
                erp_ac_remove_transaction( $payment_id );
                update_post_meta( $this->order->get_id(), '_erp_ac_transaction_payment_id', '' );
                $message = __( 'status changed to ', 'erp-woocommerce' );
            } else {
                $message = __( 'created successfully and status set as ', 'erp-woocommerce' );
            }

            if ( 'processing' == $order_status ) {
                $fields['status'] = 'awaiting_payment';
                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s %s awaiting payment', 'erp-woocommerce' ), $fields['invoice_number'], $message );
            } else if ( 'cancelled' == $order_status ) {
                $fields['status'] = 'void';
                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s %s void ', 'erp-woocommerce' ), $fields['invoice_number'], $message );
            } else {
                $fields['status'] = 'awaiting_approval';
                $order_note_mesg = sprintf( __( 'ERP Accounting: Invoice: %s %s awaiting approval ', 'erp-woocommerce' ), $fields['invoice_number'], $message );
            }
        }

        $current_user->add_cap( 'erp_ac_publish_sales_invoice' );

        $insert_id = erp_ac_insert_transaction( $fields, $this->line_items );

        $current_user->remove_cap( 'erp_ac_publish_sales_invoice' );

        if ( 'completed' == $order_status ) {
            update_post_meta( $this->order->get_id(), '_erp_ac_transaction_payment_id', $insert_id );
            update_post_meta( $this->order->get_id(), '_erp_wc_order_status', 'completed' );
        } else {
            update_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', $insert_id );
        }

        if ( is_wp_error( $insert_id ) ) {
            $this->order->add_order_note( $insert_id->get_error_message() );
        } else {
            $this->order->add_order_note( $order_note_mesg );
        }
    }

    /**
    * Prepare line item
    *
    * @since 1.0.0
    *
    * @return
    **/
    public function prepare_line_item() {
        $items = $this->order->get_items();

        if ( ! $items ) {
            return;
        }

        $transaction_id     = get_post_meta( $this->order->get_id(), '_erp_ac_transaction_id', true );
        $item_account_id    = erp_get_option( 'erp_woocommerce_product_account_head', false, 54 );
        $thousand_seperator = erp_ac_get_price_thousand_separator();

        if ( 'completed' == $this->order->get_status() && $transaction_id ) {
            $this->line_items[] = [
                'item_id'     => [],
                'journal_id'  => [],
                'account_id'  => 1,
                'description' => '',
                'qty'         => 1,
                'unit_price'  => '0',
                'discount'    => '0',
                'line_total'  => $this->order->get_total(),
                'tax'         => 0,
                'tax_rate'    => 0,
                'tax_amount'  => 0,
                'tax_journal' => 0
            ];
        } else {
            foreach ( $items as $key => $item ) {
                $tax = $this->order->get_items( array('tax' ) );
                if ( $tax ) {
                    $tax_data = $this->create_tax( $item );
                }

                $this->line_items[] = [
                    'item_id'     => 0,
                    'journal_id'  => 0,
                    'product_id'  => isset( $item['product_id'] ) ? intval( $item['product_id'] ) : 0,
                    'account_id'  => $item_account_id,
                    'description' => '',
                    'qty'         => $item['qty'],
                    'unit_price'  => $item['line_subtotal'],
                    'discount'    => ( $item['line_subtotal']-$item['line_total'] ),
                    'line_total'  => $item['line_total'],
                    'tax'         => isset( $tax_data['tax_id'] ) ? $tax_data['tax_id'] : 0,
                    'tax_rate'    => isset( $tax_data['tax_rate'] ) ? $tax_data['tax_rate'] : 0,
                    'tax_amount'  => isset( $item['line_tax'] ) ? str_replace( $thousand_seperator, '', $item['line_tax'] ) : 0,
                    'tax_journal' => 0
                ];
            }

            if ( wc_shipping_enabled() ) {
                $shipping = $this->order->get_items( array('shipping' ) );

                if ( $shipping ) {
                    $order_shipping      = reset( $shipping ) ;
                    $shipping_cost       = ! empty( $order_shipping['cost'] ) ? $order_shipping['cost'] : 0;
                    $shipping_account_id = erp_get_option( 'erp_woocommerce_shipping_account_head', false, 54 );

                    if ( isset( $order_shipping['taxes'] ) && ! empty( $order_shipping['taxes'] ) ) {
                        $tax         = maybe_unserialize( $order_shipping['taxes'] );
                        $tax_number  = 'wc-' . key( $tax );
                        $tax_amount  = reset( $tax );
                        $tax_data    = \WeDevs\ERP\Accounting\Model\Tax::whereTaxNumber( $tax_number )->first();
                        $wc_tax_data = \WC_Tax::_get_tax_rate( key( $tax ) );
                    }

                    $this->line_items[] = [
                        'item_id'     => 0,
                        'journal_id'  => 0,
                        'product_id'  => 0,
                        'account_id'  => $shipping_account_id,
                        'description' => '',
                        'qty'         => 1,
                        'unit_price'  => $shipping_cost,
                        'discount'    => 0,
                        'line_total'  => $shipping_cost,
                        'tax'         => isset( $tax_data->id ) ? $tax_data->id : 0,
                        'tax_rate'    => isset( $wc_tax_data['tax_rate'] ) ? $wc_tax_data['tax_rate'] : 0,
                        'tax_amount'  => isset( $tax_amount ) ? str_replace( $thousand_seperator, '', $tax_amount ) : 0,
                        'tax_journal' => 0
                    ];
                }
            }
        }
    }

    /**
    * Create tax
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function create_tax( $item ) {
        $tax_based_on = get_option( 'woocommerce_tax_based_on' );
        $update       = false;

        if ( 'billing' === $tax_based_on ) {
            $country  = $this->order->billing_country;
            $state    = $this->order->billing_state;
            $postcode = $this->order->billing_postcode;
            $city     = $this->order->billing_city;
        } elseif ( 'shipping' === $tax_based_on ) {
            $country  = $this->order->shipping_country;
            $state    = $this->order->shipping_state;
            $postcode = $this->order->shipping_postcode;
            $city     = $this->order->shipping_city;
        }

        // Default to base
        if ( 'base' === $tax_based_on || empty( $country ) ) {
            $default  = wc_get_base_location();
            $country  = $default['country'];
            $state    = $default['state'];
            $postcode = '';
            $city     = '';
        }

        $tax_class         = isset( $item['tax_class'] ) ? $item['tax_class'] : '';
        $tax_rate = \WC_Tax::find_rates( array(
            'country'   => $country,
            'state'     => $state,
            'postcode'  => $postcode,
            'city'      => $city,
            'tax_class' => $tax_class
        ) );

        $wc_tax_rate_id = key( $tax_rate );
        $wc_tax_array = reset( $tax_rate );
        $tax_number = 'wc-' . $wc_tax_rate_id;

        $ac_tax = \WeDevs\ERP\Accounting\Model\Tax::whereTaxNumber( $tax_number )->first();

        $args = [
            'id'             => 0,
            'tax_name'       => $wc_tax_array['label'],
            'tax_number'     => $tax_number,
            'is_compound'    => '',
            'created_by'     => 1,
            'items_id'       => [],
            'component_name' => [$wc_tax_array['label']],
            'agency_name'    => [$wc_tax_array['label']],
            'tax_rate'       => [$wc_tax_array['rate']],
        ];

        if ( $ac_tax ) {
            $update = true;
            $tax_items = \WeDevs\ERP\Accounting\Model\Tax_Items::whereTaxId( $ac_tax->id )->get()->toArray();
            $item_ids = wp_list_pluck( $tax_items, 'id' );
            $args['id'] = $ac_tax->id;
            $args['items_id'] = $item_ids;
        }

        $new_ac_tax = erp_ac_new_tax( $args );
        $tax_id     = $update ? $ac_tax->id : $new_ac_tax->id;

        if ( $update ) {
            $args['id'] = $tax_id;
        }

        erp_ac_update_tax_items( $args, $tax_id );
        erp_ac_new_tax_account( $args, $tax_id );

        $data = [
            'tax_id'   => $tax_id,
            'tax_rate' => $wc_tax_array['rate']
        ];

        return apply_filters( 'erp_wc_get_item_tax_rate', $data );
    }
}
