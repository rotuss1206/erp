<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

class Logger {

    /**
     * Is debug log on in Campaign settings
     *
     * @since 1.1.0
     *
     * @var boolean
     */
    public static $is_logger_on = false;

    /**
     * @var object
     *
     * @since 1.1.0
     */
    private static $instance;

    /**
     * Initializes the Logger() class
     *
     * @since 1.1.0
     *
     * Checks for an existing Logger() instance
     * and if it doesn't find one, then creates it.
     *
     * @return object
     */
    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Logger ) ) {
            self::$instance = new Logger;
            self::$instance->is_logger_on();
        }

        return self::$instance;
    }

    /**
     * Check if logger on or off
     *
     * @since 1.1.0
     *
     * @return void
     */
    private function is_logger_on() {
        $settings  = get_option( 'erp_settings_erp-crm_email_campaign', [] );
        $debug_log = ! empty( $settings['debug_log'] ) ? $settings['debug_log'] : 'no';

        if ( erp_validate_boolean( $debug_log ) ) {
            self::$is_logger_on = true;
        } else {
            self::$is_logger_on = false;
        }
    }

    /**
     * Log messages
     *
     * @since 1.1.0
     *
     * @param string $context
     * @param string $message
     * @param boolean $new_line
     *
     * @return void
     */
    public static function log( $context, $message = null, $new_line = false ) {
        if ( ! ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && self::$is_logger_on ) ) {
            return;
        }

        if ( is_array( $message ) ) {
            $message = json_encode( $message ) . "\n";
        }

        $log_file     = WP_CONTENT_DIR . '/erp-email-campaign.log';
        $current_time = date( 'd-M-Y H:i:s e', current_time( 'timestamp', true ) );
        $separator    = ( $message && $new_line ) ? "\n" : ": ";

        $log = sprintf( '[%s] %s%s%s', $current_time, $context, $separator, $message );

        error_log( $log, 3, $log_file );
        error_log( "\n", 3, $log_file );
    }

    /**
     * Log when `wp_schedule_single_event` function failed to schedule
     *
     * Mimic `wp_schedule_single_event` function
     *
     * @since 1.1.0
     *
     * @param integer $timestamp
     *
     * @return false|void False if the event does not get scheduled.
     */
    public static function log_failed_cron( $timestamp ) {
        $hook = 'erp_email_campaign_cron_send_email';
        $args = [];

        // Make sure timestamp is a positive integer
        if ( ! is_numeric( $timestamp ) ) {
            self::log( 'Failed to schedule', 'timestamp is not numeric' );
            return false;
        }

        if ( $timestamp <= 0 ) {
            self::log( 'Failed to schedule', 'timestamp is less than or equal zero' );
            return false;
        }

        // Don't schedule a duplicate if there's already an identical event due within 10 minutes of it
        $next = wp_next_scheduled( $hook, $args );
        if ( $next && abs( $next - $timestamp ) <= 10 * MINUTE_IN_SECONDS ) {
            self::log( 'Failed to schedule', 'duplicate schedule found' );
            return false;
        }

        $event = (object) [ 'hook' => $hook, 'timestamp' => $timestamp, 'schedule' => false, 'args' => $args ];

        $event = apply_filters( 'schedule_event', $event );

        // A plugin disallowed this event
        if ( ! $event ) {
            self::log( 'Failed to schedule', '$event is false' );
            return false;
        }
    }

}

Logger::instance();
