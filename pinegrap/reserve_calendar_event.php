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

validate_token_field();

require_cookies();

include_once('liveform.class.php');
$liveform = new liveform('reserve_calendar_event', $_POST['calendar_event_id']);
$liveform->add_fields_to_session();

// get values from form
$send_to = $liveform->get_field_value('send_to');
$page_id = $liveform->get_field_value('page_id');
$calendar_event_id = $liveform->get_field_value('calendar_event_id');
$recurrence_number = $liveform->get_field_value('recurrence_number');

// get page properties
$query =
    "SELECT
        page_name,
        page_folder as folder_id,
        page_type
    FROM page
    WHERE page_id = '" . escape($page_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if the page was not found, then log activity and output error
if (mysqli_num_rows($result) == 0) {
    log_activity('access denied to reserve calendar event because page does not exist', $_SESSION['sessionusername']);
    output_error('You do not have access to reserve this calendar event because the page does not exist. <a href="javascript:history.go(-1);">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);

$page_name = $row['page_name'];
$folder_id = $row['folder_id'];
$page_type = $row['page_type'];

// if the page type is not calendar event view, then log activity and output error
if ($page_type != 'calendar event view') {
    log_activity('access denied to reserve calendar event for page (' . $page_name . ') because page is not a calendar event view', $_SESSION['sessionusername']);
    output_error('You do not have access to reserve this calendar event because the page is not a calendar event view. <a href="javascript:history.go(-1);">Go back</a>.');
}

$calendar_event = get_calendar_event($calendar_event_id, $recurrence_number);

// if the calendar event is not published, then log activity and output error
if ($calendar_event['published'] == 0) {
    log_activity('access denied to reserve calendar event (' . $calendar_event['name'] . ') for page (' . $page_name . ') because calendar event is not published.', $_SESSION['sessionusername']);
    output_error('You do not have access to reserve this calendar event because it is not published. <a href="javascript:history.go(-1);">Go back</a>.');
}

// get all calendars that belong to this event
$query = "SELECT calendar_id FROM calendar_events_calendars_xref WHERE calendar_event_id = '" . escape($calendar_event_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$calendar_ids = array();

// loop through calendars in order to add them to array
while ($row = mysqli_fetch_assoc($result)) {
    $calendar_ids[] = $row['calendar_id'];
}

$where_calendars = "";

// loop through each calendar id so that we can check if the calendar event is allowed to appear on the calendar event view page
foreach ($calendar_ids as $calendar_id) {
    // if this is not the first calendar in the list, then output separator
    if ($where_calendars != '') {
        $where_calendars .= " OR ";
    }
    
    $where_calendars .= "(calendar_id = '" . escape($calendar_id) . "')";
}

// check if this calendar event is in a calendar that is allowed to appear on this page
$query =
    "SELECT calendar_id
    FROM calendar_event_views_calendars_xref
    WHERE
        (" . $where_calendars . ")
        AND (page_id = '" . escape($page_id) . "')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if calendar event is not allowed in this view, then log activity and output error
if (mysqli_num_rows($result) == 0) {
    log_activity('access denied to reserve calendar event (' . $calendar_event['name'] . ') for page (' . $page_name . ') because calendar event is not allowed in this calendar event view.', $_SESSION['sessionusername']);
    output_error('You do not have access to reserve this calendar event because it is not allowed in this calendar event view. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if reservations is disabled for this calendar event, then log activity and output error
if ($calendar_event['reservations'] == 0) {
    log_activity('access denied to reserve calendar event (' . $calendar_event['name'] . ') for page (' . $page_name . ') because reservations are not enabled for this calendar event.', $_SESSION['sessionusername']);
    output_error('Reservations are not allowed for this calendar event. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if the product no longer exists for the calendar event, then log activity and output error
if ($calendar_event['product_id'] == '') {
    log_activity('access denied to reserve calendar event (' . $calendar_event['name'] . ') for page (' . $page_name . ') because product no longer exists for calendar event.', $_SESSION['sessionusername']);
    output_error('You do not have access to reserve this calendar event because the product for it no longer exists. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if the event is in the past, then log activity and output error
if (strtotime($calendar_event['end_date_and_time']) < time()) {
    log_activity('access denied to reserve calendar event (' . $calendar_event['name'] . ') for page (' . $page_name . ') because the calendar event has ended.', $_SESSION['sessionusername']);
    output_error('Sorry, you may not reserve this event because the event has ended. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if user does not have access to calendar event view page, log and output error
if (check_view_access($folder_id) == false) {
    log_activity('access denied to reserve calendar event (' . $calendar_event['name'] . ') for page (' . $page_name . ') because visitor does not have view access to page', $_SESSION['sessionusername']);
    output_error('You do not have access to reserve this calendar event. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if reservations are limited and there are no remaining spots for this calendar event,
// or if inventory is enabled for product and the inventory quantity is 0 and this product is not allowed to be backordered
// then add error and forward visitor back to previous screen
if (
    (
        ($calendar_event['limit_reservations'] == 1)
        && ($calendar_event['number_of_remaining_spots'] == 0)
    )
    ||
    (
        ($calendar_event['inventory'] == 1)
        && ($calendar_event['inventory_quantity'] == 0)
        && ($calendar_event['backorder'] == 0)
    )
) {
    $liveform->mark_error('', 'Sorry, there are no remaining spots.');
    header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
    exit();
}

// we have checked for all errors, so now we may proceed with adding order item to order
initialize_order();

// if this is not a recurring event or if separate reservations is disabled, then set recurrence number to 0 in preparation for adding order item
if (
    ($calendar_event['total_recurrence_number'] == 0)
    || ($calendar_event['separate_reservations'] == 0)
) {
    $recurrence_number = 0;
}

// add order item to order
$order_item_id = add_order_item($calendar_event['product_id'], 1, 0, '', '', $calendar_event_id, $recurrence_number);

// An item has been added to the order, so offers for this order
// need to be refreshed, so that the subtotal in a cart region is accurate.
update_order_item_prices();
apply_offers_to_cart();

// send user to next page
header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($calendar_event['next_page_id'])));

$liveform->remove_form();
?>