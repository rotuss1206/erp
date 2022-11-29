<?php
/**
 * Get all file
 *
 * @param $args array
 *
 * @return array
 */
function erp_rec_get_all_file( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'file-all';
    $items     = wp_cache_get( $cache_key, 'wp-erp-rec' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'erp_employee_files ORDER BY ' . $args['orderby'] . ' ' . $args['order'] . ' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

        wp_cache_set( $cache_key, $items, 'wp-erp-rec' );
    }

    return $items;
}

/**
 * Fetch all file from database
 *
 * @return array
 */
function erp_rec_get_file_count() {
    global $wpdb;

    return (int)$wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'erp_employee_files' );
}

/**
 * Fetch a single file from database
 *
 * @param int $id
 *
 * @return array
 */
function erp_rec_get_file( $id = 0 ) {
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'erp_employee_files WHERE id = %d', $id ) );
}

/**
 * file upload field helper
 *
 * Generates markup for ajax file upload list and prints attached files.
 *
 * @since 0.1
 * @param int   $id comment ID. used for unique edit comment form pick file ID
 * @param array $files attached files
 */
function erp_rec_upload_field( $id, $files = array() ) {
    $id = $id ? '-' . $id : '';
    ?>
<div id="doc-upload-container<?php echo $id; ?>">
    <div class="doc-upload-filelist">
    <?php if ( $files ) { ?><?php foreach ( $files as $file ) {
        $delete   = sprintf( '<a href="#" data-id="%d" class="doc-delete-file button">%s</a>', $file['id'], __( 'Delete File', 'wp-erp-rec' ) );
        $hidden   = sprintf( '<input type="hidden" name="doc_attachment[]" value="%d" />', $file['id'] );
        $file_url = sprintf( '<a href="%1$s" target="_blank"><img src="%2$s" alt="%3$s" /></a>', $file['url'], $file['thumb'], esc_attr( $file['name'] ) );

        //$html = '<div class="doc-uploaded-item">' . $file_url . ' ' . $delete . $hidden . '</div>';
        $html = '<li>
                                <div class="filename-col">
                                    <div class="doc-uploaded-item">' . $file_url . ' ' . $hidden . '</div>
                                </div>
                                <div class="modified">time goes here</div>
                             </li>';
        echo $html;
    } ?><?php } ?>
    </div><?php printf( __( '<a class="fileUpload button button-primary" id="doc-upload-pickfiles%s" href="#"><span class="fa fa-lg fa-upload"></span>Upload</a>', 'wp-erp-rec' ), $id ); ?>
    </div><?php
}

/*
 * file uploader
 * para file array
 * return array
 */
function handle_file_upload( $upload_data ) {
    global $wpdb;
    $uploaded_file = wp_handle_upload( $upload_data, array( 'test_form' => false ) );
    // If the wp_handle_upload call returned a local path for the image
    if ( isset( $uploaded_file['file'] ) ) {
        $file_loc    = $uploaded_file['file'];
        $file_name   = basename( $upload_data['name'] );
        $file_type   = wp_check_filetype( $file_name );
        $attachment  = array(
            'post_mime_type' => $file_type['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
            'post_content'   => '',
            'post_status'    => 'erp_hr_rec'
        );
        $attach_id   = wp_insert_attachment( $attachment, $file_loc );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        $wpdb->update(
            $wpdb->prefix . 'posts',
            array( 'post_status' => 'erp_hr_rec' ),
            array( 'ID' => $attach_id ),
            array( '%s' ),
            array( '%d' )
        );
        return array( 'success' => true, 'attach_id' => $attach_id );
    }
    return array( 'success' => false, 'error' => $uploaded_file['error'] );
}

function buildtree( $src_arr, $parent_id = 0, $tree = array() ) {
    foreach ( $src_arr as $idx => $row ) {
        if ( $row['parent_id'] == $parent_id ) {
            foreach ( $row as $k => $v )
                $tree[$row['id']][$k] = $v;
            unset( $src_arr[$idx] );
            $tree[$row['id']]['children'] = array_values( buildtree( $src_arr, $row['id'] ) );
        }
    }
    ksort( $tree );
    return $tree;
}

/*
 * update parent folder timestamp when you are updating something inside a folder
 * para parent_id, employee id
 * return void
 */
function update_parent_folder_timestamp( $parent_id, $employee_id ) {
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'erp_employee_dir_file_relationship',
        array( 'attachment_id' => '1' ),
        array( 'dir_id' => $parent_id, 'eid' => $employee_id ),
        array( '%s' ),
        array( '%d', '%d' )
    );
    $wpdb->update(
        $wpdb->prefix . 'erp_employee_dir_file_relationship',
        array( 'attachment_id' => '0' ),
        array( 'dir_id' => $parent_id, 'eid' => $employee_id ),
        array( '%s' ),
        array( '%d', '%d' )
    );
}

/*
 * get all child nodes
 *
 * return all child to delete
 */
function get_child_dir_ids( $parent_id, &$child_kit ) {
    global $wpdb;

    $query  = "SELECT dir_id FROM {$wpdb->prefix}erp_employee_dir_file_relationship WHERE parent_id=" . $parent_id;
    $dir_id = $wpdb->get_var( $query );
    if ( is_null( $dir_id ) ) {
        return $child_kit;
    } else {
        $child_kit[] = $dir_id;
        get_child_dir_ids( $dir_id, $child_kit );
    }
}

?>