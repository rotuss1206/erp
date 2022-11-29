<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;

class People extends Model {

    protected $table    = 'erp_crm_email_campaigns_people';
    public $timestamps  = false;
    protected $fillable = [ 'people_id', 'campaign_id', 'hash', 'sent', 'open', 'bounced' ];

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
