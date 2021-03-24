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

validate_token_field();

include_once('liveform.class.php');
$liveform = new liveform('view_submitted_forms');

// if at least one form was selected
if ($_POST['forms']) {
    $number_of_forms = 0;

    // loop through all forms that were selected for deletion
    foreach ($_POST['forms'] as $form_id) {
        // get folder id so we can validate access to this form
        $query = "SELECT page.page_folder as folder_id
                 FROM forms
                 LEFT JOIN page ON forms.page_id = page.page_id
                 WHERE forms.id = '" . escape($form_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        // if user has access to edit this form's custom form then user has access to delete form
        if (check_edit_access($row['folder_id']) == true) {
            // get uploaded files for this form, so they can be deleted
            $query = "SELECT
                        files.id,
                        files.name
                     FROM form_data
                     LEFT JOIN files ON form_data.file_id = files.id
                     WHERE (form_data.form_id = '" . escape($form_id) . "') AND (form_data.file_id > 0)";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $files = array();
            
            while($row = mysqli_fetch_assoc($result)) {
                $files[] = $row;
            }
            
            // loop through all files so they can be deleted
            foreach ($files as $file) {
                // if file still exists, delete file
                if ($file['id']) {
                    // delete file record
                    $query = "DELETE FROM files WHERE id = '" . $file['id'] . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete file
                    @unlink(FILE_DIRECTORY_PATH . '/' . $file['name']);
                }
            }
            
            // delete form
            $query = "DELETE FROM forms WHERE id = '" . escape($form_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete form data
            $query = "DELETE FROM form_data WHERE (form_id = '" . escape($form_id) . "') AND (form_id != '0')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete views for this submitted form that the form view directory feature uses
            $query = "DELETE FROM submitted_form_views WHERE submitted_form_id = '" . escape($form_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $number_of_forms++;
        }
    }
    
    // if more than 0 forms were deleted, then log activity
    if ($number_of_forms > 0) {
        log_activity("$number_of_forms submitted form(s) were deleted", $_SESSION['sessionusername']);
        $liveform->add_notice("$number_of_forms form(s) have been deleted.");
    }
    
// else no forms were selected
} else {
    $liveform->mark_error('no_forms_selected_error', 'No forms were deleted because none were selected.');
}

// forward user to view forms screen
header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_submitted_forms.php');
?>