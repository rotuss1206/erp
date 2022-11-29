<?php
namespace WeDevs\ERP\Awesome_Support;

/**
 * Import class
 *
 * @since 1.0.0
 *
 * @package WPERP|Awesome Support
 */
class Import {

    public function __construct() {
        add_action('erp_crm_contact_sources', [$this, 'add_contact_source']);
        add_action('wpas_open_ticket_after', [$this, 'insert_contact']);
    }

    /**
     * Add awesome support source
     *
     * @since 1.0.0
     *
     * @param $sources
     *
     * @return mixed
     */
    public function add_contact_source($sources){
        $sources['awesome_support'] = __('Awesome Support', 'erp-awesome-support');

        return $sources;
    }


    /**
     * Inset or update user on ticket submit
     *
     * @since 1.0.0
     * @return bool|void
     */
    public function insert_contact(){

        if ( ! is_user_logged_in() ) {
            return;
        }

        $user = get_user_by( 'id', get_current_user_id() );
        if ( is_wp_error( $user ) ) {
            return;
        }

        $args = [
            'first_name' => $user->user_firstname,
            'last_name'  => $user->user_lastname,
            'email'      => $user->user_email,
            'company'    => '',
            'phone'      => '',
            'country'    => '',
            'website'    => '',
            'type'       => 'contact',
        ];

        $people = erp_insert_people( $args, true );

        if ( is_wp_error( $people ) ) {
            return false;
        }

        $contact = new \WeDevs\ERP\CRM\Contact( absint( $people->id ), 'contact' );

        $life_stage    = erp_get_option('erp_awesome_support_ls', false, 'customer');
        $contact_owner    = erp_get_option('erp_awesome_support_owner', false, erp_crm_get_default_contact_owner());

        if ( ! $people->existing ) {
            $contact->update_meta( 'life_stage', $life_stage );
            $contact->update_meta( 'source', 'awesome_support' );
            $contact->update_meta( 'contact_owner', $contact_owner );
        } else {
            if ( ! $contact->get_source() ) {
                $contact->update_meta( 'source', 'optin_form' );
            }

            if ( ! $contact->get_life_stage() ) {
                $contact->update_meta( 'life_stage', $life_stage );
            }

            if ( ! $contact->get_contact_owner() ) {
                $contact->update_meta( 'contact_owner', $contact_owner );
            }
        }

    }
}
