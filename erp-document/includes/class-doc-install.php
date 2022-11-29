<?php
/**
 * Installer Class
 *
 * @package ERP
 */
class WeDevs_ERP_Document_Installer {

    /**
     * Binding all events
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {
        register_activation_hook( WPERP_DOC_FILE, array( $this, 'activate_doc_now' ) );
        register_deactivation_hook( WPERP_DOC_FILE, array( $this, 'deactivate' ) );
    }

    /**
     * Placeholder for activation function
     * Nothing being called here yet.
     *
     * @since 0.1
     *
     * @return 0.1
     */
    public function activate_doc_now() {
        $this->create_doc_tables();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Create necessary table for ERP & HRM
     *
     * @since 0.1
     *
     * @return  void
     */
    public function create_doc_tables() {
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

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_employee_dir_file_relationship` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `eid` int(11) unsigned NOT NULL,
                 `dir_id` int(11) unsigned NOT NULL,
                 `dir_name` varchar(255) DEFAULT '',
                 `attachment_id` int(11) unsigned NOT NULL,
                 `parent_id` int(11) unsigned NOT NULL,
                 `is_dir` tinyint(1) unsigned NOT NULL,
                 `created_by` int(11) unsigned NOT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `eid` (`eid`)
             ) $collate;"

        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

    }

}

new WeDevs_ERP_Document_Installer();
