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
validate_calendars_access($user);

include_once('liveform.class.php');
$liveform = new liveform('add_calendar_event');

// if the form was not just submitted
if (!$_POST) {
    // if the form has not been submitted yet, then set default values
    if ($liveform->field_in_session('name') == FALSE) {
        $liveform->assign_field_value('show_start_time', '1');
        $liveform->assign_field_value('show_end_time', '1');
        $liveform->assign_field_value('total_recurrence_number', '1');
        $liveform->assign_field_value('recurrence_type', 'day');
        $liveform->assign_field_value('recurrence_day_sun', '1');
        $liveform->assign_field_value('recurrence_day_mon', '1');
        $liveform->assign_field_value('recurrence_day_tue', '1');
        $liveform->assign_field_value('recurrence_day_wed', '1');
        $liveform->assign_field_value('recurrence_day_thu', '1');
        $liveform->assign_field_value('recurrence_day_fri', '1');
        $liveform->assign_field_value('recurrence_day_sat', '1');
        $liveform->assign_field_value('unpublish_days', '45');
        $liveform->assign_field_value('separate_reservations', '1');
        $liveform->assign_field_value('limit_reservations', '1');
        $liveform->assign_field_value('number_of_initial_spots', '1');
        $liveform->assign_field_value('no_remaining_spots_message', '<p>Sorry, we are not accepting any more reservations at this time.</p>');
        $liveform->assign_field_value('reserve_button_label', 'Reserve');
    }

    // Assume that recurrence rows should be hidden until we find out otherwise.
    $output_recurrence_number_and_type_row_style = ' style="display: none"';
    $output_recurrence_days_of_the_week_row_style = ' style="display: none"';
    $output_recurrence_month_type_row_style = ' style="display: none"';

    // If recurrence is enabled, show recurrence rows.
    if ($liveform->get_field_value('recurrence') == 1) {
        $output_recurrence_number_and_type_row_style = '';

        // Determine if other fields should be shown depending on the recurrence type.
        switch ($liveform->get_field_value('recurrence_type')) {
            case 'day':
                $output_recurrence_days_of_the_week_row_style = '';
                break;

            case 'month':
                $output_recurrence_month_type_row_style = '';
                break;
        }
    }
    
    // get all calendars for list of calendars
    $query =
        "SELECT
           id,
           name
        FROM calendars
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $calendars = array();
    
    // loop through all calendars in order to build array
    while ($row = mysqli_fetch_assoc($result)) {
        $calendars[] = $row;
    }
    
    $output_calendars = '';
    
    // loop through all calendars in order to prepare to output list of calendars
    foreach ($calendars as $calendar) {
        // if user has access to calendar, then include this calendar
        if (validate_calendar_access($calendar['id']) == true) {
            $output_calendars .= $liveform->output_field(array('type'=>'checkbox', 'name'=>'calendar_' . $calendar['id'], 'id'=>'calendar_' . $calendar['id'], 'value'=>'1', 'class'=>'checkbox')) . '<label for="calendar_' . $calendar['id'] . '"> ' . h($calendar['name']) . '</label><br />';
        }
    }

    $output_publish_rows = '';
    
    // If the user has access to publish calendar events then output publish rows.
    if (($user['role'] < 3) || ($user['publish_calendar_events'] == 'yes')) {
        $output_publish_rows =
            '<tr>
                <td>
                    <label for="published">Publish to Calendar Pages:</label>
                </td>
                <td>' .
                    $liveform->output_field(array(
                        'type' => 'checkbox',
                        'name' => 'published',
                        'id' => 'published',
                        'value' => '1',
                        'checked' => '',
                        'class' => 'checkbox',
                        'onclick' => 'toggle_calendar_event_published()')) . '
                </td>
            </tr>
            <tr id="unpublish_days_row" style="display: none">
                <td style="padding-left: 2em">
                    <label for="unpublish_days">Unpublish:</label>
                </td>
                <td>' .
                
                    $liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'unpublish_days',
                        'name' => 'unpublish_days',
                        'size' => '3',
                        'maxlength' => '5')) . '&nbsp;
                    
                    day(s) after event ends

                    (leave blank to never unpublish automatically)

                </td>
            </tr>';
    }
    
    // get current time
    $current_time = time();
    
    // get current minute
    $current_minute = date('i', $current_time);
    
    // get number of minutes to add in order to prepare default start time for next half hour
    if ($current_minute < 30) {
        $number_of_minutes_to_add = 30 - $current_minute;
    } else {
        $number_of_minutes_to_add = 60 - $current_minute;
    }
    
    $number_of_seconds_to_add = $number_of_minutes_to_add * 60;
    
    // default start time is next half hour
    $default_start_time = $current_time + $number_of_seconds_to_add;
    
    // default end time is one hour after default start time
    $default_end_time = $default_start_time + 3600;

    // If the date format is month and then day, then use that format.
    if (DATE_FORMAT == 'month_day') {
        $month_and_day_format = 'n/j';

    // Otherwise the date format is day and then month, so use that format.
    } else {
        $month_and_day_format = 'j/n';
    }
    
    $default_start_date_and_time = date($month_and_day_format . '/Y g:i A', $default_start_time);
    $default_end_date_and_time = date($month_and_day_format . '/Y g:i A', $default_end_time);
    
    // set options for recurrence type pick list
    $recurrence_type_options =
        array(
            'Day(s)' => 'day',
            'Week(s)' => 'week',
            'Month(s)' => 'month',
            'Year(s)' => 'year'
        );

    // get all calendar event locations
    $query =
        "SELECT
            id,
            name
        FROM calendar_event_locations
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $calendar_event_locations = array();
    
    // loop through all calendar event locations in order to build array
    while ($row = mysqli_fetch_assoc($result)) {
        $calendar_event_locations[] = $row;
    }
    
    // if there is at least one location, then prepare to output locations
    if (count($calendar_event_locations) >= 1) {
        $output_calendar_event_locations = '';
        
        // loop through all calendar event locations in order to prepare to output list of calendar event locations
        foreach ($calendar_event_locations as $calendar_event_location) {
            $output_calendar_event_locations .= $liveform->output_field(array('type'=>'checkbox', 'name'=>'calendar_event_location_' . $calendar_event_location['id'], 'id'=>'calendar_event_location_' . $calendar_event_location['id'], 'value'=>'1', 'class'=>'checkbox')) . '<label for="calendar_event_location_' . $calendar_event_location['id'] . '"> ' . h($calendar_event_location['name']) . '</label><br />';
        }
        
        $output_locations = 
            '<div class="scrollable" style="max-height: 135px">
                <div style="margin-bottom: 10px">
                    ' . $output_calendar_event_locations . '
                </div>
                Or enter special Location: ' . $liveform->output_field(array('type'=>'text', 'name'=>'location', 'size'=>'20', 'maxlength'=>'100')) . '
            </div>';
        
    } else {
        $output_locations = $liveform->output_field(array('type'=>'text', 'name'=>'location', 'size'=>'20', 'maxlength'=>'100'));
    }

    // assume that reservation rows should be hidden until we find out otherwise
    $limit_reservations_row_style = ' style="display: none"';
    $number_of_initial_spots_row_style = ' style="display: none"';
    $no_remaining_spots_message_row_style = ' style="display: none"';
    $reserve_button_label_row_style = ' style="display: none"';
    $product_id_row_style = ' style="display: none"';
    $next_page_id_row_style = ' style="display: none"';
    
    // if reservations is enabled, show reservation fields
    if ($liveform->get_field_value('reservations') == 1) {
        $limit_reservations_row_style = '';
        
        // if limit reservations is enabled, show certain rows
        if ($liveform->get_field_value('limit_reservations') == 1) {
            $number_of_initial_spots_row_style = '';
            $no_remaining_spots_message_row_style = '';
        }
        
        $reserve_button_label_row_style = '';
        $product_id_row_style = '';
        $next_page_id_row_style = '';
    }
    
    print
        output_header() . '
        ' . get_date_picker_format() . '
        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
        <div id="subnav">
            <h1>[new event]</h1>
        </div>
        <div id="content">
            
            ' . get_wysiwyg_editor_code(array('full_description', 'notes', 'no_remaining_spots_message')) . '
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Event</h1>
            <div class="subheading">Create a new event on one or more calendars, and publish it at any time.</div>
            <form name="form" action="add_calendar_event.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Event Name</h2></th>
                    </tr>
                    <tr>
                        <td>Event Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'50', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Event Schedule</h2></th>
                    </tr>
                    <tr>
                        <td><label for="all_day">All Day Event:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id' => 'all_day', 'name'=>'all_day', 'value'=>'1', 'class'=>'checkbox', 'onclick' => 'toggle_calendar_event_all_day()')) . '</td>
                    </tr>
                    <tr>
                        <td>Start Date<span id="start_time_label"> &amp; Time</span>:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'id'=>'start_time', 'name'=>'start_time', 'value'=>$default_start_date_and_time, 'size'=>'19', 'maxlength'=>'19')) . '  &nbsp; <span id="show_start_time_container">' . $liveform->output_field(array('type' => 'checkbox', 'id' => 'show_start_time', 'name' => 'show_start_time', 'value' => '1', 'class' => 'checkbox')) . '<label for="show_start_time"> Show Start Time</label></span></td>
                    </tr>
                    <tr>
                        <td>End Date<span id="end_time_label"> &amp; Time</span>:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'id'=>'end_time', 'name'=>'end_time', 'value'=>$default_end_date_and_time, 'size'=>'19', 'maxlength'=>'19')) . ' &nbsp; <span id="show_end_time_container">' . $liveform->output_field(array('type' => 'checkbox', 'id' => 'show_end_time', 'name' => 'show_end_time', 'value' => '1', 'class' => 'checkbox')) . '<label for="show_end_time"> Show End Time</label></span></td>
                    </tr>
                    <script>toggle_calendar_event_all_day()</script>
                    <tr>
                        <td><label for="recurrence">Repeat:</label></td>
                        <td>' . $liveform->output_field(array('type' => 'checkbox', 'id' => 'recurrence', 'name' => 'recurrence', 'value' => '1', 'class' => 'checkbox', 'onclick' => 'toggle_calendar_event_recurrence()')) . '</td>
                    </tr>
                    <tr id="recurrence_number_and_type_row"' . $output_recurrence_number_and_type_row_style . '>
                        <td style="padding-left: 2em">For:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'id'=>'total_recurrence_number', 'name'=>'total_recurrence_number', 'size'=>'3', 'maxlength'=>'9', 'style' => 'vertical-align: middle')) . ' ' . $liveform->output_field(array('type'=>'select', 'id'=>'recurrence_type', 'name'=>'recurrence_type', 'style' => 'vertical-align: middle', 'options'=>$recurrence_type_options, 'onchange'=>'change_calendar_event_recurrence_type()')) . '</td>
                    </tr>
                    <tr id="recurrence_days_of_the_week_row"' . $output_recurrence_days_of_the_week_row_style . '>
                        <td style="padding-left: 2em">On:</td>
                        <td>
                            <table>
                                <tr>

                                    <td style="text-align: center" title="Sunday">
                                        <label for="recurrence_day_sun">Sun</label><br>
                                        ' . $liveform->output_field(array(
                                                'type' => 'checkbox',
                                                'id' => 'recurrence_day_sun',
                                                'name' => 'recurrence_day_sun',
                                                'value' => '1',
                                                'class' => 'checkbox')) . '
                                    </td>

                                    <td style="text-align: center" title="Monday">
                                        <label for="recurrence_day_mon">Mon</label><br>
                                        ' . $liveform->output_field(array(
                                                'type' => 'checkbox',
                                                'id' => 'recurrence_day_mon',
                                                'name' => 'recurrence_day_mon',
                                                'value' => '1',
                                                'class' => 'checkbox')) . '
                                    </td>

                                    <td style="text-align: center" title="Tuesday">
                                        <label for="recurrence_day_tue">Tue</label><br>
                                        ' . $liveform->output_field(array(
                                                'type' => 'checkbox',
                                                'id' => 'recurrence_day_tue',
                                                'name' => 'recurrence_day_tue',
                                                'value' => '1',
                                                'class' => 'checkbox')) . '
                                    </td>

                                    <td style="text-align: center" title="Wednesday">
                                        <label for="recurrence_day_wed">Wed</label><br>
                                        ' . $liveform->output_field(array(
                                                'type' => 'checkbox',
                                                'id' => 'recurrence_day_wed',
                                                'name' => 'recurrence_day_wed',
                                                'value' => '1',
                                                'class' => 'checkbox')) . '
                                    </td>

                                    <td style="text-align: center" title="Thursday">
                                        <label for="recurrence_day_thu">Thu</label><br>
                                        ' . $liveform->output_field(array(
                                                'type' => 'checkbox',
                                                'id' => 'recurrence_day_thu',
                                                'name' => 'recurrence_day_thu',
                                                'value' => '1',
                                                'class' => 'checkbox')) . '
                                    </td>

                                    <td style="text-align: center" title="Friday">
                                        <label for="recurrence_day_fri">Fri</label><br>
                                        ' . $liveform->output_field(array(
                                                'type' => 'checkbox',
                                                'id' => 'recurrence_day_fri',
                                                'name' => 'recurrence_day_fri',
                                                'value' => '1',
                                                'class' => 'checkbox')) . '
                                    </td>

                                    <td style="text-align: center" title="Saturday">
                                        <label for="recurrence_day_sat">Sat</label><br>
                                        ' . $liveform->output_field(array(
                                                'type' => 'checkbox',
                                                'id' => 'recurrence_day_sat',
                                                'name' => 'recurrence_day_sat',
                                                'value' => '1',
                                                'class' => 'checkbox')) . '
                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr id="recurrence_month_type_row"' . $output_recurrence_month_type_row_style . '>
                        <td style="padding-left: 2em">By:</td>
                        <td>
                            ' . $liveform->output_field(array('type' => 'radio', 'id' => 'recurrence_month_type_day_of_the_month', 'name' => 'recurrence_month_type', 'value' => 'day_of_the_month', 'checked' => 'checked', 'class' => 'radio')) . '<label for="recurrence_month_type_day_of_the_month"> Day of the Month (e.g. 15th)</label><br />
                            ' . $liveform->output_field(array('type' => 'radio', 'id' => 'recurrence_month_type_day_of_the_week', 'name' => 'recurrence_month_type', 'value' => 'day_of_the_week', 'class' => 'radio')) . '<label for="recurrence_month_type_day_of_the_week"> Day of the Week (e.g. second Sunday)</label><br />
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Event Details</h2></th>
                    </tr>
                    <tr>
                        <td>Short Description:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'short_description', 'size'=>'100', 'maxlength'=>'255')) . '</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Full Description:</td>
                        <td>' . $liveform->output_field(array('type'=>'textarea', 'name'=>'full_description', 'id'=>'full_description', 'style'=>'width: 600px; height: 200px')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Event Location(s)</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Available Event Location(s):</td>
                        <td>
                            ' . $output_locations . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Post to any of my Calendars</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Calendar(s):</td>
                        <td>
                            <div class="scrollable" style="max-height: 135px">
                                ' . $output_calendars . '
                            </div>
                        </td>
                    </tr>
                    ' . $output_publish_rows . '
                    <tr>
                        <th colspan="2"><h2>Display any Special Notes on any Calendar Event Pages</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Special Event Notes:</td>
                        <td>' . $liveform->output_field(array('type'=>'textarea', 'name'=>'notes', 'id'=>'notes', 'style'=>'width: 600px; height: 200px')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Reservations</h2></th>
                    </tr>
                    <tr>
                        <td><label for="reservations">Accept Reservations:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'reservations', 'name'=>'reservations', 'value'=>'1', 'class'=>'checkbox', 'onclick'=>'show_or_hide_reservations()')) . '</td>
                    </tr>
                    <tr id="separate_reservations_row" style="display: none">
                        <td style="padding-left: 2em"><label for="separate_reservations">Manage Reservations Separately<br />for each Repeating Instance:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'separate_reservations', 'name'=>'separate_reservations', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr id="limit_reservations_row"' . $limit_reservations_row_style . '>
                        <td style="padding-left: 2em"><label for="limit_reservations">Limit Reservations:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'limit_reservations', 'name'=>'limit_reservations', 'value'=>'1', 'class'=>'checkbox', 'onclick'=>'show_or_hide_limit_reservations()')) . '</td>
                    </tr>
                    <tr id="number_of_initial_spots_row"' . $number_of_initial_spots_row_style . '>
                        <td style="padding-left: 4em">Initial Spots:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'number_of_initial_spots', 'size'=>'3', 'maxlength'=>'9')) . '</td>
                    </tr>
                    <tr id="no_remaining_spots_message_row"' . $no_remaining_spots_message_row_style . '>
                        <td style="padding-left: 4em; vertical-align: top">No Longer Available Message:</td>
                        <td>' . $liveform->output_field(array('type'=>'textarea', 'id'=>'no_remaining_spots_message', 'name'=>'no_remaining_spots_message', 'style'=>'width: 600px; height: 200px')) . '</td>
                    </tr>
                    <tr id="reserve_button_label_row"' . $reserve_button_label_row_style . '>
                        <td style="padding-left: 2em">Reserve Button Label:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'reserve_button_label', 'size'=>'30', 'maxlength'=>'50')) . '</td>
                    </tr>
                    <tr id="product_id_row"' . $product_id_row_style . '>
                        <td style="padding-left: 2em">Reservation Product:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'product_id', 'options'=>get_product_options())) . '</td>
                    </tr>
                    <tr id="next_page_id_row"' . $next_page_id_row_style . '>
                        <td style="padding-left: 2em">Next Page:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'next_page_id', 'options'=>get_page_options())) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');

    // If all day is enabled, then validate start and end date & time fields in a certain way.
    if ($liveform->get_field_value('all_day') == 1) {
        $liveform->validate_required_field('start_time', 'Start Date is required.');
        $liveform->validate_required_field('end_time', 'End Date is required.');

        // if there is not already an error for this field and submitted date is invalid, prepare error
        if (($liveform->check_field_error('start_time') == false) && (validate_date($liveform->get_field_value('start_time')) == false)) {
            $liveform->mark_error('start_time', 'Please enter a valid start date.');
        }
        
        // if there is not already an error for this field and submitted date is invalid, prepare error
        if (($liveform->check_field_error('end_time') == false) && (validate_date($liveform->get_field_value('end_time')) == false)) {
            $liveform->mark_error('end_time', 'Please enter a valid end date.');
        }
        
        // if start date and end date do not have errors and end date is less than start date, prepare error
        if (
            ($liveform->check_field_error('start_time') == false)
            && ($liveform->check_field_error('end_time') == false)
            && (prepare_form_data_for_input($liveform->get_field_value('end_time'), 'date') < prepare_form_data_for_input($liveform->get_field_value('start_time'), 'date'))
        ) {
            $liveform->mark_error('end_time', 'Please enter an end date that is on or after the start date.');
        }

        // If there are no errors for the start and end time, then add a time on to the date,
        // for use later, because for all day we still technically store a full date and time (not just the date).
        if (
            ($liveform->check_field_error('start_time') == false)
            && ($liveform->check_field_error('end_time') == false)
        ) {
            $start_time = $liveform->get_field_value('start_time') . ' 12:00 AM';
            $end_time = $liveform->get_field_value('end_time') . ' 11:59 PM';
        }

    // Otherwise all day is disabled, so validate start and end date & time fields in a different way.
    } else {
        $liveform->validate_required_field('start_time', 'Start Date &amp; Time is required.');
        $liveform->validate_required_field('end_time', 'End Date &amp; Time is required.');

        // if there is not already an error for this field and submitted date & time is invalid, prepare error
        if (($liveform->check_field_error('start_time') == false) && (validate_date_and_time($liveform->get_field_value('start_time')) == false)) {
            $liveform->mark_error('start_time', 'Please enter a valid start date &amp; time.');
        }
        
        // if there is not already an error for this field and submitted date & time is invalid, prepare error
        if (($liveform->check_field_error('end_time') == false) && (validate_date_and_time($liveform->get_field_value('end_time')) == false)) {
            $liveform->mark_error('end_time', 'Please enter a valid end date &amp; time.');
        }
        
        // if start time and end time do not have errors and end_time is less than or equal to start time, prepare error
        if (
            ($liveform->check_field_error('start_time') == false)
            && ($liveform->check_field_error('end_time') == false)
            && (prepare_form_data_for_input($liveform->get_field_value('end_time'), 'date and time') <= prepare_form_data_for_input($liveform->get_field_value('start_time'), 'date and time'))
        ) {
            $liveform->mark_error('end_time', 'Please enter an end date &amp; time that is after the start date &amp; time.');
        }

        // If there are no errors for the start and end time, then set the exact value they entered for the start and end time.
        // We don't need to add a time because the user already entered that when all day is disabled.
        if (
            ($liveform->check_field_error('start_time') == false)
            && ($liveform->check_field_error('end_time') == false)
        ) {
            $start_time = $liveform->get_field_value('start_time');
            $end_time = $liveform->get_field_value('end_time');
        }
    }

    // If recurrence is enabled then validate recurrence fields.
    if ($liveform->get_field_value('recurrence') == 1) {
        $liveform->validate_required_field('total_recurrence_number', 'Repeat number is required when you enable Repeat.');
        $liveform->validate_required_field('recurrence_type', 'Repeat type is required when you enable Repeat.');

        // If there is not already an error for the recurrence number field,
        // and value is not a number greater than 0, then add error.
        if (
            ($liveform->check_field_error('total_recurrence_number') == false)
            &&
            (
                (is_numeric($liveform->get_field_value('total_recurrence_number')) == false)
                || ($liveform->get_field_value('total_recurrence_number') <= 0)
            )
        ) {
            $liveform->mark_error('total_recurrence_number', 'Please enter a valid number that is greater than 0 for the repeat number.');
        }

        // If the user selected the event to repeat daily and did not select
        // any days of the week, then output error.
        if (
            ($liveform->get_field_value('recurrence_type') == 'day')
            && ($liveform->get_field_value('recurrence_day_sun') == 0)
            && ($liveform->get_field_value('recurrence_day_mon') == 0)
            && ($liveform->get_field_value('recurrence_day_tue') == 0)
            && ($liveform->get_field_value('recurrence_day_wed') == 0)
            && ($liveform->get_field_value('recurrence_day_thu') == 0)
            && ($liveform->get_field_value('recurrence_day_fri') == 0)
            && ($liveform->get_field_value('recurrence_day_sat') == 0)
        ) {
            $liveform->mark_error('recurrence_days_of_the_week', 'Please select at least one day of the week for the daily repeat.');
        }

        // If the recurrence type is set to month then validate month type.
        if ($liveform->get_field_value('recurrence_type') == 'month') {
            $liveform->validate_required_field('recurrence_type', 'Repeat month type is required.');
        }
    }

    // If there are no errors for the schedule fields,
    // then continue to check if there are any location conflicts.
    if (
        ($liveform->check_field_error('start_time') == false)
        && ($liveform->check_field_error('end_time') == false)
        && ($liveform->check_field_error('total_recurrence_number') == false)
        && ($liveform->check_field_error('recurrence_type') == false)
        && ($liveform->check_field_error('recurrence_days_of_the_week') == false)
        && ($liveform->check_field_error('recurrence_month_type') == false)
    ) {
        // get all calendar event locations
        $query =
            "SELECT
                id,
                name
            FROM calendar_event_locations
            ORDER BY name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $calendar_event_locations = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            $calendar_event_locations[] = $row;
        }
        
        $calendar_event_location_selected = false;
        
        // loop through all calendar event locations in order to determine if one was selected
        foreach ($calendar_event_locations as $calendar_event_location) {
            // if a location was selected, take note
            if ($liveform->get_field_value('calendar_event_location_' . $calendar_event_location['id']) == 1) {
                $calendar_event_location_selected = true;
                break;
            }
        }
        
        // if a location was selected
        if ($calendar_event_location_selected == true) {
            // If the event is not a recurring event
            if ($liveform->get_field_value('recurrence') == 0) {
                foreach ($calendar_event_locations as $calendar_event_location) {
                    // if a location was selected, take note
                    if ($liveform->get_field_value('calendar_event_location_' . $calendar_event_location['id']) == 1) {
                        $check_availability = check_calendar_event_location_availability($calendar_event_location['id'], $start_time, $end_time);
                        if ($check_availability != 'available') {
                            if (validate_calendar_event_access($check_availability) == false) {
                                $existing_calendar_name_statement = '';
                            } else {
                                $query =
                                    "SELECT name
                                    FROM calendar_events
                                    WHERE id = '" . escape($check_availability) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                $row = mysqli_fetch_assoc($result);
                    
                                $existing_calendar_name_statement = ' by the event, ' . h($row['name']);
                            }
                            
                            $liveform->mark_error('calendar_event_location_' . $calendar_event_location['id'], 'The location, ' . h($calendar_event_location['name']) . ', is already in use during this time' . $existing_calendar_name_statement . '. Please choose a different time or location.');
                        }
                    }
                }
            // If the event is a recurring event
            } else {
                $event_start_date_and_time = prepare_form_data_for_input($start_time, 'date and time');
                $event_end_date_and_time = prepare_form_data_for_input($end_time, 'date and time');

                $recurrence_number = $liveform->get_field_value('total_recurrence_number');
                $recurrence_type = $liveform->get_field_value('recurrence_type');
                $recurrence_day_sun = $liveform->get_field_value('recurrence_day_sun');
                $recurrence_day_mon = $liveform->get_field_value('recurrence_day_mon');
                $recurrence_day_tue = $liveform->get_field_value('recurrence_day_tue');
                $recurrence_day_wed = $liveform->get_field_value('recurrence_day_wed');
                $recurrence_day_thu = $liveform->get_field_value('recurrence_day_thu');
                $recurrence_day_fri = $liveform->get_field_value('recurrence_day_fri');
                $recurrence_day_sat = $liveform->get_field_value('recurrence_day_sat');
                $recurrence_month_type = $liveform->get_field_value('recurrence_month_type');
                
                // split event start date and time into parts
                $event_start_date_and_time_parts = explode(' ', $event_start_date_and_time);
                $event_start_date = $event_start_date_and_time_parts[0];
                $event_start_time = $event_start_date_and_time_parts[1];
                
                // split event end date and time into parts
                $event_end_date_and_time_parts = explode(' ', $event_end_date_and_time);
                $event_end_time = $event_end_date_and_time_parts[1];
                
                // if recurrence number is greater than zero, then split event start date into parts, that we will use later
                if ($recurrence_number > 0) {
                    $event_start_date_parts = explode('-', $event_start_date);
                    $event_start_year = $event_start_date_parts[0];
                    $event_start_month = $event_start_date_parts[1];
                    $event_start_day = $event_start_date_parts[2];

                    // If this is a monthly event and the month type is "day of the week",
                    // then determine which week in the month the event is on.
                    // If the week is 1-4 then we will use that, however if the week is 5,
                    // then we interpret that as the last week.
                    if (
                        ($recurrence_type == 'month')
                        && ($recurrence_month_type == 'day_of_the_week')
                    ) {
                        $day_of_the_week = date('l', strtotime($event_start_date));
                        $first_day_of_the_month_timestamp = strtotime($event_start_year . '-' . $event_start_month . '-01');

                        $week = '';

                        // Create a loop in order to determine which week event falls on.
                        // We only loop through 4 weeks, because we are going to set "last" below for 5th week.
                        for ($week_index = 0; $week_index <= 3; $week_index++) {
                            // If the event is in this week, then remember the week number and break out of this loop.
                            if ($event_start_date == date('Y-m-d', strtotime('+' . $week_index . ' week ' . $day_of_the_week, $first_day_of_the_month_timestamp))) {
                                $week = $week_index + 1;
                                break;
                            }
                        }

                        // If a week was not found, then that means it falls on the 5th week,
                        // so set it to be the last week.
                        if ($week == '') {
                            $week = 'last';
                        }
                    }
                }
                
                // loop in order to create a new event for each recurrence
                for ($i = 0; $i <= $recurrence_number; $i++) {
                    // if recurrence number is greater than 0, then adjust event start date
                    if ($i > 0) {
                        // adjust event start date depending on recurrence type
                        switch ($recurrence_type) {
                            // Daily
                            case 'day':
                                $count = 0;

                                // Loop through days in the future until we find a date that is valid
                                // based on the valid days of the week that were selected.
                                while (true) {
                                    $new_time = strtotime('+1 day', strtotime($event_start_date));
                                    $event_start_date = date('Y-m-d', $new_time);
                                    $day_of_the_week = strtolower(date('D', $new_time));

                                    // If this day of the week is valid for this calendar event,
                                    // then we have found a valid date, so break out of the loop.
                                    if (${'recurrence_day_' . $day_of_the_week} == 1) {
                                        break;
                                    }

                                    $count++;

                                    // If we have already looped 7 times, then something is wrong,
                                    // so break out of this loop and the recurrence loop above.
                                    // This should never happen but is added just in case in order to
                                    // prevent an endless loop.
                                    if ($count == 7) {
                                        break 3;
                                    }
                                }

                                break;
                                
                            // Weekly
                            case 'week':
                                $new_time = mktime(0, 0, 0, $event_start_month, $event_start_day + (7 * $i), $event_start_year);
                                $event_start_date = date('Y', $new_time) . '-' . date('m', $new_time) . '-' . date('d', $new_time);
                                break;

                            // Monthly
                            case 'month':
                                switch ($recurrence_month_type) {
                                    case 'day_of_the_month':
                                        $new_time = mktime(0, 0, 0, $event_start_month + $i, 1, $event_start_year);
                                        $new_event_start_year = date('Y', $new_time);
                                        $new_event_start_month = date('m', $new_time);
                                        $new_event_start_day = $event_start_day;

                                        // if date is not valid, then get last date for month
                                        if (checkdate($new_event_start_month, $new_event_start_day, $new_event_start_year) == false) {
                                            $new_event_start_day = date('t', mktime(0, 0, 0, $new_event_start_month, 1, $new_event_start_year));
                                        }

                                        $event_start_date = $new_event_start_year . '-' . $new_event_start_month . '-' . $new_event_start_day;

                                        break;

                                    case 'day_of_the_week':
                                        $first_day_of_the_month_timestamp = mktime(0, 0, 0, $event_start_month + $i, 1, $event_start_year);

                                        // If the week is 1-4 then find the date in a certain way.
                                        if ($week != 'last') {
                                            $week_index = $week - 1;

                                            $new_time = strtotime('+' . $week_index . ' week ' . $day_of_the_week, $first_day_of_the_month_timestamp);

                                        // Otherwise the week is last, so find the date in a different way.
                                        } else {
                                            $last_day_of_the_month_timestamp = strtotime(date('Y-m-t', $first_day_of_the_month_timestamp));

                                            // If the last day of the month happens to be the right day of the week,
                                            // then thats that day that we want.
                                            if (date('l', $last_day_of_the_month_timestamp) == $day_of_the_week) {
                                                $new_time = $last_day_of_the_month_timestamp;

                                            // Otherwise find the day of the week that we want in the last week of the month.
                                            } else {
                                                $new_time = strtotime('last ' . $day_of_the_week, $last_day_of_the_month_timestamp);
                                            }
                                        }

                                        $event_start_date = date('Y-m-d', $new_time);

                                        break;
                                }

                                break;
                            
                            // Yearly
                            case 'year':
                                $new_event_start_year = $event_start_year + $i;
                                $new_event_start_month = $event_start_month;
                                $new_event_start_day = $event_start_day;
                                
                                // if date is not valid, then get last date for month
                                if (checkdate($new_event_start_month, $new_event_start_day, $new_event_start_year) == false) {
                                    $new_event_start_day = date('t', mktime(0, 0, 0, $new_event_start_month, 1, $new_event_start_year));
                                }
                                
                                $event_start_date = $new_event_start_year . '-' . $new_event_start_month . '-' . $new_event_start_day;
                                break;
                        }
                    }
                    
                    // Add the time to the start and end date
                    $event_start_date_and_time = $event_start_date . ' ' . $event_start_time;
                    $event_end_date_and_time = $event_start_date . ' ' . $event_end_time;
                    
                    foreach ($calendar_event_locations as $calendar_event_location) {
                        // if a location was selected, take note
                        if ($liveform->get_field_value('calendar_event_location_' . $calendar_event_location['id']) == 1) {
                            // Check the rooms availability
                            $check_availability = check_calendar_event_location_availability($calendar_event_location['id'], $event_start_date_and_time, $event_end_date_and_time);
                            
                            if ($check_availability != 'available') {
                                if (validate_calendar_event_access($check_availability) == false) {
                                    $existing_calendar_name_statement = '';
                                } else {
                                    $query =
                                        "SELECT name
                                        FROM calendar_events
                                        WHERE id = '" . escape($check_availability) . "'";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                    
                                    $row = mysqli_fetch_assoc($result);
                                    
                                    $existing_calendar_name_statement = ' by the event, ' . h($row['name']);
                                }
                                
                                $liveform->mark_error('calendar_event_location_' . $calendar_event_location['id'], 'The location, ' . h($calendar_event_location['name']) . ', is already in use during this time' . $existing_calendar_name_statement . '. Please choose a different time or location.');
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    // get all calendars
    $query =
        "SELECT id
        FROM calendars
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $calendars = array();
    
    // loop through all calendars to build array
    while ($row = mysqli_fetch_assoc($result)) {
        $calendars[] = $row;
    }
    
    $calendar_selected = false;
    
    // loop through all calendars
    foreach ($calendars as $calendar) {
        // if a calendar was selected and the user has access to the calendar, take note
        if (($liveform->get_field_value('calendar_' . $calendar['id']) == 1) && (validate_calendar_access($calendar['id']) == true)) {
            $calendar_selected = true;
            break;
        }
    }
    
    // if no calendars were selected, then output error
    if ($calendar_selected == false) {
        $liveform->mark_error('', 'Please select at least one calendar.');
    }
    
    // if reservations is enabled, then validate required fields
    if ($liveform->get_field_value('reservations') == 1) {
        $liveform->validate_required_field('reserve_button_label', 'Reserve Button Label is required.');
        $liveform->validate_required_field('product_id', 'Reservation Product is required.');
        $liveform->validate_required_field('next_page_id', 'Next Page is required.');
    }
    
    // if there is an error, forward user back to add calendar event screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_calendar_event.php');
        exit();
    }
    
    // If recurrence was disabled then set recurrence number to 0,
    // in order to prevent issues with logic that outputs calendar.
    if ($liveform->get_field_value('recurrence') == 0) {
        $liveform->assign_field_value('total_recurrence_number', 0);
    }
    
    // if notes has real content, then use notes content
    if ((strip_tags(escape($liveform->get_field_value('notes'))) != '')
        && (strip_tags(escape($liveform->get_field_value('notes'))) != '&nbsp;')) {
        $notes = escape(prepare_rich_text_editor_content_for_input($liveform->get_field_value('notes')));
        
    // else notes does not have real content, so set to empty string
    } else {
        $notes = '';
    }
    
    // If user has access to publish calendar events then allow publish fields to be updated.
    if (($user['role'] < 3) || ($user['publish_calendar_events'] == 'yes')) {

        $sql_publish_columns =
            'published,
            unpublish_days,';

        $sql_publish_values =
            "'" . e($liveform->get_field_value('published')) . "',
            '" . e($liveform->get_field_value('unpublish_days')) . "',";
        
    // else do not update the field
    } else {
        $sql_publish_columns = '';
        $sql_publish_values = '';
    }
    
    // create calendar event
    $query =
        "INSERT INTO calendar_events (
            name,
            $sql_publish_columns
            short_description,
            full_description,
            notes,
            all_day,
            start_time,
            end_time,
            show_start_time,
            show_end_time,
            recurrence,
            recurrence_number,
            recurrence_type,
            recurrence_day_sun,
            recurrence_day_mon,
            recurrence_day_tue,
            recurrence_day_wed,
            recurrence_day_thu,
            recurrence_day_fri,
            recurrence_day_sat,
            recurrence_month_type,
            location,
            reservations,
            separate_reservations,
            limit_reservations,
            number_of_initial_spots,
            no_remaining_spots_message,
            reserve_button_label,
            product_id,
            next_page_id,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            $sql_publish_values
            '" . escape($liveform->get_field_value('short_description')) . "',
            '" . escape(prepare_rich_text_editor_content_for_input($liveform->get_field_value('full_description'))) . "',
            '" . $notes . "',
            '" . escape($liveform->get_field_value('all_day')) . "',
            '" . escape(prepare_form_data_for_input($start_time, 'date and time')) . "',
            '" . escape(prepare_form_data_for_input($end_time, 'date and time')) . "',
            '" . escape($liveform->get_field_value('show_start_time')) . "',
            '" . escape($liveform->get_field_value('show_end_time')) . "',
            '" . escape($liveform->get_field_value('recurrence')) . "',
            '" . escape($liveform->get_field_value('total_recurrence_number')) . "',
            '" . escape($liveform->get_field_value('recurrence_type')) . "',
            '" . escape($liveform->get_field_value('recurrence_day_sun')) . "',
            '" . escape($liveform->get_field_value('recurrence_day_mon')) . "',
            '" . escape($liveform->get_field_value('recurrence_day_tue')) . "',
            '" . escape($liveform->get_field_value('recurrence_day_wed')) . "',
            '" . escape($liveform->get_field_value('recurrence_day_thu')) . "',
            '" . escape($liveform->get_field_value('recurrence_day_fri')) . "',
            '" . escape($liveform->get_field_value('recurrence_day_sat')) . "',
            '" . escape($liveform->get_field_value('recurrence_month_type')) . "',
            '" . escape($liveform->get_field_value('location')) . "',
            '" . escape($liveform->get_field_value('reservations')) . "',
            '" . escape($liveform->get_field_value('separate_reservations')) . "',
            '" . escape($liveform->get_field_value('limit_reservations')) . "',
            '" . escape($liveform->get_field_value('number_of_initial_spots')) . "',
            '" . escape(prepare_rich_text_editor_content_for_input($liveform->get_field_value('no_remaining_spots_message'))) . "',
            '" . escape($liveform->get_field_value('reserve_button_label')) . "',
            '" . escape($liveform->get_field_value('product_id')) . "',
            '" . escape($liveform->get_field_value('next_page_id')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $calendar_event_id = mysqli_insert_id(db::$con);
    
    // loop through all calendars, in order to create cross reference records
    foreach ($calendars as $calendar) {
        // if the calendar was selected and the user has access to the calendar
        if (($liveform->get_field_value('calendar_' . $calendar['id']) == 1) && (validate_calendar_access($calendar['id']) == true)) {
            $query =
                "INSERT INTO calendar_events_calendars_xref (
                    calendar_event_id,
                    calendar_id)
                VALUES (
                    '" . $calendar_event_id . "',
                    '" . $calendar['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
    
    // loop through all calendar event locations, in order to create cross reference records
    foreach ($calendar_event_locations as $calendar_event_location) {
        // if the calendar event location was selected, then create cross reference record
        if ($liveform->get_field_value('calendar_event_location_' . $calendar_event_location['id']) == 1) {
            $query =
                "INSERT INTO calendar_events_calendar_event_locations_xref (
                    calendar_event_id,
                    calendar_event_location_id)
                VALUES (
                    '" . $calendar_event_id . "',
                    '" . $calendar_event_location['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
    
    log_activity('calendar event (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);

    $liveform_calendars = new liveform('calendars');
    $liveform_calendars->add_notice('The calendar event has been created.');
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/calendars.php');
    
    $liveform->remove_form();
}
?>