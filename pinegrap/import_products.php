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
include_once('liveform.class.php');
$liveform = new liveform('import_products');
$liveform_view_products = new liveform('view_products');

// Validate that the user has access to the eCommerce area
$user = validate_user();
validate_ecommerce_access($user);

// If the form has not been submitted, output import products screen.
if (!$_POST) {
    print
        output_header() . '
        <div id="subnav">
            <h1>[new products]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Import Products</h1>
            <div class="subheading" style="margin-bottom: 1em">Import new or existing products.</div>
            <form name="form" enctype="multipart/form-data" action="import_products.php" method="post" style="margin-bottom: 15px;">
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
                        <th colspan="2"><h2>Import Options</h2></th>
                    </tr>
                    <tr>
                        <td><label for="update_existing_products">Update Existing Products :</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'update_existing_products', 'id'=>'update_existing_products', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_import_products" value="Import Products" class="submit-primary" onclick="if(document.getElementById(\'update_existing_products\').checked == true) { return confirm(\'WARNING: If an existing product has the same name as a product in the import file, then the existing product data will be replaced.\') }" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>' .
            '
        </div>' .
        output_footer();
    
    $liveform->remove_form('import_products');

// Else, the form has been submitted, so process submitted data.
} else {
    validate_token_field();
    
    // if no file was uploaded
    if (!$_FILES['file']['name']) {
        $liveform->mark_error('file', 'Please select a file.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/import_products.php');
        exit();
    }

    // Fix Mac line-ending issue.
    ini_set('auto_detect_line_endings', true);
    
    // get file handle for uploaded CSV file
    $handle = fopen($_FILES['file']['tmp_name'], "r");
    // get column names from first row of CSV file
    $columns = fgetcsv($handle, 100000, ",");
    
    // if file is empty
    if (!$columns) {
        $liveform->mark_error('file', 'The file was empty.');
        fclose($handle);
        
        // Redirect user back to the import_users page
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/import_products.php');
        exit();
    }

    $submit_form_fields = array();
    
    // create array with column field names
    foreach ($columns as $key => $value) {
        $prefix = substr($value, 0, 4);

        // If this column is a submit form field (e.g. sfc_example), then store it for later.
        if (($prefix == 'sfc_') || ($prefix == 'sfu_')) {
            $field_name = substr($value, 4);

            if ($field_name != '') {
                if ($prefix == 'sfc_') {
                    $action = 'create';
                } else {
                    $action = 'update';
                }

                $submit_form_fields[] = array(
                    'name' => $field_name,
                    'action' => $action,
                    'column' => $key);
            }

        // Otherwise this is a normal column, so deal with it in the general way.
        } else {
            $column_names[] = convert_column_name($value);
        }
    }

    // Assume that columns do not exist until we find out otherwise.
    $address_name_column_exists = false;
    $enabled_column_exists = false;

    // foreach column field name
    foreach ($column_names as $key => $value) {
        // if the column is invalid, remove from column list
        if ($value === FALSE) {
            unset($column_names[$key]);
            
        // Else, create an index key variable to hold the column_name position.
        } else {
            ${$value . '_key'} = $key;

            if ($value == 'address_name') {
                $address_name_column_exists = TRUE;
            } else if ($value == 'enabled') {
                $enabled_column_exists = true;
            }
        }
    }
    
    // Predefine variables
    $product_imported_count = 0;
    $product_updated_count = 0;

    // loops through all rows of data in CSV file
    while ($row = fgetcsv($handle, 100000, ",")) {
        // If product name is not blank, do something with the rows data.
        if ($row[$name_key]) {
            // get product information if it exists
            $query =
                "SELECT
                    id,
                    title,
                    meta_description,
                    full_description,
                    details,
                    seo_analysis_current,
                    address_name
                FROM products
                WHERE name = '" . escape($row[$name_key]) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            if (mysqli_num_rows($result) > 0) {
                $row_2 = mysqli_fetch_assoc($result);
                $product_id = $row_2['id'];
                $title = $row_2['title'];
                $meta_description = $row_2['meta_description'];
                $full_description = $row_2['full_description'];
                $details = $row_2['details'];
                $seo_analysis_current = $row_2['seo_analysis_current'];
                $address_name = $row_2['address_name'];

                $new_address_name = '';
                
                // If update_existing_products checkbox was checked
                if ($_POST['update_existing_products'] == '1') {
                    $enabled = '';

                    // if the seo analysis is current, then initialize variables for storing new values for seo fields, so we can determine if we need to clear the seo current status
                    if ($seo_analysis_current == 1) {
                        $new_title = '';
                        $new_meta_description = '';
                        $new_full_description = '';
                        $new_details = '';
                    }
                    
                    $update_values = '';
                    
                    // Build update values to use in the query string
                    foreach ($column_names as $column_name) {
                        $column_value = $row[${$column_name . '_key'}];
                        if ($column_value != '') {
                            // If the column_name is price or extra_shipping_cost, convert the data to the correct format.
                            if (($column_name == 'price') || ($column_name == 'extra_shipping_cost')) {
                                // remove commas
                                $column_value = str_replace(',', '', $column_value);
                                
                                // convert dollars to cents
                                $column_value = $column_value * 100;
                            }
                            
                            // if this column is address_name, then prepare address name
                            if ($column_name == 'address_name') {
                                $column_value = prepare_catalog_item_address_name($column_value, $product_id);
                                $new_address_name = $column_value;
                            }

                            // If this column is enabled, then store value for later.
                            if ($column_name == 'enabled') {
                                $enabled = $column_value;
                            }
                            
                            // if this column is keywords, then update the product's keywords in the tag cloud
                            if ($column_name == 'keywords') {
                                // get the tag cloud keywords xref records for this product
                                $query = "SELECT item_id FROM tag_cloud_keywords_xref WHERE item_id = '" . escape($product_id) . "' AND item_type = 'product'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                // if there is an xref record, then update the keywords in the tag cloud
                                if (mysqli_num_rows($result) > 0) {
                                    $new_keywords = array();
                                    
                                    // get the new keywords
                                    $new_keywords = explode(',', $column_value);
                                    
                                    // loop through the keywords to remove any extra spaces before and after the keyword
                                    foreach ($new_keywords as $key => $new_keyword) {
                                        if ($new_keyword != '') {
                                            $new_keywords[$key] = trim($new_keyword);
                                        }
                                    }
                                    
                                    // remove duplicate entries from the array
                                    $new_keywords = array_unique($new_keywords);
                                    
                                    $original_keywords = array();
                                    
                                    // get the original meta keywords for this product
                                    $query = "SELECT keyword FROM tag_cloud_keywords WHERE (item_id = '" . escape($product_id) . "' AND item_type = 'product')";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                    while($row_3 = mysqli_fetch_assoc($result)) {
                                        $original_keywords[] = $row_3['keyword'];
                                    }
                                    
                                    // if there are original keywords, then compare them to the new keywords and remove any keywords that are in both arrays from the new keywords array,
                                    // and remove any original keywords that are not in the new keywords array from the database
                                    if (count($original_keywords) > 0) {
                                        // loop through the old and new keywords arrays to remove any keywords that are in both, and to remove old keywords from the database that are not in the new keywords array
                                        foreach ($original_keywords as $original_keyword) {
                                            $found_keyword = FALSE;
                                            
                                            foreach ($new_keywords as $key => $new_keyword) {
                                                // if the original keyword matches the new keyword, then remove it from the new keywords array and indicate that a keyword was found
                                                if ($original_keyword == $new_keyword) {
                                                    unset($new_keywords[$key]);
                                                    $found_keyword = TRUE;
                                                }
                                            }
                                            
                                            // if a keyword was not found, then remove it from the database
                                            if ($found_keyword == FALSE) {
                                                $query = "DELETE FROM tag_cloud_keywords WHERE ((keyword = '" . escape($original_keyword) . "') AND (item_id = '" . escape($product_id) . "') AND (item_type = 'product'))";
                                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                            }
                                        }
                                    }
                                    
                                    // loop through the new keywords and add them to the database
                                    foreach ($new_keywords as $key => $new_keyword) {
                                        // if the new keyword is not blank, then insert the keyword
                                        if ($new_keyword != '') {
                                            $query = 
                                                "INSERT INTO tag_cloud_keywords 
                                                (
                                                    keyword, 
                                                    item_id, 
                                                    item_type
                                                ) VALUES (
                                                    '" . escape($new_keyword) . "',
                                                    '" . escape($product_id) . "',
                                                    'product'
                                                )";
                                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                        }
                                    }
                                }
                            }
                            
                            if ($update_values != '') {
                                $update_values .= ', ';
                            }
                            
                            $update_values .= $column_name . " = '" . escape($column_value) . "'";
                            
                            // if the seo analysis is current, then determine if this is a column that we need to set the new value for
                            if ($seo_analysis_current == 1) {
                                switch ($column_name) {
                                    case 'title':
                                        $new_title = $column_value;
                                        break;
                                        
                                    case 'meta_description':
                                        $new_meta_description = $column_value;
                                        break;
                                        
                                    case 'full_description':
                                        $new_full_description = $column_value;
                                        break;
                                    
                                    case 'details':
                                        $new_details = $column_value;
                                        break;
                                }
                            }
                        }
                    }
                    
                    $sql_seo_analysis_current = "";
                    
                    // if the seo analysis is current and the title, meta description, full description, or details has changed, the prepare to clear current status
                    if (
                        ($seo_analysis_current == 1)
                        &&
                        (
                            ((trim($new_title) != '') && (trim($title) != trim($new_title)))
                            || ((trim($new_meta_description) != '') && (trim($meta_description) != trim($new_meta_description)))
                            || ((trim($new_full_description) != '') && (trim($full_description) != trim($new_full_description)))
                            || ((trim($new_details) != '') && (trim($details) != trim($new_details)))
                        )
                    ) {
                        $sql_seo_analysis_current = "seo_analysis_current = '0',";
                    }
                    
                    // Update row into products table
                    $query = "
                        UPDATE products 
                        SET
                            $update_values,
                            $sql_seo_analysis_current
                            user = '" . $user['id'] . "',
                            timestamp = UNIX_TIMESTAMP()
                        WHERE name = '" . escape($row[$name_key]) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    // If an enabled column exists and the product is disabled, then delete tag cloud keywords.
                    if (
                        ($enabled_column_exists == true)
                        && ($enabled == 0)
                    ) {
                        db("DELETE FROM tag_cloud_keywords WHERE (item_id = '" . escape($product_id) . "') AND (item_type = 'product')");
                    }

                    // If a submit form field column exists, then update those fields.
                    if ($submit_form_fields) {
                        // Get the custom form page id in order to determine
                        // if submit form is enabled for this product,
                        // and so that we can verify field names are valid.
                        $submit_form_custom_form_page_id = db_value(
                            "SELECT submit_form_custom_form_page_id
                            FROM products
                            WHERE id = '$product_id'");

                        // If a custom form is selected for this product,
                        // then continue to deal with submit form fields.
                        if ($submit_form_custom_form_page_id) {
                            // Delete existing submit form fields.
                            db(
                                "DELETE FROM product_submit_form_fields
                                WHERE (product_id = '$product_id')");

                            // Loop through the submit form fields in order to add values to the db.
                            foreach ($submit_form_fields as $field) {
                                $value = $row[$field['column']];

                                // If there was a value included for this field in the CSV file,
                                // then continue to deal with this field.
                                if ($value != '') {
                                    // Check to make sure that the field actually exists on the custom form
                                    // in order to make sure that user is not trying to do something funny like trying to
                                    // set a field on a different form from the one they selected.
                                    $field_id = db_value(
                                        "SELECT id
                                        FROM form_fields
                                        WHERE
                                            (name = '" . e($field['name']) . "')
                                            AND (page_id = '" . e($submit_form_custom_form_page_id) . "')");

                                    // If a field was found then continue to add field to database.
                                    if ($field_id) {
                                        db(
                                            "INSERT INTO product_submit_form_fields (
                                                product_id,
                                                action,
                                                form_field_id,
                                                value)
                                            VALUES (
                                                '$product_id',
                                                '" . $field['action'] . "',
                                                '$field_id',
                                                '" . e(trim($value)) . "')");
                                    }
                                }
                            }
                        }
                    }

                    $product_updated_count ++;
                }

            // Else, the product name was not already in use.
            } else {
                // Create new product
                $insert_columns = '';
                $insert_values = '';
                
                // Loop through each column name to build dynamic data for the query string.
                foreach ($column_names as $column_name) {
                    // if the column name is not address_name, then continue, because we are going to set the address_name later
                    if ($column_name != 'address_name') {
                        $column_value = $row[${$column_name . '_key'}];
                        if ($column_value != '') {
                            // If the column_name is price or extra_shipping_cost, convert the data to the correct format.
                            if (($column_name == 'price') || ($column_name == 'extra_shipping_cost')) {
                                // remove commas
                                $column_value = str_replace(',', '', $column_value);
                                
                                // convert dollars to cents
                                $column_value = $column_value * 100;
                            }
                            
                            if ($insert_columns != '') {
                                $insert_columns .= ', ';
                            }
                                                    
                            if ($insert_values != '') {
                                $insert_values .= ', ';
                            }
                            
                            $insert_columns .= $column_name;
                            $insert_values .= "'" . escape($column_value) . "'";
                        }
                    }
                }

                // If an enabled column was not included,
                // then set product to be enabled by default.
                if ($enabled_column_exists == false) {
                    if ($insert_columns != '') {
                        $insert_columns .= ', ';
                    }
                                            
                    if ($insert_values != '') {
                        $insert_values .= ', ';
                    }
                    
                    $insert_columns .= 'enabled';
                    $insert_values .= "'1'";
                }
                
                // Insert row into products table
                $query = "
                    INSERT INTO products (
                        " . $insert_columns . ",
                        user,
                        timestamp)
                    VALUES (
                        " . $insert_values . ",
                        '" . $user['id'] . "',
                        UNIX_TIMESTAMP())";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $product_id = mysqli_insert_id(db::$con);
                
                $address_name = '';
                
                // if the address name is NOT blank then use that value for the address name
                if ($row[${'address_name_key'}] != '') {
                    $address_name = $row[${'address_name_key'}];
                    
                // else if the short description is NOT blank then use that value
                } elseif ($row[${'short_description_key'}] != '') {
                    $address_name = $row[${'short_description_key'}];
                    
                // else if the name is NOT blank then use that value
                } elseif ($row[${'name_key'}] != '') {
                    $address_name = $row[${'name_key'}];
                    
                // else use the product id
                } else {
                    $address_name = $product_id;
                }
                
                // prepare the address name for the database
                $address_name = prepare_catalog_item_address_name($address_name);
                
                // update the product's address name
                $query = "UPDATE products SET address_name = '" . escape($address_name) . "' WHERE id = '$product_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                // If a submit form field column exists, then update those fields.
                if ($submit_form_fields) {
                    // Get the custom form page id in order to determine
                    // if submit form is enabled for this product,
                    // and so that we can verify field names are valid.
                    $submit_form_custom_form_page_id = db_value(
                        "SELECT submit_form_custom_form_page_id
                        FROM products
                        WHERE id = '$product_id'");

                    // If a custom form is selected for this product,
                    // then continue to deal with submit form fields.
                    if ($submit_form_custom_form_page_id) {
                        // Loop through the submit form fields in order to add values to the db.
                        foreach ($submit_form_fields as $field) {
                            $value = $row[$field['column']];

                            // If there was a value included for this field in the CSV file,
                            // then continue to deal with this field.
                            if ($value != '') {
                                // Check to make sure that the field actually exists on the custom form
                                // in order to make sure that user is not trying to do something funny like trying to
                                // set a field on a different form from the one they selected.
                                $field_id = db_value(
                                    "SELECT id
                                    FROM form_fields
                                    WHERE
                                        (name = '" . e($field['name']) . "')
                                        AND (page_id = '" . e($submit_form_custom_form_page_id) . "')");

                                // If a field was found then continue to add field to database.
                                if ($field_id) {
                                    db(
                                        "INSERT INTO product_submit_form_fields (
                                            product_id,
                                            action,
                                            form_field_id,
                                            value)
                                        VALUES (
                                            '$product_id',
                                            '" . $field['action'] . "',
                                            '$field_id',
                                            '" . e(trim($value)) . "')");
                                }
                            }
                        }
                    }
                }

                // If submit form, and submit form update are enabled for this product,
                // and the where field is set to reference code, and the where value is blank or contains carets
                // then add product form field for the reference code and set that in the product property.
                if (
                    $row[${'submit_form_key'}]
                    && $row[${'submit_form_update_key'}]
                    && ($row[${'submit_form_update_where_field_key'}] == 'reference_code')
                    &&
                    (
                        ($row[${'submit_form_update_where_value_key'}] == '')
                        || (mb_strpos($row[${'submit_form_update_where_value_key'}], '^^') !== false)
                    )
                ) {
                    // Remove carets from where value, in order to get field name.
                    $field_name = str_replace('^^', '', $row[${'submit_form_update_where_value_key'}]);

                    if ($field_name == '') {
                        $field_name = 'reference_code';
                    }

                    db(
                        "INSERT INTO form_fields (
                            form_type,
                            product_id,
                            name,
                            label,
                            type,
                            required,
                            user,
                            timestamp)
                        VALUES (
                            'product',
                            '$product_id',
                            '" . e($field_name) . "',
                            'Conversation Number:',
                            'text box',
                            '0',
                            '" . USER_ID . "',
                            UNIX_TIMESTAMP())");

                    $field_id = mysqli_insert_id(db::$con);

                    // Enable product form and set reference code field in product.
                    db(
                        "UPDATE products
                        SET
                            form = '1',
                            submit_form_update_where_value = '^^" . e($field_name) . "^^'
                        WHERE id = '$product_id'");
                }
                
                $product_imported_count ++;
            }
        }
    }
    
    fclose($handle);
    
    // Display notices to tell the user what has been done. Redirect them to proper page.
    if (($product_imported_count > 0) && ($product_updated_count > 0)) {
        $liveform_view_products->add_notice($product_imported_count . ' product(s) have been imported, and ' . $product_updated_count . ' product(s) have been updated.');
        log_activity($product_imported_count . ' product(s) have been imported, and ' . $product_updated_count . ' product(s) have been updated.', $_SESSION['sessionusername']);
    } elseif ($product_imported_count > 0) {
        $liveform_view_products->add_notice($product_imported_count . ' product(s) have been imported.');
        log_activity($product_imported_count . ' product(s) have been imported.', $_SESSION['sessionusername']);
    } elseif ($product_updated_count > 0) {
        $liveform_view_products->add_notice($product_updated_count . ' product(s) have been updated.');
        log_activity($product_updated_count . ' product(s) have been updated.', $_SESSION['sessionusername']);
    } else {
        $liveform_view_products->add_notice('No products have been imported or updated.');
    }
    
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_products.php');
}

function convert_column_name($column_name)
{
    // If the first custom product field is active and that is the column, then return colum name.
    if (
        (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '')
        && (mb_strtolower($column_name) == mb_strtolower(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL))
    ) {
        return 'custom_field_1';
    }

    // If the second custom product field is active and that is the column, then return colum name.
    if (
        (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '')
        && (mb_strtolower($column_name) == mb_strtolower(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL))
    ) {
        return 'custom_field_2';
    }

    // If the third custom product field is active and that is the column, then return colum name.
    if (
        (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '')
        && (mb_strtolower($column_name) == mb_strtolower(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL))
    ) {
        return 'custom_field_3';
    }

    // If the fourth custom product field is active and that is the column, then return colum name.
    if (
        (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '')
        && (mb_strtolower($column_name) == mb_strtolower(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL))
    ) {
        return 'custom_field_4';
    }

    switch ($column_name) {
        case 'name':
            return('name');
            break;

        case 'enabled':
            return('enabled');
            break;

        case 'short_description':
            return('short_description');
            break;
            
        case 'full_description':
            return('full_description');
            break;
            
        case 'details':
            return('details');
            break;
            
        case 'code':
            return('code');
            break;
            
        case 'keywords':
            return('keywords');
            break;
            
        case 'image_name':
            return('image_name');
            break;
            
        case 'price':
            return('price');
            break;
            
        case 'taxable':
            return('taxable');
            break;
            
        case 'selection_type':
            return('selection_type');
            break;
            
        case 'default_quantity':
            return('default_quantity');
            break;

        case 'minimum_quantity':
            return('minimum_quantity');
            break;

        case 'maximum_quantity':
            return('maximum_quantity');
            break;
            
        case 'address_name':
            return('address_name');
            break;
        
        case 'title':
            return('title');
            break;
            
        case 'meta_description':
            return('meta_description');
            break;
            
        case 'meta_keywords':
            return('meta_keywords');
            break;
            
        case 'inventory':
            return('inventory');
            break;
            
        case 'inventory_quantity':
            return('inventory_quantity');
            break;
            
        case 'backorder':
            return('backorder');
            break;
            
        case 'out_of_stock_message':
            return('out_of_stock_message');
            break;

        case 'required_product_id':
            return('required_product');
            break;

        case 'form':
            return('form');
            break;
        
        case 'form_name':
            return('form_name');
            break;
        
        case 'form_label_column_width':
            return('form_label_column_width');
            break;
        
        case 'form_quantity_type':
            return('form_quantity_type');
            break;
            
        case 'shippable':
            return('shippable');
            break;

        case 'weight':
            return('weight');
            break;
            
        case 'primary_weight_points':
            return('primary_weight_points');
            break;
            
        case 'secondary_weight_points':
            return('secondary_weight_points');
            break;

        case 'length':
            return('length');
            break;

        case 'width':
            return('width');
            break;

        case 'height':
            return('height');
            break;

        case 'container_required':
            return('container_required');
            break;
            
        case 'preparation_time':
            return('preparation_time');
            break;
            
        case 'free_shipping':
            return('free_shipping');
            break;
            
        case 'extra_shipping_cost':
            return('extra_shipping_cost');
            break;
            
        case 'commissionable':
            return('commissionable');
            break;
            
        case 'commission_rate_limit':
            return('commission_rate_limit');
            break;
            
        case 'order_receipt_message':
            return('order_receipt_message');
            break;

        case 'order_receipt_bcc_email_address':
            return('order_receipt_bcc_email_address');
            break;
            
        case 'email_page_id':
            return('email_page');
            break;

        case 'email_bcc_email_address':
            return('email_bcc');
            break;
            
        case 'recurring':
            return('recurring');
            break;

        case 'recurring_schedule_editable_by_customer':
            return('recurring_schedule_editable_by_customer');
            break;
            
        case 'recurring_days_before_start':
            return('start');
            break;
            
        case 'recurring_number_of_payments':
            return('number_of_payments');
            break;
            
        case 'recurring_payment_period':
            return('payment_period');
            break;

        case 'recurring_profile_disabled_perform_actions':
            return('recurring_profile_disabled_perform_actions');
            break;
        
        case 'recurring_profile_disabled_expire_membership':
            return('recurring_profile_disabled_expire_membership');
            break;
        
        case 'recurring_profile_disabled_revoke_private_access':
            return('recurring_profile_disabled_revoke_private_access');
            break;
        
        case 'recurring_profile_disabled_email':
            return('recurring_profile_disabled_email');
            break;
        
        case 'recurring_profile_disabled_email_subject':
            return('recurring_profile_disabled_email_subject');
            break;
        
        case 'recurring_profile_disabled_email_page_id':
            return('recurring_profile_disabled_email_page_id');
            break;
            
        case 'recurring_sage_group_id':
            return('sage_group_id');
            break;

        case 'contact_group_id':
            return('contact_group_id');
            break;
            
        case 'membership_renewal':
            return('membership_renewal');
            break;

        case 'grant_private_access':
            return('grant_private_access');
            break;
            
        case 'private_folder_id':
            return('private_folder');
            break;

        case 'private_days':
            return('private_days');
            break;

        case 'start_page_id':
            return('send_to_page');
            break;
            
        case 'reward_points':
            return('reward_points');
            break;

        case 'gift_card':
            return('gift_card');
            break;

        case 'gift_card_email_subject':
            return('gift_card_email_subject');
            break;

        case 'gift_card_email_format':
            return('gift_card_email_format');
            break;

        case 'gift_card_email_body':
            return('gift_card_email_body');
            break;

        case 'gift_card_email_page_id':
            return('gift_card_email_page_id');
            break;

        case 'submit_form':
            return('submit_form');
            break;

        case 'submit_form_custom_form_page_id':
            return('submit_form_custom_form_page_id');
            break;

        case 'submit_form_create':
            return('submit_form_create');
            break;

        case 'submit_form_update':
            return('submit_form_update');
            break;

        case 'submit_form_update_where_field':
            return('submit_form_update_where_field');
            break;

        case 'submit_form_update_where_value':
            return('submit_form_update_where_value');
            break;

        case 'submit_form_quantity_type':
            return('submit_form_quantity_type');
            break;

        case 'add_comment':
            return('add_comment');
            break;

        case 'add_comment_page_id':
            return('add_comment_page_id');
            break;

        case 'add_comment_message':
            return('add_comment_message');
            break;

        case 'add_comment_name':
            return('add_comment_name');
            break;

        case 'add_comment_only_for_submit_form_update':
            return('add_comment_only_for_submit_form_update');
            break;
            
        case 'notes':
            return('notes');
            break;

        case 'google_product_category':
            return('google_product_category');
            break;
        
        case 'gtin':
            return('gtin');
            break;
        
        case 'brand':
            return('brand');
            break;
        
        case 'mpn':
            return('mpn');
            break;
    }
    
    return false;
}
?>