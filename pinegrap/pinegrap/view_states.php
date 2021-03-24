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

    case 'Code':
        $sort_column = 'code';
        break;

    case 'Country':
        $sort_column = 'country_name';
        break;

    case 'Last Modified':
        $sort_column = 'timestamp';
        break;
    default:
        $sort_column = 'name';
}

if ($_GET['sort']) {
    $asc_desc = $_GET['order'];
} else {
    $asc_desc = 'asc';
}

if (($sort_column == 'name') && (!$_GET['order'])) {
    $asc_desc = 'asc';
}

$query = "SELECT
            states.id,
            states.name,
            states.code,
            countries.name as country_name,
            user.user_username as user,
            states.timestamp
        FROM states
        LEFT JOIN countries ON states.country_id = countries.id
        LEFT JOIN user ON states.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    $name = h($row['name']);
    $code = h($row['code']);
    $country_name = $row['country_name'];
    $username = $row['user'];
    $timestamp = $row['timestamp'];

    $output_link_url = 'edit_state.php?id=' . $id;
    
    $number_of_results++;
    
    $output_rows .= '
        <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label">' . $name . '</td>
            <td>' . $code . '</td>
            <td>' . $country_name . '</td>
            <td>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

print
output_header() . '
<div id="subnav">
    ' . render(array('template' => 'commerce_subnav.php')) . '
</div>
<div id="button_bar">
    <a href="add_state.php">Add State</a>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All States</h1>
    <div class="subheading">All states/provinces that are valid for billing address and shipping address selection.</div>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
    </div>
    <table class="chart">
        <tr>
            <th>' .asc_or_desc('Name','view_states'). '</th>
            <th>' .asc_or_desc('Code','view_states'). '</th>
            <th>' .asc_or_desc('Country','view_states'). '</th>
            <th>' .asc_or_desc('Last Modified','view_states'). '</th>
        </tr>
        ' . $output_rows . '
    </table>
</div>' .
output_footer();
?>