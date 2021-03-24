<?php

/**
 *
 * liveSite - Enterprise Website Platform
 *
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

include('init.php');
$user = validate_user();
validate_forms_access($user);

include_once('liveform.class.php');
$liveform = new liveform('import_submitted_forms');

if (!$_POST) {
    // get custom forms for pick list
    $custom_form_options = array();
    $custom_form_options['-Select-'] = '';
    
    $query =
        "SELECT
           page.page_id,
           page.page_name,
           page.page_folder,
           custom_form_pages.form_name
        FROM page
        LEFT JOIN custom_form_pages ON page.page_id = custom_form_pages.page_id
        WHERE page.page_type = 'custom form'
        ORDER BY custom_form_pages.form_name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        $page_id = $row['page_id'];
        $page_name = $row['page_name'];
        $folder_id = $row['page_folder'];
        $form_name = $row['form_name'];
        
        // if user has access to this custom form, add custom form to pick list
        if (check_edit_access($folder_id) == true) {
            if ($form_name) {
                $name = $form_name;
            } else {
                $name = $page_name;
            }
            
            $custom_form_options[$name] = $page_id;
        }
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new submitted forms]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Import Submitted Forms</h1>
            <div class="subheading">Upload submitted form data for any existing custom form.</div>
            <form enctype="multipart/form-data" action="import_submitted_forms.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Select Formatted Text File to Upload</h2></th>
                    </tr>
                    <tr>
                        <td>CSV File:</td>
                        <td>' . $liveform->output_field(array('type'=>'file', 'name'=>'file', 'size'=>'60')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Select the Custom Form</h2></th>
                    </tr>
                    <tr>
                        <td>Custom Form:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'custom_form_page_id', 'options'=>$custom_form_options)) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_import" value="Import" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->unmark_errors();
    $liveform->clear_notices();

} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('custom_form_page_id', 'Custom Form is required.');
    
    // if no CSV file was uploaded, prepare error
    if (!$_FILES['file']['name']) {
        $liveform->mark_error('file', 'CSV File is required.');
    }
    
    $custom_form_page_id = $liveform->get_field_value('custom_form_page_id');
    
    // if a custom form was selected, validate user's access to custom form
    if ($custom_form_page_id) {
        // get folder id that custom form is in, in order to validate user's access
        $query = "SELECT page_folder FROM page WHERE page_id = '" . escape($custom_form_page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $folder_id = $row['page_folder'];
        
        // if user does not have access to custom form, prepare error
        if (check_edit_access($folder_id) == false) {
            log_activity("access denied to import submitted forms because user does not have access to modify folder that custom form is in", $_SESSION['sessionusername']);
            $liveform->mark_error('custom_form_page_id', 'You do not have access to import submitted forms for that custom form.  Please choose a different custom form.');
        }
    }
    
    // if an error does not exist
    if ($liveform->check_form_errors() == false) {
        // Fix Mac line-ending issue.
        ini_set('auto_detect_line_endings', true);
        
        // get file handle for uploaded CSV file
        $handle = fopen($_FILES['file']['tmp_name'], "r");
        
        // get column names from first row of CSV file
        $columns = fgetcsv($handle, 100000, ",");
        
        $fields = array();
        
        // loop through all column field names in order to determine which columns have valid field names
        foreach ($columns as $key => $field_name) {
            // get field id from field name if a field exists
            $query =
                "SELECT
                    id,
                    name,
                    type,
                    wysiwyg,
                    multiple
                FROM form_fields
                WHERE
                    (page_id = '" . escape($custom_form_page_id) . "')
                    AND (name = '" . escape($field_name) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if a field was found, then add to fields array
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                
                $field_id = $row['id'];
                
                $fields[] =
                    array(
                        'id' => $field_id,
                        'name' => $row['name'],
                        'type' => $row['type'],
                        'wysiwyg' => $row['wysiwyg'],
                        'multiple' => $row['multiple'],
                        'key' => $key
                    );
            }
        }
        
        $imported_submitted_forms = 0;
        
        // if at least one valid field was found in CSV file
        if ($fields) {
            $pretty_urls = check_if_pretty_urls_are_enabled($custom_form_page_id);

            // loop through all rows of data in CSV file
            while ($row = fgetcsv($handle, 100000, ",")) {
                // insert submitted form
                $query =
                    "INSERT INTO forms (
                        page_id,
                        complete,
                        user_id,
                        reference_code,
                        submitted_timestamp,
                        last_modified_user_id,
                        last_modified_timestamp)
                    VALUES (
                        '" . escape($custom_form_page_id) . "',
                        '1',
                        '" . $user['id'] . "',
                        '" . generate_form_reference_code() . "',
                        UNIX_TIMESTAMP(),
                        '" . $user['id'] . "',
                        UNIX_TIMESTAMP())";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $submitted_form_id = mysqli_insert_id(db::$con);
                
                // loop through all fields in order to insert data for field
                foreach ($fields as $field) {
                    $key = $field['key'];
                    
                    $file_id = 0;
                    $data = '';
                    
                    // assume that data should not be added for this field until we find out otherwise
                    $add_data = FALSE;
                    
                    // if this is a file upload field then check if file exists and if user has access to file
                    if ($field['type'] == 'file upload') {
                        // check if file exists with the file name that exists in imported data
                        // it must not be a design file and must not be an attachment already
                        $query =
                            "SELECT
                               id,
                               folder AS folder_id
                            FROM files
                            WHERE
                                (name = '" . escape(trim($row[$key])) . "')
                                AND (design = '0')
                                AND (attachment = '0')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // if a file exists with the same name then continue to check if user has edit access to file
                        if (mysqli_num_rows($result) > 0) {
                            $file = mysqli_fetch_assoc($result);
                            
                            // if the user has edit access to the folder that the file is in, then remember to add data and mark file as an attachment
                            if (check_edit_access($file['folder_id']) == true) {
                                $add_data = TRUE;
                                $file_id = $file['id'];
                                $data = '';
                                
                                // mark file as an attachment
                                $query = "UPDATE files SET attachment = '1' WHERE id = '" . $file_id . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }
                        }
                        
                    // else this is not a file upload field, so add data
                    } else {
                        $add_data = TRUE;
                        $data = $row[$key];
                    }
                    
                    // if data should be added for this field, then add it
                    if ($add_data == TRUE) {
                        // assume that the form data type is standard until we find out otherwise
                        $form_data_type = 'standard';
                        
                        // if the form field's type is date, date and time, or time, then set form data type to the form field type
                        if (
                            ($field['type'] == 'date')
                            || ($field['type'] == 'date and time')
                            || ($field['type'] == 'time')
                        ) {
                            $form_data_type = $field['type'];
                            
                        // else if the form field is a wysiwyg text area, then set type to html
                        } elseif (($field['type'] == 'text area') && ($field['wysiwyg'] == 1)) {
                            $form_data_type = 'html';
                        }

                        // If this field is a pick list and allow multiple selection is enabled,
                        // or if this field is a check box, and a separator exists in the data,
                        // then this data contains multiple values, so add multiple data records.
                        if (
                            (
                                (
                                    ($field['type'] == 'pick list')
                                    && ($field['multiple'] == 1)
                                )
                                || ($field['type'] == 'check box')
                            )
                            && (mb_strpos($data, ',') !== false)
                        ) {
                            $data_values = explode(',', $data);
                            
                            // Loop through all data values for this field in order to add database record for each one.
                            foreach ($data_values as $data_value) {
                                $query =
                                    "INSERT INTO form_data (
                                        form_id,
                                        form_field_id,
                                        file_id,
                                        data,
                                        name,
                                        type)
                                    VALUES (
                                        '" . $submitted_form_id . "',
                                        '" . $field['id'] . "',
                                        '" . $file_id . "',
                                        '" . escape(trim($data_value)) . "',
                                        '" . escape($field['name']) . "',
                                        '$form_data_type')";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }

                        // Otherwise the data has only one value so add a single record in the database for it.
                        } else {
                            $query =
                                "INSERT INTO form_data (
                                    form_id,
                                    form_field_id,
                                    file_id,
                                    data,
                                    name,
                                    type)
                                VALUES (
                                    '" . $submitted_form_id . "',
                                    '" . $field['id'] . "',
                                    '" . $file_id . "',
                                    '" . escape($data) . "',
                                    '" . escape($field['name']) . "',
                                    '$form_data_type')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                    }
                }

                // If pretty URLs are enabled, then update address name.
                if ($pretty_urls == true) {
                    update_submitted_form_address_name($submitted_form_id);
                }
                
                $imported_submitted_forms++;
            }
            
        // else there was not at least one valid field found, so prepare error
        } else {
            $liveform->mark_error('file', 'A valid field name could not be found.  Please make sure there is at least one valid field name in the first row of the CSV file.');
            
            fclose($handle);
            
            // send user back to import submitted forms screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/import_submitted_forms.php');
            exit();
        }
        
        // if no submitted forms were imported, because there were no rows, prepare error
        if ($imported_submitted_forms == 0) {
            $liveform->mark_error('file', 'No submitted forms were imported, because there were no rows of data in the CSV file.');
            
            fclose($handle);
            
            // send user back to import submitted forms screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/import_submitted_forms.php');
            exit();
        }
        
        fclose($handle);
        
        $liveform->remove_form('import_submitted_forms');
        
        log_activity("$imported_submitted_forms submitted form(s) were imported", $_SESSION['sessionusername']);
        
        $liveform->add_notice("$imported_submitted_forms submitted form(s) have been imported.");
    }
    
    // send user back to import submitted forms screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/import_submitted_forms.php');
}
?>