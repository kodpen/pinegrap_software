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
$liveform = new liveform('view_calendar_event_locations');

// if user does not have access to calendar event location, then output error
if ($user['role'] > 2) {
    log_activity("access denied to view calendar event locations because user does not have access", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['calendars']['view_calendar_event_locations'][$key] = trim($value);
    }
}

$keys_and_values = '';

// If a screen was passed and it is a positive integer, then use it.
// These checks are necessary in order to avoid SQL errors below for a bogus screen value.
if (
    $_REQUEST['screen']
    and is_numeric($_REQUEST['screen'])
    and $_REQUEST['screen'] > 0
    and $_REQUEST['screen'] == round($_REQUEST['screen'])
) {
    $screen = (int) $_REQUEST['screen'];

// Otherwise, use the default, which is the first screen.
} else {
    $screen = 1;
}

switch ($_SESSION['software']['calendars']['view_calendar_event_locations']['sort']) {
    case 'Name':
        $sort_column = 'calendar_event_locations.name';
        break;

    case 'Created':
        $sort_column = 'calendar_event_locations.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'calendar_event_locations.last_modified_timestamp';
        break;

    default:
        $sort_column = 'calendar_event_locations.last_modified_timestamp';
        $_SESSION['software']['calendars']['view_calendar_event_locations']['sort'] = 'Last Modified';
        $_SESSION['software']['calendars']['view_calendar_event_locations']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['calendars']['view_calendar_event_locations']['order']) == false) {
    $_SESSION['software']['calendars']['view_calendar_event_locations']['order'] = 'asc';
}

$all_calendar_event_locations = 0;

// get all calendar locations
$query =
    "SELECT
        calendar_event_locations.id,
        calendar_event_locations.name,
        calendar_event_locations.created_timestamp,
        calendar_event_locations.last_modified_timestamp,
        calendar_event_locations.last_modified_user_id,
        created_user.user_username as created_username,
        last_modified_user.user_username as last_modified_username
    FROM calendar_event_locations
    LEFT JOIN user as created_user ON calendar_event_locations.created_user_id = created_user.user_id
    LEFT JOIN user as last_modified_user ON calendar_event_locations.last_modified_user_id = last_modified_user.user_id
    ORDER BY $sort_column " . escape($_SESSION['software']['calendars']['view_calendar_event_locations']['order']);

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$locations = array();

while ($row = mysqli_fetch_assoc($result)) {
    $locations[] = $row;
    // Add one to all calendar event locations
    $all_calendar_event_locations++;
}

$output_rows = '';

// if there is at least one result to display
if ($locations) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($locations);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_calendar_event_locations.php?screen=' . $previous . $keys_and_values . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_calendar_event_locations.php?screen=\' + this.options[this.selectedIndex].value) + \'' . $keys_and_values . '\'">';

        // build HTML output for links to screens
        for ($i = 1; $i <= $number_of_screens; $i++) {
            // if this number is the current screen, then select option
            if ($i == $screen) {
                $selected = ' selected="selected"';
            // else this number is not the current screen, so do not select option
            } else {
                $selected = '';
            }

            $output_screen_links .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
        }

        $output_screen_links .= '</select>';
    }

    // build Next button if necessary
    $next = $screen + 1;
    // if next screen is less than or equal to the total number of screens, output next link
    if ($next <= $number_of_screens) {
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_calendar_event_locations.php?screen=' . $next . $keys_and_values . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($locations) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        if ($locations[$key]['created_username']) {
            $created_username = $locations[$key]['created_username'];
        } else {
            $created_username = '[Unknown]';
        }

        if ($locations[$key]['last_modified_username']) {
            $last_modified_username = $locations[$key]['last_modified_username'];
        } else {
            $last_modified_username = '[Unknown]';
        }

        $output_link_url = 'edit_calendar_event_location.php?id=' . $locations[$key]['id'];

        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                <td class="chart_label">' . h($locations[$key]['name']) . '</td>
                <td>' . get_relative_time(array('timestamp' => $locations[$key]['created_timestamp'])) . ' by ' . h($created_username) . '</td>
                <td>' . get_relative_time(array('timestamp' => $locations[$key]['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>
            </tr>';
    }
}

if ($user['role'] < 3) {
    $calendar_event_locations_link = '<a href="view_calendar_event_locations.php">Event Locations</a>';
}

print
    output_header() . '
    <div id="subnav">
        <table>
            <tr>
                <td>
                    <ul>
                        <li><a href="view_calendars.php">My Calendars</a></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li>' . $calendar_event_locations_link . '</li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>
    <div id="button_bar">
        <a href="add_calendar_event_location.php">Create Location</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Event Locations</h1>
        <div class="subheading">All event locations shared by all calendars.</div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_calendar_event_locations) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['calendars']['view_calendar_event_locations']['sort'], $_SESSION['software']['calendars']['view_calendar_event_locations']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['calendars']['view_calendar_event_locations']['sort'], $_SESSION['software']['calendars']['view_calendar_event_locations']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['calendars']['view_calendar_event_locations']['sort'], $_SESSION['software']['calendars']['view_calendar_event_locations']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();
?>