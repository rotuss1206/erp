<?php
namespace WeDevs\ERP\WooCommerce;

/**
* Manage Search segment for contact
*
* @since 1.0.0
*
* @package WPERP|WooCommerce
*/
class Segment {

    /**
     * Autometically loaded when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'erp_crm_global_serach_fields', [ $this, 'wc_segment_fields' ], 10, 2 );
        add_filter( 'erp_crm_customer_segmentation_sql', [ $this, 'custom_segmentation' ], 11, 6 );
        add_action( 'wp_ajax_erp-search-wc-product', [ $this, 'search_product' ], 10 );
        add_action( 'wp_ajax_erp-selected-edit-product', [ $this, 'get_formated_editable_product' ], 11, 6 );
    }

    /**
     * Initializes the Segment class
     *
     * Checks for an existing Segment instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new \WeDevs\ERP\WooCommerce\Segment();
        }
        return $instance;
    }

    /**
    * Load all WooCommerce segment fields
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function wc_segment_fields( $fields, $type ) {
        if ( 'contact' == $type ) {
            $wc_fields = [
                'billing_first_name' => [
                    'title'     => __( 'Billing First Name', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'is', 'erp-woocommerce' ),
                        '!'  => __( 'is not', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'billing_last_name' => [
                    'title'     => __( 'Billing Last Name', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'is', 'erp-woocommerce' ),
                        '!'  => __( 'is not', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'billing_email' => [
                    'title'     => __( 'Billing email', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'billing_phone' => [
                    'title'     => __( 'Billing Phone', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'billing_country_state' => [
                    'title'     => __( 'Billing Country/State', 'erp-woocommerce' ),
                    'type'      => 'dropdown',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' )
                    ],
                    'options' => \WeDevs\ERP\Countries::instance()->country_dropdown_options(),
                ],
                'billing_address_1' => [
                    'title'     => __( 'Billing Address 1', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' )
                    ],
                ],
                'billing_address_2' => [
                    'title'     => __( 'Billing Address 2', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' )
                    ],
                ],
                'billing_city' => [
                    'title'     => __( 'Billing City', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' )
                    ]
                ],
                'billing_postcode' => [
                    'title'     => __( 'Billing Postcode', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],

                'shipping_first_name' => [
                    'title'     => __( 'Shipping First Name', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'is', 'erp-woocommerce' ),
                        '!'  => __( 'is not', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'shipping_last_name' => [
                    'title'     => __( 'Shipping Last Name', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'is', 'erp-woocommerce' ),
                        '!'  => __( 'is not', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'shipping_email' => [
                    'title'     => __( 'Shipping email', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'shipping_phone' => [
                    'title'     => __( 'Shipping Phone', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'shipping_country_state' => [
                    'title'     => __( 'Shipping Country/State', 'erp-woocommerce' ),
                    'type'      => 'dropdown',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' )
                    ],
                    'options' => \WeDevs\ERP\Countries::instance()->country_dropdown_options(),
                ],
                'shipping_address_1' => [
                    'title'     => __( 'Shipping Address 1', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' )
                    ],
                ],
                'shipping_address_2' => [
                    'title'     => __( 'Shipping Address 2', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' )
                    ],
                ],
                'shipping_city' => [
                    'title'     => __( 'Shipping City', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' )
                    ]
                ],
                'shipping_postcode' => [
                    'title'     => __( 'Shipping Postcode', 'erp-woocommerce' ),
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => [
                        '%'  => __( 'has', 'erp-woocommerce' ),
                        '!%' => __( 'has not', 'erp-woocommerce' ),
                        ''   => __( 'from', 'erp-woocommerce' ),
                        '!'  => __( 'not from', 'erp-woocommerce' ),
                        '~'  => __( 'contains', 'erp-woocommerce' ),
                        '!~' => __( 'not contains', 'erp-woocommerce' ),
                        '^'  => __( 'begins with', 'erp-woocommerce' ),
                        '$'  => __( 'ends with', 'erp-woocommerce' ),
                    ]
                ],
                'order_status' => [
                    'title'     => __( 'Order status', 'erp-woocommerce' ),
                    'type'      => 'dropdown',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'is', 'erp-woocommerce' ),
                        '!'  => __( 'is not', 'erp-woocommerce' )
                    ],
                    'options' => erp_wc_get_order_statuses_dwopdown(),
                ],

                'order_date' => [
                    'title'     => __( 'Order date', 'erp-woocommerce' ),
                    'type'      => 'date_range',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'exactly', 'erp-woocommerce' ),
                        '!'  => __( 'not', 'erp-woocommerce' ),
                        '>'  => __( 'After', 'erp-woocommerce' ),
                        '<'  => __( 'Before', 'erp-woocommerce' ),
                        '<>'  => __( 'Between', 'erp-woocommerce' ),
                    ]
                ],

                'order_total' => [
                    'title'     => __( 'Order Total', 'erp-woocommerce' ),
                    'type'      => 'number_range',
                    'text'      => '',
                    'condition' => [
                        ''   => __( 'exactly', 'erp-woocommerce' ),
                        '>'  => __( 'grater', 'erp-woocommerce' ),
                        '<'  => __( 'less', 'erp-woocommerce' ),
                        '<>'  => __( 'Between', 'erp-woocommerce' ),
                    ]
                ],

                'ordered_product' => [
                    'title'       => __( 'Ordered Product', 'erp-woocommerce' ),
                    'type'        => 'dropdown_mulitple_select2',
                    'placeholder' => __( 'Search a product', 'erp-woocommerce' ),
                    'action'      => 'erp-search-wc-product',
                    'editaction'  => 'erp-selected-edit-product',
                    'text'        => '',
                    'condition'   => [
                        ''   => __( 'in', 'erp-woocommerce' ),
                        '!'   => __( 'not in', 'erp-woocommerce' ),
                    ]
                ],

            ];

            $fields = $fields + $wc_fields;
        }

        return $fields;
    }

    /**
    * Manupulate custom segmentaions
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function custom_segmentation( $custom_sql, $field, $value, $or_query, $i, $table_alias ) {
        global $wpdb;

        $wc_filtered_field = [ 'order_status', 'order_date', 'order_total', 'ordered_product' ];

        if ( 'billing_country_state' == $field || 'shipping_country_state' == $field ) {
            $pepmeta_tb  = $wpdb->prefix . 'erp_peoplemeta';

            if ( $value ) {
                $custom_sql['where'][] = "(";
                $j=0;

                foreach ( $value as $key => $search_value ) {

                    $search_condition_regx = erp_crm_get_save_search_regx( $search_value );
                    $condition             = array_shift( $search_condition_regx );
                    $key_value             = explode( ':', $search_value );

                    if ( 'billing_country_state' == $field ) {
                        $field_cs = [ 'billing_country', 'billing_state' ];
                    } else {
                        $field_cs = [ 'shipping_country', 'shipping_state' ];
                    }

                    $addOr = ( $j == count( $value )-1 ) ? '' : " OR ";

                    $k = 0;
                    foreach ( $key_value as $index => $key_val ) {
                        $name = "people_meta_" . ( $table_alias ) . "_" . ($i+1) . "_" . ( $k+1 );
                        $custom_sql['join'][$name] = "LEFT JOIN $pepmeta_tb as $name on people.id = $name.`erp_people_id`";
                        $addAnd = ( $k==0 ) ? '' : ' AND ';

                        if ( count( $key_value ) > 1 ) {
                            $custom_sql['where'][] = "$addAnd($name.meta_key='$field_cs[$index]' and $name.meta_value $condition '$key_val')";
                        } else {
                            $custom_sql['where'][] = "$addAnd($name.meta_key='$field_cs[$index]' and $name.meta_value $condition '$key_val')";
                        }
                        $k++;
                    }

                    $custom_sql['where'][] = "$addOr";

                    $j++;
                }
                $custom_sql['where'][] = ( $i == count( $or_query )-1 ) ? ")" : " ) AND";
            }

        } elseif( in_array( $field, $wc_filtered_field ) ) {

            $order_tb                              = $wpdb->prefix . 'erp_wc_orders';
            $order_product_tb                      = $wpdb->prefix . 'erp_wc_order_product';
            $custom_sql['join'][$order_tb]         = "LEFT JOIN $order_tb as wco on people.`id` = wco.`people_id`";
            $custom_sql['join'][$order_product_tb] = "LEFT JOIN $order_product_tb as wcop on wcop.`order_id` = wco.`order_id`";

            if ( $value ) {
                $val = erp_crm_get_save_search_regx( $value );
                $custom_sql['where'][] = "(";
                $j=0;

                foreach ( $val as $search_val => $search_condition ) {

                    $addOr = ( $j == count( $val )-1 ) ? '' : " OR ";

                    if ( 'order_status' == $field ) {
                        $formatted_search_val = str_replace( 'wc-', '', $search_val );
                        $custom_sql['where'][] = "order_status $search_condition '$formatted_search_val'$addOr";
                    }

                    if ( 'order_date' == $field ) {
                        if ( $search_condition == 'BETWEEN' ) {
                            $formatted_val = explode( ',', $search_val );
                            $custom_sql['where'][] = "( DATE_FORMAT( `order_date`, '%Y-%m-%d' ) >= '$formatted_val[0]' AND DATE_FORMAT( `order_date`, '%Y-%m-%d' ) <= '$formatted_val[1]' )$addOr";
                        } else {
                            $custom_sql['where'][] = "DATE_FORMAT( `order_date`, '%Y-%m-%d' ) $search_condition '$search_val'$addOr";
                        }
                    }

                    if ( 'order_total' == $field ) {
                        if ( $search_condition == 'BETWEEN' ) {
                            $formatted_val = explode( ',', $search_val );
                            $custom_sql['where'][] = "( `order_total` >= '$formatted_val[0]' AND `order_total` <= '$formatted_val[1]' )$addOr";
                        } else {
                            $custom_sql['where'][] = "`order_total` $search_condition '$search_val'$addOr";
                        }
                    }

                    if ( 'ordered_product' == $field ) {
                        if ( $search_condition == '=' ) {
                            $custom_sql['where'][] = "( `product_id` IN ( '$search_val' ) )$addOr";
                        }
                    }


                    $j++;
                }

                $custom_sql['where'][] = ( $i == count( $or_query )-1 ) ? ")" : " ) AND";
            }

        } else {
            $custom_sql = apply_filters( 'erp_wc_customer_segmentation_sql', $custom_sql, $field, $value, $or_query, $i, $table_alias );
        }

        return $custom_sql;
    }

    /**
    * Search wc product
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function search_product() {
        global $wpdb;

        $term = isset( $_REQUEST['q'] ) ? stripslashes( $_REQUEST['q'] ) : '';

        if ( empty( $term ) ) {
            $term = wc_clean( stripslashes( $_GET['term'] ) );
        } else {
            $term = wc_clean( $term );
        }

        if ( empty( $term ) ) {
            die();
        }

        $like_term = '%' . $wpdb->esc_like( $term ) . '%';

        if ( is_numeric( $term ) ) {
            $query = $wpdb->prepare( "
                SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
                WHERE posts.post_status = 'publish'
                AND (
                    posts.post_parent = %s
                    OR posts.ID = %s
                    OR posts.post_title LIKE %s
                    OR (
                        postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
                    )
                )
            ", $term, $term, $term, $like_term );
        } else {
            $query = $wpdb->prepare( "
                SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
                WHERE posts.post_status = 'publish'
                AND (
                    posts.post_title LIKE %s
                    or posts.post_content LIKE %s
                    OR (
                        postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
                    )
                )
            ", $like_term, $like_term, $like_term );
        }

        $query .= " AND posts.post_type IN ('" . implode( "','", array_map( 'esc_sql', ['product'] ) ) . "')";

        if ( ! empty( $_GET['exclude'] ) ) {
            $query .= " AND posts.ID NOT IN (" . implode( ',', array_map( 'intval', explode( ',', $_GET['exclude'] ) ) ) . ")";
        }

        if ( ! empty( $_GET['include'] ) ) {
            $query .= " AND posts.ID IN (" . implode( ',', array_map( 'intval', explode( ',', $_GET['include'] ) ) ) . ")";
        }

        if ( ! empty( $_GET['limit'] ) ) {
            $query .= " LIMIT " . intval( $_GET['limit'] );
        }

        $posts          = array_unique( $wpdb->get_col( $query ) );
        $found_products = array();

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $post ) {
                $product = wc_get_product( $post );

                if ( ! $product || ( $product->is_type( 'variation' ) && empty( $product->get_parent_id() ) ) ) {
                    continue;
                }

                if ( $product->get_sku() ) {
                    $identifier = $product->get_sku();
                } else {
                    $identifier = '#' . $product->get_id();
                }

                $product_title = sprintf( '%s - %s', $identifier, $product->get_title() );

                $found_products[ $post ] = $product_title;
            }
        }

        wp_send_json_success( $found_products );
    }

    /**
    * Get formatter product name
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function get_formated_editable_product() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wp-erp-crm-nonce' ) ) {
            wp_send_json_error( __( 'Nonce Verification failed', 'erp-woocommerce' ) );
        }

        $options = [];
        $selected = isset( $_POST['selected'] ) ? $_POST['selected'] : [];

        if ( empty( $selected ) ) {
            wp_send_json_error();
        }

        foreach ( $selected as $key => $product_id ) {
            $product = wc_get_product( $product_id );

            if ( ! $product || ( $product->is_type( 'variation' ) && empty( $product->get_parent_id() ) ) ) {
                continue;
            }

            $options[] = [
                'id' => $product_id,
                'text' => $product->get_formatted_name()
            ];
        }

        wp_send_json_success( $options );
    }

}
