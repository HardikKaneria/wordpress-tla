jQuery(document).ready(function ($) {
    var nonce = myCustomPlugin.nonce;

    /**
     * Handle the CSV upload process.
     */
    $('#upload-csv').on('click', function () {
        var fileInput = $('#csv_file')[0];
        if (fileInput.files.length === 0) {
            alert('Please select a file to upload.');
            return;
        }

        var formData = new FormData();
        formData.append('csv_file', fileInput.files[0]);
        formData.append('_wpnonce', nonce);

        $.ajax({
            url: myCustomPlugin.apiUrl + '/upload-csv',
            method: 'POST',
            headers: {
                'X-WP-Nonce': myCustomPlugin.nonce
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $('#csv-column-mapping').show();
                    processCSV(fileInput.files[0]);
                } else {
                    alert('Failed to upload CSV.');
                }
            },
            error: function (error) {
                console.error('Error uploading CSV:', error);
                alert('Error uploading CSV: ' + error.responseText);
            }
        });
    });

    /**
     * Process the uploaded CSV file and generate the column mapping interface.
     * 
     * @param {File} file The uploaded CSV file.
     */
    function processCSV(file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var csvData = e.target.result;
            var rows = csvData.split('\n');
            var headers = rows[0].split(',');

            $.ajax({
                url: myCustomPlugin.apiUrl + '/get-database-columns',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': nonce
                },
                success: function (response) {
                    var dbColumns = response.columns;
                    var html = '';
                    dbColumns.forEach(function (dbColumn) {
                        if (!['id', 'use_case_slug', 'use_case_url', 'use_case_post_id'].includes(dbColumn)) { // Exclude auto-filled columns
                            html += '<tr>';
                            html += '<td>' + dbColumn + '</td>';
                            html += '<td>';
                            html += '<select class="csv-column-select">';
                            headers.forEach(function (header) {
                                html += '<option value="' + header.trim() + '">' + header.trim() + '</option>';
                            });
                            html += '</select>';
                            html += '</td>';
                            html += '</tr>';
                        }
                    });
                    $('#mapping-table-body').html(html);
                },
                error: function (error) {
                    console.error('Error fetching database columns:', error);
                }
            });
        };
        reader.readAsText(file);
    }

    /**
     * Save the column mapping and display the rows categorized as duplicates, updates, or new.
     */
    $('#save-mapping').on('click', function () {
        var mapping = {};
        $('#mapping-table-body tr').each(function () {
            var dbColumn = $(this).find('td:first').text();
            var csvColumn = $(this).find('select.csv-column-select').val();
            mapping[csvColumn] = dbColumn;
        });

        $.ajax({
            url: myCustomPlugin.apiUrl + '/save-mapping',
            method: 'POST',
            headers: {
                'X-WP-Nonce': nonce
            },
            contentType: 'application/json',
            data: JSON.stringify({
                mapping: mapping
            }),
            success: function (response) {
                displayRows(response);
            },
            error: function (error) {
                console.error('Error saving mapping:', error);
            }
        });
    });

    /**
     * Display the categorized rows (duplicates, updates, new) based on the saved mapping.
     * 
     * @param {Object} data The categorized rows data.
     */
    function displayRows(data) {
        var duplicateRows = data.duplicate_rows;
        var updatedRows = data.updated_rows;
        var newRows = data.new_rows;

        var duplicateHtml = '';
        var updatedHtml = '';
        var newHtml = '';

        if (duplicateRows.length > 0) {
            duplicateRows.forEach(function (row) {
                duplicateHtml += '<tr>';
                Object.keys(row).forEach(function (key) {
                    duplicateHtml += '<td data-key="' + key + '">' + row[key] + '</td>';
                });
                duplicateHtml += '</tr>';
            });
            $('#duplicate-rows-body').html(duplicateHtml);
            $('#duplicate-rows').show();
        }

        if (updatedRows.length > 0) {
            updatedRows.forEach(function (row) {
                updatedHtml += '<tr>';
                Object.keys(row).forEach(function (key) {
                    updatedHtml += '<td data-key="' + key + '">' + row[key] + '</td>';
                });
                updatedHtml += '</tr>';
            });
            $('#update-rows-body').html(updatedHtml);
            $('#update-rows').show();
        }

        if (newRows.length > 0) {
            newRows.forEach(function (row) {
                newHtml += '<tr>';
                Object.keys(row).forEach(function (key) {
                    newHtml += '<td data-key="' + key + '">' + row[key] + '</td>';
                });
                newHtml += '</tr>';
            });
            $('#new-rows-body').html(newHtml);
            $('#new-rows').show();
        }

        $('#save-changes').off('click').on('click', function () { // Use off() to unbind previous click handlers
            if ($('#confirm-duplicates').is(':checked') && $('#confirm-updates').is(':checked') && $('#confirm-new').is(':checked')) {
                ['duplicate_rows', 'updated_rows', 'new_rows'].forEach(function (type) {
                    confirmChanges(type);
                });
            } else {
                alert('Please confirm all changes before saving.');
            }
        });

        $('#clear-changes').off('click').on('click', function () { // Use off() to unbind previous click handlers
            location.reload();
        });
    }

    /**
     * Confirm and save the changes (duplicates, updates, new) to the database.
     * 
     * @param {string} type The type of changes to confirm.
     */
    function confirmChanges(type) {
        var rows = [];
        switch (type) {
            case 'duplicate_rows':
                // No action needed for duplicates as per the new requirement.
                break;
            case 'updated_rows':
                $('#update-rows-body tr').each(function () {
                    var row = {};
                    $(this).find('td').each(function () {
                        row[$(this).data('key')] = $(this).text();
                    });
                    rows.push(row);
                });
                break;
            case 'new_rows':
                $('#new-rows-body tr').each(function () {
                    var row = {};
                    $(this).find('td').each(function () {
                        row[$(this).data('key')] = $(this).text();
                    });
                    rows.push(row);
                });
                break;
        }

        $.ajax({
            url: myCustomPlugin.apiUrl + '/save-changes',
            method: 'POST',
            headers: {
                'X-WP-Nonce': nonce
            },
            contentType: 'application/json',
            data: JSON.stringify({
                type: type,
                rows: rows
            }),
            success: function (response) {
                console.log('Changes saved successfully');
                if (type === 'new_rows') { // Reload the page after saving new rows
                    location.reload();
                }
            },
            error: function (error) {
                console.error('Error saving changes:', error);
                alert('Error saving changes: ' + error.responseText);
            }
        });
    }

    /**
     * Handle single row deletion via AJAX.
     */
    $(document).on('click', '.delete-single', function (e) {
        e.preventDefault();
        var rowId = $(this).data('id');
        if (confirm('Are you sure you want to delete this row?')) {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                headers: {
                    'X-WP-Nonce': nonce
                },
                data: {
                    action: 'delete_single_row',
                    row_id: rowId
                },
                success: function (response) {
                    if (response.success) {
                        alert('Row deleted successfully.');
                        location.reload();
                    } else {
                        alert('Failed to delete row.');
                    }
                }
            });
        }
    });

    /**
     * Handle select all checkboxes functionality.
     */
    $('#select-all').on('click', function () {
        $('input[type="checkbox"]').prop('checked', this.checked);
    });

    /**
     * Handle refreshing use cases by calling the REST API endpoint.
     */
    $('#refresh-use-cases').on('click', function () {
        $.ajax({
            url: myCustomPlugin.apiUrl + '/refresh-use-cases',
            method: 'POST',
            headers: {
                'X-WP-Nonce': myCustomPlugin.nonce
            },
            success: function (response) {
                if (response.success) {
                    alert('Use case posts refreshed successfully.');
                    location.reload();
                } else {
                    alert('Failed to refresh use case posts.');
                }
            },
            error: function (error) {
                console.error('Error refreshing use case posts:', error);
                alert('Error refreshing use case posts: ' + error.responseText);
            }
        });
    });
});
