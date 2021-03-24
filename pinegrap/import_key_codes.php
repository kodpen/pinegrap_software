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
validate_ecommerce_access($user);

if (!$_POST) {
    $output =
        output_header() . '
        <div id="subnav">
            <h1>[new key codes]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Import Key Codes</h1>
            <div class="subheading" style="margin-bottom: 1em">Import new or overwrite existing key codes.</div>
            <form enctype="multipart/form-data" action="import_key_codes.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Select Formatted Text File to Upload</h2></th>
                    </tr>
                    <tr>
                        <td>CSV File: <input name="file" type="file" size="60" /><br /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_import" value="Import" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();

    print $output;
    
} else {
    validate_token_field();
    
    // if no file was uploaded
    if (!$_FILES['file']['name']) {
        output_error('Please select a file. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    // Fix Mac line-ending issue.
    ini_set('auto_detect_line_endings', true);

    // get file handle for uploaded CSV file
    $handle = fopen($_FILES['file']['tmp_name'], "r");
    // get column names from first row of CSV file
    $columns = fgetcsv($handle, 100000, ",");

    // if file is empty
    if (!$columns) {
        output_error('The file was empty. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    // create array with column field names
    foreach ($columns as $key => $value) {
        $column_names[] = convert_column_name($value);
    }

    // Assume that enabled column does not exist, until we find out otherwise.
    // We use this later to determine whether we need to enable by default,
    // in case enabled column was not included.
    $enabled_column_exists = false;

    // foreach column field name
    foreach ($column_names as $key => $value) {
        // if the column is invalid, remove from column list
        if ($value === false) {
            unset($column_names[$key]);
        }

        // if column is key code, then store key location for key code
        if ($value == 'code') {
            $key_code_key = $key;

        } else if ($value == 'enabled') {
            $enabled_column_exists = true;
        }
    }
    
    // if key code column could not be found, output error
    if (isset($key_code_key) == false) {
        output_error('A key code column could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    // build list of column names for database query
    foreach ($column_names as $key => $value) {
        $column_list .= "$value, ";
    }
    
    $column_list .= 'user, timestamp';

    // If an enabled column does not exist in the CSV file,
    // then add column so key codes are enabled by default.
    if (!$enabled_column_exists) {
        $column_list .= ', enabled';
    }

    $imported_key_codes = 0;

    // loops through all rows of data in CSV file
    while ($row = fgetcsv($handle, 100000, ",")) {
        $key_code = $row[$key_code_key];
        
        // if there is a key code
        if ($key_code) {
            // delete key code if key code already exists
            $query = "DELETE FROM key_codes WHERE code = '" . escape($key_code) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            $data_list = '';            
            
            // foreach field
            foreach ($column_names as $key => $value) {
                // create value list
                $value = escape($row[$key]);
                $data_list .= "'$value', ";
            }

            $data_list .= "'$user[id]', UNIX_TIMESTAMP()";

            // If an enabled column does not exist in the CSV file,
            // then add column so key codes are enabled by default.
            if (!$enabled_column_exists) {
                $data_list .= ", '1'";
            }
            
            // insert row of data into database
            $query = "INSERT INTO key_codes ($column_list)
                     VALUES ($data_list)";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            $imported_key_codes++;
        }
    }
    
    fclose($handle);

    log_activity("$imported_key_codes key codes were imported", $_SESSION['sessionusername']);

    // forward user to view key codes screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_key_codes.php');
}

function convert_column_name($column_name)
{
    // convert column name to lowercase
    $column_name = mb_strtolower($column_name);
    // remove spaces from column name
    $column_name = str_replace(' ', '', $column_name);
    // remove underscores from column name
    $column_name = str_replace('_', '', $column_name);
    // remove dashes from column name
    $column_name = str_replace('-', '', $column_name);

    switch ($column_name) {
        case 'keycode':
            return('code');
            break;

        case 'offercode':
            return('offer_code');
            break;

        case 'enabled':
            return('enabled');
            break;

        case 'expirationdate':
            return('expiration_date');
            break;

        case 'notes':
            return('notes');
            break;

        case 'singleuse':
            return('single_use');
            break;

        case 'report':
            return('report');
            break;
    }

    return false;
}