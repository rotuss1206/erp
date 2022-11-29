<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign as CampaignModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\Templates as TemplatesModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\People as PeopleModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\PeopleQueue as QueueModel;
use WeDevs\ERP\CRM\EmailCampaign\Logger;
use WeDevs\ORM\Eloquent\Facades\DB;
use WeDevs\ERP\Framework\Traits\Hooker;

class Task {

    use Hooker;

    /**
     * IMAP settings for bounce handling
     *
     * @var array
     */
    private $bounce_settings = [];

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'erp_email_campaign_cron_send_email', 'send_email' );

        // bounce checker task
        if ( ecamp_is_bounce_settings_active() ) {
            $this->bounce_settings = get_option( 'erp_settings_erp-email_bounce' );

            $bounce_check_interval = $this->bounce_settings['schedule'];

            if ( !wp_next_scheduled ( 'erp_email_campaign_check_bounced_emails' ) ) {
                wp_schedule_event( time(), $bounce_check_interval, 'erp_email_campaign_check_bounced_emails' );
            }

            $this->action( 'erp-email-campaign-email-bounced', 'after_bounce_action' );
            $this->action( 'erp_email_campaign_check_bounced_emails', 'check_bounced_mail' );
        }
    }

    /**
     * Send campaign emails
     *
     * @return void
     */
    public function send_email() {
        Logger::log( 'Triggered send_mail' );

        // calculate send limit
        $settings = get_option( 'erp_settings_erp-crm_email_campaign', [] );

        $send_per_interval = !empty( $settings['count'] ) ? absint( $settings['count'] ) :  60;
        $interval          = !empty( $settings['interval'] ) ? absint( $settings['interval'] ) :  60;
        $send_limit        = ceil( $send_per_interval / $interval );

        Logger::log( 'Send limit', $send_limit );

        $prefix = DB::instance()->db->prefix;
        $subscribers = DB::table( 'erp_crm_email_campaigns_people_queue as subscribers' )
                        ->select(
                            'subscribers.id AS queue_id', 'subscribers.campaign_id', 'subscribers.people_id',
                            DB::raw( 'coalesce( `users`.`user_email`, `people`.`email` ) email' )
                        )
                        ->leftJoin( "{$prefix}erp_peoples AS people", 'subscribers.people_id', '=', 'people.id' )
                        ->leftJoin( "{$prefix}users AS users", 'people.user_id', '=', 'users.ID' )
                        ->whereNull( 'subscribers.deleted_at' )
                        ->where( 'subscribers.send_at', '<=', current_time( 'mysql' ) )
                        ->orderBy( 'subscribers.campaign_id', 'ASC' )
                        ->orderBy( 'subscribers.people_id', 'ASC' )
                        ->take( $send_limit )
                        ->get();

        if ( !empty( $subscribers ) ) {
            Logger::log( 'Start sending emails to', count( $subscribers ) . ' subscribers' );
            $total_sent_to = 0;

            @ini_set( 'max_execution_time', '0' );

            foreach ( $subscribers as $subscriber ) {
                $campaign         = CampaignModel::find( $subscriber->campaign_id );
                $people           = erp_get_people_by( 'id', $subscriber->people_id );
                $current_time     = current_time( 'mysql' );
                $hash             = sha1( $current_time . $subscriber->campaign_id . $subscriber->people_id . 'erp-email-campaign' );

                // prepare mail data
                $to               = $people->email;
                $subject          = templates()->render_shortcodes( $campaign->email_subject, $subscriber->campaign_id, $subscriber->people_id, $hash );
                $message          = templates()->render_email( $subscriber->campaign_id, $subscriber->people_id, $hash );
                $unsubscribe_link = ecamp_unsubscribe_link( $hash );

                // headers
                $headers = "List-Unsubscribe: <{$unsubscribe_link}>" . "\r\n";

                add_filter( 'erp_mail_content_type', function () {
                    return 'text/html; charset=UTF-8';
                } );

                add_filter( 'erp_mail_from_name', function () use( $campaign ) {
                    return $campaign->sender_name;
                } );

                add_filter( 'erp_mail_from', function () use( $campaign ) {
                    return $campaign->sender_email;
                } );

                $return_path = $campaign->reply_to_email;
                if ( !empty( $this->bounce_settings['username'] ) ) {
                    $return_path = $this->bounce_settings['username'];
                }

                add_filter( 'erp_mail_return_path', function () use ( $return_path ) {
                    return $return_path;
                } );

                $headers .= "Reply-To: {$campaign->reply_to_name} <{$campaign->reply_to_email}>" . "\r\n";

                // send email
                $mail_sent = erp_mail( $to, $subject, $message, $headers );

                Logger::log( sprintf( 'To: %s, sent: %s, camp_id: %d, people_id: %d', $to, $mail_sent, $subscriber->campaign_id, $subscriber->people_id  ) );

                // after mail sent
                if ( !empty( $mail_sent ) ) {
                    global $phpmailer;

                    $message_id = substr( $phpmailer->getLastMessageID(), 1, 32 );

                    // remove people_id from queue
                    $sub = QueueModel::find( $subscriber->queue_id );

                    if ( ! $sub ) {

                        Logger::log( 'Null Subscriber', print_r( $subscriber, true ) );

                    } else {

                        $sub->forceDelete();

                        // insert into people table
                        PeopleModel::insert( [
                            'campaign_id'   => $subscriber->campaign_id,
                            'people_id'     => $subscriber->people_id,
                            'hash'          => $hash,
                            'message_id'    => $message_id,
                            'sent'          => $current_time
                        ] );

                        // Check if anyone to send is left. If no one left to send, then update the campaign status
                        $people_count = $campaign->peopleQueue()->get()->count();

                        if ( 'automatic' !== $campaign->send && empty( $people_count ) ) {
                            $campaign->update( [ 'status' => 'sent' ] );
                        }

                        ++$total_sent_to;
                    }
                }
            }

            Logger::log( 'Total sent to', $total_sent_to );

        } else {
            Logger::log( 'Queue is empty' );
        }

        ecamp_set_next_email_schedule();
    }

    /**
     * Check for bounced emails
     *
     * @return void
     */
    public function check_bounced_mail() {
        $mail_server = $this->bounce_settings['mail_server'];
        $username = $this->bounce_settings['username'];
        $password = $this->bounce_settings['password'];
        $protocol = $this->bounce_settings['protocol'];
        $port = isset( $this->bounce_settings['port'] ) ? $this->bounce_settings['port'] : 993;
        $encryption = isset( $this->bounce_settings['encryption'] ) ? $this->bounce_settings['encryption'] : 'ssl';

        $last_checked = get_option( 'erp-email-campaign-bounce-last-check', current_time( 'mysql' ) );

        $since = date( 'd F Y', strtotime( '-1 day ' . $last_checked ) );

        $imap = new \WeDevs\ERP\Imap( $mail_server, $port, $protocol, $username, $password, $encryption );

        $query = 'UNSEEN SINCE "' . $since . '"';
        $email_ids = $imap->open( 'inbox', $query, SE_UID );
        $bounced_ids = [];
        $hostname = ecamp_server_hostname();

        foreach ( $email_ids as $email_id ) {
            $bounced_email = $imap->get_body( $email_id );

            preg_match( "/Message-ID:.?\<([\w\d]{32})@{$hostname}\>/" , $bounced_email, $matches );

            if ( !empty( $matches[1] ) ) {
                $bounced_ids[] = $email_id;
                do_action( 'erp-email-campaign-email-bounced', $matches[1] );
            }
        }

        if ( !empty( $bounced_ids ) ) {
            $imap->mark_seen_emails( $bounced_ids );
        }

        update_option( 'erp-email-campaign-bounce-last-check', current_time( 'mysql' ) );
    }

    /**
     * After bounce action handler
     *
     * @param string $message_id
     *
     * @return void
     */
    public function after_bounce_action( $message_id ) {
        $people = PeopleModel::where( 'message_id', $message_id )->first();

        if ( empty( $people ) ) {
            return;
        }

        // change people under a campaign to bounced
        $people->update( [ 'bounced' => 1 ] );

        // update people activity
        erp_email_campaign()->save_activity( $people->people_id, $people->campaign_id );

        // This option is set in ERP Settings > Emails > Bounce
        $options = get_option( 'erp_settings_erp-crm_email_campaign', [] );

        if ( !empty( $options['after_bounce_action'] ) && 'do_nothing' !== $options['after_bounce_action'] ) {

            switch ( $options['after_bounce_action'] ) {
                case 'trash_user':
                    error_log( print_r( 'wooooo',true ) );
                    erp_email_campaign()->unsubscribe_people( $people->campaign_id, $people->people_id );
                    \WeDevs\ERP\Framework\Models\People::find( $people->people_id )->types()->whereNull( 'deleted_at' )->update( [ 'deleted_at' => current_time( 'mysql' ) ] );
                    break;

                case 'unsubscribe':
                    erp_email_campaign()->unsubscribe_people( $people->campaign_id, $people->people_id );
                    break;

                case 'unsub_add_to_list':
                    if ( !empty( $options['contact_list'] ) ) {
                        erp_email_campaign()->unsubscribe_people( $people->campaign_id, $people->people_id );

                        // add to list
                        $subscriber = \WeDevs\ERP\CRM\Models\ContactSubscriber::firstOrNew( [
                            'user_id'  => $people->people_id,
                            'group_id' => $options['contact_list']
                        ] );

                        if ( !$subscriber->exists ) {
                            $attrs = [
                                'user_id'           => $people->people_id,
                                'group_id'          => $options['contact_list'],
                                'status'            => 'subscribe',
                                'subscribe_at'      => current_time( 'mysql' ),
                                'unsubscribe_at'    => null,
                            ];

                            $subscriber->setRawAttributes( $attrs, true );
                            $subscriber->save();
                        }
                    }
                    break;
            }

        }
    }
}

// don't do anything if it's an ajax call
if ( !defined( 'DOING_AJAX' ) ) {
    new Task();
}
