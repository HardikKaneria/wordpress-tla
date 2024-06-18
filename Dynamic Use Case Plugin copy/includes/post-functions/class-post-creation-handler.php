<?php

namespace MyCustomPlugin;

use WP_REST_Request;
use WP_Error;
use WP_REST_Response;

class Post_Creation_Handler
{

    /**
     * Register REST API routes for creating use case posts.
     */
    public function register_routes()
    {
        register_rest_route('my-custom-plugin/v1', '/create-use-case-posts', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_use_case_posts'),
            'permission_callback' => array($this, 'permissions_check'),
        ));
        error_log('Route /create-use-case-posts registered');
    }

    /**
     * Create WordPress posts from use case data stored in the custom table.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    /**
     * Create or update WordPress posts from use case data stored in the custom table.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function create_use_case_posts(WP_REST_Request $request)
    {
        error_log('create_use_case_posts called');
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_cases';
        $use_cases = $wpdb->get_results("SELECT * FROM $table_name");

        if (empty($use_cases)) {
            error_log('No use cases to process.');
            return rest_ensure_response(array('success' => false, 'message' => 'No use cases to process.'));
        }

        foreach ($use_cases as $use_case) {
            error_log('Processing use case ID: ' . $use_case->id);

            // Check if the post exists by ID
            if ($use_case->use_case_post_id) {
                $post_exists = get_post_status($use_case->use_case_post_id);
                if ($post_exists) {
                    // Update the post meta if the post exists
                    $this->update_post_meta($use_case);
                    error_log('Updated existing post for use case ID: ' . $use_case->id);
                    continue;
                }
            }

            // Check if a post with the same title already exists
            $existing_post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_type = 'use_case'", $use_case->use_case_title));
            if ($existing_post_id) {
                // Update the use case table with the existing post ID and URL
                $permalink = get_permalink($existing_post_id);
                $wpdb->update($table_name, ['use_case_post_id' => $existing_post_id, 'use_case_url' => $permalink], ['id' => $use_case->id]);
                // Update the post meta and categories
                $this->update_post_meta($use_case, $existing_post_id);
                error_log('Linked existing post for use case ID: ' . $use_case->id);
                continue;
            }

            // Create a new post if it doesn't exist
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
             // Set the featured image
             $thumbnail_url = $use_case->use_case_thumbnail_url;
             if (!empty($thumbnail_url)) {
                 $attachment_id = self::set_featured_image_from_url($thumbnail_url, $post_id);
                 if (is_wp_error($attachment_id)) {
                     error_log('Error setting featured image: ' . $attachment_id->get_error_message());
                 } else {
                     set_post_thumbnail($post_id, $attachment_id);
                 }
             }

            // Set the use case category
            $term_result = wp_set_post_terms($post_id, $use_case->use_case_category, 'use_case_categories');
            if (is_wp_error($term_result)) {
                error_log('Error setting terms: ' . $term_result->get_error_message());
            } else {
                error_log('Terms set successfully.');
            }

            // Log the post creation
            $this->log_activity('create', $use_case->id, $post_id, 'Post created successfully.');

            // Get permalink and update custom table
            $permalink = get_permalink($post_id);
            $wpdb->update($table_name, ['use_case_post_id' => $post_id, 'use_case_url' => $permalink], ['id' => $use_case->id]);
            error_log("Use case post created: ID - $post_id, Title - {$use_case->use_case_title}");
        }

        return rest_ensure_response(array('success' => true, 'message' => 'Use case posts processed successfully.'));
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

    return $attachment_id;
}

    /**
     * Update post meta for an existing post.
     * 
     * @param object $use_case The use case object.
     * @param int $post_id The post ID.
     */
    private function update_post_meta($use_case, $post_id = null)
    {
        $post_id = $post_id ?: $use_case->use_case_post_id;

        update_post_meta($post_id, 'use_case_thumbnail_url', $use_case->use_case_thumbnail_url);
        update_post_meta($post_id, 'use_case_image_url', $use_case->use_case_image_url);
        update_post_meta($post_id, 'use_case_solution_text', $use_case->use_case_solution_text);
        update_post_meta($post_id, 'use_case_result_text', $use_case->use_case_result_text);
        update_post_meta($post_id, 'use_case_cta_text', $use_case->use_case_cta_text);
        wp_set_post_terms($post_id, $use_case->use_case_category, 'use_case_categories');
    }

    /**
     * Log activity in the log table
     *
     * @param string $action The action performed (create, update, delete).
     * @param int $use_case_id The ID of the use case.
     * @param int $post_id The ID of the WordPress post.
     * @param string $message The log message.
     */
    private function log_activity($action, $use_case_id, $post_id, $message)
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
        error_log("Log entry created: Action - $action, Use Case ID - $use_case_id, Post ID - $post_id, Message - $message");
    }

    /**
     * Permission callback to check if the current user has manage_options capability.
     * 
     * @param WP_REST_Request $request
     * @return true|WP_Error
     */
    public function permissions_check($request)
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 403));
        }
        return true;
    }
}

// Ensure this handler is used
add_action('rest_api_init', function () {
    $post_creation_handler = new \MyCustomPlugin\Post_Creation_Handler();
    $post_creation_handler->register_routes();
});
