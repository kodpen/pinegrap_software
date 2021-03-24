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

include_once('liveform.class.php');
$liveform = new liveform('edit_arrival_date');

if (!$_POST) {
    // get arrival date data
    $query = "SELECT * FROM arrival_dates WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $name = h($row['name']);
    $description = h($row['description']);
    $code = h($row['code']);
    $arrival_date = prepare_form_data_for_output($row['arrival_date'], 'date');
    $status = $row['status'];
    $start_date = prepare_form_data_for_output($row['start_date'], 'date');
    $end_date = prepare_form_data_for_output($row['end_date'], 'date');
    $default_selected = $row['default_selected'];
    $sort_order = $row['sort_order'];
    $custom = $row['custom'];
    $custom_maximum_arrival_date = prepare_form_data_for_output($row['custom_maximum_arrival_date'], 'date');

    // prepare checked status for status radio buttons
    if ($status == 'enabled') {
        $status_enabled = ' checked="checked"';
        $status_disabled = '';
    } else {
        $status_enabled = '';
        $status_disabled = ' checked="checked"';
    }

    // prepare checked status for default checkbox
    if ($default_selected == 1) {
        $default_selected_checked = ' checked="checked"';
    } else {
        $default_selected_checked = '';
    }
    
    $custom_maximum_arrival_date_row_style = '';
    $shipping_cutoff_heading_row_style = '';
    $shipping_cutoff_row_style = '';
    
    // if custom is enabled, then prepare to check custom check box and hide shipping cut-off rows
    if ($custom == 1) {
        $custom_checked = ' checked="checked"';
        $shipping_cutoff_heading_row_style = 'display: none';
        $shipping_cutoff_row_style = 'display: none';
        
    // else custom is not enabled, so prepare to not check custom check box and hide custom maximum arrival date row
    } else {
        $custom_checked = '';
        $custom_maximum_arrival_date_row_style = 'display: none';
    }
    
    if ($custom_maximum_arrival_date == '0/0/0000') {
        $custom_maximum_arrival_date = '';
    }
    
    // get shipping cut-offs
    $query =
        "SELECT
            shipping_method_id,
            date_and_time
        FROM shipping_cutoffs
        WHERE arrival_date_id = '" . escape($_GET['id']) . "'
        ORDER BY id ASC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $shipping_cutoffs = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $shipping_cutoffs[] = $row;
    }

    $output_shipping_cutoffs_for_javascript = '';
    $count = 0;

    // loop through shipping cut-offs in order to prepare output for javascript
    foreach ($shipping_cutoffs as $shipping_cutoff) {
        $output_shipping_cutoffs_for_javascript .=
            'shipping_cutoffs[' . $count . '] = new Array();
            shipping_cutoffs[' . $count . ']["shipping_method_id"] = "' . $shipping_cutoff['shipping_method_id'] . '";
            shipping_cutoffs[' . $count . ']["date_and_time"] = "' . prepare_form_data_for_output($shipping_cutoff['date_and_time'], 'date and time') . '";' . "\n";

        $count++;
    }
    
    // get all shipping methods, in order to prepare shipping method id options for shipping cut-offs
    $query =
        "SELECT
            id,
            name,
            code
        FROM shipping_methods
        ORDER BY name ASC, code ASC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $shipping_methods = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $shipping_methods[] = $row;
    }
    
    $output_shipping_method_id_options_for_javascript = '';
    $count = 0;

    // loop through all shipping methods in order to prepare javascript array
    foreach ($shipping_methods as $shipping_method) {
        // set the name to the shipping method name
        $shipping_method_name = $shipping_method['name'];
        
        // if there is a code, then add the code to the name
        if ($shipping_method['code'] != '') {
            $shipping_method_name .= ' (' . $shipping_method['code'] . ')';
        }
        
        // set the value to the shipping method id
        $shipping_method_value = $shipping_method['id'];
        
        $output_shipping_method_id_options_for_javascript .=
            'shipping_method_id_options[' . $count . '] = new Array();
            shipping_method_id_options[' . $count . ']["name"] = "' . escape_javascript($shipping_method_name) . '";
            shipping_method_id_options[' . $count . ']["value"] = "' . $shipping_method_value . '";' . "\n";
        
        $count++;
    }

    $output =
        output_header() . '
        ' . get_date_picker_format() . '
        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
        <div id="subnav">
            <h1>' . $name . '</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Arrival Date</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit a arrival date for any special occasion, or holiday that will be presented during checkout. (Delete all will hide feature.)</div>
            <form name="form" action="edit_arrival_date.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" id="last_shipping_cutoff_number" name="last_shipping_cutoff_number" value="0" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Arrival Date</h2></th>
                    </tr>
                    <tr>
                        <td>Arrival Date:</td>
                        <td>
                            <input type="text" id="arrival_date" name="arrival_date" size="10" maxlength="10" value="' . $arrival_date . '" />
                            <script>
                                $("#arrival_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Or allow Customer to enter this Arrival Date during checkout</h2></th>
                    </tr>
                    <tr>
                        <td>Display Custom Field:</td>
                        <td><input type="checkbox" name="custom" id="custom" value="1"' . $custom_checked . ' class="checkbox" onclick="show_or_hide_custom()" /></td>
                    </tr>
                    <tr id="custom_maximum_arrival_date_row" style="' . $custom_maximum_arrival_date_row_style . '">
                        <td style="padding-left: 2em">Latest Allowed Arrival Date:</td>
                        <td>
                            <input type="text" id="custom_maximum_arrival_date" name="custom_maximum_arrival_date" size="10" maxlength="10" value="' . $custom_maximum_arrival_date . '" /> (leave blank for no restriction)
                            <script>
                                $("#custom_maximum_arrival_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>New Arrival Date Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Arrival Date Code:</td>
                        <td><input type="text" name="code" maxlength="50" value="' . $code . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Order Preview Page Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Arrival Date Name:</td>
                        <td><input type="text" name="name" maxlength="50" value="' . $name . '" /></td>
                    </tr>
                    <tr>
                        <td>Arrival Date Description:</td>
                        <td><input type="text" name="description" size="80" maxlength="255" value="' . $description . '" /></td>
                    </tr>
                    <tr>
                        <td>Sort Order:</td>
                        <td><input name="sort_order" type="text" size="3" maxlength="3" value="' . $sort_order . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Arrival Date Availability</h2></th>
                    </tr>
                    <tr>
                        <td>Start Date:</td>
                        <td>
                            <input type="text" id="start_date" name="start_date" size="10" maxlength="10" value="' . $start_date . '" />
                            <script>
                                $("#start_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>End Date:</td>
                        <td>
                            <input type="text" id="end_date" name="end_date" size="10" maxlength="10" value="' . $end_date . '" />
                            <script>
                                $("#end_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>Status Override:</td>
                        <td><input type="radio" name="status" id="enabled" value="enabled"' . $status_enabled . ' class="radio" /><label for="enabled">Enabled</label> <input type="radio" name="status" id="disabled" value="disabled"' . $status_disabled . ' class="radio" /><label for="disabled">Disabled</label></td>
                    </tr>
                    <tr>
                        <td>Selected by Default:</td>
                        <td><input type="checkbox" name="default_selected" value="1"' . $default_selected_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="shipping_cutoff_heading_row" style="' . $shipping_cutoff_heading_row_style . '">
                        <th colspan="2"><h2>Shipping Cut-offs</h2></th>
                    </tr>
                    <tr id="shipping_cutoff_row" style="' . $shipping_cutoff_row_style . '">
                        <td colspan="2">
                            <script type="text/javascript">
                                var last_shipping_cutoff_number = 0;
    
                                var shipping_cutoffs = new Array();
                                
                                ' . $output_shipping_cutoffs_for_javascript . '
                                
                                var shipping_method_id_options = new Array();

                                ' . $output_shipping_method_id_options_for_javascript . '
                                
                                // once the page has loaded loop through all shipping cut-offs in order to create rows for them
                                window.onload = function() {
                                    for (var i = 0; i < shipping_cutoffs.length; i++) {
                                        create_shipping_cutoff(shipping_cutoffs[i]);
                                    }
                                };
                            </script>
                            <div><a href="javascript:void(0)" onclick="create_shipping_cutoff()" class="button">Add Shipping Cut-off</a></div>
                            <table id="shipping_cutoff_table" class="chart" style="display: none; margin-top: 1.25em">
                                <tr>
                                    <th style="text-align: left">Shipping Method</th>
                                    <th style="text-align: left">Cut-off Date &amp; Time</th>
                                    <th style="text-align: left">&nbsp;</th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This arrival date will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

    print $output;
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // delete shipping cut-offs (we do this regardless of whether the arrival date is being deleted or edited)
    $query = "DELETE FROM shipping_cutoffs WHERE arrival_date_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // assume that there is not an error for a shipping cut-off until we find out otherwise
    $shipping_cutoff_error = FALSE;
    
    // if arrival date was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete arrival date
        $query = "DELETE FROM arrival_dates WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('arrival date (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
    // else arrival date was not selected for delete
    } else {
        // update arrival date
        $query =
            "UPDATE arrival_dates SET
                name = '" . escape($_POST['name']) . "',
                description = '" . escape($_POST['description']) . "',
                code = '" . escape($_POST['code']) . "',
                arrival_date = '" . escape(prepare_form_data_for_input($_POST['arrival_date'], 'date')) . "',
                status = '" . escape($_POST['status']) . "',
                start_date = '" . escape(prepare_form_data_for_input($_POST['start_date'], 'date')) . "',
                end_date = '" . escape(prepare_form_data_for_input($_POST['end_date'], 'date')) . "',
                default_selected = '" . escape($_POST['default_selected']) . "',
                sort_order = '" . escape($_POST['sort_order']) . "',
                custom = '" . escape($_POST['custom']) . "',
                custom_maximum_arrival_date = '" . escape(prepare_form_data_for_input($_POST['custom_maximum_arrival_date'], 'date')) . "',
                user = '" . $user['id'] . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if default selected was checked, then turn value off for all other arrival dates
        if ($_POST['default_selected'] == 1) {
            // update arrival date
            $query = "UPDATE arrival_dates SET default_selected = 0 WHERE id != '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        // create array for storing shipping methods that have a shipping cut-off, so that we do not create multiple cut-offs for the same shipping method
        $shipping_cutoff_shipping_methods = array();
        
        // loop through all shipping cut-offs in order to insert them into database
        for ($i = 1; $i <= $_POST['last_shipping_cutoff_number']; $i++) {
            // if a shipping method was selected
            // and a shipping cut-off has not already been added for the selected shipping method
            // and a cut-off date and time was entered
            // and the cut-off date and time is a valid date
            // and the cut-off date and time is between the start date and end date for the arrival date,
            // then insert shipping cut-off into database
            if (
                ($_POST['shipping_cutoff_' . $i . '_shipping_method_id'] != '')
                && (in_array($_POST['shipping_cutoff_' . $i . '_shipping_method_id'], $shipping_cutoff_shipping_methods) == FALSE)
                && ($_POST['shipping_cutoff_' . $i . '_date_and_time'] != '')
                && (validate_date_and_time($_POST['shipping_cutoff_' . $i . '_date_and_time']) == TRUE)
                && (prepare_form_data_for_input($_POST['shipping_cutoff_' . $i . '_date_and_time'], 'date and time') >= prepare_form_data_for_input($_POST['start_date'], 'date') . ' 00:00:00')
                && (prepare_form_data_for_input($_POST['shipping_cutoff_' . $i . '_date_and_time'], 'date and time') <= prepare_form_data_for_input($_POST['end_date'], 'date') . ' 23:59:59')
            ) {
                $query =
                    "INSERT INTO shipping_cutoffs (
                        arrival_date_id,
                        shipping_method_id,
                        date_and_time)
                    VALUES (
                        '" . escape($_POST['id']) . "',
                        '" . escape($_POST['shipping_cutoff_' . $i . '_shipping_method_id']) . "',
                        '" . escape(prepare_form_data_for_input($_POST['shipping_cutoff_' . $i . '_date_and_time'], 'date and time')) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // remember that the shipping method has been used in a shipping cut-off
                $shipping_cutoff_shipping_methods[] = $_POST['shipping_cutoff_' . $i . '_shipping_method_id'];
                
            // else there was an error, so remember that
            } else {
                $shipping_cutoff_error = TRUE;
            }
        }

        log_activity('arrival date (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
    }
    
    // if there was a shipping cut-off error, then add notice and forward user to edit arrival date screen
    if ($shipping_cutoff_error == TRUE) {
        $liveform->add_notice('The arrival date has been saved, however one or more shipping cut-offs were removed because of an error. It is recommended that you review the shipping cut-offs. Please make sure you select a shipping method, enter a valid cut-off date &amp; time that falls between the start date and end date, and make sure you do not enter multiple shipping cut-offs for the same shipping method.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_arrival_date.php?id=' . urlencode($_POST['id']));
        
    // else there was not a shipping cut-off error, so forward user to view arrival dates screen
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_arrival_dates.php');
    }
}
?>