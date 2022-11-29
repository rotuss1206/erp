<?php
/**
 * Plugin Name: WP ERP - Awesome Support
 * Plugin URI:  http://wperp.com
 * Description: WP ERP and Awesome Support integration.
 * Version:     1.0.0
 * Author:      WPERP
 * Author URI:  http://wperp.com
 * Donate link: http://wperp.com
 * License:     GPLv2+
 * Text Domain: erp-awesome-support
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2017 WPERP (email : support@wperp.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main initiation class
 *
 * @since 1.0.0
 */
class ERP_Awesome_Support {

    /**
     * Add-on Version
     *
     * @since 1.0.0
     * @var  string
     */
    public $version = '1.0.0';

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it does't find one, creates it.
     *
     * @since 1.0.0
     *
     * @return object Class instance
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );

        // on activate plugin register hook
        register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        //after erp load
        add_action( 'erp_crm_loaded', [ $this, 'init_actions' ] );
    }

    /**
     * Fire on activate
     *
     * @since 1.0.0
     */
    public function activate() {
        if ( ! class_exists( 'WeDevs_ERP' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( sprintf(
                __( 'You need to install %s in order to use %s', 'erp-edd' ),
                '<a href="https://wordpress.org/plugins/erp/" target="_blank"><strong>WP ERP</strong></a>',
                '<strong>WP ERP - Awesome Support</strong>'
            ) );
        }

        if ( ! class_exists( 'Awesome_Support' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( sprintf(
                __( 'You need to install %s in order to use %s', 'erp-edd' ),
                '<a href="https://wordpress.org/plugins/awesome-support/" target="_blank"><strong>Awesome Support</strong></a>',
                '<strong>WP ERP - Awesome Support</strong>'
            ) );
        }
    }

    /**
     * Initialize plugin for localization
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function localization_setup() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'erp_awesome_support' );
        load_textdomain( 'erp-awesome-support', WP_LANG_DIR . '/erp-awesome-support/erp-awesome-support-' . $locale . '.mo' );
        load_plugin_textdomain( 'erp-awesome-support', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Define constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPERP_AS_VERSION', $this->version );
        define( 'WPERP_AS_FILE', __FILE__ );
        define( 'WPERP_AS_PATH', dirname( WPERP_AS_FILE ) );
        define( 'WPERP_AS_INCLUDES', WPERP_AS_PATH . '/includes' );
        define( 'WPERP_AS_URL', plugins_url( '', WPERP_AS_FILE ) );
        define( 'WPERP_AS_ASSETS', WPERP_AS_URL . '/assets' );
        define( 'WPERP_AS_VIEWS', WPERP_AS_PATH . '/views' );
        define( 'WPERP_AS_TEMPLATES_DIR', WPERP_AS_PATH . '/templates' );
    }

    /**
     * Include required files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require WPERP_AS_INCLUDES . '/functions.php';
        require WPERP_AS_INCLUDES . '/class-settings.php';
        require WPERP_AS_INCLUDES . '/class-import.php';
        require WPERP_AS_INCLUDES . '/class-widget.php';
    }

    /**
     * Initialize WordPress action hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_actions() {
        add_action( 'init', [ $this, 'instantiate' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'load_assets' ] );
    }

    /**
     * Instantiate classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function instantiate() {
        new \WeDevs\ERP\Awesome_Support\Settings();
        new \WeDevs\ERP\Awesome_Support\Import();
        new \WeDevs\ERP\Awesome_Support\Widget();

        if ( is_admin() && class_exists( '\WeDevs\ERP\License' ) ) {
            new \WeDevs\ERP\License( __FILE__, 'Awesome Support Sync', $this->version, 'weDevs' );
        }
    }

    /**
     * Add all the assets required by the plugin
     *
     * @since 1.0.0
     *
     * @return void
     */
    function load_assets() {
        wp_register_style( 'erp-awesome-support', WPERP_AS_ASSETS . '/css/erp-awesome-support.css', [], date( 'i' ) );
//		wp_register_script('erp-awesome-support', WPERP_AS_ASSETS.'/js/erp-awesome-support.js', ['jquery'], date('i'), true);
//		wp_localize_script('erp-awesome-support', 'jsobject', ['ajaxurl' => admin_url( 'admin-ajax.php' )]);
        wp_enqueue_style( 'erp-awesome-support' );
//		wp_enqueue_script('erp-awesome-support');
    }

}

// init our class
$GLOBALS['ERP_Awesome_Support'] = new ERP_Awesome_Support();

/**
 * Grab the $ERP_Awesome_Support object and return it
 */
function erp_awesome_support() {
    global $ERP_Awesome_Support;

    return $ERP_Awesome_Support;
}
