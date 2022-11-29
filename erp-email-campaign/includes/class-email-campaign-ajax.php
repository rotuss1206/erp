<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Framework\Traits\Ajax;

class Campaign_Ajax {

    use Hooker;
    use Ajax;

    /**
     * The class constructor
     */
    public function __construct() {
        $this->action( 'wp_ajax_get_campaign_editor_data', 'get_campaign_editor_data' );
        $this->action( 'wp_ajax_get_post_type_tax_terms', 'get_post_type_tax_terms' );
        $this->action( 'wp_ajax_get_posts_for_wp_posts_editor', 'get_posts_for_wp_posts_editor' );
        $this->action( 'wp_ajax_get_posts_for_template', 'get_posts_for_template' );
        $this->action( 'wp_ajax_save_campaign', 'save_campaign' );
        $this->action( 'wp_ajax_get_template_preset', 'get_template_preset' );
        $this->action( 'wp_ajax_preview_preset', 'preview_preset' );
        $this->action( 'wp_ajax_get_video_thumb_from_url', 'get_video_thumb_from_url' );
        $this->action( 'wp_ajax_send_preview_email', 'send_preview_email' );

        $this->action( 'wp_ajax_pause_campaign', 'pause_campaign' );
        $this->action( 'wp_ajax_resume_campaign', 'resume_campaign' );
        $this->action( 'wp_ajax_duplicate_campaign', 'duplicate_campaign' );

        $this->action( 'wp_ajax_get_campaign_people_data', 'get_campaign_people_data' );
        $this->action( 'wp_ajax_get_subscriber_campaign_activities', 'get_subscriber_campaign_activities' );
    }

    /**
     * Send editor data
     *
     * @return void
     */
    public function get_campaign_editor_data() {
        $this->verify_nonce( 'erp-email-campaign' );

        $data = campaign_editor()->get_editor_data( $_REQUEST['campaignId'] );

        $this->send_success( $data );
    }

    /**
     * Send Taxonomies and Terms list for a Post Type
     *
     * @return void
     */
    public function get_post_type_tax_terms() {
        $this->verify_nonce( 'erp-email-campaign' );

        $taxTerms = campaign_posts()->get_post_type_tax_terms( $_REQUEST['post_type'] );

        $this->send_success( $taxTerms );
    }

    /**
     * Get post result for Customizer - WP Post Editor
     *
     * @return void
     */
    public function get_posts_for_wp_posts_editor() {
        $this->verify_nonce( 'erp-email-campaign' );

        $results = campaign_posts()->get_posts( $_REQUEST['args'], false );

        $this->send_success( $results );
    }

    /**
     * Get post result for Customizer - WP Post Content
     *
     * @return void
     */
    public function get_posts_for_template() {
        $this->verify_nonce( 'erp-email-campaign' );

        $results = campaign_posts()->get_posts( $_REQUEST['args'] );

        $this->send_success( $results );
    }

    /**
     * Save campaign via ajax
     *
     * @return void
     */
    public function save_campaign() {
        $this->verify_nonce( 'erp-email-campaign' );

        $response = [
            'campaignId' => 0,
            'msg' => __( 'Something went wrong! Please try again.', 'erp-email-campaign' ),
        ];

        $limit_exceeded = apply_filters('psts_check_campaigns_limit', $response);

        if($limit_exceeded) {
            $this->send_success( ['msg' => 'You have exceeded your email campaigns limit.'] );

        }

        $campaign = campaign_editor()->save_campaign( $_POST );

        if ( $campaign->id ) {
            $response = [
                'campaignId' => $campaign->id,
                'page' => ecamp_admin_url( [ 'action' => 'edit', 'id' => $campaign->id ] ),
            ];

            if ( isset( erp_email_campaign()->statuses[ $campaign->status ] ) && erp_email_campaign()->statuses[ $campaign->status ]['can_edit'] ) {
                $response['msg'] = __( 'Campaign updated successfully', 'erp-email-campaign' );

            } else {
                $response['redirectTo'] = ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' );
            }
        }

        $this->send_success( $response );
    }

    /**
     * Get a template preset
     *
     * @return void
     */
    public function get_template_preset() {
        $this->verify_nonce( 'erp-email-campaign' );

        if ( empty( $_REQUEST['templateId'] ) ) {
            $this->send_error();
        }

        $template = templates()->get_preset( $_REQUEST['templateId'] );

        $this->send_success( $template );
    }

    /**
     * Save preview html as transient and return preview url
     *
     * @return void
     */
    public function preview_preset() {
        $this->verify_nonce( 'erp-email-campaign' );

        if ( empty( $_POST['html'] ) ) {
            $this->send_error();
        }

        $hash = md5( current_time( 'timestamp' ) );

        set_transient( 'ecamp-preview-preset-' . $hash, $_POST['html'], 10 * MINUTE_IN_SECONDS );

        $this->send_success( site_url( '?erp-email-campaign=1&preview-preset=' . $hash ) );
    }

    /**
     * Get youtube and vimeo video thumbnail image links
     *
     * @return void
     */
    public function get_video_thumb_from_url() {
        $this->verify_nonce( 'erp-email-campaign' );

        if ( empty( $_REQUEST['source'] ) || empty( $_REQUEST['videoId'] ) ) {
            $this->send_error();
        }

        $img_link = campaign_editor()->get_video_thumb_from_url( $_REQUEST['source'], $_REQUEST['videoId'] );

        $this->send_success( $img_link );
    }

    /**
     * Pause Campaign
     *
     * @return void
     */
    public function pause_campaign() {
        $this->verify_nonce( 'erp-email-campaign' );

        if ( empty( $_REQUEST['campaign_id'] ) || !erp_email_campaign()->pause_campaign( $_REQUEST['campaign_id'] ) ) {
            $this->send_error();
        }

        $campaign = erp_email_campaign()->get_campaign( $_REQUEST['campaign_id'] );

        if ( 'scheduled' === $campaign->send && ! empty( $campaign->deliver_at ) && ( strtotime( $campaign->deliver_at ) > current_time( 'timestamp' ) ) ) {
            $replace = 'scheduled';

        } else {
            $replace = 'in_progress';
        }

        $data = [
            'status' => sprintf( '<span class="list-table-status paused">%s</span>', __( 'Paused', 'erp-email-campaign' ) ),

            'resume' => sprintf(
                '<a href="#resume" title="%s" class="campaign-link-resume resume-this-campaign" data-campaign="%s">%s</a>',
                __( 'Resume this item', 'erp-email-campaign' ),
                $campaign->id,
                __( 'Resume', 'erp-email-campaign' )
            ),

            'edit' => sprintf(
                '<a href="%s" title="%s" class="campaign-link-edit">%s</a>',
                ecamp_admin_url( [ 'action' => 'edit', 'id' => $campaign->id ] ),
                __( 'Edit this item', 'erp-email-campaign' ),
                __( 'Edit', 'erp-email-campaign' )
            ),

            'name' => sprintf(
                '<a href="%s" class="campaign-link-name"><strong>%s</strong></a>',
                ecamp_admin_url( [ 'action' => 'edit', 'id' => $campaign->id ] ),
                wp_unslash( $_REQUEST['title'] )
            ),

            'replace'       => $replace,
            'replaceWith'   => 'paused'
        ];

        if ( 'inactive' === $campaign->status ) {
            $data['status'] = sprintf( '<span class="list-table-status paused">%s</span>', __( 'Paused', 'erp-email-campaign' ) );
            $data['replace'] = 'active';
            $data['replaceWith'] = 'paused';
        }

        $this->send_success( $data );
    }

    /**
     * Resume a campaign from pause state
     *
     * @since 1.0.0
     * @since 1.1.0 Add conditions for `erp_matches_segment`
     *
     * @return void
     */
    public function resume_campaign() {
        $this->verify_nonce( 'erp-email-campaign' );

        if ( empty( $_REQUEST['campaign_id'] ) || !erp_email_campaign()->resume_campaign( $_REQUEST['campaign_id'] ) ) {
            $this->send_error();
        }

        $campaign = erp_email_campaign()->get_campaign( $_REQUEST['campaign_id'] );

        if ( 'scheduled' === $campaign->send && ! empty( $campaign->deliver_at ) && ( strtotime( $campaign->deliver_at ) > current_time( 'timestamp' ) ) ) {
            $progress_bar = sprintf(
                '<span class="schedule-label"><i class="dashicons dashicons-clock"></i> %s: %s</span>',
                __( 'send at', 'erp-email-campaign' ),
                date( 'Y-m-d g:i a', strtotime( $campaign->deliver_at ) )
            );

            $status = sprintf(
                '<div id="campaign-id-%s" class="status-container"><span class="list-table-status %s">%s</span>%s</div>',
                $campaign->id,
                'scheduled',
                __( 'Scheduled', 'erp-email-campaign' ),
                $progress_bar
            );

            $replace_with = 'scheduled';

        } else {
            $status = sprintf( '<span class="list-table-status in_progress">%s</span>', __( 'In Progress', 'erp-email-campaign' ) );
            $replace_with = 'in_progress';
        }

        $data = [
            'status' => $status,

            'pause' => sprintf(
                '<a href="#pause" title="%s" class="campaign-link-pause pause-this-campaign" data-campaign="%s">%s</a>',
                __( 'Pause this item', 'erp-email-campaign' ),
                $campaign->id,
                __( 'Pause', 'erp-email-campaign' )
            ),

            'view' => sprintf(
                '<a href="%s" title="%s" class="campaign-link-view">%s</a>',
                ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ),
                __( 'View this item', 'erp-email-campaign' ),
                __( 'View', 'erp-email-campaign' )
            ),

            'name' => sprintf(
                '<a href="%s" class="campaign-link-name"><strong>%s</strong></a>',
                ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ),
                wp_unslash( $_REQUEST['title'] )
            ),

            'replace'       => 'paused',
            'replaceWith'   => $replace_with
        ];

        if ( 'active' === $campaign->status ) {
            $sent = $campaign->people->count();
            $event = $campaign->event;
            $status = '';

            if ( 'erp_create_new_people' === $event->action )  {
                $status = sprintf(
                    __( 'Sending <strong>%s%s</strong> after a new <strong>%s</strong> is added.<br> Sent to <strong>%d</strong> subscribers', 'erp-email-campaign' ),
                    $event->schedule_offset ? $event->schedule_offset . ' ' : '',
                    $event->schedule_type,
                    ucfirst( $event->arg_value ),
                    $sent
                );

            } else if ( 'erp_matches_segment' === $event->action ) {
                $save_search = erp_email_campaign()->save_search_list( $event->arg_value );

                if ( $event->schedule_event ) {
                    $status = sprintf(
                        __( 'Sending <strong>%s%s</strong> after a new or updated contact matches the segment <strong>%s</strong>.<br> Sent to <strong>%d</strong> subscribers', 'erp-email-campaign' ),
                        $event->schedule_offset . ' ',
                        $event->schedule_type,
                        ucfirst( $save_search['name'] ),
                        $sent
                    );

                } else {
                    $status = sprintf(
                        __( 'Sending <strong>%s</strong> when a new or updated contact matches the segment <strong>%s</strong>.<br> Sent to <strong>%d</strong> subscribers', 'erp-email-campaign' ),
                        $event->schedule_type,
                        ucfirst( $save_search['name'] ),
                        $sent
                    );
                }

            } else {
                $list = \WeDevs\ERP\CRM\Models\ContactGroup::find( $event->arg_value )->name;

                $status = sprintf(
                    __( 'Sending <strong>%s%s</strong> after someone subscribes to the list <strong>%s</strong>.<br> Sent to <strong>%d</strong> subscribers', 'erp-email-campaign' ),
                    $event->schedule_offset ? $event->schedule_offset . ' ' : '',
                    $event->schedule_type,
                    $list,
                    $sent
                );
            }

            $data['status'] = sprintf( '<span class="list-table-status active">%s</span>', $status );
            $data['replace'] = 'paused';
            $data['replaceWith'] = 'active';
        }

        $this->send_success( $data );
    }

    /**
     * Duplicate an exisitng campaign
     *
     * @return void
     */
    public function duplicate_campaign() {
        $this->verify_nonce( 'erp-email-campaign' );

        $campaign = erp_email_campaign()->duplicate_campaign( $_POST['campaign_id'] );

        if ( empty( $campaign->id ) ) {
            $data = __( 'Could not duplicate this campaign. Please try again.', 'erp-email-campaign' );
            $this->send_error( $data );
        }

        $campaign_url = ecamp_admin_url( [ 'action' => 'edit', 'id' => $campaign->id ] );
        $data = sprintf( __( 'Campaign duplicated successfully. <a href="%s">View campaign</a>', 'erp-email-campaign' ), $campaign_url );

        $this->send_success( $data );
    }

    /**
     * Get data for subscribers belongs to a campaign
     *
     * @return void
     */
    public function get_campaign_people_data() {
        $this->verify_nonce( 'erp-email-campaign' );

        $campaign = new Single_Campaign( $_REQUEST['id'] );

        $args = [];
        if ( !empty( $_REQUEST['number'] ) ) {
            $args['number'] = absint( $_REQUEST['number'] );
        }

        if ( !empty( $_REQUEST['offset'] ) ) {
            $args['offset'] = absint( $_REQUEST['offset'] );
        }

        if ( !empty( $_REQUEST['s'] ) ) {
            $args['s'] = $_REQUEST['s'];
        }

        if ( !empty( $_REQUEST['email-status'] ) ) {
            $args['email-status'] = $_REQUEST['email-status'];
        }

        if ( !empty( $_REQUEST['group'] ) ) {
            $args['group'] = absint( $_REQUEST['group'] );
        }

        $data = $campaign->get_recipient_list_table_data( $args );

        $this->send_success( $data );
    }

    /**
     * Send preview email for a campaign
     *
     * @return void
     */
    public function send_preview_email() {
        $this->verify_nonce( 'erp-email-campaign' );

        if ( empty( $_POST['email'] ) || !is_email( $_POST['email'] ) ) {
            $this->send_error( __( 'Invalid email address.', 'erp-email-campaign' ) );
        }

        $campaign = \WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign::find( $_POST['campaign_id'] );

        if ( empty( $campaign ) ) {
            $this->send_error( __( 'Invalid campaign', 'erp-email-campaign' ) );
        }

        // prepare mail data
        $to = $_POST['email'];
        $subject = __( 'Preview campaign', 'erp-email-campaign' ) . ': ' . $campaign->email_subject;
        $message = templates()->render_email( $_POST['campaign_id'], 0, '' );
        $unsubscribe_link = '';

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

        $headers .= "Reply-To: \"{$campaign->reply_to_name}\" <{$campaign->reply_to_email}>" . "\r\n";

        $custom_headers = [];

        // send email
        $mail_sent = erp_mail( $to, $subject, $message, $headers, [], $custom_headers );

        if ( $mail_sent ) {
            $this->send_success( __( 'Mail sent successfully', 'erp-email-campaign' ) );
        } else {
            $this->send_error( __( 'Mail sending failed', 'erp-email-campaign' ) );
        }
    }

    public function get_subscriber_campaign_activities() {
        $this->verify_nonce( 'erp-email-campaign' );

        if ( empty( $_GET['campaign_id'] ) || empty( $_GET['subscriber_id'] ) ) {
            $this->send_error( __( 'Invalid operation', 'erp-email-campaign' ) );
        }

        $data = erp_email_campaign()->get_subscriber_campaign_activities( $_GET['campaign_id'], $_GET['subscriber_id'] );

        if ( is_wp_error( $data ) ) {
            $this->send_error( $data->get_error_message() );
        }

        $this->send_success( $data );
    }
}

new Campaign_Ajax();
