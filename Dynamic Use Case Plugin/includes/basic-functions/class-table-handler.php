<?php

namespace MyCustomPlugin;

use WP_REST_Response;
use WP_REST_Request;
use WP_Error;
class Table_Handler
{

    /**
     * Create the custom table for storing use cases.
     * 
     * @param string $table_name Name of the table to create.
     */
    public static function create_table($table_name = 'use_cases')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            use_case_title text NOT NULL,
            use_case_category varchar(255) NOT NULL,
            use_case_description text NOT NULL,
            use_case_thumbnail_url text NOT NULL,
            use_case_image_url text NOT NULL,
            use_case_solution_text text NOT NULL,
            use_case_result_text text NOT NULL,
            use_case_excerpt text NOT NULL,
            use_case_cta_text varchar(255) NOT NULL,
            use_case_slug varchar(255) NOT NULL,
            use_case_url text,
            use_case_post_id bigint(20),
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Delete the specified table.
     * 
     * @param string $table_name Name of the table to delete.
     */
    public static function delete_table($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    /**
     * Insert data into the specified table.
     * 
     * @param string $table_name Name of the table to insert data into.
     * @param array $data Data to insert.
     * @return int ID of the inserted row.
     * @throws \Exception If an error occurs during insertion.
     */
    public static function insert_data($table_name, $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;

        // Generate slug from the title
        $data['use_case_slug'] = sanitize_title($data['use_case_title']);

        // Check if a post with the same title already exists
        $existing_post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_type = 'use_case'", $data['use_case_title']));
        if ($existing_post_id) {
            // If the post already exists, update the use case table with the existing post ID and URL
            $permalink = get_permalink($existing_post_id);
            $data['use_case_post_id'] = $existing_post_id;
            $data['use_case_url'] = $permalink;

            // Insert or update the use case table
            $wpdb->replace($table_name, $data);

            if ($wpdb->last_error) {
                throw new \Exception($wpdb->last_error);
            }

            // Ensure the use case category is set
            wp_set_post_terms($existing_post_id, $data['use_case_category'], 'use_case_category');

            return (int) $existing_post_id;
        }
        $excerpt = $data['use_case_excerpt'];
        // Insert post
        $post_id = wp_insert_post([
            'post_title' => $data['use_case_title'],
            'post_content' => $data['use_case_description'],
            'post_excerpt' => $excerpt,
            'post_type' => 'use_case',
            'post_status' => 'publish',
            'meta_input' => [
                'use_case_thumbnail_url' => $data['use_case_thumbnail_url'],
                'use_case_image_url' => $data['use_case_image_url'],
                'use_case_solution_text' => $data['use_case_solution_text'],
                'use_case_result_text' => $data['use_case_result_text'],
                'use_case_cta_text' => $data['use_case_cta_text'],
                'use_case_category' => $data['use_case_category']
            ]
        ]);

        if (is_wp_error($post_id)) {
            throw new \Exception($post_id->get_error_message());
        }

        // Get permalink
        $permalink = get_permalink($post_id);

        // Add permalink and post ID to the data
        $data['use_case_url'] = $permalink;
        $data['use_case_post_id'] = $post_id;

        // Insert data into custom table
        $wpdb->insert($table_name, $data);

        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }

        // Set the use case category
        wp_set_post_terms($post_id, $data['use_case_category'], 'use_case_category');

        return (int) $wpdb->insert_id;
    }

    /**
     * Update data in the specified table.
     * 
     * @param string $table_name Name of the table to update.
     * @param int $id ID of the row to update.
     * @param array $data Data to update.
     * @throws \Exception If an error occurs during update.
     */
    public static function update_data($table_name, $id, $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;

        if (isset($data['use_case_title'])) {
            $data['use_case_slug'] = sanitize_title($data['use_case_title']);
        }

        $post_id = wp_update_post([
            'ID' => $data['use_case_post_id'],
            'post_title' => $data['use_case_title'],
            'post_content' => $data['use_case_description']
        ]);

        if (is_wp_error($post_id)) {
            throw new \Exception($post_id->get_error_message());
        }

        update_post_meta($post_id, 'use_case_thumbnail_url', $data['use_case_thumbnail_url']);
        update_post_meta($post_id, 'use_case_image_url', $data['use_case_image_url']);
        update_post_meta($post_id, 'use_case_solution_text', $data['use_case_solution_text']);
        update_post_meta($post_id, 'use_case_result_text', $data['use_case_result_text']);
        update_post_meta($post_id, 'use_case_cta_text', $data['use_case_cta_text']);
        update_post_meta($post_id, 'use_case_category', $data['use_case_category']);

        $permalink = get_permalink($post_id);
        $data['use_case_url'] = $permalink;

        $wpdb->update($table_name, $data, ['id' => $id]);

        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }

        // Log the update
        self::log_activity('update', $id, $post_id, 'Post and use case updated successfully.');
    }


    /**
     * Delete data from the specified table.
     * 
     * @param string $table_name Name of the table to delete data from.
     * @param int $id ID of the row to delete.
     * @throws \Exception If an error occurs during deletion.
     */
    public static function delete_data($table_name, $id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;

        // Get the post ID from the use case
        $use_case = self::get_row_by_id($table_name, $id);
        $post_id = $use_case ? $use_case->use_case_post_id : null;

        // Delete post
        if ($post_id) {
            wp_delete_post($post_id, true);
        }

        // Delete from custom table
        $wpdb->delete($table_name, array('id' => $id));

        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }

        // Log the deletion
        self::log_activity('delete', $id, $post_id, 'Post and use case deleted successfully.');
    }


    /**
     * Log activity in the log table
     *
     * @param string $action The action performed (create, update, delete).
     * @param int $use_case_id The ID of the use case.
     * @param int $post_id The ID of the WordPress post.
     * @param string $message The log message.
     */
    private static function log_activity($action, $use_case_id, $post_id, $message)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_case_logs';

        $wpdb->insert($table_name, array(
            'action' => $action,
            'use_case_id' => $use_case_id,
            'post_id' => $post_id,
            'message' => $message,
            'created_at' => current_time('mysql')
        ));
    }


    /**
     * Retrieve all data from the specified table.
     * 
     * @param string $table_name Name of the table to retrieve data from.
     * @return array|object|null Table rows as objects.
     */
    public static function get_data($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_results("SELECT * FROM $table_name");
    }

    /**
     * Retrieve titles from the specified table.
     * 
     * @param string $table_name Name of the table to retrieve titles from.
     * @return array|object|null Table rows with titles as objects.
     */
    public static function get_titles($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_results("SELECT id, use_case_title, use_case_thumbnail_url, use_case_image_url, use_case_slug FROM $table_name");
    }

    /**
     * Check if the specified table exists.
     * 
     * @param string $table_name Name of the table to check.
     * @return bool True if the table exists, false otherwise.
     */
    public static function check_table_exists($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    }

    /**
     * Add a new column to the specified table.
     * 
     * @param string $table_name Name of the table to add the column to.
     * @param string $column_name Name of the column to add.
     * @param string $column_type Type of the column to add.
     * @throws \Exception If an error occurs during the column addition.
     */
    public static function add_column($table_name, $column_name, $column_type)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $wpdb->query("ALTER TABLE $table_name ADD $column_name $column_type");

        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }
    }

    /**
     * Get the total number of use cases in the specified table.
     * 
     * @param string $table_name Name of the table to count rows in.
     * @return int Total number of use cases.
     */
    public static function get_total_use_cases($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    /**
     * Get the count of use cases per category in the specified table.
     * 
     * @param string $table_name Name of the table to count categories in.
     * @return array Category counts as an associative array.
     */
    public static function get_category_count($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_results("SELECT use_case_category, COUNT(*) as count FROM $table_name GROUP BY use_case_category", ARRAY_A);
    }

    /**
     * Get the structure of the specified table.
     * 
     * @param string $table_name Name of the table to describe.
     * @return array Table structure as an array of objects.
     */
    public static function get_table_structure($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_results("DESCRIBE $table_name");
    }

    /**
     * Get the column names of the specified table.
     * 
     * @param string $table_name Name of the table to get columns from.
     * @return array Column names as an array.
     */
    public static function get_table_columns($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name", ARRAY_A);
        return array_map(function ($column) {
            return $column['Field'];
        }, $columns);
    }

    /**
     * Get a row from the table by title.
     * 
     * @param string $table_name Name of the table to query.
     * @param string $title Title of the row to fetch.
     * @return object|null The row object if found, null otherwise.
     */
    public static function get_row_by_title($table_name, $title)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE use_case_title = %s", $title));
    }

    /**
     * Get the first row from the specified table.
     * 
     * @param string $table_name Name of the table to query.
     * @return object|null The first row object if found, null otherwise.
     */
    public static function get_first_row($table_name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
    }

    /**
     * Get a row from the table by ID.
     * 
     * @param string $table_name Name of the table to query.
     * @param int $id ID of the row to fetch.
     * @return object|null The row object if found, null otherwise.
     */
    public static function get_row_by_id($table_name, $id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }

    /**
     * Generate a URL-friendly slug from the given title.
     * 
     * @param string $title Title to generate the slug from.
     * @return string Generated slug.
     */
    private static function generate_slug($title)
    {
        $slug = sanitize_title($title);
        return $slug;
    }

    /**
     * Create the logs table.
     */
    public static function create_logs_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_case_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            action text NOT NULL,
            details text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Insert a log entry.
     * 
     * @param string $action The action being logged.
     * @param string $details Details about the action.
     */
    public static function insert_log($action, $details)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_case_logs';

        $wpdb->insert($table_name, [
            'action' => $action,
            'details' => $details,
        ]);

        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }
    }

    /**
     * Get all log entries.
     * 
     * @return array List of log entries.
     */
    public static function get_logs()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_case_logs';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    }
    public static function update_post_meta_on_refresh() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_cases';
    
        $use_cases = $wpdb->get_results("SELECT * FROM $table_name");
    
        if (empty($use_cases)) {
            error_log('No use cases found in the database.');
            return new WP_REST_Response('No use cases found.', 404);
        }
    
        foreach ($use_cases as $use_case) {
            if (is_null($use_case->use_case_post_id)) {
                // Check if the post already exists by title
                $existing_post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_type = 'use_case'", $use_case->use_case_title));
                if ($existing_post_id) {
                    // Update the use case table with the existing post ID and URL
                    $permalink = get_permalink($existing_post_id);
                    $wpdb->update($table_name, ['use_case_post_id' => $existing_post_id, 'use_case_url' => $permalink], ['id' => $use_case->id]);
                    // Update the post meta and categories
                    self::update_post_meta($use_case, $existing_post_id);
                    continue;
                }
    
                $excerpt = $use_case->use_case_excerpt;
                $post_id = wp_insert_post(array(
                    'post_title' => $use_case->use_case_title,
                    'post_content' => $use_case->use_case_description,
                    'post_excerpt' => $excerpt,
                    'post_type' => 'use_case',
                    'post_status' => 'publish',
                    'meta_input' => array(
                        'use_case_thumbnail_url' => $use_case->use_case_thumbnail_url,
                        'use_case_image_url' => $use_case->use_case_image_url,
                        'use_case_solution_text' => $use_case->use_case_solution_text,
                        'use_case_result_text' => $use_case->use_case_result_text,
                        'use_case_cta_text' => $use_case->use_case_cta_text,
                        'use_case_category' => $use_case->use_case_category
                    )
                ));
    
                if (is_wp_error($post_id)) {
                    error_log('Error creating post: ' . $post_id->get_error_message());
                    continue;
                }
    
                // Set the use case category
                $term_result = wp_set_post_terms($post_id, $use_case->use_case_category, 'use_case_categories');
                if (is_wp_error($term_result)) {
                    error_log('Error setting terms: ' . $term_result->get_error_message());
                } else {
                    error_log('Terms set successfully.');
                }
    
                // Set the featured image
                $thumbnail_url = $use_case->use_case_thumbnail_url;
                if (!empty($thumbnail_url)) {
                    $attachment_id = self::set_featured_image_from_url($thumbnail_url, $post_id);
                    if (is_wp_error($attachment_id)) {
                        error_log('Error setting featured image: ' . $attachment_id->get_error_message());
                    } else {
                        set_post_thumbnail($post_id, $attachment_id);
                        error_log("Featured image set successfully: Post ID - $post_id, Attachment ID - $attachment_id");
                    }
                } else {
                    error_log('No thumbnail URL provided for use case ID: ' . $use_case->id);
                }
    
                // Get permalink and update custom table
                $permalink = get_permalink($post_id);
                $wpdb->update($table_name, ['use_case_post_id' => $post_id, 'use_case_url' => $permalink], ['id' => $use_case->id]);
                error_log("Use case post created: ID - $post_id, Title - {$use_case->use_case_title}");
    
                // Log the post creation
                self::log_activity('create', $use_case->id, $post_id, 'Post created successfully.');
    
            } else {
                // Check if the post ID exists in the posts table
                $post_exists = get_post_status($use_case->use_case_post_id);
                if ($post_exists) {
                    // Update existing post meta
                    self::update_post_meta($use_case, $use_case->use_case_post_id);
                    error_log('Post meta updated for use case ID: ' . $use_case->id);
                } else {
                    // The post ID does not exist, create a new post
                    $excerpt = $use_case->use_case_excerpt;
                $post_id = wp_insert_post(array(
                    'post_title' => $use_case->use_case_title,
                    'post_content' => $use_case->use_case_description,
                    'post_excerpt' => $excerpt,
                    'post_type' => 'use_case',
                    'post_status' => 'publish',
                    'meta_input' => array(
                        'use_case_thumbnail_url' => $use_case->use_case_thumbnail_url,
                        'use_case_image_url' => $use_case->use_case_image_url,
                        'use_case_solution_text' => $use_case->use_case_solution_text,
                        'use_case_result_text' => $use_case->use_case_result_text,
                        'use_case_cta_text' => $use_case->use_case_cta_text,
                        'use_case_category' => $use_case->use_case_category
                    )
                ));
    
                    if (is_wp_error($post_id)) {
                        error_log('Error creating post: ' . $post_id->get_error_message());
                        continue;
                    }
    
                    // Set the use case category
                    $term_result = wp_set_post_terms($post_id, $use_case->use_case_category, 'use_case_categories');
                    if (is_wp_error($term_result)) {
                        error_log('Error setting terms: ' . $term_result->get_error_message());
                    } else {
                        error_log('Terms set successfully.');
                    }
    
                    // Set the featured image
                    $thumbnail_url = $use_case->use_case_thumbnail_url;
                    if (!empty($thumbnail_url)) {
                        $attachment_id = self::set_featured_image_from_url($thumbnail_url, $post_id);
                        if (is_wp_error($attachment_id)) {
                            error_log('Error setting featured image: ' . $attachment_id->get_error_message());
                        } else {
                            set_post_thumbnail($post_id, $attachment_id);
                            error_log("Featured image set successfully: Post ID - $post_id, Attachment ID - $attachment_id");
                        }
                    } else {
                        error_log('No thumbnail URL provided for use case ID: ' . $use_case->id);
                    }
    
                    // Get permalink and update custom table
                    $permalink = get_permalink($post_id);
                    $wpdb->update($table_name, ['use_case_post_id' => $post_id, 'use_case_url' => $permalink], ['id' => $use_case->id]);
                    error_log("Use case post created: ID - $post_id, Title - {$use_case->use_case_title}");
    
                    // Log the post creation
                    self::log_activity('create', $use_case->id, $post_id, 'Post created successfully.');
                }
            }
        }
    
        self::insert_log('Refresh Use Cases', 'Use cases refreshed successfully.');
        error_log('Use cases refreshed successfully.');
        return new WP_REST_Response(array('success' => true, 'message' => 'Use cases refreshed successfully.'), 200);
    }
    
    /**
     * Download an image from a URL and set it as the featured image for a post.
     *
     * @param string $image_url The URL of the image to download.
     * @param int $post_id The ID of the post to attach the image to.
     * @return int|WP_Error The attachment ID on success, WP_Error on failure.
     */
    public static function set_featured_image_from_url($image_url, $post_id) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    
        // Download the image and attach it to the post
        $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');
    
        if (is_wp_error($attachment_id)) {
            error_log('Error downloading image: ' . $attachment_id->get_error_message());
        } else {
            error_log('Image downloaded successfully: ' . $attachment_id);
        }
    
        return $attachment_id;
    }
    

    /**
     * Update post meta for an existing post.
     * 
     * @param object $use_case The use case object.
     * @param int $post_id The post ID.
     */
    private static function update_post_meta($use_case, $post_id = null) {
        $post_id = $post_id ?: $use_case->use_case_post_id;

        update_post_meta($post_id, 'use_case_thumbnail_url', $use_case->use_case_thumbnail_url);
        update_post_meta($post_id, 'use_case_image_url', $use_case->use_case_image_url);
        update_post_meta($post_id, 'use_case_solution_text', $use_case->use_case_solution_text);
        update_post_meta($post_id, 'use_case_result_text', $use_case->use_case_result_text);
        update_post_meta($post_id, 'use_case_cta_text', $use_case->use_case_cta_text);
        wp_set_post_terms($post_id, $use_case->use_case_category, 'use_case_category');
    }
}

add_action('rest_api_init', function () {
    register_rest_route('my-custom-plugin/v1', '/refresh-use-cases', array(
        'methods' => 'POST',
        'callback' => array('\MyCustomPlugin\Table_Handler', 'update_post_meta_on_refresh'),
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
});


