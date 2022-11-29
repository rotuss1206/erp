<?php
namespace WeDevs\ERP\ERP_Document;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler {

    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for HRM
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {
        // delete attachment file in file system
        $this->action( 'wp_ajax_erp_hr_attachment_delete_file', 'delete_hr_attachment' );
        // get first level dir info by employee id
        $this->action( 'wp_ajax_wp-erp-rec-get-dir-info', 'get_dirInfo' );
        // get first level file info by employee id
        $this->action( 'wp_ajax_wp-erp-rec-get-file-info', 'get_fileInfo' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_wp-erp-rec-createDir', 'createDir' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_wp-erp-rec-deleteDirFile', 'deleteDirFile' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_file_dir_ajax_upload', 'uploadEmployeeFile' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_wp-erp-rec-renameDirFile', 'renameDirFile' );
        // load parent dir
        $this->action( 'wp_ajax_wp-erp-doc-loadParentNodes', 'loadDirTree' );
        // move function
        $this->action( 'wp_ajax_wp-erp-doc-moveNow', 'moveNow' );
        // search
        $this->action( 'wp_ajax_wp-erp-rec-search-dir-info', 'searchDirNow' );
        $this->action( 'wp_ajax_wp-erp-rec-search-file-info', 'searchFileNow' );
    }

    /**
    * search dir info by search key
    *
    * @return array
    */
    public function searchDirNow() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid             = isset( $_GET['employee_id'] ) || $_GET['employee_id'] != '' ? $_GET['employee_id'] : 0;
            $search_key      = ( isset( $_GET['skey'] ) ? $_GET['skey'] : '' );
            $current_user_id = get_current_user_id();

            $query = "SELECT users.ID, users.user_nicename, dir.dir_id, dir.eid, dir.is_dir, dir.dir_name, dir.created_at, DATE_FORMAT(dir.updated_at, '%Y-%m-%d %h:%i %p') as updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                LEFT JOIN {$wpdb->prefix}users as users
                ON dir.created_by=users.ID
                WHERE dir.eid='" . $eid . "' AND dir.dir_name LIKE'%" . $search_key . "%' AND dir.is_dir=1";

            $udata = $wpdb->get_results( $query, ARRAY_A );

            $user_file_data = [ ];
            foreach ( $udata as $ufd ) {
                $user_file_data[] = array(
                    //'dir_name'      => $ufd['dir_name'],
                    'dir_file_name' => $ufd['dir_name'],
                    'dir_id'        => $ufd['dir_id'],
                    'eid'           => $ufd['eid'],
                    'is_dir'        => $ufd['is_dir'],
                    'updated_at'    => date( 'Y-m-d g:i A', strtotime( $ufd['updated_at'] ) ),
                    'user_link'     => get_edit_profile_url( $ufd['ID'] ),
                    'user_id'       => $ufd['ID'],
                    'user_nicename' => $ufd['user_nicename']
                );
            }
            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * search file info by search key
    *
    * @return array
    */
    public function searchFileNow() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid             = isset( $_GET['employee_id'] ) || $_GET['employee_id'] != '' ? $_GET['employee_id'] : 0;
            $search_key      = ( isset( $_GET['skey'] ) ? $_GET['skey'] : '' );
            $current_user_id = get_current_user_id();

            $query = "SELECT users.ID, users.user_nicename, file.dir_id, file.eid, file.is_dir, file.dir_name, file.attachment_id, file.updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as file
                LEFT JOIN {$wpdb->prefix}users as users
                ON file.created_by=users.ID
                WHERE file.eid='" . $eid . "' AND file.dir_name LIKE'%" . $search_key . "%' AND file.is_dir=0";
            $udata = $wpdb->get_results( $query, ARRAY_A );

            $user_file_data = [ ];
            foreach ( $udata as $ufd ) {
                $user_file_data[] = array(
                    'user_nicename'   => $ufd['user_nicename'],
                    'user_id'         => $ufd['ID'],
                    'user_link'       => get_edit_profile_url( $ufd['ID'] ),
                    'dir_id'          => $ufd['dir_id'],
                    'eid'             => $ufd['eid'],
                    'is_dir'          => $ufd['is_dir'],
                    'attactment_url'  => wp_get_attachment_url( $ufd['attachment_id'] ),
                    'attachment_type' => get_post_mime_type( $ufd['attachment_id'] ),
                    //'file_name'       => $ufd['dir_name'],
                    'dir_file_name'   => $ufd['dir_name'],
                    'file_size'       => number_format( filesize( get_attached_file( $ufd['attachment_id'] ) ) / 1024, 2 ) . ' KB',
                    'updated_at'      => date( 'Y-m-d g:i A', strtotime( $ufd['updated_at'] ) )
                );
            }
            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * remove attachment
     *
     * @return void
     */
    public function delete_hr_attachment() {
        $this->verify_nonce( 'doc_form_builder_nonce' );

        if ( isset( $_POST['file_id'] ) ) {
            $attachmentid = $_POST['file_id'];
            if ( false === wp_delete_attachment( $attachmentid, false ) ) {
                $this->send_error( __( 'The file could not deleted', 'erp-document' ) );
            } else {
                $this->send_success( __( 'File has been deleted', 'erp-document' ) );
            }
        } else {
            $this->send_error( __( 'File could not deleted', 'erp-document' ) );
        }
    }

    /**
    * get dir file info by employee id
    *
    * @return array
    */
    public function get_dirInfo() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid             = isset( $_GET['employee_id'] ) || $_GET['employee_id'] != '' ? $_GET['employee_id'] : 0;
            $parent_id       = ( isset( $_GET['dir_id'] ) ? $_GET['dir_id'] : 0 );
            $current_user_id = get_current_user_id();

            $query = "SELECT users.ID, users.user_nicename, dir.dir_id, dir.eid, dir.is_dir, dir.dir_name, dir.created_at, DATE_FORMAT(dir.updated_at, '%Y-%m-%d %h:%i %p') as updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                LEFT JOIN {$wpdb->prefix}users as users
                ON dir.created_by=users.ID
                WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.is_dir=1";

            $udata = $wpdb->get_results( $query, ARRAY_A );

            $user_file_data = [ ];
            foreach ( $udata as $ufd ) {
                $user_file_data[] = array(
                    //'dir_name'      => $ufd['dir_name'],
                    'dir_file_name' => $ufd['dir_name'],
                    'dir_id'        => $ufd['dir_id'],
                    'eid'           => $ufd['eid'],
                    'is_dir'        => $ufd['is_dir'],
                    'updated_at'    => date( 'Y-m-d g:i A', strtotime( $ufd['updated_at'] ) ),
                    'user_link'     => get_edit_profile_url( $ufd['ID'] ),
                    'user_id'       => $ufd['ID'],
                    'user_nicename' => $ufd['user_nicename']
                );
            }
            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * get dir file info by employee id
    *
    * @return array
    */
    public function get_fileInfo() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;

            $eid             = isset( $_GET['employee_id'] ) || $_GET['employee_id'] != '' ? $_GET['employee_id'] : 0;
            $parent_id       = ( isset( $_GET['dir_id'] ) ? $_GET['dir_id'] : 0 );
            $current_user_id = get_current_user_id();

            $query = "SELECT users.ID, users.user_nicename, file.dir_id, file.eid, file.is_dir, file.dir_name, file.attachment_id, file.updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as file
                LEFT JOIN {$wpdb->prefix}users as users
                ON file.created_by=users.ID
                WHERE file.eid='" . $eid . "' AND file.parent_id='" . $parent_id . "' AND file.is_dir=0";
            $udata = $wpdb->get_results( $query, ARRAY_A );

            $user_file_data = [ ];
            foreach ( $udata as $ufd ) {
                $user_file_data[] = array(
                    'user_nicename'   => $ufd['user_nicename'],
                    'user_id'         => $ufd['ID'],
                    'user_link'       => get_edit_profile_url( $ufd['ID'] ),
                    'dir_id'          => $ufd['dir_id'],
                    'eid'             => $ufd['eid'],
                    'is_dir'          => $ufd['is_dir'],
                    'attactment_url'  => wp_get_attachment_url( $ufd['attachment_id'] ),
                    'attachment_type' => get_post_mime_type( $ufd['attachment_id'] ),
                    //'file_name'       => $ufd['dir_name'],
                    'dir_file_name'   => $ufd['dir_name'],
                    'file_size'       => number_format( filesize( get_attached_file( $ufd['attachment_id'] ) ) / 1024, 2 ) . ' KB',
                    'updated_at'      => date( 'Y-m-d g:i A', strtotime( $ufd['updated_at'] ) )
                );
            }
            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * delete dir or file
    *
    * @return bool
    */
    public function deleteDirFile() {
        if ( isset( $_POST['employee_id'] ) ) {
            global $wpdb;
            $eid                  = !isset( $_POST['employee_id'] ) || empty( $_POST['employee_id'] ) ? 0 : $_POST['employee_id'];
            $parent_id            = isset( $_POST['parent_id'] ) || $_POST['parent_id'] != '' ? $_POST['parent_id'] : 0;
            $selected_dir_file_id = json_decode( stripslashes( $_POST['selected_dir_file_id'] ) );

            // delete attachment file first
            foreach ( $selected_dir_file_id as $sdfid ) {
                $attachment_id = $wpdb->get_var( "SELECT attachment_id FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id={$sdfid} AND is_dir=0" );
                wp_delete_attachment( $attachment_id );
            }

            foreach ( $selected_dir_file_id as $fd_id ) {
                do_action( 'erp_doc_dir_or_file_delete', $fd_id, $eid );

                // find the deepest nodes if have any
                $child_kit = [ ];
                get_child_dir_ids( $fd_id, $child_kit );
                $wpdb->delete(
                    $wpdb->prefix . 'erp_employee_dir_file_relationship',
                    array( 'dir_id' => $fd_id, 'eid' => $eid ),
                    array( '%d', '%d' )
                );
                $wpdb->delete(
                    $wpdb->prefix . 'erp_employee_dir_file_relationship',
                    array( 'parent_id' => $fd_id, 'eid' => $eid ),
                    array( '%d', '%d' )
                );
                //delete all child nodes now
                if ( count($child_kit) > 0 ) {
                    $dir_id_to_be_deleted = implode( ",", $child_kit );
                    $query                = "DELETE FROM {$wpdb->prefix}erp_employee_dir_file_relationship WHERE eid={$eid} AND parent_id IN($dir_id_to_be_deleted)";
                    $wpdb->query($query);
                }
            }

            if ( $parent_id != 0 ) {
                update_parent_folder_timestamp( $parent_id, $eid );
            }

            $this->send_success( __( 'Deleted successfully', 'erp-document' ) );
        } else {
            $this->send_success( __( 'Could not deleted', 'erp-document' ) );
        }
    }

    /**
    * create dir
    *
    * @return json
    */
    public function createDir() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid              = isset( $_GET['employee_id'] ) || $_GET['employee_id'] != '' ? $_GET['employee_id'] : 0;
            $parent_id        = $_GET['parent_id'];
            $dir_name         = $_GET['dirName'];
            $dir_exist_result = $this->checkDuplicateDir( $eid, $parent_id, $dir_name );

            if ( $dir_exist_result == true ) {
                $this->send_error( __( 'Folder name already exits', 'erp-document' ) );
            } else {
                $current_user_id = get_current_user_id();
                // get last dir id by employee id
                $query = "SELECT dir.dir_id
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                WHERE dir.eid='" . $eid . "'
                ORDER BY dir.dir_id DESC LIMIT 1";

                $last_dir_id = $wpdb->get_var( $query );

                //insert dir name
                $data = array(
                    'eid'        => $eid,
                    'dir_id'     => $last_dir_id + 1,
                    'dir_name'   => $dir_name,
                    'parent_id'  => $parent_id,
                    'is_dir'     => 1,
                    'created_by' => get_current_user_id(),
                    'created_at' => date( 'Y-m-d H:i:s', time() )
                );

                $format = array(
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_employee_dir_file_relationship', $data, $format );
                if ( $parent_id != 0 ) { // if parent is not 0 then update the parent update timestamp
                    update_parent_folder_timestamp( $parent_id, $eid );
                }
                do_action( 'erp_doc_dir_or_file_new', $data );
                $this->send_success( __( 'Folder created successfully', 'erp-document' ) );
            }

        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * rename dir or file
    *
    * @return json
    */
    public function renameDirFile() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;

            $eid                   = !isset( $_GET['employee_id'] ) || empty( $_GET['employee_id'] ) ? 0 : $_GET['employee_id'];
            $parent_id             = $_GET['parent_id'];
            $target_id             = $_GET['target_dir_id'];
            $dir_name              = $_GET['dirName'];
            $type                  = $_GET['dir_file_type'];
            $dir_exist_result      = false;
            $filename_exist_result = false;

            if ( $type == 'folder' ) {
                $dir_exist_result = $this->checkDuplicateDir( $eid, $parent_id, $dir_name );
            } else {
                $filename_exist_result = $this->checkDuplicateFile( $eid, $parent_id, $dir_name );
            }

            if ( $dir_exist_result == true || $filename_exist_result == true ) {
                $this->send_error( __( 'Given name already exits!', 'erp-document' ) );
            } else {
                do_action( 'erp_doc_dir_or_file_rename', $type, $dir_name, $target_id, $eid );
                //update dir or file name
                $data         = array(
                    'dir_name'   => $dir_name,
                    'created_by' => get_current_user_id()
                );
                $where        = array(
                    'eid'    => $eid,
                    'dir_id' => $target_id
                );
                $data_format  = array(
                    '%s',
                    '%d'
                );
                $where_format = array(
                    '%d'
                );
                $wpdb->update( $wpdb->prefix . 'erp_employee_dir_file_relationship', $data, $where, $data_format, $where_format );
                if ( $parent_id != 0 ) {
                    update_parent_folder_timestamp( $parent_id, $eid );
                }

                $this->send_success( __( 'Renamed successfully', 'erp-document' ) );
            }

        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * check duplicate dir
    *
    * @return bool
    */
    public function checkDuplicateDir( $eid, $parent_id, $dir_name ) {
        global $wpdb;
        $current_user_id = get_current_user_id();

        $query = "SELECT *
            FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
            WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.dir_name='" . $dir_name . "' AND dir.is_dir=1";

        if ( count( $wpdb->get_results( $query, ARRAY_A ) ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * check duplicate file name
    *
    * @return bool
    */
    public function checkDuplicatefile( $eid, $parent_id, $dir_name ) {
        global $wpdb;
        $current_user_id = get_current_user_id();

        $query = "SELECT *
            FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
            WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.dir_name='" . $dir_name . "' AND dir.is_dir=0";

        if ( count( $wpdb->get_results( $query, ARRAY_A ) ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * file upload for employee
    *
    * @return void
    */
    public function uploadEmployeeFile() {
        global $wpdb;
        $this->verify_nonce( 'file_upload_nonce' );

        $object_id       = isset( $_REQUEST['object_id'] ) ? intval( $_REQUEST['object_id'] ) : 0;
        $parent_id       = isset( $_REQUEST['parent_id'] ) ? $_REQUEST['parent_id'] : 0;
        $employee_id     = isset( $_REQUEST['employee_id'] ) ? $_REQUEST['employee_id'] : 0;
        $response        = $this->upload_file( $object_id );
        $current_user_id = get_current_user_id();

        if ( $response['success'] ) {
            $file = $this->get_file( $response['file_id'] );

            $delete   = sprintf( '<a href="#" data-id="%d" class="doc-delete-file button">%s</a>', $file['id'], __( 'Delete File', 'wp-erp-rec' ) );
            $hidden   = sprintf( '<input type="hidden" name="doc_attachment[]" value="%d" />', $file['id'] );
            $file_url = sprintf( '<input class="dir_file_chkbox dir_chkbox" type="checkbox" value="0" v-model="dir_file_checkboxx"><a class="filelink" href="%1$s" target="_blank"><img src="%2$s" alt="%3$s" /></a>', $file['url'], $file['thumb'], esc_attr( $file['name'] ) );

            // get last dir id by employee id
            $query = "SELECT dir.dir_id
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                WHERE dir.eid='" . $employee_id . "'
                ORDER BY dir.dir_id DESC LIMIT 1";

            $last_dir_id = $wpdb->get_var( $query );
            /*********end of last dir id******/
            // insert attach id to wp_erp_employee_dir_file_relationship
            //insert applicant attach cv id
            $data = array(
                'eid'           => $employee_id,
                'dir_id'        => $last_dir_id + 1,
                'dir_name'      => $file['name'],
                'attachment_id' => $file['id'],
                'parent_id'     => $parent_id,
                'is_dir'        => 0,
                'created_by'    => get_current_user_id(),
                'created_at'    => current_time( 'Y-m-d H:i:s' )
            );

            $format = array(
                '%d',
                '%d',
                '%s',
                '%d',
                '%d',
                '%d',
                '%d',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_employee_dir_file_relationship', $data, $format );

            if ( $parent_id != 0 ) {
                update_parent_folder_timestamp( $parent_id, $employee_id );
            }
            do_action( 'erp_doc_dir_or_file_new', $data );

            //$html = '<div class="doc-uploaded-item">' . $file_url . ' ' . $delete . $hidden . '</div>';
            $html = '<li><div class="filename-col">' . $file_url . ' ' . $hidden . '</div>
                        <div class="modified-time">' . $file['upload_time'] . '</div>
                     </li>';
            echo json_encode( array(
                'success' => true,
                'content' => $html
            ) );

            exit;
        }

        echo json_encode( array(
            'success' => false,
            'error'   => $response['error']
        ) );

        exit;

    }

    /**
     * Upload a file and insert as attachment
     *
     * @param int $post_id
     *
     * @return int|bool
     */
    public function upload_file( $post_id = 0 ) {
        global $wpdb;
        if ( $_FILES['doc_attachment']['error'] > 0 ) {
            return false;
        }

        $upload = array(
            'name'     => $_FILES['doc_attachment']['name'],
            'type'     => $_FILES['doc_attachment']['type'],
            'tmp_name' => $_FILES['doc_attachment']['tmp_name'],
            'error'    => $_FILES['doc_attachment']['error'],
            'size'     => $_FILES['doc_attachment']['size']
        );

        $uploaded_file = wp_handle_upload( $upload, array( 'test_form' => false ) );

        if ( isset( $uploaded_file['file'] ) ) {
            $file_loc  = $uploaded_file['file'];
            $file_name = basename( $_FILES['doc_attachment']['name'] );
            $file_type = wp_check_filetype( $file_name );

            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'erp_hr_rec'
            );

            $attach_id   = wp_insert_attachment( $attachment, $file_loc );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            /*extra work for updating post status inherit to wp-erp-rec*/
            $wpdb->update(
                $wpdb->prefix . 'posts',
                array( 'post_status' => 'erp_hr_rec' ),
                array( 'ID' => $attach_id ),
                array( '%s' ),
                array( '%d' )
            );

            do_action( 'doc_after_upload_file', $attach_id, $attach_data, $post_id );
            return array( 'success' => true, 'file_id' => $attach_id );
        }

        return array( 'success' => false, 'error' => $uploaded_file['error'] );
    }

    /**
     * Get an attachment file
     *
     * @param int $attachment_id
     *
     * @return array
     */
    public function get_file( $attachment_id ) {
        $file = get_post( $attachment_id );

        if ( $file ) {
            $response = array(
                'id'          => $attachment_id,
                //'name'        => get_the_title($attachment_id),
                'name'        => basename( get_attached_file( $attachment_id ) ),
                'url'         => wp_get_attachment_url( $attachment_id ),
                'upload_time' => $file->post_date
            );

            if ( wp_attachment_is_image( $attachment_id ) ) {

                $thumb             = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
                $response['thumb'] = $thumb[0];
                $response['type']  = 'image';
            } else {
                $response['thumb'] = wp_mime_type_icon( $file->post_mime_type );
                $response['type']  = 'file';
            }

            return $response;
        }

        return false;
    }

    /**
     * Get parent directories to make tree template
     *
     * @since 0.1
     *
     * @return void
     */
    public function loadDirTree() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid = $_GET['employee_id'];

            $q  = "SELECT dir.dir_id as id, dir.dir_name as text, dir.parent_id as parent_id
                    FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                    WHERE dir.eid='" . $eid . "' AND dir.is_dir=1";
            $ad = array_values( buildtree( $wpdb->get_results( $q, ARRAY_A ) ) );
            // insert the root as parent id 0 for the tree
            $withroot = array(
                'id'        => 0,
                'text'      => 'Home',
                'parent_id' => 0,
                'children'  => $ad );

            echo json_encode( $withroot );
            exit;
            //echo wp_json_encode($data, JSON_PRETTY_PRINT);
            //$this->send_success($data, JSON_PRETTY_PRINT);
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * Dir file Move function
     *
     * @since 0.1
     *
     * @return void
     */
    public function moveNow() {
        if ( isset( $_POST['employee_id'] ) ) {
            global $wpdb;
            $eid       = $_POST['employee_id'];
            $parent_id = $_POST['parent_id'];
            if ( empty( $eid ) || $eid == '' ) {
                $eid = 0;
            }
            $new_parent_id   = is_numeric( $_POST['new_parent_id'] ) ? $_POST['new_parent_id'] : 0;
            $selectedDirFile = json_decode( stripslashes( $_POST['selectedDirFile'] ) );
            // check selected dir is new parent, if true then send error else do move operation
            if ( in_array( $new_parent_id, $selectedDirFile ) ) {
                $this->send_error( 'You cannot move a folder into itself' );
            }

            $where_dir_id = implode( ',', $selectedDirFile );
            do_action( 'erp_doc_dir_or_file_move', $where_dir_id, $new_parent_id, $parent_id, $eid );
            $wpdb->query(
                "UPDATE {$wpdb->prefix}erp_employee_dir_file_relationship
                SET parent_id={$new_parent_id}
                WHERE eid={$eid} AND dir_id IN ($where_dir_id)"
            );

            if ( $parent_id != 0 ) {
                update_parent_folder_timestamp( $parent_id, $eid );
            }

            $this->send_success( __( 'Moved successfully', 'erp-document' ) );
        } else {
            $this->send_success( [ ] );
        }
    }
}