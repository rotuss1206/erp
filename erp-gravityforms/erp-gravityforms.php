<?php
/**
 * Plugin Name: WP ERP - Gravity Forms Integration
 * Description: Gravity Forms integration for WP ERP
 * Plugin URI: https://wperp.com/downloads/crm-gravity-forms/
 * Author: weDevs
 * Author URI: https://wedevs.com
 * Version: 1.1.0
 * License: GPL2
 * Text Domain: erp-gravityforms
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

class WeDevs_ERP_CRM_Gravity_Forms {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.1.0';

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @return object class instance
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
     */
    public function __construct() {
        // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );

        // on ERP CRM loaded hook
        add_action( 'erp_crm_loaded', [ $this, 'erp_crm_loaded' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'erp-gravityforms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Executes if CRM is installed
     *
     * @return boolean/void
     */
    public function erp_crm_loaded() {
        if ( is_admin() && class_exists( '\WeDevs\ERP\License' ) ) {
            new \WeDevs\ERP\License( __FILE__, 'Gravity Forms Sync', $this->version, 'weDevs' );
        }

        include_once dirname( __FILE__ ) . '/gravityforms.php';
    }

}

WeDevs_ERP_CRM_Gravity_Forms::init();
