<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Events extends Model {

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

    protected $table    = 'erp_crm_email_campaigns_events';
    public $timestamps  = false;
    protected $fillable = [ 'campaign_id', 'action', 'arg_value', 'schedule_type', 'schedule_offset' ];

    /**
     * Belongs to relationship
     *
     * @return object
     */
    public function campaign() {
        return $this->belongsTo( 'WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign', 'campaign_id' );
    }

}
