<?php
/**
 * Helper Functions
 */

/**
 * Email_Campaign class instance
 *
 * @since 1.1.0
 *
 * @return object Email_Campaign class instance
 */
function erp_email_campaign() {
    return \WeDevs\ERP\CRM\EmailCampaign\Email_Campaign::instance();
}


/**
 * Build admin url
 *
 * @param array  $queries
 * @param string $page
 * @param string $base
 *
 * @return string admin url
 */
function ecamp_admin_url( $queries = [], $page = 'erp-email-campaign', $base = 'admin.php' ) {
    if ( version_compare( WPERP_VERSION, '1.4.0', '>=' ) ) {
        $page = 'erp-crm';
        $queries['section'] = 'email-campaign';
        $queries = array_reverse( $queries );
    }
    $queries = [ 'page' => $page ] + $queries;

    $query_string = http_build_query( $queries );

    return admin_url( $base . '?' . $query_string );
}

/**
 * Date format for the jQuery Datepicker
 *
 * @return string example: Y-m-d will change to yy-mm-dd
 */
function ecamp_js_date_format() {
    $format = erp_get_option( 'date_format', 'erp_settings_general', 'd-m-Y' );

    $js_format = str_replace( [ 'Y', 'm', 'd' ], [ 'yy', 'mm', 'dd' ], $format );

    return $js_format;
}

/**
 * Generates an unsubscribe link for a people id
 *
 * @param string $hash
 *
 * @return string
 */
function ecamp_unsubscribe_link( $hash ) {
    return site_url( "?erp-email-campaign=1&unsubscribe={$hash}" );
}

/**
 * Generates edit subscription link for a people id
 *
 * @param string $hash
 *
 * @return string
 */
function ecamp_edit_subscription_link( $hash ) {
    return site_url( "?erp-email-campaign=1&edit-subscription={$hash}" );
}

/**
 * Generates email open tracker image url
 *
 * Hash is the value of hash column in people table
 *
 * @param string $hash
 *
 * @return string
 */
function ecamp_get_tracker_image( $hash ) {
    return site_url( "?erp-email-campaign=1&image=1&user={$hash}" );
}

/**
 * Determine if the imap for bounce settings mail is active or not.
 *
 * @return boolean
 */
function ecamp_is_bounce_settings_active() {
    $options = get_option( 'erp_settings_erp-email_bounce', [] );

    $enable_imap = ( isset( $options['enable_imap'] ) && $options['enable_imap'] == 'yes' ) ? true : false;
    $imap_status = (boolean) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;

    if ( $enable_imap && $imap_status ) {
        return true;
    }

    return false;
}

/**
 * ERP Company Address and logo
 *
 * @return string
 */
function get_company_details() {
    $full_address = '';
    $logo = '';

    $company = new \WeDevs\ERP\Company();

    $address = array_filter( $company->address );

    if ( !empty( $address['country'] ) && '-1' === $address['country'] ) {
        unset( $address['country'] );
    }

    if ( !empty( $address['state'] ) && '-1' === $address['state'] ) {
        unset( $address['state'] );
    }

    if ( !empty( $address ) && !empty( $address['country'] ) ) {
        $erp_countries  = \WeDevs\ERP\Countries::instance();
        $all_countries  = $erp_countries->get_countries();

        $country = $all_countries[ $address['country'] ];

        if ( !empty( $address['state'] ) ) {
            $all_states = array_filter( $erp_countries->states );

            if ( !empty( $all_states[ $address['country'] ][ $address['state'] ] ) ) {
                $address['state'] = $all_states[ $address['country'] ][ $address['state'] ];
            }
        }

        $address['country'] = $country;
    }

    $logo = $company->get_logo();

    if ( $logo && (string) $company->website ) {
        $logo = '<a href="' . $company->website . '">' . $logo . '</a>';
    }

    return [
        'logo'      => $logo,
        'name'      => $company->name,
        'address'   => $address,
        'phone'     => $company->phone,
        'fax'       => $company->fax,
        'mobile'    => $company->mobile,
        'website'   => $company->website,
        'currency'  => $company->currency,
    ];
}

/**
 * Get the ERP people types
 *
 * @return array
 */
function ecamp_get_people_types() {
    $types = [];
    $people_types = \WeDevs\ERP\Framework\Models\PeopleTypes::orderBy( 'id', 'asc' )->get()->toArray();

    foreach ( $people_types as $type ) {
        $types[ $type['id'] ] = $type['name'];
    }

    return $types;
}


/**
 * Get the server hostname.
 *
 * Returns 'localhost.localdomain' if unknown.
 * Copied from wp-includes/class-phpmailer.php
 *
 * @return string
 */
function ecamp_server_hostname() {
    $result = 'localhost.localdomain';

    if ( isset( $_SERVER ) and array_key_exists( 'SERVER_NAME', $_SERVER ) and !empty( $_SERVER['SERVER_NAME'] ) ) {
        $result = $_SERVER['SERVER_NAME'];
    } elseif ( function_exists( 'gethostname' ) && gethostname() !== false ) {
        $result = gethostname();
    } elseif ( php_uname( 'n' ) !== false ) {
        $result = php_uname( 'n' );
    }

    return $result;
}

/**
 * Set schedule for sending email
 *
 * @since 1.1.0
 *
 * @param boolean $immediate
 *
 * @return void
 */
function ecamp_set_next_email_schedule( $immediate = false ) {
    $schedule = wp_next_scheduled( 'erp_email_campaign_cron_send_email' );
    $logger   = \WeDevs\ERP\CRM\EmailCampaign\Logger::instance();

    if ( function_exists( 'xdebug_call_function' ) ) {
        $logger::log( 'Called ecamp_set_next_email_schedule from', xdebug_call_function() );
    }

    if ( $schedule ) {
        return;
    }

    $people_on_queue = \WeDevs\ERP\CRM\EmailCampaign\Models\PeopleQueue::count();

    if ( empty( $people_on_queue ) ) {
        $logger::log( 'Next schedule', 'Exiting since no subscriber found on queue' );
        return;
    }

    if ( $immediate ) {
        $next_schedule = time();

    } else {
        $settings = get_option( 'erp_settings_erp-crm_email_campaign' );
        $count    = !empty( $settings['count'] ) ? absint( $settings['count'] ) : 60;
        $interval = !empty( $settings['interval'] ) ? absint( $settings['interval'] ) : 60;

        $next_schedule = time() + ( ceil( $interval / $count ) * 60 );
    }

    $scheduled = wp_schedule_single_event( $next_schedule, 'erp_email_campaign_cron_send_email' );

    if ( false === $scheduled ) {
        $logger::log_failed_cron( $next_schedule );
    } else {
        $logger::log( 'Next schedule', date( 'Y-m-d H:i:s', $next_schedule ) );
    }
}

/**
 * WP Timezone Settings
 *
 * @since 1.1.0
 *
 * @return string
 */
function ecamp_get_wp_timezone() {
    $momentjs_tz_map = [
        'UTC-12'    => 'Etc/GMT+12',
        'UTC-11.5'  => 'Pacific/Niue',
        'UTC-11'    => 'Pacific/Pago_Pago',
        'UTC-10.5'  => 'Pacific/Honolulu',
        'UTC-10'    => 'Pacific/Honolulu',
        'UTC-9.5'   => 'Pacific/Marquesas',
        'UTC-9'     => 'America/Anchorage',
        'UTC-8.5'   => 'Pacific/Pitcairn',
        'UTC-8'     => 'America/Los_Angeles',
        'UTC-7.5'   => 'America/Edmonton',
        'UTC-7'     => 'America/Denver',
        'UTC-6.5'   => 'Pacific/Easter',
        'UTC-6'     => 'America/Chicago',
        'UTC-5.5'   => 'America/Havana',
        'UTC-5'     => 'America/New_York',
        'UTC-4.5'   => 'America/Halifax',
        'UTC-4'     => 'America/Manaus',
        'UTC-3.5'   => 'America/St_Johns',
        'UTC-3'     => 'America/Sao_Paulo',
        'UTC-2.5'   => 'Atlantic/South_Georgia',
        'UTC-2'     => 'Atlantic/South_Georgia',
        'UTC-1.5'   => 'Atlantic/Cape_Verde',
        'UTC-1'     => 'Atlantic/Azores',
        'UTC-0.5'   => 'Atlantic/Reykjavik',
        'UTC+0'     => 'Etc/UTC',
        'UTC'       => 'Etc/UTC',
        'UTC+0.5'   => 'Etc/UTC',
        'UTC+1'     => 'Europe/Madrid',
        'UTC+1.5'   => 'Europe/Belgrade',
        'UTC+2'     => 'Africa/Tripoli',
        'UTC+2.5'   => 'Asia/Amman',
        'UTC+3'     => 'Europe/Moscow',
        'UTC+3.5'   => 'Asia/Tehran',
        'UTC+4'     => 'Europe/Samara',
        'UTC+4.5'   => 'Asia/Kabul',
        'UTC+5'     => 'Asia/Karachi',
        'UTC+5.5'   => 'Asia/Kolkata',
        'UTC+5.75'  => 'Asia/Kathmandu',
        'UTC+6'     => 'Asia/Dhaka',
        'UTC+6.5'   => 'Asia/Rangoon',
        'UTC+7'     => 'Asia/Bangkok',
        'UTC+7.5'   => 'Asia/Bangkok',
        'UTC+8'     => 'Asia/Shanghai',
        'UTC+8.5'   => 'Asia/Pyongyang',
        'UTC+8.75'  => 'Australia/Eucla',
        'UTC+9'     => 'Asia/Tokyo',
        'UTC+9.5'   => 'Australia/Darwin',
        'UTC+10'    => 'Australia/Brisbane',
        'UTC+10.5'  => 'Australia/Adelaide',
        'UTC+11'    => 'Australia/Melbourne',
        'UTC+11.5'  => 'Pacific/Norfolk',
        'UTC+12'    => 'Asia/Anadyr',
        'UTC+12.75' => 'Asia/Anadyr',
        'UTC+13'    => 'Pacific/Fiji',
        'UTC+13.75' => 'Pacific/Chatham',
        'UTC+14'    => 'Pacific/Tongatapu',
    ];

    $current_offset = get_option('gmt_offset');
    $tzstring = get_option('timezone_string');

    // Remove old Etc mappings. Fallback to gmt_offset.
    if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
        $tzstring = '';
    }

    if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
        if ( 0 == $current_offset ) {
            $tzstring = 'UTC+0';
        } elseif ($current_offset < 0) {
            $tzstring = 'UTC' . $current_offset;
        } else {
            $tzstring = 'UTC+' . $current_offset;
        }

    }

    if ( array_key_exists( $tzstring , $momentjs_tz_map ) ) {
        $tzstring = $momentjs_tz_map[ $tzstring ];
    }

    return $tzstring;
}

/**
 * Convert date time based on current timezone to GMT
 *
 * @since 1.1.0
 *
 * @param string  $date_time In `Y-m-d H:i:s` format
 * @param boolean $timestamp when true, it will return in unix timestamp format
 *
 * @return string
 */
function ecamp_convert_to_gmt( $date_time, $timestamp = false ) {
    $tzstring = ecamp_get_wp_timezone();

    $date = \Carbon\Carbon::createFromFormat( 'Y-m-d H:i:s', $date_time, $tzstring );
    $date = $date->setTimezone('GMT');

    if ( $timestamp ) {
        return $date->timestamp;
    }

    return $date->toDateTimeString();
}

/**
 * Convert GMT date time to current wp timezone time
 *
 * @since 1.1.0
 *
 * @param string  $date_time GMT date time in `Y-m-d H:i:s` format
 * @param boolean $timestamp when true, it will return in unix timestamp format
 *
 * @return string
 */
function ecamp_gmt_to_tz( $date_time, $timestamp = false ) {
    $tzstring = ecamp_get_wp_timezone();

    $date = \Carbon\Carbon::createFromFormat( 'Y-m-d H:i:s', $date_time, 'GMT' );
    $date = $date->setTimezone( $tzstring );

    if ( $timestamp ) {
        return $date->timestamp;
    }

    return $date->toDateTimeString();
}
