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

function get_calendar_view_screen_content($properties) {
    
    $current_page_id = $properties['current_page_id'];
    $default_view = $properties['default_view'];
    $number_of_upcoming_events = $properties['number_of_upcoming_events'];
    $calendar_event_view_page_id = $properties['calendar_event_view_page_id'];
    $device_type = $properties['device_type'];
    
    // remember the current URL to this calendar view, in case we need to forward user to it in the future
    $_SESSION['software']['last_calendar_view_url'] = get_request_uri();
    
    if (isset($_GET['calendar_id']) == true) {
        $_SESSION['software']['calendar_views'][$current_page_id]['calendar_id'] = $_GET['calendar_id'];
    }

    // if the default view is upcoming, then the view should be upcoming, regardless of what is in the query string or session
    if ($default_view == 'upcoming') {
        $_SESSION['software']['calendar_views'][$current_page_id]['view'] = 'upcoming';
    
    // else if the view is set in the query string, then use that
    } elseif (isset($_GET['view']) == true) {
        $_SESSION['software']['calendar_views'][$current_page_id]['view'] = $_GET['view'];

    // else if mobile viewing, then override default and set to weekly
    } elseif ($device_type == 'mobile') {
        $_SESSION['software']['calendar_views'][$current_page_id]['view'] = 'weekly';

    // else if there is no view in the session, then use the default view
    } elseif (isset($_SESSION['software']['calendar_views'][$current_page_id]['view']) == false) {
        $_SESSION['software']['calendar_views'][$current_page_id]['view'] = $default_view;
    }

    if (isset($_GET['date']) == true) {
        $_SESSION['software']['calendar_views'][$current_page_id]['date'] = $_GET['date'];
    }

    // get all calendars for calendar pick list
    $query =
        "SELECT
           id,
           name
        FROM calendars
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $calendars = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $calendars[] = $row;
    }
    
    // loop through all calendars in order to prepare calendar pick list
    foreach ($calendars as $key => $calendar) {
        // check if calendar should be included in this calendar view
        $query =
            "SELECT calendar_id
            FROM calendar_views_calendars_xref
            WHERE
                (calendar_id = '" . escape($calendar['id']) . "')
                AND (page_id = '" . escape($current_page_id) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if calendar is not included in this view, then remove calendar from array
        if (mysqli_num_rows($result) == 0) {
            unset($calendars[$key]);
        }
    }
    
    $link = '';
    
    if ($calendar_event_view_page_id) {
        $link = PATH . encode_url_path(get_page_name($calendar_event_view_page_id));
    }
    
    return get_calendar($_SESSION['software']['calendar_views'][$current_page_id]['calendar_id'], $calendars, $_SESSION['software']['calendar_views'][$current_page_id]['view'], 'published', '', $_SESSION['software']['calendar_views'][$current_page_id]['date'], $link, $number_of_upcoming_events);
}