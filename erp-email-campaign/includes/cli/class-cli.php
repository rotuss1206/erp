<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

/**
 * CLI commands
 *
 * @since 1.1.0
 */
class CLI extends \WP_CLI_Command {

    /**
     * Common validation to see if campaign id is provided
     *
     * @since 1.1.0
     *
     * @param int|array $campaign_id
     *
     * @return void
     */
    public function check_required_id( $campaign_id ) {
        if ( empty( $campaign_id ) ) {
            \WP_CLI::error( __( 'Campaign id is required', 'erp-email-campaign' ) );
        }
    }

    /**
     * Duplicate a campaign
     *
     * @since 1.1.0
     *
     * @param int $campaign_id
     *
     * @return void
     */
    public function duplicate_campaign( $campaign_id ) {
        $this->check_required_id( $campaign_id );

        $campaign_id = array_pop( $campaign_id );

        $campaign = erp_email_campaign()->duplicate_campaign( $campaign_id );

        if ( !empty( $campaign->id ) ) {
            \WP_CLI::success( __( 'Campaign duplicated successfully', 'erp-email-campaign' ) );
        } else {
            \WP_CLI::error( __( 'Could not duplicate', 'erp-email-campaign' ) );
        }
    }

    /**
     * Delete campaign or campaigns
     *
     * @since 1.1.0
     *
     * @param array $campaign_ids
     *
     * @alias delete_campaign
     *
     * @return void
     */
    public function delete_campaigns( $campaign_ids ) {
        $this->check_required_id( $campaign_ids );

        $first_id = $campaign_ids[0];

        if ( strpos( $first_id, '-' ) !== false ) {
            $ids = explode( '-', $first_id );
            $campaign_ids = range( $ids[0], $ids[1] );
        }

        erp_email_campaign()->delete_campaigns( $campaign_ids );

        if ( count( $campaign_ids ) > 1 ) {
            $msg = __( 'Campaigns deleted', 'erp-email-campaign' );
        } else {
            $msg = __( 'Campaign deleted', 'erp-email-campaign' );
        }

        \WP_CLI::success( $msg );
    }

    /**
     * Clear campaign tables
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function clear_tables() {
        global $wpdb;

        // first delete campaigns in proper way
        $campaign_ids = \WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign::lists( 'id' );

        if ( !empty( $campaign_ids ) ) {
            erp_email_campaign()->delete_campaigns( $campaign_ids );
        } else {
            \WP_CLI::warning( __( 'No campaign found', 'erp-email-campaign' ) );
        }

        // now truncate tables
        $results = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}erp_crm_email_campaigns%'", ARRAY_A );

        if ( !empty( $results ) ) {
            foreach ( $results as $table ) {
                $table = array_pop( $table );
                $wpdb->query( 'TRUNCATE TABLE ' . $table );
            }
        }

        include_once WPERP_EMAIL_CAMPAIGN_INCLUDES . '/install/class-install.php';

        $install = new Install();

        $install->activate();
    }

}

\WP_CLI::add_command( 'erp_ecamp', 'WeDevs\ERP\CRM\EmailCampaign\CLI' );
