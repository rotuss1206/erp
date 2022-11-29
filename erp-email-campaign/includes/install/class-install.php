<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

/**
 * Installation related functions and actions
 *
 * @since 1.1.0
 */
class Install {

    /**
     * Class constructor
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function __construct() {
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
            return;
        }

        // on plugin register hook
        register_activation_hook( WPERP_EMAIL_CAMPAIGN_FILE, [ $this, 'activate' ] );

        // on plugin deactivation hook
        register_deactivation_hook( WPERP_EMAIL_CAMPAIGN_FILE, [ $this, 'deactivate' ] );
    }

    /**
     * Executes while Plugin Activation
     *
     * @since 1.0.0
     * @since 1.1.0 Moved in this class from `WeDevs_ERP_CRM_Email_Campaign` class
     *
     * @return void
     */
    public function activate() {
        if ( !class_exists( 'WeDevs_ERP' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( WPERP_EMAIL_CAMPAIGN_FILE ) );
            wp_die( __( 'You need to install WP-ERP main plugin to use this addon', 'erp-email-campaign' ) );
        }

        self::create_table();
        self::insert_table_data_for_presets();

        // check if subscription page exits. if not then create one
        $query = new \WP_Query( [ 'post_type' => 'erp-email-campaign' ] );

        if ( !$query->have_posts() ) {
            // Create post object
            $subscription_pg = array(
                'post_title'        => 'ERP Email Campaign Subscription',
                'post_content'      => '',
                'post_status'       => 'publish',
                'comment_status'    => 'closed',
                'ping_status'       => 'closed',
                'post_author'       => 1,
                'post_type'         => 'erp-email-campaign',
            );

            // Insert the post into the database
            wp_insert_post( $subscription_pg );
        }

        /* Restore original Post Data */
        wp_reset_postdata();

        /* set next email sending schedule */
        \WeDevs_ERP_CRM_Email_Campaign::init()->includes();
        ecamp_set_next_email_schedule( true );
    }

    /**
     * Execute during plugin deactivation
     *
     * @since 1.0.0
     * @since 1.1.0 Moved in this class from `WeDevs_ERP_CRM_Email_Campaign` class
     *
     * @return void
     */
    public function deactivate() {
        wp_clear_scheduled_hook( 'erp_email_campaign_cron_send_email' );
        wp_clear_scheduled_hook( 'erp_email_campaign_check_bounced_emails' );
    }

    /**
     * Placeholder for creating tables while activating plugin
     *
     * @since 1.0.0
     * @since 1.1.0 Moved in this class from `WeDevs_ERP_CRM_Email_Campaign` class.
     *              Moved table_schema array here from table-schema.php.
     *              Add `erp_crm_email_campaigns_open_stats` table schema
     *
     * @return void
     */
    public static function create_table() {

        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        $table_schema = [

            // primary table that contains campaign informations
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `email_subject` varchar(255) NOT NULL,
                `status` varchar(20) NOT NULL,
                `sender_name` varchar(255) NOT NULL,
                `sender_email` varchar(255) NOT NULL,
                `reply_to_name` varchar(255) NOT NULL,
                `reply_to_email` varchar(255) NOT NULL,
                `send` varchar(255) NOT NULL,
                `campaign_name` varchar(255) DEFAULT NULL,
                `deliver_at` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            // campaign lists
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_lists` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `campaign_id` bigint(20) NOT NULL,
                `type` varchar(40) NOT NULL,
                `type_id` int(20) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `campaign_id` (`campaign_id`),
                KEY `type_id` (`type_id`)
            ) $collate;",

            // store data for event based campaigns
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_events` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` bigint(20) unsigned NOT NULL,
              `action` varchar(255) NOT NULL DEFAULT '',
              `arg_value` varchar(255) NOT NULL DEFAULT '',
              `schedule_type` varchar(20) NOT NULL,
              `schedule_offset` int(10) unsigned NOT NULL,
              `deleted_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `campaign_id` (`campaign_id`)
            ) $collate;",

            // Built-in base templates and themes. For base templates category is null
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_template_presets` (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `name` varchar(255) NOT NULL,
                `category` int(10) DEFAULT NULL,
                `template` longtext NOT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            // categories for themes
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_template_preset_categories` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`)
            ) $collate;",

            // template json, html and links for a campaign created by user
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_templates` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `campaign_id` bigint(20) NOT NULL,
              `template` longtext NOT NULL,
              `html` longtext NOT NULL,
              `links` longtext,
              `plugin_version` varchar(10) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `campaign_id` (`campaign_id`)
            ) $collate;",

            // When we save a campaign, people belongs to campaign lists will
            // put into this table. After successfully send, campaign-people pair
            // will remove from this table
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_people_queue` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` bigint(20) unsigned NOT NULL,
              `people_id` bigint(20) unsigned NOT NULL,
              `send_at` datetime NOT NULL,
              `deleted_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `campaign_id` (`campaign_id`),
              KEY `people_id` (`people_id`)
            ) $collate;",

            // after successfully send campaign email, campaign-people pair will put into this table
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_people` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` bigint(20) unsigned NOT NULL,
              `people_id` bigint(20) unsigned NOT NULL,
              `hash` varchar(40) NOT NULL,
              `message_id` varchar(32) NOT NULL,
              `sent` datetime DEFAULT NULL,
              `open` datetime DEFAULT NULL,
              `clicked` tinyint(1) DEFAULT NULL,
              `bounced` tinyint(1) DEFAULT NULL,
              `unsubscribed` tinyint(1) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `campaign_id` (`campaign_id`),
              KEY `people_id` (`people_id`),
              KEY `message_id` (`message_id`),
              KEY `hash` (`hash`)
            ) $collate;",

            // Saves the url once to which visitor will redirect to
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_urls` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `url` text NOT NULL,
              PRIMARY KEY (`id`)
            ) $collate;",

            // URL click statistics
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_url_stats` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `url_id` bigint(20) NOT NULL,
              `campaign_id` bigint(20) NOT NULL,
              `people_id` bigint(20) NOT NULL,
              `clicked_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `url_id` (`url_id`),
              KEY `campaign_id` (`campaign_id`),
              KEY `people_id` (`people_id`)
            ) $collate;",

            // Save search unsubscribers table
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_save_search_unsubscribers` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` bigint(20) unsigned NOT NULL,
              `search_id` bigint(20) unsigned NOT NULL,
              `unsubscribed_at` datetime NOT NULL,
               PRIMARY KEY (`id`),
               KEY `user_id` (`user_id`),
               KEY `search_id` (`search_id`)
            ) $collate;",

            // Email open tracket stats
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_open_stats` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` bigint(20) unsigned NOT NULL,
              `people_id` bigint(20) unsigned NOT NULL,
              `opened_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `campaign_id` (`campaign_id`),
              KEY `people_id` (`people_id`)
            ) $collate;"

        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }

    /**
     * Insert default data for the plugin during installation
     *
     * @since 1.0.0
     * @since 1.1.0 Moved in this class from `WeDevs_ERP_CRM_Email_Campaign` class.
     *              Moved codes here from table-data.php.
     *              JSON data for templates put in template-presets.txt.
     *              Rename function name from `insert_table_data` to `insert_table_data_for_presets`.
     *
     * @return void
     */
    public static function insert_table_data_for_presets() {
        global $wpdb;

        // Truncate preset table
        $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_crm_email_campaigns_template_presets' );

        // Insert preset data
        $sql = "INSERT INTO `{$wpdb->prefix}erp_crm_email_campaigns_template_presets` (`id`, `title`, `name`, `category`, `template`)
                VALUES
                    ( 1,  'Text Only', 'text-only', null, %s ),
                    ( 2,  '1 Column', '1-column', null, %s ),
                    ( 3,  '1 Column - Banded', '1-column-banded', null, %s ),
                    ( 4,  '1:2 Column', '1-2-column', null, %s ),
                    ( 5,  '1:2 Column - Banded', '1-2-column-banded', null, %s ),
                    ( 6,  '1:2:1 Column', '1-2-1-column', null, %s ),
                    ( 7,  '1:2:1 Column - Banded', '1-2-1-column-banded', null, %s ),
                    ( 8,  '1:3 Column', '1-3-column', null, %s ),
                    ( 9,  '1:3 Column - Banded', '1-3-column-banded', null, %s ),
                    ( 10, '1:3:1 Column', '1-3-1-column', null, %s ),
                    ( 11, '1:3:1 Column - Banded', '1-3-1-column-banded', null, %s ),
                    ( 12, '1:3:2 Column', '1-3-2-column', null, %s ),
                    ( 13, '1:3:2 Column - Banded', '1-3-2-column-banded', null, %s ),
                    ( 14, '2 Column', '2-column', null, %s ),
                    ( 15, '2 Column - Banded', '2-column-banded', null, %s ),
                    ( 16, '2:1 Column', '2-1-column', null, %s ),
                    ( 17, '2:1 Column - Banded', '2-1-column-banded', null, %s ),
                    ( 18, '2:1:2 Column', '2-1-2-column', null, %s ),
                    ( 19, '2:1:2 Column - Banded', '2-1-2-column-banded', null, %s ),
                    ( 20, '3:1:3 Column', '3-1-3-column', null, %s ),
                    ( 21, '3:1:3 Column - Banded', '3-1-3-column-banded', null, %s ),
                    ( 22, 'Shopping', 'shopping', 1, %s ),
                    ( 23, 'Ultimate Store', 'ultimate-store', 1, %s),
                    ( 24, 'Popular Courses', 'popular-courses', 2, %s),
                    ( 25, 'Featured Courses', 'featured-courses', 2, %s),
                    ( 26, 'Restaurant', 'restaurant', 3, %s),
                    ( 27, 'Food Factory', 'food-factory', 3, %s),
                    ( 28, 'Fitness Gym', 'fitness-gym', 4, %s);";


        /**
         * Get template presets from template-presets.txt.
         * Presets are saved in unslashed json format. So we cannot use them in php or json array
         * without escaping. And I don't want to add extra back slashes
         */
        $contents = file_get_contents( WPERP_EMAIL_CAMPAIGN_INCLUDES . '/install/template-presets.txt' );
        $templates = explode( "\n" , $contents );
        $template_presets = [];
        foreach ( $templates as $template ) {
            if ( !empty( $template ) ) {
                $template_presets[] = $template;
            }
        }

        $wpdb->query( $wpdb->prepare(
            $sql, $template_presets
        ) );

        // Truncate category table
        $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_crm_email_campaigns_template_preset_categories' );

        // insert categories
        $sql = "INSERT INTO `{$wpdb->prefix}erp_crm_email_campaigns_template_preset_categories` (`id`, `title`)
                VALUES
                    ( 1, 'E-Commerce' ),
                    ( 2, 'Education' ),
                    ( 3, 'Food' ),
                    ( 4, 'Health' );";

        $wpdb->query( $sql );
    }

}

new Install();
