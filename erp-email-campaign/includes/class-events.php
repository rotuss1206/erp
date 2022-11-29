<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\CRM\EmailCampaign\Models\Events as EventModel;
use WeDevs\ERP\Framework\Traits\Hooker;

class Events {

    use Hooker;

    /**
     * Class constructor
     *
     * @since 1.0.0
     * @since 1.1.0 Add hooks for `erp_matches_segment`
     *              Hook method after edit subscription
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'erp_crm_create_contact_subscriber', 'create_contact_subscriber' );
        $this->action( 'erp_crm_edit_contact_subscriber', 'create_contact_subscriber' );
        $this->action( 'erp_create_new_people', 'erp_create_new_people' );
        $this->action( 'erp_create_new_people', 'erp_matches_segment' );
        $this->action( 'erp_update_people', 'erp_matches_segment' );
    }

    /**
     * On create new contact subscriber
     *
     * @since 1.0.0
     * @since 1.1.0 Do not proceed if ERP_IS_IMPORTING is true
     *              Add subscriber to queue only if status is `subscribe`
     *
     * @param object $subscriber
     *
     * @return void
     */
    public function create_contact_subscriber( $subscriber ) {
        if ( defined( 'ERP_IS_IMPORTING' ) && ERP_IS_IMPORTING ) {
            return;
        }

        if ( 'subscribe' !== $subscriber->status ) {
            return;
        }

        $event = EventModel::where( 'action', 'erp_crm_create_contact_subscriber' )
                           ->where( 'arg_value', $subscriber->group_id )
                           ->first();

        if ( $event && $event->campaign()->where( 'status', 'active' )->first() ) {
            $this->add_to_queue( $event, $subscriber->user_id );
        }
    }

    /**
     * On create new people
     *
     * @since 1.0.0
     * @since 1.1.0 Do not proceed if ERP_IS_IMPORTING is true
     *
     * @param int $people_id
     *
     * @return void
     */
    public function erp_create_new_people( $people_id ) {
        if ( defined( 'ERP_IS_IMPORTING' ) && ERP_IS_IMPORTING ) {
            return;
        }

        $erp_people = erp_get_people( $people_id );

        $types = $erp_people->types;

        foreach ( $types as $type ) {
            $event = EventModel::where( 'action', 'erp_create_new_people' )
                               ->where( 'arg_value', $type )
                               ->first();

            if ( $event && $event->campaign()->where( 'status', 'active' )->first() ) {
                $this->add_to_queue( $event, $people_id );
            }

        }
    }

    /**
     * Create/Update people that matches a segment
     *
     * @since 1.1.0
     *
     * @param int $people_id
     *
     * @return void
     */
    public function erp_matches_segment( $people_id ) {
        if ( defined( 'ERP_IS_IMPORTING' ) && ERP_IS_IMPORTING ) {
            return;
        }

        $DB = \WeDevs\ORM\Eloquent\Facades\DB::instance();
        $prefix = $DB->db->prefix;

        $events = $DB->table( 'erp_crm_email_campaigns as campaign' )
                      ->select( 'event.*' )
                      ->leftJoin(
                        "{$prefix}erp_crm_email_campaigns_events as event",
                        'campaign.id', '=', 'event.campaign_id'
                      )
                      ->where( 'campaign.status', 'active' )
                      ->whereNull( 'campaign.deleted_at' )
                      ->where( 'event.action', 'erp_matches_segment' )
                      ->get();

        if ( !empty( $events ) ) {
            foreach ( $events as $event ) {
                $save_search = \WeDevs\ERP\CRM\Models\SaveSearch::where( 'id', $event->arg_value )->first();

                if ( !empty( $save_search ) ) {
                    $people_belongs_to_search = erp_get_peoples( [
                        'type' => 'contact',
                        'erpadvancefilter' => $save_search->search_val,
                        'test_user' => $people_id
                    ] );

                    if ( $people_belongs_to_search ) {
                        $people_queue = \WeDevs\ERP\CRM\EmailCampaign\Models\PeopleQueue::where( 'campaign_id', $event->campaign_id )->where( 'people_id', $people_id )->count();

                        if ( empty( $people_queue ) ) {
                            $this->add_to_queue( $event, $people_id );
                        }
                    }
                }
            }
        }
    }

    /**
     * Add people to queue
     *
     * @param object $event
     * @param int    $people_id
     *
     * @return void
     */
    protected function add_to_queue( $event, $people_id ) {
        $send_at = current_time( 'mysql' );

        if ( 'immediately' !== $event->schedule_type ) {
            $send_at = strtotime( $send_at . ' +' . $event->schedule_offset . ' ' . $event->schedule_type );
            $send_at = date( 'Y-m-d H:i:s', $send_at );
        }

        erp_email_campaign()->queue_people( $event->campaign_id, [], [ $people_id ], $send_at );
    }

}

new Events();
