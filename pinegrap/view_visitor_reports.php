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
validate_visitors_access($user);

include_once('liveform.class.php');
$liveform = new liveform('view_visitor_reports');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['stats']['view_visitor_reports'][$key] = trim($value);
    }
}

$output_web_statistics_link = '';

// if an external web stats link is supplied in the settings, then prepare link to web stats
if ((defined('STATS_URL') == true) && (STATS_URL != '')) {
	$output_web_statistics_link = '<a href="'.STATS_URL.'" target="_blank">Website Analytics</a>';
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

switch ($_SESSION['software']['stats']['view_visitor_reports']['sort']) {
    case 'Name':
        $sort_column = 'visitor_reports.name';
        break;

    case 'Created':
        $sort_column = 'visitor_reports.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'visitor_reports.last_modified_timestamp';
        break;

    default:
        $sort_column = 'visitor_reports.last_modified_timestamp';
        $_SESSION['software']['stats']['view_visitor_reports']['sort'] = 'Last Modified';
        $_SESSION['software']['stats']['view_visitor_reports']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['stats']['view_visitor_reports']['order']) == false) {
    $_SESSION['software']['stats']['view_visitor_reports']['order'] = 'asc';
}

$my_visitor_reports = 0;
$all_visitor_reports = 0;

// get all visitor reports
$query =
    "SELECT
        visitor_reports.id,
        visitor_reports.name,
        created_user.user_username as created_username,
        visitor_reports.created_timestamp,
        last_modified_user.user_username as last_modified_username,
        visitor_reports.last_modified_timestamp
    FROM visitor_reports
    LEFT JOIN user AS created_user ON visitor_reports.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON visitor_reports.last_modified_user_id = last_modified_user.user_id
    ORDER BY $sort_column " . escape($_SESSION['software']['stats']['view_visitor_reports']['order']);

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$visitor_reports = array();

while ($row = mysqli_fetch_assoc($result)) {
    $visitor_reports[] = $row;
    // Add one to all visitor reports
    $all_visitor_reports++;
    // Add one to my visitor reports
    $my_visitor_reports++;
}

$output_rows = '';

// if there is at least one result to display
if ($visitor_reports) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($visitor_reports);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_visitor_reports.php?screen=' . $previous . $keys_and_values . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_visitor_reports.php?screen=\' + this.options[this.selectedIndex].value) + \'' . $keys_and_values . '\'">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_visitor_reports.php?screen=' . $next . $keys_and_values . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($visitor_reports) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        if ($visitor_reports[$key]['created_username']) {
            $created_username = $visitor_reports[$key]['created_username'];
        } else {
            $created_username = '[Unknown]';
        }

        if ($visitor_reports[$key]['last_modified_username']) {
            $last_modified_username = $visitor_reports[$key]['last_modified_username'];
        } else {
            $last_modified_username = '[Unknown]';
        }

        $output_link_url  = 'view_visitor_report.php?id=' . $visitor_reports[$key]['id'];
        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                <td class="chart_label">' . h($visitor_reports[$key]['name']) . '</td>
                <td>' . get_relative_time(array('timestamp' => $visitor_reports[$key]['created_timestamp'])) . ' by ' . h($created_username) . '</td>
                <td>' . get_relative_time(array('timestamp' => $visitor_reports[$key]['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>
            </tr>';
    }
}

print
    output_header() . '
    <div id="subnav">
        ' . $output_web_statistics_link . '
    </div>
    <div id="button_bar">
        <a href="view_visitor_report.php">Create Visitor Report</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Visitor Reports</h1>
        <div class="subheading">Create real-time reports on all website visitor activities.</div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($my_visitor_reports) . ' I can access. ' . number_format($all_visitor_reports) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['stats']['view_visitor_reports']['sort'], $_SESSION['software']['stats']['view_visitor_reports']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['stats']['view_visitor_reports']['sort'], $_SESSION['software']['stats']['view_visitor_reports']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['stats']['view_visitor_reports']['sort'], $_SESSION['software']['stats']['view_visitor_reports']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();