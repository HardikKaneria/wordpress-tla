<?php

namespace MyCustomPlugin;

class Admin_Page
{

    /**
     * Display the main admin page for the plugin.
     */
    public static function display_admin_page()
    {
        $table_name = 'use_cases';

        // Retrieve total number of use cases
        $total_use_cases = Table_Handler::get_total_use_cases($table_name);

        // Retrieve count of use cases per category
        $categories_count = Table_Handler::get_category_count($table_name);
?><div class="wrap use-cases-dashboard">
            <h1 class="page-title-admin">Use Cases Management System</h1>
            <div class="statistics-section">
                <h2 class="section-title">Statistics</h2>
                <p class="section-description">Overview of the total number of use cases available in the system.</p>
                <p class="total-use-cases">Total Use Cases: <strong><?php echo $total_use_cases; ?> use cases</strong></p>
            </div>
            <div class="twotab">
                <div class="category-counts-section">
                    <h3 class="section-title">Category Counts</h3>
                    <p class="section-description">A breakdown of use cases by category.</p>
                    <table class="wp-list-table widefat fixed striped category-counts-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Use Cases</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories_count as $category_count) { ?>
                                <tr>
                                    <td><?php echo esc_html($category_count['use_case_category']); ?></td>
                                    <td><?php echo esc_html($category_count['count']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="refresh-section">
                    <h3 class="section-title">Refresh Use Cases</h3>
                    <p class="section-description">Click the button below to refresh the use cases data.</p>
                    <button id="refresh-use-cases" class="button button-primary">Refresh Use Cases</button>
                    <button id="export-use-cases" class="button button-secondary" style="margin-left: 10px;">Export Use Cases</button>
                    <div id="refresh-result" style="margin-top: 20px;"></div>
                </div>
            </div>
            <div class="container-main">
                <h2 class="plugin-description-title">Plugin Description</h2>
                <p class="plugin-description"><strong>Plugin Name:</strong> Use Cases Management System</p>
                <p class="plugin-description"><strong>Description:</strong> The Use Cases Management System plugin is a powerful and versatile tool designed to provide an efficient and comprehensive solution for managing product use cases within your WordPress site. This plugin is particularly beneficial for businesses and organizations that need to document and showcase various use cases, solutions, and success stories. It empowers users to create, upload, categorize, and manage detailed use case entries, enhancing the ability to demonstrate the effectiveness and benefits of their products or services.</p>
            </div>
        </div>


        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Roboto:wght@400;500&display=swap');

            .wrap.use-cases-dashboard {
                background-color: #f4f6f9;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                font-family: 'Roboto', sans-serif;
            }

            .plugin-features,
            .function-features {
                font-size: 16px;
                color: #000000;
                margin: 10px 20px;
                padding-left: 20px;
                list-style-type: disc;
                line-height: 26px;
            }

            .page-title-admin {
                font-size: 36px !important;
                color: #275B42;
                margin-bottom: 10px;
                font-weight: 700 !important;
                font-family: 'Montserrat', sans-serif;
            }

            .plugin-description {
                font-size: 16px;
                color: #000000;
                margin: 10px 20px 30px 20px !important;
                line-height: 26px;
            }

            .statistics-section {
                margin-top: 20px;
                padding: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .category-counts-section,
            .refresh-section {
                margin-top: 20px;
                padding: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                width: 50%;
            }

            .twotab {
                display: flex;
                gap: 40px;
                flex-direction: row;
            }

            .section-title {
                font-size: 1.5em;
                color: #275B42;
                margin-bottom: 10px;
                font-family: 'Montserrat', sans-serif;
            }

            .section-description {
                font-size: 18px;
                color: #777;
                margin-bottom: 20px;
            }

            #refresh-use-cases, #export-use-cases {
                background: #275B42;
                border-color: #275B42;
                color: #fff;
                text-decoration: none;
                text-shadow: none;
            }

            #refresh-use-cases:hover {
                background: transparent;
                border-color: #275B42;
                color: #275B42;
                text-decoration: none;
                text-shadow: none;
            }

            .total-use-cases {
                font-size: 1.2em;
                color: #333;
            }

            .category-counts-table th,
            .category-counts-table td {
                padding: 10px;
                text-align: left;
                font-size: 18px;
                font-weight: 500;
                text-align: center;
            }

            .category-counts-table th {
                background-color: #275B42;
                color: #fff !important;
                text-align: center;
                font-family: 'Montserrat', sans-serif;
            }

            /* .category-counts-table tr:nth-child(even) {
                background-color: #e9ecef;
            } */
            .container-main {
                background-color: white;
                padding: 20px;
                border-radius: 14px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                margin-top: 20px;
            }

            .child-row {
                display: flex;
                flex-direction: row;
            }

            .button-primary {
                background-color: #275B42;
                border-color: #275B42;
                box-shadow: none;
                text-shadow: none;
            }

            .button-primary:hover {
                background-color: #1e4734;
                border-color: #1e4734;
            }
        </style>
    <?php
    }

    /**
     * Display the page to create a new table.
     */
    public static function display_create_table_page()
    {
        error_log('Displaying create table page');
    ?>
        <div class="wrap">
            <h1>Create New Table</h1>
            <form id="create-table-form" method="post">
                <input type="text" name="table_name" id="table_name" placeholder="Table Name">
                <input type="submit" name="create_table" value="Create Table">
            </form>
            <?php self::handle_create_table(); ?>
        </div>
    <?php
    }

    /**
     * Display the page to upload a CSV file.
     */
    public static function display_upload_csv_page()
    {
    ?>
        <div class="wrap upload-csv-dashboard">
            <h1 class="page-title-admin">Upload CSV</h1>
            <p class="page-description">The Upload CSV page allows you to seamlessly import use case data into your system. Follow the steps below to upload a CSV file, map the CSV columns to database fields, and save the data efficiently. This feature helps you quickly bulk upload and manage use cases, ensuring data consistency and integrity.</p>

            <form id="csv-upload-form" method="post" enctype="multipart/form-data">
                <input type="file" name="csv_file" id="csv_file" accept=".csv" />
                <button type="button" id="upload-csv" class="button button-primary">Upload CSV</button>
            </form>

            <div id="csv-column-mapping" style="display:none; margin-top: 20px;">
                <h3 class="section-title">Map CSV Columns to Database Columns</h3>
                <p class="section-description">Map each column in your CSV file to the corresponding database column. This ensures the data is correctly organized and stored in the right fields.</p>
                <table class="wp-list-table widefat fixed striped mapping-table">
                    <thead>
                        <tr>
                            <th>Database Column</th>
                            <th>CSV Column</th>
                        </tr>
                    </thead>
                    <tbody id="mapping-table-body">
                        <!-- Mapping rows will be dynamically added here -->
                    </tbody>
                </table>
                <button id="save-mapping" class="button button-primary" style="margin-top: 10px;">Save Mapping</button>
            </div>

            <div id="duplicate-rows" style="display:none; margin-top: 20px;">
                <h3 class="section-title">Duplicate Rows</h3>
                <p class="section-description">The following rows are identified as duplicates. Please confirm if these rows should be processed or ignored.</p>
                <table class="wp-list-table widefat fixed striped duplicate-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="duplicate-rows-body">
                        <!-- Duplicate rows will be dynamically added here -->
                    </tbody>
                </table>
            </div>

            <div id="update-rows" style="display:none; margin-top: 20px;">
                <h3 class="section-title">Updated Rows</h3>
                <p class="section-description">The following rows have been updated. Please confirm if these updates should be applied to the database.</p>
                <table class="wp-list-table widefat fixed striped update-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="update-rows-body">
                        <!-- Updated rows will be dynamically added here -->
                    </tbody>
                </table>
            </div>

            <div id="new-rows" style="display:none; margin-top: 20px;">
                <h3 class="section-title">New Rows</h3>
                <p class="section-description">The following rows are new entries. Please confirm if these rows should be added to the database.</p>
                <table class="wp-list-table widefat fixed striped new-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="new-rows-body">
                        <!-- New rows will be dynamically added here -->
                    </tbody>
                </table>
            </div>

            <div class="confirmation-section">
                <p>Are you sure you want to save these data in the database?</p>
                <div class="labl">
                    <label><input type="checkbox" id="confirm-duplicates"> Confirm Duplicate Rows</label>
                    <label><input type="checkbox" id="confirm-updates"> Confirm Updated Rows</label>
                    <label><input type="checkbox" id="confirm-new"> Confirm New Rows</label>
                </div>
                <div class="finalbuttons"><button id="save-changes" class="button button-primary" style="margin-top: 20px;">Save Changes</button>
                    <button id="clear-changes" class="button" style="margin-top: 20px;">Clear</button>
                </div>
            </div>
        </div>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Roboto:wght@400;500&display=swap');

            .wrap.upload-csv-dashboard {
                background-color: #f4f6f9;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                font-family: 'Roboto', sans-serif;
            }

            .page-title-admin {
                font-size: 36px !important;
                color: #275B42;
                margin-bottom: 10px;
                font-weight: 700 !important;
                font-family: 'Montserrat', sans-serif;
                text-align: left;
            }

            .page-description,
            .section-description {
                font-size: 16px;
                color: #000000;
                margin: 10px 20px 30px 20px !important;
                font-family: 'Roboto', sans-serif;
                text-align: justify;
            }

            .section-title {
                font-size: 20px;
                color: #275B42;
                margin-bottom: 10px;
                font-family: 'Montserrat', sans-serif;
            }

            .mapping-table,
            .duplicate-table,
            .update-table,
            .new-table {
                margin-top: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                font-size: 18px;
                text-align: center;
            }

            .mapping-table {
                width: 50%;
                align-self: center;
            }

            .mapping-table th,
            .duplicate-table th,
            .update-table th,
            .new-table th {
                background-color: #275B42;
                color: #fff !important;
                font-family: 'Montserrat', sans-serif;
                padding: 10px;
            }

            .mapping-table td,
            .duplicate-table td,
            .update-table td,
            .new-table td {
                padding: 10px;
                font-weight: 500;
                text-align: center;
                font-size: 16px;
            }

            .mapping-table tr:nth-child(even),
            .duplicate-table tr:nth-child(even),
            .update-table tr:nth-child(even),
            .new-table tr:nth-child(even) {
                background-color: #e9ecef;
            }

            .button-primary {
                background-color: #275B42;
                border-color: #275B42;
                box-shadow: none;
                text-shadow: none;
            }

            .button-primary:hover {
                background-color: #1e4734;
                border-color: #1e4734;
            }

            .confirmation-section {
                margin-top: 20px;
                display: flex;
                flex-direction: column;
                gap: 20px;
                font-size: 18px;
                align-items: center;
            }

            .confirmation-section p {
                font-size: 18px;
            }

            .finalbuttons {
                display: flex;
                gap: 20px;
            }

            .labl {
                gap: 16px;
                display: flex;
                flex-direction: column;
                justify-content: flex-start
            }

            #upload-csv,
            #save-changes,
            #save-mapping {
                background-color: #275B42 !important;
                font-weight: 700;
                color: #fff;
                padding: 5px 45px 5px 45px;
                border-radius: 8px;
                font-size: 16px;
            }

            #clear-changes {
                background-color: transparent;
                font-weight: 700;
                border: 1px solid #275B42 !important;
                color: #275B42;
                padding: 5px 45px 5px 45px;
                border-radius: 8px;
                font-size: 16px;
            }

            #csv-upload-form {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #csv-column-mapping {
                margin-top: 20px;
                display: flex;
                flex-direction: column;
            }
        </style>

    <?php
    }

    /**
     * Display the page to view data in the custom table.
     */
    public static function display_view_data_page()
    {
        error_log('Displaying view data page');

        // Retrieve columns of the use cases table
        $columns = Table_Handler::get_table_columns('use_cases');
    ?>
        <div class="wrap use-cases-table">
            <h1 class="page-title-admin">Use Cases Table</h1>
            <p class="page-description">Manage your use cases easily with the Use Cases Table. You can view, edit, and delete use case entries, as well as add new use cases manually.</p>

            <form id="bulk-delete-form" method="post">
                <table class="wp-list-table widefat fixed striped use-cases-data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <?php foreach ($columns as $column) { ?>
                                <?php if (!in_array($column, [])) { ?>
                                    <th><?php echo esc_html(ucwords(str_replace('_', ' ', $column))); ?></th>
                                <?php } ?>
                            <?php } ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Retrieve all data from the use cases table
                        $data = Table_Handler::get_data('use_cases');
                        if (!empty($data)) {
                            foreach ($data as $row) {
                                echo '<tr>';
                                echo '<td><input type="checkbox" name="selected_ids[]" value="' . esc_html($row->id) . '"></td>';
                                foreach ($columns as $column) {
                                    if ($column === 'use_case_thumbnail_url' || $column === 'use_case_image_url') {
                                        echo '<td><img src="' . esc_html($row->$column) . '" alt="' . esc_html($column) . '" style="width:50px;height:50px;"></td>';
                                    } elseif (!in_array($column, [])) {
                                        echo '<td>' . esc_html($row->$column) . '</td>';
                                    }
                                }
                                echo '<td><a href="#" class="button edit-button">Edit</a> <a href="#" class="button delete-button" data-id="' . esc_html($row->id) . '">Delete</a></td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="' . (count($columns) + 2) . '">No data found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <button type="submit" name="bulk_delete" class="button bulk-delete-button">Bulk Delete</button>
            </form>

            <div class="add-use-case-section">
                <h2 class="section-title">Add New Use Case</h2>
                <p class="section-description">Use the form below to manually add new use cases. Fill out the necessary fields and click "Add Use Case" to save the entry to the database.</p>
                <form method="post" class="add-use-case-form">
                    <?php foreach ($columns as $column) { ?>
                        <?php if (!in_array($column, ['id', 'use_case_post_id', 'use_case_url'])) { ?>
                            <div class="form-field">
                                <label for="<?php echo esc_attr($column); ?>"><?php echo esc_html(ucwords(str_replace('_', ' ', $column))); ?></label>
                                <input type="text" name="<?php echo esc_attr($column); ?>" placeholder="<?php echo esc_attr(ucwords(str_replace('_', ' ', $column))); ?>">
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <input type="submit" name="add_use_case" class="button button-primary" value="Add Use Case">
                </form>
            </div>

            <?php
            if (isset($_POST['add_use_case'])) {
                $data = array();
                foreach ($columns as $column) {
                    $data[$column] = sanitize_text_field($_POST[$column]);
                }
                Table_Handler::insert_data('use_cases', $data);
                echo '<div class="notice notice-success is-dismissible"><p>Use case added successfully.</p></div>';
            }
            self::handle_bulk_delete();
            ?>
        </div>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Roboto:wght@400;500&display=swap');

            .wrap.use-cases-table {
                background-color: #f4f6f9;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                font-family: 'Roboto', sans-serif;
            }

            .page-title-admin {
                font-size: 36px !important;
                color: #275B42;
                margin-bottom: 10px;
                font-weight: 700 !important;
                font-family: 'Montserrat', sans-serif;
                text-align: center;
            }

            .page-description {
                font-size: 18px;
                color: #000000;
                margin: 20px 20px 30px 20px !important;
                text-align: center;
            }

            .use-cases-data-table {
                width: 100%;
                border-collapse: collapse;
            }

            .use-cases-data-table th,
            .use-cases-data-table td {
                padding: 12px;
                text-align: left;
                font-size: 14px;
                font-weight: 500;
                text-align: center;
            }

            .use-cases-data-table th {
                background-color: #275B42;
                color: #fff !important;
                text-align: center;
                font-family: 'Montserrat', sans-serif;
            }

            .edit-button,
            .delete-button {
                background-color: #275B42;
                border-color: #275B42;
                color: #fff;
                text-decoration: none;
                text-shadow: none;
            }

            .edit-button:hover,
            .delete-button:hover {
                background-color: #1e4734;
                border-color: #1e4734;
            }

            .bulk-delete-button {
                background-color: #d9534f;
                border-color: #d43f3a;
                color: #fff;
                margin-top: 20px;
                display: block;
                width: 100%;
                text-align: center;
            }

            .bulk-delete-button:hover {
                background-color: #c9302c;
                border-color: #ac2925;
            }

            .add-use-case-section {
                margin-top: 30px;
                padding: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 1.5em;
                color: #275B42;
                margin-bottom: 10px;
                font-family: 'Montserrat', sans-serif;
            }

            .section-description {
                font-size: 18px;
                color: #777;
                margin-bottom: 20px;
            }

            .add-use-case-form {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
            }

            .add-use-case-form .form-field {
                flex: 1 1 calc(33.333% - 20px);
                display: flex;
                flex-direction: column;
            }

            .add-use-case-form .form-field label {
                font-weight: 500;
                margin-bottom: 5px;
            }

            .add-use-case-form .form-field input {
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 16px;
            }

            .add-use-case-form .button-primary {
                background-color: #275B42;
                border-color: #275B42;
                box-shadow: none;
                text-shadow: none;
                margin-top: 20px;
                align-self: flex-end;
            }

            .add-use-case-form .button-primary:hover {
                background-color: #1e4734;
                border-color: #1e4734;
            }
        </style>


    <?php
    }

    /**
     * Display the page to update the structure of the custom table.
     */
    public static function display_update_table_structure_page()
    {
        error_log('Displaying update table structure page');

        // Retrieve current structure of the use cases table
        $current_structure = Table_Handler::get_table_structure('use_cases');
    ?>
        <div class="wrap">
            <h1>Update Table Structure</h1>
            <h2>Current Structure</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Column Name</th>
                        <th>Column Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_structure as $column) { ?>
                        <tr>
                            <td><?php echo esc_html($column->Field); ?></td>
                            <td><?php echo esc_html($column->Type); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <h2>Add New Column</h2>
            <form id="update-table-structure-form" method="post">
                <input type="text" name="column_name" placeholder="New Column Name">
                <select name="column_type">
                    <option value="TEXT">Text</option>
                    <option value="VARCHAR(255)">Varchar</option>
                    <option value="INT">Integer</option>
                    <!-- Add more types as needed -->
                </select>
                <input type="submit" name="add_column" value="Add Column" class="button button-primary">
            </form>
            <?php self::handle_update_table_structure(); ?>
        </div>
    <?php
    }

    /**
     * Handle creating a new table.
     */
    public static function handle_create_table()
    {
        if (isset($_POST['create_table'])) {
            error_log('Creating new table');
            $table_name = sanitize_text_field($_POST['table_name']);
            if (!empty($table_name)) {
                Table_Handler::create_table($table_name);
                if (Table_Handler::check_table_exists($table_name)) {
                    echo '<div class="notice notice-success is-dismissible"><p>Table Created Successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to create table.</p></div>';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Please enter a valid table name.</p></div>';
            }
        }
    }

    /**
     * Handle bulk deletion of rows from the custom table.
     */
    public static function handle_bulk_delete()
    {
        if (isset($_POST['bulk_delete'])) {
            error_log('Handling bulk delete');
            $selected_ids = isset($_POST['selected_ids']) ? $_POST['selected_ids'] : array();
            foreach ($selected_ids as $id) {
                Table_Handler::delete_data('use_cases', $id);
            }
            echo '<div class="notice notice-success is-dismissible"><p>Selected rows deleted successfully.</p></div>';
        }
    }



    /**
     * Handle updating the structure of the custom table.
     */
    public static function handle_update_table_structure()
    {
        if (isset($_POST['add_column'])) {
            error_log('Adding new column');
            $column_name = sanitize_text_field($_POST['column_name']);
            $column_type = sanitize_text_field($_POST['column_type']);
            if (!empty($column_name) && !empty($column_type)) {
                Table_Handler::add_column('use_cases', $column_name, $column_type);
                echo '<div class="notice notice-success is-dismissible"><p>Column added successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Please enter valid column details.</p></div>';
            }
        }
    }

    /**
     * Display the template settings page.
     */
    public static function display_template_settings_page()
    {
        // Template settings page code removed as per new requirements
    }

    /**
     * Display the logs page.
     */
    public static function display_logs_page()
    {
        // Get all log entries
        $logs = Table_Handler::get_logs();
    ?>
        <div class="wrap use-cases-logs">
            <h1 class="page-title-admin">Use Case Logs</h1>
            <p class="logs-description">The Use Case Logs page provides a comprehensive view of all actions performed within the Use Cases Management System plugin. This includes detailed records of each use case processed, ensuring transparency and accountability. You can review the actions taken, view specific details, and see the exact timestamp of when each action occurred. This is essential for tracking the history of use case management and understanding how data has been manipulated over time.</p>
            <p class="logs-description">Whether you're monitoring the creation of new use cases, updates to existing entries, or any other actions performed by users, the logs offer a complete audit trail. This is especially useful for administrators who need to maintain data integrity and security within the WordPress environment. Each log entry is meticulously recorded with precise details to help you manage your use cases efficiently.</p>
            <table class="wp-list-table widefat fixed striped logs-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)) {
                        foreach ($logs as $log) { ?>
                            <tr>
                                <td><?php echo esc_html($log->action); ?></td>
                                <td><?php echo esc_html($log->details); ?></td>
                                <td><?php echo esc_html($log->created_at); ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="3">No log entries found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Roboto:wght@400;500&display=swap');

            .wrap.use-cases-logs {
                background-color: #f4f6f9;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                font-family: 'Roboto', sans-serif;
            }

            .page-title-admin {
                font-size: 36px !important;
                color: #275B42;
                margin-bottom: 10px;
                font-weight: 700 !important;
                font-family: 'Montserrat', sans-serif;
                text-align: left;
            }

            .logs-description {
                font-size: 16px;
                color: #000000;
                margin: 10px 20px 30px 20px !important;
                font-family: 'Roboto', sans-serif;
                text-align: justify;
            }

            .logs-table {
                margin-top: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                font-size: 18px;
                text-align: left;
            }

            .logs-table th {
                background-color: #275B42;
                color: #fff !important;
                font-family: 'Montserrat', sans-serif;
                padding: 10px;
            }

            .logs-table td {
                padding: 10px;
                font-weight: 500;
                text-align: left;
            }

            .logs-table tr:nth-child(even) {
                background-color: #e9ecef;
            }
        </style>
<?php
    }
}
