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

validate_token_field();

// if user does not have access to calendar event, then output error
if (validate_calendar_event_access($_GET['id']) == false) {
    log_activity("access denied to edit calendar event because user does not have access to calendar that the calendar event is in", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

// get original calendar event's info
$query =
    "SELECT
        name,
        published,
        unpublish_days,
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
        next_page_id
    FROM calendar_events WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$calendar_event = mysqli_fetch_assoc($result);

// if the event is published and the user does not have rights to publish calendar events then do not publish new event
if (($calendar_event['published'] == 1) && ($user['role'] == 3) && ($user['publish_calendar_events'] == FALSE)) {
    $calendar_event['published'] = 0;
}

// create new calendar event
$query =
    "INSERT INTO calendar_events (
        name,
        published,
        unpublish_days,
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
        '" . escape($calendar_event['name']) . "',
        '" . e($calendar_event['published']) . "',
        '" . e($calendar_event['unpublish_days']) . "',
        '" . escape($calendar_event['short_description']) . "',
        '" . escape($calendar_event['full_description']) . "',
        '" . escape($calendar_event['notes']) . "',
        '" . escape($calendar_event['all_day']) . "',
        '" . escape($calendar_event['start_time']) . "',
        '" . escape($calendar_event['end_time']) . "',
        '" . escape($calendar_event['show_start_time']) . "',
        '" . escape($calendar_event['show_end_time']) . "',
        '" . escape($calendar_event['recurrence']) . "',
        '" . escape($calendar_event['recurrence_number']) . "',
        '" . escape($calendar_event['recurrence_type']) . "',
        '" . escape($calendar_event['recurrence_day_sun']) . "',
        '" . escape($calendar_event['recurrence_day_mon']) . "',
        '" . escape($calendar_event['recurrence_day_tue']) . "',
        '" . escape($calendar_event['recurrence_day_wed']) . "',
        '" . escape($calendar_event['recurrence_day_thu']) . "',
        '" . escape($calendar_event['recurrence_day_fri']) . "',
        '" . escape($calendar_event['recurrence_day_sat']) . "',
        '" . escape($calendar_event['recurrence_month_type']) . "',
        '" . escape($calendar_event['location']) . "',
        '" . escape($calendar_event['reservations']) . "',
        '" . escape($calendar_event['separate_reservations']) . "',
        '" . escape($calendar_event['limit_reservations']) . "',
        '" . escape($calendar_event['number_of_initial_spots']) . "',
        '" . escape($calendar_event['no_remaining_spots_message']) . "',
        '" . escape($calendar_event['reserve_button_label']) . "',
        '" . escape($calendar_event['product_id']) . "',
        '" . escape($calendar_event['next_page_id']) . "',
        '" . $user['id'] . "',
        UNIX_TIMESTAMP(),
        '" . $user['id'] . "',
        UNIX_TIMESTAMP())";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$new_calendar_event_id = mysqli_insert_id(db::$con);

// get all calendars that original event is in
$query = "SELECT calendar_id as id FROM calendar_events_calendars_xref WHERE calendar_event_id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$calendars = array();

// loop through all calendars in order to build array
while ($row = mysqli_fetch_assoc($result)) {
    $calendars[] = $row;
}

// loop through all calendars in order to assign new event to them
foreach ($calendars as $calendar) {
    // if the user has access to this calendar then assign new calendar event to this calendar
    if (validate_calendar_access($calendar['id']) == TRUE) {
        $query =
            "INSERT INTO calendar_events_calendars_xref (
                calendar_event_id,
                calendar_id)
            VALUES (
                '" . $new_calendar_event_id . "',
                '" . $calendar['id'] . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
}

// get all calendar event exceptions for the original event in order to assign them to the new event
$query = "SELECT recurrence_number FROM calendar_event_exceptions WHERE calendar_event_id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$calendar_event_exceptions = array();

// loop through all exceptions in order to build array
while ($row = mysqli_fetch_assoc($result)) {
    $calendar_event_exceptions[] = $row;
}

// loop through all exceptions in order to assign exceptions to new event
foreach ($calendar_event_exceptions as $calendar_event_exception) {
    $query =
        "INSERT INTO calendar_event_exceptions (
            calendar_event_id,
            recurrence_number)
        VALUES (
            '" . $new_calendar_event_id . "',
            '" . $calendar_event_exception['recurrence_number'] . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}

log_activity('calendar event (' . $calendar_event['name'] . ') was duplicated', $_SESSION['sessionusername']);

include_once('liveform.class.php');
$liveform = new liveform('edit_calendar_event', $new_calendar_event_id);

// add notice that the calendar event has been duplicated
$liveform->add_notice('The calendar event has been duplicated, and you are now editing the duplicate.');

header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_calendar_event.php?id=' . $new_calendar_event_id);
?>