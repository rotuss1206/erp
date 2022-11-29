<?php
/**
 * Plugin Name: WP ERP - WooCommerce
 * Description: WooCommerce integration with CRM and Accounting modules in ERP
 * Plugin URI: http://wperp.com/downloads/erp-woocommerce/
 * Author: weDevs
 * Author URI: http://wedevs.com
 * Version: 1.3.1
 * License: GPL2
 * Text Domain: erp-woocommerce
 * Domain Path: languages
 *
 * Copyright (c) 2016 weDevs (email: info@wperp.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDevs ERP WooCommerce Main Class
 */
class WeDevs_ERP_WooCommerce {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.3.1';


    /**
     * Initializes the WeDevs_ERP_WooCommerce class
     *
     * Checks for an existing WeDevs_ERP_WooCommerce instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {

            $instance = new WeDevs_ERP_WooCommerce();
        }
        return $instance;
    }
    /**
     * Constructor for the WeDevs_ERP_WooCommerce class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        // plugin not installed - notice
        add_action( 'admin_notices', [ $this, 'admin_notice' ] );

        // on plugin register hook
        register_activation_hook( __FILE__, [ $this, 'activate' ] );

        // Make sure both ERP and WC is loaded before initialize
        add_action( 'erp_loaded', [ $this, 'after_erp_loaded' ] );
        add_action( 'woocommerce_loaded', [ $this, 'after_wc_loaded' ] );
    }

    /**
     * Display an error message if WP ERP is not active
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_notice() {
        if ( !class_exists( 'WeDevs_ERP' ) ) {
            printf(
                '%s'. __( '<strong>Error:</strong> <a href="%s">WP ERP</a> Plugin is required to use ERP WooCommerce plugin.', 'erp-woocommerce' ) . '%s',
                '<div class="message error"><p>',
                'https://wordpress.org/plugins/erp/',
                '</p></div>'
            );
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            printf(
                '%s'. __( '<strong>Error:</strong> <a href="%s">WooCommerce</a> Plugin is required to use ERP WooCommerce plugin.', 'erp-woocommerce' ) . '%s',
                '<div class="message error"><p>',
                'https://wordpress.org/plugins/woocommerce/',
                '</p></div>'
            );
        }
    }

    /**
     * Executes while Plugin Activation
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate() {
        if ( ! class_exists( 'WeDevs_ERP' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'You need to install WP-ERP main plugin to use this addon', 'erp-woocommerce' ) );
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'You need to install WooCommerce plugin to use this addon', 'erp-woocommerce' ) );
        }

        // Create all necessary tables
        $this->create_tables();
    }

    /**
     * Execute after ERP is loaded
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function after_erp_loaded() {
        if ( ! did_action( 'woocommerce_loaded' ) ) {
            add_action( 'woocommerce_loaded', [ $this, 'init_plugin' ] );

        } else {
            $this->init_plugin();
        }
    }

    /**
     * Execute after WooCommerce is loaded
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function after_wc_loaded() {
        if ( ! did_action( 'erp_loaded' ) ) {
            add_action( 'erp_loaded', [ $this, 'init_plugin' ] );

        } else {
            $this->init_plugin();
        }
    }

    /**
     * Execute if ERP main is installed
     *
     * @since 1.0.0
     * @since 1.1.0 Check if `WPERP_WOOCOMMERCE_VERSION` is all ready defined
     *
     * @return void
     */
    public function init_plugin() {
        if ( defined( 'WPERP_WOOCOMMERCE_VERSION' ) || ! defined( 'WC_VERSION' ) ) {
            return;
        }

        $this->define_constants();
        $this->includes();
        $this->init_classes();
        $this->init_actions();
        $this->init_filters();
    }

    /**
     * Define Add-on constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'WPERP_WOOCOMMERCE_VERSION', $this->version );
        define( 'WPERP_WOOCOMMERCE_FILE', __FILE__ );
        define( 'WPERP_WOOCOMMERCE_PATH', dirname( WPERP_WOOCOMMERCE_FILE ) );
        define( 'WPERP_WOOCOMMERCE_INCLUDES', WPERP_WOOCOMMERCE_PATH . '/includes' );
        define( 'WPERP_WOOCOMMERCE_URL', plugins_url( '', WPERP_WOOCOMMERCE_FILE ) );
        define( 'WPERP_WOOCOMMERCE_ASSETS', WPERP_WOOCOMMERCE_URL . '/assets' );
    }

    /**
     * Include the required files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/model/order_product.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/model/product_order.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/customer.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/settings.php';
        include_once WPERP_WOOCOMMERCE_INCLUDES . '/subscription.php';

        if ( version_compare( WC_VERSION , '3.0', '>=' ) ) {
            if ( version_compare( WPERP_VERSION , '1.5.0', '>=' ) ) {
                include_once WPERP_WOOCOMMERCE_INCLUDES . '/accounting-new.php';
            } else {
                include_once WPERP_WOOCOMMERCE_INCLUDES . '/accounting.php';
            }

            include_once WPERP_WOOCOMMERCE_INCLUDES . '/functions.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/orders.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/segment.php';

        } else {
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/accounting.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/functions.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/orders.php';
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/deprecated/segment.php';
        }
    }

    /**
     * Instantiate classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_classes() {
        if ( is_admin() && class_exists( '\WeDevs\ERP\License' ) ) {
            new \WeDevs\ERP\License( __FILE__, 'WooCommerce Integration', $this->version, 'weDevs' );
        }

        if ( defined('WP_CLI') && WP_CLI ) {
            include_once WPERP_WOOCOMMERCE_INCLUDES . '/cli/commands.php';
            \WP_CLI::add_command( 'erpwc', '\WeDevs\ERP\WooCommerce\CLI\Commands' );
        }

        WeDevs\ERP\WooCommerce\Order::init();
        WeDevs\ERP\WooCommerce\Customer::init();
        WeDevs\ERP\WooCommerce\Segment::init();
        new \WeDevs\ERP\WooCommerce\Subscription();
    }

    /**
     * Initializes action hooks
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function init_actions() {
        add_action( 'init', [ $this, 'localization_setup' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Initializes action filters
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function init_filters() {
        // Settings page filter
        add_filter( 'erp_settings_pages', [ $this, 'add_settings_page' ] );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [ $this, 'plugin_action_links' ] );
    }

    /**
     * Add action links
     *
     * @param $links
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=erp-settings&tab=erp-woocommerce' ) . '">' . __( 'Settings', 'erp-woocommerce' ) . '</a>';
        return $links;
    }

    /**
     * Initialize plugin for localization
     *
     * @since 1.0.0
     *
     * @uses load_plugin_textdomain()
     *
     * @return void
     */
    public function localization_setup() {
        load_plugin_textdomain( 'erp-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Register all styles and scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'erp-wc-style', WPERP_WOOCOMMERCE_ASSETS . '/css/admin.css' );
        wp_enqueue_script( 'erp-wc-script', WPERP_WOOCOMMERCE_ASSETS . '/js/erp-wc.js', ['jquery'], false, true );

        wp_localize_script( 'erp-wc-script', 'erpWC', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'erp-wc-nonce' )
        ) );
    }

    /**
     * Register HR settings page
     *
     * @since 1.0.0
     *
     * @param array
     */
    public function add_settings_page( $settings = [] ) {
        $settings[] = new \WeDevs\ERP\WooCommerce\WooCommerce_Settings();
        return $settings;
    }

    /**
     * Create table schema
     *
     * @since 1.0.0
     *
     * @return void
     **/
    public function create_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( !empty( $wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( !empty( $wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        $table_schema = [
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_wc_orders` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `people_id` bigint(20) DEFAULT NULL,
              `order_id` bigint(20) DEFAULT NULL,
              `order_status` varchar(11) DEFAULT NULL,
              `order_date` datetime DEFAULT NULL,
              `order_total` decimal(13,2) DEFAULT NULL,
              `accounting` tinyint(4) DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `people_id` (`people_id`),
              KEY `order_id` (`order_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_wc_order_product` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` bigint(20) DEFAULT NULL,
              `product_id` bigint(20) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `order_id` (`order_id`),
              KEY `product_id` (`product_id`)
            ) $collate;"
        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }
}

$erp_woocommerce = WeDevs_ERP_WooCommerce::init();
