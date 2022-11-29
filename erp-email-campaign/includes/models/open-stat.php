<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;

class OpenStat extends Model {

    protected $table    = 'erp_crm_email_campaigns_open_stats';
    public $timestamps  = false;
    protected $fillable = [ 'campaign_id', 'people_id', 'opened_at' ];


    /**
     * Relation to EmailCampaign model
     *
     * @return array
     */
    public function campaign() {
        $campaign = $this->belongsTo( 'WeDevs\ERP\CRM\EmailCampaign\Models\EmailCampaign', 'campaign_id' );

        return $campaign;
    }

    /**
     * Relation to People model
     *
     * @return array
     */
    public function people() {
        $people = $this->belongsTo( 'WeDevs\ERP\CRM\EmailCampaign\Models\People', 'people_id' );

        return $people;
    }
}
