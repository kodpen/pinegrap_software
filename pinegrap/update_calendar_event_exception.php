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
$user = validate_user();
validate_calendars_access($user);

validate_token_field();

// Make sure that the user should be able to update a calendar exception before any data is processed.
if (validate_calendar_event_access($_GET['calendar_event_id']) == false) {
    log_activity("access denied to edit calendar event exception because user does not have access to calendar that the calendar event is in", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

// if user is a basic user and user does not have access to publish calendar events then check if calendar event is published
// in order to check if the user should be able to edit exceptions for this calendar event
if (($user['role'] == 3) && ($user['publish_calendar_events'] == FALSE)) {
    // check to see if the calendar event has been published
    $query = "SELECT published FROM calendar_events WHERE id = '" . escape($_GET['calendar_event_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    // if the event has been published then log and output error
    if ($row['published'] == '1') {
        log_activity("access denied to edit calendar event exception for a published calendar event because user does not have publish rights to calendar events", $_SESSION['sessionusername']);
        output_error('Access denied.');
    }
}

// Make sure that all variables required were passed.
if (
    (isset($_GET['action']))
    && (isset($_GET['calendar_event_id'])) 
    && (isset($_GET['recurrence_number']))
    ) {
    // Convert the GET requests to variables for easy use.
    $action = $_GET['action'];
    $calendar_event_id = $_GET['calendar_event_id'];
    $recurrence_number = $_GET['recurrence_number'];
    
    // Switch based on the action= passed in the query string
    switch ($action) {
        case 'create':
            // Insert the exception into the database.
            $query =
                "INSERT INTO calendar_event_exceptions (
                    calendar_event_id,
                    recurrence_number) 
                VALUES (
                    '" . escape($calendar_event_id) . "',
                    '" . escape($recurrence_number) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // Send a notice to let the user know the exception was created.
            $liveform = new liveform('calendars');
            $liveform->add_notice('The instance of the repeating event has been removed.');
            // Redirect back to calendars.php
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/calendars.php');
            break;

        case 'delete':
            // Get the start time, end time and recurrence information for the event before we remove it.
            $query = 
                "SELECT 
                    start_time,
                    end_time,
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
                    recurrence_month_type
                FROM calendar_events
                WHERE id = '" . escape($calendar_event_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            // Give the returned $row variable names for easy access.
            $start_time = $row['start_time'];
            $end_time = $row['end_time'];
            $recurrence = $row['recurrence'];
            $recurrence_number_row = $row['recurrence_number'];
            $recurrence_type = $row['recurrence_type'];
            $recurrence_day_sun = $row['recurrence_day_sun'];
            $recurrence_day_mon = $row['recurrence_day_mon'];
            $recurrence_day_tue = $row['recurrence_day_tue'];
            $recurrence_day_wed = $row['recurrence_day_wed'];
            $recurrence_day_thu = $row['recurrence_day_thu'];
            $recurrence_day_fri = $row['recurrence_day_fri'];
            $recurrence_day_sat = $row['recurrence_day_sat'];
            $recurrence_month_type = $row['recurrence_month_type'];
            
            // Get all locations used by the selected event
            $query = 
                "SELECT 
                    calendar_event_location_id
                FROM calendar_events_calendar_event_locations_xref
                WHERE calendar_event_id = '" . escape($calendar_event_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $calendar_event_location_ids = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $calendar_event_location_ids[] = $row['calendar_event_location_id'];
            }
            
            // If the event is a recurring event find the date for the selected recurrence
            if ($recurrence == 1) {
                $event_start_date_and_time_parts = explode(' ', $start_time);
                $event_start_date = $event_start_date_and_time_parts[0];
                $event_start_time = $event_start_date_and_time_parts[1];
                
                $event_end_date_and_time_parts = explode(' ', $end_time);
                $event_end_time = $event_end_date_and_time_parts[1];
                
                $event_start_date_parts = explode('-', $event_start_date);
                $event_start_year = $event_start_date_parts[0];
                $event_start_month = $event_start_date_parts[1];
                $event_start_day = $event_start_date_parts[2];
                
                switch ($recurrence_type) {
                    // Daily
                    case 'day':
                        // Loop through all recurrence numbers in order to
                        // calculate the date for this recurrence number.
                        for ($i = 1; $i <= $recurrence_number; $i++) {
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
                                    break 2;
                                }
                            }
                        }
                        
                        break;
                        
                    // Weekly
                    case 'week':
                        $new_time = mktime(0, 0, 0, $event_start_month, $event_start_day + (7 * $recurrence_number), $event_start_year);
                        $event_start_date = date('Y', $new_time) . '-' . date('m', $new_time) . '-' . date('d', $new_time);
                        break;

                    // Monthly
                    case 'month':
                        switch ($recurrence_month_type) {
                            case 'day_of_the_month':
                                $new_time = mktime(0, 0, 0, $event_start_month + $recurrence_number, 1, $event_start_year);
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
                                // Determine which week in the month the event is on.
                                // If the week is 1-4 then we will use that, however if the week is 5,
                                // then we interpret that as the last week.
                            
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

                                $first_day_of_the_month_timestamp = mktime(0, 0, 0, $event_start_month + $recurrence_number, 1, $event_start_year);

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
                        $new_event_start_year = $event_start_year + $recurrence_number;
                        $new_event_start_month = $event_start_month;
                        $new_event_start_day = $event_start_day;
                        
                        // if date is not valid, then get last date for month
                        if (checkdate($new_event_start_month, $new_event_start_day, $new_event_start_year) == false) {
                            $new_event_start_day = date('t', mktime(0, 0, 0, $new_event_start_month, 1, $new_event_start_year));
                        }
                        
                        $event_start_date = $new_event_start_year . '-' . $new_event_start_month . '-' . $new_event_start_day;
                        break;
                }
                // Combine the date together with the time
        	    $event_start_date_and_time = $event_start_date . ' ' . $event_start_time;
                $event_end_date_and_time = $event_start_date . ' ' . $event_end_time;

                foreach ($calendar_event_location_ids as $calendar_event_location_id) {
                    // Check the availability for each location based on the start time and end time of the event.
                    $check_availability = check_calendar_event_location_availability($calendar_event_location_id['calendar_event_location_id'], $event_start_date_and_time, $event_end_date_and_time, $calendar_event_id);
                    // If the availability check was not available, then display an error.
                    if ($check_availability != 'available') {
                        // If the user does not have access to the calendar event that caused the error, do not print out the event name.
                        if (validate_calendar_event_access($check_availability) == false) {
                            $existing_calendar_name_statement = 'there is another event';
                        } else {
                            // Query the database for the calendar event name that is using the location.
                            $query =
                                "SELECT name
                                FROM calendar_events
                                WHERE id = '" . escape($check_availability) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            $existing_calendar_name_statement = 'the event, ' . h($row['name']) . ', is';
                        }
                        // Mark the error
                        $liveform = new liveform('calendars');
                        $liveform->mark_error('', 'The instance of the repeating event could not be unremoved because ' . $existing_calendar_name_statement . ' using the same location during the same time.');
                        
                        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/calendars.php');
                        exit();
                        break;
                    }
                }
            // Else, the event was not a recurring event.
            } else {
                foreach ($calendar_event_location_ids as $calendar_event_location_id) {
                    // Check the availability for each location
                    $check_availability = check_calendar_event_location_availability($calendar_event_location_id['calendar_event_location_id'],$start_time,$end_time,$calendar_event_id);
                    // If the room is not available, display an error to the user
                    if ($check_availability != 'available') {
                        // Check if the user has access to the calendar event, so we can display the name.
                        // If the user does nto have access, do not show the name.
                        if (validate_calendar_event_access($check_availability) == false) {
                            $existing_calendar_name_statement = 'there is another event';
                        } else {
                            // Query the name of the event so that we can display it to the user.
                            $query =
                                "SELECT name
                                FROM calendar_events
                                WHERE id = '" . escape($check_availability) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            $existing_calendar_name_statement = 'the event "' . h($row['name']) . '" is';
                        }
                        // Finally, display the error.
                        $liveform = new liveform('calendars');
                        $liveform->mark_error('', 'The instance of the repeating event could not be unremoved because ' . $existing_calendar_name_statement . ' using the same location during the same time.');

                        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/calendars.php');
                        break;
                    }
                }
            }
            
            // If there was nothing that stopped us yet, delete the calendar event exception.
            $query =
                "DELETE FROM calendar_event_exceptions 
                WHERE 
                    (calendar_event_id = '" . escape($calendar_event_id) . "')
                    AND (recurrence_number = '" . escape($recurrence_number) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // Mark a notice so that the user knows we did something with the selected event.
            $liveform = new liveform('calendars');
            $liveform->add_notice('The instance of the repeating event has been unremoved.');
            
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/calendars.php');
            
            break;
    }
}
?>