<?php
/**
 * Plugin Name: WP ERP - Email Campaign
 * Description: Email Campaign add-on for WP ERP - CRM Module
 * Plugin URI: http://wperp.com/downloads/erp-email-campaign
 * Author: weDevs
 * Author URI: http://wedevs.com
 * Version: 1.1.0
 * License: GPL2
 * Text Domain: erp-email-campaign
 * Domain Path: languages
 *
 * Copyright (c) 2016 Tareq Hasan (email: info@wedevs.com). All rights reserved.
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
 * Email Campaign plugin main class
 */
class WeDevs_ERP_CRM_Email_Campaign {

    /**
     * Add-on Version
     *
     * @var string
     */
    public $version = '1.1.0';

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @return object
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
     * @return void
     */
    public function __construct() {
        // register constants
        $this->define_constants();

        // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );

        // plugin not installed - notice
        add_action( 'admin_notices', [ $this, 'admin_notice' ] );

        // on ERP CRM loaded hook
        add_action( 'erp_crm_loaded', [ $this, 'erp_crm_loaded' ] );

        // include install class
        if ( is_admin() || defined( 'WP_CLI' ) && WP_CLI ) {
            include WPERP_EMAIL_CAMPAIGN_INCLUDES . '/install/class-install.php';
        }
    }

    /**
     * Define Add-on constants
     *
     * @since 1.0.0
     * @since 1.1.0 Added condition to ensure register constants once.
     *
     * @return void
     */
    private function define_constants() {
        if ( defined( 'WPERP_EMAIL_CAMPAIGN_VERSION' ) ) {
            return;
        }

        define( 'WPERP_EMAIL_CAMPAIGN_VERSION', $this->version );
        define( 'WPERP_EMAIL_CAMPAIGN_FILE', __FILE__ );
        define( 'WPERP_EMAIL_CAMPAIGN_PATH', dirname( WPERP_EMAIL_CAMPAIGN_FILE ) );
        define( 'WPERP_EMAIL_CAMPAIGN_INCLUDES', WPERP_EMAIL_CAMPAIGN_PATH . '/includes' );
        define( 'WPERP_EMAIL_CAMPAIGN_URL', plugins_url( '', WPERP_EMAIL_CAMPAIGN_FILE ) );
        define( 'WPERP_EMAIL_CAMPAIGN_ASSETS', WPERP_EMAIL_CAMPAIGN_URL . '/assets' );
        define( 'WPERP_EMAIL_CAMPAIGN_VIEWS', WPERP_EMAIL_CAMPAIGN_PATH . '/views' );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'erp-email-campaign', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages/' );
    }

    /**
     * Display an error message if WP ERP is not active
     *
     * @return void
     */
    public function admin_notice() {
        if ( !class_exists( 'WeDevs_ERP' ) ) {
            printf(
                '%s'. __( '<strong>Error:</strong> <a href="%s">CRM</a> Plugin is required to use Email Campaign plugin.', 'erp-email-campaign' ) . '%s',
                '<div class="message error"><p>',
                'https://wordpress.org/plugins/erp/',
                '</p></div>'
            );
        }
    }

    /**
     * Executes if CRM is installed
     *
     * @since 1.0.0
     * @since 1.1.0 Remove `define_constants` method call
     *
     * @return void
     */
    public function erp_crm_loaded() {
        if ( is_admin() && class_exists( '\WeDevs\ERP\License' ) ) {
            new \WeDevs\ERP\License( __FILE__, 'Email Campaign', $this->version, 'weDevs' );
        }

        $this->includes();
    }

    /**
     * Include required files
     *
     * @return void
     */
    public function includes() {
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/functions.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/email-campaign.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/campaign-list.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/templates.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/url.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/url-stat.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/open-stat.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/people.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/people-queue.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/models/events.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-capabilities.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-campaign-posts.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-templates.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-email-campaign.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-single-campaign.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-events.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-frontend.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-task.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-hooks.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-logger.php';

        // admin functionalities
        add_action( 'init', function () {
            if ( current_user_can( 'manage_erp_email_campaign' ) ) {
                include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-admin.php';
            }
        } );

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/cli/class-cli.php';
        }
    }

}

WeDevs_ERP_CRM_Email_Campaign::init();
