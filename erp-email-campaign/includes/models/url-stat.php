<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;

class URLStat extends Model {

    protected $table    = 'erp_crm_email_campaigns_url_stats';
    public $timestamps  = false;
    protected $fillable = [ 'url_id', 'campaign_id', 'people_id', 'clicked_at' ];


    /**
     * URL Stats
     *
     * @return array
     */
    public function url() {
        $url = $this->belongsTo( 'WeDevs\ERP\CRM\EmailCampaign\Models\URL', 'url_id' );

        return $url;
    }
}
