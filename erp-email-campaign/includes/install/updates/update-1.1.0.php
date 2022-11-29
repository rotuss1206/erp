<?php
/**
 * Version 1.1.0 DB updates
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ecamp_update_1_1_0() {
    global $wpdb;

    $query = $wpdb->prepare(
        'select count(*) from information_schema.tables where table_schema = %s and table_name = %s',
        $wpdb->dbname, $wpdb->prefix . 'erp_crm_email_campaigns'
    );


    if ( $wpdb->get_var( $query ) ) {
        $template_tbl      = $wpdb->prefix . 'erp_crm_email_campaigns_templates';
        $template_tbl_cols = $wpdb->get_col( "DESC " . $template_tbl );

        if ( ! empty( $template_tbl_cols ) && !in_array( 'plugin_version' , $template_tbl_cols ) ) {
            add_filter( 'erp-email-campaign-updates-wpdb-query', function( $queries ) {

                $queries[] = "ALTER TABLE  `" . WPERP_ATTEND_DB_PREFIX . "erp_crm_email_campaigns_templates`"
                           . " ADD  `plugin_version` varchar(10) DEFAULT NULL;";

                return $queries;

            } );

            add_action( 'erp-email-campaign-updates-end', 'erp_ecamp_update_1_1_0_table_data' );
        }
    }

}

erp_ecamp_update_1_1_0();

/**
 * Insert table data for new column
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ecamp_update_1_1_0_table_data() {
    global $wpdb;

    $wpdb->query(
        "UPDATE {$wpdb->prefix}erp_crm_email_campaigns_templates"
        . " SET plugin_version = '1.0.0' WHERE plugin_version IS NULL"
    );

    // truncate and insert new data for presets
    \WeDevs\ERP\CRM\EmailCampaign\Install::insert_table_data_for_presets();
}

/**
 * Create new table and insert data
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ecamp_update_1_1_0_create_table() {
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

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_email_campaigns_open_stats` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` bigint(20) unsigned NOT NULL,
              `people_id` bigint(20) unsigned NOT NULL,
              `opened_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) $collate;"

        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

        $campaign_people = \WeDevs\ERP\CRM\EmailCampaign\Models\People::select( 'campaign_id', 'people_id', 'open' )->whereNotNull( 'open' )->get();

        $campaign_people->each( function ( $people ) {
            $open_stat = new \WeDevs\ERP\CRM\EmailCampaign\Models\OpenStat();

            $open_stat->campaign_id = $people->campaign_id;
            $open_stat->people_id   = $people->people_id;
            $open_stat->opened_at   = $people->open;

            $open_stat->save();
        } );
}

erp_ecamp_update_1_1_0_create_table();
