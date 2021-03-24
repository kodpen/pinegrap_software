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
    // get shipping method data
    $query = "SELECT * FROM shipping_methods WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $shipping_method = $row;

    $name = h($row['name']);
    $description = h($row['description']);
    $code = h($row['code']);
    $status = $row['status'];
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];

    $service = $row['service'];

    $realtime_rate = $row['realtime_rate'];

    $realtime_rate_checked = '';

    if ($realtime_rate) {
        $realtime_rate_checked = ' checked="checked"';
    }

    $base_rate = sprintf("%01.2lf", $row['base_rate'] / 100);
    $variable_base_rate = $row['variable_base_rate'];

    $base_rate_2 = '';
    $base_rate_2_subtotal = '';

    if ($row['base_rate_2_subtotal'] > 0) {
        $base_rate_2 = sprintf("%01.2lf", $row['base_rate_2'] / 100);
        $base_rate_2_subtotal = sprintf("%01.2lf", $row['base_rate_2_subtotal'] / 100);
    }

    $base_rate_3 = '';
    $base_rate_3_subtotal = '';

    if ($row['base_rate_3_subtotal'] > 0) {
        $base_rate_3 = sprintf("%01.2lf", $row['base_rate_3'] / 100);
        $base_rate_3_subtotal = sprintf("%01.2lf", $row['base_rate_3_subtotal'] / 100);
    }

    $base_rate_4 = '';
    $base_rate_4_subtotal = '';

    if ($row['base_rate_4_subtotal'] > 0) {
        $base_rate_4 = sprintf("%01.2lf", $row['base_rate_4'] / 100);
        $base_rate_4_subtotal = sprintf("%01.2lf", $row['base_rate_4_subtotal'] / 100);
    }

    $primary_weight_rate = sprintf("%01.2lf", $row['primary_weight_rate'] / 100);
    $primary_weight_rate_first_item_excluded = $row['primary_weight_rate_first_item_excluded'];
    $secondary_weight_rate = sprintf("%01.2lf", $row['secondary_weight_rate'] / 100);
    $secondary_weight_rate_first_item_excluded = $row['secondary_weight_rate_first_item_excluded'];
    $item_rate = sprintf("%01.2lf", $row['item_rate'] / 100);
    $item_rate_first_item_excluded = $row['item_rate_first_item_excluded'];
    $base_transit_days = $row['base_transit_days'];
    $adjust_transit = $row['adjust_transit'];
    $street_address = $row['street_address'];
    $po_box = $row['po_box'];
    $transit_on_sunday = $row['transit_on_sunday'];
    $transit_on_saturday = $row['transit_on_saturday'];
    $available_on_sunday = $row['available_on_sunday'];
    $available_on_sunday_cutoff_time = $row['available_on_sunday_cutoff_time'];
    $available_on_monday = $row['available_on_monday'];
    $available_on_monday_cutoff_time = $row['available_on_monday_cutoff_time'];
    $available_on_tuesday = $row['available_on_tuesday'];
    $available_on_tuesday_cutoff_time = $row['available_on_tuesday_cutoff_time'];
    $available_on_wednesday = $row['available_on_wednesday'];
    $available_on_wednesday_cutoff_time = $row['available_on_wednesday_cutoff_time'];
    $available_on_thursday = $row['available_on_thursday'];
    $available_on_thursday_cutoff_time = $row['available_on_thursday_cutoff_time'];
    $available_on_friday = $row['available_on_friday'];
    $available_on_friday_cutoff_time = $row['available_on_friday_cutoff_time'];
    $available_on_saturday = $row['available_on_saturday'];
    $available_on_saturday_cutoff_time = $row['available_on_saturday_cutoff_time'];
    $protected = $row['protected'];

    $output_variable_base_rate_checked = '';
    $output_base_rate_2_row_style = ' style="display: none"';
    $output_base_rate_3_row_style = ' style="display: none"';
    $output_base_rate_4_row_style = ' style="display: none"';

    if ($variable_base_rate == 1) {
        $output_variable_base_rate_checked = ' checked="checked"';
        $output_base_rate_2_row_style = '';
        $output_base_rate_3_row_style = '';
        $output_base_rate_4_row_style = '';
    }

    // prepare checked status for status radio buttons
    if ($status == 'enabled') {
        $status_enabled = ' checked="checked"';
        $status_disabled = '';
    } else {
        $status_enabled = '';
        $status_disabled = ' checked="checked"';
    }

    // prepare checked status for adjust transit checkbox
    if ($adjust_transit == 1) {
        $adjust_transit_checked = ' checked="checked"';
    } else {
        $adjust_transit_checked = '';
    }

    $primary_weight_rate_first_item_excluded_checked = '';

    if ($primary_weight_rate_first_item_excluded == 1) {
        $primary_weight_rate_first_item_excluded_checked = ' checked="checked"';
    }

    $secondary_weight_rate_first_item_excluded_checked = '';

    if ($secondary_weight_rate_first_item_excluded == 1) {
        $secondary_weight_rate_first_item_excluded_checked = ' checked="checked"';
    }

    $item_rate_first_item_excluded_checked = '';

    if ($item_rate_first_item_excluded == 1) {
        $item_rate_first_item_excluded_checked = ' checked="checked"';
    }

    // prepare checked status for street address checkbox
    if ($street_address == 1) {
        $street_address_checked = ' checked="checked"';
    } else {
        $street_address_checked = '';
    }

    // prepare checked status for po box checkbox
    if ($po_box == 1) {
        $po_box_checked = ' checked="checked"';
    } else {
        $po_box_checked = '';
    }

    // get all zones for zones selection
    $query = "SELECT id, name FROM zones ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $zones[] = array('id'=>$row['id'], 'name'=>$row['name']);
    }

    // if there is at least one zone
    if ($zones) {
        // foreach zone, check if zone is allowed or disallowed for this zone
        foreach ($zones as $key => $value) {
            $query = "SELECT zone_id FROM shipping_methods_zones_xref WHERE shipping_method_id = '" . escape($_GET['id']) . "' AND zone_id = '" . $zones[$key]['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if shipping method and zone were found
            if (mysqli_num_rows($result)) {
                $allowed_zones[] = $zones[$key];
            } else {
                $disallowed_zones[] = $zones[$key];
            }
        }

        // if there is at least one allowed zone
        if ($allowed_zones) {
            // foreach allowed zone prepare option
            foreach ($allowed_zones as $key => $value) {
                $output_allowed_zones .= '<option value="' . $allowed_zones[$key]['id'] . '">' . h($allowed_zones[$key]['name']) . '</option>';
            }
        }

        // if there is at least one disallowed zone
        if ($disallowed_zones) {
            // foreach disallowed zone prepare option
            foreach ($disallowed_zones as $key => $value) {
                $output_disallowed_zones .= '<option value="' . $disallowed_zones[$key]['id'] . '">' . h($disallowed_zones[$key]['name']) . '</option>';
            }
        }
    }

    // Leave handle days blank if zero.

    $handle_days = '';

    if ($shipping_method['handle_days']) {
        $handle_days = $shipping_method['handle_days'];
    }

    $handle_mon_checked = '';

    if ($shipping_method['handle_mon']) {
        $handle_mon_checked = ' checked="checked"';
    }

    $handle_tue_checked = '';

    if ($shipping_method['handle_tue']) {
        $handle_tue_checked = ' checked="checked"';
    }

    $handle_wed_checked = '';

    if ($shipping_method['handle_wed']) {
        $handle_wed_checked = ' checked="checked"';
    }

    $handle_thu_checked = '';

    if ($shipping_method['handle_thu']) {
        $handle_thu_checked = ' checked="checked"';
    }

    $handle_fri_checked = '';

    if ($shipping_method['handle_fri']) {
        $handle_fri_checked = ' checked="checked"';
    }

    $handle_sat_checked = '';

    if ($shipping_method['handle_sat']) {
        $handle_sat_checked = ' checked="checked"';
    }

    $handle_sun_checked = '';

    if ($shipping_method['handle_sun']) {
        $handle_sun_checked = ' checked="checked"';
    }

    $ship_mon_checked = '';

    if ($shipping_method['ship_mon']) {
        $ship_mon_checked = ' checked="checked"';
    }

    $ship_tue_checked = '';

    if ($shipping_method['ship_tue']) {
        $ship_tue_checked = ' checked="checked"';
    }

    $ship_wed_checked = '';

    if ($shipping_method['ship_wed']) {
        $ship_wed_checked = ' checked="checked"';
    }

    $ship_thu_checked = '';

    if ($shipping_method['ship_thu']) {
        $ship_thu_checked = ' checked="checked"';
    }

    $ship_fri_checked = '';

    if ($shipping_method['ship_fri']) {
        $ship_fri_checked = ' checked="checked"';
    }

    $ship_sat_checked = '';

    if ($shipping_method['ship_sat']) {
        $ship_sat_checked = ' checked="checked"';
    }

    $ship_sun_checked = '';

    if ($shipping_method['ship_sun']) {
        $ship_sun_checked = ' checked="checked"';
    }

    // Leave end of day blank if zero.

    $end_of_day = '';

    if ($shipping_method['end_of_day'] != '00:00:00') {
        $end_of_day = prepare_form_data_for_output($shipping_method['end_of_day'], 'time');
    }
    
    // prepare checked status for transit on sunday checkbox
    if ($transit_on_sunday == 1) {
        $transit_on_sunday_checked = ' checked="checked"';
    } else {
        $transit_on_sunday_checked = '';
    }
    
    // prepare checked status for transit on saturday checkbox
    if ($transit_on_saturday == 1) {
        $transit_on_saturday_checked = ' checked="checked"';
    } else {
        $transit_on_saturday_checked = '';
    }
    
    // get excluded transit dates
    $query = "SELECT date FROM excluded_transit_dates WHERE shipping_method_id = '" . escape($_GET['id']) . "' ORDER BY date";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $output_excluded_transit_dates = '';
    
    // loop through all excluded transit dates in order to prepare data for textarea
    while ($row = mysqli_fetch_assoc($result)) {
        // if this is not the first excluded transit date, then add a newline character
        if ($output_excluded_transit_dates != '') {
            $output_excluded_transit_dates .= "\n";
        }
        
        $output_excluded_transit_dates .= prepare_form_data_for_output($row['date'], 'date');
    }
    
    $available_on_sunday_checked = '';
    $available_on_sunday_cutoff_time_cell_style = 'display: none';
    $available_on_monday_checked = '';
    $available_on_monday_cutoff_time_cell_style = 'display: none';
    $available_on_tuesday_checked = '';
    $available_on_tuesday_cutoff_time_cell_style = 'display: none';
    $available_on_wednesday_checked = '';
    $available_on_wednesday_cutoff_time_cell_style = 'display: none';
    $available_on_thursday_checked = '';
    $available_on_thursday_cutoff_time_cell_style = 'display: none';
    $available_on_friday_checked = '';
    $available_on_friday_cutoff_time_cell_style = 'display: none';
    $available_on_saturday_checked = '';
    $available_on_saturday_cutoff_time_cell_style = 'display: none';
    
    // if available on sunday is one, then set it to checked and show it's cut-off time cell
    if ($available_on_sunday == '1') {
        $available_on_sunday_checked = ' checked="checked"';
        $available_on_sunday_cutoff_time_cell_style = '';
    }
    
    // if available on sunday cutoff time is set to 00:00:00, then set it to blank
    if ($available_on_sunday_cutoff_time == '00:00:00') {
        $available_on_sunday_cutoff_time = '';
    }
    
    // if available on monday is one, then set it to checked and show it's cut-off time cell
    if ($available_on_monday == '1') {
        $available_on_monday_checked = ' checked="checked"';
        $available_on_monday_cutoff_time_cell_style = '';
    }
    
    // if available on monday cutoff time is set to 00:00:00, then set it to blank
    if ($available_on_monday_cutoff_time == '00:00:00') {
        $available_on_monday_cutoff_time = '';
    }
    
    // if available on tuesday is one, then set it to checked and show it's cut-off time cell
    if ($available_on_tuesday == '1') {
        $available_on_tuesday_checked = ' checked="checked"';
        $available_on_tuesday_cutoff_time_cell_style = '';
    }
    
    // if available on tuesday cutoff time is set to 00:00:00, then set it to blank
    if ($available_on_tuesday_cutoff_time == '00:00:00') {
        $available_on_tuesday_cutoff_time = '';
    }
    
    // if available on wednesday is one, then set it to checked and show it's cut-off time cell
    if ($available_on_wednesday == '1') {
        $available_on_wednesday_checked = ' checked="checked"';
        $available_on_wednesday_cutoff_time_cell_style = '';
    }
    
    // if available on wednesday cutoff time is set to 00:00:00, then set it to blank
    if ($available_on_wednesday_cutoff_time == '00:00:00') {
        $available_on_wednesday_cutoff_time = '';
    }
    
    // if available on thursday is one, then set it to checked and show it's cut-off time cell
    if ($available_on_thursday == '1') {
        $available_on_thursday_checked = ' checked="checked"';
        $available_on_thursday_cutoff_time_cell_style = '';
    }
    
    // if available on thursday cutoff time is set to 00:00:00, then set it to blank
    if ($available_on_thursday_cutoff_time == '00:00:00') {
        $available_on_thursday_cutoff_time = '';
    }
    
    // if available on friday is one, then set it to checked and show it's cut-off time cell
    if ($available_on_friday == '1') {
        $available_on_friday_checked = ' checked="checked"';
        $available_on_friday_cutoff_time_cell_style = '';
    }
    
    // if available on friday cutoff time is set to 00:00:00, then set it to blank
    if ($available_on_friday_cutoff_time == '00:00:00') {
        $available_on_friday_cutoff_time = '';
    }
    
    // if available on saturday is one, then set it to checked and show it's cut-off time cell
    if ($available_on_saturday == '1') {
        $available_on_saturday_checked = ' checked="checked"';
        $available_on_saturday_cutoff_time_cell_style = '';
    }
    
    // if available on saturday cutoff time is set to 00:00:00, then set it to blank
    if ($available_on_saturday_cutoff_time == '00:00:00') {
        $available_on_saturday_cutoff_time = '';
    }

    $protected_checked = '';

    if ($protected) {
        $protected_checked = ' checked="checked"';
    }
    
    $output =
        output_header() . '
        ' . get_date_picker_format() . '
        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
        <div id="subnav">
            <h1>' . $name . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Shipping Method</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit a shipping method that will be made available during checkout based on the products and destination address.</div>
            <form name="form" action="edit_shipping_method.php" method="post" onsubmit="prepare_selects(new Array(\'allowed_zones\'))">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Shipping Method Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Code:</td>
                        <td><input type="text" name="code" maxlength="50" value="' . $code . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Shipping Method Page Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Display Name:</td>
                        <td><input type="text" name="name" maxlength="50" value="' . $name . '" /></td>
                    </tr>
                    <tr>
                        <td>Display Message:</td>
                        <td><input type="text" name="description" size="80" maxlength="255" value="' . $description . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Service for Real-Time Rate &amp; Delivery</h2></th>
                    </tr>
                    <tr>
                        <td>Service:</td>
                        <td>
                            ' . render(array('template' => 'shipping_method_service.php')) . '
                            <script>$("#service").val("' . escape_javascript($service) . '")</script>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Shipping Method Charges</h2></th>
                    </tr>
                    <tr id="realtime_rate_row" style="display: none">
                        <td><label for="realtime_rate">Real-Time Rate:</label></td>
                        <td>
                            <input type="checkbox" id="realtime_rate" name="realtime_rate" value="1"' . $realtime_rate_checked . '
                                class="checkbox">

                            <script>init_shipping_method_service()</script>
                        </td>
                    </tr>
                    <tr>
                        <td>Base Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate" size="5" value="' . $base_rate . '"> &nbsp;&nbsp; <input type="checkbox" name="variable_base_rate" id="variable_base_rate" value="1"' . $output_variable_base_rate_checked . ' class="variable_base_rate checkbox" onclick="toggle_variable_base_rate()"><label for="variable_base_rate"> Enable variable base rate</label></td>
                    </tr>
                    <tr class="base_rate_2_row"' . $output_base_rate_2_row_style . '>
                        <td style="padding-left: 2em">or</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_2" value="' . $base_rate_2 . '" size="5">&nbsp; if recipient subtotal is at least &nbsp;' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_2_subtotal" value="' . $base_rate_2_subtotal . '" size="5"></td>
                    </tr>
                    <tr class="base_rate_3_row"' . $output_base_rate_3_row_style . '>
                        <td style="padding-left: 2em">or</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_3" value="' . $base_rate_3 . '" size="5">&nbsp; if recipient subtotal is at least &nbsp;' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_3_subtotal" value="' . $base_rate_3_subtotal . '" size="5"></td>
                    </tr>
                    <tr class="base_rate_4_row"' . $output_base_rate_4_row_style . '>
                        <td style="padding-left: 2em; padding-bottom: 2em">or</td>
                        <td style="padding-bottom: 2em">' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_4" value="' . $base_rate_4 . '" size="5">&nbsp; if recipient subtotal is at least &nbsp;' . BASE_CURRENCY_SYMBOL . '<input type="text" name="base_rate_4_subtotal" value="' . $base_rate_4_subtotal . '" size="5"></td>
                    </tr>
                    <tr>
                        <td>Primary Weight Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="primary_weight_rate" size="5" value="' . $primary_weight_rate . '" /> &nbsp;&nbsp; <input type="checkbox" id="primary_weight_rate_first_item_excluded" name="primary_weight_rate_first_item_excluded" value="1"' . $primary_weight_rate_first_item_excluded_checked . ' class="checkbox" /><label for="primary_weight_rate_first_item_excluded"> Don\'t apply to first item.</label></td>
                    </tr>
                    <tr>
                        <td>Secondary Weight Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="secondary_weight_rate" size="5" value="' . $secondary_weight_rate . '" /> &nbsp;&nbsp; <input type="checkbox" id="secondary_weight_rate_first_item_excluded" name="secondary_weight_rate_first_item_excluded" value="1"' . $secondary_weight_rate_first_item_excluded_checked . ' class="checkbox" /><label for="secondary_weight_rate_first_item_excluded"> Don\'t apply to first item.</label></td>
                    </tr>
                    <tr>
                        <td>Item Rate:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . '<input type="text" name="item_rate" size="5" value="' . $item_rate . '" /> &nbsp;&nbsp; <input type="checkbox" id="item_rate_first_item_excluded" name="item_rate_first_item_excluded" value="1"' . $item_rate_first_item_excluded_checked . ' class="checkbox" /><label for="item_rate_first_item_excluded"> Don\'t apply to first item.</label></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Destination Delivery Options</h2></th>
                    </tr>
                    <tr>
                        <td>Allow Street Address:</td>
                        <td><input type="checkbox" name="street_address" value="1"' . $street_address_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td>Allow PO Box:</td>
                        <td><input type="checkbox" name="po_box" value="1"' . $po_box_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table style="width: 100%">
                                <tr>
                                    <th colspan="3"><h2>Available Destinations</h2></th>
                                </tr>
                                <tr>
                                    <td style="width: 40%">
                                        <div style="margin-bottom: 3px">Allowed Zones</div>
                                        <input type="hidden" id="allowed_zones_hidden" name="allowed_zones_hidden" value="">
                                        <select id="allowed_zones" multiple="multiple" size="10" style="width: 95%">' . $output_allowed_zones . '</select>
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
                                maxlength="5" value="' . h($handle_days) . '">&nbsp;
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
                                            value="1"' . $handle_mon_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Tueday">
                                        <label for="handle_tue">Tue</label><br>
                                        <input type="checkbox" id="handle_tue" name="handle_tue"
                                            value="1"' . $handle_tue_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Wednesday">
                                        <label for="handle_wed">Wed</label><br>
                                        <input type="checkbox" id="handle_wed" name="handle_wed"
                                            value="1"' . $handle_wed_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Thursday">
                                        <label for="handle_thu">Thu</label><br>
                                        <input type="checkbox" id="handle_thu" name="handle_thu"
                                            value="1"' . $handle_thu_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Friday">
                                        <label for="handle_fri">Fri</label><br>
                                        <input type="checkbox" id="handle_fri" name="handle_fri"
                                            value="1"' . $handle_fri_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Saturday">
                                        <label for="handle_sat">Sat</label><br>
                                        <input type="checkbox" id="handle_sat" name="handle_sat"
                                            value="1"' . $handle_sat_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Sunday">
                                        <label for="handle_sun">Sun</label><br>
                                        <input type="checkbox" id="handle_sun" name="handle_sun"
                                            value="1"' . $handle_sun_checked . ' class="checkbox">
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
                                            value="1"' . $ship_mon_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Tueday">
                                        <label for="ship_tue">Tue</label><br>
                                        <input type="checkbox" id="ship_tue" name="ship_tue"
                                            value="1"' . $ship_tue_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Wednesday">
                                        <label for="ship_wed">Wed</label><br>
                                        <input type="checkbox" id="ship_wed" name="ship_wed"
                                            value="1"' . $ship_wed_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Thursday">
                                        <label for="ship_thu">Thu</label><br>
                                        <input type="checkbox" id="ship_thu" name="ship_thu"
                                            value="1"' . $ship_thu_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Friday">
                                        <label for="ship_fri">Fri</label><br>
                                        <input type="checkbox" id="ship_fri" name="ship_fri"
                                            value="1"' . $ship_fri_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Saturday">
                                        <label for="ship_sat">Sat</label><br>
                                        <input type="checkbox" id="ship_sat" name="ship_sat"
                                            value="1"' . $ship_sat_checked . ' class="checkbox">
                                    </td>

                                    <td style="text-align: center" title="Sunday">
                                        <label for="ship_sun">Sun</label><br>
                                        <input type="checkbox" id="ship_sun" name="ship_sun"
                                            value="1"' . $ship_sun_checked . ' class="checkbox">
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
                            <input type="text" id="end_of_day" name="end_of_day" size="8"
                                maxlength="8" value="' . $end_of_day . '">&nbsp;
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
                            <input type="text" id="base_transit_days" name="base_transit_days" size="3" maxlength="10" value="' . $base_transit_days . '">&nbsp;
                            day(s)
                        </td>
                    </tr>
                    <tr>
                        <td><label for="adjust_transit">Adjust Transit for Country:</label></td>
                        <td colspan="2"><input type="checkbox" name="adjust_transit" id="adjust_transit" value="1"' . $adjust_transit_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td><label for="transit_on_saturday">Transit on Saturday:</label></td>
                        <td colspan="2"><input type="checkbox" name="transit_on_saturday" id="transit_on_saturday" value="1"' . $transit_on_saturday_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr>
                        <td><label for="transit_on_sunday">Transit on Sunday:</label></td>
                        <td colspan="2"><input type="checkbox" name="transit_on_sunday" id="transit_on_sunday" value="1"' . $transit_on_sunday_checked . ' class="checkbox" /></td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2>Excluded Dates for Shipping &amp; Handling (e.g. Holidays)</h2></th>
                    </tr>

                    <tr>
                        <td style="vertical-align: top">Excluded Dates:</td>
                        <td>Enter one date per line (' . get_date_format_help() . ')<br /><textarea name="excluded_transit_dates" rows="10" cols="28">' . $output_excluded_transit_dates . '</textarea></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Shipping Method Availability</h2></th>
                    </tr>
                    <tr>
                        <td>Start Time:</td>
                        <td>
                            <input type="text" id="start_time" name="start_time" size="19" maxlength="19" value="' . prepare_form_data_for_output($start_time, 'date and time') . '" />
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
                            <input type="text" id="end_time" name="end_time" size="19" maxlength="19" value="' . prepare_form_data_for_output($end_time, 'date and time') . '" />
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
                        <td><input type="radio" name="status" id="enabled" value="enabled"' . $status_enabled . ' class="radio" /><label for="enabled">Enabled</label> <input type="radio" name="status" id="disabled" value="disabled"' . $status_disabled . ' class="radio" /><label for="disabled">Disabled</label></td>
                    </tr>
                    <tr>
                        <td>Available on:</td>
                        <td>
                            <table style="width: 100%">
                                <tr>
                                    <td><input type="checkbox" id="available_on_monday" name="available_on_monday" value="1"' . $available_on_monday_checked . ' class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_monday">Monday</label></td>
                                    <td><div id="available_on_monday_cutoff_time_cell" style="' . $available_on_monday_cutoff_time_cell_style . '">Cut-off time: <input type="text" name="available_on_monday_cutoff_time" size="8" maxlength="8" value="' . prepare_form_data_for_output($available_on_monday_cutoff_time, 'time', true) . '" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_tuesday" name="available_on_tuesday" value="1"' . $available_on_tuesday_checked . ' class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_tuesday">Tuesday</label></td>
                                    <td><div id="available_on_tuesday_cutoff_time_cell" style="' . $available_on_tuesday_cutoff_time_cell_style . '">Cut-off time: <input type="text" name="available_on_tuesday_cutoff_time" size="8" maxlength="8" value="' . prepare_form_data_for_output($available_on_tuesday_cutoff_time, 'time', true) . '" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_wednesday" name="available_on_wednesday" value="1"' . $available_on_wednesday_checked . ' class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_wednesday">Wednesday</label></td>
                                    <td><div id="available_on_wednesday_cutoff_time_cell" style="' . $available_on_wednesday_cutoff_time_cell_style . '">Cut-off time: <input type="text" name="available_on_wednesday_cutoff_time" size="8" maxlength="8" value="' . prepare_form_data_for_output($available_on_wednesday_cutoff_time, 'time', true) . '" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_thursday" name="available_on_thursday" value="1"' . $available_on_thursday_checked . ' class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_thursday">Thursday</label></td>
                                    <td><div id="available_on_thursday_cutoff_time_cell" style="' . $available_on_thursday_cutoff_time_cell_style . '">Cut-off time: <input type="text" name="available_on_thursday_cutoff_time" size="8" maxlength="8" value="' . prepare_form_data_for_output($available_on_thursday_cutoff_time, 'time', true) . '" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_friday" name="available_on_friday" value="1"' . $available_on_friday_checked . ' class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_friday">Friday</label></td>
                                    <td><div id="available_on_friday_cutoff_time_cell" style="' . $available_on_friday_cutoff_time_cell_style . '">Cut-off time: <input type="text" name="available_on_friday_cutoff_time" size="8" maxlength="8" value="' . prepare_form_data_for_output($available_on_friday_cutoff_time, 'time', true) . '" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_saturday" name="available_on_saturday" value="1"' . $available_on_saturday_checked . ' class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_saturday">Saturday</label></td>
                                    <td><div id="available_on_saturday_cutoff_time_cell" style="' . $available_on_saturday_cutoff_time_cell_style . '">Cut-off time: <input type="text" name="available_on_saturday_cutoff_time" size="8" maxlength="8" value="' . prepare_form_data_for_output($available_on_saturday_cutoff_time, 'time', true) . '" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="available_on_sunday" name="available_on_sunday" value="1"' . $available_on_sunday_checked . ' class="checkbox" onclick="show_or_hide_cut_off_cell(this)" /> <label for="available_on_sunday">Sunday</label></td>
                                    <td><div id="available_on_sunday_cutoff_time_cell" style="' . $available_on_sunday_cutoff_time_cell_style . '">Cut-off time: <input type="text" name="available_on_sunday_cutoff_time" size="8" maxlength="8" value="' . prepare_form_data_for_output($available_on_sunday_cutoff_time, 'time', true) . '" /> (h:mm AM/PM)</div></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><em>For reference, the current site time is ' . date("g:i A") . '.</em></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="protected">Protected:</label></td>
                        <td><input type="checkbox" id="protected" name="protected" value="1"' . $protected_checked . ' class="checkbox"></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This shipping method will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

    print $output;

} else {

    validate_token_field();
    
    // delete shipping method references in shipping_methods_zones_xref (we do this reguardless of whether we are deleting the shipping method or updating the shipping method)
    $query = "DELETE FROM shipping_methods_zones_xref ".
             "WHERE shipping_method_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // delete excluded transit dates for this shipping method (we do this reguardless of whether we are deleting the shipping method or updating the shipping method)
    $query = "DELETE FROM excluded_transit_dates WHERE shipping_method_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if shipping method was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete shipping_method
        $query = "DELETE FROM shipping_methods WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete records in offer_actions_shipping_methods_xref for this shipping method
        $query = "DELETE FROM offer_actions_shipping_methods_xref WHERE shipping_method_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete shipping cut-offs for this shipping method
        $query = "DELETE FROM shipping_cutoffs WHERE shipping_method_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        db("DELETE FROM ship_date_adjustments WHERE shipping_method_id = '" . escape($_POST['id']) . "'");

        log_activity('shipping method (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
        
    // else shipping method was not selected for delete
    } else {

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

        // update shipping method
        $query = "UPDATE shipping_methods SET
                    name = '" . escape($_POST['name']) . "',
                    description = '" . escape($_POST['description']) . "',
                    code = '" . escape($_POST['code']) . "',
                    status = '" . escape($_POST['status']) . "',
                    start_time = '" . escape(prepare_form_data_for_input($_POST['start_time'], 'date and time')) . "',
                    end_time = '" . escape(prepare_form_data_for_input($_POST['end_time'], 'date and time')) . "',
                    service = '" . e($_POST['service']) . "',
                    realtime_rate = '" . e($realtime_rate) . "',
                    base_rate = '" . escape($base_rate) . "',
                    variable_base_rate = '" . escape($variable_base_rate) . "',
                    base_rate_2 = '" . escape($base_rate_2) . "',
                    base_rate_2_subtotal = '" . escape($base_rate_2_subtotal) . "',
                    base_rate_3 = '" . escape($base_rate_3) . "',
                    base_rate_3_subtotal = '" . escape($base_rate_3_subtotal) . "',
                    base_rate_4 = '" . escape($base_rate_4) . "',
                    base_rate_4_subtotal = '" . escape($base_rate_4_subtotal) . "',
                    primary_weight_rate = '" . escape($primary_weight_rate) . "',
                    primary_weight_rate_first_item_excluded = '" . escape($_POST['primary_weight_rate_first_item_excluded']) . "',
                    secondary_weight_rate = '" . escape($secondary_weight_rate) . "',
                    secondary_weight_rate_first_item_excluded = '" . escape($_POST['secondary_weight_rate_first_item_excluded']) . "',
                    item_rate = '" . escape($item_rate) . "',
                    item_rate_first_item_excluded = '" . escape($_POST['item_rate_first_item_excluded']) . "',
                    base_transit_days = '" . escape($_POST['base_transit_days']) . "',
                    adjust_transit = '" . escape($_POST['adjust_transit']) . "',
                    street_address = '" . escape($_POST['street_address']) . "',
                    po_box = '" . escape($_POST['po_box']) . "',
                    handle_days = '" . e($_POST['handle_days']) . "',
                    handle_mon = '" . e($_POST['handle_mon']) . "',
                    handle_tue = '" . e($_POST['handle_tue']) . "',
                    handle_wed = '" . e($_POST['handle_wed']) . "',
                    handle_thu = '" . e($_POST['handle_thu']) . "',
                    handle_fri = '" . e($_POST['handle_fri']) . "',
                    handle_sat = '" . e($_POST['handle_sat']) . "',
                    handle_sun = '" . e($_POST['handle_sun']) . "',
                    ship_mon = '" . e($_POST['ship_mon']) . "',
                    ship_tue = '" . e($_POST['ship_tue']) . "',
                    ship_wed = '" . e($_POST['ship_wed']) . "',
                    ship_thu = '" . e($_POST['ship_thu']) . "',
                    ship_fri = '" . e($_POST['ship_fri']) . "',
                    ship_sat = '" . e($_POST['ship_sat']) . "',
                    ship_sun = '" . e($_POST['ship_sun']) . "',
                    end_of_day = '" . e(prepare_form_data_for_input($_POST['end_of_day'], 'time')) . "',
                    transit_on_sunday = '" . escape($_POST['transit_on_sunday']) . "',
                    transit_on_saturday = '" . escape($_POST['transit_on_saturday']) . "',
                    available_on_sunday = '" . escape($_POST['available_on_sunday']) . "',
                    available_on_sunday_cutoff_time = '" . escape(prepare_form_data_for_input($_POST['available_on_sunday_cutoff_time'], 'time')) . "',
                    available_on_monday = '" . escape($_POST['available_on_monday']) . "',
                    available_on_monday_cutoff_time = '" . escape(prepare_form_data_for_input($_POST['available_on_monday_cutoff_time'], 'time')) . "',
                    available_on_tuesday = '" . escape($_POST['available_on_tuesday']) . "',
                    available_on_tuesday_cutoff_time = '" . escape(prepare_form_data_for_input($_POST['available_on_tuesday_cutoff_time'], 'time')) . "',
                    available_on_wednesday = '" . escape($_POST['available_on_wednesday']) . "',
                    available_on_wednesday_cutoff_time = '" . escape(prepare_form_data_for_input($_POST['available_on_wednesday_cutoff_time'], 'time')) . "',
                    available_on_thursday = '" . escape($_POST['available_on_thursday']) . "',
                    available_on_thursday_cutoff_time = '" . escape(prepare_form_data_for_input($_POST['available_on_thursday_cutoff_time'], 'time')) . "',
                    available_on_friday = '" . escape($_POST['available_on_friday']) . "',
                    available_on_friday_cutoff_time = '" . escape(prepare_form_data_for_input($_POST['available_on_friday_cutoff_time'], 'time')) . "',
                    available_on_saturday = '" . escape($_POST['available_on_saturday']) . "',
                    available_on_saturday_cutoff_time = '" . escape(prepare_form_data_for_input($_POST['available_on_saturday_cutoff_time'], 'time')) . "',
                    protected = '" . e($_POST['protected']) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // load all zones in array by exploding string that has allowed zone ids separated by commas
        $allowed_zones = explode(',', $_POST['allowed_zones_hidden']);

        // foreach allowed zone insert row in shipping_methods_zones_xref table
        foreach ($allowed_zones as $zone_id) {
            // if zone id is not blank, insert row
            if ($zone_id) {
                $query = "INSERT INTO shipping_methods_zones_xref (shipping_method_id, zone_id) VALUES ('" . escape($_POST['id']) . "', '" . escape($zone_id) . "')";
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
                    '" . escape($_POST['id']) . "',
                    '" . escape($excluded_transit_date) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        log_activity('shipping method (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
    }

    // forward user to view shipping methods screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_shipping_methods.php');
}