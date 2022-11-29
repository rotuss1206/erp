<?php

namespace WeDevs\ERP\WooCommerce;

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * Settings class
 *
 * @since 1.0.0
 *
 * @package WPERP|WooCommerce
 */
class WooCommerce_Settings extends ERP_Settings_Page {

    /**
     * Constructor function
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->id            = 'erp-woocommerce';
        $this->label         = __( 'WooCommerce', 'erp-woocommerce' );
        $this->single_option = true;
        $this->sections      = $this->get_sections();

        add_action( 'erp_admin_field_wc_data_sync', [ $this, 'wc_sync_data' ] );
    }

    /**
     * Get registered tabs
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_sections() {

        $sections = [
            'wc_sync'         => __( 'Synchronization', 'erp-woocommerce' ),
            'wc_subscription' => __( 'Subscription', 'erp-woocommerce' ),
        ];

        if ( wperp()->modules->is_module_active( 'crm' ) ) {
            $sections['crm'] = __( 'CRM', 'erp-woocommerce' );
        }

        if ( wperp()->modules->is_module_active( 'accounting' ) ) {
            $sections['accounting'] = __( 'Accounting', 'erp-woocommerce' );
        }

        return $sections;
    }


    /**
     * Get sections fields
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {

        if ( wperp()->modules->is_module_active( 'crm' ) ) {
            $life_stages = erp_crm_get_life_stages_dropdown_raw();
            $crm_users   = erp_crm_get_crm_user();
            $users       = [ '' => __( '&mdash; Select Owner &mdash;', 'erp-woocommerce' ) ];

            foreach ( $crm_users as $user ) {
                $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
            }

            $fields['crm'] = [
                [
                    'title' => __( '', 'erp-woocommerce' ),
                    'type'  => 'title',
                ],
                [
                    'title'   => __( 'Enable CRM Sync', 'erp-woocommerce' ),
                    'type'    => 'radio',
                    'options' => [ 'yes' => __( 'Yes', 'erp-woocommerce' ), 'no' => __( 'No', 'erp-woocommerce' ) ],
                    'id'      => 'erp_woocommerce_is_crm_active',
                    'desc'    => __( 'Active all crm importing functionality with WooCommerce order', 'erp-woocommerce' ),
                    'tooltip' => true,
                    'default' => 'yes'
                ],
                [
                    'title'   => __( 'When registers as a customer', 'erp-woocommerce' ),
                    'type'    => 'select',
                    'options' => $life_stages,
                    'id'      => 'erp_woocommerce_ls_register_user',
                    'desc'    => __( 'When user register as a customer then which life stage you want to chose when contact created( default : Lead )', 'erp-woocommerce' ),
                    'class'   => 'erp-select2',
                    'tooltip' => true,
                    'default' => 'lead'
                ],
                [
                    'title'   => __( 'When placed an order', 'erp-woocommerce' ),
                    'type'    => 'select',
                    'options' => $life_stages,
                    'id'      => 'erp_woocommerce_ls_place_order',
                    'desc'    => __( 'When user place an order then which life stage you want to choose for a contact( default : Opportunity )', 'erp-woocommerce' ),
                    'class'   => 'erp-select2',
                    'tooltip' => true,
                    'default' => 'opportunity'
                ],
                [
                    'title'   => __( 'When becomes a paid user', 'erp-woocommerce' ),
                    'type'    => 'select',
                    'options' => $life_stages,
                    'id'      => 'erp_woocommerce_ls_paid_user',
                    'desc'    => __( 'When user place an order and the order is completed then which life stage you want to choose for a contact( default : Customer )', 'erp-woocommerce' ),
                    'class'   => 'erp-select2',
                    'tooltip' => true,
                    'default' => 'customer'
                ],
                [
                    'title'   => __( 'Default Contact Owner', 'erp-woocommerce' ),
                    'id'      => 'erp_woocommerce_contact_owner',
                    'type'    => 'select',
                    'class'   => 'erp-select2',
                    'desc'    => __( 'Default contact owner for contact.', 'erp-woocommerce' ),
                    'options' => $users,
                    'tooltip' => true
                ],
                [
                    'type' => 'sectionend',
                    'id'   => 'erp_woocommerce_script_styling_options'
                ]
            ];
        }

        if ( wperp()->modules->is_module_active( 'accounting' ) ) {
            if ( version_compare( WPERP_VERSION , '1.5.0', '>=' ) ) {
                $fields['accounting'] = [
                    [
                        'title' => __( '', 'erp-woocommerce' ),
                        'type'  => 'title',
                    ],
                    [
                        'title'   => __( 'Enable Accounting Sync', 'erp-woocommerce' ),
                        'type'    => 'radio',
                        'options' => [ 'yes' => __( 'Yes', 'erp-woocommerce' ), 'no' => __( 'No', 'erp-woocommerce' ) ],
                        'id'      => 'erp_woocommerce_is_accounting_active',
                        'desc'    => __( 'Sync all accounting data with WooCommerce order', 'erp-woocommerce' ),
                        'tooltip' => true,
                        'default' => 'yes'
                    ],
                    [
                        'type' => 'sectionend',
                        'id'   => 'erp_woocommerce_script_styling_options'
                    ]

                ];
            } else {
                $accounts        = erp_ac_get_chart_dropdown( [ 'exclude' => [ 1, 2, 3, 5 ] ] );
                $account_details = reset( $accounts );
                $account_list    = wp_list_pluck( $account_details['options'], 'name', 'id' );
                $deposit_to      = erp_ac_get_bank_dropdown();

                $fields['accounting'] = [
                    [
                        'title' => __( '', 'erp-woocommerce' ),
                        'type'  => 'title',
                    ],
                    [
                        'title'   => __( 'Enable Accounting Sync', 'erp-woocommerce' ),
                        'type'    => 'radio',
                        'options' => [ 'yes' => __( 'Yes', 'erp-woocommerce' ), 'no' => __( 'No', 'erp-woocommerce' ) ],
                        'id'      => 'erp_woocommerce_is_accounting_active',
                        'desc'    => __( 'Sync all accounting data with WooCommerce order', 'erp-woocommerce' ),
                        'tooltip' => true,
                        'default' => 'yes'
                    ],
                    [
                        'title'   => __( 'Payment account', 'erp-woocommerce' ),
                        'type'    => 'select',
                        'options' => $deposit_to,
                        'id'      => 'erp_woocommerce_payment_account_head',
                        'desc'    => __( '', 'erp-woocommerce' ),
                        'class'   => 'erp-select2',
                        'tooltip' => true,
                        'default' => ''
                    ],
                    [
                        'title'   => __( 'Product account', 'erp-woocommerce' ),
                        'type'    => 'select',
                        'options' => $account_list,
                        'id'      => 'erp_woocommerce_product_account_head',
                        'desc'    => __( 'Invoice or payment items/line-item account', 'erp-woocommerce' ),
                        'class'   => 'erp-select2',
                        'tooltip' => true,
                        'default' => ''
                    ],
                    [
                        'title'   => __( 'Shipping account', 'erp-woocommerce' ),
                        'type'    => 'select',
                        'options' => $account_list,
                        'id'      => 'erp_woocommerce_shipping_account_head',
                        'desc'    => __( 'Shipping account for invoice', 'erp-woocommerce' ),
                        'class'   => 'erp-select2',
                        'tooltip' => true,
                        'default' => ''
                    ],
                    [
                        'type' => 'sectionend',
                        'id'   => 'erp_woocommerce_script_styling_options'
                    ]

                ];
            }

        }

        $fields['wc_sync'] = array(
            array(
                'title' => __( 'WooCommerce data sync', 'erp-woocommerce' ),
                'type'  => 'title',
                'desc'  => __( '', 'erp-woocommerce' ),
                'id'    => 'erp-ac-tax-options'
            ),
            array(
                'type' => 'wc_data_sync'
            ),
            array( 'type' => 'sectionend', 'id' => 'script_styling_optionsjj' ),
        );

        $fields['wc_sync']['submit_button'] = false;

        $contact_groups = Subscription::get_contact_groups();
        $contact_groups = array_merge( [ '0' => __( 'Select a contact group', 'erp-woocommerce' ) ], $contact_groups );


        $fields['wc_subscription'] = [
            [
                'type'  => 'title',
                'title' => __( 'Contact Group Subscription', 'erp-woocommerce' ),
            ],
            [
                'type'    => 'checkbox',
                'title'   => __( 'Show signup on checkout', 'erp-woocommerce' ),
                'id'      => 'show_wc_signup_option',
                'desc'    => __( 'Show a checkbox option to allow customer to signup during checkout', 'erp-woocommerce' ),
                'default' => 'yes'
            ],
            [
                'type'    => 'select',
                'title'   => __( 'Default contact group', 'erp-woocommerce' ),
                'id'      => 'default_wc_signup_contact_group',
                'desc'    => __( 'Select the a default contact group you wish to subscribe your customers to.', 'erp-woocommerce' ),
                'options' => $contact_groups,
                'default' => 0
            ],
            [
                'type'    => 'text',
                'title'   => __( 'Signup option label', 'erp-woocommerce' ),
                'id'      => 'wc_signup_option_label',
                'desc'    => __( 'This text will show next to the signup option', 'erp-woocommerce' ),
                'default' => __( 'Signup for the newsletter', 'erp-woocommerce' ),
            ],
            [
                'type' => 'sectionend'
            ],
        ];


        return $fields[ $section ];
    }


    /**
     * Sync data for WooCommerce
     *
     * @since 1.0.0
     *
     * @return void
     **/
    public function wc_sync_data() {
        require_once WPERP_WOOCOMMERCE_INCLUDES . '/views/wc-sync.php';
    }
}

// return new \WeDevs\ERP\WooCommerce\WooCommerce_Settings();

