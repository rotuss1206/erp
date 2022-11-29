<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeopleQueue extends Model {

    use SoftDeletes;

    protected $table    = 'erp_crm_email_campaigns_people_queue';
    public $timestamps  = false;
    protected $fillable = [ 'campaign_id', 'people_id', 'send_at' ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

    /**
     * Belongs to relationship
     *
     * @return object
     */
    public function campaign() {
        $campaign = $this->belongsTo( 'WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign', 'campaign_id' );

        return $campaign;
    }
}
