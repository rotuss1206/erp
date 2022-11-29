<?php
namespace WeDevs\ERP\ERP_Document;

/**
 * Handle the form submissions
 *
 * Although our most of the forms uses ajax and popup, some
 * are needed to submit via regular form submits. This class
 * Handles those form submission in this module
 *
 * @package WP ERP
 * @subpackage HRM
 */
class Form_Handler {

    /**
     * Hook 'em all
     */
    public function __construct() {
        add_action( 'load-admin_page_jobseeker_list', array( $this, 'status_bulk_action' ) );
        //add_action( 'admin_init', array( $this, 'status_bulk_action' ) );
    }

    /**
     * Check is current page actions
     *
     * @since 0.1
     *
     * @param  integer $page_id
     * @param  integer $bulk_action
     *
     * @return boolean
     */
    public function verify_current_page_screen( $page_id, $bulk_action ) {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return false;
        }

        if ( $_GET['page'] != $page_id ) {
            return false;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $bulk_action ) ) {
            return false;
        }

        return true;
    }

    /**
     * Handle designation bulk action
     *
     * @since 0.1
     *
     * @return void [redirection]
     */
    public function status_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'jobseeker_list', 'bulk-jobseekers' ) ) {
            return;
        }

        $jobseeker_table = new \WeDevs\ERP\ERP_Recruitment\Jobseeker_List_Table();
        $action = $jobseeker_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'action', 'action2' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'filter_status' :
                    wp_redirect( $redirect );
                    exit();
            }
        }
    }

}

new Form_Handler();