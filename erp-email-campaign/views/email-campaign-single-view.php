<script>
    var campaignEmailStats = JSON.parse('<?php echo json_encode( $email_stats ); ?>');
</script>
<div class="wrap erp erp-email-campaign erp-email-campaign-single send-<?php echo $campaign->send; ?>" id="erp-email-campaign-single">
    <h1 style="margin-bottom: 15px;"><?php _e( 'Campaign', 'erp-email-campaign' ); ?> : <?php echo $campaign->email_subject; ?></h1>

    <div class="list-table-wrap erp-grid-container">
        <div class="row">
            <div class="col-3">
                <div class="postbox ecamp-single-summery" style="height: 312px;">
                    <h3 class="hndle"><span><?php _e( 'Summery', 'erp-email-campaign' ); ?></span></h3>

                    <table class="wp-list-table widefat fixed striped valign-top table-summery">
                        <tbody>
                            <tr>
                                <th><?php _e( 'Status', 'erp-email-campaign' ); ?></th>
                                <td>
                                    <?php if ( ( 'paused' !== $campaign->status && 'draft' !== $campaign->status) && 'scheduled' === $campaign->send && ! empty( $campaign->deliver_at ) && ( strtotime( $campaign->deliver_at ) > current_time( 'timestamp' ) ) ): ?>
                                            <span class="list-table-status scheduled">
                                                <?php _e( 'Scheduled', 'erp-email-campaign' ); ?>
                                                <span class="schedule-label"><i class="dashicons dashicons-clock"></i> <?php _e( 'send at', 'erp-email-campaign' ) ?>: <?php echo date( 'Y-m-d g:i a', strtotime( $campaign->deliver_at ) ) ?></span>
                                            </span>
                                    <?php else: ?>
                                        <span class="list-table-status <?php echo $campaign->status; ?>">
                                            <?php if ( 'active' === $campaign->status ): ?>
                                                <?php echo $campaign->get_active_campaign_status(); ?>
                                            <?php elseif( 'sent' === $campaign->status ): ?>
                                                <?php printf( __( 'Sent to %s subscribers', 'erp-email-campaign' ), $sent ); ?>
                                            <?php else: ?>
                                                <?php echo $campaign->email_campaign->statuses[ $campaign->status ]['label']; ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Subject', 'erp-email-campaign' ); ?></th>
                                <td>
                                    <?php echo $campaign->email_subject; ?>
                                </td>
                            </tr>

                            <?php if ( 'automatic' !== $campaign->send ): ?>
                                <tr>
                                    <th><?php _e( 'Lists', 'erp-email-campaign' ); ?></th>
                                    <td>
                                        <?php
                                            $list_titles = $campaign->get_list_titles();
                                            echo empty( $list_titles ) ? '-' : implode( ', ' , $campaign->get_list_titles() );
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th><?php _e( 'From', 'erp-email-campaign' ); ?></th>
                                <td>
                                    <?php echo $campaign->sender_name; ?> &lt;<?php echo $campaign->sender_email; ?>&gt;
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Reply To', 'erp-email-campaign' ); ?></th>
                                <td>
                                    <?php echo $campaign->reply_to_name; ?> &lt;<?php echo $campaign->reply_to_email; ?>&gt;
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="ecamp-single-summery-btns">
                        <a href="#duplicate" class="button duplicate-campaign" data-campaign="<?php echo $campaign->id; ?>">
                            <?php _e( 'Duplicate', 'erp-email-campaign' ); ?>
                        </a>

                        <a href="<?php echo site_url( '?erp-email-campaign=1&view-email-in-browser=1&campaign=' . $campaign->id ); ?>" target="_blank" class="button">
                            <?php _e( 'View', 'erp-email-campaign' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="postbox single-campaign-chart">
                    <h3 class="hndle"><span><?php _e( 'Email Stats', 'erp-email-campaign' ); ?></span></h3>

                    <div class="postbox-inside">
                        <div id="ecmap-single-email-stats" style="width: 100%; height: 250px;">
                            <?php if ( empty( $email_stats ) ): ?>
                                <p class="text-center">
                                    <?php _e( 'No email statistic found for this campaign', 'erp-email-campaign' ); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h3><span><?php _e( 'Link Statistics', 'erp-email-campaign' ); ?></span></h3>
        <div class="postbox">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="column-serial">#</th>
                        <th class="campaign-link"><?php _e( 'Links', 'erp-email-campaign' ); ?></th>
                        <th class="click-counts text-center"><?php _e( 'Unique Clicks', 'erp-email-campaign' ); ?></th>
                        <th class="click-counts text-center"><?php _e( 'Total Clicks', 'erp-email-campaign' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $url_stats ) ): ?>
                        <tr>
                            <td class="text-center" colspan="4"><?php _e( 'No link statistic found for this campaign', 'erp-email-campaign' ); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach( $url_stats as $i => $stat ): ?>
                            <tr>
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo $stat->url; ?></td>
                                <td class="text-center"><?php echo $stat->unique_click; ?></td>
                                <td class="text-center"><?php echo $stat->total_click; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <hr>

        <h3><span><?php _e( 'Campaign Subscribers', 'erp-email-campaign' ); ?></span></h3>
        <div id="campaign-people-stats">
            <vtable v-ref:vtable
                table-class="customers"
                action="get_campaign_people_data"
                :wpnonce="wpnonce"
                page="<?php echo ecamp_admin_url( [], 'erp-email-campaign' ); ?>"
                per-page="10"
                :top-nav-filter="topNavFilter"
                :extra-bulk-action = "groupFilter"
                :fields="fields"
                :search="search"
                hide-cb="hide"
                after-fetch-data="afterFetchData"
            ></vtable>

            <div id="erp-email-campaign-subscriber-details" class="erp-email-campaign-modal" tabindex="-1" role="dialog">
                <div class="erp-email-campaign-modal-dialog" role="document">
                    <div class="erp-email-campaign-modal-content">
                        <div class="erp-email-campaign-modal-header">
                            <button type="button" class="erp-close" data-dismiss="erp-email-campaign-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="erp-email-campaign-modal-title"><?php _e( "Subscriber's campaign activity", 'erp-email-campaign' ); ?></h4>
                        </div>
                        <div v-if="subscriberDetailsIsFetching" class="erp-email-campaign-modal-body">
                            <div class="text-center">
                                <div class="erp-spinner"></div><br>
                                <?php _e( 'Loading activity data', 'erp-email-campaign' ); ?>...
                            </div>
                        </div>
                        <div v-else class="erp-email-campaign-modal-body">
                            <div class="clearfix">
                                <p class="subscriber-profile pull-left">
                                    <img :src="subscriberInfo.avatar" alt="">
                                    <a :href="subscriberInfo.details_url">{{ subscriberInfo.first_name }} {{ subscriberInfo.last_name }}</a>
                                    {{ subscriberInfo.email }}
                                </p>
                            </div>

                            <div class="subscriber-activities">
                                <div v-for="timeLineItem in timeLineItems" class="subscriber-activity">
                                    <i class="timeline-icon"></i>
                                    <div class="timeline-item-content">
                                        <p v-if="'sent' === timeLineItem.type">
                                            <?php _e( 'Email sent', 'erp-email-campaign' ); ?>
                                        </p>
                                        <p v-if="'open' === timeLineItem.type">
                                            <?php _e( 'Opened email', 'erp-email-campaign' ); ?>
                                        </p>
                                        <p v-if="'url' === timeLineItem.type">
                                            <?php _e( 'Clicked link', 'erp-email-campaign' ); ?> <a target="_blank" :href="timeLineItem.url">{{ timeLineItem.url }}</a>
                                        </p>
                                        <span class="timeline-time">{{ getTimeLineDateTime(timeLineItem.time) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>

    </div><!-- .list-table-wrap -->
</div>
