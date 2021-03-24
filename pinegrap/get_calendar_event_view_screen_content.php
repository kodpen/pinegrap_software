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

function get_calendar_event_view_screen_content($properties)
{
    global $user;
    
    $current_page_id = $properties['current_page_id'];
    $notes = $properties['notes'];
    $back_button_label = $properties['back_button_label'];
    $editable = $properties['editable'];
    $calendar_event_id = $properties['calendar_event_id'];

    // If a calendar event id was not passed to this function by a process like
    // update search index, then this page is being called via a web browser,
    // so set the calendar event id to the value from the query string.
    if ($calendar_event_id == '') {
        $calendar_event_id = $_GET['id'];
    }
    
    include_once('liveform.class.php');
    $liveform = new liveform('reserve_calendar_event', $calendar_event_id);
    
    // if the user did not come from the control panel, then prepare to output calendar event
    if ((isset($_GET['from']) == false) || ($_GET['from'] != 'control_panel')) {
        // get calendar event data
        $calendar_event = get_calendar_event($calendar_event_id, $_REQUEST['recurrence_number']);
        
        // if calendar event could not be found, then output error
        if ($calendar_event == FALSE) {
            output_error('The requested calendar event could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        
        // if calendar event is not published, output error
        if ($calendar_event['published'] == 0) {
            output_error('The requested calendar event is not currently published. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        
        // get all calendars that belong to this event
        $query =
            "SELECT
                calendar_id
            FROM calendar_events_calendars_xref
            WHERE calendar_event_id = '" . escape($calendar_event_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $event_calendars = array();
        
        // Step through each row and add it to the array
        while ($row = mysqli_fetch_assoc($result)) {
            $event_calendars[] = $row;
        }
        
        $where_calendar_id = '';
        
        // Loop through each calendar so that we can check if we are allowed to output the event on this calendar view
        foreach ($event_calendars as $event_calendar) {
            // check if calendar event is allowed in this view
            $where_calendar_id .= "(calendar_id = '" . escape($event_calendar['calendar_id']) . "') OR ";
        }
        
        // Remove the last OR from the where_calendar_id variable and enclose it
        if (mb_strlen($where_calendar_id) > 0) {
            $where_calendar_id = mb_substr($where_calendar_id, 0, -4);
            $where_calendar_id = '(' . $where_calendar_id . ')';
        }
        
        // Query the rules for this calendar event view page.
        $query =
            "SELECT calendar_id
            FROM calendar_event_views_calendars_xref
            WHERE
                " . $where_calendar_id . "
                AND (page_id = '" . escape($current_page_id) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if calendar event is not allowed in this view, then output error
        if (mysqli_num_rows($result) == 0) {
            output_error('The requested calendar event is not allowed in this calendar event view. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        
        $output_location = '';
        
        // if locations are not blank, then output them to the page
        if ($calendar_event['location'] != '') {
            $output_location = 'Location(s): ' . h($calendar_event['location']) . '<br />';
        }
        
        $output_recurrence_number = '';
        
        // if there is a recurrence number, then output it
        if ($_REQUEST['recurrence_number'] != '') {
            $output_recurrence_number = '&recurrence_number=' . h($_REQUEST['recurrence_number']);
        }
        
        // if edit mode is on and if the user has access to edit this calendar event, then output edit button for images
        if (
            ($editable == TRUE) 
            && (
                ($user['role'] < 3) 
                || (
                    ($user['manage_calendars'] == TRUE) 
                    && (validate_calendar_event_access($calendar_event['id']) == TRUE)
                )
            )
        ) {
            // add the edit container to images in content
            $calendar_event['full_description'] = add_edit_button_for_images('calendar_event', $calendar_event['id'], $calendar_event['full_description']);
        }
        
        $output_notes = '';
        
        // if notes are enabled for this calendar event view and the notes for this calendar event are not blank, then prepare to output notes
        if (($notes == 1) && ($calendar_event['notes_content'] != '')) {
            $output_notes = 
                '<fieldset class="software_fieldset" style="margin-bottom: 15px; clear: both;">
                    <legend class="software_legend">Notes</legend>
                    <div style="margin: 10px">
                        ' . $calendar_event['notes_content'] . '
                    </div>
                </fieldset>';
        }
        
        $output_no_remaining_spots_message = '';
        $output_reserve_button = '';
        
        // if reservations is enabled and reservation product still exists and is enabled
        // and the event is not in the past, then prepare reservation info.
        if (
            ($calendar_event['reservations'] == 1)
            && ($calendar_event['product_id'] != '')
            && ($calendar_event['product_enabled'] == 1)
            && (strtotime($calendar_event['end_date_and_time']) >= time())
        ) {
            // if reservations are not limited
            // or the number of remaining spots is greater than 0
            // and inventory is disabled for product
            // or the inventory quantity is greater than 0
            // or the product is allowed to be backordered
            // then output reserve form
            if (
                (
                    ($calendar_event['limit_reservations'] == 0)
                    || ($calendar_event['number_of_remaining_spots'] > 0)
                )
                &&
                (
                    ($calendar_event['inventory'] == 0)
                    || ($calendar_event['inventory_quantity'] > 0)
                    || ($calendar_event['backorder'] == 1)
                )
            ) {
                $output_reserve_form =
                    '<form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/reserve_calendar_event.php" method="post" style="margin: 0em; display: inline">
                        ' . get_token_field() . '
                        ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                        ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'page_id', 'value'=>$current_page_id)) . '
                        ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'calendar_event_id', 'value'=>$calendar_event['id'])) . '
                        ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'recurrence_number', 'value'=>$_REQUEST['recurrence_number'])) . '
                        ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'require_cookies', 'value'=>'true')) . '
                        <input type="submit" name="submit" value="' . h($calendar_event['reserve_button_label']) . '" class="software_input_submit_primary submit_button" />
                    </form>';
            
            // else reservation is not available, so output message(s)
            } else {
                // if reservations are limited and the number of remaining spots is 0, then output no remaining spots message
                if (
                    ($calendar_event['limit_reservations'] == 1)
                    && ($calendar_event['number_of_remaining_spots'] == 0)
                ) {
                    $output_no_remaining_spots_message = $calendar_event['no_remaining_spots_message'];
                }
                
                // if inventory is enabled for product and the inventory quantity is 0 and product is not allowed to be backordered, then output out of stock message
                if (
                    ($calendar_event['inventory'] == 1)
                    && ($calendar_event['inventory_quantity'] == 0)
                    && ($calendar_event['backorder'] == 0)
                ) {
                    // if the message is not blank, then add a space for separation
                    if ($output_no_remaining_spots_message != '') {
                        $output_no_remaining_spots_message .= ' ';
                    }
                    
                    $output_no_remaining_spots_message .= $calendar_event['out_of_stock_message'];
                }
            }
        }
        
        $output_back_button = '';
        
        // if back button label is not blank, then prepare to output back button
        if ($back_button_label != '') {
            // if there is a reserve form, then add separation and set class
            if ($output_reserve_form != '') {
                $output_back_button .= '&nbsp;&nbsp;';
                $output_class = 'software_button_secondary';
                
            // else there is no reserve form, so set class
            } else {
                $output_class = 'software_button_primary';
            }
            
            $output_back_button .= '<a href="' . h($_SESSION['software']['last_calendar_view_url']) . '" class="' . $output_class . '">' . h($back_button_label) . '</a>';
        }
        
        $output_reserve_form_and_or_back_button = '';
        
        // if there is a reserve form or back button, then output area for both
        if (($output_reserve_form != '') || ($output_back_button != '')) {
            $output_reserve_form_and_or_back_button = '<div style="margin-top: 1em; margin-bottom: 1.5em">' . $output_reserve_form . $output_back_button . '</div>';
        }
        
        $output =
            $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <div style="margin-bottom: 10px" class="software_calendar_event"><h2>' . h($calendar_event['name']) . '</h2></div>
            <div class="heading" style="border:none;">
                ' . $calendar_event['date_and_time_range'] . '<br />
                ' . $output_location . '
            </div>
            <div class="software_icalendar_link"><a class="software_button_tiny_secondary" style="font-weight: normal;" href="' . URL_SCHEME . HOSTNAME . OUTPUT_PATH . h(encode_url_path(get_page_name($current_page_id))) . '?id=' . h($calendar_event_id) . $output_recurrence_number . '&icalendar=true">Add Event to my Personal Calendar</a></div>
            <div class="data" style="margin-top: 10px; margin-bottom: 15px">
                ' . $calendar_event['full_description'] . '
            </div>
            ' . $output_notes . '
            ' . $output_no_remaining_spots_message . '
            ' . $output_reserve_form_and_or_back_button;
        
        $liveform->remove_form();

        return $output;
    
    // else the user did come from the control panel, so prepare to output filler
    } else {
        return '<p class="software_notice">Calendar Event details will be displayed here when this page is linked to from a Calendar Page Type.</p>';
    }
}
?>