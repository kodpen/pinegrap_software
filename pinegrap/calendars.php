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

$liveform = new liveform('calendars');

if (isset($_GET['calendar_id']) == true) {
    $_SESSION['software']['calendars']['calendar_id'] = $_GET['calendar_id'];
}

if (isset($_GET['view']) == true) {
    $_SESSION['software']['calendars']['view'] = $_GET['view'];
}

if (isset($_GET['status']) == true) {
    $_SESSION['software']['calendars']['status'] = $_GET['status'];
}

if (isset($_GET['date']) == true) {
    $_SESSION['software']['calendars']['date'] = $_GET['date'];
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

// loop through all calendars in order to prepare calendar pick list
while ($row = mysqli_fetch_assoc($result)) {
    // if user has access to calendar, then include this calendar
    if (validate_calendar_access($row['id']) == true) {
        $calendars[] = $row;
        
        // Get current calendar name
        if ($row['id'] == $_SESSION['software']['calendars']['calendar_id']) {
            $calendar_name = $row['name'];
        }
    }
}

echo
    output_header() . '
    <div id="subnav">
        <h1>' . h($calendar_name) . '</h1>
    </div>
    <div id="button_bar">
        <a href="add_calendar_event.php">Create Calendar Event</a>
        <a href="edit_calendar.php?id=' . h($_SESSION['software']['calendars']['calendar_id']) . '">Edit Calendar Properties</a>
    </div>
    <div id="content">
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>Edit Calendar</h1>
        <div class="subheading">View and update this calendar.</div>
        ' . get_calendar($_SESSION['software']['calendars']['calendar_id'], $calendars, $_SESSION['software']['calendars']['view'], $_SESSION['software']['calendars']['status'], $user, $_SESSION['software']['calendars']['date'], PATH . SOFTWARE_DIRECTORY . '/edit_calendar_event.php') . '
    </div>
    ' . output_footer();

$liveform->remove_form();