<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Additional Capabilities
 *
 * @since 1.1.0
 */
class Capabilities {

    use Hooker;

    /**
     * Class constructor
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'user_has_cap', 'add_managerial_capability', 10, 4 );
    }

    /**
     * Add campaign managerial capability to certain user roles
     *
     * @since 1.1.0
     *
     * @param array $allcaps
     * @param string $caps
     * @param array $args
     * @param object $wp_user
     *
     * @return array
     */
    public function add_managerial_capability( $allcaps, $caps, $args, $wp_user ) {
        $settings = get_option( 'erp_settings_erp-crm_email_campaign', [] );
        $managerial_roles = ! empty( $settings['managerial_roles'] ) ? $settings['managerial_roles'] : ['administrator', 'erp_crm_manager'];

        foreach ( $wp_user->roles as $role ) {
            if ( in_array( $role, $managerial_roles ) ) {
                $allcaps['manage_erp_email_campaign'] = 1;
                break;
            }
        }

        return $allcaps;
    }
}

new Capabilities();
