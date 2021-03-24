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

$number_of_results = 0;

switch($_GET['sort']) {
    case 'Name':
        $sort_column = 'name';
        break;

    case 'Arrival Date':
        $sort_column = 'arrival_date';
        break;

    case 'Status':
        $sort_column = 'status';
        break;

    case 'Start Date':
        $sort_column = 'start_date';
        break;

    case 'End Date':
        $sort_column = 'end_date';
        break;

    case 'Last Modified':
        $sort_column = 'timestamp';
        break;
    default:
        $sort_column = 'timestamp';
        $asc_desc = 'DESC';
}

if ($_GET['sort']) {
    $asc_desc = $_GET['order'];
} else {
    $asc_desc = 'asc';
}

if (($sort_column == 'timestamp') && (!$_GET['order'])) {
    $asc_desc = 'desc';
}

// Get the current date so that later we can figure out if arrival dates are active.
$current_date = date('Y-m-d');

$query = "SELECT
            arrival_dates.id as id,
            arrival_dates.name as name,
            arrival_dates.arrival_date as arrival_date,
            arrival_dates.status as status,
            arrival_dates.start_date as start_date,
            arrival_dates.end_date as end_date,
            user.user_username as user,
            arrival_dates.timestamp as timestamp
        FROM arrival_dates
        LEFT JOIN user ON arrival_dates.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_array($result)) {

    $id = $row['id'];
    $name = $row['name'];
    $arrival_date = prepare_form_data_for_output($row['arrival_date'], 'date');
    $status = $row['status'];
    $start_date = prepare_form_data_for_output($row['start_date'], 'date');
    $end_date = prepare_form_data_for_output($row['end_date'], 'date');
    $username = $row['user'];
    $timestamp = $row['timestamp'];
    
    $output_link_url = 'edit_arrival_date.php?id=' . $id;

    // If this arrival date is active, then use class that shows green color.
    if (
        ($row['status'] == 'enabled')
        && ($row['start_date'] <= $current_date)
        && ($row['end_date'] >= $current_date)
    ) {
        $output_status_class = 'status_enabled';
    
    // Otherwise this arrival date is not active, so use class that shows red color.
    } else {
        $output_status_class = 'status_disabled';
    }
     
    $number_of_results++;
     
    $output_rows .=
        '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label ' . $output_status_class . '" nowrap>' . $name . '</td>
            <td nowrap>' . $arrival_date . '</td>
            <td nowrap>' . ucwords($status) . '</td>
            <td nowrap>' . $start_date . '</td>
            <td nowrap>' . $end_date . '</td>
            <td nowrap>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

print 
output_header() . '
<div id="subnav">
    ' . render(array('template' => 'commerce_subnav.php')) . '
</div>
<div id="button_bar">
    <a href="add_arrival_date.php">Create Arrival Date</a>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All Shipping Arrival Dates</h1>
    <div class="subheading">All arrival dates for determining shipping methods for all deliveries.</div>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
    </div>
    <table class="chart">
        <tr>
            <th nowrap>' .asc_or_desc('Name','view_arrival_dates'). '</td>
            <th nowrap>' .asc_or_desc('Arrival Date','view_arrival_dates'). '</td>
            <th nowrap>' .asc_or_desc('Status','view_arrival_dates'). '</td>
            <th nowrap>' .asc_or_desc('Start Date','view_arrival_dates'). '</td>
            <th nowrap>' .asc_or_desc('End Date','view_arrival_dates'). '</td>
            <th nowrap>' .asc_or_desc('Last Modified','view_arrival_dates'). '</td>
        </tr>
        ' . $output_rows . '
    </table>
</div>' .
output_footer();
?>