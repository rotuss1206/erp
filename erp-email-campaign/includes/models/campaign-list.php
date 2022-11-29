<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;
use WeDevs\ERP\CRM\Models\ContactGroup;
use WeDevs\ERP\CRM\Models\SaveSearch;

class CampaignList extends Model {

    protected $table    = 'erp_crm_email_campaigns_lists';
    public $timestamps  = false;
    protected $fillable = [ 'type', 'type_id' ];

    /**
     * Returns an array of ids of selected list types
     *
     * @param object $query
     *
     * @return array
     */
    public function scopeIdsOnly( $query ) {
        return wp_list_pluck( $query->select( 'type_id' )->get()->toArray(), 'type_id' );
    }

    /**
     * Returns list titles grouped by list type
     *
     * @param object $query
     *
     * @return array
     */
    public function scopeListsByType( $query ) {
        $lists = [];

        $type_and_ids = collect( $query->select( 'type', 'type_id' )->get()->toArray() )->groupBy( 'type' )->toArray();

        foreach ( $type_and_ids as $type => $pair ) {
            $ids = wp_list_pluck( $pair, 'type_id' );

            switch ( $type ) {
                case 'contact_groups':
                    $lists['contact_groups'] = ContactGroup::select( 'id', 'name as title' )->whereIn( 'id', $ids )->get()->toArray();
                    break;

                case 'save_searches':
                    $lists['save_searches'] = SaveSearch::select( 'id', 'search_name as title', 'search_val as search' )->whereIn( 'id', $ids )->get()->toArray();
                    break;
            }
        }

        return $lists;
    }

}
