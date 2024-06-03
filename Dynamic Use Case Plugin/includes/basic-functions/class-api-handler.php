<?php

namespace MyCustomPlugin;

use WP_REST_Request;
use WP_Error;
use WP_REST_Response;

class API_Handler {
    
    /**
     * Register REST API routes for the plugin.
     */
    public function register_routes() {
        register_rest_route('my-custom-plugin/v1', '/upload-csv', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_upload_csv'),
            'permission_callback' => array($this, 'permissions_check'),
        ));

        register_rest_route('my-custom-plugin/v1', '/save-mapping', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_save_mapping'),
            'permission_callback' => array($this, 'permissions_check'),
        ));

        register_rest_route('my-custom-plugin/v1', '/get-database-columns', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_table_columns'),
            'permission_callback' => array($this, 'permissions_check'),
        ));

        register_rest_route('my-custom-plugin/v1', '/save-changes', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_save_changes'),
            'permission_callback' => array($this, 'permissions_check'),
        ));

        register_rest_route('my-custom-plugin/v1', '/use-cases', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_use_cases'),
            'permission_callback' => '__return_true', // Adjust as necessary
        ));

        register_rest_route('my-custom-plugin/v1', '/use-case/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_use_case'),
            'permission_callback' => '__return_true', // Allow all users
        ));

        register_rest_route('my-custom-plugin/v1', '/refresh-use-cases', array(
            'methods' => 'POST',
            'callback' => array('\MyCustomPlugin\Table_Handler', 'update_post_meta_on_refresh'),
            'permission_callback' => array($this, 'permissions_check'),
        ));
        

    }

    /**
     * Retrieve all use cases from the database.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_use_cases(WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_cases';

        try {
            // Fetch all use cases from the database
            $results = $wpdb->get_results("SELECT *, CONCAT('" . home_url() . "/usecase/', id, '/', use_case_slug) AS use_case_url FROM $table_name");
            if (empty($results)) {
                error_log('No use cases found in the database.');
                return new WP_Error('no_use_cases', 'No use cases found', array('status' => 404));
            }
            return rest_ensure_response($results);
        } catch (\Exception $e) {
            error_log('Database error: ' . $e->getMessage());
            return new WP_Error('db_error', 'Database error: ' . $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Retrieve a specific use case by ID.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_use_case(WP_REST_Request $request) {
        global $wpdb;
        $use_case_id = $request->get_param('id');
        $table_name = $wpdb->prefix . 'use_cases';

        try {
            // Fetch the use case by ID from the database
            $use_case = $wpdb->get_row($wpdb->prepare("SELECT *, CONCAT('" . home_url() . "/usecase/', id, '/', use_case_slug) AS use_case_url FROM $table_name WHERE id = %d", $use_case_id));
            if (!$use_case) {
                return new WP_Error('no_use_case', 'No use case found', array('status' => 404));
            }
            return rest_ensure_response($use_case);
        } catch (\Exception $e) {
            return new WP_Error('db_error', 'Database error: ' . $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Retrieve columns of the use cases table.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_table_columns(WP_REST_Request $request) {
        $table_name = 'use_cases';

        // Fetch table columns
        $columns = Table_Handler::get_table_columns($table_name);
        if (empty($columns)) {
            return new WP_Error('no_columns_found', 'No columns found.', array('status' => 404));
        }
        return rest_ensure_response(array('columns' => $columns));
    }

    /**
     * Check if the current user has the required permissions.
     * 
     * @param WP_REST_Request $request
     * @return true|WP_Error
     */
    public function permissions_check($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 403));
        }
        return true;
    }
    /**
     * Handle the CSV upload process.
     * 
     * @param WP_REST_Request $request The REST API request.
     * @return WP_REST_Response|WP_Error The response or error.
     */
    public function handle_upload_csv(WP_REST_Request $request) {
        error_log('handle_upload_csv called');
    
        if (!current_user_can('manage_options')) {
            error_log('User does not have manage_options capability');
            Table_Handler::insert_log('CSV Upload Attempt', 'User does not have manage_options capability');
            return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 403));
        }
    
        if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            error_log('Nonce verification failed');
            Table_Handler::insert_log('CSV Upload Attempt', 'Nonce verification failed');
            return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 401));
        }
    
        // Check if the file is uploaded
        if (!isset($_FILES['csv_file']) || empty($_FILES['csv_file']['tmp_name'])) {
            error_log('No file uploaded');
            Table_Handler::insert_log('CSV Upload Attempt', 'No file uploaded');
            return new WP_Error('no_file_uploaded', 'No file uploaded.', array('status' => 400));
        }
    
        $uploaded_file = $_FILES['csv_file']['tmp_name'];
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . basename($_FILES['csv_file']['name']);
    
        if (move_uploaded_file($uploaded_file, $file_path)) {
            error_log('File uploaded successfully: ' . $file_path);
            update_option('my_custom_plugin_csv_file', $file_path);
            Table_Handler::insert_log('CSV Uploaded', 'File uploaded successfully: ' . $file_path);
            return rest_ensure_response(array('success' => true, 'message' => 'File uploaded successfully.'));
        } else {
            error_log('Failed to move uploaded file');
            Table_Handler::insert_log('CSV Upload Failed', 'Failed to move uploaded file');
            return new WP_Error('file_upload_error', 'Failed to move uploaded file.', array('status' => 500));
        }
    }
    

    /**
     * Handle saving the CSV to database column mapping.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle_save_mapping(WP_REST_Request $request) {
        if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 401));
        }
    
        $mapping = $request->get_param('mapping');
        $table_name = 'use_cases'; // Use the table name directly
    
        if (!is_array($mapping)) {
            return new WP_Error('invalid_mapping', 'Invalid mapping data.', array('status' => 400));
        }
    
        $csv_file = get_option('my_custom_plugin_csv_file');
    
        if (!$csv_file || !file_exists($csv_file)) {
            error_log('CSV file not found: ' . $csv_file);
            return new WP_Error('csv_open_error', 'Error opening CSV file.', array('status' => 500));
        }
    
        $duplicate_rows = [];
        $updated_rows = [];
        $new_rows = [];
    
        if (($handle = fopen($csv_file, 'r')) !== FALSE) {
            $header = fgetcsv($handle);
            $existing_titles = [];
    
            while (($data = fgetcsv($handle)) !== FALSE) {
                $row_data = array();
                foreach ($mapping as $csv_column => $db_column) {
                    $index = array_search($csv_column, $header);
                    if ($index !== FALSE) {
                        $row_data[$db_column] = $data[$index];
                    }
                }
                $existing_row = Table_Handler::get_row_by_title($table_name, $row_data['use_case_title']);
    
                if ($existing_row) {
                    $is_duplicate = true;
                    foreach ($row_data as $key => $value) {
                        if ($existing_row->$key != $value) {
                            $is_duplicate = false;
                            break;
                        }
                    }
                    if ($is_duplicate) {
                        $duplicate_rows[] = $row_data;
                    } else {
                        $updated_rows[] = $row_data;
                    }
                } else {
                    if (!in_array($row_data['use_case_title'], $existing_titles)) {
                        $new_rows[] = $row_data;
                        $existing_titles[] = $row_data['use_case_title'];
                    }
                }
            }
            fclose($handle);
        }
    
        return rest_ensure_response([
            'duplicate_rows' => $duplicate_rows,
            'updated_rows' => $updated_rows,
            'new_rows' => $new_rows
        ]);
    }
    

    /**
     * Handle saving changes to the database.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle_save_changes(WP_REST_Request $request) {
        if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            Table_Handler::insert_log('Save Changes Attempt', 'Nonce verification failed');
            return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 401));
        }
    
        $type = $request->get_param('type');
        $rows = $request->get_param('rows');
        $table_name = 'use_cases';
    
        if (!in_array($type, ['duplicate_rows', 'updated_rows', 'new_rows'])) {
            Table_Handler::insert_log('Save Changes Attempt', 'Invalid type');
            return new WP_Error('invalid_type', 'Invalid type.', array('status' => 400));
        }
    
        foreach ($rows as $row) {
            switch ($type) {
                case 'duplicate_rows':
                    // No action needed for duplicates as per the new requirement.
                    break;
                case 'updated_rows':
                    $existing_row = Table_Handler::get_row_by_title($table_name, $row['use_case_title']);
                    if ($existing_row) {
                        Table_Handler::update_data($table_name, $existing_row->id, $row);
                    }
                    break;
                case 'new_rows':
                    $inserted_id = Table_Handler::insert_data($table_name, $row);
                    $row['id'] = $inserted_id; // Update row with inserted ID
                    break;
            }
        }
    
        Table_Handler::insert_log('Save Changes', 'Changes saved successfully for type: ' . $type);
        return rest_ensure_response(array('success' => true, 'message' => 'Changes saved successfully.'));
    }
    
}
