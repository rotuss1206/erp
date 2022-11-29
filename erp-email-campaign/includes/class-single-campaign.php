<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use \WeDevs\ORM\Eloquent\Facades\DB;

class Single_Campaign {

    /**
     * Campaign Id with which this class will be instantiate
     *
     * @var int
     */
    private $campaign_id;

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * The wordpress database table prefix
     *
     * @var string
     */
    private $db_prefix = '';

    /**
     * Holds campaign email stats
     *
     * @var array
     */
    private $email_stats = [];

    /**
     * Class constructor
     *
     * @param int $campaign_id
     *
     * @return void
     */
    public function __construct( $campaign_id ) {
        $campaign_id = absint( $campaign_id );

        $this->campaign_id = $campaign_id;
        $email_campaign = erp_email_campaign();
        $campaign = $email_campaign->get_campaign( $campaign_id );
        $this->container = $campaign->toArray();
        $this->container['campaign'] = $campaign; // holds the model
        $this->container['email_campaign'] = $email_campaign; // holds the Email_Campaign instance
        $this->db_prefix = DB::instance()->db->prefix;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    /**
     * An array of campaign lists titles
     *
     * No ids, not grouped by type
     *
     * @return array
     */
    public function get_list_titles() {
        $campaign_lists = [];
        $lists = $this->campaign->campaignLists()->listsByType();

        if ( !empty( $lists ) ) {
            foreach ( $lists as $type => $pair ) {
                $campaign_lists = array_merge( $campaign_lists, wp_list_pluck( $pair, 'title' ) );
            }
        }

        return $campaign_lists;
    }


    /**
     * Campaign URL Stats
     *
     * @return array
     */
    public function get_url_stats() {
        return DB::table( 'erp_crm_email_campaigns_urls as urls' )
               ->select(
                   'stats.id', 'stats.url_id', 'stats.campaign_id', 'urls.url',
                   DB::raw( 'count(`stats`.`id`) as total_click' ),
                   DB::raw( 'count(DISTINCT(`stats`.`people_id`)) as unique_click' )
               )
               ->leftJoin( "{$this->db_prefix}erp_crm_email_campaigns_url_stats as stats", 'urls.id', '=', 'stats.url_id' )
               ->where( 'stats.campaign_id', $this->campaign_id )
               ->groupBy( 'stats.url_id' )
               ->orderBy( 'total_click', 'DESC' )
               ->get();
    }

    /**
     * Single Campaign Subscribers
     *
     * @param array $args
     *
     * @return array
     */
    public function get_recipient_list_table_data( $args = [] ) {
        $defaults = [
            'number' => 10,
            'offset' => 0,
            's' => '',
            'email-status' => '',
            'group' => 0,
        ];

        // filter params
        $args = wp_parse_args( $args, $defaults );


        /**
         * In order to filter contact group, we need to change the base table.
         * First we'll build query when no group filtering and then for the group filtering.
         */
        if ( empty( $args['group'] ) ) {
            $query = DB::table( 'erp_crm_email_campaigns_people as cp' )
                     ->select(
                         'p.id', 'p.company', 'cp.sent', 'cp.open', 'cp.clicked', 'cp.bounced',
                         DB::raw( 'coalesce( `p`.`email`, `u`.`user_email` ) email' ),
                         DB::raw( 'coalesce( `p`.`first_name`, `f`.`meta_value` ) first_name' ),
                         DB::raw( 'coalesce( `p`.`last_name`, `l`.`meta_value` ) last_name' )
                     );

        } else {
            $query = DB::table( 'erp_crm_contact_subscriber as cs' )
                     ->select(
                         'p.id', 'cp.sent', 'cp.open', 'cp.clicked', 'cp.bounced',
                         DB::raw( 'coalesce( `p`.`email`, `u`.`user_email` ) email' ),
                         DB::raw( 'coalesce( `p`.`first_name`, `f`.`meta_value` ) first_name' ),
                         DB::raw( 'coalesce( `p`.`last_name`, `l`.`meta_value` ) last_name' )
                     )
                     ->leftJoin( "{$this->db_prefix}erp_crm_email_campaigns_people as cp", 'cs.user_id', '=', 'cp.people_id' )
                     ->leftJoin( "{$this->db_prefix}erp_crm_contact_group as cg", 'cs.group_id', '=', 'cg.id' )
                     ->where( 'cs.group_id', absint( $args['group'] ) );

        }

        // common
        $query->leftJoin( "{$this->db_prefix}erp_peoples as p", 'cp.people_id', '=', 'p.id' )
              ->leftJoin( "{$this->db_prefix}users as u", 'p.user_id', '=', 'u.ID' )
              ->leftJoin( "{$this->db_prefix}usermeta as f", function ( $join ) {
                  $join->on( 'u.id', '=', 'f.user_id' )->where( 'f.meta_key', '=', 'first_name' );
              } )
              ->leftJoin( "{$this->db_prefix}usermeta as l", function ( $join ) {
                  $join->on( 'u.id', '=', 'l.user_id' )->where( 'l.meta_key', '=', 'last_name' );
              } )
              ->where( 'cp.campaign_id', $this->id )
              ->orderBy( 'cp.sent', 'ASC' );

        // Search filter
        if ( !empty( $args['s'] ) ) {
            $query->where( function ( $query ) use ( $args ) {
                $query->orWhere( 'p.email', 'LIKE', "%{$args['s']}%" )
                      ->orWhere( 'u.user_email', 'LIKE', "%{$args['s']}%" )
                      ->orWhere( 'p.first_name', 'LIKE', "%{$args['s']}%" )
                      ->orWhere( 'f.meta_value', 'LIKE', "%{$args['s']}%" )
                      ->orWhere( 'p.last_name', 'LIKE', "%{$args['s']}%" )
                      ->orWhere( 'l.meta_value', 'LIKE', "%{$args['s']}%" );
            } );
        }

        // email status filtering
        if ( !empty( $args['email-status'] ) && 'all' !== $args['email-status'] ) {
            $query->whereNotNull( "cp.{$args['email-status']}" );
        }

        // execute queries
        $total_items = $query->count();
        $items = $query->skip( $args['offset'] )->take( $args['number'] )->get();

        $campaign_lists = $this->campaign->campaignLists()->listsByType();

        // additional data
        $campaign = $this;
        array_walk( $items , function ( &$item, $i ) use ( $campaign, $campaign_lists ) {
            // contact
            $item->avatar = get_avatar( $item->email, 32 );
            $item->contactProfile = ecamp_admin_url( [ 'action' => 'view', 'id' => $item->id ], 'erp-sales-customers' );

            // the lists that a contact belongs to
            $groups = DB::table( 'erp_crm_email_campaigns_lists as cl' )
                      ->select( 'cg.name', 'cs.unsubscribe_at' )
                      ->leftJoin( "{$this->db_prefix}erp_crm_contact_subscriber as cs", 'cl.type_id', '=', 'cs.group_id' )
                      ->leftJoin( "{$this->db_prefix}erp_crm_email_campaigns_people as cp", 'cs.user_id', '=', 'cp.people_id' )
                      ->leftJoin( "{$this->db_prefix}erp_crm_contact_group as cg", 'cl.type_id', '=', 'cg.id' )
                      ->where( 'cl.campaign_id', $campaign->id )
                      ->where( 'cp.people_id', $item->id )
                      ->where( 'cl.type', 'contact_groups' )
                      ->groupBy( 'cl.type_id' )
                      ->get();


            $group_names = wp_list_pluck( $groups, 'name' );

            $search_names = [];

            if ( is_array( $campaign_lists ) && !empty( $campaign_lists['save_searches'] ) ) {
                foreach ( $campaign_lists['save_searches'] as $search ) {

                    $people_belongs_to_search = erp_get_peoples( [
                        'type' => 'contact',
                        'erpadvancefilter' => $search['search'],
                        'test_user' => $item->id,
                        'count' => true
                    ] );

                    if ( !empty( $people_belongs_to_search ) ) {
                        $search_names[] = $search['title'];
                    }

                }
            }

            $lists = array_merge( $group_names, $search_names );

            $item->lists = implode( ', ', $lists );

            // Suubscribed/Unsubscribed
            $unsub_at = wp_list_pluck( $groups, 'unsubscribe_at' );

            $unsub_at = array_filter( $unsub_at );

            if ( count($unsub_at) ) {
                $item->subs_status = __( 'Unsubscribed', 'erp-email-campaign' );
            } else {
                $item->subs_status = __( 'Subscribed', 'erp-email-campaign' );
            }

            // email and click statuses
            $email_status = 'email-status';
            $item->$email_status = __( 'Unopened', 'erp-email-campaign' );

            if ( !empty( $item->open ) ) {
                $item->opened = erp_format_date( $item->open, 'M d Y h:i a' );
                $item->$email_status = __( 'Opened', 'erp-email-campaign' );
            }

            if ( absint( $item->clicked ) ) {
                $item->$email_status = __( 'Clicked', 'erp-email-campaign' );
            }

            if ( $item->bounced ) {
                $item->$email_status = __( 'Bounced', 'erp-email-campaign' );
            }
        } );

        return [
            'total_items' => $total_items,
            'data' => $items
        ];
    }

    /**
     * Email Stats
     *
     * Counts for on queue, sent, opened, clicked and bounced emails.
     *
     * @return array
     */
    public function get_email_stats() {
        if ( !empty( $this->email_stats ) ) {
            return $this->email_stats;
        }

        $on_queue = $this->campaign->peopleQueue()->count();

        $people = $this->campaign->people()->get()->toArray();

        $opened = array_filter( $people, function ( $subscriber ) {
            return !empty( $subscriber['open'] );
        } );

        $clicked = array_filter( $people, function ( $subscriber ) {
            return !empty( $subscriber['clicked'] );
        } );

        $bounced = array_filter( $people, function ( $subscriber ) {
            return !empty( $subscriber['bounced'] );
        } );

        $stats = [
            'clicked'       => count( $clicked ),
            'not_clicked'   => count( $opened ) - count( $clicked ),
            'not_opened'    => count( $people ) - count( $opened ),
            'bounced'       => count( $bounced ),
            'on_queue'      => $on_queue,
        ];

        return $stats;
    }

    /**
     * Legends with email stat data for flot chart
     *
     * @return array
     */
    public function get_email_stats_with_legends() {
        $legends = [
          'clicked'     => [ 'label' => __( '<strong>%s</strong> Clicked', 'erp-email-campaign' ), 'color' => '#8BC34A' ],
          'not_clicked' => [ 'label' => __( '<strong>%s</strong> Opened but did not click', 'erp-email-campaign' ), 'color' => '#1EAAF1' ],
          'not_opened'  => [ 'label' => __( '<strong>%s</strong> Not opened', 'erp-email-campaign' ), 'color' => '#00BFA5' ],
          'bounced'     => [ 'label' => __( '<strong>%s</strong> Bounced', 'erp-email-campaign' ), 'color' => '#FD8A6A' ],
          'on_queue'    => [ 'label' => __( '<strong>%s</strong> On Queue', 'erp-email-campaign' ), 'color' => '#F5BE3B' ],
        ];

        $stats = $this->get_email_stats();

        $data = [];

        foreach ( $stats as $key => $count ) {
            if ( $count ) {
                $legends[ $key ]['label'] = sprintf( $legends[ $key ]['label'], $count );
                $data[] = $legends[ $key ] + [ 'data' => $count ];
            }
        }

        return $data;
    }

    /**
     * Email sending progress bar
     *
     * Not using yet
     *
     * @return string
     */
    public function get_email_sending_progress_bar() {
        $stats = $this->get_email_stats();

        $total = $stats['sent'] + $stats['on_queue'];

        $progress_bar = '';
        if ( $total ) {
            $perc = ( $stats['sent'] / $total ) * 100;
            $progress_bar = '<span class="campaign-progress-bar clearfix"><span class="progress-done" style="width: ' . $perc . '%;"></span><span class="progress-text">' . $stats['sent'] . ' / ' . $total . '</span></span>';
        }

        return $progress_bar;
    }

    /**
     * Get the status for automatic-active campaigns
     *
     * @since 1.0.0
     * @since 1.1.0 Add conditions for `erp_matches_segment`
     *
     * @return string
     */
    public function get_active_campaign_status() {
        $event = $this->campaign->event;
        $status = '';

        $sent = $this->campaign->people->count();

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

        return sprintf(
                '<span class="list-table-status active">%s</span>', $status
        );
    }
}
