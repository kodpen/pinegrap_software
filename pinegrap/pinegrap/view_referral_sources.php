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

if (($sort_column == 'sort_order') && (!$_GET['order'])) {
    $asc_desc = 'asc';
} else {
    $asc_desc = 'desc';
}

$query = "SELECT
            referral_sources.id as id,
            referral_sources.name as name,
            referral_sources.code as code,
            user.user_username as user,
            referral_sources.timestamp as timestamp
        FROM referral_sources
        LEFT JOIN user ON referral_sources.user = user.user_id
        ORDER BY $sort_column $asc_desc";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    $name = $row['name'];
    $code = $row['code'];
    $username = $row['user'];
    $timestamp = $row['timestamp'];
    
    $output_link_url = 'edit_referral_source.php?id=' . $id;
    
    $number_of_results++;
    
    $output_rows .= '
        <tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td class="chart_label">' . h($name) . '</td>
            <td>' . h($code) . '</td>
            <td>' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($username) . '</td>
        </tr>';
}

print 
output_header() . '
<div id="subnav">
    ' . render(array('template' => 'commerce_subnav.php')) . '
</div>
<div id="button_bar">
    <a href="add_referral_source.php">Create Referral Source</a>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All Referral Sources</h1>
    <div class="subheading">All possible answers to the checkout question: "How did you hear about us?"</div>
    <div class="view_summary">
        Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
    </div>
    <table class="chart">
        <tr>
            <th>' .asc_or_desc('Name','view_referral_sources'). '</td>
            <th>' .asc_or_desc('Code','view_referral_sources'). '</td>
            <th>' .asc_or_desc('Last Modified','view_referral_sources'). '</td>
        </tr>
        ' . $output_rows . '
    </table>
</div>' .
output_footer();
?>