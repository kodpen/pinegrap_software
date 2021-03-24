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

    case 'Zip Code Required':
        $sort_column = 'zip_code_required';
        break;

    case 'Transit Adjustment':
        $sort_column = 'transit_adjustment_days';
        break;

    case 'Default':
        $sort_column = 'default_selected';
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

if (($sort_column == 'timestamp') && (!$_GET['order'])) {
    $asc_desc = 'desc';
}

$query = "SELECT
            countries.id,
            countries.name,
            countries.code,
            countries.zip_code_required,
            countries.transit_adjustment_days,
            countries.default_selected,
            user.user_username as user,
            countries.timestamp
        FROM countries
        LEFT JOIN user ON countries.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    $name = h($row['name']);
    $code = h($row['code']);
    $zip_code_required = $row['zip_code_required'];
    $transit_adjustment_days = $row['transit_adjustment_days'];
    $default_selected = $row['default_selected'];
    $username = $row['user'];
    $timestamp = $row['timestamp'];

    $zip_code_required_check_mark = '';

    if ($zip_code_required) {
        $zip_code_required_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">';
    }

    if ($row['default_selected']) {
        $default_selected = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    } else {
        $default_selected = '';
    }
    $output_link_url = 'edit_country.php?id=' . $id;
    
    $number_of_results++;
    
    $output_rows .= '
        <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label">' . $name . '</td>
            <td>' . $code . '</td>
            <td style="text-align: center">' . $zip_code_required_check_mark . '</td>
            <td style="text-align: right;">' . $transit_adjustment_days . '</td>
            <td style="text-align: center">' . $default_selected . '</td>
            <td>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

echo
output_header() . '
<div id="subnav">
    ' . render(array('template' => 'commerce_subnav.php')) . '
</div>
<div id="button_bar">
    <a href="add_country.php">Add Country</a>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All Countries</h1>
    <div class="subheading">All countries that are valid for billing address and shipping address selection.</div>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
    </div>
    <table class="chart">
        <tr>
            <th>' .asc_or_desc('Name','view_countries'). '</th>
            <th>' .asc_or_desc('Code','view_countries'). '</th>
            <th style="text-align: center">' . asc_or_desc('Zip Code Required','view_countries') . '</th>
            <th style="text-align: right;">' .asc_or_desc('Transit Adjustment','view_countries'). '</th>
            <th style="text-align: center">' .asc_or_desc('Default','view_countries'). '</th>
            <th>' .asc_or_desc('Last Modified','view_countries'). '</th>
        </tr>
        ' . $output_rows . '
    </table>
</div>' .
output_footer();