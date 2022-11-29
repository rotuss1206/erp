<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\CRM\EmailCampaign\Models\Templates as TemplatesModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\URL as URLModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\People as PeopleModel;
use WeDevs\ERP\Framework\Traits\Hooker;

class Frontend {

    use Hooker;

    /**
     * PeopleModel Object
     *
     * @var object
     */
    protected $people;

    /**
     * Subscriber Object or People Details informations
     *
     * @var object
     */
    protected $subscriber;

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
        /*= View Email in browser =*/
        if ( !empty( $_GET['erp-email-campaign'] ) && !empty( $_GET['view-email-in-browser'] ) && !empty( $_GET['campaign'] ) ) {
            $this->action( 'init', 'view_email_in_browser' );
        }

        if ( !empty( $_GET['erp-email-campaign'] ) && !empty( $_GET['preview-preset'] ) ) {
            $this->action( 'init', 'preview_preset' );
        }

        /*= URL/Unsubscribe/Edit Subscriptions =*/
        if ( !empty( $_GET['erp-email-campaign'] ) ) {

            // url
            if ( !empty( $_GET['image'] ) && $this->is_valid_people( $_GET['user'], false ) ) {
                $this->action( 'init', 'email_open_tracker' );

            } else if ( !empty( $_GET['user'] ) && !empty( $_GET['url'] ) && $this->is_valid_people( $_GET['user'], false ) ) {
                $this->action( 'init', 'url_tracker' );

            // unsubscribe link
            } else if ( !empty( $_GET['unsubscribe'] ) && $this->is_valid_people( $_GET['unsubscribe'] ) ) {
                $this->action( 'init', 'redirect_to_unsubscribe' );

            // edit subscription link
            } else if ( !empty( $_GET['edit-subscription'] ) && $this->is_valid_people( $_GET['edit-subscription'] ) ) {
                $this->action( 'init', 'redirect_to_edit_subscription' );
            }

        }
    }

    /**
     * Checks if hash is in people table
     *
     * Also set people data and custom campaign page
     *
     * @param string  $hash
     * @param boolean $set_custom_page
     *
     * @return boolean
     */
    private function is_valid_people( $hash, $set_custom_page = true ) {
        $is_valid = false;

        if ( $people = PeopleModel::where( 'hash', $hash )->first() ) {
            $this->people = $people;
            $this->subscriber = erp_get_people_by( 'id', $people->people_id );

            // load custom page
            if ( $set_custom_page ) {
                $this->action( 'pre_get_posts', 'set_campaign_post' );
            }

            $is_valid = true;
        }

        return $is_valid;
    }

    /**
     * View email in browser
     *
     * @return void
     */
    public function view_email_in_browser() {
        $campaign_id = $_GET['campaign'];
        $templates_model = TemplatesModel::where( 'campaign_id', $campaign_id )->first();

        if ( $templates_model ) {
            $email_subject = erp_email_campaign()->get_campaign( $campaign_id )->email_subject;

            // get responsive styles
            ob_start();
            echo file_get_contents( WPERP_EMAIL_CAMPAIGN_PATH . '/assets/css/email-template-styles-responsive.css' );
            $responsive_styles = ob_get_clean();


            $html = templates()->render_shortcodes( $templates_model->html, $campaign_id );

            $html = preg_replace( '/\.\.\/wp-content\/plugins\/erp-email-campaign/', WPERP_EMAIL_CAMPAIGN_URL, $html );

            $html = wp_unslash( $html );

            include_once WPERP_EMAIL_CAMPAIGN_VIEWS . '/view-email-in-browser.php';
        }

        exit;
    }

    public function preview_preset() {
        // get responsive styles
        ob_start();
        echo file_get_contents( WPERP_EMAIL_CAMPAIGN_PATH . '/assets/css/email-template-styles-responsive.css' );
        $responsive_styles = ob_get_clean();

        $html = wp_unslash( get_transient( 'ecamp-preview-preset-' . $_GET['preview-preset'] ) );
        $html = preg_replace( '/\.\.\/wp-content\/plugins\/erp-email-campaign/', WPERP_EMAIL_CAMPAIGN_URL, $html );
        $primary_styles = WPERP_EMAIL_CAMPAIGN_URL . '/assets/css/email-template-styles.css';

        include WPERP_EMAIL_CAMPAIGN_VIEWS . '/email.php';

        exit;
    }

    /**
     * Track email open time by 1x1px image
     *
     * In case of sending test emails, we don't have user hash.
     * So, we'll ignore db update in that case.
     *
     * @return void
     */
    public function email_open_tracker() {
        $current_time = current_time( 'mysql' );

        if ( !empty( $_GET['user'] ) && $this->is_valid_people( $_GET['user'], false ) && empty( $this->people->open ) ) {
            $this->people->update( [ 'open' => $current_time ] );
        }

        Models\OpenStat::insert( [
            'campaign_id'   => $this->people->campaign_id,
            'people_id'     => $this->people->people_id,
            'opened_at'     => $current_time,
        ] );

        // add activity
        erp_email_campaign()->save_activity( $this->people->people_id, $this->people->campaign_id );

        header( 'Cache-Control: no-store, no-cache, must-revalidate' );
        header( 'Cache-Control: post-check=0, pre-check=0', false );
        header( 'Pragma: no-cache' );
        header( 'Content-type: image/png' );

        $image = WPERP_EMAIL_CAMPAIGN_PATH . '/assets/images/one-by-one-pixel.png';
        $handle = fopen( $image, 'r' );

        if ( !$handle ) {
            exit;
        }

        $contents = fread( $handle, filesize( $image ) );
        fclose( $handle );
        echo $contents;

        exit;
    }

    /**
     * URL click tracker
     *
     * @since 1.0.0
     * @since 1.1.0 Update open stat as well as click stat
     *
     * @return void
     */
    public function url_tracker() {
        $campaign_id    = $this->people->campaign_id;
        $people_id      = $this->people->people_id;
        $url_hash       = $_GET['url'];

        $templates_model = TemplatesModel::select( 'links' )->where( 'campaign_id', $campaign_id );

        // in case there is no record exists in db for this campaign,
        // we'll just redirect user to home
        if ( !$templates_model->count() ) {
            wp_redirect( site_url( '/' ), 302 );
        }

        $links = json_decode( $templates_model->first()->links, true );

        if ( array_key_exists( $url_hash, $links ) ) {
            // update url stats
            $url = $links[ $url_hash ];

            $url_model = URLModel::firstOrCreate( [ 'url' => $url ] );

            $stat_model = $url_model->stats()->create( [
                'url_id' => $url_model->id,
                'campaign_id' => $campaign_id,
                'people_id' => $people_id,
                'clicked_at' => current_time( 'mysql' ),
            ] );

            /**
             * Update people model - click and open stat
             * Someone may open an email with image disabled but click a link.
             * In that case we have to update the open stat also since `email_open_tracker`
             * method will not execute
             */
            $people = PeopleModel::where( 'campaign_id', $campaign_id )->where( 'people_id', $people_id )->first();

            if ( ! empty( $people ) ) {

                if ( empty( $people->open ) ) {
                    $people->open = current_time( 'mysql' );
                }

                $people->clicked = 1;

                $people->save();
            }


            // add activity
            erp_email_campaign()->save_activity( $people_id, $campaign_id );

            // build url with utm params
            $campaign = erp_email_campaign()->get_campaign( $campaign_id );

            if ( !empty( $campaign ) && !empty( $campaign->campaign_name ) ) {
                $url = add_query_arg([
                    'utm_source' => 'newsletter',
                    'utm_medium' => 'email',
                    'utm_campaign' => $campaign->campaign_name,
                ], $url );
            }

            wp_redirect( $url, 302 );

        } else {
            wp_redirect( site_url( '/' ), 302 );
        }

        exit;
    }

    /**
     * Set a special page for campaign
     *
     * @param void
     */
    public function set_campaign_post( $query ) {
        global $wp_query;

        if ( !$query->is_main_query() )
            return;

        if ( empty( $query->query ) ) {
            $query-> set('post_type' ,'erp-email-campaign');
            remove_all_actions ( '__after_loop');
        }
    }

    /**
     * Add debug logger
     * Redirect to ERP core unsubscribe page
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function redirect_to_unsubscribe() {
        $sub_page_id = absint( erp_get_option( 'page_id', 'erp_settings_erp-crm_subscription', 0 ) );
        $link        = get_permalink( $sub_page_id );

        $people_hash = erp_people_get_meta( $this->people->people_id, 'hash', true );

        if ( empty( $people_hash ) ) {
            $people_hash = sha1( microtime() . 'erp-unique-hash-id' . $this->people->people_id );

            erp_people_update_meta( $this->people->people_id, 'hash', $people_hash );
        }

        $groups      = erp_email_campaign()->get_campaign_contact_groups( $this->people->campaign_id );
        $group_ids   = wp_list_pluck( $groups, 'id' );
        $groups      = \WeDevs\ERP\CRM\Models\ContactGroup::select('id')->whereIn( 'id', $group_ids )->whereNull( 'private' )->get();

        if ( ! empty( $groups ) ) {
            $group_ids = wp_list_pluck( $groups->toArray(), 'id' );

            $link = add_query_arg( [
                'erp-subscription-action' => 'unsubscribe',
                'id'                      => $people_hash,
                'g'                       => implode( ':', $group_ids ),
                'campaign'                => $this->people->hash
            ], $link );

            wp_safe_redirect( $link, 302 );
            exit;
        }
    }

    /**
     * Redirecto to ERP core edit subscription page
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function redirect_to_edit_subscription() {
        $sub_page_id = absint( erp_get_option( 'page_id', 'erp_settings_erp-crm_subscription', 0 ) );
        $link        = get_permalink( $sub_page_id );

        $people_hash = erp_people_get_meta( $this->people->people_id, 'hash', true );

        if ( empty( $people_hash ) ) {
            $people_hash = sha1( microtime() . 'erp-unique-hash-id' . $this->people->people_id );

            erp_people_update_meta( $this->people->people_id, 'hash', $people_hash );
        }

        $link = add_query_arg( [
            'erp-subscription-action' => 'edit',
            'id'                      => $people_hash,
        ], $link );

        wp_safe_redirect( $link, 302 );
        exit;
    }

}

new Frontend();
