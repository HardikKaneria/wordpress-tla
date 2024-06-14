<?php
/*
Plugin Name: Use Cases Management System
Description: The Use Cases Management System plugin is a powerful and versatile tool designed to provide an efficient and comprehensive solution for managing product use cases within your WordPress site. This plugin is particularly beneficial for businesses and organizations that need to document and showcase various use cases, solutions, and success stories. It empowers users to create, upload, categorize, and manage detailed use case entries, enhancing the ability to demonstrate the effectiveness and benefits of their products or services.
Version: 1.1.2
Author: Hardik Kaneria
Text Domain: thelenders.app
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('MY_CUSTOM_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include necessary files
require_once MY_CUSTOM_PLUGIN_PATH . 'includes/basic-functions/class-table-handler.php';
require_once MY_CUSTOM_PLUGIN_PATH . 'includes/basic-functions/class-api-handler.php';
require_once MY_CUSTOM_PLUGIN_PATH . 'includes/basic-functions/class-admin-page.php';
require_once MY_CUSTOM_PLUGIN_PATH . 'includes/post-functions/class-post-creation-handler.php';

use MyCustomPlugin\Table_Handler;

// Function to run on plugin activation
function my_custom_plugin_activate()
{
    Table_Handler::create_table('use_cases');
    Table_Handler::create_logs_table(); // Create logs table
    register_use_case_post_type();
    register_use_case_taxonomy();
    use_case_rewrite_rules();
    flush_rewrite_rules();
    Table_Handler::insert_log('Plugin Activated', 'The plugin has been activated and necessary tables are created.');
}
register_activation_hook(__FILE__, 'my_custom_plugin_activate');

// Function to run on plugin deactivation
function my_custom_plugin_deactivate()
{
}
register_deactivation_hook(__FILE__, 'my_custom_plugin_deactivate');

// Add admin menu
function my_custom_plugin_menu()
{
    add_menu_page(
        'Use Cases Management',
        'Use Cases Management',
        'manage_options',
        'use-cases',
        array('MyCustomPlugin\Admin_Page', 'display_admin_page'),
        'dashicons-admin-generic'
    );

    add_submenu_page(
        'use-cases',
        'Use Case Logs',
        'Use Case Logs',
        'manage_options',
        'use-case-logs',
        array('MyCustomPlugin\Admin_Page', 'display_logs_page')
    );

    $table_exists = Table_Handler::check_table_exists('use_cases');
    if (!$table_exists) {
        add_submenu_page(
            'use-cases',
            'Create Table',
            'Create Table',
            'manage_options',
            'create-table',
            array('MyCustomPlugin\Admin_Page', 'display_create_table_page')
        );
    } else {
        add_submenu_page(
            'use-cases',
            'Upload CSV',
            'Upload CSV',
            'manage_options',
            'upload-csv',
            array('MyCustomPlugin\Admin_Page', 'display_upload_csv_page')
        );

        add_submenu_page(
            'use-cases',
            'View Data',
            'View Data',
            'manage_options',
            'view-data',
            array('MyCustomPlugin\Admin_Page', 'display_view_data_page')
        );

        add_submenu_page(
            'use-cases',
            'Update Table Structure',
            'Update Table Structure',
            'manage_options',
            'update-table-structure',
            array('MyCustomPlugin\Admin_Page', 'display_update_table_structure_page')
        );
    }
}
add_action('admin_menu', 'my_custom_plugin_menu');


// Register REST API endpoints
add_action('rest_api_init', function () {
    $api_handler = new MyCustomPlugin\API_Handler();
    $api_handler->register_routes();
});

// Handle single row deletion via AJAX
add_action('wp_ajax_delete_single_row', 'handle_delete_single_row');
function handle_delete_single_row()
{
    check_ajax_referer('wp_rest', '_wpnonce');

    if (isset($_POST['row_id'])) {
        $row_id = intval($_POST['row_id']);
        MyCustomPlugin\Table_Handler::delete_data('use_cases', $row_id);
        wp_send_json_success('Row deleted successfully.');
    } else {
        wp_send_json_error('Invalid row ID.');
    }
}

// Enqueue scripts and styles for use case search
function enqueue_use_case_scripts()
{
    wp_enqueue_script('tfjs', 'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest', array(), null, true);
    wp_enqueue_script('use', 'https://cdn.jsdelivr.net/npm/@tensorflow-models/universal-sentence-encoder', array('tfjs'), null, true);
    wp_enqueue_script('use-case-search', plugin_dir_url(__FILE__) . 'assets/js/use-case-search.js', array('jquery', 'tfjs', 'use'), null, true);
    wp_enqueue_style('use-case-search', plugin_dir_url(__FILE__) . 'assets/css/use-case-search.css', array(), null, 'all');


    wp_localize_script('use-case-search', 'myCustomPlugin', array(
        'apiUrl' => rest_url('my-custom-plugin/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
        'siteUrl' => home_url()
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_use_case_scripts');

function enqueue_admin_scripts()
{
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'assets/js/custom-script.js', array('jquery'), null, true);
    wp_localize_script('custom-script', 'myCustomPlugin', array(
        'apiUrl' => rest_url('my-custom-plugin/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

$image_url = plugins_url('assets/images/Fav.png', __FILE__);

// Shortcode for use case search
function use_case_search_shortcode()
{
    $image_url = plugins_url('assets/images/Fav.png', __FILE__);
    ob_start();
?> <style>
        .tla-sear-input {
            width: 100%;
            max-width: 678px;
            height: 61px;
            border: 2px #275B42 solid;
            padding: 0 20px;
            border-radius: 37px;
            font-size: 20px;
            font-family: Open Sans, sans-serif;
            font-weight: 700;
        }

        .tla-sear-button {
            width: 198px;
            height: 61px;
            background: #275B42;
            color: white;
            font-size: 20px;
            font-family: Open Sans, sans-serif;
            font-weight: 700;
            border: none;
            border-radius: 37px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
    <div id="use-case-search-container" class="tla-use-case-search">
        <div class="tla-sear-frame">
            <div class="tla-sear-frame14">
                <div class="tla-sear-title">
                    <h2>Browse Our Insights: Achieve Your Perfect Outcome</h2>
                </div>
                <div class="tla-sear-description">Enter your query and let our smart search guide you to relevant success stories and solutions. Boost efficiency, increase profitability, or enhance relationships with actionable insights from our use cases. Start exploring industry-leading solutions with just a few clicks.</div>
            </div>
            <div class="tla-sear-group">
                <div class="imgs">
                    <img src="<?php echo esc_url($image_url); ?>" width="61" height="61">
                </div>
                <input type="text" id="tla-sear-search-term" class="tla-sear-input" style="width: 100%;
    max-width: 678px;
    height: 61px;
    border: 2px #275B42 solid;
    padding: 0 20px;
    border-radius: 37px;
    font-size: 20px;
    font-family: Open Sans, sans-serif;
    font-weight: 700;
    " placeholder="Search Use Cases">
                <button id="tla-sear-search-button" class="tla-sear-button" style="    width: 198px;
    height: 61px;
    background: #275B42;
    color: white;
    font-size: 20px;
    font-family: Open Sans, sans-serif;
    font-weight: 700;
    border: none;
    border-radius: 37px;
    cursor: pointer;
    
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;">Search</button>
            </div>
            <div id="tla-sear-results-container" class="tla-sear-results"></div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('use_case_search', 'use_case_search_shortcode');

// Register custom post type for use cases
// Register Custom Post Type
function register_use_case_post_type()
{
    $labels = array(
        'name'                  => _x('Use Cases', 'Post type general name', 'textdomain'),
        'singular_name'         => _x('Use Case', 'Post type singular name', 'textdomain'),
        'menu_name'             => _x('Use Cases', 'Admin Menu text', 'textdomain'),
        'name_admin_bar'        => _x('Use Case', 'Add New on Toolbar', 'textdomain'),
        'add_new'               => __('Add New', 'textdomain'),
        'add_new_item'          => __('Add New Use Case', 'textdomain'),
        'new_item'              => __('New Use Case', 'textdomain'),
        'edit_item'             => __('Edit Use Case', 'textdomain'),
        'view_item'             => __('View Use Case', 'textdomain'),
        'all_items'             => __('All Use Cases', 'textdomain'),
        'search_items'          => __('Search Use Cases', 'textdomain'),
        'parent_item_colon'     => __('Parent Use Cases:', 'textdomain'),
        'not_found'             => __('No use cases found.', 'textdomain'),
        'not_found_in_trash'    => __('No use cases found in Trash.', 'textdomain'),
        'featured_image'        => _x('Featured Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
        'set_featured_image'    => _x('Set featured image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
        'remove_featured_image' => _x('Remove featured image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
        'use_featured_image'    => _x('Use as featured image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
        'archives'              => _x('Use Case archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
        'insert_into_item'      => _x('Insert into use case', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
        'uploaded_to_this_item' => _x('Uploaded to this use case', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
        'filter_items_list'     => _x('Filter use cases list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
        'items_list_navigation' => _x('Use Cases list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
        'items_list'            => _x('Use Cases list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'use_case'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'excerpt', 'thumbnail', 'comments', 'revisions', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('use_case', $args);
}

add_action('init', 'register_use_case_post_type');


add_action('init', 'register_use_case_post_type');

// Register meta boxes
function use_case_register_meta_boxes()
{
    add_meta_box('use_case_meta_box', 'Use Case Details', 'use_case_meta_box_callback', 'use_case', 'normal', 'high');
}

add_action('add_meta_boxes', 'use_case_register_meta_boxes');

function use_case_meta_box_callback($post)
{
    // Add nonce for security and authentication
    wp_nonce_field('use_case_nonce_action', 'use_case_nonce');

    // Retrieve existing meta data
    $use_case_thumbnail_url = get_post_meta($post->ID, 'use_case_thumbnail_url', true);
    $use_case_description = get_post_meta($post->ID, 'use_case_description', true);
    $use_case_solution_text = get_post_meta($post->ID, 'use_case_solution_text', true);
    $use_case_result_text = get_post_meta($post->ID, 'use_case_result_text', true);
    $use_case_cta_text = get_post_meta($post->ID, 'use_case_cta_text', true);
    $use_case_cta_url = get_post_meta($post->ID, 'use_case_cta_url', true);

    // Display the form fields
?>
    <p>
        <label for="use_case_thumbnail_url">Thumbnail URL:</label>
        <input type="text" id="use_case_thumbnail_url" name="use_case_thumbnail_url" value="<?php echo esc_attr($use_case_thumbnail_url); ?>" />
    </p>
    <p>
        <label for="use_case_description">Description:</label>
        <textarea id="use_case_description" name="use_case_description"><?php echo esc_textarea($use_case_description); ?></textarea>
    </p>
    <p>
        <label for="use_case_solution_text">Solution:</label>
        <textarea id="use_case_solution_text" name="use_case_solution_text"><?php echo esc_textarea($use_case_solution_text); ?></textarea>
    </p>
    <p>
        <label for="use_case_result_text">Result:</label>
        <textarea id="use_case_result_text" name="use_case_result_text"><?php echo esc_textarea($use_case_result_text); ?></textarea>
    </p>
    <p>
        <label for="use_case_cta_text">CTA Text:</label>
        <input type="text" id="use_case_cta_text" name="use_case_cta_text" value="<?php echo esc_attr($use_case_cta_text); ?>" />
    </p>
    <p>
        <label for="use_case_cta_url">CTA URL:</label>
        <input type="text" id="use_case_cta_url" name="use_case_cta_url" value="<?php echo esc_attr($use_case_cta_url); ?>" />
    </p>
<?php
}

// Save meta box data
function use_case_save_meta_box_data($post_id)
{
    // Check nonce for security
    if (!isset($_POST['use_case_nonce']) || !wp_verify_nonce($_POST['use_case_nonce'], 'use_case_nonce_action')) {
        return;
    }

    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (isset($_POST['post_type']) && 'use_case' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Save meta data
    $fields = [
        'use_case_thumbnail_url',
        'use_case_description',
        'use_case_solution_text',
        'use_case_result_text',
        'use_case_cta_text',
        'use_case_cta_url',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}

add_action('save_post', 'use_case_save_meta_box_data');


function save_use_case_meta($post_id)
{
    // Verify nonce
    if (!isset($_POST['use_case_nonce']) || !wp_verify_nonce($_POST['use_case_nonce'], 'use_case_nonce_action')) {
        return;
    }

    // Check if auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save meta data
    update_post_meta($post_id, 'use_case_thumbnail_url', sanitize_text_field($_POST['use_case_thumbnail_url']));
    update_post_meta($post_id, 'use_case_description', sanitize_textarea_field($_POST['use_case_description']));
    update_post_meta($post_id, 'use_case_solution_text', sanitize_textarea_field($_POST['use_case_solution_text']));
    update_post_meta($post_id, 'use_case_result_text', sanitize_textarea_field($_POST['use_case_result_text']));
    update_post_meta($post_id, 'use_case_cta_text', sanitize_text_field($_POST['use_case_cta_text']));
    update_post_meta($post_id, 'use_case_cta_url', esc_url_raw($_POST['use_case_cta_url']));
}

add_action('save_post', 'save_use_case_meta');


// Register Custom Taxonomy
function register_use_case_taxonomy()
{
    $labels = array(
        'name'                       => _x('Use Case Categories', 'taxonomy general name', 'textdomain'),
        'singular_name'              => _x('Use Case Category', 'taxonomy singular name', 'textdomain'),
        'search_items'               => __('Search Use Case Categories', 'textdomain'),
        'all_items'                  => __('All Use Case Categories', 'textdomain'),
        'parent_item'                => __('Parent Use Case Category', 'textdomain'),
        'parent_item_colon'          => __('Parent Use Case Category:', 'textdomain'),
        'edit_item'                  => __('Edit Use Case Category', 'textdomain'),
        'update_item'                => __('Update Use Case Category', 'textdomain'),
        'add_new_item'               => __('Add New Use Case Category', 'textdomain'),
        'new_item_name'              => __('New Use Case Category Name', 'textdomain'),
        'menu_name'                  => __('Use Case Categories', 'textdomain'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'use_case_category'),
    );

    register_taxonomy('use_case_category', array('use_case'), $args);
}
add_action('init', 'register_use_case_taxonomy');



// Register REST API endpoints for post creation
// In your main plugin file or where you initialize your plugin
add_action('rest_api_init', function () {
    $post_creation_handler = new \MyCustomPlugin\Post_Creation_Handler();
    $post_creation_handler->register_routes();
    error_log('Post_Creation_Handler routes registered');
});


// Register use case rewrite rules
function use_case_rewrite_rules()
{
    add_rewrite_rule(
        '^use-case/([0-9]+)/([^/]+)/?$',
        'index.php?use_case_id=$matches[1]&use_case_slug=$matches[2]',
        'top'
    );
}
add_action('init', 'use_case_rewrite_rules');

// Add custom query vars
function add_custom_query_vars($vars)
{
    $vars[] = 'use_case_id';
    $vars[] = 'use_case_slug';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');

// Use custom template for use case posts
// Load custom template for Use Case post type
function use_case_single_template($single)
{
    global $post;

    if ($post->post_type == 'use_case') {
        $template = plugin_dir_path(__FILE__) . 'templates/single-use_case.php';
        if (file_exists($template)) {
            return $template;
        }
    }
    return $single;
}

add_filter('single_template', 'use_case_single_template');


// Register use case URLs on dashboard load
function register_use_case_urls_on_dashboard_load()
{
    if (is_admin()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'use_cases';
        $use_cases = $wpdb->get_results("SELECT id, use_case_slug FROM $table_name");

        foreach ($use_cases as $use_case) {
            add_rewrite_rule(
                '^usecase/' . $use_case->use_case_slug . '/?$',
                'index.php?use_case_id=' . $use_case->id . '&use_case_slug=' . $use_case->use_case_slug,
                'top'
            );
        }
        flush_rewrite_rules();
    }
}
add_action('admin_init', 'register_use_case_urls_on_dashboard_load');


function my_custom_plugin_enqueue_scripts()
{
    if (is_page_template('page-use-cases.php')) {
        wp_enqueue_style('use-cases-css', plugin_dir_url(__FILE__) . 'assets/css/use-cases.css');
    }
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_enqueue_scripts');

function my_custom_plugin_register_page_template($page_templates)
{
    $page_templates['page-use-cases.php'] = 'Use Cases Page';
    return $page_templates;
}
add_filter('theme_page_templates', 'my_custom_plugin_register_page_template');

function my_custom_plugin_redirect_page_template($template)
{
    if (is_page() && get_page_template_slug() == 'page-use-cases.php') {
        $plugin_template = plugin_dir_path(__FILE__) . 'page-use-cases.php';
        if (file_exists($plugin_template)) {
            $template = $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'my_custom_plugin_redirect_page_template');

add_action('pre_get_posts', 'include_custom_post_types_in_query');

function include_custom_post_types_in_query($query)
{
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'use_case') {
        error_log('Query is set to include Use Case posts.');
    }
}
