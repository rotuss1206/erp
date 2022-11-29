<?php
/*
Plugin Name: WP ERP - Document Manager
Plugin URI: http://wperp.com/downloads/document-manager/
Description: Manage your employee and company documents.
Version: 1.2.0
Author: weDevs
Author URI: https://wedevs.com
Text Domain: erp-document
Domain Path: languages
License: GPL2
*/

/**
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
if ( !defined('ABSPATH') ) exit;

/**
 * Base_Plugin class
 *
 * @class Base_Plugin The class that holds the entire Base_Plugin plugin
 */
class WeDevs_ERP_Document {

    /**
     * Constructor for the Base_Plugin class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */

    /* plugin version 0.1
    *
    * @var string
    */
    public $version = '1.2.0';


    public function __construct() {
        $this->define_constants();

        require_once WPERP_DOC_INCLUDES . '/class-doc-install.php';

        add_action( 'erp_hrm_loaded', array( $this, 'init_plugin' ) );
        add_action( 'erp_crm_loaded', array( $this, 'init_plugin' ) );
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {
        $this->includes();

        $this->init_classes();

        $this->actions();
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new WeDevs_ERP_Document();
        }

        return $instance;
    }

    /**
     * check php version is supported
     *
     * @return bool
     */
    public function is_supported_php() {
        if ( version_compare(PHP_VERSION, '5.4.0', '<=') ) {
            return false;
        }

        return true;
    }

    /**
     * define the plugin constant
     *
     * @return void
     */
    public function define_constants() {
        define('WPERP_DOC', $this->version);
        define('WPERP_DOC_FILE', __FILE__);
        define('WPERP_DOC_PATH', dirname(WPERP_DOC_FILE));
        define('WPERP_DOC_INCLUDES', WPERP_DOC_PATH . '/includes');
        define('WPERP_DOC_MODULES', WPERP_DOC_PATH . '/modules');
        define('WPERP_DOC_URL', plugins_url('', WPERP_DOC_FILE));
        define('WPERP_DOC_ASSETS', WPERP_DOC_URL . '/assets');
        define('WPERP_DOC_VIEWS', WPERP_DOC_INCLUDES . '/admin/views');
        define('WPERP_DOC_JS_TMPL', WPERP_DOC_VIEWS . '/js-templates');
    }

    /**
     * including necessary files
     *
     * @return void
     */
    public function includes() {
        require_once WPERP_DOC_INCLUDES . '/class-doc-install.php';
        require_once WPERP_DOC_INCLUDES . '/functions-file.php';
        require_once WPERP_DOC_INCLUDES . '/class-doc-ajax.php';
        require_once WPERP_DOC_INCLUDES . '/class-form-handler.php';
        require_once WPERP_DOC_INCLUDES . '/class-doc-log.php';

        if ( !class_exists('WP_List_Table') ) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        if ( !$this->is_supported_php() ) {
            return;
        }
    }

    /**
     * function objective
     *
     * @return
     */
    public function init_classes() {
        new \WeDevs\ERP\ERP_Document\Ajax_Handler();
        new \WeDevs\ERP\ERP_Document\Doc_Log();

        if ( is_admin() && class_exists( '\WeDevs\ERP\License' ) ) {
            new \WeDevs\ERP\License( __FILE__, 'Document Manager', $this->version, 'weDevs' );
        }
    }

    /**
     * function objective
     *
     * @return
     */
    public function actions() {
        add_action('init', array($this, 'localization_setup'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'set_document_menu'));
        add_action('erp_hr_employee_single_tabs', array($this, 'erp_hr_rec_employee_document_callback'));
        add_action('admin_footer', array($this, 'admin_doc_js_templates'));
        add_action( 'erp_hr_employee_single_bottom', [ $this, 'frontend_enqueue_scripts'] );
        // CRM Document
        add_filter( 'erp_crm_customer_feeds_nav', [ $this, 'erp_cr_company_document_tab' ] );
        add_action( 'erp_crm_feeds_nav_content', [ $this, 'erp_cr_company_document_tab_content' ] );
    }

    /**
     * Print JS templates in footer
     *
     * @return void
     */
    public function admin_doc_js_templates() {
        global $current_screen;
        if ( version_compare( WPERP_VERSION,  '1.4.0', '<' ) ) {
            switch ($current_screen->base) {
                case 'hr-management_page_erp-hr-documents':
                    erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                    break;
                case 'hr-management_page_erp-hr-employee':
                    erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                    break;
                case 'crm_page_erp-sales-customers':
                    erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                    break;
                case 'crm_page_erp-sales-companies':
                   erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                    break;
                default:
                    # code...
                    break;
            }
        }

        $section =  isset( $_GET['section'] ) ? $_GET['section'] : '';

        switch ( $section ) {
            case 'documents':
                erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                break;
            case 'employee':
                erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                break;
            case 'contacts':
                erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                break;
            case 'companies':
                erp_get_js_template(WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template');
                break;
            default:
                # code..
                break;
        }
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain('erp-document', false, dirname(plugin_basename(__FILE__)) . '/i18n/languages/');
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @since 1.0.0
     * @since 1.1.1 Load scripts in specific pages
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts($hook) {
        $hook = str_replace( sanitize_title( __( 'HR Management', 'erp' ) ) , 'hr-management', $hook );

        $document_pages = [
            'hr-management_page_erp-hr-documents',
            'hr-management_page_erp-hr-employee',
            'hr-management_page_erp-hr-my-profile',
            'crm_page_erp-sales-customers',
            'crm_page_erp-sales-companies',
            'wp-erp_page_erp-hr',
            'wp-erp_page_erp-crm'
        ];

        if ( ! in_array( $hook , $document_pages ) ) {
            return;
        }

        wp_enqueue_style('erp-doc-style', WPERP_DOC_ASSETS . '/css/stylesheet.css');
        wp_enqueue_script( 'plupload-handlers');
        wp_enqueue_script( 'erp-vuejs' );
        wp_enqueue_script( 'erp-document-upload', WPERP_DOC_ASSETS . '/js/erp-document-upload.js', array('jquery', 'plupload-handlers'), false, true);
        wp_enqueue_script( 'erp-document', WPERP_DOC_ASSETS . '/js/erp-document.js', array('jquery'), false, true);
        wp_enqueue_script( 'erp-document-entry', WPERP_DOC_ASSETS . '/js/erp-document-entry.js', array('jquery'), false, true);

        $upload_size = 20000 * 1024 * 1024;
        $localize_scripts = [
            'nonce'    => wp_create_nonce('doc_form_builder_nonce'),
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'move'     => __('Move', 'erp-document'),
            'moveto'   => __('Move to', 'erp-document'),
            'plupload' => array(
                'browse_button'       => 'doc-upload-pickfiles',
                'container'           => 'doc-upload-container',
                'max_file_size'       => $upload_size . 'b',
                'url'                 => admin_url('admin-ajax.php') . '?action=file_dir_ajax_upload&_wpnonce=' . wp_create_nonce('file_upload_nonce'),
                'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
                'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                'filters'             => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
                'resize'              => array('width' => (int)get_option('large_size_w'), 'height' => (int)get_option('large_size_h'), 'quality' => 100)
            ),

            'current_user_id'      => get_current_user_id(),
            'isAdmin'              => current_user_can( 'manage_options' ),
            'isCrmManager'         => current_user_can( 'erp_crm_manager' ),
            'isAgent'              => current_user_can( 'erp_crm_agent' ),
        ];

        wp_localize_script('erp-document', 'wpErpDoc', $localize_scripts);
    }

    /**
     * set recuritment menu
     *
     * @return
     */
    public function set_document_menu() {
        $profile_id = !empty($_REQUEST['id'])? intval($_REQUEST['id']): 0;
        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            add_submenu_page('erp-hr', __('Documents', 'erp-document'), __('Documents', 'erp-document'), 'erp_hr_manager', 'erp-hr-documents', array($this, 'erp_hr_company_document_tab'));
        } else {
            erp_add_menu( 'hr', array(
                'title'         =>  __( 'Documents', 'erp-document' ),
                'slug'          =>  'documents',
                'capability'    =>  'erp_hr_manager',
                'callback'      =>  [ $this, 'erp_hr_company_document_tab' ],
                'position'      =>  35,
            ) );
        }
    }

    /**
     * Check persion
     *
     * @param  integer $user_id
     *
     * @return booliean
     */
    public function permited_user ( $user_id ) {
        $current_user_id = get_current_user_id();

        if ( ! current_user_can( 'erp_edit_document', $user_id ) &&  $current_user_id !== $user_id ) {
            return false;
        }

        return true;
    }

    /**
     * Include employee profile tab
     *
     * @param  array  $tabs
     *
     * @return array
     */
    public function erp_hr_rec_employee_document_callback( $tabs ) {
        $profile_id = !empty( $_REQUEST['id'] )? intval( $_REQUEST['id'] ): get_current_user_id();

        if ( ! $this->permited_user( $profile_id ) ) {
            return $tabs;
        }


        $tabs['document'] = array(
            'title'    => __( 'Documents', 'erp-document' ),
            'callback' => array( $this, 'erp_hr_employee_document_tab' )
        );

        return $tabs;
    }

    /**
     * Include employee file system page
     *
     * @return void
     */
    public function erp_hr_employee_document_tab() {
        require_once WPERP_DOC_VIEWS . '/view-file-system-list.php';
    }

    /**
     * Include company file system page
     *
     * @return void
     */
    public function erp_hr_company_document_tab() {
        require_once WPERP_DOC_VIEWS . '/view-company-file-system-list.php';
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function frontend_enqueue_scripts() {

        $this->enqueue_scripts('hr-management_page_erp-hr-employee');
        erp_get_js_template( WPERP_DOC_JS_TMPL . '/dir-tree.php', 'erp-doc-tree-template' );

    }

    /**
     * CRM Document
     */

    /**
     * Include company file system tab
     *
     * @return void
     */
    public function erp_cr_company_document_tab( $tabs ) {
        $tabs['document']   = array(
            'title' => __( 'Documents', 'erp-document' ),
            'icon'  => '<i class="fa fa-file-text-o"></i>'
        );

        return $tabs;
    }

    /**
     * Include company file system page
     *
     * @return void
     */
    public function erp_cr_company_document_tab_content() {
        require_once WPERP_DOC_VIEWS . '/crm-document.php';
    }

}

$WeDevs_ERP_Document = WeDevs_ERP_Document::init();
