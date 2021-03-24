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

validate_token_field();

// if there is a page_id supplied in the query string, this is a page form
if ((isset($_POST['page_id'])) && ($_POST['page_id'] != '')) {
    validate_area_access($user, 'user');
    
    // get page info
    $query =
        "SELECT
            page_type,
            page_folder,
            page_name
        FROM page
        WHERE page_id = '" . escape($_POST['page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $page_type = $row['page_type'];
    $folder_id = $row['page_folder'];
    $page_name = $row['page_name'];
    
    $form_type = '';
    
    // get the form type by looking at the page type
    switch ($page_type) {
        case 'custom form':
            $form_type = 'custom';
            break;

        // Express order can have a shipping and/or billing form, so check query string for the type
        // of form that we are dealing with
        case 'express order':

            if ($_REQUEST['form_type'] == 'shipping') {
                $form_type = 'shipping';
            } else {
                $form_type = 'billing';
            }

            break;

        case 'shipping address and arrival':
            $form_type = 'shipping';
            break;

        case 'billing information':
            $form_type = 'billing';
            break;
    }

    // Get the form type name that we will output to user

    $form_type_name = '';

    switch ($form_type) {
        case 'custom':
            $form_type_name = 'custom form';
            break;

        case 'shipping':
            $form_type_name = 'custom shipping form';
            break;

        case 'billing':
            $form_type_name = 'custom billing form';
            break;
    }
    
    $form_type_identifier_id = 'page_id';
    
    // validate user's access
    if (check_edit_access($folder_id) == false) {
        log_activity('access denied to delete fields from ' . $form_type_name . ' because user does not have access to modify folder that ' . $form_type_name . ' is in', $_SESSION['sessionusername']);
        output_error('Access denied.');
    }

    $form_name = '';

    // If this is a page and form type that supports a form name, then get it
    if ($page_type != 'express order' or $form_type != 'shipping') {
    
        // get form name for page
        $query = "SELECT form_name FROM " . str_replace(' ', '_', $page_type) . "_pages WHERE page_id = '" . escape($_POST['page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $form_name = $row['form_name'];
    }

    // if form name is blank, use page name for form name
    if (!$form_name) {
        $form_name = $page_name;
    }
}

// if there is a product_id supplied in the query string, this is a product form
if ((isset($_POST['product_id'])) && ($_POST['product_id'] != '')) {

    validate_ecommerce_access($user);
    
    $form_type = 'product';
    $form_type_name = 'product form';
    $form_type_identifier_id = 'product_id';
    
    // get product name, short description and form name to determine what we will use for the form name
    $query = "SELECT 
                 name,
                 short_description,
                 form_name
             FROM products
             WHERE id = '" . escape($_POST['product_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $product_name = $row['name'];
    $short_description = $row['short_description'];
    $form_name = $row['form_name'];
    
    // if form name is blank and short description is not, use short description for form name
    if (($form_name == '') && ($short_description != '')) {
        $form_name = $short_description;
        
    // else, if form name is blank and product name is not, use product name for form name
    } else if (($form_name == '') && ($product_name != '')) {
        $form_name = $product_name;
    }
}

$liveform = new liveform('view_fields');

// if at least one field was selected
if ($_POST['fields']) {
    $number_of_fields = 0;

    $access_error_exists = false;

    // loop through all fields that were selected for deletion
    foreach ($_POST['fields'] as $field_id) {
        // get page_id, product_id and page_folder for this field, in order to validate access
        $query = "SELECT 
                     form_fields.page_id,
                     form_fields.product_id,
                     page.page_folder
                 FROM form_fields
                 LEFT JOIN page ON form_fields.page_id = page.page_id
                 WHERE 
                     form_fields.id = '" . escape($field_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $page_id = $row['page_id'];
        $product_id = $row['product_id'];
        $folder_id = $row['page_folder'];
        
        // if there is a page id, folder id and the user has access to the folder, allow the user access to delete field
        if (
            ($page_id != '0')
            && ($folder_id != '')
            && (check_edit_access($folder_id) == true)
        ) {
            $access_granted = true;
        // else, if there is a product id and the user has eCommerce access, allow the user access to delete the field
        } else if (($product_id != '0') && (validate_ecommerce_access($user))) {
            $access_granted = true;
        } else {
            $access_granted = false;
            $access_error_exists = true;
        }
        
        // If the user has access to the field, the delete field.
        if ($access_granted == true) {

            // delete field
            $query = "DELETE FROM form_fields WHERE id = '" . escape($field_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete form field options for field
            $query = "DELETE FROM form_field_options WHERE form_field_id = '" . escape($field_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // Delete target options for this field.
            db("DELETE FROM target_options WHERE trigger_form_field_id = '" . escape($field_id) . "'");

            db("DELETE FROM product_submit_form_fields WHERE form_field_id = '" . escape($field_id) . "'");

            // If this is a custom form, then delete submitted form data for this,
            // field also, because for custom forms, we don't have a way to show
            // submitted form data without the custom form field.
            if ($form_type == 'custom') {

                // Get uploaded files for this field, in order to delete them.
                $files = db_items(
                    "SELECT
                        files.id,
                        files.name
                    FROM form_data
                    LEFT JOIN files ON form_data.file_id = files.id
                    WHERE
                        (form_data.form_field_id = '" . e($field_id) . "')
                        AND (form_data.file_id != 0)
                        AND (files.id IS NOT NULL)");

                // Loop through files in order to delete record in DB and on file system.
                foreach ($files as $file) {
                    db("DELETE FROM files WHERE id = '" . $file['id'] . "'");
                    @unlink(FILE_DIRECTORY_PATH . '/' . $file['name']);
                    log_activity('file (' . $file['name'] . ') from a submitted form was deleted because a custom form field was deleted');
                }
                
                // Delete all submitted form data for this field.
                db("DELETE FROM form_data WHERE form_field_id = '" . e($field_id) . "'");

            }
            
            $number_of_fields++;

        }
    }
    
    // if this is a product form then update last modified info for product and log activity
    if ($form_type == 'product') {
        // if more than 0 fields were deleted, then log activity
        if ($number_of_fields > 0) {
            // update last modified for product
            $query = "UPDATE products
                     SET
                        user = '" . $user['id'] . "',
                        timestamp = UNIX_TIMESTAMP()
                     WHERE id = '" . escape($_POST['product_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            log_activity("$number_of_fields field(s) were deleted from $form_type_name ($form_name)", $_SESSION['sessionusername']);
            $liveform->add_notice("$number_of_fields field(s) have been deleted.");
        }
        
    // else, this is a form for a page, so update last modified info for page and log activity
    } else {
        // if more than 0 fields were deleted, then log activity
        if ($number_of_fields > 0) {
            // update last modified for page
            $query = "UPDATE page
                     SET
                        page_timestamp = UNIX_TIMESTAMP(),
                        page_user = '" . $user['id'] . "'
                     WHERE page_id = '" . escape($_POST['page_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // If this page type supports a custom layout, then check if this page
            // has a modified custom layout in order to determine if we should show a warning.
            if (check_if_page_type_supports_layout($page_type)) {
                $page = db_item(
                    "SELECT
                        layout_type,
                        layout_modified
                    FROM page
                    WHERE page_id = '" . e($_POST['page_id']) . "'");

                if (($page['layout_type'] == 'custom') && $page['layout_modified']) {
                    $liveform->add_warning('You might need to edit the custom layout now, because you have made changes to fields on the custom form.');
                }
            }
            
            log_activity("$number_of_fields field(s) were deleted from page ($page_name), form ($form_name)", $_SESSION['sessionusername']);
            $liveform->add_notice("$number_of_fields field(s) have been deleted.");
        }
        
    }
    
    if ($access_error_exists == true) {
        $liveform->mark_error('access_error', 'At least one field could not be deleted, because you do not have access to the field(s).');
    }
    
// else no fields were selected
} else {
    $liveform->mark_error('no_fields_selected_error', 'No fields were deleted because none were selected.');
}

$url_form_type = '';

// If this is an express order page, then determine if we should forward to shipping
// or billing form.
if ($page_type == 'express order') {

    $url_form_type = '&form_type=';

    if ($form_type == 'shipping') {
        $url_form_type .= 'shipping';
    } else {
        $url_form_type .= 'billing';
    }
}

// forward user to view fields screen
header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_fields.php?' . $form_type_identifier_id . '=' . $_POST[$form_type_identifier_id] . $url_form_type . '&send_to=' . urlencode($_POST['send_to']));