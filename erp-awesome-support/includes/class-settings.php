<?php
namespace WeDevs\ERP\Awesome_Support;

/**
 * Settings class
 *
 * @since 1.0.0
 *
 * @package WPERP|Awesome Support
 */
class Settings {

    /**
     * Constructor function
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'erp_settings_crm_sections', [ $this, 'crm_sections_awesome_support' ] );

        // Add fields to ERP Settings Awesome Support section
        add_filter( 'erp_settings_crm_section_fields', [ $this, 'crm_sections_awesome_support_fields' ], 10, 2 );
    }

    /**
     * Add plugin settings area in CRM settings tab
     *
     * @param array $sections
     *
     * @return array
     */
    public function crm_sections_awesome_support( $sections ) {
        $sections['awesome_support'] = __( 'Awesome Support', 'erp-awesome-support' );
        return $sections;
    }

    /**
     * Settings fields for HelpScout
     *
     * @param array  $fields
     *
     * @return array
     */
    public function crm_sections_awesome_support_fields( $fields ) {
        $life_stages = erp_crm_get_life_stages_dropdown_raw();
        $crm_users   = erp_crm_get_crm_user();
        $users       = [ '' => __( '&mdash; Select Owner &mdash;', 'erp-awesome-support' ) ];

        foreach ( $crm_users as $user ) {
            $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
        }


        $fields['awesome_support'][] = [
            'title' => __( 'Awesome Support Setting', 'erp-awesome-support' ),
            'type'  => 'title',
        ];

        $fields['awesome_support'][] = [
            'title'   => __( 'Customer life stage', 'erp-awesome-support' ),
            'type'    => 'select',
            'options' => $life_stages,
            'id'      => 'erp_awesome_support_ls',
            'desc'    => __( 'When user open a ticket, then which life stage you want to choose for that contact( default : Opportunity )', 'erp-awesome-support' ),
            'class'   => 'erp-select2',
            'tooltip' => true,
            'default' => 'customer'
        ];

        $fields['awesome_support'][] = [
            'title'   => __( 'Default Contact Owner', 'erp-awesome-support' ),
            'type'    => 'select',
            'options' => $users,
            'id'      => 'erp_awesome_support_owner',
            'desc'    => __( 'Default contact owner for contact.', 'erp-awesome-support' ),
            'class'   => 'erp-select2',
            'tooltip' => true,
            'default' => 'customer'
        ];

        $fields['awesome_support'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

        return $fields;
    }


}
