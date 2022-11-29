<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign as CampaignModel;


/**
 * Campaign List table class
 */
class Campaign_List_Table extends \WP_List_Table {

    private $people = [];
    private $people_queue = [];
    private $total_people = 0;
    private $lists = [];

    public function __construct() {
        global $status, $page;

        $this->actions();

        parent::__construct( [
            'singular' => 'campaign',
            'plural'   => 'campaigns',
            'ajax'     => false
        ] );
    }

    /**
     * Set list table class
     *
     * @return void
     */
    public function get_table_classes() {
        return [ 'widefat', 'fixed', 'striped', 'campaign-list-table', $this->_args['plural'] ];
    }

    /**
     * Search form for list table
     *
     * @return void
     */
    public function search_box( $text = '', $input_id = 0 ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() ) {
            return;
        }
        ?>
        <p class="search-box">
            <input type="search" id="erp-email-campaign-search" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( __( 'Search Campaign', 'erp-email-campaign' ), 'button', 'campaign_search', false, [ 'id' => 'erp-email-campaign-search-submit' ] ); ?>
        </p>
        <?php
    }

    /**
     * Set the filter listing views
     *
     * @return array
     */
    public function get_views() {
        $campaigns = CampaignModel::select( 'status', 'send' )->get(); // collection object

        $campaign_filters = [
            'all'       => [
                'label' => __( 'All', 'erp-email-campaign' ),
                'count' => $campaigns->count()
            ],

            'automatic' => [
                'label' => __( 'Automatic', 'erp-email-campaign' ),

                'count' => $campaigns->filter( function ( $campaign ) {
                    return ( 'automatic' === $campaign->send );
                } )->count()
            ],

            'standard'  => [
                'label' => __( 'Standard', 'erp-email-campaign' ),

                'count' => $campaigns->filter( function ( $campaign ) {
                    return ( 'automatic' !== $campaign->send );
                } )->count()
            ],

            'active'    => [
                'label' => __( 'Active', 'erp-email-campaign' ),

                'count' => $campaigns->filter( function ( $campaign ) {
                    return ( 'in_progress' === $campaign->status ) || ( 'active' === $campaign->status );
                 } )->count()
            ],

            'paused'    => [
                'label' => __( 'Paused', 'erp-email-campaign' ),
                'count' => $campaigns->filter( function ( $campaign ) {
                    return ( 'paused' === $campaign->status ) || ( 'inactive' === $campaign->status );
                } )->count()
            ],

            'sent'      => [
                'label' => __( 'Sent', 'erp-email-campaign' ),
                'count' => $campaigns->filter( function ( $campaign ) {
                    return ( 'sent' === $campaign->status );
                } )->count()
            ],

            'draft'     => [
                'label' => __( 'Draft', 'erp-email-campaign' ),
                'count' => $campaigns->filter( function ( $campaign ) {
                    return ( 'draft' === $campaign->status );
                } )->count()
            ],

            'trashed'   => [
                'label' => __( 'Trashed', 'erp-email-campaign' ),
                'count' => CampaignModel::onlyTrashed()->count()
            ]
        ];

        $page_status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';
        $nav_filters  = [];

        foreach ( $campaign_filters as $filter_key => $filter ) {
            $class = ( $filter_key == $page_status ) ? 'current' : 'status-' . $filter_key;

            $nav_filters[ $filter_key ] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
                ( $filter_key == 'all' ) ? ecamp_admin_url() : ecamp_admin_url( [ 'status' => $filter_key ] ),
                $class, $filter['label'],
                $filter['count']
            );
        }

        return $nav_filters;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        if ( !empty( $_REQUEST['status'] ) && 'trashed' === $_REQUEST['status'] ) {
            $actions = [
                'restore'    => __( 'Restore', 'erp-email-campaign' ),
                'delete'     => __( 'Delete Parmanently', 'erp-email-campaign' ),
            ];

        } else {
            $actions = [
                'trash'     => __( 'Move to Trash', 'erp-email-campaign' ),
            ];
        }


        return $actions;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page              = get_option( 'posts_per_page', 20 );
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;

        $args = [
            'columns'   => [ 'id', 'email_subject', 'status', 'send', 'deliver_at', 'created_at', 'deleted_at' ],
            'offset'    => $offset,
            'per_page'  => $per_page,
        ];

        // Filter for order by
        if ( !empty( $_REQUEST['orderby'] ) ) {
            switch ( $_REQUEST['orderby'] ) {
                case 'email-subject':
                    $args['orderby'] = 'email_subject';
                    break;

                default:
                    $args['orderby'] = $_REQUEST['orderby'];
                    break;
            }
        }

        // Filter for order
        if ( !empty( $_REQUEST['order'] ) ) {
            $args['order'] = $_REQUEST['order'];
        }

        // filter campaign status
        if ( !empty( $_REQUEST['status'] ) ) {

            switch ( $_REQUEST['status'] ) {
                case 'paused':
                    $args['status'] = [ 'paused', 'inactive' ];
                    break;

                case 'automatic':
                    $args['status'] = [ 'active', 'inactive', 'draft' ];
                    $args['send']   = 'automatic';
                    break;

                case 'standard':
                    $args['status'] = [ 'in_progress', 'sent', 'paused', 'draft' ];
                    $args['send']   = 'immediately';
                    break;

                case 'active':
                    $args['status'] = [ 'in_progress', 'active' ];
                    break;

                default:
                    $args['status'] = $_REQUEST['status'];
                    break;
            }

        }

        // search campaigns
        if ( !empty( $_REQUEST['s'] ) ) {
            $args['s'] = $_REQUEST['s'];
        }

        // Prepare all item after all filtering
        $this->items = erp_email_campaign()->get_campaigns( $args );

        // Render total customer according to above filter
        $total_items = erp_email_campaign()->total_items;

        // Set pagination according to filter
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );
    }

    /**
     * Generate the table navigation above or below the table
     *
     * Mostly copy/pasted from class-wp-list-table.php
     *
     * @param string $which
     *
     * @return void
     */
    protected function display_tablenav( $which ) {
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        }
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?> <?php echo (!$this->has_items()) ? 'no-item' : ''; ?>">

            <?php if ( $this->has_items() ): ?>
            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions( $which ); ?>
            </div>
            <?php endif; ?>

            <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
        <?php if ( 'bottom' === $which ): ?>

            <?php if ( !empty( $_REQUEST['status'] ) ): ?>
            <input name="status" type="hidden" value="<?php echo $_REQUEST['status']; ?>">
            <?php endif; ?>

            <div id="list-table-loader">&nbsp;</div>
        <?php endif; ?>
        <?php
    }

    /**
     * Generates content for a single row of the table
     *
     * @param object $item The current item
     */
    public function single_row( $item ) {
        echo '<tr class="campaign-list-row ' . $item->status . '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'            => '<input type="checkbox" />',
            'icon'          => '',
            'name'          => __( 'Email Subject', 'erp-email-campaign' ),
            'status'        => __( 'Status', 'erp-email-campaign' ),
            'lists'         => __( 'Lists', 'erp-email-campaign' ),
            'open'          => __( 'Open', 'erp-email-campaign' ),
            'click'         => __( 'Click', 'erp-email-campaign' ),
            'unsubscribed'  => __( 'Unsub', 'erp-email-campaign' ),
            'bounced'       => __( 'Bounce', 'erp-email-campaign' ),
            'created_at'    => __( 'Created At', 'erp-email-campaign' )
        ];

        return apply_filters( 'erp_crm_campaign_table_cols', $columns );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = [
            'name'       => [ 'email-subject', true ],
            'created_at' => [ 'created_at', true ],
        ];

        return $sortable_columns;
    }

    /**
     * Message to show if no campaign found
     *
     * @return void
     */
    public function no_items() {
        _e( 'No campaign found.', 'erp-email-campaign' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    public function column_default( $campaign, $column_name ) {
        $content = '';

        switch ( $column_name ) {

            case 'created_at':
                $content = !empty( $campaign->created_at ) ? erp_format_date( $campaign->created_at ) : '-';
                break;

            default:
                $content = isset( $campaign->$column_name ) ? $campaign->$column_name : '';
                break;

        }

        return $content;
    }

    /**
     * Render the checkbox column data
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        $this->people = $item->people->toArray();
        $this->people_queue = $item->peopleQueue()->withTrashed()->get()->toArray();

        $sent = count( $this->people );
        $queue = count( $this->people_queue );
        $this->total_people = $sent + $queue;

        $this->lists = $item->campaignLists()->listsByType();

        if ( !is_array( $this->lists ) ) {
            $this->lists = [];
        }

        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', $item->id
        );
    }

    /**
     * Icon for the email type
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_icon( $item ) {
        $type = ( 'automatic' === $item->send ) ? 'automatic' : 'standard';

        $image = WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/email-type-' . $type . '.png';
        $title = ( 'automatic' === $item->send ) ? __( 'Automatic Newsletter', 'erp-email-campaign' ) : __( 'Standard Newsletter', 'erp-email-campaign' );

        return sprintf( '<img class="email-type-icon" src="%s" alt="%s" title="%s" data-tiptip>', $image, $title, $title );
    }

    /**
     * Render the campaign name column data
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_name( $item ) {
        /*
            @TODO: Right now after bulk action we are redirecting to main page.
                   status query param should be remain in the URL.
        */

        $actions = [];
        $nonce = wp_create_nonce( 'bulk-campaigns' );

        $query_args = [];

        if ( !empty( $_REQUEST['status'] ) ) {
            $query_args['status'] = $_REQUEST['status'];
        }

        if ( !empty( $item->deleted_at ) ) {
            $actions['restore'] = sprintf(
                '<a href="%s" title="%s">%s</a>',
                ecamp_admin_url( [ 'status' => 'trashed', 'action' => 'restore', 'id' => $item->id, '_wpnonce' => $nonce ] ),
                __( 'Restore this item', 'erp-email-campaign' ),
                __( 'Restore', 'erp-email-campaign' )
            );

            $actions['delete'] = sprintf(
                '<a href="%s" title="%s">%s</a>',
                ecamp_admin_url( [ 'status' => 'trashed', 'action' => 'delete', 'id' => $item->id, '_wpnonce' => $nonce ] ),
                __( 'Permanently delete this item', 'erp-email-campaign' ),
                __( 'Delete Permanently', 'erp-email-campaign' )
            );

            $name = sprintf(
                '<strong>%s</strong> %s',
                $item->email_subject,
                $this->row_actions( $actions )
            );

        } else if ( ( isset( erp_email_campaign()->statuses[ $item->status ] ) && erp_email_campaign()->statuses[ $item->status ]['can_edit'] ) ) {
            if ( 'paused' === $item->status || 'inactive' === $item->status ) {
                $actions['resume'] = sprintf(
                    '<a href="#resume" title="%s" class="campaign-link-resume resume-this-campaign" data-campaign="%s">%s</a>',
                    __( 'Resume this item', 'erp-email-campaign' ),
                    $item->id,
                    __( 'Resume', 'erp-email-campaign' )
                );
            }

            $actions['edit'] = sprintf(
                '<a href="%s" title="%s" class="campaign-link-edit">%s</a>',
                ecamp_admin_url( [ 'action' => 'edit', 'id' => $item->id ] ),
                __( 'Edit this item', 'erp-email-campaign' ),
                __( 'Edit', 'erp-email-campaign' )
            );

            $args = [ 'action' => 'duplicate', 'id' => $item->id, '_wpnonce' => $nonce ];
            $actions['duplicate'] = sprintf(
                '<a href="%s" title="%s">%s</a>',
                ecamp_admin_url( array_merge( $args, $query_args ) ),
                __( 'Duplicate this item', 'erp-email-campaign' ),
                __( 'Duplicate', 'erp-email-campaign' )
            );

            $args = [ 'action' => 'trash', 'id' => $item->id, '_wpnonce' => $nonce ];
            $actions['trash'] = sprintf(
                '<a href="%s" class="submitdelete" title="%s">%s</a>',
                ecamp_admin_url( array_merge( $args, $query_args ) ),
                __( 'Movie this item to Trash', 'erp-email-campaign' ),
                __( 'Trash', 'erp-email-campaign' )
            );

            $name = sprintf(
                '<a href="%s" class="campaign-link-name"><strong>%s</strong></a> %s',
                ecamp_admin_url( [ 'action' => 'edit', 'id' => $item->id ] ),
                $item->email_subject,
                $this->row_actions( $actions )
            );

        } else {
            if (
                ( isset( erp_email_campaign()->statuses[ $item->status ] ) && erp_email_campaign()->statuses[ $item->status ]['can_pause'] ) ||
                'active' === $item->status
            ) {
                $actions['pause'] = sprintf(
                    '<a href="#pause" title="%s" class="status-action status-pause pause-this-campaign" data-campaign="%s">%s</a>',
                    __( 'Pause this campaign', 'erp-email-campaign' ),
                    $item->id,
                    __( 'Pause', 'erp-email-campaign' )
                );

            }

            $actions['view'] = sprintf(
                '<a href="%s" title="%s" class="campaign-link-view">%s</a>',
                ecamp_admin_url( [ 'action' => 'view', 'id' => $item->id ], 'erp-email-campaign' ),
                __( 'View this item', 'erp-email-campaign' ),
                __( 'View', 'erp-email-campaign' )
            );

            $args = [ 'action' => 'duplicate', 'id' => $item->id, '_wpnonce' => $nonce ];
            $actions['duplicate'] = sprintf(
                '<a href="%s" title="%s">%s</a>',
                ecamp_admin_url( array_merge( $args, $query_args ) ),
                __( 'Duplicate this item', 'erp-email-campaign' ),
                __( 'Duplicate', 'erp-email-campaign' )
            );

            $args = [ 'action' => 'trash', 'id' => $item->id, '_wpnonce' => $nonce ];
            $actions['trash'] = sprintf(
                '<a href="%s" class="submitdelete" title="%s">%s</a>',
                ecamp_admin_url( array_merge( $args, $query_args ) ),
                __( 'Movie this item to Trash', 'erp-email-campaign' ),
                __( 'Trash', 'erp-email-campaign' )
            );

            $name = sprintf(
                '<a href="%s" class="campaign-link-name"><strong>%s</strong></a> %s',
                ecamp_admin_url( [ 'action' => 'view', 'id' => $item->id ], 'erp-email-campaign' ),
                $item->email_subject,
                $this->row_actions( $actions )
            );
        }


        return $name;
    }

    /**
     * Render the Status column data
     *
     * @since 1.0.0
     * @since 1.1.0 Add conditions for `erp_matches_segment`
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_status( $item ) {
        if ( !empty( $_REQUEST['status'] ) && 'trashed' === $_REQUEST['status'] ) {
            return sprintf(
                '<span class="list-table-status trashed">%s</span>',
                erp_email_campaign()->statuses['trashed']['label']
            );
        }

        $sent = count( $this->people );

        if ( 'sent' === $item->status ) {
            return sprintf(
                '<span class="list-table-status sent">%s</span>',
                sprintf( __( 'Sent to %s subscribers', 'erp-email-campaign' ), $sent )
            );

        } else if ( 'active' === $item->status ) {
            $event = $item->event;
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

            return sprintf(
                    '<span class="list-table-status active">%s</span>', $status
            );

        } else if ( 'inactive' === $item->status  ) {
            return sprintf(
                '<span class="list-table-status paused">%s</span>', __( 'Paused', 'erp-email-campaign' )
            );
        }

        if ( ( 'paused' !== $item->status && 'draft' !== $item->status ) && 'scheduled' === $item->send && ! empty( $item->deliver_at ) && ( strtotime( $item->deliver_at ) > current_time( 'timestamp' ) ) ) {
            $progress_bar = sprintf(
                '<span class="schedule-label"><i class="dashicons dashicons-clock"></i> %s: %s</span>',
                __( 'send at', 'erp-email-campaign' ),
                date( 'Y-m-d g:i a', strtotime( $item->deliver_at ) )
            );

            return sprintf(
                '<div id="campaign-id-%s" class="status-container"><span class="list-table-status %s">%s</span>%s</div>',
                $item->id,
                'scheduled',
                __( 'Scheduled', 'erp-email-campaign' ),
                $progress_bar
            );
        }

        $progress_bar = '';
        if ( $this->total_people ) {
            $perc = ( $sent / $this->total_people ) * 100;
            $progress_bar = '<span class="campaign-progress-bar clearfix"><span class="progress-done" style="width: ' . $perc . '%;"></span><span class="progress-text">' . $sent . ' / ' . $this->total_people . '</span></span>';
        }

        $label = isset( erp_email_campaign()->statuses[ $item->status ] ) ? erp_email_campaign()->statuses[ $item->status ]['label'] : '';

        if ( 'scheduled' === $item->status ) {
            $label .= ' to be sent on ' . date( 'M j h:i a', strtotime( $item->deliver_at ) );
        }

        return sprintf(
            '<div id="campaign-id-%s" class="status-container"><span class="list-table-status %s">%s</span>%s</div>',
            $item->id,
            $item->status,
            $label,
            $progress_bar
        );
    }

    /**
     * Render the Lists column data
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_lists( $item ) {
        $lists = [];

        if ( empty( $this->lists ) ) {
            return '-';
        }

        foreach ( $this->lists as $type => $pair ) {
            $lists = array_merge( $lists, wp_list_pluck( $pair, 'title' ) );
        }

        return implode( ', ' , $lists );
    }

    /**
     * Render the Open column data
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_open( $item ) {
        if ( empty( $this->people ) ) {
            return '-';
        }

        $opened = wp_list_pluck( $this->people, 'open' );
        $opened = count( array_filter( $opened ) );

        if ( $this->total_people ) {
            echo ceil( ( $opened / $this->total_people ) * 100 ) . '%';
        } else {
            echo '0%';
        }
    }

    /**
     * Render the Clicks column data
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_click( $item ) {
        if ( empty( $this->people ) ) {
            return '-';
        }

        $clicked = $item->urlStats()->groupBy('people_id')->get()->count();

        if ( $this->total_people ) {
            echo ceil( ( $clicked / $this->total_people ) * 100 ) . '%';
        } else {
            echo '0%';
        }
    }

    /**
     * Render the Unsubscribed column data
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_unsubscribed( $item ) {
        if ( empty( $this->people ) ) {
            return '-';
        }

        $unsubscribed = wp_list_pluck( $this->people, 'unsubscribed' );
        $unsubscribed = count( array_filter( $unsubscribed ) );

        if ( $this->total_people ) {
            echo ceil( ( $unsubscribed / $this->total_people ) * 100 ) . '%';
        } else {
            echo '0%';
        }
    }

    /**
     * Render the bounced column data
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_bounced( $item ) {
        if ( empty( $this->people ) ) {
            return '-';
        }

        $bounced = wp_list_pluck( $this->people, 'bounced' );
        $bounced = count( array_filter( $bounced ) );

        if ( $this->total_people ) {
            echo ceil( ( $bounced / $this->total_people ) * 100 ) . '%';
        } else {
            echo '0%';
        }
    }

}
