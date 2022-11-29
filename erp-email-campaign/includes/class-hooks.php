<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\CRM\EmailCampaign\Models\People as PeopleModel;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Filter and action hooks
 *
 * @since 1.1.0
 */
class Hooks {

    use Hooker;

    /**
     * Class constructor
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'erp_subscription_unsubscribe', 'unsubscribe_people', 10, 2 );
        $this->action( 'erp_subscription_unsubscribe_group_list', 'add_save_search_list_names', 10, 3 );
        $this->filter( 'erp_subscription_lists_subscriber_belongs_to', 'save_searches_subscriber_belongs_to', 10, 3 );
        $this->action( 'erp_subscription_edit', 'subscrition_edit', 10, 3 );
        $this->action( 'admin_bar_menu', 'show_cron_activity_status', 999999 );
        $this->action( 'shutdown', 'shutdown' );
    }

    /**
     * Unsubsribe from save searches
     *
     * @since 1.1.0
     *
     * @param array  $args
     * @param object $subsciption_obj
     *
     * @return void
     */
    public function unsubscribe_people( $args, $subsciption_obj ) {
        if ( ! empty( $args['campaign'] )  ) {
            $campaign = PeopleModel::where( 'hash', $args['campaign'] )->first()->campaign;
            $save_searches = $campaign->campaignLists()->where( 'type', 'save_searches' )->get();

            if ( $save_searches->count() ) {
                $save_searches_ids = wp_list_pluck( $save_searches, 'type_id' );
                $save_searches_ids = $save_searches_ids->toArray();

                erp_email_campaign()->update_subscription(
                   $campaign->id,
                   $subsciption_obj->people_id,
                   [],
                   [],
                   [],
                   $save_searches_ids
                );
            }
        }
    }

    /**
     * Add save search list to the contact lists that a subscriber belongs to
     *
     * @since 1.1.0
     *
     * @param array  $group_names
     * @param array  $args
     * @param object $subscription_obj
     */
    public function add_save_search_list_names( $group_names, $args, $subscription_obj ) {
        if ( ! empty( $args['campaign'] )  ) {
            $campaign_people = PeopleModel::where( 'hash', $args['campaign'] )->first();
            $campaign = $campaign_people->campaign;
            $save_searches = $campaign->campaignLists()->where( 'type', 'save_searches' )->get();

            if ( $save_searches->count() ) {
                $save_searches_ids = wp_list_pluck( $save_searches, 'type_id' );
                $save_searches_ids = $save_searches_ids->toArray();
                $save_searches = \WeDevs\ERP\CRM\Models\SaveSearch::whereIn( 'id', $save_searches_ids )->get();

                $save_searches->each( function ( $search ) use ( &$group_names, $campaign_people ) {
                    $people_belongs_to_search = erp_get_peoples( [
                        'type' => 'contact',
                        'erpadvancefilter' => $search->search_val,
                        'test_user' => $campaign_people->people_id,
                        'count' => true
                    ] );

                    if ( $people_belongs_to_search ) {
                        $group_names[] = $search->search_name;
                    }

                } );
            }
        }

        return $group_names;
    }

    /**
     * Returns the save searches that a subscriber belongs to
     *
     * @since 1.1.0
     *
     * @param array   $lists
     * @param boolean $with_private
     * @param object  $subscription_obj
     *
     * @return array
     */
    public function save_searches_subscriber_belongs_to( $lists, $with_private, $subscription_obj ) {
        $save_searches = \WeDevs\ERP\CRM\Models\SaveSearch::select( 'id', 'search_name', 'search_val' )
                            ->get();

        if ( ! $save_searches->count() ) {
            return $lists;
        }

        // NOTE: checking only contacts
        $supported_searches = erp_crm_get_serach_key( 'contact' );

        $save_searches = $save_searches->filter( function ( &$save_search ) use ( $subscription_obj, $supported_searches ) {
            $search_key = preg_replace( '/\[\]=.*/', '', $save_search->search_val );

            if ( ! array_key_exists( $search_key , $supported_searches ) ) {
                return false;
            }

            $people_belongs_to_search = erp_get_peoples( [
                'type' => 'contact',
                'erpadvancefilter' => $save_search->search_val,
                'test_user' => $subscription_obj->people_id
            ] );

            if ( ! $people_belongs_to_search ) {
                return false;
            }

            $result = \WeDevs\ORM\Eloquent\Facades\DB::table( 'erp_crm_save_search_unsubscribers' )
                     ->select( 'unsubscribed_at' )
                     ->where( 'user_id', $subscription_obj->people_id )
                     ->where( 'search_id', $save_search->id )
                     ->first();

            $unsubscribed_at = ! empty( $result->unsubscribed_at ) ? $result->unsubscribed_at : null;

            $save_search->setAttribute( 'name', $save_search->search_name );
            $save_search->setAttribute( 'user_id', $subscription_obj->people_id );
            $save_search->setAttribute( 'unsubscribe_at', $unsubscribed_at );

            return true;
        } );

        if ( $save_searches->count() ) {
            $lists['save_search'] = $save_searches;
        }

        return $lists;
    }

    /**
     * Edit subscription action hook function
     *
     * @since 1.1.0
     *
     * @param array  $form_data        Submitted form data
     * @param array  $contact_lists    Contact groups and save searches that a subscriber belongs to
     * @param object $subscription_obj Subscription class object
     *
     * @return void
     */
    public function subscrition_edit( $form_data, $contact_lists, $subscription_obj ) {
        if ( empty( $contact_lists['save_search'] ) ) {
            return;
        }

        $save_searces = $contact_lists['save_search'];
        $campaign_id = 0;
        $people_id = $subscription_obj->people_id;
        $sub_group = [];
        $unsub_group = [];
        $sub_search = [];
        $unsub_search = [];
        $managing = true;

        $save_searces->each( function ( $save_search ) use ( $form_data, &$sub_search, &$unsub_search ) {
            if ( ! empty( $form_data['save_search'][ $save_search->id ] ) ) {
                $sub_search[] = $save_search->id;
            } else {
                $unsub_search[] = $save_search->id;
            }
        } );

        erp_email_campaign()->update_subscription( $campaign_id, $people_id, $sub_group, $unsub_group, $sub_search, $unsub_search, $managing );
    }

    /**
     * Cron activity indicator
     *
     * Show cron active/inactive status in admin menu when debug mode is on
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function show_cron_activity_status() {
        global $wp_admin_bar;

        if ( ! current_user_can( 'manage_erp_email_campaign' ) || ! Logger::$is_logger_on ) {
            return;
        }

        $menu_classes = [ 'erp-email-campaign-cron-activity' ];

        $people_on_queue = Models\PeopleQueue::count();

        if ( ! $people_on_queue ) {
            $info = __( 'No subscriber on queue', 'erp-email-campaign' );

        } else {
            $schedule = wp_next_scheduled( 'erp_email_campaign_cron_send_email' );

            if ( empty( $schedule ) ) {
                $icon = 'warning';
                $info = __( 'You have subscriber on queue but cron is inactive', 'erp-email-campaign' );
                $menu_classes[] = 'inactive';

            } else {
                $info = __( 'Cron is active and working', 'erp-email-campaign' );
                $icon = 'yes';
                $menu_classes[] = 'active';
            }
        }

        $icon       = isset( $icon ) ? sprintf( '<span class="dashicons dashicons-%s"></span> ', $icon ) : '';
        $menu_title = $icon . __( 'Email Campaign', 'erp-email-campaign' );

        /* Add the main siteadmin menu item */
        $wp_admin_bar->add_menu( [
            'id'     => 'erp-email-campaign',
            'parent' => 'top-secondary',
            'title'  => $menu_title,
            'meta'   => [ 'class' => implode( ' ' , $menu_classes ) ],
        ] );

        $wp_admin_bar->add_menu( [
            'parent' => 'erp-email-campaign',
            'id'     => 'erp-email-campaign-info',
            'title'  => $info
        ] );

        wp_enqueue_style( 'erp-email-campaign-admin-bar', WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/erp-email-campaign-admin-bar.css', [], WPERP_EMAIL_CAMPAIGN_VERSION );
    }

    /**
     * WP shutdown hook
     *
     * Make sure to set next schedule even after a fatal error happen
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function shutdown() {
        $error = error_get_last();

        // fatal error, E_ERROR === 1
        if ( $error['type'] === E_ERROR ) {
            ecamp_set_next_email_schedule();
        }
    }

}

new Hooks();
