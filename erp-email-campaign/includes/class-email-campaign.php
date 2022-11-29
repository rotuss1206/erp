<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign as CampaignModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\CampaignList as CampaignListModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\Events as EventsModel;
use WeDevs\ERP\CRM\EmailCampaign\Models\People as PeopleModel;
use \WeDevs\ORM\Eloquent\Facades\DB;

class Email_Campaign {

    /**
     * Valid campaign statuses
     *
     * @var array
     */
    public $statuses = [];

    /**
     * Total items found in current sql
     *
     * @var integer
     */
    public $total_items = 0;

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
        $this->statuses = [
            'all'           => [
                'label'     => __( 'All', 'erp-email-campaign' ),
                'can_pause' => 0,
                'can_edit'  => 0
            ],

            'in_progress'   => [
                'label'     => __( 'In Progress', 'erp-email-campaign' ),
                'can_pause' => 1,
                'can_edit'  => 0
            ],

            'scheduled'     => [
                'label'     => __( 'Scheduled', 'erp-email-campaign' ),
                'can_pause' => 1,
                'can_edit'  => 0
            ],

            'paused'        => [
                'label'     => __( 'Paused', 'erp-email-campaign' ),
                'can_pause' => 0,
                'can_edit'  => 1
            ],

            'sent'          => [
                'label'     => __( 'Sent', 'erp-email-campaign' ),
                'can_pause' => 0,
                'can_edit'  => 0
            ],

            'draft'         => [
                'label'     => __( 'Draft', 'erp-email-campaign' ),
                'can_pause' => 0,
                'can_edit'  => 1
            ],

            'active'        => [
                'label'     => __( 'Active', 'erp-email-campaign' ),
                'can_pause' => 1,
                'can_edit'  => 0
            ],

            'inactive'      => [
                'label'     => __( 'Inactive', 'erp-email-campaign' ),
                'can_pause' => 0,
                'can_edit'  => 1
            ],

            'trashed'       => [
                'label'     => __( 'Trashed', 'erp-email-campaign' ),
                'can_pause' => 0,
                'can_edit'  => 0
             ]
        ];
    }

    /**
     * Campaigns list with filters
     *
     * @param array $args
     *
     * @return object
     */
    public function get_campaigns( $args = [] ) {

        $defaults = [
            'columns'    => [],
            'per_page'   => 20,
            'offset'     => 0,
            'orderby'    => 'created_at',
            'order'      => 'DESC',
            'count'      => false,
            'status'     => 'all'
        ];

        $args        = wp_parse_args( $args, $defaults );
        $cache_key   = 'erp-crm-email-camp' . '-' . md5( serialize( $args ) );
        $items       = [];

        $campaigns = new CampaignModel();

        // select columns if defined
        if ( !empty( $args['columns'] ) ) {
            $campaigns = $campaigns->select( $args['columns'] );
        }

        // search filter
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s = $args['s'];
            $campaigns = $campaigns->where( 'email_subject', 'LIKE', "%$arg_s%" );
        }

        // send type
        if ( !empty( $args['send'] ) ) {
            $campaigns = $campaigns->where( 'send', $args['send'] );
        }

        // status filter
        if ( 'trashed' === $args['status'] ) {
            $campaigns = $campaigns->onlyTrashed();
        } else {
            $campaigns = $campaigns->status( $args['status'] );
        }

        // count the total items
        $this->total_items = $campaigns->count();

        if ( $this->total_items ) {
            // pagination
            $campaigns = $campaigns->skip( $args['offset'] )->take( $args['per_page'] );

            // get results
            $items = $campaigns->orderBy( $args['orderby'], $args['order'] )->get();
        }

        return $items;
    }

    /**
     * A campaign with details
     *
     * @param int $campaign_id
     *
     * @return object
     */
    public function get_campaign( $campaign_id ) {
        return CampaignModel::find( $campaign_id );
    }

    /**
     * Campaign Lists
     *
     * Contact Groups and Save Search list
     *
     * @since 1.0.0
     * @since 1.1.0 Get campaign with where and first instead of find. For some reason
     *              find doesn't work with WP_CLI
     *
     * @return array
     */
    public function get_campaign_lists( $campaign_id = 0 ) {
        if ( $campaign_id ) {
            $selected_lists = CampaignModel::where( 'id', $campaign_id )->first();

            $selected_contact_groups = $selected_lists->campaignLists( 'contact_groups' )->count() ? $selected_lists->campaignLists( 'contact_groups' )->idsOnly() : [];
            $selected_save_search    = $selected_lists->campaignLists( 'save_searches' )->count() ? $selected_lists->campaignLists( 'save_searches' )->idsOnly() : [];
        }

        $list = [
            'contact_groups' => [
                'title' => __( 'Contact Groups', 'erp-email-campaign' ),
                // ** ids must be string ** //
                'selected' => $campaign_id ? $selected_contact_groups : []
            ],

            'save_searches' => [
                'title' => __( 'Saved Searches', 'erp-email-campaign' ),
                'selected' => $campaign_id ? $selected_save_search : []
            ],
        ];

        $contact_groups = erp_crm_get_contact_groups( [
            'number'     => -1,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        foreach ( $contact_groups as $i => $group ) {
            $list['contact_groups']['lists'][$i] = [
                'id'    => $group->id,
                'name'  => $group->name,
                'count' => $group->subscriber
            ];
        }

        $list['save_searches']['lists'] = $this->save_search_list();

        return apply_filters( 'erp_crm_email_campaign_contact_lists', $list );
    }

    /**
     * Genrates the list for save searches
     *
     * @since 1.0.0
     * @since 1.1.0 Add optional $id param
     *
     * @param int $id
     *
     * @return array
     */
    public function save_search_list( $id = 0 ) {
        $list = [];

        if ( !empty( $id ) ) {
            $save_searches = \WeDevs\ERP\CRM\Models\SaveSearch::where( 'id', $id )->first();

            if ( !empty( $save_searches ) ) {
                $list = [
                    'id'    => $save_searches->id,
                    'name'  => $save_searches->search_name,
                    'query' => $save_searches->search_val,
                    'count' => erp_get_peoples( [
                        'type'              => 'contact',
                        'erpadvancefilter'  => $save_searches->search_val,
                        'count'             => true
                    ] )
                ];
            }

        } else {
            $save_searches = \WeDevs\ERP\CRM\Models\SaveSearch::select( 'id', 'search_name', 'search_val' )
                             ->where( 'global', 1 )
                             ->where( 'type', 'contact' )
                             ->get();

            if ( !empty( $save_searches ) ) {
                foreach ( $save_searches as $search ) {
                    $list[] = [
                        'id'    => $search->id,
                        'name'  => $search->search_name,
                        'query' => $search->search_val,
                        'count' => erp_get_peoples( [
                            'type'              => 'contact',
                            'erpadvancefilter'  => $search->search_val,
                            'count'             => true
                        ] )
                    ];
                }
            }
        }

        return $list;
    }

    /**
     * Save campaign data
     *
     * @param int   $campaign_id
     * @param array $campaign
     * @param array $selected_lists
     * @param array $email_template
     * @param bool  $log_campaign
     *
     * @return object Eloquent EmailCampaign model
     */
    public function save_campaign( $campaign_id, $data, $selected_lists, $email_template, $log_campaign = false ) {
        $campaign = CampaignModel::firstOrNew( [ 'id' => $campaign_id ] );

        $values = [
            'email_subject'     => wp_unslash( $data['email_subject'] ),
            'status'            => $data['status'],
            'sender_name'       => $data['sender_name'],
            'sender_email'      => $data['sender_email'],
            'reply_to_name'     => $data['reply_to_name'],
            'reply_to_email'    => $data['reply_to_email'],
            'send'              => $data['send'],
            'campaign_name'     => wp_unslash( $data['campaign_name'] ),
            'deliver_at'        => $data['deliver_at'],
        ];

        if ( $campaign->exists ) {
            $new_campaign = false;
            $old_status = $campaign->status;

            $campaign->update( $values );

            $new_status = $campaign->status;
        } else {
            $new_campaign = true;

            $campaign->setRawAttributes( $values, true );
            $campaign->save();
        }

        $campaign_id = $campaign->id;

        // Save actions
        if ( 'automatic' === $data['send'] ) {
            $event = EventsModel::where( 'campaign_id', $campaign_id );

            $values = [
                'campaign_id'       => $campaign_id,
                'action'            => $data['event']['action'],
                'arg_value'         => $data['event']['arg_value'],
                'schedule_type'     => $data['event']['schedule_type'],
                'schedule_offset'   => 'immediately' === $data['event']['schedule_type'] ? 0 : $data['event']['schedule_offset'],
            ];

            if ( $event->count() ) {
                $event->update( $values );
            } else {
                EventsModel::insert( $values );
            }
        }

        /* save campaign lists */
        // remove existing lists first
        $campaign_lists = $campaign->campaignLists();
        $campaign_lists->delete();

        // save lists
        if ( !empty( $selected_lists ) ) {
            $lists = [];

            foreach ( $selected_lists as $type => $selected_list ) {
                foreach ( $selected_list as $type_id ) {
                    $lists[] = new CampaignListModel( [ 'type' => $type, 'type_id' => $type_id ] );
                }
            }

            $campaign_lists->saveMany( $lists );
        }

        // save template
        templates()->save_template( $campaign_id, $email_template );

        // save listed people id
        if ( ( 'automatic' !== $data['send'] ) && ( 'draft' !== $data['status'] ) ) {
            $this->queue_people( $campaign_id, $selected_lists, [], $data['deliver_at'] );
        }

        if ( 'draft' === $data['status'] ) {
            $campaign->peopleQueue()->withTrashed()->forceDelete();
        }

        if ( $new_campaign || $log_campaign ) {
            $args = [];

            if ( $new_campaign ) {
                $args['message'] = sprintf(
                    '<a href="%s">%s</a> has been created.',
                    ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ),
                    $campaign->email_subject
                );

            } else {

                if ( 'draft' === $new_status && 'draft' !== $old_status ) {
                    $args['message'] = sprintf(
                        '<a href="%s">%s</a>: changed status to draft.',
                        ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ),
                        $campaign->email_subject
                    );

                } else if ( 'draft' === $old_status && 'in_progress' === $new_status )  {
                    $args['message'] = sprintf(
                        '<a href="%s">%s</a>: starts sending email as standard campaign.',
                        ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ),
                        $campaign->email_subject
                    );

                } else if ( 'draft' === $old_status && 'active' === $new_status )  {
                    $args['message'] = sprintf(
                        '<a href="%s">%s</a>: starts sending email as automatic newsletter campaign.',
                        ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ),
                        $campaign->email_subject
                    );
                }

            }

            $this->audit_log( $args );
        }

        return $campaign;
    }

    /**
     * Store people ids belongs to selected list
     *
     * @param int       $campaign_id
     * @param array     $selected_list
     * @param array     $people_ids
     * @param string    $send_at date time
     *
     * @return void
     */
    public function queue_people( $campaign_id, $selected_lists, $people_ids = [], $send_time = null ) {
        $campaign = $this->get_campaign( $campaign_id );

        $people_model = $campaign->people();
        $queue_model  = $campaign->peopleQueue();

        // for automated newsletters, we'll supply the people_ids
        if ( empty( $people_ids ) ) {
            $people_ids = $this->get_list_subscribers( $selected_lists );
        }

        // ids that are in people table and to whom email already sent
        $existing_ids = $people_model->select( 'people_id' )
                 ->whereIn( 'people_id', $people_ids )
                 ->get()->toArray();
        $existing_ids = wp_list_pluck( $existing_ids, 'people_id' );

        $new_ids = array_diff( $people_ids, $existing_ids );

        // calculate the send time
        $send_at = $send_time ? $send_time : current_time( 'mysql' );

        // do not queue people that doesn't have email
        $queue_people = array_map( function ( $people_id ) use ( $campaign_id, $send_at ) {
            $erp_people = \WeDevs\ERP\Framework\Models\People::whereNotNull( 'email' )->where( 'email', '!=', '' )->where( 'id', $people_id )->first();

            if ( $erp_people && is_email( $erp_people->email ) ) {
                return [ 'campaign_id' => $campaign_id, 'people_id' => $people_id, 'send_at' => $send_at ];
            } else {
                return null;
            }
        }, $new_ids );

        $queue_people = array_filter( $queue_people );

        // Store ids on queue table. First delete existing pair, then insert new ids
        // based on selected_lists. After successfully send email, a row will be
        // deleted and inserted into campaign_people table
        if ( !$send_time ) {
            $queue_model->withTrashed()->forceDelete();
            $queue_model->insert( $queue_people );

        } else {
            foreach ( $queue_people as $people ) {
                $existing_queue = $queue_model->where( 'campaign_id', $campaign_id )
                                              ->where( 'people_id', $people['people_id'] )
                                              ->get()->count();

                if ( $existing_queue ) {
                    $queue_model->update( [
                        'campaign_id' => $campaign_id,
                        'people_id' => $people['people_id'],
                        'send_at' => $people['send_at']
                    ] );

                } else {
                    $queue_model->insert( $people );
                }

            }
        }

        ecamp_set_next_email_schedule( true );
    }

    /**
     * Subscribers belongs to one or multiple lists
     *
     * @param array $lists
     *
     * @return array subscriber ids that refer to id column of people table
     */
    public function get_list_subscribers( $lists ) {
        $group_ids = [];
        $search_ids = [];

        // contact groups
        if ( !empty( $lists['contact_groups'] ) ) {
            $group_ids = \WeDevs\ERP\CRM\Models\ContactSubscriber::select( 'user_id' )
                         ->whereIn( 'group_id', $lists['contact_groups'] )
                         ->whereNull( 'unsubscribe_at' )
                         ->get()->toArray();

            $group_ids = wp_list_pluck( $group_ids, 'user_id' );
        }

        // save search
        if ( !empty( $lists['save_searches'] ) ) {
            $unsubscribed_ids = [];

            foreach ( $lists['save_searches'] as $search_id ) {
                $search = \WeDevs\ERP\CRM\Models\SaveSearch::find( $search_id );
                $search = $search->search_val;

                $people = erp_get_peoples( [
                    'type' => 'contact',
                    'erpadvancefilter' => $search,
                    'number' => -1
                ] );

                $people = wp_list_pluck( $people, 'id' );

                $search_ids = array_merge( $search_ids, $people );
            }

            $unsubscribed_ids = DB::table( 'erp_crm_save_search_unsubscribers' )
                                ->select( 'user_id' )
                                ->whereIn( 'search_id', $lists['save_searches'] )
                                ->get();

            $unsubscribed_ids = wp_list_pluck( $unsubscribed_ids, 'user_id' );

            $search_ids = array_diff( $search_ids , $unsubscribed_ids );
        }

        return array_unique( array_merge( $group_ids, $search_ids ) );
    }

    /**
     * Get the unsubscribed people ids for one or multiple lists ids
     *
     * @param array $lists
     *
     * @return array
     */
    public function get_list_unsubscribers( $lists ) {
        $group_ids = [];
        $search_ids = [];

        if ( !empty( $lists['contact_groups'] ) ) {
            $group_ids = $this->get_group_unsubs( $lists['contact_groups'] );
        }

        if ( !empty( $lists['save_searches'] ) ) {
            $search_ids = $this->get_save_search_unsubs( $lists['save_searches'] );
        }

        return array_unique( array_merge( $group_ids, $search_ids ) );
    }

    /**
     * Get the unsubscribed people ids for contact groups
     *
     * @param array $group_ids
     *
     * @return array
     */
    public function get_group_unsubs( $group_ids ) {
        $people_ids = \WeDevs\ERP\CRM\Models\ContactSubscriber::select( 'user_id' )
                      ->whereIn( 'group_id', $group_ids )
                      ->whereNotNull( 'unsubscribe_at' )
                      ->get()->toArray();

        return wp_list_pluck( $people_ids, 'user_id' );
    }

    /**
     * Get the unsubscribed people ids for save searches
     *
     * @param array $search_ids
     *
     * @return array
     */
    public function get_save_search_unsubs( $search_ids ) {
        $people_ids = DB::table( 'erp_crm_save_search_unsubscribers' )
                      ->select( 'user_id' )
                      ->whereIn( 'search_id', $search_ids )
                      ->get();

        return wp_list_pluck( $people_ids, 'user_id' );
    }

    /**
     * Pause a campaign
     *
     * @param int $campaign_id
     *
     * @return bool
     */
    public function pause_campaign( $campaign_id ) {
        $paused = false;

        $campaign = CampaignModel::where( 'id', $campaign_id )->where( 'status', 'in_progress' )->first();

        if ( $campaign ) {
            // soft delete the queued people for this campaign
            $campaign->peopleQueue()->delete();

            $paused = $campaign->update( [ 'status' => 'paused' ] );

            $email_subject = $campaign->email_subject;
        }

        // check for event based campaign
        $campaign = CampaignModel::where( 'id', $campaign_id )->where( 'status', 'active' )->first();

        if ( $campaign ) {
            // soft delete the queued people for this campaign
            $campaign->peopleQueue()->delete();

            $paused = $campaign->update( [ 'status' => 'inactive' ] );

            $email_subject = $campaign->email_subject;
        }

        if ( !empty( $email_subject ) ) {
            $args = [
                'message' => sprintf(
                    '<a href="%s">%s</a> has been paused.',
                    ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign_id ], 'erp-email-campaign' ),
                    $email_subject
                ),

                'changetype' => 'paused'
            ];

            $this->audit_log( $args );
        }

        return $paused;
    }

    /**
     * Resume a campaign
     *
     * @param int $campaign_id
     *
     * @return bool
     */
    public function resume_campaign( $campaign_id ) {
        $resumed = false;

        $campaign = CampaignModel::where( 'id', $campaign_id )->where( 'status', 'paused' )->first();

        if ( $campaign ) {
            $campaign->peopleQueue()->withTrashed()->restore();

            $resumed = $campaign->update( [ 'status' => 'in_progress' ] );

            $email_subject = $campaign->email_subject;
        }

        $campaign = CampaignModel::where( 'id', $campaign_id )->where( 'status', 'inactive' )->first();

        if ( $campaign ) {
            $campaign->peopleQueue()->withTrashed()->restore();

            $resumed = $campaign->update( [ 'status' => 'active' ] );

            $email_subject = $campaign->email_subject;
        }

        if ( !empty( $email_subject ) ) {
            $args = [
                'message' => sprintf(
                    '<a href="%s">%s</a> has been re-activated.',
                    ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign_id ], 'erp-email-campaign' ),
                    $email_subject
                ),

                'changetype' => 'reactivate'
            ];

            $this->audit_log( $args );
        }

        ecamp_set_next_email_schedule( true );

        return $resumed;
    }

    /**
     * Campaign Subscriber statues
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function get_campaign_subscriber_statuses( $campaign_id ) {
        $counts = CampaignModel::find( $campaign_id )->people()->select(
                    DB::raw( 'count(`id`) as total' ),
                    DB::raw( 'count(`open`) as opened' ),
                    DB::raw( 'count(`clicked`) as clicked' ),
                    DB::raw( 'count(`bounced`) as bounced' )
                  )
                  ->first()->toArray();

        return [
            'all'       => [ 'count' => $counts['total'],   'label' => __( 'All', 'erp-email-campaign' ) ],
            'open'      => [ 'count' => $counts['opened'],  'label' => __( 'Opened', 'erp-email-campaign' ) ],
            'clicked'   => [ 'count' => $counts['clicked'], 'label' => __( 'Clicked', 'erp-email-campaign' ) ],
            'bounced'   => [ 'count' => $counts['bounced'], 'label' => __( 'Bounced', 'erp-email-campaign' ) ]
        ];
    }

    /**
     * Contact groups selected for a campaign
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function get_campaign_contact_groups( $campaign_id ) {
        $prefix = DB::instance()->db->prefix;

        return DB::table( 'erp_crm_email_campaigns_lists as cl' )
                    ->select( 'cg.id', 'cg.name' )
                    ->leftJoin( "{$prefix}erp_crm_contact_group as cg", 'cl.type_id', '=', 'cg.id' )
                    ->where( 'cl.campaign_id', $campaign_id )
                    ->where( 'cl.type', 'contact_groups' )
                    ->get();
    }

    /**
     * Throw error for invalid campaigns
     *
     * @param int|array   $campaign_ids
     * @param bool        $ignore_delete_check
     *
     * @return void
     */
    public function die_if_invalid_campaign( $campaign_ids, $ignore_delete_check = false ) {
        if ( !is_array( $campaign_ids ) ) {
            $campaign_ids = [ $campaign_ids ];
        }

        foreach ( $campaign_ids as $campaign_id ) {
            $campaign = CampaignModel::withTrashed()->find( $campaign_id );

            if ( empty( $campaign ) ) {
                wp_die( __( 'You attempted to edit an item that doesn’t exist. Perhaps it was deleted?' ) );
            } else if ( $campaign->deleted_at && !$ignore_delete_check ) {
                wp_die( __( 'You can’t edit this item because it is in the Trash. Please restore it and try again.' ) );
            }
        }
    }

    /**
     * Move a campaign to trash
     *
     * Eloquent soft delete a campaign
     *
     * @param int|array $campaign_ids
     *
     * @return int  number/count of trashed ids
     */
    public function trash_campaigns( $campaign_ids = [] ) {
        if ( !empty( $campaign_ids ) ) {
            $deleted = CampaignModel::destroy( $campaign_ids );

            if ( !empty( $deleted ) ) {

                if ( is_array( $campaign_ids ) ) {
                    foreach ( $campaign_ids as $campaign_id ) {
                        $campaign = CampaignModel::withTrashed()->find( $campaign_id );

                        $args = [
                            'message'       => sprintf( '<strong>%s</strong> has been trashed.', $campaign->email_subject ),
                            'changetype'    => 'trashed'
                        ];

                        $this->audit_log( $args );

                        $campaign->peopleQueue()->delete();
                    }

                } else {
                    $campaign = CampaignModel::withTrashed()->find( $campaign_ids );

                    $args = [
                        'message'       => sprintf( '<strong>%s</strong> has been trashed.', $campaign->email_subject ),
                        'changetype'    => 'trashed'
                    ];

                    $this->audit_log( $args );

                    $campaign->peopleQueue()->delete();
                }

                return $deleted;
            }
        }

        return 0;
    }

    /**
     * Restore campaigns from trash
     *
     * @param int|array $campaign_ids
     *
     * @return void Should return updated row count
     */
    public function restore_campaigns( $campaign_ids = [] ) {
        if ( !is_array( $campaign_ids ) ) {
            $campaign_ids = [ $campaign_ids ];
        }

        CampaignModel::whereIn( 'id', $campaign_ids )->restore();

        foreach ( $campaign_ids as $campaign_id ) {
            $campaign = CampaignModel::find( $campaign_id );

            $args = [
                'message'       => sprintf(
                    '<a href="%s">%s</a> has been restored from trash.',
                    ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign->id ], 'erp-email-campaign' ),
                    $campaign->email_subject
                ),

                'changetype'    => 'delete'
            ];

            $this->audit_log( $args );

            $campaign->peopleQueue()->withTrashed()->restore();
        }
    }

    /**
     * Permanently delete campaigns
     *
     * @since 1.0.0
     * @since 1.1.0 Delete related models only if $campaign is exists
     *
     * @param int|array $campaign_ids
     *
     * @return void Should return updated row count
     */
    public function delete_campaigns( $campaign_ids = [] ) {
        if ( !is_array( $campaign_ids ) ) {
            $campaign_ids = [ $campaign_ids ];
        }

        foreach ( $campaign_ids as $campaign_id ) {
            $campaign = CampaignModel::withTrashed()->find( $campaign_id );

            if ( $campaign ) {
                $campaign->template()->forceDelete();
                $campaign->people()->forceDelete();
                $campaign->peopleQueue()->withTrashed()->forceDelete();
                $campaign->urlStats()->forceDelete();

                if ( 'automatic' === $campaign->send ) {
                    $campaign->event()->forceDelete();
                } else {
                    $campaign->campaignLists()->forceDelete();
                }

                $campaign->forceDelete();

                $args = [
                    'message'       => sprintf( '<strong>%s</strong> has been deleted.', $campaign->email_subject ),
                    'changetype'    => 'delete'
                ];

                $this->audit_log( $args );
            }
        }
    }

    /**
     * Duplicate an email campaign
     *
     * @since 1.0.0
     * @since 1.1.0 Compare version to check if wp_slash is needed for template and html
     *
     * @param int   $campaign_id
     * @param array $args
     *
     * @return object Eloquent EmailCampaign model
     */
    public function duplicate_campaign( $campaign_id, $args = [] ) {
        $campaign = $this->get_campaign( absint( $campaign_id ) );

        if ( empty( $campaign ) ) {
            return 0;
        }

        // format campaign data without id
        $campaign_data = $campaign->toArray();

        $defaults = [
            'email_subject' =>  __( 'Duplicate', 'erp-email-campaign' ) . ': ' . $campaign_data['email_subject'],
            'status' => 'draft',
        ];

        $args = wp_parse_args( $args, $defaults );

        $campaign_data['email_subject'] = $args['email_subject'];
        $campaign_data['status'] = $args['status'];

        // format template data, take only template and html columns
        $template = $campaign->template;

        if ( version_compare( $template->plugin_version, '1.1.0', '<' ) ) {
            $template_data = [
                'template'  => $template->template,
                'html'      => $template->html,
            ];
        } else {
            $template_data = [
                'template'  => wp_slash( $template->template ),
                'html'      => wp_slash( $template->html ),
            ];
        }

        // format lists data
        $lists_data = [];

        if ( 'automatic' === $campaign_data['send'] ) {
            $event = $campaign->event->toArray();

            $campaign_data['event'] = [
                'action'            => $event['action'],
                'arg_value'         => $event['arg_value'],
                'schedule_type'     => $event['schedule_type'],
                'schedule_offset'   => $event['schedule_offset'],
            ];

        } else {
            $lists = $this->get_campaign_lists( $campaign_id );
            foreach ( $lists as $type => $list_type_arr ) {
                $lists_data[ $type ] = $list_type_arr['selected'];
            }
        }

        return $this->save_campaign( 0, $campaign_data, $lists_data, $template_data );
    }

    /**
     * Get the unsubscribed lists ids for a people id
     *
     * @param int $people_id
     *
     * @return array
     */
    public function get_people_unsubbed_lists( $people_id ) {
        $prefix = DB::instance()->db->prefix;
        $contact_groups = [];
        $save_searches = [];

        $groups = DB::table( 'erp_crm_email_campaigns_people as people' )
                      ->select( 'subscriber.group_id' )
                      ->leftJoin(
                        "{$prefix}erp_crm_contact_subscriber as subscriber",
                        'people.people_id', '=', 'subscriber.user_id'
                      )
                      ->where( 'people.people_id', $people_id )
                      ->whereNotNull( 'subscriber.unsubscribe_at' )
                      ->groupBy( 'subscriber.group_id' )
                      ->get();

        if ( count( $groups ) ) {
            $contact_groups = wp_list_pluck( $groups, 'group_id' );
        }

        $search = DB::table( 'erp_crm_email_campaigns_people as people' )
                      ->select( 'subscriber.search_id' )
                      ->leftJoin(
                        "{$prefix}erp_crm_save_search_unsubscribers as subscriber",
                        'people.people_id', '=', 'subscriber.user_id'
                      )
                      ->where( 'people.people_id', $people_id )
                      ->groupBy( 'subscriber.search_id' )
                      ->get();

        if ( count( $search ) ) {
            $save_searches = wp_list_pluck( $search, 'search_id' );
        }

        return [
            'contact_groups' => $contact_groups,
            'save_searches' => $save_searches,
        ];
    }

    /**
     * Update subscription status for a people id
     *
     * @param int  $campaign_id
     * @param int  $people_id
     * @param int  $sub_group
     * @param int  $unsub_group
     * @param int  $sub_search
     * @param int  $unsub_search
     * @param bool $managing
     *
     * @return void
     */
    public function update_subscription( $campaign_id, $people_id, $sub_group, $unsub_group, $sub_search, $unsub_search, $managing = false ) {
        $prefix = DB::instance()->db->prefix;

        // update in campaign people table
        if ( !$managing ) {
            $this->get_campaign( $campaign_id )
                 ->people()
                 ->where( 'people_id', $people_id )
                 ->update( [ 'unsubscribed' => 1 ] );
        }

        foreach ( $sub_group as $group_id ) {
            DB::table( 'erp_crm_contact_subscriber' )
                ->where( 'user_id', $people_id )
                ->where( 'group_id', $group_id )
                ->update( [ 'unsubscribe_at' => null ] );
        }

        foreach ( $unsub_group as $group_id ) {
            DB::table( 'erp_crm_contact_subscriber' )
               ->where( 'user_id', $people_id )
               ->where( 'group_id', $group_id )
               ->update( [ 'unsubscribe_at' => current_time( 'mysql' ) ] );
        }

        foreach ( $sub_search as $search_id ) {
            DB::table( 'erp_crm_save_search_unsubscribers' )
               ->where( 'user_id', $people_id )
               ->where( 'search_id', $search_id )
               ->delete();
        }

        foreach ( $unsub_search as $search_id ) {
            DB::table( 'erp_crm_save_search_unsubscribers' )
               ->where( 'user_id', $people_id )
               ->where( 'search_id', $search_id )
               ->delete();

            $search = \WeDevs\ERP\CRM\Models\SaveSearch::find( $search_id );
            $people_belongs_to_search = erp_get_peoples( [
                'type' => 'contact',
                'erpadvancefilter' => $search->search_val,
                'test_user' => $people_id,
                'count' => true
            ] );

            if ( $people_belongs_to_search ) {
                DB::table( 'erp_crm_save_search_unsubscribers' )->insert(
                    [ 'user_id' => $people_id, 'search_id' => $search_id, 'unsubscribed_at' => current_time( 'mysql' ) ]
                );
            }
        }
    }

    /**
     * Get campaign event details for a campaign
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function get_campaign_event( $campaign_id ) {
        $default = [
            'action' => null,
            'arg_value' => null,
            'schedule_type' => null,
            'schedule_offset' => 1,
        ];

        $event = [];

        if ( $campaign = $this->get_campaign( $campaign_id ) ) {

            $campaign_event = $campaign->event;

            if ( $campaign_event ) {
                $event = $campaign_event->toArray();
            }
        }

        return wp_parse_args( $event, $default );
    }

    /**
     * Unsubsribe a people from all lists belongs to a campaign
     *
     * @param int $campaign_id
     * @param int $people_id
     *
     * @return void
     */
    public function unsubscribe_people( $campaign_id, $people_id ) {
        $deselected_groups = [];
        $deselected_search = [];

        $campaign_lists = CampaignListModel::where( 'campaign_id', $campaign_id )->listsByType();

        if ( !empty( $campaign_lists['contact_groups'] ) ) {
            $deselected_groups = wp_list_pluck( $campaign_lists['contact_groups'], 'id' );
        }

        if ( !empty( $campaign_lists['save_searches'] ) ) {
            $deselected_search = wp_list_pluck( $campaign_lists['save_searches'], 'id' );
        }

        // Update subscription status for a people id
        $this->update_subscription( $campaign_id, $people_id, [], $deselected_groups, [], $deselected_search );
    }

    /**
     * ERP audit log for email campaigns
     *
     * @param array $args
     *
     * @return void
     */
    public function audit_log( $args ) {
        // the message field is required
        if ( empty( $args['message'] ) ) {
            return;
        }

        $audit_log = \WeDevs\ERP\Log::instance();

        $defaults = [
            'component'     => 'CRM',
            'sub_component' => 'Email Campaign',
            'changetype'    => 'add',
            'created_by'    => get_current_user_id(),
        ];

        $args = wp_parse_args( $args, $defaults );

        $audit_log->insert_log( $args );
    }

    /**
     * Save people activities
     *
     * @param int $people_id
     * @param int $campaign_id
     *
     * @return void
     */
    public function save_activity( $people_id, $campaign_id ) {

        $people = PeopleModel::where( 'campaign_id', $campaign_id )
                             ->where( 'people_id', $people_id )
                             ->first();

        if ( empty( $people ) ) {
            return;
        }

        $campaign = $people->campaign;

        $campaign_title = sprintf(
            '<a href="%s">%s</a>',
            ecamp_admin_url( [ 'action' => 'view', 'id' => $campaign_id ], 'erp-email-campaign' ),
            $campaign->email_subject
        );

        // log if email is bounced
        if ( $people->bounced ) {
            $message = sprintf(
                __( 'Bounced email for the campaign &ldquo;%s&rdquo;', 'erp-email-campaign' ),
                $campaign_title
            );

        } else {
            // log if email is sent but did not opened
            $message = sprintf(
                __( 'Sent email for the campaign &ldquo;%s&rdquo; at %s', 'erp-email-campaign' ),
                $campaign_title,
                date( 'h:i a', strtotime( $people->sent ) )
            );

            // log when email is opened
            if ( $people->open ) {
                $current_time = current_time( 'timestamp' );
                $time_diff = human_time_diff( strtotime( $people->sent ), $current_time );

                $message = sprintf(
                    __( 'Received email from the campaign &ldquo;%s&rdquo; at %s and then opened %s later.', 'erp-email-campaign' ),
                    $campaign_title,
                    date( 'h:i a', strtotime( $people->sent ) ),
                    $time_diff
                );
            }
        }

        // Search for any existing activity matching people id and campaign id.
        // If not find any, create a new one.
        $activity = \WeDevs\ERP\CRM\Models\Activity::firstOrNew( [
            'type' => 'email_campaign',
            'user_id' => $people_id,
            'email_subject' => $campaign_id
        ] );

        $values = [
            'user_id'       => $people_id,
            'type'          => 'email_campaign',
            'message'       => $message,
            'email_subject' => $campaign_id,
            'log_type'      => $people->bounced ? 'bounced' : 'general'
        ];

        if ( $activity->exists ) {
            $activity->update( $values );
        } else {
            $activity->setRawAttributes( $values, true );
            $activity->save();
        }
    }

    /**
     * Has people on email sending queue
     *
     * @since 1.1.0
     *
     * @return boolean
     */
    public function has_people_on_queue() {
        return !! ( Models\PeopleQueue::where( 'send_at', '<=', current_time( 'mysql' ) )->count() );
    }

    public function get_subscriber_campaign_activities( $campaign_id, $subscriber_id ) {
        $camp_subs_id_pair = [ 'campaign_id' => $campaign_id, 'people_id' => $subscriber_id ];

        $people = PeopleModel::where( $camp_subs_id_pair )->first();

        if ( empty( $people ) ) {
            return new WP_Error( 'no-subscriber-found', __( 'No subscriber found', 'erp-email-campaign' ) );
        }

        $prefix = DB::instance()->db->prefix;

        $info = erp_get_people( $subscriber_id );

        $info->avatar = erp_crm_get_avatar_url( $info->id, $info->email, $info->user_id );
        $info->details_url = erp_crm_get_details_url( $info->id, $info->types );

        $activities = [
            'info' => $info,
            'activities' => [
                'sent'  => $people->sent,
                'open'  => Models\OpenStat::select( 'opened_at' )->where( $camp_subs_id_pair )->get()->toArray(),
                'url'   => DB::table( 'erp_crm_email_campaigns_url_stats as stats' )
                           ->select( 'stats.clicked_at', 'url.url' )
                           ->leftJoin( "{$prefix}erp_crm_email_campaigns_urls as url", 'stats.url_id', '=', 'url.id' )
                           ->where( 'stats.campaign_id', $campaign_id )
                           ->where( 'stats.people_id', $subscriber_id )
                           ->get()
                ]
        ];

        return $activities;
    }
}
