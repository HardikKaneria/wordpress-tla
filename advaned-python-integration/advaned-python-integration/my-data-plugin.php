<?php
/**
 * Plugin Name: My Data Processing Plugin
 * Description: A plugin to upload CSV/Excel, process with Pyodide, and display charts.
 * Version: 1.0
 * Author: Your Name
 */

function my_data_plugin_enqueue_scripts() {
    // Scripts are enqueued in wp_head if the shortcode is present.
}

add_action('wp_enqueue_scripts', 'my_data_plugin_enqueue_scripts');

function my_data_plugin_shortcode() {
    $form_html = '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                  <form id="data-upload-form" method="post" enctype="multipart/form-data">
                    <input type="file" name="datafile" id="datafile">
                    <!-- Update your form submission button to include an ID -->
                    <button id="upload-btn" type="submit">Upload</button>
                  </form>
                  <!-- Add this to your HTML where you want the loading indicator to appear -->
                  <div id="loading-indicator" style="display: none;">
                      <p>Loading... Please wait.</p>
                      <!-- You can also add a spinner or any other graphical element here -->
                  </div>
                  <div id="chart-container"></div>'; // Container where charts will be displayed.
    return $form_html;
}


add_shortcode('my_data_plugin', 'my_data_plugin_shortcode');

function my_data_plugin_add_pyodide_to_header() {
    global $post;
    if (isset($post->post_content) && has_shortcode($post->post_content, 'my_data_plugin')) {
        echo '<script type="module">
                import { loadPyodide } from "https://cdn.jsdelivr.net/pyodide/v0.18.1/full/pyodide.mjs";
                window.loadPyodide = loadPyodide;
              </script>
              <script type="module" src="' . plugins_url('js/process-data.js', __FILE__) . '"></script>';


    }
}

add_action('wp_head', 'my_data_plugin_add_pyodide_to_header');
