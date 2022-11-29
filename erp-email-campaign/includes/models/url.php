<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;

class URL extends Model {

    protected $table    = 'erp_crm_email_campaigns_urls';
    public $timestamps  = false;
    protected $fillable = [ 'url' ];

    /**
     * Stats
     *
     * @return array
     */
    public function stats() {
        $stats = $this->hasMany( 'WeDevs\ERP\CRM\EmailCampaign\Models\URLStat', 'url_id' );

        return $stats;
    }
}
