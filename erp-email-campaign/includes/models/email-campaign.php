<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\Paginator;

/**
 * EmailCampign model
 *
 * @since 1.0.0
 * @since 1.2.0 Save `deliver_at` in GMT offset and get in current wp timezone offset
 */
class EmailCampaign extends Model {

    use SoftDeletes;

    protected $table    = 'erp_crm_email_campaigns';
    public $timestamps  = true;
    protected $fillable = [
        'email_subject', 'status', 'sender_name', 'sender_email',
        'reply_to_name', 'reply_to_email', 'send', 'campaign_name',
        'deliver_at'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

    /**
     * Fetch campaign with status
     *
     * Statues: all(without trashed), scheduled, paused, sent, draft, in_progress
     *
     * @param object $query
     * @param string|array $status
     *
     * @return object
     */
    public function scopeStatus( $query, $status = 'all' ) {

        if ( is_array( $status ) ) {
            return $query->whereIn( 'status', $status );

        } else {

            if ( 'all' === $status ) {
                return $query->where( 'status', '!=', '' );
            }

            return $query->where( 'status', '=', $status );
        }
    }

    /**
     * Templates
     *
     * @return object
     */
    public function template() {
        $template = $this->hasOne( 'WeDevs\ERP\CRM\EmailCampaign\Models\Templates', 'campaign_id' );

        return $template;
    }

    /**
     * Campaign Lists
     *
     * @since 1.0.0
     * @since 1.1.0 Rename from `list` to `campaignLists`
     *
     * @param string $type
     *
     * @return object
     */
    public function campaignLists( $type = 'all' ) {
        $lists = $this->hasMany( 'WeDevs\ERP\CRM\EmailCampaign\Models\CampaignList', 'campaign_id' );

        if ( 'all' !== $type ) {
            $lists = $lists->where( 'type', '=', $type );
        }

        return $lists;
    }

    /**
     * Event for automatic campaigns
     *
     * @return object
     */
    public function event() {
        $event = $this->hasOne( 'WeDevs\ERP\CRM\EmailCampaign\Models\Events', 'campaign_id' );

        return $event;
    }

    /**
     * Campaign Subscribers
     *
     * @return object
     */
    public function people() {
        $people = $this->hasMany( 'WeDevs\ERP\CRM\EmailCampaign\Models\People', 'campaign_id' );

        return $people;
    }

    /**
     * Campaign Subscribers which are on queue
     *
     * @return object
     */
    public function peopleQueue() {
        $people = $this->hasMany( 'WeDevs\ERP\CRM\EmailCampaign\Models\PeopleQueue', 'campaign_id' );

        return $people;
    }

    /**
     * URL Stats
     *
     * @return object
     */
    public function urlStats() {
        $stats = $this->hasMany( 'WeDevs\ERP\CRM\EmailCampaign\Models\URLStat', 'campaign_id' );

        return $stats;
    }

    /**
     * Accessor for deliver_at
     *
     * @since 1.1.0
     *
     * @param string $value
     *
     * @return string
     */
    public function getDeliverAtAttribute( $deliver_at ) {
        $deliver_at = ! empty( $deliver_at ) ? ecamp_gmt_to_tz( $deliver_at ) : null;

        return $deliver_at;
    }

    /**
     * Mutator for deliver_at
     *
     * @since 1.1.0
     *
     * @param string $value
     *
     * @return string
     */
    public function setDeliverAtAttribute( $deliver_at ) {
        $deliver_at = ! empty( $deliver_at ) ? ecamp_convert_to_gmt( $deliver_at ) : null;

        $this->attributes['deliver_at'] = $deliver_at;
    }
}
