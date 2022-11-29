<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\CRM\EmailCampaign\Models\Templates as TemplatesModel;

class Templates {

    /**
     * Contact custom meta by ERP Field Builder addon
     *
     * @var array
     */
    private $contact_custom_fields;

    /**
     * Contact custom meta by ERP Field Builder addon
     *
     * @var array
     */
    private $company_custom_fields;

    /**
     * Company details
     *
     * @var array
     */
    private $company_details;

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Templates constructor
     *
     * @return void
     */
    public function __construct() {
        // if ( is_plugin_active( 'erp-field-builder/erp-field-builder.php' ) ) {
            $this->contact_custom_fields = get_option( 'erp-contact-fields', [] );
            $this->company_custom_fields = get_option( 'erp-company-fields', [] );
        // }
    }

    /**
     * Returns campaign's template which is in JSON format
     *
     * @since 1.0.0
     * @since 1.1.0 Different decode methods for templates saved in v1.0.0 and after v1.0.0
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function get_campaign_template( $campaign_id ) {
        if ( !empty( $campaign_id ) ) {
            $campaign_id = absint( $campaign_id );
            $template_data = TemplatesModel::where( 'campaign_id', $campaign_id )->first();

        } else {
            $template_data = TemplatesModel::getPreset(1);
            $template_data->plugin_version = WPERP_EMAIL_CAMPAIGN_VERSION;
        }

        if ( !empty( $template_data->template ) ) {
            // templates that saved in before v1.1.0 have to unslashed first
            if ( !isset( $template_data->plugin_version ) || version_compare( $template_data->plugin_version, '1.1.0', '<' ) ) {
                $template = json_decode( wp_unslash( $template_data->template ), true );
            } else {
                $template = json_decode( $template_data->template, true );
            }

        }

        $template = $this->get_template_missing_properties( $template );

        return $template;
    }

    /**
     * Get a template preset
     *
     * @since 1.0.0
     * @since 1.1.0 From 1.1.0 templates are saved without slashes. So no need to unslash it.
     *
     * @param id $preset_id
     *
     * @return object
     */
    public function get_preset( $preset_id ) {
        $preset = TemplatesModel::getPreset( $preset_id );

        $preset->template = json_decode( $preset->template, true );

        $preset->template = $this->get_template_missing_properties( $preset->template );

        return $preset;
    }

    /**
     * Properties introduced from 1.1.0
     *
     * @since 1.1.0
     *
     * @param array $template
     *
     * @return array
     */
    protected function get_template_missing_properties( $template ) {
        if ( empty( $template['globalCss']['fontFamily'] ) ) {
            $template['globalCss']['fontFamily'] = 'arial,helvetica,sans-serif';
        }

        if ( empty( $template['globalCss']['fontSize'] ) ) {
            $template['globalCss']['fontSize'] = '14px';
        }

        if ( empty( $template['globalCss']['color'] ) ) {
            $template['globalCss']['color'] = '#333';
        }

        if ( ! empty( $template['sections'] ) ) {
            foreach ( $template['sections'] as $i => $section ) {
                if ( empty( $section['rowContainerStyle']['fontFamily'] ) ) {
                    $template['sections'][$i]['rowContainerStyle']['fontFamily'] = 'inherit';
                }

                if ( empty( $section['rowContainerStyle']['fontSize'] ) ) {
                    $template['sections'][$i]['rowContainerStyle']['fontSize'] = 'inherit';
                }

                if ( empty( $section['rowContainerStyle']['color'] ) ) {
                    $template['sections'][$i]['rowContainerStyle']['color'] = 'inherit';
                }
            }
        }

        return $template;
    }

    /**
     * Get all base templates
     *
     * 'template' column is not selected
     *
     * @return array
     */
    public function get_base_templates() {
        return TemplatesModel::getBaseTemplates();
    }

    /**
     * Get themes by category
     *
     * 'template' column is not selected
     *
     * @return array
     */
    public function get_themes_by_category() {
        return TemplatesModel::getThemesByCategory();
    }

    /**
     * Save email campaign template
     *
     * @since 1.0.0
     * @since 1.1.0 Use eloquent `save` method for both insert and update data.
     *              Save template version number.
     *              Match `https` links
     *
     * @param int   $campaign_id
     * @param array $template_data
     *
     * @return void
     */
    public function save_template( $campaign_id, $template_data ) {
        $templates_model = TemplatesModel::firstOrNew( [ 'campaign_id' => $campaign_id ] );

        $template_data_html = wp_unslash( $template_data['html'] );

        // link replacement
        preg_match_all( '/href="((http|https):\/\/.*?)"/' , $template_data_html, $matches );

        $links = [];
        if ( !empty( $matches[1] ) ) {

            foreach ( $matches[1] as $i => $match ) {
                $link = str_replace( "\\", "", $match );

                if ( !preg_match( '/jpg|jpeg|png|gif|bmp/' , $link ) ) {
                    $md5_hash = md5( $link );
                    $links[ $md5_hash ] = wp_unslash( $link );
                }
            }
        }

        // save template data
        $templates_model->campaign_id    = $campaign_id;
        $templates_model->template       = wp_unslash( $template_data['template'] );
        $templates_model->html           = $template_data_html;
        $templates_model->links          = json_encode( $links );
        $templates_model->plugin_version = WPERP_EMAIL_CAMPAIGN_VERSION;

        $templates_model->save();
    }

    /**
     * Render HTML for the email
     *
     * @since 1.0.0
     * @since 1.1.0 Different decode methods for templates saved in v1.0.0 and after v1.0.0
     *
     * @param int       $campaign_id
     * @param int       $people_id
     * @param string    $hash
     *
     * @return string html
     */
    public function render_email( $campaign_id, $people_id, $hash ) {
        $html = '';

        $templates_model = TemplatesModel::where( 'campaign_id', $campaign_id )->first();

        if ( $templates_model ) {
            // get responsive styles
            ob_start();
            echo file_get_contents( WPERP_EMAIL_CAMPAIGN_PATH . '/assets/css/email-template-styles-responsive.css' );
            $responsive_styles = ob_get_clean();

            // templates that saved in before v1.1.0 have to unslashed first
            if ( !isset( $templates_model->plugin_version ) || version_compare( $templates_model->plugin_version, '1.1.0', '<' ) ) {
                $html = wp_unslash( $templates_model->html );
            } else {
                $html = $templates_model->html;
            }

            // set hashes for links for tracking clicks
            $links = json_decode( $templates_model->links );

            if ( !empty( $links ) ) {

                foreach ( $links as $url_hash => $link ) {
                    $replace_by_str = site_url( "?erp-email-campaign=1&user={$hash}&url={$url_hash}" );
                    $html = str_replace( 'href="' . $link . '"',  'href="' . $replace_by_str . '"', $html );
                }

            }

            // set stock image urls
            $html = preg_replace( '/\.\.\/wp-content\/plugins\/erp-email-campaign/', WPERP_EMAIL_CAMPAIGN_URL, $html );

            // render shortcodes
            $html = $this->render_shortcodes( $html, $campaign_id, $people_id, $hash );

            // get tracker image to track if user open this email
            $tracker_image = ecamp_get_tracker_image( $hash );

            ob_start();
            include WPERP_EMAIL_CAMPAIGN_VIEWS . '/email.php';
            $html = ob_get_clean();

            // get CSS styles
            ob_start();
            echo file_get_contents( WPERP_EMAIL_CAMPAIGN_PATH . '/assets/css/email-template-styles.css' );
            $css = ob_get_clean();

            // apply CSS styles inline for picky email clients
            $emogrifier = new \WeDevs\ERP\Lib\Emogrifier( $html, $css );
            $html = $emogrifier->emogrify();

        }

        return $html;
    }

    /**
     * The available shortcodes used in email templates
     *
     * @since 1.0.0
     * @since 1.1.0 Add $type param
     *
     * @param string $type If type name is passed then function will return the shortcodes under that type
     *
     * @return array
     */
    public function shortcodes( $type = '' ) {
        $shortcodes = [];

        $shortcodes['user'] = [
            'title' => __( 'User', 'erp-email-campaign' ),
            'codes' => [
                'first_name'        => [ 'title' => __( 'First Name', 'erp-email-campaign' ), 'default' => 'reader' ],
                'last_name'         => [ 'title' => __( 'Last Name', 'erp-email-campaign' ), 'default' => 'reader' ],
                'email'             => [ 'title' => __( 'Email', 'erp-email-campaign' ), 'placeholder' => 'recipient@example.com' ],
                'company'           => [ 'title' => __( 'Company', 'erp-email-campaign' ), 'placeholder' => __( 'Company Name', 'erp-email-campaign' ) ],
                'phone'             => [ 'title' => __( 'Phone', 'erp-email-campaign' ), 'placeholder' => '8801000000000' ],
                'mobile'            => [ 'title' => __( 'Mobile', 'erp-email-campaign' ), 'placeholder' => '8801000000000' ],
                'other'             => [ 'title' => __( 'Other', 'erp-email-campaign' ), 'placeholder' => __( 'other informations', 'erp-email-campaign' ) ],
                'website'           => [ 'title' => __( 'Website', 'erp-email-campaign' ), 'placeholder' => 'http://example.com' ],
                'fax'               => [ 'title' => __( 'Fax', 'erp-email-campaign' ), 'placeholder' => '(880) 100 0000000' ],
                'notes'             => [ 'title' => __( 'Notes', 'erp-email-campaign' ), 'placeholder' => __( 'notes', 'erp-email-campaign' ) ],
                'street_1'          => [ 'title' => __( 'Street 1', 'erp-email-campaign' ), 'placeholder' => __( 'Street address 1', 'erp-email-campaign' ) ],
                'street_2'          => [ 'title' => __( 'Street 2', 'erp-email-campaign' ), 'placeholder' => __( 'Street address 2', 'erp-email-campaign' ) ],
                'city'              => [ 'title' => __( 'City', 'erp-email-campaign' ), 'placeholder' => __( 'City Name', 'erp-email-campaign' ) ],
                'state'             => [ 'title' => __( 'State', 'erp-email-campaign' ), 'placeholder' => __( 'State Name', 'erp-email-campaign' ) ],
                'postal_code'       => [ 'title' => __( 'Postal Code', 'erp-email-campaign' ), 'placeholder' => '1216' ],
                'country'           => [ 'title' => __( 'Country', 'erp-email-campaign' ), 'placeholder' => __( 'Country Name', 'erp-email-campaign' ) ],
                'currency_code'     => [ 'title' => __( 'Currency Code', 'erp-email-campaign' ), 'placeholder' => 'USD' ],
                'currency_symbol'   => [ 'title' => __( 'Currency Symbol', 'erp-email-campaign' ), 'placeholder' => '$' ],
                'currency_name'     => [ 'title' => __( 'Currency Name', 'erp-email-campaign' ), 'placeholder' => __( 'US Dollar', 'erp-email-campaign' ) ],
            ]
        ];

        if ( !empty( $this->contact_custom_fields ) ) {
            $shortcodes['meta']['title'] = __( 'Contact Custom Meta', 'erp-email-campaign' );

            foreach ( $this->contact_custom_fields as $field ) {
                $shortcodes['meta']['codes'][ $field['name'] ] = [ 'title' => $field['label'] ];
            }
        }

        if ( !empty( $this->company_custom_fields ) ) {
            $shortcodes['company_meta']['title'] = __( 'Company Custom Meta', 'erp-email-campaign' );

            foreach ( $this->company_custom_fields as $field ) {
                $shortcodes['company_meta']['codes'][ $field['name'] ] = [ 'title' => $field['label'] ];
            }
        }

        $shortcodes['newsletter'] = [
            'title' => __( 'Newsletter', 'erp-email-campaign' ),
            'codes' => [
                'newsletter_subject' => [ 'title' => __( 'Newsletter Subject', 'erp-email-campaign' ) ]
            ]
        ];

        $shortcodes['date'] = [
            'title' => __( 'Date', 'erp-email-campaign' ),
            'codes' => [
                'current_date'              => [ 'title' => __( 'Current date', 'erp-email-campaign' ) ],
                'current_day_full_name'     => [ 'title' => __( 'Full name of current day', 'erp-email-campaign' ) ],
                'current_day_short_name'    => [ 'title' => __( 'Short name of current day', 'erp-email-campaign' ) ],
                'current_month_number'      => [ 'title' => __( 'Current Month number', 'erp-email-campaign' ) ],
                'current_month_full_name'   => [ 'title' => __( 'Full name of current month', 'erp-email-campaign' ) ],
                'current_month_short_name'  => [ 'title' => __( 'Short name of current month', 'erp-email-campaign' ) ],
                'year'                      => [ 'title' => __( 'Year', 'erp-email-campaign' ) ],
            ]
        ];

        $this->company_details = get_company_details();

        $shortcodes['company'] = [
            'title' => __( 'Company', 'erp-email-campaign' ),
            'codes' => [
                'logo'      => [ 'title' => __( 'Logo', 'erp-email-campaign' ), 'plain_text' => true, 'text' => $this->company_details['logo'] ],
                'name'      => [ 'title' => __( 'Name', 'erp-email-campaign' ) ],
                'address'   => [ 'title' => __( 'Mailing Address', 'erp-email-campaign' ) ],
                'phone'     => [ 'title' => __( 'Phone', 'erp-email-campaign' ) ],
                'fax'       => [ 'title' => __( 'Fax', 'erp-email-campaign' ) ],
                'mobile'    => [ 'title' => __( 'Mobile', 'erp-email-campaign' ) ],
                'website'   => [ 'title' => __( 'Website', 'erp-email-campaign' ) ],
                'currency'  => [ 'title' => __( 'Currency', 'erp-email-campaign' ) ],
            ]
        ];

        $shortcodes['links'] = [
            'title' => __( 'Links', 'erp-email-campaign' ),
            'codes' => [
                'unsubscribe'       => [ 'title' => __( 'Unsubscribe Link', 'erp-email-campaign' ), 'text' => __( 'Unsubscribe', 'erp-email-campaign' ) ],
                'edit_subscription' => [ 'title' => __( 'Edit Subscription Page Link', 'erp-email-campaign' ), 'text' => __( 'Edit your subscription', 'erp-email-campaign' ) ],
                'archive'           => [ 'title' => __( 'Email Archive Link', 'erp-email-campaign' ), 'text' => __( 'View this email in your browser', 'erp-email-campaign' ) ],
            ]
        ];

        if ( !empty( $type ) && !empty( $shortcodes[ $type ] ) ) {
            return $shortcodes[ $type ];
        }

        return $shortcodes;
    }

    /**
     * Render the shortcodes present in template HTML
     *
     * @param string  $html
     * @param integer $campaign_id
     * @param integer $people_id
     *
     * @return string
     */
    public function render_shortcodes( $html, $campaign_id, $people_id = 0, $hash = '' ) {
        $subscriber = null;

        if ( $people_id ) {
            $subscriber = new \WeDevs\ERP\CRM\Contact( absint( $people_id ) );
        }

        foreach ( $this->shortcodes() as $type_name => $shortcode_type ) {
            foreach ( $shortcode_type['codes'] as $shortcode => $code_details ) {
                // skip if the code is plain text type
                if ( !empty( $code_details['plain_text'] ) ) {
                    continue;
                }

                preg_match_all( "/\[($type_name):($shortcode).*?\]/", $html, $matches );

                if ( !empty( $matches[1] ) && !empty( $matches[2] ) && method_exists( $this , "sc_$type_name" ) ) {
                    foreach ( $matches[0] as $i => $match ) {
                        $code_string = $matches[0][$i];
                        $replace_with = call_user_func( [ $this, "sc_$type_name" ], $campaign_id, $subscriber, $hash, $shortcode, $code_string );
                        $html = str_replace( $code_string , $replace_with, $html );
                    }
                }
            }
        }

        return $html;
    }

    /**
     * User Shortcodes
     *
     * @param int    $campaign_id
     * @param object $subscriber
     * @param string $hash
     * @param string $shortcode
     * @param string $code_string
     *
     * @return string
     */
    private function sc_user( $campaign_id, $subscriber, $hash, $shortcode, $code_string ) {
        $user = '';

        if ( empty( $subscriber->id ) ) {
            $user_shortcodes = $this->shortcodes( 'user' );
            $user_shortcodes = $user_shortcodes['codes'];

            switch ( $shortcode ) {

                case 'first_name':
                case 'last_name':
                    if ( preg_match( '/default=\\\"(.*?)\\\"/' , $code_string, $default ) ) {
                        $user = $default[1];

                    // from v1.1.0 we are saving template after using wp_unslash
                    } else if ( preg_match( '/default=\"(.*?)\"/' , $code_string, $default ) ) {
                        $user = $default[1];
                    }

                    break;

                default:
                    if ( !empty( $user_shortcodes[ $shortcode ]['placeholder'] ) ) {
                        $user = $user_shortcodes[ $shortcode ]['placeholder'];
                    }
                    break;
            }

        } else {
            switch ( $shortcode ) {
                case 'first_name':
                case 'last_name':

                    if ( !empty( $subscriber ) ) {
                        $user = $subscriber->$shortcode;
                    } else if ( preg_match( '/default=\\\"(.*?)\\\"/' , $code_string, $default ) ) {
                        $user = $default[1];
                    }

                    break;

                case 'country':
                    $user = erp_get_country_name( $subscriber->country );
                    break;

                case 'state':
                    $user = erp_get_state_name( $subscriber->country, $subscriber->state );
                    break;

                case 'currency_code':
                    $user = $subscriber->currency;
                    break;

                case 'currency_symbol':
                    $user = erp_get_currency_symbol( $subscriber->currency );
                    break;

                case 'currency_name':
                    $user = !empty( $currencies[ $subscriber->currency ] ) ? $currencies[ $subscriber->currency ] : '';
                    break;

                default:
                    $user = $subscriber->$shortcode;
                    break;
            }
        }

        return $user;
    }

    /**
     * Contact Meta
     *
     * Meta Created by ERP Field Builder addon
     *
     * @param int    $campaign_id
     * @param object $subscriber
     * @param string $hash
     * @param string $shortcode
     * @param string $code_string
     *
     * @return string
     */
    private function sc_meta( $campaign_id, $subscriber, $hash, $shortcode, $code_string ) {
        if ( empty( $subscriber->id ) ) {
            return '';
        }

        $field = array_filter( $this->contact_custom_fields, function ( $field ) use ( $shortcode ) {
            return $field['name'] === $shortcode;
        } );

        $field = array_pop( $field );

        $contact_meta = $subscriber->get_meta( $shortcode );

        if ( in_array( $field['type'] , [ 'radio', 'checkbox', 'select' ]) ) {
            $selected = '';

            foreach ( $field['options'] as $option ) {
                if ( $contact_meta === $option['value'] ) {
                    $contact_meta = $option['text'];
                }
            }
        }

        return $contact_meta;
    }

    /**
     * Company Meta
     *
     * Meta Created by ERP Field Builder addon
     *
     * @param int    $campaign_id
     * @param object $subscriber
     * @param string $hash
     * @param string $shortcode
     * @param string $code_string
     *
     * @return string
     */
    private function sc_company_meta( $campaign_id, $subscriber, $hash, $shortcode, $code_string ) {
        if ( empty( $subscriber->id ) ) {
            return '';
        }

        $field = array_filter( $this->company_custom_fields, function ( $field ) use ( $shortcode ) {
            return $field['name'] === $shortcode;
        } );

        $field = array_pop( $field );

        $company_meta = $subscriber->get_meta( $shortcode );

        if ( in_array( $field['type'] , [ 'radio', 'checkbox', 'select' ] ) ) {
            $selected = '';

            foreach ( $field['options'] as $option ) {
                if ( $company_meta === $option['value'] ) {
                    $company_meta = $option['text'];
                    break;
                }
            }
        }

        return $company_meta;
    }

    /**
     * Newsletter Shortcodes
     *
     * @param int    $campaign_id
     * @param object $subscriber
     * @param string $hash
     * @param string $shortcode
     * @param string $code_string
     *
     * @return string
     */
    private function sc_newsletter( $campaign_id, $subscriber, $hash, $shortcode, $code_string ) {
        $newsletter = '';

        if ( !empty( $campaign_id ) ) {
            switch ( $shortcode ) {
                case 'newsletter_subject':
                    $newsletter = erp_email_campaign()->get_campaign( $campaign_id )->email_subject;
                    break;
            }
        }

        return $newsletter;
    }

    /**
     * Date Shortcodes
     *
     * @param int    $campaign_id
     * @param object $subscriber
     * @param string $hash
     * @param string $shortcode
     * @param string $code_string
     *
     * @return string
     */
    private function sc_date( $campaign_id, $subscriber, $hash, $shortcode, $code_string ) {
        $date = '';

        switch ( $shortcode ) {
            case 'current_date':
                $date = date( 'd' );
                break;

            case 'current_day_full_name':
                $date = date( 'l' );
                break;

            case 'current_day_short_name':
                $date = date( 'D' );
                break;

            case 'current_month_number':
                $date = date( 'm' );
                break;

            case 'current_month_full_name':
                $date = date( 'F' );
                break;

            case 'current_month_short_name':
                $date = date( 'M' );
                break;

            case 'year':
                $date = date( 'Y' );
                break;

        }

        return $date;
    }

    /**
     * Company Shortcodes
     *
     * @param int    $campaign_id
     * @param object $subscriber
     * @param string $hash
     * @param string $shortcode
     * @param string $code_string
     *
     * @return string
     */
    private function sc_company( $campaign_id, $subscriber, $hash, $shortcode, $code_string ) {
        $company = '';

        switch ( $shortcode ) {
            case 'address':
                $seperator = ',';

                if ( preg_match( '/seperator=\\\"(.*?)\\\"/' , $code_string, $match ) ) {
                    $seperator = htmlspecialchars_decode( $match[1] );
                }

                $company = implode( "{$seperator} " , $this->company_details['address'] );
                break;

            default:
                $company = $this->company_details[ $shortcode ];
                break;
        }

        return $company;
    }

    /**
     * Link Shortcodes
     *
     * @param int    $campaign_id
     * @param object $subscriber
     * @param string $hash
     * @param string $shortcode
     * @param string $code_string
     *
     * @return string
     */
    private function sc_links( $campaign_id, $subscriber, $hash, $shortcode, $code_string ) {
        $link = '';
        $text = '';

        if ( preg_match( '/text=\\\"(.*?)\\\"/' , $code_string, $match ) ) {
            $text = $match[1];
        }

        switch ( $shortcode ) {
            case 'unsubscribe':

                if ( empty( $text ) ) {
                    $text = __( 'Unsubscribe', 'erp-email-campaign' );
                }

                if ( !empty( $subscriber->id ) ) {
                    $link = '<a href="' . ecamp_unsubscribe_link( $hash ) . '">' . $text . '</a>';
                } else {
                    $link = '<a href="' . site_url( "#unsubscribe" ) . '">' . $text . '</a>';
                }

                break;

            case 'edit_subscription':

                if ( empty( $text ) ) {
                    $text = __( 'Edit your subscription', 'erp-email-campaign' );
                }

                if ( !empty( $subscriber->id ) ) {
                    $link = '<a href="' . ecamp_edit_subscription_link( $hash ) . '">' . $text . '</a>';
                } else {
                    $link = '<a href="' . site_url( "#edit-subscription" ) . '">' . $text . '</a>';
                }

                break;

            case 'archive':

                if ( empty( $text ) ) {
                    $text = __( 'View this email in your browser', 'erp-email-campaign' );
                }

                $link = '<a href="' . site_url( "?erp-email-campaign=1&view-email-in-browser=1&campaign=$campaign_id" ) . '">' . $text . '</a>';

                break;
        }

        return $link;
    }
}

/**
 * Class instance
 *
 * @return object
 */
function templates() {
    return Templates::instance();
}
