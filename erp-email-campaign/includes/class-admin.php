<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Class responsible for admin panel functionalities
 */
class Admin {

    use Hooker;

    /**
     * Send email intervals in minutes
     *
     * @var array
     */
    private $send_email_intervals = [
        1   => 'every minute',
        2   => 'every 2 minutes',
        5   => 'every 5 minutes',
        10  => 'every 10 minutes',
        15  => 'every 15 minutes',
        30  => 'every 30 minutes',
        60  => 'every hour',
        120 => 'every 2 hours',
    ];

    /**
     * Action taken after an email bounces
     *
     * @var array
     */
    private $after_bounce_actions = [];

    /**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     */
    public function __construct() {
        $this->after_bounce_actions = [
            'do_nothing'            => __( 'Do nothing', 'erp-email-campaign' ),
            'trash_user'            => __( 'Trash User', 'erp-email-campaign' ),
            'unsubscribe'           => __( 'Unsubscribe User', 'erp-email-campaign' ),
            'unsub_add_to_list'     => __( 'Unsubscribe User and add to the list...', 'erp-email-campaign' )
        ];
        $this->includes();
        $this->hooks();
    }

    /**
     * Include the required files
     *
     * @since 1.0.0
     * @since 1.1.0 Include class-updates.php
     *
     * @return void
     */
    private function includes() {
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-email-campaign-editor.php';
        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-email-campaign-ajax.php';

        // updater class
        if ( is_admin() ) {
            include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/install/class-updates.php';
            new \WeDevs\ERP\CRM\EmailCampaign\Updates();
        }
    }

    /**
     * Initializes action hooks to ERP
     *
     * @return void
     */
    private function hooks() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
        $this->action( 'admin_menu', 'add_menu' );

        $this->action( 'load-email-campaign_page_erp-email-campaign-editor', 'editor_page_on_load' );
        $this->action( 'load-toplevel_page_erp-email-campaign', 'list_table_form_handler' );
        $this->action( 'load-wp-erp_page_erp-crm', 'list_table_form_handler' );

        $this->action( 'admin_notices', 'admin_notices' );
        $this->action( 'removable_query_args', 'removable_query_args' );

        // settings
        $this->action( 'erp_settings_email_sections', 'email_bounce_settings' );
        $this->action( 'erp_settings_email_section_fields', 'email_bounce_settings_fields', 10, 2 );
        $this->action( 'erp_admin_field_bounce_status', 'bounce_status' );
        $this->action( 'erp_after_save_settings', 'update_bounce_schedule' );

        $this->action( 'erp_settings_crm_sections', 'crm_sections_email_campaign' );
        $this->action( 'erp_settings_crm_section_fields', 'crm_sections_email_campaign_fields', 10, 2 );
        $this->action( 'erp_admin_field_send_interval', 'send_interval' );
        $this->action( 'erp_after_save_settings', 'save_admin_settings' );
        $this->action( 'erp_admin_field_after_bounce_action', 'after_bounce_action' );
        $this->action( 'erp_after_save_settings', 'erp_update_option_after_bounce_action' );

        // people activities log
        $this->action( 'erp_crm_load_vue_js_template', 'erp_crm_load_vue_js_template' );
        $this->action( 'erp_crm_load_contact_vue_scripts', 'contact_vue_scripts' );
        $this->filter( 'erp_crm_customer_feeds_nav', 'erp_crm_customer_feeds_nav_for_activities_page' );
    }

    /**
     * Register admin scripts
     *
     * @return void
     */
    public function admin_scripts( $hook_suffix ) {

        if ( version_compare( WPERP_VERSION, "1.4.0", '>=' ) ) {
            $this->load_admin_scripts( $hook_suffix );
            return;
        }
        $ecampGlobal = [
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'erp-email-campaign' ),
            'debug'     => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false,
            'date'      => [
                'format'        => ecamp_js_date_format(),
                'placeholder'   => erp_format_date( 'now' )
            ],
            'time'      => [
                'format'        => get_option( 'time_format', 'g:i a' ),
                'placeholder'   => date( get_option( 'time_format', 'g:i a' ), current_time( 'timestamp' ) )
            ],
        ];

        // I want to use one template for both create and edit campaign. Problem is,
        // when we'll edit, "Add New" in main menu will be highlighted. So, I'll edit a
        // campaign in top level erp-email-campaign and new will be in email-campaign-editor.
        $is_customizer_page = (
            'toplevel_page_erp-email-campaign' === $hook_suffix && !empty( $_GET['action'] ) && 'edit' === $_GET['action']
            || 'email-campaign_page_erp-email-campaign-editor' === $hook_suffix || 'wp-erp_page_erp-crm' == $hook_suffix
        );
        $section = isset( $_GET['section'] ) ? $_GET['section'] : '';
        // editor scripts
        if ( $is_customizer_page ) {
            // styles
            wp_enqueue_style( 'tiny-mce', site_url( '/wp-includes/css/editor.css' ), [], WPERP_EMAIL_CAMPAIGN_VERSION );
            wp_enqueue_style( 'erp-email-campaign-template-style', WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/email-template-styles.css', [], WPERP_EMAIL_CAMPAIGN_VERSION );
            wp_enqueue_style(
                'erp-email-campaign-editor',
                WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign-editor.css', [
                    'wp-color-picker', 'erp-styles', 'erp-timepicker',
                    'erp-fontawesome', 'erp-sweetalert', 'erp-nprogress',
                    'tiny-mce', 'erp-email-campaign-template-style'
                ], WPERP_EMAIL_CAMPAIGN_VERSION
            );

            // scripts
            wp_enqueue_script( 'tiny-mce', site_url( '/wp-includes/js/tinymce/tinymce.min.js' ), [] );
            wp_enqueue_script( 'tiny-mce-code', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/tinymce/plugins/code/plugin.min.js', [ 'tiny-mce' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'tiny-mce-hr', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/tinymce/plugins/hr/plugin.min.js', [ 'tiny-mce' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'tiny-mce-wpeditimage', site_url( '/wp-includes/js/tinymce/plugins/wpeditimage/plugin.min.js' ), [ 'tiny-mce' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'erp-sortable', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/Sortable.js', [], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script(
                'erp-email-campaign-editor',
                WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/erp-email-campaign-editor.js', [
                    'erp-vuejs', 'jquery', 'jquery-ui-datepicker', 'wp-color-picker',
                    'erp-timepicker', 'erp-sortable', 'erp-tiptip', 'tiny-mce', 'tiny-mce-code', 'tiny-mce-hr',
                    'erp-select2', 'erp-sweetalert', 'erp-nprogress'
                ], WPERP_EMAIL_CAMPAIGN_VERSION, true
            );

            wp_localize_script( 'erp-email-campaign-editor', 'ecampGlobal', $ecampGlobal );


            wp_enqueue_style( 'erp-email-campaign-vendor', WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign-vendor.css', [], WPERP_EMAIL_CAMPAIGN_VERSION );

            wp_enqueue_style(
                'erp-email-campaign',
                WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign.css',
                [ 'erp-styles', 'erp-nprogress', 'erp-flotchart-valuelabel-css', 'erp-email-campaign-vendor' ],
                WPERP_EMAIL_CAMPAIGN_VERSION
            );

            wp_enqueue_script( 'erp-vue-table', WPERP_CRM_ASSETS . "/js/vue-table.js", [ 'erp-vuejs', 'jquery' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'erp-email-campaign-vendor', WPERP_EMAIL_CAMPAIGN_ASSETS . "/js/erp-email-campaign-vendor.js", [ 'jquery' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_localize_script( 'erp-vue-table', 'wpVueTable', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wp-erp-vue-table' )
            ] );

            wp_enqueue_script( 'erp-email-campaign', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/erp-email-campaign.js', [ 'jquery', 'erp-vuejs', 'erp-nprogress', 'erp-flotchart', 'erp-flotchart-pie', 'erp-tiptip', 'erp-email-campaign-vendor', 'erp-momentjs' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );

            // localized vars for the single campaign page
            $ecampGlobal['searchPlaceHolder'] = __( 'Search Contact', 'erp-email-campaign' );

            if ( !empty( $_GET['action'] ) && 'view' === $_GET['action'] && !empty( $_GET['id'] ) ) {
                erp_email_campaign()->die_if_invalid_campaign( $_GET['id'] );

                // top nav filter and group filters for campaign subscribers list table
                $ecampGlobal['topNavFilter'] = erp_email_campaign()->get_campaign_subscriber_statuses( $_GET['id'] );

                $groups = erp_email_campaign()->get_campaign_contact_groups( $_GET['id'] );

                $groups = array_map( function ( $group ) {
                    return [
                        'id' => $group[0]->id,
                        'text' => $group[0]->name
                    ];

                } , (array) $groups );

                array_unshift( $groups, [ 'id' => 0, 'text' => __( 'Filter by Contact Group', 'erp-email-campaign' ) ] );

                $ecampGlobal['groupFilter'] = $groups;

                // i18n strings
                $ecampGlobal['i18n'] = [
                    'name'              => __( 'Name', 'erp-email-campaign' ),
                    'email'             => __( 'Email Status', 'erp-email-campaign' ),
                    'lists'             => __( 'Lists', 'erp-email-campaign' ),
                    'subs_status'       => __( 'Subscription Status', 'erp-email-campaign' ),
                    'opened'            => __( 'Opened', 'erp-email-campaign' ),
                    'confirmDuplicate'  => __( 'Are you sure you want to duplicate this campaign?', 'erp-email-campaign' )
                ];

                $ecampGlobal['campaignId'] = $_GET['id'];
            }

            wp_localize_script( 'erp-email-campaign', 'ecampGlobal', $ecampGlobal );

        } else if ( 'erp-settings_page_erp-settings' === $hook_suffix ) {
            wp_enqueue_style( 'erp-email-campaign-settings', WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign-settings.css', [], WPERP_EMAIL_CAMPAIGN_VERSION );
            wp_enqueue_script( 'erp-email-campaign-settings', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/erp-email-campaign-settings.js', [ 'jquery', 'erp-vuejs' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );

            $this->erp_update_option_after_bounce_action();

            $options = get_option( 'erp_settings_erp-crm_email_campaign', [] );

            $settings = [
                'afterBounceActions' => $this->after_bounce_actions,
                'contact_groups' => \WeDevs\ERP\CRM\Models\ContactGroup::select( 'id', 'name' )->get()->toArray(),
                'selectedAction' => !empty( $options['after_bounce_action'] ) ? $options['after_bounce_action'] : 'do_nothing',
                'selectedList' => !empty( $options['contact_list'] ) ? absint( $options['contact_list'] ) : 0,
                'i18n' => [
                    'afterEmailBounces'     => __( 'After an email bounces', 'erp-email-campaign' ),
                    'selectAGroup'          => __( 'Select a contact group', 'erp-email-campaign' ),
                    'mustSelectAGroup'      => __( 'You must select a group.', 'erp-email-campaign' ),
                ],
            ];

            wp_localize_script( 'erp-email-campaign-settings', 'ecampGlobal', array_merge( $ecampGlobal, $settings ) );
        }

    }

    /**
     * @param $hook_suffix
     */
    public function load_admin_scripts( $hook_suffix ){
        $ecampGlobal = [
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'erp-email-campaign' ),
            'debug'     => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false,
            'date'      => [
                'format'        => ecamp_js_date_format(),
                'placeholder'   => erp_format_date( 'now' )
            ],
            'time'      => [
                'format'        => get_option( 'time_format', 'g:i a' ),
                'placeholder'   => date( get_option( 'time_format', 'g:i a' ), current_time( 'timestamp' ) )
            ],
        ];

        // I want to use one template for both create and edit campaign. Problem is,
        // when we'll edit, "Add New" in main menu will be highlighted. So, I'll edit a
        // campaign in top level erp-email-campaign and new will be in email-campaign-editor.
        $section = !empty( $_GET['section'] ) ? $_GET['section'] : false;
        // editor scripts
        if ( 'email-campaign' == $section ) {
            // styles
            wp_enqueue_style( 'tiny-mce', site_url( '/wp-includes/css/editor.css' ), [], WPERP_EMAIL_CAMPAIGN_VERSION );
            wp_enqueue_style( 'erp-email-campaign-template-style', WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/email-template-styles.css', [], WPERP_EMAIL_CAMPAIGN_VERSION );
            wp_enqueue_style(
                'erp-email-campaign-editor',
                WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign-editor.css', [
                'wp-color-picker', 'erp-styles', 'erp-timepicker',
                'erp-fontawesome', 'erp-sweetalert', 'erp-nprogress',
                'tiny-mce', 'erp-email-campaign-template-style'
            ], WPERP_EMAIL_CAMPAIGN_VERSION
            );

            // scripts
            wp_enqueue_script( 'tiny-mce', site_url( '/wp-includes/js/tinymce/tinymce.min.js' ), [] );
            wp_enqueue_script( 'tiny-mce-code', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/tinymce/plugins/code/plugin.min.js', [ 'tiny-mce' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'tiny-mce-hr', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/tinymce/plugins/hr/plugin.min.js', [ 'tiny-mce' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'tiny-mce-wpeditimage', site_url( '/wp-includes/js/tinymce/plugins/wpeditimage/plugin.min.js' ), [ 'tiny-mce' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'erp-sortable', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/Sortable.js', [], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script(
                'erp-email-campaign-editor',
                WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/erp-email-campaign-editor.js', [
                'erp-vuejs', 'jquery', 'jquery-ui-datepicker', 'wp-color-picker',
                'erp-timepicker', 'erp-sortable', 'erp-tiptip', 'tiny-mce', 'tiny-mce-code', 'tiny-mce-hr',
                'erp-select2', 'erp-sweetalert', 'erp-nprogress'
            ], WPERP_EMAIL_CAMPAIGN_VERSION, true
            );

            wp_localize_script( 'erp-email-campaign-editor', 'ecampGlobal', $ecampGlobal );


            wp_enqueue_style( 'erp-email-campaign-vendor', WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign-vendor.css', [], WPERP_EMAIL_CAMPAIGN_VERSION );

            wp_enqueue_style(
                'erp-email-campaign',
                WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign.css',
                [ 'erp-styles', 'erp-nprogress', 'erp-flotchart-valuelabel-css', 'erp-email-campaign-vendor' ],
                WPERP_EMAIL_CAMPAIGN_VERSION
            );

            wp_enqueue_script( 'erp-vue-table', WPERP_CRM_ASSETS . "/js/vue-table.js", [ 'erp-vuejs', 'jquery' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_enqueue_script( 'erp-email-campaign-vendor', WPERP_EMAIL_CAMPAIGN_ASSETS . "/js/erp-email-campaign-vendor.js", [ 'jquery' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
            wp_localize_script( 'erp-vue-table', 'wpVueTable', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wp-erp-vue-table' )
            ] );

            wp_enqueue_script( 'erp-email-campaign', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/erp-email-campaign.js', [ 'jquery', 'erp-vuejs', 'erp-nprogress', 'erp-flotchart', 'erp-flotchart-pie', 'erp-tiptip', 'erp-email-campaign-vendor', 'erp-momentjs' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );

            // localized vars for the single campaign page
            $ecampGlobal['searchPlaceHolder'] = __( 'Search Contact', 'erp-email-campaign' );

            if ( !empty( $_GET['action'] ) && 'view' === $_GET['action'] && !empty( $_GET['id'] ) ) {
                erp_email_campaign()->die_if_invalid_campaign( $_GET['id'] );

                // top nav filter and group filters for campaign subscribers list table
                $ecampGlobal['topNavFilter'] = erp_email_campaign()->get_campaign_subscriber_statuses( $_GET['id'] );

                $groups = erp_email_campaign()->get_campaign_contact_groups( $_GET['id'] );

                $groups = array_map( function ( $group ) {
                    return [
                        'id' => $group[0]->id,
                        'text' => $group[0]->name
                    ];

                } , (array) $groups );

                array_unshift( $groups, [ 'id' => 0, 'text' => __( 'Filter by Contact Group', 'erp-email-campaign' ) ] );

                $ecampGlobal['groupFilter'] = $groups;

                // i18n strings
                $ecampGlobal['i18n'] = [
                    'name'              => __( 'Name', 'erp-email-campaign' ),
                    'email'             => __( 'Email Status', 'erp-email-campaign' ),
                    'lists'             => __( 'Lists', 'erp-email-campaign' ),
                    'subs_status'       => __( 'Subscription Status', 'erp-email-campaign' ),
                    'opened'            => __( 'Opened', 'erp-email-campaign' ),
                    'confirmDuplicate'  => __( 'Are you sure you want to duplicate this campaign?', 'erp-email-campaign' )
                ];

                $ecampGlobal['campaignId'] = $_GET['id'];
            }

            wp_localize_script( 'erp-email-campaign', 'ecampGlobal', $ecampGlobal );

        } else if ( 'erp-settings_page_erp-settings' === $hook_suffix ) {
            wp_enqueue_style( 'erp-email-campaign-settings', WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign-settings.css', [], WPERP_EMAIL_CAMPAIGN_VERSION );
            wp_enqueue_script( 'erp-email-campaign-settings', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/erp-email-campaign-settings.js', [ 'jquery', 'erp-vuejs' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );

            $this->erp_update_option_after_bounce_action();

            $options = get_option( 'erp_settings_erp-crm_email_campaign', [] );

            $settings = [
                'afterBounceActions' => $this->after_bounce_actions,
                'contact_groups' => \WeDevs\ERP\CRM\Models\ContactGroup::select( 'id', 'name' )->get()->toArray(),
                'selectedAction' => !empty( $options['after_bounce_action'] ) ? $options['after_bounce_action'] : 'do_nothing',
                'selectedList' => !empty( $options['contact_list'] ) ? absint( $options['contact_list'] ) : 0,
                'i18n' => [
                    'afterEmailBounces'     => __( 'After an email bounces', 'erp-email-campaign' ),
                    'selectAGroup'          => __( 'Select a contact group', 'erp-email-campaign' ),
                    'mustSelectAGroup'      => __( 'You must select a group.', 'erp-email-campaign' ),
                ],
            ];

            wp_localize_script( 'erp-email-campaign-settings', 'ecampGlobal', array_merge( $ecampGlobal, $settings ) );
        }
    }
    /**
     * Add admin panel menu item
     *
     * @return void
     */
    public function add_menu() {
        // main plugin page
        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            add_menu_page( __( 'Email Campaign', 'erp-email-campaign' ), __( 'Email Campaign', 'erp-email-campaign' ), 'manage_erp_email_campaign', 'erp-email-campaign', [ $this, 'campaign_list_table' ], 'dashicons-email-alt' );

            add_submenu_page( 'erp-email-campaign', __( 'All Campaigns', 'erp-email-campaign' ), __( 'All Campaigns', 'erp-email-campaign' ), 'manage_erp_email_campaign', 'erp-email-campaign', [ $this, 'campaign_list_table' ] );
            add_submenu_page( 'erp-email-campaign', __( 'Add New', 'erp-email-campaign' ), __( 'Add New', 'erp-email-campaign' ), 'manage_erp_email_campaign', 'erp-email-campaign-editor', [ $this, 'campaign_editor_page' ] );
            add_submenu_page( 'erp-email-campaign', __( 'Settings', 'erp-email-campaign' ), __( 'Settings', 'erp-email-campaign' ), 'manage_options', 'admin.php?page=erp-settings&tab=erp-crm&section=email_campaign' );
        } else {
            erp_add_menu( 'crm', array(
                'title'         =>  __( 'Email Campaign', 'erp-email-campaign' ),
                'slug'          =>  'email-campaign',
                'capability'    =>  'manage_erp_email_campaign',
                'callback'      =>  [ $this, 'campaign_list_table' ],
                'position'      =>  40
            ) );

            erp_add_submenu( 'crm', 'email-campaign', array(
                'title' =>  __( 'All Campaigns', 'erp-email-campaign' ),
                'slug'          =>  'all-email-campaign',
                'capability'    =>  'manage_erp_email_campaign',
                'callback'      =>  [ $this, 'campaign_list_table' ],
                'position'      =>  1
            ) );

            erp_add_submenu( 'crm', 'email-campaign', array(
                'title' =>  __( 'Add New', 'erp-email-campaign' ),
                'slug'          =>  'email-campaign-editor',
                'capability'    =>  'manage_erp_email_campaign',
                'callback'      =>  [ $this, 'campaign_editor_page' ],
                'position'      =>  5
            ) );

            erp_add_submenu( 'crm', 'email-campaign', array(
                'title' =>  __( 'Settings', 'erp-email-campaign' ),
                'slug'          =>  'erp-email-campaign',
                'capability'    =>  'manage_erp_email_campaign',
                'callback'      =>  [],
                'direct_link'   =>  admin_url( 'admin.php?page=erp-settings&tab=erp-crm&section=email_campaign' ),
                'position'      =>  10
            ) );
        }
    }

    /**
     * Campaign List Table
     *
     * @return void
     */
    public function campaign_list_table() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        switch ( $action ) {
            case 'view':
                $campaign = new Single_Campaign( $_GET['id'] );
                $email_stats = $campaign->get_email_stats_with_legends();
                $url_stats = $campaign->get_url_stats();
                $sent = $campaign->campaign->people->count();
                $total_people = $sent + $campaign->campaign->peopleQueue->count();

                $template = WPERP_EMAIL_CAMPAIGN_VIEWS . '/email-campaign-single-view.php';
                break;

            case 'edit':
                $campaign_id = !empty( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
                $template = WPERP_EMAIL_CAMPAIGN_VIEWS . '/email-campaign-edit.php';
                break;

            default:
                include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/class-email-campaign-list-table.php';
                $campaign_table = new Campaign_List_Table();
                $template = WPERP_EMAIL_CAMPAIGN_VIEWS . '/email-campaign-list.php';
                break;
        }

        $template = apply_filters( 'erp_email_campaign_template_list_table', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * The callback function that executes when submenu pages is loaded
     *
     * @return void
     */
    public function campaign_editor_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        switch ( $action ) {
            default:
                $campaign_id = !empty( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
                $template = WPERP_EMAIL_CAMPAIGN_VIEWS . '/email-campaign-edit.php';
                break;
        }

        $template = apply_filters( 'erp_email_campaign_template_editor', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Get the current action selected from the bulk actions dropdown.
     *
     * @return string|false The action name or False if no action was selected
     */
    public function current_action() {
        if ( isset( $_REQUEST['campaign_search'] ) && !empty( $_REQUEST['campaign_search'] ) )
            return 'campaign_search';

        if ( isset( $_REQUEST['filter_action'] ) && !empty( $_REQUEST['filter_action'] ) )
            return false;

        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return $_REQUEST['action'];

        if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
            return $_REQUEST['action2'];

        return false;
    }

    /**
     * Check if campaigns are exist before do bulk action
     *
     * @param int|array $ids
     * @param bool      $ignore_delete_check
     *
     * @return void
     */
    private function validate_campaigns_before_bulk_action( $ids, $ignore_delete_check = false ) {
        if ( !is_array( $ids ) ) {
            $ids = [ $ids ];
        }

        foreach ( $ids as $id ) {
            erp_email_campaign()->die_if_invalid_campaign( $id, $ignore_delete_check );
        }
    }

    /**
     * Handle when list table form is submitted
     *
     * @return void
     */
    public function list_table_form_handler() {
        $section = !empty($_GET['section']) ? $_GET['section'] : false;

        if ( $section != 'email-campaign' ) {
            return;
        }

        // this condition is for editor page
        if ( isset( $_GET['action'] ) && ( 'edit' === $_GET['action'] || 'view' === $_GET['action'] ) ) {
            $this->editor_page_on_load();
        }

        if ( !isset( $_REQUEST['_wpnonce'] ) || !isset( $_GET['page'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-campaigns' ) ) {
            return;
        }

        $action = $this->current_action();

        if ( $action ) {
            $redirect = remove_query_arg( [ '_wp_http_referer', '_wpnonce', 'campaign_search', 'id', 'action', 'action2' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );

            if ( !empty( $_REQUEST['status'] ) ) {
                $redirect = add_query_arg( 'status', $_REQUEST['status'], $redirect );
            }

            switch ( $action ) {

                case 'trash':

                    if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
                        $this->validate_campaigns_before_bulk_action( $_GET['id'] );

                        $delete_count = erp_email_campaign()->trash_campaigns( $_GET['id'] );

                        $redirect = add_query_arg( 'trashed', $delete_count, $redirect );
                    }

                    wp_safe_redirect( $redirect, 302 );
                    exit;

                case 'restore':

                    if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
                        $this->validate_campaigns_before_bulk_action( $_GET['id'], true );

                        erp_email_campaign()->restore_campaigns( $_GET['id'] );

                        /** This counting is wrong. restore_campaign() method should return the count **/
                        $restore_count = is_array( $_GET['id'] ) ? count( $_GET['id'] ) : 1;

                        $redirect = add_query_arg( 'untrashed', $restore_count, $redirect );
                    }

                    wp_safe_redirect( $redirect, 302 );
                    exit;

                case 'delete':

                    if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
                        erp_email_campaign()->die_if_invalid_campaign( $_GET['id'], true );

                        erp_email_campaign()->delete_campaigns( $_GET['id'] );

                        /** This counting is wrong. delete_campaigns() method should return the count **/
                        $delete_count = is_array( $_GET['id'] ) ? count( $_GET['id'] ) : 1;

                        $redirect = add_query_arg( 'deleted', $delete_count, $redirect );
                    }

                    wp_safe_redirect( $redirect, 302 );
                    exit;

                case 'duplicate':

                    if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
                        erp_email_campaign()->die_if_invalid_campaign( $_GET['id'], true );

                        $new_campaign = erp_email_campaign()->duplicate_campaign( $_GET['id'] );

                        $redirect = add_query_arg( 'duplicated', $new_campaign->id, $redirect );

                    }

                    wp_safe_redirect( $redirect, 302 );
                    exit;

                case 'campaign_search':

                    $redirect = remove_query_arg( [ 'action', 'action2' ], wp_unslash( $redirect ) );

                default:

                    wp_safe_redirect( $redirect, 302 );
                    exit;

            }
        }
    }

    /**
     * On load editor page hook
     *
     * @since 1.0.0
     * @since 1.1.0 Redirect to main listing page when no campaign found
     *
     * @return void
     */
    public function editor_page_on_load() {
        if ( !empty( $_GET['id'] ) && !empty( $_GET['action'] )  || isset( $_GET['sub-section'] ) && $_GET['sub-section'] == 'email-campaign-editor' ) {
            $campaign = erp_email_campaign()->get_campaign( $_GET['id'] );

            if ( empty( $campaign ) ) {
                wp_safe_redirect( ecamp_admin_url(), 302 );
                exit;
            }

            if (
                'edit' === $_GET['action'] &&
                 isset( erp_email_campaign()->statuses[ $campaign->status ] ) &&
                 !erp_email_campaign()->statuses[ $campaign->status ]['can_edit']
            ) {
                wp_safe_redirect( ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ), 302 );
                exit;
            }


            if (
                'view' === $_GET['action'] &&
                 isset( erp_email_campaign()->statuses[ $campaign->status ] ) &&
                 erp_email_campaign()->statuses[ $campaign->status ]['can_edit']
            ) {
                wp_safe_redirect( ecamp_admin_url( [ 'action' => 'edit', 'id' => $campaign->id ], 'erp-email-campaign' ), 302 );
                exit;
            }
        }
    }

    /**
     * Print notices for WordPress
     *
     * @param  string  $text
     * @param  string  $type
     *
     * @return void
     */
    public function display_notice( $text, $type = 'updated' ) {
        printf( '<div class="%s"><p>%s</p></div>', esc_attr( $type ), $text );
    }

    /**
     * Admin notices
     *
     * @return void
     */
    public function admin_notices() {
        if ( !empty( $_GET['page'] ) && 'erp-email-campaign' === $_GET['page'] ) {
            if ( !empty( $_GET['trashed'] ) ) {
                $notice = sprintf( _n( '%d campaign moved to the Trash.', '%d campaigns moved to the Trash.', $_GET['trashed'], 'erp-email-campaign' ), $_GET['trashed'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['untrashed'] ) ) {
                $notice = sprintf( _n( '%d campaign restored from the Trash.', '%d campaigns restored from the Trash.', $_GET['untrashed'], 'erp-email-campaign' ), $_GET['untrashed'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['deleted'] ) ) {
                $notice = sprintf( _n( '%d campaign permanently deleted.', '%d campaigns permanently deleted.', $_GET['deleted'], 'erp-email-campaign' ), $_GET['deleted'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['duplicated'] ) ) {
                $campaign_url = ecamp_admin_url( [ 'action' => 'edit', 'id' => $_GET['duplicated'] ] );
                $notice = sprintf( __( 'Campaign duplicated successfully. <a href="%s">View campaign</a>', 'erp-email-campaign' ), $campaign_url );
                $this->display_notice( $notice );
            }
        }
    }

    /**
     * Add custom query args to the wp removable query args
     *
     * @param array $args
     *
     * @return array
     */
    public function removable_query_args( $args ) {
        $args[] = 'duplicated';

        return $args;
    }

    /**
     * Add Bounce Settings in Email Settings tab
     *
     * @param array $sections
     *
     * @return array
     */
    public function email_bounce_settings( $sections ) {
        $sections['bounce'] = __( 'Bounce', 'erp-email-campaign' );

        return $sections;
    }

    /**
     * Bounce settings fields
     *
     * @param array  $fields
     * @param string $section
     *
     * @return array
     */
    public function email_bounce_settings_fields( $fields, $section ) {

        if ( !extension_loaded( 'imap' ) || !function_exists( 'imap_open' ) ) {

            $fields['bounce'][] = [
                'title' => __( 'Bounce Settings Options', 'erp-email-campaign' ),
                'type'  => 'title',
                'desc'  => sprintf(
                    '%s' . __( 'Your server does not have PHP IMAP extension loaded. To enable this feature, please contact your hosting provider and ask to enable PHP IMAP extension.', 'erp' ) . '%s',
                    '<section class="notice notice-warning"><p>',
                    '</p></section>'
                )
            ];

            return $fields;
        }

        $fields['bounce'][] = [
            'title' => __( 'Bounce Settings Options', 'erp-email-campaign' ),
            'type'  => 'title',
            'desc'  => __( 'Email bounce settings for ERP Email Campaign.', 'erp-email-campaign' )
        ];

        $fields['bounce'][] = [
            'type' => 'bounce_status',
        ];

        $fields['bounce'][] = [
            'title' => __( 'When bounce occurs', 'erp-email-campaign' ),
            'type'  => 'after_bounce_action',
            'id'    => 'after_bounce_action'
        ];

        $fields['bounce'][] = [
            'title'   => __( 'Enable Settings', 'erp-email-campaign' ),
            'id'      => 'enable_imap',
            'type'    => 'radio',
            'options' => [ 'yes' => __( 'Yes', 'erp-email-campaign' ), 'no' => __( 'No', 'erp-email-campaign' ) ],
            'default' => 'no'
        ];

        $fields['bounce'][] = [
            'title'   => __( 'Cron Schedule', 'erp-email-campaign' ),
            'id'      => 'schedule',
            'type'    => 'select',
            'desc'    => __( 'Interval time to run cron.', 'erp-email-campaign' ),
            'options' => [
                'per_minute'        => __( 'Every Minute', 'erp-email-campaign' ),
                'hourly'            => __( 'Hourly', 'erp-email-campaign' ),
                'twicedaily'        => __( 'Twice Daily', 'erp-email-campaign' ),
                'daily'             => __( 'Daily', 'erp-email-campaign' ),
                'weekly'            => __( 'Weekly', 'erp-email-campaign' ),
            ],
            'default' =>  'twicedaily',
        ];

        $fields['bounce'][] = [
            'title'             => __( 'Mail Server', 'erp-email-campaign' ),
            'id'                => 'mail_server',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder'   => 'imap.gmail.com'
            ],
            'desc'              => __( 'IMAP/POP3 host address.', 'erp-email-campaign' ),
        ];

        $fields['bounce'][] = [
            'title'             => __( 'Username', 'erp-email-campaign' ),
            'id'                => 'username',
            'type'              => 'text',
            'desc'              => __( 'Your email id.', 'erp-email-campaign' ),
            'custom_attributes' => [
                'placeholder'   => 'email@example.com'
            ]
        ];

        $fields['bounce'][] = [
            'title' => __( 'Password', 'erp-email-campaign' ),
            'id'    => 'password',
            'type'  => 'password',
            'desc'  => __( 'Your email password.', 'erp-email-campaign' )
        ];

        $fields['bounce'][] = [
            'title'   => __( 'Protocol', 'erp-email-campaign' ),
            'id'      => 'protocol',
            'type'    => 'select',
            'desc'    => __( 'Protocol type.', 'erp-email-campaign' ),
            'options' => [ 'imap' => __( 'IMAP', 'erp-email-campaign' ), 'pop3' => __( 'POP3', 'erp-email-campaign' ) ],
            'default' =>  'imap',
        ];

        $fields['bounce'][] = [
            'title' => __( 'Port', 'erp-email-campaign' ),
            'id'    => 'port',
            'type'  => 'text',
            'desc'  => __( 'IMAP: 993<br> POP3: 995', 'erp-email-campaign' ),
        ];

        $fields['bounce'][] = [
            'title'   => __( 'Authentication', 'erp-email-campaign' ),
            'id'      => 'authentication',
            'type'    => 'select',
            'options' => [ 'ssl' => __( 'SSL', 'erp-email-campaign' ), 'tls' => __( 'TLS', 'erp-email-campaign' ), 'notls' => __( 'None', 'erp-email-campaign' ) ],
            'default' =>  'ssl',
            'desc'    => __( 'Authentication type.', 'erp-email-campaign' ),
        ];

        $fields['bounce'][] = [
            'type' => 'imap_test_connection',
        ];

        $fields['bounce'][] = [
            'id'      => 'imap_status',
            'type'    => 'hidden',
            'default' => 0,
        ];

        $fields['bounce'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];
        // End IMAP settings

        return $fields;
    }


    /**
     * Imap connection status.
     *
     * @return void
     */
    public function bounce_status() {
        $options     = get_option( 'erp_settings_erp-email_bounce', [] );
        $imap_status = (boolean) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php _e( 'Status', 'erp' ); ?>
            </th>
            <td class="forminp forminp-text">
                <span class="dashicons dashicons-<?php echo ( $imap_status ) ? 'yes green' : 'no red' ?>"></span><?php echo ( $imap_status ) ? __( 'Connected', 'erp' ) : __( 'Not Connected', 'erp' ); ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Update bounce check schedule after saving bounce settings
     *
     * @return void
     */
    public function update_bounce_schedule() {
        if ( !isset( $_GET['tab'] ) || 'erp-email' !== $_GET['tab'] || !isset( $_GET['section'] ) || ( 'bounce' !== $_GET['section'] ) ) {
            return;
        }

        wp_clear_scheduled_hook( 'erp_email_campaign_check_bounced_emails' );

        if ( ecamp_is_bounce_settings_active() ) {
            $recurrence = isset( $_POST['schedule'] ) ? $_POST['schedule'] : 'twicedaily';

            wp_schedule_event( time(), $recurrence, 'erp_email_campaign_check_bounced_emails' );
        }
    }

    /**
     * Add plugin settings area in CRM settings tab
     *
     * @param array $sections
     *
     * @return array
     */
    public function crm_sections_email_campaign( $sections ) {
        $sections['email_campaign'] = __( 'Email Campaign', 'erp-email-campaign' );
        return $sections;
    }

    /**
     * Settings fields for Email Campaign
     *
     * @since 1.0.0
     * @since 1.1.0 Add Campaign Manager and Debug log settings
     *
     * @param array  $fields
     * @param string $section
     *
     * @return array
     */
    public function crm_sections_email_campaign_fields( $fields, $section ) {
        global $wp_roles;
        $fields['email_campaign'][] = [
            'title' => __( 'Email Campaign Settings', 'erp-email-campaign' ),
            'type'  => 'title',
        ];

        $fields['email_campaign'][] = [
            'title' => __( 'Send', 'erp-email-campaign' ),
            'type'  => 'send_interval',
            'id'    => 'send_interval'
        ];

        $fields['email_campaign'][] = [
            'title' => __( 'Campaign Manager', 'erp-email-campaign' ),
            'type'  => 'multiselect',
            'id'    => 'managerial_roles',
            'class' => 'select2-is-loading',
            'custom_attributes' => [
                'data-settings-id' => 'ecamp-managerial-roles-settings'
            ],
            'options' => $wp_roles->get_names(),
            'default' => [ 'administrator', 'erp_crm_manager' ],
            'desc'    => __( 'Selected user roles will have permission to create, view, update and delete campaigns', 'erp-email-campaign' )
        ];

        $fields['email_campaign'][] = [
            'type'    => 'checkbox',
            'title'   => __( 'Use Debug Log', 'erp-email-campaign' ),
            'id'      => 'debug_log',
            'desc'    => sprintf(
                __( 'Log miscelloneous actions into debug.log <p class="description">Please use <strong>%s</strong> and <strong>%s</strong> <br>in your <strong>wp-config.php</strong></p>', 'erp-email-campaign' ),
                "define( 'WP_DEBUG', true );",
                "define( 'WP_DEBUG_LOG', true );"
            ),
            'default' => 'no'
        ];

        $fields['email_campaign'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

        return $fields;
    }

    /**
     * Print the send interval fields
     *
     * @return void
     */
    public function send_interval() {
        $options  = get_option( 'erp_settings_erp-crm_email_campaign', [] );

        $count    = !empty( $options['count'] ) ? absint( $options['count'] ) : 60;
        $interval = ( !empty( $options['interval'] ) && in_array( absint( $options['interval'] ) , array_keys( $this->send_email_intervals ) ) )
                        ? absint( $options['interval'] ) : 60;
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php _e( 'Send', 'erp-email-campaign' ); ?>
            </th>
            <td class="forminp">
                <input name="count" type="number" min="1" class="small-text" value="<?php echo $count; ?>">&nbsp;&nbsp;&nbsp;emails&nbsp;&nbsp;
                <select name="interval">
                    <?php echo erp_html_generate_dropdown( $this->send_email_intervals, $interval ); ?>
                </select>
                <p class="description"><?php _e( 'Your web host has limits. We suggest 60 emails per hour to be safe.', 'erp-email-campaign' ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Update send_interval fields
     *
     * @return void
     */
    public function save_admin_settings() {
        if ( !isset( $_GET['tab'] ) || 'erp-crm' !== $_GET['tab'] || !isset( $_GET['section'] ) || 'email_campaign' !== $_GET['section'] ) {
            return;
        }

        $options = get_option( 'erp_settings_erp-crm_email_campaign', [] );

        $count = 60;
        if ( !empty( $_POST['count'] ) ) {
            $count = absint( $_POST['count'] );
        }

        $interval = 60;
        if ( !empty( $_POST['interval'] ) && in_array( absint( $_POST['interval'] ) , array_keys( $this->send_email_intervals ) ) ) {
            $interval = absint( $_POST['interval'] );
        }

        $options['count'] = $count;
        $options['interval'] = $interval;

        $options['managerial_roles'] = !empty( $_POST['managerial_roles'] ) ? $_POST['managerial_roles'] : [];

        if ( !in_array( 'administrator' , $options['managerial_roles'] ) ) {
            $options['managerial_roles'][] = 'administrator';
        }

        if ( !in_array( 'erp_crm_manager' , $options['managerial_roles'] ) ) {
            $options['managerial_roles'][] = 'erp_crm_manager';
        }

        update_option( 'erp_settings_erp-crm_email_campaign', $options );

        ecamp_set_next_email_schedule();
    }

    /**
     * Settings fields after bounce action
     *
     * @return void
     */
    public function after_bounce_action() {
        ?>
        <tr valign="top" id="ecamp-after-bounce-settings" v-cloak>
            <th scope="row" class="titledesc">{{ i18n.afterEmailBounces }}</th>
            <td class="forminp">
                <select name="after_bounce_action" v-model="selectedAction">
                    <option v-for="(action, label) in actions" :value="action">{{ label }}</option>
                </select><br>

                <select v-if="'unsub_add_to_list' === selectedAction" style="margin-top: 5px;" name="contact_list" v-model="selectedList">
                    <option value="0">{{ i18n.selectAGroup }}</option>
                    <option v-for="group in groups" :value="group.id">{{ group.name }}</option>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * After bounce action settings update
     *
     * @return void
     */
    public function erp_update_option_after_bounce_action() {
        if ( !isset( $_GET['tab'] ) || 'erp-email' !== $_GET['tab'] || !isset( $_GET['section'] ) || 'bounce' !== $_GET['section'] ) {
            return;
        }

        $options = get_option( 'erp_settings_erp-crm_email_campaign', [] );

        if ( !empty( $_POST['after_bounce_action'] ) && in_array( $_POST['after_bounce_action'], array_keys( $this->after_bounce_actions ) ) ) {

            switch ( $_POST['after_bounce_action'] ) {
                case 'unsub_add_to_list':
                    $contact_list = absint( $_POST['contact_list'] );

                    if ( !empty( $contact_list ) ) {
                        $options['after_bounce_action'] = $_POST['after_bounce_action'];
                        $options['contact_list'] = absint( $_POST['contact_list'] );
                    }
                    break;

                default:
                    $options['after_bounce_action'] = $_POST['after_bounce_action'];
                    $options['contact_list'] = 0;
                    break;
            }
        }

        update_option( 'erp_settings_erp-crm_email_campaign', $options );
    }

    /**
     * Include campaign activity vue js template for single contact page
     *
     * @return void
     */
    public function erp_crm_load_vue_js_template() {
        erp_get_vue_component_template( WPERP_EMAIL_CAMPAIGN_VIEWS . '/email-campaign-component.php', 'erp-crm-timeline-email-campaign' );
    }

    /**
     * Enqueue script for single contact page
     *
     * @return void
     */
    public function contact_vue_scripts() {
        wp_enqueue_script( 'erp-email-campaign-contact-activity', WPERP_EMAIL_CAMPAIGN_ASSETS . '/js/contact-activity-email-campaign-component.js', [ 'wp-erp-crm-vue-component' ], WPERP_EMAIL_CAMPAIGN_VERSION, true );
    }

    /**
     * Add Email Campaign to feed nav list only in Activities page
     *
     * @param array $nav_items
     *
     * @return array
     */
    public function erp_crm_customer_feeds_nav_for_activities_page( $nav_items ) {
        global $current_screen;

        if ( 'crm_page_erp-sales-activities' === $current_screen->id ) {
            $nav_items['email_campaign'] = [
                'title' => __( 'Email Campaign', 'erp-email-campaign' ),
                'icon'  => '<i class="fa fa-envelope-o"></i>'
            ];
        }

        return $nav_items;
    }
}

new Admin();
