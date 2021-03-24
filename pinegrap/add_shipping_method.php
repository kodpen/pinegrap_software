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
    // get all zones for zones selection
    $query = "SELECT id, name FROM zones ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $output_disallowed_zones .= '<option value="' . $row['id'] . '">' . h($row['name']) . '</option>';
    }

    if (DATE_FORMAT == 'month_day') {
        $output_end_time = '12/31/2099 11:59 PM';
    } else {
        $output_end_time = '31/12/2099 11:59 PM';
    }

    $output =
        output_header() . '
        ' . get_date_picker_format() . '
        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
        <div id="subnav">
            <h1>[new shipping method]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Shipping Method</h1>
            <div class="subheading" style="margin-bottom: 1em">Create a new shipping method that will be made available during checkout based on the products and destination address.</div>
            <form name="form" action="add_shipping_method.php" method="post" onsubmit="prepare_selects(new Array(\'allowed_zones\'))">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Shipping Method Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Code:</td>
                        <td><input type="text" name="code" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Shipping Method Page Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Display Name:</td>
                        <td><input type="text" name="name" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <td>Display Message:</td>
                        <td><input type="text" name="description" size="80" maxlength="255" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Service for Real-Time Rate &amp; Delivery</h2></th>
                    </tr>
                    <tr>
                        <td>Service:</td>
                        <td>
                            ' . render(array('template' => 'shipping_method_service.php')) . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Shipping Method Charges</h2></th>
                    </tr>
                    <tr id="realtime_rate_row" style="display: none">
                        <td><label for="realtime_rate">Real-Time Rate:</label></td>
                        <td>
                            <input type="checkbox" id="realtime_rate" name="realtime_rate" value="1"
                                class="checkbox">

                            <script>init_shipping_method_service()</script>
                        </td>
                    </tr>
                    <tr>
                        <td>Base Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate" size="5"> &nbsp;&nbsp; <input type="checkbox" name="variable_base_rate" id="variable_base_rate" value="1" class="variable_base_rate checkbox" onclick="toggle_variable_base_rate()"><label for="variable_base_rate"> Enable variable base rate</label></td>
                    </tr>
                    <tr class="base_rate_2_row" style="display: none">
                        <td style="padding-left: 2em">or</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_2" size="5">&nbsp; if recipient subtotal is at least &nbsp;' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_2_subtotal" size="5"></td>
                    </tr>
                    <tr class="base_rate_3_row" style="display: none">
                        <td style="padding-left: 2em">or</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_3" size="5">&nbsp; if recipient subtotal is at least &nbsp;' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_3_subtotal" size="5"></td>
                    </tr>
                    <tr class="base_rate_4_row" style="display: none">
                        <td style="padding-left: 2em; padding-bottom: 2em">or</td>
                        <td style="padding-bottom: 2em">' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_4" size="5">&nbsp; if recipient subtotal is at least &nbsp;' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_4_subtotal" size="5"></td>
                    </tr>
                    <tr>
                        <td>Primary Weight Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="primary_weight_rate" size="5" /> &nbsp;&nbsp; <input type="checkbox" id="primary_weight_rate_first_item_excluded" name="primary_weight_rate_first_item_excluded" value="1" class="checkbox" /><label for="primary_weight_rate_first_item_excluded"> Don\'t apply to first item</label></td>
                    </tr>
                    <tr>
                        <td>Secondary Weight Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="secondary_weight_rate" size="5" /> &nbsp;&nbsp; <input type="checkbox" id="secondary_weight_rate_first_item_excluded" name="secondary_weight_rate_first_item_excluded" value="1" class="checkbox" /><label for="secondary_weight_rate_first_item_excluded"> Don\'t apply to first item</label></td>
                    </tr>
                    <tr>
                        <td>Item Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="item_rate" size="5" /> &nbsp;&nbsp; <input type="checkbox" id="item_rate_first_item_excluded" name="item_rate_first_item_excluded" value="1" class="checkbox" /><label for="item_rate_first_item_excluded"> Don\'t apply to first item</label></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Destination Delivery Options</h2></th>
                    </tr>
                    <tr>
                        <td>Allow Street Address:</td>
                        <td><input type="checkbox" name="street_address" value="1" checked="checked" class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td>Allow PO Box:</td>
                        <td><input type="checkbox" name="po_box" value="1" checked="checked" class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <table style="width: 100%">
                                <tr>
                                    <th colspan="3"><h2>Available Destinations</h2></th>
                                </tr>
                                <tr>
                                    <td style="width: 40%">
                                        <div style="margin-bottom: 3px">Allowed Zones</div>
                                        <input type="hidden" id="allowed_zones_hidden" name="allowed_zones_hidden" value="">
                                        <select id="allowed_zones" multiple="multiple" size="10" style="width: 95%"></select>
                                    </td>
                                    <td style="width: 20%; text-align: center; vertical-align: middle; padding-left: 15px; padding-right: 15px">
                                        <input type="button" value="&gt;&gt;" onclick="move_options(\'allowed_zones\', \'disallowed_zones\', \'right\');" /><br />
                                        <br />
                                        <input type="button" value="&lt;&lt;" onclick="move_options(\'allowed_zones\', \'disallowed_zones\', \'left\');" /><br />
                                    </td>
                                    <td style="width: 40%">
                                        <div style="margin-bottom: 3px">Disallowed Zones</div>
                                        <select id="disallowed_zones" multiple="multiple" size="10" style="width: 95%">' . $output_disallowed_zones . '</select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2>Handling</h2></th>
                    </tr>

                    <tr>
                        <td>
                            <label for="handle_days">Handling Time:</label>
                        </td>
                        <td>
                            <input type="text" id="handle_days" name="handle_days" size="3"
                                maxlength="5">&nbsp;
                            day(s)
                        </td>
                    </tr>

                    <tr>
                        <td>Handling on:</td>
                        <td>
                            <table>
                                <tr>
                                    <td style="text-align: center" title="Monday">
                                        <label for="handle_mon">Mon</label><br>
                                        <input type="checkbox" id="handle_mon" name="handle_mon"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Tueday">
                                        <label for="handle_tue">Tue</label><br>
                                        <input type="checkbox" id="handle_tue" name="handle_tue"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Wednesday">
                                        <label for="handle_wed">Wed</label><br>
                                        <input type="checkbox" id="handle_wed" name="handle_wed"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Thursday">
                                        <label for="handle_thu">Thu</label><br>
                                        <input type="checkbox" id="handle_thu" name="handle_thu"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Friday">
                                        <label for="handle_fri">Fri</label><br>
                                        <input type="checkbox" id="handle_fri" name="handle_fri"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Saturday">
                                        <label for="handle_sat">Sat</label><br>
                                        <input type="checkbox" id="handle_sat" name="handle_sat"
                                            value="1" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Sunday">
                                        <label for="handle_sun">Sun</label><br>
                                        <input type="checkbox" id="handle_sun" name="handle_sun"
                                            value="1" class="checkbox">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td>Ships on:</td>
                        <td>
                            <table>
                                <tr>
                                    <td style="text-align: center" title="Monday">
                                        <label for="ship_mon">Mon</label><br>
                                        <input type="checkbox" id="ship_mon" name="ship_mon"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Tueday">
                                        <label for="ship_tue">Tue</label><br>
                                        <input type="checkbox" id="ship_tue" name="ship_tue"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Wednesday">
                                        <label for="ship_wed">Wed</label><br>
                                        <input type="checkbox" id="ship_wed" name="ship_wed"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Thursday">
                                        <label for="ship_thu">Thu</label><br>
                                        <input type="checkbox" id="ship_thu" name="ship_thu"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Friday">
                                        <label for="ship_fri">Fri</label><br>
                                        <input type="checkbox" id="ship_fri" name="ship_fri"
                                            value="1" checked="checked" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Saturday">
                                        <label for="ship_sat">Sat</label><br>
                                        <input type="checkbox" id="ship_sat" name="ship_sat"
                                            value="1" class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Sunday">
                                        <label for="ship_sun">Sun</label><br>
                                        <input type="checkbox" id="ship_sun" name="ship_sun"
                                            value="1" class="checkbox">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="end_of_day">End of Day Time:</label>
                        </td>
                        <td>
                            <input type="text" id="end_of_day" name="end_of_day" size="8" maxlength="8">&nbsp;
                            (h:mm AM/PM)
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2>Transit</h2></th>
                    </tr>
                    <tr>
                        <td>
                            <label for="base_transit_days">Base Transit Time:</label>
                        </td>
                        <td colspan="2">
                            <input type="text" id="base_transit_days" name="base_transit_days"
                                size="3" maxlength="10">&nbsp;
                            day(s)
                        </td>
                    </tr>
                    <tr>
                        <td><label for="adjust_transit">Adjust Transit for Country:</label></td>
                        <td colspan="2"><input type="checkbox" name="adjust_transit" id="adjust_transit" value="1" class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td><label for="transit_on_saturday">Transit on Saturday:</label></td>
                        <td colspan="2"><input type="checkbox" name="transit_on_saturday" id="transit_on_saturday" value="1" class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td><label for="transit_on_sunday">Transit on Sunday:</label></td>
                        <td colspan="2"><input type="checkbox" name="transit_on_sunday" id="transit_on_sunday" value="1" class="checkbox" /></td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2>Excluded Dates for Shipping &amp; Handling (e.g. Holidays)</h2></th>
                    </tr>

                    <tr>
                        <td style="vertical-align: top">Excluded Dates:</td>
                        <td style="vertical-align: top">Enter one date per line (' . get_date_format_help() . ')<br /><textarea name="excluded_transit_dates" rows="10" cols="28"></textarea></td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2>Shipping Method Availability</h2></th>
                    </tr>

                    <tr>
                        <td>Start Time:</td>
                        <td>
                            <input type="text" id="start_time" name="start_time" size="19" maxlength="19" value="'. date('n/j/Y') . ' 12:00 AM" />
                            <script>
                                $("#start_time").datetimepicker({
                                    dateFormat: date_picker_format,
                                    timeFormat: "h:mm TT"
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>End Time:</td>
                        <td>
                            <input type="text" id="end_time" name="end_time" size="19" maxlength="19" value="' . $output_end_time . '" />
                            <script>
                                $("#end_time").datetimepicker({
                                    dateFormat: date_picker_format,
                                    timeFormat: "h:mm TT"
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><input type="radio" name="status" id="enabled" value="enabled" checked="checked" class="radio" /><label for="enabled">Enabled</label> <input type="radio" name="status" id="disabled" value="disabled" class="radio" /><label for="disabled">Disabled</label></td>
                    </tr>
                    <tr>
                        <td>Available on:</td>
                        <td>
                            <table style="width: 100%">
                                <tr>
                                    <td><input type="checkbox" id="available_on_monday" name="available_on_monday" value="1" checked="checked" class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_monday">Monday</label></td>
                                    <td><div id="available_on_monday_cutoff_time_cell">Cut-off time: <input type="text" name="available_on_monday_cutoff_time" size="8" maxlength="8" value="" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_tuesday" name="available_on_tuesday" value="1" checked="checked" class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_tuesday">Tuesday</label></td>
                                    <td><div id="available_on_tuesday_cutoff_time_cell">Cut-off time: <input type="text" name="available_on_tuesday_cutoff_time" size="8" maxlength="8" value="" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_wednesday" name="available_on_wednesday" value="1" checked="checked" class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_wednesday">Wednesday</label></td>
                                    <td><div id="available_on_wednesday_cutoff_time_cell">Cut-off time: <input type="text" name="available_on_wednesday_cutoff_time" size="8" maxlength="8" value="" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_thursday" name="available_on_thursday" value="1" checked="checked" class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_thursday">Thursday</label></td>
                                    <td><div id="available_on_thursday_cutoff_time_cell">Cut-off time: <input type="text" name="available_on_thursday_cutoff_time" size="8" maxlength="8" value="" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_friday" name="available_on_friday" value="1" checked="checked" class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_friday">Friday</label></td>
                                    <td><div id="available_on_friday_cutoff_time_cell">Cut-off time: <input type="text" name="available_on_friday_cutoff_time" size="8" maxlength="8" value="" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_saturday" name="available_on_saturday" value="1" checked="checked" class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_saturday">Saturday</label></td>
                                    <td><div id="available_on_saturday_cutoff_time_cell">Cut-off time: <input type="text" name="available_on_saturday_cutoff_time" size="8" maxlength="8" value="" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_sunday" name="available_on_sunday" value="1" checked="checked" class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_sunday">Sunday</label></td>
                                    <td><div id="available_on_sunday_cutoff_time_cell">Cut-off time: <input type="text" name="available_on_sunday_cutoff_time" size="8" maxlength="8" value="" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><em>For reference, the current site time is ' . date("g:i A") . '.</em></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="protected">Protected:</label></td>
                        <td><input type="checkbox" id="protected" name="protected" value="1" class="checkbox"></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

    print $output;

} else {

    validate_token_field();

    $service = $_POST['service'];

    $realtime_rate = $_POST['realtime_rate'];

    // If the service does not support real-time rates then clear real-time rate value, so it
    // won't be enabled in db and show check mark on All Shipping Methods screen.
    if (!$service or substr($service, 0, 5) == 'fedex') {
        $realtime_rate = 0;
    }
    
    // convert rates from dollars to cents
    $base_rate = $_POST['base_rate'] * 100;
    $primary_weight_rate = $_POST['primary_weight_rate'] * 100;
    $secondary_weight_rate = $_POST['secondary_weight_rate'] * 100;
    $item_rate = $_POST['item_rate'] * 100;

    $variable_base_rate = 0;
    $base_rate_2 = 0;
    $base_rate_2_subtotal = 0;
    $base_rate_3 = 0;
    $base_rate_3_subtotal = 0;
    $base_rate_4 = 0;
    $base_rate_4_subtotal = 0;

    // If variable base rate is enabled, then prepare that data.
    if ($_POST['variable_base_rate'] == 1) {
        $variable_base_rates = array();

        // Loop through the different variable base rate fields
        // in order to add them to array if data was entered for them.
        for ($number = 2; $number <= 4; $number++) { 
            // If a valid base rate and subtotal was entered, then add this variable base rate.
            if (
                (is_numeric($_POST['base_rate_' . $number]))
                && ($_POST['base_rate_' . $number] >= 0)
                && (is_numeric($_POST['base_rate_' . $number . '_subtotal']))
                && ($_POST['base_rate_' . $number . '_subtotal'] > 0)
            ) {
                $variable_base_rates[] = array(
                    'subtotal' => $_POST['base_rate_' . $number . '_subtotal'] * 100,
                    'rate' => $_POST['base_rate_' . $number] * 100);
            }
        }

        // If there is at least one valid variable base rate, then deal with them.
        if ($variable_base_rates) {
            // Prepare to enable variable base rates in the db.
            $variable_base_rate = 1;

            // Order the variable base rates by the subtotal (subtotal is the first value in the array).
            sort($variable_base_rates);

            // Loop through the variable base rates in order to prepare db values.
            foreach ($variable_base_rates as $key => $rate) {
                $number = $key + 2;

                ${'base_rate_' . $number} = $rate['rate'];
                ${'base_rate_' . $number . '_subtotal'} = $rate['subtotal'];
            }
        }
    }

    // create shipping method
    $query = "INSERT INTO shipping_methods (
                name,
                description,
                code,
                status,
                start_time,
                end_time,
                service,
                realtime_rate,
                base_rate,
                variable_base_rate,
                base_rate_2,
                base_rate_2_subtotal,
                base_rate_3,
                base_rate_3_subtotal,
                base_rate_4,
                base_rate_4_subtotal,
                primary_weight_rate,
                primary_weight_rate_first_item_excluded,
                secondary_weight_rate,
                secondary_weight_rate_first_item_excluded,
                item_rate,
                item_rate_first_item_excluded,
                base_transit_days,
                adjust_transit,
                street_address,
                po_box,
                handle_days,
                handle_mon,
                handle_tue,
                handle_wed,
                handle_thu,
                handle_fri,
                handle_sat,
                handle_sun,
                ship_mon,
                ship_tue,
                ship_wed,
                ship_thu,
                ship_fri,
                ship_sat,
                ship_sun,
                end_of_day,
                transit_on_sunday,
                transit_on_saturday,
                available_on_sunday,
                available_on_sunday_cutoff_time,
                available_on_monday,
                available_on_monday_cutoff_time,
                available_on_tuesday,
                available_on_tuesday_cutoff_time,
                available_on_wednesday,
                available_on_wednesday_cutoff_time,
                available_on_thursday,
                available_on_thursday_cutoff_time,
                available_on_friday,
                available_on_friday_cutoff_time,
                available_on_saturday,
                available_on_saturday_cutoff_time,
                protected,
                user,
                timestamp)
            VALUES (
                '" . escape($_POST['name']) . "',
                '" . escape($_POST['description']) . "',
                '" . escape($_POST['code']) . "',
                '" . escape($_POST['status']) . "',
                '" . escape(prepare_form_data_for_input($_POST['start_time'], 'date and time')) . "',
                '" . escape(prepare_form_data_for_input($_POST['end_time'], 'date and time')) . "',
                '" . e($_POST['service']) . "',
                '" . e($realtime_rate) . "',
                '" . escape($base_rate) . "',
                '" . escape($variable_base_rate) . "',
                '" . escape($base_rate_2) . "',
                '" . escape($base_rate_2_subtotal) . "',
                '" . escape($base_rate_3) . "',
                '" . escape($base_rate_3_subtotal) . "',
                '" . escape($base_rate_4) . "',
                '" . escape($base_rate_4_subtotal) . "',
                '" . escape($primary_weight_rate) . "',
                '" . escape($_POST['primary_weight_rate_first_item_excluded']) . "',
                '" . escape($secondary_weight_rate) . "',
                '" . escape($_POST['secondary_weight_rate_first_item_excluded']) . "',
                '" . escape($item_rate) . "',
                '" . escape($_POST['item_rate_first_item_excluded']) . "',
                '" . escape($_POST['base_transit_days']) . "',
                '" . escape($_POST['adjust_transit']) . "',
                '" . escape($_POST['street_address']) . "',
                '" . escape($_POST['po_box']) . "',
                '" . e($_POST['handle_days']) . "',
                '" . e($_POST['handle_mon']) . "',
                '" . e($_POST['handle_tue']) . "',
                '" . e($_POST['handle_wed']) . "',
                '" . e($_POST['handle_thu']) . "',
                '" . e($_POST['handle_fri']) . "',
                '" . e($_POST['handle_sat']) . "',
                '" . e($_POST['handle_sun']) . "',
                '" . e($_POST['ship_mon']) . "',
                '" . e($_POST['ship_tue']) . "',
                '" . e($_POST['ship_wed']) . "',
                '" . e($_POST['ship_thu']) . "',
                '" . e($_POST['ship_fri']) . "',
                '" . e($_POST['ship_sat']) . "',
                '" . e($_POST['ship_sun']) . "',
                '" . e(prepare_form_data_for_input($_POST['end_of_day'], 'time')) . "',
                '" . escape($_POST['transit_on_sunday']) . "',
                '" . escape($_POST['transit_on_saturday']) . "',
                '" . escape($_POST['available_on_sunday']) . "',
                '" . escape(prepare_form_data_for_input($_POST['available_on_sunday_cutoff_time'], 'time')) . "',
                '" . escape($_POST['available_on_monday']) . "',
                '" . escape(prepare_form_data_for_input($_POST['available_on_monday_cutoff_time'], 'time')) . "',
                '" . escape($_POST['available_on_tuesday']) . "',
                '" . escape(prepare_form_data_for_input($_POST['available_on_tuesday_cutoff_time'], 'time')) . "',
                '" . escape($_POST['available_on_wednesday']) . "',
                '" . escape(prepare_form_data_for_input($_POST['available_on_wednesday_cutoff_time'], 'time')) . "',
                '" . escape($_POST['available_on_thursday']) . "',
                '" . escape(prepare_form_data_for_input($_POST['available_on_thursday_cutoff_time'], 'time')) . "',
                '" . escape($_POST['available_on_friday']) . "',
                '" . escape(prepare_form_data_for_input($_POST['available_on_friday_cutoff_time'], 'time')) . "',
                '" . escape($_POST['available_on_saturday']) . "',
                '" . escape(prepare_form_data_for_input($_POST['available_on_saturday_cutoff_time'], 'time')) . "',
                '" . e($_POST['protected']) . "',
                " . $user['id'] . ",
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $shipping_method_id = mysqli_insert_id(db::$con);

    // load all allowed zones in array by exploding string that has allowed zone ids separated by commas
    $allowed_zones = explode(',', $_POST['allowed_zones_hidden']);

    // foreach allowed zone insert row in shipping_methods_zones_xref table
    foreach ($allowed_zones as $zone_id) {
        // if zone id is not blank, insert row
        if ($zone_id) {
            $query = "INSERT INTO shipping_methods_zones_xref (shipping_method_id, zone_id) VALUES ($shipping_method_id, '" . escape($zone_id) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
    
    // load all excluded transit dates into an array
    $excluded_transit_dates = explode("\n", $_POST['excluded_transit_dates']);
    
    // loop through all excluded transit dates in order to validate
    foreach ($excluded_transit_dates as $key => $excluded_transit_date) {
        // remove spaces from beginning and end of date
        $excluded_transit_date = trim($excluded_transit_date);
        
        // convert date to storage format
        $excluded_transit_date = prepare_form_data_for_input($excluded_transit_date, 'date');
        
        // split date into parts
        $excluded_transit_date_parts = explode('-', $excluded_transit_date);
        $year = $excluded_transit_date_parts[0];
        $month = $excluded_transit_date_parts[1];
        $day = $excluded_transit_date_parts[2];
        
        // if date is valid then update date in array
        if ((is_numeric($month) == true) && (is_numeric($day) == true) && (is_numeric($year) == true) && (checkdate($month, $day, $year) == true)) {
            $excluded_transit_dates[$key] = $excluded_transit_date;
            
        // else date is not valid, so remove date from array
        } else {
            unset($excluded_transit_dates[$key]);
        }
    }
    
    // remove duplicate dates from array
    $excluded_transit_dates = array_unique($excluded_transit_dates);
    
    // sort array
    sort($excluded_transit_dates);
    
    // loop through all excluded transit dates in order to add dates to database
    foreach ($excluded_transit_dates as $excluded_transit_date) {
        $query =
            "INSERT INTO excluded_transit_dates (
                shipping_method_id,
                date)
            VALUES (
                '$shipping_method_id',
                '" . escape($excluded_transit_date) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    log_activity('shipping method (' . $_POST['name'] . ') was created', $_SESSION['sessionusername']);

    // forward user to view shipping methods screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_shipping_methods.php');
}