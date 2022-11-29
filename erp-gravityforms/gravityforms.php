<?php
namespace WeDevs\ERP\CRM\ContactForms;

use WeDevs\ERP\Framework\Traits\Hooker;

class Gravity_Forms {

    use Hooker;

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @return object class instance
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * The class constructor
     *
     * @return void
     */
    public function __construct() {
        $this->filter( 'erp_contact_forms_plugin_list', 'add_to_plugin_list' );
        $this->action( 'crm_get_gravity_forms_forms', 'get_forms' );
        $this->action( 'gform_after_submission', 'after_form_submit' );
    }

    /**
     * Add Gravity Forms in integration list
     *
     * @param array
     *
     * @return array
     */
    public function add_to_plugin_list( $plugins ) {
        $plugins['gravity_forms'] = array(
            'title' => __( 'Gravity Forms', 'erp-gravityforms' ),
            'is_active' => class_exists( 'GFForms' )
        );

        return $plugins;
    }

    /**
     * Get all Gravity Forms forms and their fields
     *
     * @return array
     */
    function get_forms() {
        $forms = array();

        $gf_forms = \GFFormsModel::get_forms( true );

        $saved_settings = get_option( 'wperp_crm_contact_forms', '' );

        foreach ( $gf_forms as $key => $form ) {
            $forms[ $form->id ] = array(
                'name' => $form->id,
                'title' => $form->title,
                'fields' => array(),
                'contactGroup' => '0',
                'contactOwner' => '0'
            );

            $form_meta = \GFFormsModel::get_form_meta( $form->id );

            foreach ( $form_meta['fields'] as $field ) {
                $field = \GF_Fields::create( $field );

                if ( empty( $field['inputs'] ) ) {
                    $forms[ $form->id ]['fields'][ $field->id ] = $field->label;

                    if ( !empty( $saved_settings['gravity_forms'][ $form->id ]['map'][ $field->id ] ) ) {
                        $crm_option = $saved_settings['gravity_forms'][ $form->id ]['map'][ $field->id ];
                    } else {
                        $crm_option = '';
                    }

                    $forms[ $form->id ]['map'][ $field->id ] = !empty( $crm_option ) ? $crm_option : '';

                } else {
                    foreach ( $field['inputs'] as $i => $group_field) {
                        if ( empty( $group_field['isHidden'] ) ) {
                            $forms[ $form->id ]['fields'][ $group_field['id'] ] = $group_field['label'];

                            if ( !empty( $saved_settings['gravity_forms'][ $form->id ]['map'][ $group_field['id'] ] ) ) {
                                $crm_option = $saved_settings['gravity_forms'][ $form->id ]['map'][ $group_field['id'] ];
                            } else {
                                $crm_option = '';
                            }

                            $forms[ $form->id ]['map'][ $group_field['id'] ] = !empty( $crm_option ) ? $crm_option : '';
                        }
                    }
                }
            }

            if ( !empty( $saved_settings['gravity_forms'][ $form->id ]['contact_group'] ) ) {
                $forms[ $form->id ]['contactGroup'] = $saved_settings['gravity_forms'][ $form->id ]['contact_group'];
            }

            if ( !empty( $saved_settings['gravity_forms'][ $form->id ]['contact_owner'] ) ) {
                $forms[ $form->id ]['contactOwner'] = $saved_settings['gravity_forms'][ $form->id ]['contact_owner'];
            }
        }

        return $forms;
    }

    /**
     * After gravity form submission hook
     *
     * With the do_action call, this function will provide the
     * contact data to the \WeDevs\ERP\CRM -> save_submitted_form_data function
     *
     * @param array $submitted_data The form submitted data.
     *
     * @return void
     */
    function after_form_submit( $submitted_data ) {
        $form_id = $submitted_data['form_id'];

        $cfi_settings = get_option( 'wperp_crm_contact_forms', '' );

        if ( !empty( $cfi_settings['gravity_forms'][ $form_id ] ) ) {

            $gforms_settings = $cfi_settings['gravity_forms'][ $form_id ];

            if ( in_array( 'country' , $gforms_settings ) ) {
                $field_id = array_search( 'country' , $gforms_settings );
                $country = $submitted_data[ $field_id ];

                if ( !empty( $country ) ) {
                    $crm_countries = \WeDevs\ERP\Countries::instance()->get_countries();
                    $country_abbr = array_search( $country , $crm_countries );

                    $submitted_data[ $field_id ] = $country_abbr;
                }
            }

            do_action( 'wperp_integration_gravity_forms_form_submit', $submitted_data, 'gravity_forms', $form_id );
        }
    }

}

Gravity_Forms::init();