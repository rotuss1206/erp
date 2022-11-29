<?php
namespace WeDevs\ERP\CRM\EmailCampaign\Models;

use WeDevs\ERP\Framework\Model;

class Templates extends Model {

    protected $table    = 'erp_crm_email_campaigns_templates';
    public $timestamps  = false;
    protected $fillable = [ 'campaign_id', 'template', 'html', 'links' ];

    /**
     * Get a template preset
     *
     * @param object $query
     * @param int    $presetId
     *
     * @return object
     */
    public function scopeGetPreset( $query, $presetId ) {
        return $query->getQuery()->getConnection()->table( 'erp_crm_email_campaigns_template_presets' )
                     ->where( 'id', '=', $presetId )->first();
    }

    /**
     * Get all base templates
     *
     * 'template' column is not selected
     *
     * @param object $query
     *
     * @return array
     */
    public function scopeGetBaseTemplates( $query ) {
        return $query->getQuery()->getConnection()->table( 'erp_crm_email_campaigns_template_presets' )
                     ->select( 'id', 'title', 'name' )->whereNull( 'category' )->get();
    }

    /**
     * Get Themes by category
     *
     * @param object $query
     *
     * @return array
     */
    public function scopeGetThemesByCategory( $query ) {
        $prefix = $query->getQuery()->getConnection()->db->prefix;

        $results = $query->getQuery()->getConnection()->table( 'erp_crm_email_campaigns_template_presets AS presets' )
                     ->select( 'presets.id', 'presets.title', 'presets.name', 'cat.id AS cat_id', 'cat.title AS category' )
                     ->leftJoin( "{$prefix}erp_crm_email_campaigns_template_preset_categories AS cat", 'presets.category', '=', 'cat.id' )
                     ->where( 'category', '!=', 0 )
                     ->get();

        $themes = [];

        foreach ( $results as $theme ) {
            if ( empty( $themes[ $theme->cat_id ]['title'] ) ) {
                $themes[ $theme->cat_id ] = [
                    'title' => $theme->category,
                ];
            }

            $themes[ $theme->cat_id ]['themes'][] = [
                'id'    => $theme->id,
                'title' => $theme->title,
                'name'  => $theme->name
            ];

        }

        return $themes;
    }
}
