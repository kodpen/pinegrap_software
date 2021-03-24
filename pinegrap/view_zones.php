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

switch($_GET['sort']) {
    case 'Name':
        $sort_column = 'name';
        break;

    case 'Base Rate':
        $sort_column = 'base_rate';
        break;

    case 'Primary W. Rate':
        $sort_column = 'primary_weight_rate';
        break;

    case 'Secondary W. Rate':
        $sort_column = 'secondary_weight_rate';
        break;

    case 'Item Rate':
        $sort_column = 'item_rate';
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
            zones.id,
            zones.name,
            zones.base_rate,
            zones.primary_weight_rate,
            zones.secondary_weight_rate,
            zones.item_rate,
            user.user_username as user,
            zones.timestamp
        FROM zones
        LEFT JOIN user ON zones.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$number_of_results = 0;

while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    $name = h($row['name']);
    $base_rate = sprintf("%01.2lf", $row['base_rate'] / 100);
    $primary_weight_rate = sprintf("%01.2lf", $row['primary_weight_rate'] / 100);
    $secondary_weight_rate = sprintf("%01.2lf", $row['secondary_weight_rate'] / 100);
    $item_rate = sprintf("%01.2lf", $row['item_rate'] / 100);
    $username = $row['user'];
    $timestamp = $row['timestamp'];

    $output_link_url ='edit_zone.php?id=' . $id;
    
    $number_of_results++;
    
    $output_rows .=
        '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label">' . $name . '</td>
            <td style="text-align: right;">' . BASE_CURRENCY_SYMBOL . $base_rate . '</td>
            <td style="text-align: right;">' . BASE_CURRENCY_SYMBOL . $primary_weight_rate . '</td>
            <td style="text-align: right;">' . BASE_CURRENCY_SYMBOL . $secondary_weight_rate . '</td>
            <td style="text-align: right;">' . BASE_CURRENCY_SYMBOL . $item_rate . '</td>
            <td>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

print 
output_header() . '
<div id="subnav">
    ' . render(array('template' => 'commerce_subnav.php')) . '
</div>
<div id="button_bar">
    <a href="add_zone.php">Create Shipping Zone</a>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All Shipping Zones</h1>
    <div class="subheading">All geographic areas where your organization can ship products.</div>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
    </div>
    <table class="chart">
        <tr>
            <th>' .asc_or_desc('Name','view_zones'). '</td>
            <th style="text-align: right">' .asc_or_desc('Base Rate','view_zones'). '</td>
            <th style="text-align: right">' .asc_or_desc('Primary W. Rate','view_zones'). '</td>
            <th style="text-align: right">' .asc_or_desc('Secondary W. Rate','view_zones'). '</td>
            <th style="text-align: right">' .asc_or_desc('Item Rate','view_zones'). '</td>
            <th>' .asc_or_desc('Last Modified','view_zones'). '</td>
        </tr>
        ' . $output_rows . '
    </table>
</div>' .
output_footer();
?>