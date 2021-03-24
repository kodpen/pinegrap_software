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

    case 'Tax Rate':
        $sort_column = 'tax_rate';
        break;

    case 'Last Modified':
        $sort_column = 'timestamp';
        break;
    default:
        $sort_column = 'timestamp';
}

if ($_GET['sort']) {
    $asc_desc = $_GET['order'];
} else {
    $asc_desc = 'asc';
}

if (($sort_column == 'timestamp') && (!$_GET['order'])) {
    $asc_desc = 'desc';
}

$query = "SELECT
            tax_zones.id,
            tax_zones.name,
            tax_zones.tax_rate,
            user.user_username as user,
            tax_zones.timestamp
        FROM tax_zones
        LEFT JOIN user ON tax_zones.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    $name = $row['name'];
    $tax_rate = $row['tax_rate'];
    $username = $row['user'];
    $timestamp = $row['timestamp'];

    $output_link_url = 'edit_tax_zone.php?id=' . $id;
    
    $number_of_results++;
    
    $output_rows .= '
        <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label" nowrap>' . h($name) . '</td>
            <td style="text-align: left" nowrap>' . $tax_rate . '%</td>
            <td nowrap>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

print
output_header() . '
<div id="subnav">
    ' . render(array('template' => 'commerce_subnav.php')) . '
</div>
<div id="button_bar">
    <a href="add_tax_zone.php">Create Tax Zone</a>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All Tax Zones</h1>
    <div class="subheading">All geographic areas where your organization collects tax for any product.</div>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
    </div>
    <table class="chart">
        <tr>
            <th nowrap>' .asc_or_desc('Name','view_tax_zones'). '</th>
            <th nowrap>' .asc_or_desc('Tax Rate','view_tax_zones'). '</th>
            <th nowrap>' .asc_or_desc('Last Modified','view_tax_zones'). '</th>
        </tr>
        ' . $output_rows . '
    </table>
</div>' .
output_footer();
?>