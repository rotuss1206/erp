<?php
namespace WeDevs\ERP\ERP_Document;

use \WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Log handler
 *
 * @package WP-ERP-DOC
 */
class Doc_Log {

    use Hooker;

    /**
     * Load autometically when class instantiate
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {

        // Directory and file
        $this->action('erp_doc_dir_or_file_new', 'create_directory');
        $this->action('erp_doc_dir_or_file_delete', 'delete_directory', 10, 2);
        $this->action('erp_doc_dir_or_file_rename', 'rename_directory', 10, 4);
        $this->action('erp_doc_dir_or_file_move', 'move_directory', 10, 4);
    }

    /**
     * Get different array from two array
     *
     * @since 0.1
     *
     * @param  array $new_data
     * @param  array $old_data
     *
     * @return array
     */
    public function get_array_diff( $new_data, $old_data, $is_seriazie = false ) {

        $old_value = $new_value = [];
        $changes_key = array_keys(array_diff_assoc($new_data, $old_data));

        foreach ($changes_key as $key => $change_field_key) {
            $old_value[$change_field_key] = $old_data[$change_field_key];
            $new_value[$change_field_key] = $new_data[$change_field_key];
        }

        if ( !$is_seriazie ) {
            return [
                'new_val' => $new_value ? base64_encode(maybe_serialize($new_value)) : '',
                'old_val' => $old_value ? base64_encode(maybe_serialize($old_value)) : ''
            ];
        } else {
            return [
                'new_val' => $new_value,
                'old_val' => $old_value
            ];
        }
    }

    /**
     * Add log when new directory created
     *
     * @since 0.1
     *
     * @param  array $fields
     *
     * @return void
     */
    public function create_directory( $fields ) {
        global $wpdb;

        $file_or_folder = $fields['is_dir'] == 1 ? 'folder' : 'file';
        $company_or_employee = $fields['eid'] > 0 ? 'employee' : 'company';
        if ( $file_or_folder == 'folder' ) {
            if ( $company_or_employee == 'company' ) {
                $log_message = sprintf(__('<strong>%s</strong> %s has been created (for company)', 'wp-erp-doc'), $fields['dir_name'], $file_or_folder);
            } else {
                $log_message = sprintf(__('<strong>%s</strong> %s has been created (for employee)', 'wp-erp-doc'), $fields['dir_name'], $file_or_folder);
            }
        } else {
            if ( $company_or_employee == 'company' ) {
                $log_message = sprintf(__('<strong>%s</strong> %s has been uploaded (for company)', 'wp-erp-doc'), $fields['dir_name'], $file_or_folder);
            } else {
                $log_message = sprintf(__('<strong>%s</strong> %s has been uploaded (for employee)', 'wp-erp-doc'), $fields['dir_name'], $file_or_folder);
            }
        }

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'document',
            'changetype'    => 'add',
            'message'       => $log_message,
            'created_by'    => get_current_user_id()
        ];
        $log_data_format = [
            '%s',
            '%s',
            '%s',
            '%s',
            '%d'
        ];
        $wpdb->insert($wpdb->prefix . 'erp_audit_log', $log_data, $log_data_format);
    }

    /**
     * Add log when directory or file deleted
     *
     * @since 0.1
     *
     * @param  integer $dir_id
     *
     * @return void
     */
    public function delete_directory( $dir_id, $employee_id ) {
        global $wpdb;
        if ( !$dir_id ) {
            return;
        }

        $dir_file_name = $wpdb->get_var("SELECT dir_name FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id={$dir_id} AND eid={$employee_id}");

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'document',
            'message'       => ($employee_id > 0) ? sprintf(__('<strong>%s</strong> has been deleted (employee)', 'wp-erp-doc'), $dir_file_name) : sprintf('<strong>%s</strong> has been deleted (company)', $dir_file_name),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete'
        ];
        $log_data_format = [
            '%s',
            '%s',
            '%s',
            '%d',
            '%s'
        ];
        $wpdb->insert($wpdb->prefix . 'erp_audit_log', $log_data, $log_data_format);

    }

    /**
     * Add log when directory or file renamed
     *
     * @since 0.1
     *
     * @param  integer $type
     * @param  integer $dir_name
     * @param  integer $dir_id
     * @param  integer $employee_id
     *
     * @return void
     */
    public function rename_directory( $type, $dir_name, $dir_id, $employee_id ) {
        global $wpdb;
        if ( !$dir_id ) {
            return;
        }

        $old_file_name = $wpdb->get_var("SELECT dir_name FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id={$dir_id} AND eid={$employee_id}");

        if ( $employee_id == 0 ) {
            $log_message = sprintf(__('<strong>%s</strong> %s has been renamed to %s (company)', 'wp-erp-doc'), $old_file_name, $type, $dir_name);
        } else {
            $log_message = sprintf(__('<strong>%s</strong> %s has been renamed to %s (emplyee)', 'wp-erp-doc'), $old_file_name, $type, $dir_name);
        }

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'document',
            'old_value'     => $old_file_name,
            'new_value'     => $dir_name,
            'message'       => $log_message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'edit'
        ];
        $log_data_format = [
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d'
        ];
        $wpdb->insert($wpdb->prefix . 'erp_audit_log', $log_data, $log_data_format);

    }

    /**
     * Add log when directory or file moved / cut
     *
     * @since 0.1
     *
     * @param  integer $selected dir file id
     * @param  integer $new parent id
     * @param  integer $parent id as old parent id
     * @param  integer $employee_id
     *
     * @return void
     */
    public function move_directory( $selected_dir_file_id, $new_parent_id, $parent_id, $employee_id ) {
        global $wpdb;

        $old_parent_name = $wpdb->get_var("SELECT dir_name FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id={$parent_id} AND eid={$employee_id}");
        $new_parent_name = $wpdb->get_var("SELECT dir_name FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id={$new_parent_id} AND eid={$employee_id}");
        $moveable_file_folder_name = $wpdb->get_results("SELECT dir_name FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id IN ({$selected_dir_file_id}) AND eid={$employee_id}", ARRAY_A);

        $data_in_commas = '';
        if ( count($moveable_file_folder_name) > 1 ) {
            foreach ( $moveable_file_folder_name as $mffn ) {
                $data_in_commas = $data_in_commas . $mffn['dir_name'] . ',';
            }
        } else {
            $data_in_commas = $moveable_file_folder_name[0]['dir_name'];
        }

        if ( $employee_id == 0 ) {
            $log_message = sprintf(__('<strong>%s</strong> has been moved to %s (company)', 'wp-erp-doc'), $data_in_commas, ( $new_parent_name == '' ? 'root' : $new_parent_name ) );
        } else {
            $log_message = sprintf(__('<strong>%s</strong> has been moved to %s (emplyee)', 'wp-erp-doc'), $data_in_commas, ( $new_parent_name == '' ? 'root' : $new_parent_name ) );
        }

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'document',
            'old_value'     => sprintf( __('previous parent name was ', 'wp-erp-doc') . '%s', ( $old_parent_name == '' ? 'root' : $old_parent_name ) ),
            'new_value'     => sprintf( __('new parent name is ', 'wp-erp-doc') .'%s', ( $new_parent_name == '' ? 'root' : $new_parent_name ) ),
            'message'       => $log_message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'edit'
        ];
        $log_data_format = [
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d'
        ];
        $wpdb->insert($wpdb->prefix . 'erp_audit_log', $log_data, $log_data_format);

    }

}