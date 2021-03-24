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
validate_email_access($user);

include_once('liveform.class.php');
$liveform = new liveform('import_email_campaign_profiles');

// If the form has not been submitted, then output it.
if (!$_POST) {

    echo
        output_header() . '
        <div id="subnav">
            <h1>[new campaign profiles]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Import Campaign Profiles</h1>
            <div class="subheading" style="margin-bottom: 1em">
                Import new and update existing campaign profiles.
            </div>
            <p style="color: red">Please be aware that existing campaign profiles will be updated if the name matches.</p>
            <form name="form" enctype="multipart/form-data" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Select Formatted Text File to Upload</h2></th>
                    </tr>
                    <tr>
                        <td>CSV File:</td>
                        <td>' .
                            $liveform->output_field(array(
                                'type' => 'file',
                                'name' => 'file',
                                'size' => '60')) . '
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_button" value="Import Campaign Profiles" class="submit-primary">&nbsp;&nbsp;
                    <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form('import_email_campaign_profiles');

// Otherwise the form has been submitted so process it.
} else {

    validate_token_field();
    
    // If a file was not selected, then add error and forward user back to previous screen.
    if ($_FILES['file']['name'] == '') {
        $liveform->mark_error('file', 'Please select a CSV file.');
        go($_SERVER['PHP_SELF']);
    }

    // Fix Mac line-ending issue.
    ini_set('auto_detect_line_endings', true);
    
    // Get file handle for uploaded CSV file.
    $handle = fopen($_FILES['file']['tmp_name'], 'r');

    // Get column names from first row of CSV file.
    $row = fgetcsv($handle, 100000, ',');
    
    // If the file is empty then add error and forward user back to previous screen.
    if (!$row) {
        $liveform->mark_error('file', 'Sorry, the file you selected is empty.');
        go($_SERVER['PHP_SELF']);
    }

    // Trim all column names.
    $row = array_map('trim', $row);

    $columns = array();
    
    // Loop through the columns in order to determine which are valid.
    foreach ($row as $number => $name) {
        // If this column is valid, then add it to the columns array.
        if (
            ($name == 'name')
            || ($name == 'enabled')
            || ($name == 'action')
            || ($name == 'action_item_id')
            || ($name == 'subject')
            || ($name == 'format')
            || ($name == 'body')
            || ($name == 'page_id')
            || ($name == 'from_name')
            || ($name == 'from_email_address')
            || ($name == 'reply_email_address')
            || ($name == 'bcc_email_address')
            || ($name == 'schedule_time')
            || ($name == 'schedule_length')
            || ($name == 'schedule_unit')
            || ($name == 'schedule_period')
            || ($name == 'schedule_base')
            || ($name == 'purpose')
        ) {
            $columns[$name] = array(
                'name' => $name,
                'number' => $number);
        }
    }

    // If no valid columns were found then add error and forward user back to previous screen.
    if (!$columns) {
        $liveform->mark_error('file', 'Sorry, we could not find any valid column names in the CSV file.');
        go($_SERVER['PHP_SELF']);
    }

    // If there is no name column then add error and forward user back to previous screen.
    if (!$columns['name']) {
        $liveform->mark_error('file', 'Sorry, we could not find a "name" column in the CSV file.');
        go($_SERVER['PHP_SELF']);
    }
    
    // Prepare to keep track of how many campaign profiles were imported and updated.
    $imported_count = 0;
    $updated_count = 0;

    // Loops through all rows of data in CSV file, in order to create or update campaign profiles.
    while ($row = fgetcsv($handle, 100000, ',')) {
        // Trim all values.
        $row = array_map('trim', $row);

        $name = $row[$columns['name']['number']];

        // If the name is blank, then skip this row.
        if ($name == '') {
            continue;
        }

        // If an existing campaign profile has this name, then update campaign profile.
        if ($id = db_value("SELECT id FROM email_campaign_profiles WHERE name = '" . e($name) . "'")) {
            $sql_columns = '';

            // Loop through columns to build SQL update values.
            foreach ($columns as $column) {
                // If this is the schedule time column then prepare time data for db.
                if ($column['name'] == 'schedule_time') {
                    $value = prepare_form_data_for_input($row[$column['number']], 'time');

                } else {
                    $value = $row[$column['number']];
                }

                $sql_columns .= $column['name'] . " = '" . e($value) . "',";
            }

            db(
                "UPDATE email_campaign_profiles 
                SET
                    $sql_columns
                    last_modified_user_id = '" . USER_ID . "',
                    last_modified_timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . e($id) . "'");
            
            $updated_count++;

        // Otherwise an existing campaign profile was not found, so create a new one.
        } else {
            $sql_columns = '';
            $sql_values = '';

            // Loop through columns to build SQL update values.
            foreach ($columns as $column) {
                $sql_columns .= $column['name'] . ",";

                // If this is the schedule time column then prepare time data for db.
                if ($column['name'] == 'schedule_time') {
                    $value = prepare_form_data_for_input($row[$column['number']], 'time');
                    
                } else {
                    $value = $row[$column['number']];
                }

                $sql_values .= "'" . e($value) . "',";
            }

            // If an enabled column was not included,
            // then set campaign profile to be enabled by default.
            if (!$columns['enabled']) {
                $sql_columns .= "enabled,";
                $sql_values .= "'1',";
            }

            db(
                "INSERT INTO email_campaign_profiles (
                    $sql_columns
                    created_user_id,
                    created_timestamp,
                    last_modified_user_id,
                    last_modified_timestamp)
                VALUES (
                    $sql_values
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP(),
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");

            $imported_count++;
        }
    }

    if ($imported_count) {
        if ($imported_count > 1) {
            $imported_plural_suffix = 's';
            $imported_verb = 'have';
        } else {
            $imported_plural_suffix = '';
            $imported_verb = 'has';
        }
    }

    if ($updated_count) {
        if ($updated_count > 1) {
            $updated_plural_suffix = 's';
            $updated_verb = 'have';
        } else {
            $updated_plural_suffix = '';
            $updated_verb = 'has';
        }
    }

    $liveform_view_email_campaign_profiles = new liveform('view_email_campaign_profiles');
    
    if (($imported_count > 0) && ($updated_count > 0)) {
        $message = number_format($imported_count) . ' campaign profile' . $imported_plural_suffix . ' ' . $imported_verb . ' been imported, and ' . number_format($updated_count) . ' campaign profile' . $updated_plural_suffix . ' ' . $updated_verb . ' been updated.';
        log_activity($message, $_SESSION['sessionusername']);

    } else if ($imported_count > 0) {
        $message = number_format($imported_count) . ' campaign profile' . $imported_plural_suffix . ' ' . $imported_verb . ' been imported.';
        log_activity($message, $_SESSION['sessionusername']);

    } else if ($updated_count > 0) {
        $message = number_format($updated_count) . ' campaign profile' . $updated_plural_suffix . ' ' . $updated_verb . ' been updated.';
        log_activity($message, $_SESSION['sessionusername']);

    } else {
        $message = 'No campaign profiles have been imported or updated.';
    }

    $liveform_view_email_campaign_profiles->add_notice($message);

    go(PATH . SOFTWARE_DIRECTORY . '/view_email_campaign_profiles.php');
}